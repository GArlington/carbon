<?php


/**
*  Reads reflector object's comments and extracts text
*  and attributes starting with '@', à la Javadoc.
*/
class QAttrReader
{
	private $_textlines = array();
	private $_attributes = array();

	/**
	*  Constructor
	*/
	public function __construct($reflector)
	{
		$comment_lines = trim(substr($reflector->getDocComment(),3,-2));

		// Process each comment line...
		foreach(explode("\n",$comment_lines) as $line) {
			$line = preg_replace('/^\**\s*/','',trim($line)); // Remove decorating asterisks and spaces...

			// Is it an attribute assignment?
			if( $line[0]=='@' ){
				list($name, $value) = explode(' ',$line,2);
				$this->_attributes[substr($name,1)] = trim($value);
			}
			else
				$this->_textlines[] = $line;
		}
	}

	/**
	*  Returns attribute value or "" if undefined.
	*/
	public function GetAttrib($name)
	{
		return isset($this->_attributes) ? $this->_attributes[$name] : "";
	}

	/**
	*  Returns text lines array (without attribute assignements).
	*/
	public function ToArray()
	{
		return $this->_textlines;
	}

	/**
	*  Returns text lines string (without attribute assignements).
	*/
	public function ToString()
	{
		return implode('\n',$this->_textlines);
	}
}