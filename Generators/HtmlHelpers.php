<?php


function Highlight($s)
{
	$s = utf8_decode($s);
	$s = htmlentities($s);
	$s = str_replace("{{", "<span class='highlight'>", $s);
	$s = str_replace("}}", "</span>", $s);
	return $s;
}
