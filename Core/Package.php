<?php
include_once("XmlElement.php");
include_once("Entity.php");
include_once("Enum.php");


/**
*  Implements a model package.
*  Packages are used to logicaly group entities (and enumerations).
*/
class Package extends XmlElement
{
	/**
	*  Constructor.
	*/
	function __construct($node, &$objDictionary)
	{
		parent::__construct($node);

		$this->ImportNodes($node->entity, "Entity", $objDictionary, $this );
		$this->ImportNodes($node->enum, "Enum", $objDictionary, $this );
	}
}