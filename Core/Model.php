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
					foreach($object->members as $member) {
						$type = $member->type . ($member->size?"[$member->size]":'');
						printf("\n\t\t%-30s%-20s tags=[%s]", $member->name, $type, implode(',', array_keys($member->tags)));
					}
				}
				elseif( $object instanceof Enum ) {
					print("\n\n\tEnum $object->name");
					foreach($object->members as $member)
						printf("\n\t\t%-30s%-20s tags[%s]", $member->name, $member->value, implode(',', array_keys($member->tags)));
				}
			}
		}
	}

	/**
	*  Validates that members references in index, unique constraints,
	*  etc are effectively members of said entity.
	*/
	private function SecondPass()
	{
		foreach($this->objDictionary as $object)
			if( $object instanceof Entity ) {
				// Validate member types...
				$this->ValidateMemberType($object);

				// Implements interfaces...
				$implemented_interfaces = array();
				foreach($object->interfaces as $interface)
					$this->InjectInterface($object, $interface, $implemented_interfaces);

				foreach($object->uniques as $unique)
					$this->AssertMembersList($object, $unique->ref, $unique->name);

				foreach($object->indexes as $index)
					$this->AssertMembersList($object, $index->ref, $index->name);
			}
	}


	/**
	*  Prints an error if one of the members in the received
	*  comma delimited list contains an unknown member.
	*/
	private function AssertMembersList($entity, $list, $cname)
	{
		$pkgname = $entity->package->name;
		$fullname = "$pkgname.$entity->name.$cname";
		foreach($list as $pname)
			if(!isset($entity->members[$pname]))
				Print("\nERROR: Unknown member '$pname' referenced in '$fullname'");
	}


	/**
	*  Validates given entity's members types.
	*/
	private function ValidateMemberType($entity)
	{
		foreach($entity->members as $member) {
			// Build fully qualified name for error reporting...
			$pkgname = $entity->package->name;
			$fqn = "$pkgname.$entity->name.$member->name";

			// Check size against types...
			if( ($member->type=="text" || $member->type=="binary") && $member->size<1 )
					Print("\nERROR: Invalid member size for '$fqn'.");

			if( $member->type!="text" && $member->type!="binary" && $member->size!=0 )
					Print("\nERROR: Member size not allowed for '$fqn'.");

			// Set objects instances for reference types...
			if( !in_array($member->type, Model::$BASE_TYPES) )
				if( !isset($this->objDictionary[$member->type]) )
					Print("\nERROR: Unknown member type ($member->type) for '$fqn'.");
				else {
					$t = $member->typeref = $this->objDictionary[$member->type];

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

		// Get instance of interface from manisfest and visit its members,
		// injecting those that are not already there.
		$other = $this->objDictionary[$interface];
		$otherpkgname = $other->package->name;
		foreach($other->members as $member)
			if( isset($entity->members[$member->name]) && !$recursing )
				throw new Exception("Member '$fullname.$member->name' already implemented in interface '$otherpkgname.$interface'");
			else {
				$entity->members[$member->name] = $member;
				$entity->members[$member->name]->interface = $interface;
			}


		// Add this interface to the list of already implemented interfaces...
		$implemented_interfaces[] = $interface;

		// Recurse if this other entity/interface also have some interfaces of its own...
		foreach($other->interfaces as $subinterface)
				$this->InjectInterface($entity, $subinterface, $implemented_interfaces, true);
	}
}