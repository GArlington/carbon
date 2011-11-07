<?php
include_once("Q/Regex.php");
include_once("XmlElement.php");


/**
*  Implements a table index.
*/
class Index extends XmlElement
{
	public $ref=array();

	public function __construct($package, $node)
	{
		parent::__construct($package, $node);
		$this->ref = Regex::SplitWords(',',$this->ReadAttr("ref"));
	}
}