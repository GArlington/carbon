<?php
include_once("Q/QRegex.php");


/**
*  Implements a property constraint.
*/
class Constraint
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
	*  examples:
	*	constraints="Trim;Required"
	*	constraints="LargerThan(StartDate)"
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