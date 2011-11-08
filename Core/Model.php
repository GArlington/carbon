<?php
include_once("Magic.php");
include_once("Q/DirectoryIO.php");
include_once("Q/Regex.php");
include_once("Package.php");


/**
*  Implements the data model defined in XML files and used by the various
*  generators to produce their output.
*/
class Model
{
	public static $BASE_TYPES = array('logical','integer','real','time','text','binary');

	/**
	*  Contains all model objects indexed by object name.
	*/
	public $manifest = array();

	/**
	*  Contains all model objects grouped by package.
	*/
	public $packages = array();

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
	*  Recursively loads model from xml package files in given directory.
	*/
	public function Load($path)
	{
		if( !($files=DirectoryIO::GetFiles($path, "*.xml", true)) )
			throw new Exception("No files to read in path: '$path'");

		foreach( $files as $file ) {
			print("\nLoading: $file" );

			if( !$rootnode = simplexml_load_file($file) )
				throw new Exception("Error reading xml file");

			if( $rootnode->GetName() == "package" ) {
				$package = new Package($rootnode, $this->manifest);
				if( isset($this->packages[$package->name]) )
					throw new Exception("Package $package->name already loaded." );
				$this->packages[$package->name] = $package;
			}
			else
				throw new Exception("Unrecognized xml root node '".$rootnode->GetName()."' in: $file");
		}

		// Sort dictionaries...
		ksort($this->manifest);
		ksort($this->packages);

		// Perform operations that require a complete model...
		$this->SecondPass();
	}


	/**
	*  Dumps model to console.
	*/
	public function Debug()
	{
		foreach($this->packages as $name=>$pkgobjects) {
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
		foreach($this->manifest as $object) {
			if( $object instanceof Entity )
				$this->ValidatePropertyType($object);

			$implemented_interfaces = array();
			foreach($object->interfaces as $interface)
				$object->ImplementInterface($interface, $this->manifest);
		}
	}

	/**
	*  Validates given entity's properties types.
	*/
	private function ValidatePropertyType($object)
	{
		foreach($object->properties as $property) {
			$fqn = sprintf("%s.%s.%s",$object->package->name,$object->name,$property->name);

			if( ($property->type=="text" || $property->type=="binary") && $property->size<1 )
					Print("\nERROR: Invalid property size for '$fqn'.");

			if( $property->type!="text" && $property->type!="binary" && $property->size!=0 )
					Print("\nERROR: Property size not allowed for '$fqn'.");

			if( !in_array($property->type, Model::$BASE_TYPES) )
				if( !isset($this->manifest[$property->type]) )
					Print("\nERROR: Unknown property type ($property->type) for '$fqn'.");
				else {
					$t = $this->manifest[$property->type];

					// Also update foreign reference...
					if( $t && $t instanceof Entity && !isset($t->refby[$object->name]) )
						$t->refby[$object->name] = $object;

					$property->typeref = $t;
				}
		}
	}
}