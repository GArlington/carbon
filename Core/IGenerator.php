<?php


/**
*  Interface used by all Carbon generators.
*/
interface IGenerator
{
	function Run(&$model, $dir);
}