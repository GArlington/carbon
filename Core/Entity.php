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
	public $refby      = array();

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
	*  Constructor.
	*/
	public function __construct($node, $package)
	{
		parent::__construct($node, $package);

		$this->abstract = $this->ReadAttr('abstract');
		$this->interfaces = Regex::SplitWords(',', $this->ReadAttr('implements'));

		// Load children...
		if( isset($node->property) )
			$this->ImportNodes($node->property, "Property", $this->properties);

		if( isset($node->unique) )
			$this->ImportNodes($node->unique, "Unique", $this->uniques);

		if( isset($node->index) )
			$this->ImportNodes($node->index, "Index", $this->indexes);

		// Validate children...
		foreach($this->uniques as $unique)
			$this->CheckConstraintTargets($unique);

		foreach($this->indexes as $index)
			$this->CheckConstraintTargets($index);
	}

	/**
	*  Returns true if entity implements given interface.
	*/
	public function HasInterface($name)
	{
		return in_array($name, $this->interfaces);
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