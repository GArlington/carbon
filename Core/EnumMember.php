<?php
include_once("XmlElement.php");


/**
*  Implements an enumeration value.
*/
class EnumMember extends XmlElement
{
	public $value;

	/*
	*  Constructor.
	*/
	function __construct($node)
	{
		parent::__construct($node);
		$this->value = $this->ReadAttr("value", $this->name);
	}
}