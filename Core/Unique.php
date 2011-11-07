<?php
include_once("Q/Regex.php");
include_once("XmlElement.php");


/**
*  Implements a database table unicity constraint.
*/
class Unique extends XmlElement
{
	/**
	*  Target properties.
	*/
	public $ref=array();

	/**
	*  Constructor.
	*/
	public function __construct($node)
	{
		parent::__construct($node);
		$this->ref = Regex::SplitWords(',',$this->ReadAttr("ref"));
	}
}