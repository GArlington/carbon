<?php
include_once("XmlElement.php");
include_once("Constraint.php");
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


	/*
	*  Constructor.
	*/
	function __construct($package, $node=null)
	{
		parent::__construct($package, $node);

		if( $node ) {
			$this->default = $this->ReadAttr("default");
			$this->SetType($this->ReadAttr("type"));
			$this->SetConstraint($this->ReadAttr("constraint"));
		}
	}

	/**
	*  Returns a new property instance from given arguments.
	*/
	static function Make($name, $type, $comment="")
	{
		$result = new Property(null);
		$result->name = $name;
		$result->SetType($type);
		$result->comment = $comment;
		return $result;
	}

	/**
	*  Returns true if property has a constraint of given name.
	*/
	function HasConstraint($name)
	{
		return isset($this->constraints[$name]);
	}

	/**
	*  Sets the various rawtype, type and size members from received raw type
	*/
	public function SetType($type)
	{
		$this->rawtype = $type;

		// Read size handling K and M multipliers (e.g. text:20K or binary:50M)
		list($this->type,$this->size) = explode(':',$this->rawtype.":0");
		$multiplier = strtolower(substr($this->size, -1));
		if( $multiplier=='k' )
			$this->size *= 1024;
		elseif( $multiplier=='m' )
			$this->size *= (1024 * 1024);
	}

	public function SetConstraint($constraint)
	{
		foreach( Regex::SplitWords(';',$constraint) as $signature ) {
			$constraint = new Constraint($signature);
			$this->constraints[$constraint->name] = $constraint;
		}
	}
}