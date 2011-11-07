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
	function __construct($package, $node=null)
	{
		parent::__construct($package, $node);

		if( $node ) {
			if( isset($node->value) )
				$this->ImportNodes($package, $node->value, "EnumerationValue", $this->values);

			// Check values unicity, names are already checked by ImportNodes()
			$knownValues = array();
			foreach($this->values as $item)
				if( in_array($item->value, $knownValues) )
					throw new Exception("Duplicate enumeration value in '$package->name.$this->name'");
				else
					$knownValues[] = $item->value;
		}
	}
}