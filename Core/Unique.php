<?php
include_once("Q/QRegex.php");
include_once("XmlElement.php");


/**
*  Implements a database table unicity constraint.
*/
class Unique extends XmlElement
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
	*  Assigns unique references using given comma delimited string.
	*/
	public function SetReferences($refs)
	{
		$this->ref = QRegex::SplitWords(',', $refs);
	}
}