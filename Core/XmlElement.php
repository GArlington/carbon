<?php
include_once("Q/QRegex.php");
include_once("Hint.php");


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
	protected function __construct($package, $node=null)
	{
		$this->package = $package;

		if( $node ) {
			$this->node    = $node;
			$this->name    = $this->ReadAttr("name");
			$this->comment = $this->ReadAttr("comment");

			$classname = get_class($this);

			if( trim($this->name)=='' )
				throw new Exception("Missing $classname name in package $package->name");

			if( strlen($this->name) > MAX_IDENTIFIER_LENGTH )
				print("\nWARNING: $classname identifier '$this->name' too long in '$package->name'" );

			// Read hints...
			foreach( QRegex::SplitWords(';',$this->ReadAttr("hint")) as $signature ) {
				if( trim($signature) )
					$this->SetHint($signature);
			}

			if( $node->label )
				foreach( $node->label as $label ) {
					$lang = isset($label['lang']) ? (string)$label['lang'] : DEFAULT_LANG;
					$label = (string)$label;
					$this->SetLabel($lang, $label);
				}
		}
	}


	/**
	*  Adds a hint signature to the element.
	*/
	public function SetHint($signature)
	{
		$hint = new Hint($signature);
		$this->hints[$hint->name] = $hint;
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
	public function SetLabel($lang, $text)
	{
		$this->labels[trim($lang)] = trim($text);
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
	protected function ImportNodes($package, $nodes, $class, &$dictionary)
	{
		foreach($nodes as $node) {
			$instance = new $class($package, $node);
			if( isset($dictionary[$instance->name]) ) {
				$classname = get_class($this);
				throw new Exception("Duplicate $class name '$instance->name' in '$this->name'.");
			}
			$dictionary[$instance->name] = $instance;
		}
	}
}