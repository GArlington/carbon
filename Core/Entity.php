<?php
include_once("Q/Regex.php");
include_once("XmlElement.php");
include_once("EntityMember.php");
include_once("Unique.php");
include_once("Index.php");


/**
*  Implements a model entity.
*/
class Entity extends XmlElement
{
	/**
	*  If true, the entity is abstract and is expected to have no database
	*  representation. It is usualy used to inject members in other
	*  abstract or non-abstract entities.
	*/
	public $abstract;

	/**
	*  List of all entities refering to this one. It is filled by the model
	*  class on its second pass.
	*/
	public $refby = array();

	/**
	*  List of all interface names that have been used to inject members
	*  in this entity.
	*/
	public $interfaces = array();

	/**
	*  List of all members of the entity.
	*/
	public $members = array();

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
		if( $node )
			$this->InitFromXml($node, $package);
	}

	/**
	*  Returns true if entity implements given interface.
	*/
	public function HasInterface($name)
	{
		return in_array($name, $this->interfaces);
	}

	/**
	*  Initializes entity using given parameters.
	*/
	public function InitFromXml($node, $package)
	{
		parent::__construct($node, $package);

		$this->abstract = $this->ReadAttr('abstract');
		$this->interfaces = Regex::SplitWords(',', $this->ReadAttr('implements'));

		// Load children...
		if( isset($node->member) )
			$this->ImportNodes($node->member, "EntityMember", $this->members);

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
	*  Checks that constraint targets are all members of the entity.
	*/
	private function CheckConstraintTargets($constraint)
	{
		$fullname = sprintf("%s.%s.%s",$this->package->name, $this->name, $constraint->name);

		foreach($constraint->ref as $reference)
			if( !isset($this->members[$reference]) )
				Print("\nERROR: Unknown member '$reference' referenced in '$fullname'");
	}
}