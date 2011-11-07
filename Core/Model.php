<?php
include_once("Q/DirectoryIO.php");
include_once("Q/Regex.php");
include_once("Package.php");


/**
*  Implements the data model defined in XML files and used by the various
*  generators to produce their output.
*/
class Model
{
	/**
	*  Allowed base types. Every other type is expected
	*  to be found in the model.
	*/
	public static $BASE_TYPES = array(
		'logical',
		'integer',
		'real',
		'time',
		'text',
		'binary'
	);

	/**
	*  Contains all model objects indexed by object name.
	*/
	public $objDictionary = array();

	/**
	*  Contains all model objects grouped by package.
	*/
	public $pkgDictionary = array();

	/**
	*  Namespace supplied to the constructor. Used by generators.
	*/
	public $namespace;

	/**
	*  License (copyright) supplied to the constructor. Used by generators.
	*/
	public $license;

	/**
	*  Constructor.
	*/
	public function __construct($namespace, $license)
	{
		$this->namespace = $namespace;
		$this->license = $license;
	}

	/**
	*  Loads model from xml files in given directory.
	*  - The search is recursive.
	*  - One package per file.
	*/
	public function Load($path)
	{
		// Are there any files to read?
		if( !($files=DirectoryIO::GetFiles($path, "*.xml", true)) )
			throw new Exception("No files to read in path: '$path'");

		foreach( $files as $file ) {
			print("\nLoading: $file" );

			if( !$rootnode = simplexml_load_file($file) )
				throw new Exception("Error reading xml file");

			if( $rootnode->GetName() == "package" )
				$package = new Package($rootnode, $this->objDictionary);
			else
				throw new Exception("Unrecognized xml root node '".$rootnode->GetName()."' in: $file");
		}

		// Create the package dictionary from the objects one.
		foreach($this->objDictionary as $object)
			$this->pkgDictionary[$object->package->name][$object->name] = $object;

		// Sort dictionaries...
		ksort($this->objDictionary);
		ksort($this->pkgDictionary);
		foreach($this->pkgDictionary as &$object_dictionary)
			ksort($object_dictionary);

		// Perform operations that require a complete model...
		$this->SecondPass();
	}


	/**
	*  Dumps model to console.
	*/
	public function Debug()
	{
		foreach($this->pkgDictionary as $name=>$pkgobjects) {
			print("\n_______________________________________________");
			print("\nPackage $name");

			foreach( $pkgobjects as $object ) {
				if( $object instanceof Entity ) {
					print("\n\n\tEntity $object->name");
					print(" interfaces=[".implode(',', $object->interfaces)."]");
					print(" tags=[".implode(',', array_keys($object->tags))."]");
					print(" refby=[".implode(',', array_keys($object->refby))."]");
					foreach($object->properties as $property) {
						$type = $property->type . ($property->size?"[$property->size]":'');
						printf("\n\t\t%-30s%-20s tags=[%s]", $property->name, $type, implode(',', array_keys($property->tags)));
					}
				}
				elseif( $object instanceof Enumeration ) {
					print("\n\n\tEnumeration $object->name");
					foreach($object->values as $value)
						printf("\n\t\t%-30s%-20s tags[%s]", $value->name, $value->value, implode(',', array_keys($value->tags)));
				}
			}
		}
	}

	/**
	*  Validates that properties references in index, unique constraints,
	*  etc are effectively members of said entity.
	*/
	private function SecondPass()
	{
		foreach($this->objDictionary as $object)
			if( $object instanceof Entity ) {
				// Validate property types...
				$this->ValidatePropertyType($object);

				// Implements interfaces...
				$implemented_interfaces = array();
				foreach($object->interfaces as $interface)
					$this->InjectInterface($object, $interface, $implemented_interfaces);

				foreach($object->uniques as $unique)
					$this->AssertPropertyList($object, $unique->ref, $unique->name);

				foreach($object->indexes as $index)
					$this->AssertPropertyList($object, $index->ref, $index->name);
			}
	}


	/**
	*  Prints an error if one of the properties in the received
	*  comma delimited list contains an unknown property.
	*/
	private function AssertPropertyList($entity, $list, $cname)
	{
		$pkgname = $entity->package->name;
		$fullname = "$pkgname.$entity->name.$cname";
		foreach($list as $pname)
			if(!isset($entity->properties[$pname]))
				Print("\nERROR: Unknown property '$pname' referenced in '$fullname'");
	}


	/**
	*  Validates given entity's properties types.
	*/
	private function ValidatePropertyType($entity)
	{
		foreach($entity->properties as $property) {
			// Build fully qualified name for error reporting...
			$pkgname = $entity->package->name;
			$fqn = "$pkgname.$entity->name.$property->name";

			// Check size against types...
			if( ($property->type=="text" || $property->type=="binary") && $property->size<1 )
					Print("\nERROR: Invalid property size for '$fqn'.");

			if( $property->type!="text" && $property->type!="binary" && $property->size!=0 )
					Print("\nERROR: Property size not allowed for '$fqn'.");

			// Set objects instances for reference types...
			if( !in_array($property->type, Model::$BASE_TYPES) )
				if( !isset($this->objDictionary[$property->type]) )
					Print("\nERROR: Unknown property type ($property->type) for '$fqn'.");
				else {
					$t = $property->typeref = $this->objDictionary[$property->type];

					// While at it update reverse reference...
					if( $t && $t instanceof Entity && !isset($t->refby[$entity->name]) )
						$t->refby[$entity->name] = $entity;
				}
		}
	}


	/**
	*  Implement given interface in entity. If said interface also implements some
	*  interfaces we recursively implement them unless it's already been implemented.
	*/
	private function InjectInterface($entity, $interface, &$implemented_interfaces, $recursing=false)
	{
		// Already implemented?
		if( in_array($interface, $implemented_interfaces) )
			return;

		$pkgname = $entity->package->name;
		$fullname = "$pkgname.$entity->name";

		// Do we know this interface?
		if( !isset($this->objDictionary[$interface]) ) {
			Print("\nERROR: Unknown interface '$interface' specified in '$fullname'");
			return;
		}

		// Get instance of interface from manisfest and visit its properties,
		// injecting those that are not already there.
		$other = $this->objDictionary[$interface];
		$otherpkgname = $other->package->name;
		foreach($other->properties as $property)
			if( isset($entity->properties[$property->name]) && !$recursing )
				throw new Exception("Property '$fullname.$property->name' already implemented in interface '$otherpkgname.$interface'");
			else {
				$entity->properties[$property->name] = $property;
				$entity->properties[$property->name]->interface = $interface;
			}


		// Add this interface to the list of already implemented interfaces...
		$implemented_interfaces[] = $interface;

		// Recurse if this other entity/interface also have some interfaces of its own...
		foreach($other->interfaces as $subinterface)
				$this->InjectInterface($entity, $subinterface, $implemented_interfaces, true);
	}
}