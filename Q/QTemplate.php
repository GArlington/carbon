<?php


/**
*  Loads a php script. The loaded script will have access to passed $viewdata.
*
*  Example:
*	$view = new QTemplate();
*	$viewdata = array();
*       $viewdata[name] = 'Smith';
*       $viewdata[gender] = Gender::Female;
*       print( $view->Load('views/person.php', $viewdata) );
*/
class QTemplate
{
	private $_filename;
	
	
	public function __construct($filename)
	{
		$this->filename = $filename;
	}
	
	
	public function Load($viewdata)
	{
		@ob_end_clean();
		ob_start();
		include($this->filename);
		$result = ob_get_contents();
		@ob_end_clean();
		return $result;
	}
}