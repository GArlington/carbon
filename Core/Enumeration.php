<?php
include_once("XmlElement.php");
include_once("EnumerationValue.php");


/**
*  Implements a model enumeration.
*/
class Enumeration extends XmlElement
{
	/**
	*  List of all interface names.
	*/
	public $interfaces = array();

	/**
	*  Enumeration values.
	*/
	public $values = array();

	/**
	*  List of interfaces that have effectively been implemented.
	*  Internally used by the ImplementInterface() method.
	*/
	private $implementedInterfaces = array();


	/*
	*  Constructor.
	*/
	function __construct($package, $node=null)
	{
		parent::__construct($package, $node);

		if( $node ) {
			$this->interfaces = QRegex::SplitWords(',', $this->ReadAttr('implements'));
			if( isset($node->value) )
				$this->ImportNodes($package, $node->value, "EnumerationValue", $this->values);
			$this->AssertUniqueValue();
		}
	}

	/**
	*  Adds an enumeration value.
	*/
	public function AddValue($name, $value, $comment="", $hint="", $labels=null)
	{
		$fqn = sprint("%s.%s.%s", $this->package->name, $this->name, $name);

		if( isset($this->values[$name]) )
			throw new Exception("Duplicate enumeration value '$fqn'");

		$value = new EnumerationValue($this->package);
		$value->name = $name;
		$value->value = $value;
		$value->hint = $hint;
		$value->comment = $comment;
		$value->labels = $labels ? $labels : array();
		$this->AssertUniqueValue();
	}

	/**
	*  Returns true if entity implements given interface.
	*/
	public function HasInterface($name)
	{
		return in_array($name, $this->implementedInterfaces);
	}

	/**
	*  Implements given interface from model manifest.
	*/
	public function ImplementInterface($name, &$manifest, $recursing=false)
	{
		// Leave if already implemented:
		if( $this->HasInterface($name) )
			return;

		// Construct our fully qualified name:
		$fqn = sprintf("%s.%s", $this->package->name, $this->name);

		// Leave if interface is unknown:
		if( !isset($manifest[$name]) || !($manifest[$name] instanceof $this) ) {
			print("\nERROR: Invalid interface '$name' in '$fqn'");
			return;
		}

		// Create reference to foreign component:
		$foreign = $manifest[$name];
		$foreignpkg = $foreign->package->name;

		// Import foreign properties:
		foreach($foreign->values as $value)
			if( isset($this->values[$value->name]) && !$recursing )
				throw new Exception("Enumeration value '$fqn.$value->name' already implemented in interface '$foreignpkg.$name'");
			else
				$this->values[$value->name] = $value;
		$this->implementedInterfaces[] = $name;

		// Recurse if this foreign entity also have some interfaces of its own:
		foreach($foreign->interfaces as $subinterface)
			$this->ImplementObjectInterface($subinterface, $manifest, true);
	}

	/**
	*  Throws an exception if duplicate enumeration values are found.
	*/
	private function AssertUniqueValue()
	{
		$knownValues = array();
		foreach($this->values as $item) {
			$fqn = sprintf("%s.%s.%s", $this->package->name, $this->name, $item->name);
			if( in_array($item->value, $knownValues) )
				throw new Exception("Duplicate enumeration value '$fqn'");
			else
				$knownValues[] = $item->value;
		}
	}
}