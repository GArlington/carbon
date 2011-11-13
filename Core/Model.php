<?php
include_once("Magic.php");
include_once("Q/QDirectory.php");
include_once("Q/QRegex.php");
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
		if( !($files=QDirectory::GetFiles($sourceDir, "*.xml", true)) )
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


		// Apply model plugins...
		if( $plugins && count($plugins) ) {
			print("\n\nRunning plugins:");
			foreach($plugins as $name => $plugin) {
				print("\n\t$name");
				$plugin->Run($this);
			}
		}

		$this->ConsolidateModel();

		// Sort...
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
			foreach($object->interfaces as $interface)
				$object->ImplementInterface($interface, $this->manifest);

			if( $object instanceof Entity ) {
				$this->ValidatePropertyType($object);
				$this->ValidateIndexes($object);
				$this->ValidateUnicityConstraints($object);
			}
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
					print("\nERROR: Invalid property size for '$fqn'.");

			if( $property->type!="text" && $property->type!="binary" && $property->size!=0 )
					print("\nERROR: Property size not allowed for '$fqn'.");

			if( in_array($property->type, Model::$BASE_TYPES) )
				continue;
				
			if( !isset($this->manifest[$property->type]) )	
				print("\nERROR: Unknown property type ($property->type) for '$fqn'.");
			else {
				$t = $this->manifest[$property->type];

				// Also update foreign reference...
				if( $t && $t instanceof Entity && !isset($t->refby[$object->name]) )
					$t->refby[$object->name] = $object;

				$property->typeref = $t;
				
				// If referencing an enumeration, checks that the default value is valid...
				if( $property->typeref instanceof Enumeration && $property->default )
					if( !isset($property->typeref->values[$property->default]) )
						print("\nERROR: Invalid default value '$property->default' for $fqn");
			}
		}
	}

	/**
	*  Validates entity indexes and unique constraints.
	*/
	private function ValidateIndexes($object)
	{
		foreach($object->indexes as $index) {
			$fqn = sprintf("%s.%s.%s",$object->package->name,$object->name,$index->name);
			foreach($index->ref as $name)
				if( !isset($object->properties[$name]) )
					print("\nERROR: Unknown property '$name' in index '$fqn'.");
		}
	}

	/**
	*  Validates entity indexes and unique constraints.
	*/
	private function ValidateUnicityConstraints($object)
	{
		foreach($object->uniques as $unique) {
			$fqn = sprintf("%s.%s.%s",$object->package->name,$object->name,$unique->name);
			foreach($unique->ref as $name)
				if( !isset($object->properties[$name]) )
					print("\nERROR: Unknown property '$name' in unicity constraint '$fqn'.");
		}
	}
}