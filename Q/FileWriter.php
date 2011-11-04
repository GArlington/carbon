<?php


class FileWriter
{
	protected $f;

	function __construct($name, $mode="w") { $this->f = fopen($name,$mode);	}
	function __destruct()                  { $this->Close(); }
	function Write($s)                     { return fwrite($this->f, $s); }
	function Writeln($s="")                { return fwrite($this->f, $s . "\r\n"); }
	function Close()                       { @fclose($this->f); }
}