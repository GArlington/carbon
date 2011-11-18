<?php


/**
*  Loads a php script. The loaded script will have access to passed $data.
*
*  Example:
*	$template = new QTemplate();
*	$data = array();
*       $data[name] = 'Smith';
*       $data[gender] = Gender::Female;
*       print( $template->Load('views/person.php', $data) );
*/
class QTemplate
{
	private $_filename;


	public function __construct($filename)
	{
		$this->filename = $filename;
	}


	public function Load($data)
	{
		@ob_end_clean();
		ob_start();
		include($this->filename);
		$result = ob_get_contents();
		@ob_end_clean();
		return $result;
	}
}