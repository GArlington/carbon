<?php
include_once("XmlElement.php");


/**
*  Implements an enumeration value.
*/
class EnumerationValue extends XmlElement
{
	public $value;
	public $origin;

	/*
	*  Constructor.
	*/
	function __construct($package, $node=null)
	{
		parent::__construct($package, $node);
		$this->value = $this->ReadAttr("value", $this->name);
	}
}