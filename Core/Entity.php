<?php
include_once("Q/Regex.php");
include_once("XmlElement.php");
include_once("Property.php");
include_once("Unique.php");
include_once("Index.php");

/**
*  Implements a model entity.
*/
class Entity extends XmlElement
{
	/**
	*  If true, the entity is abstract and is expected to have no database
	*  representation. It is usualy used to inject properties in other
	*  abstract or non-abstract entities.
	*/
	public $abstract;

	/**
	*  List of all entities refering to this one. It is filled by the model
	*  class on its second pass.
	*/
	public $refby = array();

	/**
	*  List of all interface names that have been used to inject properties
	*  in this entity.
	*/
	public $interfaces = array();

	/**
	*  List of all properties of the entity.
	*/
	public $properties = array();

	/**
	*  List of unique constraints defined for the entity.
	*  These normaly only have database representations.
	*/
	public $uniques = array();

	/**
	*  List of indexes defined for the entity.
	*  These normaly only have database representations.
	*/
	public $indexes = array();

	/**
	*  List of interfaces that have effectively been implemented.
	*  Internally used by the ImplementInterface() method.
	*/
	private $implementedInterfaces = array();


	/**
	*  Constructor.
	*/
	public function __construct($package, $node=null)
	{
		parent::__construct($package, $node);

		if( $node ) {
			$this->abstract = $this->ReadAttr('abstract');
			$this->interfaces = Regex::SplitWords(',', $this->ReadAttr('implements'));

			if( isset($node->property) )
				$this->ImportNodes($package, $node->property, "Property", $this->properties);

			if( isset($node->unique) )
				$this->ImportNodes($package, $node->unique, "Unique", $this->uniques);

			if( isset($node->index) )
				$this->ImportNodes($package, $node->index, "Index", $this->indexes);

			foreach($this->uniques as $unique)
				$this->CheckConstraintTargets($unique);

			foreach($this->indexes as $index)
				$this->CheckConstraintTargets($index);
		}
	}

	/**
	*  Returns true if entity implements given interface.
	*/
	public function HasInterface($name)
	{
		return in_array($name, $this->implementedInterfaces);
	}

	/**
	*  Adds an enumeration value.
	*/
	public function AddProperty($property)
	{
		$fqn = sprintf("%s.%s.%s", $this->package->name, $this->name, $property->name);
		if( isset($this->properties[$property->name]) )
			throw new Exception("Duplicate property '$fqn'");
		$this->properties[$property->name] = $property;
	}

	/**
	*  Adds a unicity constraint. Receives a Unique instance.
	*/
	public function AddUnicityConstraint($constraint)
	{
		$fqn = sprintf("%s.%s.%s", $this->package->name, $this->name, $constraint->name);
		if( $this->uniques[$constraint->name] )
			throw new Exception("Duplicate unicity constraint '$fqn'");
		$this->uniques[$constraint->name] = $constraint;
	}

	/**
	*  Adds a unicity constraint. Receives a Unique instance.
	*/
	public function AddIndex($index)
	{
		$fqn = sprintf("%s.%s.%s", $this->package->name, $this->name, $index->name);
		if( $this->indexes[$index->name] )
			throw new Exception("Duplicate index '$fqn'");
		$this->indexes[$index->name] = $index;
	}

	/**
	*  Implements given interface from model manifest.
	*/
	public function ImplementInterface($name, &$manifest, $recursing=false)
	{
		// Leave if already implemented:
		if( $this->HasInterface($name) )
			return;

		// Construct our fully qualified name:
		$fqn = sprintf("%s.%s",$this->package->name, $this->name);

		// Leave if interface is unknown:
		if( !isset($manifest[$name]) || !($manifest[$name] instanceof $this) ) {
			print("\nERROR: Invalid interface '$name' in '$fqn'");
			return;
		}

		// Create reference to foreign component:
		$foreign = $manifest[$name];
		$foreignpkg = $foreign->package->name;

		// Import foreign properties:
		foreach($foreign->properties as $property)
			if( isset($this->properties[$property->name]) && !$recursing )
				throw new Exception("Property '$fqn.$property->name' already implemented in interface '$foreignpkg.$name'");
			else
				$this->properties[$property->name] = $property;

		$this->implementedInterfaces[] = $name;

		// Recurse if this foreign entity also have some interfaces of its own:
		foreach($foreign->interfaces as $subinterface)
			$this->ImplementObjectInterface($subinterface, $manifest, true);
	}

	/**
	*  Checks that constraint targets are all members of the entity.
	*/
	private function CheckConstraintTargets($constraint)
	{
		$fullname = sprintf("%s.%s.%s",$this->package->name, $this->name, $constraint->name);

		foreach($constraint->ref as $reference)
			if( !isset($this->properties[$reference]) )
				Print("\nERROR: Unknown property '$reference' referenced in '$fullname'");
	}
}