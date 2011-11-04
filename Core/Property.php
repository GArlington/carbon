<?php
include_once("XmlElement.php");
include_once("PropertyConstraint.php");
include_once("Q/Regex.php");


/**
*  Implements an entity property.
*/
class Property extends XmlElement
{
	/**
	*  Property type as read in the XML file.
	*/
	public $rawtype;

	/**
	*  Property type after being massaged, e.g. size removed from text.
	*/
	public $type;

	/**
	*  Size extracted from raw type and converted to bytes of necessary.
	*/
	public $size;

	/**
	*  Default property value.
	*/
	public $default;

	/**
	*  List of constraint applied to the property.
	*/
	public $constraints = array();

	/**
	*  Reference to entity or enumeration object if not a base type.
	*  This is filled by the model instance on the second pass.
	*/
	public $typeref;

	/**
	*  Interface name if injected from one.
	*  This is filled by the model instance on the second pass.
	*/
	public $interface;


	/*
	*  Constructor.
	*/
	function __construct($node, $package=null)
	{
		parent::__construct($node, $package);

		// Read default value...
		$this->default = $this->ReadAttr("default");

		// Read type as defined, before we start messing with it...
		$this->rawtype = $this->ReadAttr("type");

		// Read size handling K and M multipliers (e.g. text:20K or binary:50M)
		list($this->type,$this->size) = explode(':',$this->rawtype.":0");
		$multiplier = strtolower(substr($this->size, -1));
		if( $multiplier=='k' )
			$this->size *= 1024;
		elseif( $multiplier=='m' )
			$this->size *= (1024 * 1024);

		// Read constraints
		foreach( Regex::SplitWords(';',$this->ReadAttr("constraint")) as $signature ) {
			$constraint = new PropertyConstraint($signature);
			$this->constraints[$constraint->name] = $constraint;
		}

		if( $this->type=='logical' && $this->default=='' )
			$this->default = 'false';
	}

	/**
	*  Returns true if property has a constraint of given name.
	*/
	function HasConstraint($name)
	{
		return isset($this->constraints[$name]);
	}
}