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
	public function Load($sourceDir, $plugins=null)
	{
		if( !($files=DirectoryIO::GetFiles($sourceDir, "*.xml", true)) )
			throw new Exception("No files to read in path: '$sourceDir'");

		foreach( $files as $file ) {
			print("\n\t$file" );

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


		// Apply dynamic model extension plugins...
		if( $plugins && count($plugins) ) {
			print("\n\nRunning plugins:");
			foreach($plugins as $name => $plugin) {
				print("\n\t$name");
				$plugin->Run($this);
			}
		}

		$this->ConsolidateModel();

		// Sort dictionaries...
		foreach($this->packages as $package) {
			ksort($package->entities);
			ksort($package->enumerations);
		}
		ksort($this->manifest);
		ksort($this->packages);
	}

	/**
	*  Validates properties' foreign types and implements interfaces that are not.
	*/
	private function ConsolidateModel()
	{
		foreach($this->manifest as $object) {
			if( $object instanceof Entity )
				$this->ValidatePropertyType($object);

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