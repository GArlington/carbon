<?php
include_once("XmlElement.php");
include_once("EnumerationValue.php");


/**
*  Implements a model enumeration.
*/
class Enumeration extends XmlElement
{
	/**
	*  Enumeration values.
	*/
	public $values = array();


	/*
	*  Constructor.
	*/
	function __construct($node, $package)
	{
		parent::__construct($node, $package);

		if( isset($node->value) )
			$this->ImportNodes($node->value, "EnumerationValue", $this->values);

		if( !$this->values || !count($this->values) )
			throw new Exception("No enumeration values for '$package->name.$this->name'");

		// Ensure unicity of enumeration values (names are already
		// checked by the ImportNodes() method...)
		$knownValues = array();
		foreach($this->values as $item)
			if( in_array($item->value, $knownValues) )
				throw new Exception("Duplicate enumeration value in '$package->name.$this->name'");
			else
				$knownValues[] = $item->value;
	}
}