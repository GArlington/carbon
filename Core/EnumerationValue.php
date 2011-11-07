<?php
include_once("XmlElement.php");


/**
*  Implements an enumeration value.
*/
class EnumerationValue extends XmlElement
{
	public $value;

	/*
	*  Constructor.
	*/
	function __construct($package, $node)
	{
		parent::__construct($package, $node);
		$this->value = $this->ReadAttr("value", $this->name);
	}
}