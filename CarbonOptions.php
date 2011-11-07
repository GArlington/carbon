<?php
include_once("Q/Options.php");


/**
*  @syntax   php builder.php [options]
*  @example  php builder.php --m:in -g:xgen2
*/
class BuilderOptions extends Options
{
	/**
	*  Sets model definition directory.
	*  @option   --model -m
	*  @gradient --gradient -g
	*/
	public $ModelDir = "./Model";

	/**
	*  Sets output directory.
	*  @option --out -o
	*/
	public $OutputDir = "./Out";

	/**
	*  Sets generator directory.
	*  @option --gen -g
	*/
	public $GeneratorsDir = "./Generators";

	/**
	*  Sets generator directory.
	*  @option --namespace -n
	*/
	public $Namespace = "Carbon";

	/**
	*  Sets licenses/copyright notice.
	*  @option --license -l
	*/
	public $License = "MIT Licensed -- Copyright (c) Alain Bacon, 2011.";
}