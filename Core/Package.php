<?php
include_once("XmlElement.php");
include_once("Entity.php");
include_once("Enumeration.php");


/**
*  Implements a model package.
*  Packages are used to logicaly group entities (and enumerations).
*/
class Package extends XmlElement
{
	public $entities     = array();
	public $enumerations = array();

	/**
	*  Constructor.
	*/
	public function __construct($node, &$manifest)
	{
		parent::__construct(null, $node);

		$this->ImportNodes($this , $node->entity, "Entity", $this->entities);
		foreach( $this->entities as $entity ) {
			if( isset($manifest[$entity->name]) )
				throw new Exception("$entity->name already implemented in ".($manifest[$entity->name]->package->name));
			$manifest[$entity->name] = $entity;
		}

		$this->ImportNodes($this, $node->enumeration, "Enumeration", $this->enumerations);
		foreach( $this->enumerations as $enumeration ) {
			if( isset($manifest[$enumeration->name]) )
				throw new Exception( "$enumeration->name already implemented in ".($manifest[$enumeration->name]->package->name) );
			$manifest[$enumeration->name] = $enumeration;
		}
	}

	/**
	*  Adds entity to package.
	*/
	public function AddEntity($entity, &$manifest)
	{
		if( isset($manifest[$entity->name]) )
			throw new Exception("$entity->name already implemented in ".($manifest[$entity->name]->package->name));
		$manifest[$entity->name] = $entity;
		$this->entities[] = $entity;
	}

	/**
	*  Adds enumeration to package.
	*/
	public function AddEnumeration($enumeration, &$manifest)
	{
		if( isset($manifest[$enumeration->name]) )
			throw new Exception("$enumeration->name already implemented in ".($manifest[$enumeration->name]->package->name));
		$manifest[$enumeration->name] = $enumeration;
		$this->enumerations[] = $enumeration;
	}
}