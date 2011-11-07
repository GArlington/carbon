<?php
include_once("XmlElement.php");
include_once("EnumMember.php");


/**
*  Implements a model enumeration.
*/
class Enum extends XmlElement
{
	/**
	*  Enumeration members.
	*/
	public $members = array();


	/*
	*  Constructor.
	*/
	function __construct($node, $package)
	{
		parent::__construct($node, $package);

		if( isset($node->member) )
			$this->ImportNodes($node->member, "EnumMember", $this->members);

		if( !$this->members || !count($this->members) )
			throw new Exception("No enumeration members for '$package->name.$this->name'");

		// Ensure unicity of enumeration members (names are already
		// checked by the ImportNodes() method...)
		$knownValues = array();
		foreach($this->members as $item)
			if( in_array($item->value, $knownValues) )
				throw new Exception("Duplicate enumeration member in '$package->name.$this->name'");
			else
				$knownValues[] = $item->value;
	}
}