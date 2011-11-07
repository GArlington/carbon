<?php
include_once("Q/Regex.php");
include_once("Hint.php");

/**
*  The maximum identifier length is necessary because of database identifier
*  limitations. Oracle allows 30 characters, MySql allows 64, etc. We also
*  want to garantee some space for prefixes/suffixes when generating
*  some entity related tables, constraints, etc. Set value according to
*  database(s) you want to support.
*/
define('MAX_IDENTIFIER_LENGTH', 25);


/**
*  If language is not specified in <label> tags this is the one we assume.
*/
define('DEFAULT_LANG','en');


/**
*  This is the base class of most model objects and components.
*  It encapsulates attributes and common code.
*/
class XmlElement
{
	/**
	*  Element name. Must be unique within the model.
	*/
	public $name;

	/**
	*  Element's package if applicable.
	*/
	public $package;

	/**
	*  Element's comment.
	*/
	public $comment;

	/**
	*  List of generator hints for things not implemented by Carbon.
	*/
	public $hints = array();

	/**
	*  List of multi language text labels.
	*/
	public $labels = array();

	/**
	*  Element's XML source node.
	*/
	private $node;


	/**
	*  Constructor.
	*/
	protected function __construct($node, $package=null)
	{
		$this->node    = $node;
		$this->package = $package;
		$this->name    = $this->ReadAttr("name");
		$this->comment = $this->ReadAttr("comment");

		$classname = get_class($this);

		if( $this->name=="" )
			throw new Exception("Missing $classname name.");

		if( strlen($this->name) > MAX_IDENTIFIER_LENGTH )
			throw new Exception("Identifier too long: $this->name, max: ".MAX_IDENTIFIER_LENGTH);

		// Read hints...
		foreach( Regex::SplitWords(';',$this->ReadAttr("hint")) as $signature ) {
			if( trim($signature) ) {
				$hint = new Hint($signature);
				$this->hints[$hint->name] = $hint;
			}
		}

		if( $node->label )
			foreach( $node->label as $label ) {
				if( isset($label['lang']) )
					$lang = trim( (string)$label['lang'] );
				else
					$lang = DEFAULT_LANG;
				$this->labels[$lang] = trim((string)$label);
			}
	}


	/**
	*  Returns tag value or $default if undefined for this entity.
	*/
	public function GetHint($name)
	{
		return isset($this->hints[$name]) ? $this->hints[$name] : null;
	}


	/**
	*  Returns label value for given language or element name if none defined.
	*/
	public function GetLabel($lang=DEFAULT_LANG)
	{
		return isset($this->labels[$lang]) ? $this->labels[$lang] : $this->name;
	}


	/**
	*  Returns attribute value or empty string if missing.
	*/
	protected function ReadAttr($name, $default='')
	{
		return trim(isset($this->node[$name]) ? (string)$this->node[$name] : $default);
	}


	/**
	*  Imports child nodes in given dictionary.
	 */
	protected function ImportNodes($nodes, $class, &$dictionary, $package="")
	{
		foreach($nodes as $node) {
			$instance = new $class($node, $package);
			if( isset($dictionary[$instance->name]) ) {
				$classname = get_class($this);
				throw new Exception("Duplicate $class name '$instance->name' in $classname '$this->name'.");
			}
			$dictionary[$instance->name] = $instance;
		}
	}
}