<?php


class Regex
{
	/**
	*  Split given value delimited string ignoring surounding white spaces.
	*  Defaults to comma delimited values (csv) if no delimiter is specified.
	*  Returns an empty array if the string is empty.
	*/
        public static function SplitWords($delim, $s)
	{
		return $s=='' ? array() : preg_split("/\s*($delim)\s*/", $s);
	}

	/**
	*  Splits string in the form func(arg,arg,...).
	*  The first value in returned array is the 'func' part, and the remaining
	*  elements are the parameters.
	*/
        public static function SplitFunc($signature)
	{
		return preg_split("/[\s,\(\)]+/", $signature, -1, PREG_SPLIT_NO_EMPTY);
	}
}