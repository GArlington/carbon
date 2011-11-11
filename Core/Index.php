<?php
include_once("Q/Regex.php");
include_once("XmlElement.php");


/**
*  Implements a table index.
*/
class Index extends XmlElement
{
	public $ref = array();

	/**
	*  Constructor.
	*/
	public function __construct($package, $node=null)
	{
		parent::__construct($package, $node);
		if( $node )
			$this->SetReferences( $this->ReadAttr("ref") );
	}

	/**
	*  Assigns index references using given comma delimited string.
	*/
	public function SetReferences($refs)
	{
		$this->ref = Regex::SplitWords(',', $refs);
	}
}