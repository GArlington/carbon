<?php
include_once("Q/QRegex.php");


/**
*  Implements an element hint. They can be applied to any XML element and
*  are a generic way to trigger some behavior in the generators.
*/
class Hint
{
	/**
	*  Constraint name.
	*/
	public $name;

	/**
	*  Constraint parameters.
	*/
	public $params;

	/**
	*  Constraint signature.
	*/
	public $signature;


	/**
	*  Constructor.
	*
	*  $str syntax: name[([@]param1,[@]param2,...)]
	*
	*  Constraints can have parameters and these parameters can refer
	*  to other property values if they start with the @ character.
	*
	*  examples:
	*	constraints="Trim Required"
	*	constraints="LargerThan(@StartDate)"
	*	constraints="MinLenght(3)"
	*	constraints="ConstraintRange(10,80)"
	*/
	public function __construct($signature)
	{
		$this->signature = $signature;
		$parts = QRegex::SplitFunc($signature);
		$this->name = array_shift($parts);
		$this->params = $parts;
	}
}