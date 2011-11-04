<?php


function Highlight($s) 
{
	$s = str_replace("{{", "<span class='highlight'>", $s);
	$s = str_replace("}}", "</span>", $s);
	return $s;
}
