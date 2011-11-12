<?php
include_once("QAttrReader.php");


class QOptions
{
	/**
	*  Parses givens arguments initializing descendant's option
	*  values and returns remaining arguments (non-options).
	*  Options start with one or more dash and are of the form:
	*
	*  --option:value or -o:value or -o:"value with spaces"
	*
	*  If $autohelp is true and --help of -h is found in the
	*  received arguments then available options and their
	*  respective descriptions will be dumped to the console
	*  and execution halted.
	*/
	public function Parse($args, $autohelp=true)
	{
		$result=array();

		// Remove script name (1st arg)...
		array_shift($args);

		foreach($args as $arg) {
			if( $arg=="--help" || $arg=="-h" )
				$this->PrintHelp();
			else if($arg[0]=='-')
				$this->SetOptionValue($arg);
			else
				$result[] = $arg;
		}
		return $result;
	}

	/**
	*  Prints the help and ends execution.
	*/
	public function PrintHelp()
	{
		$rc = new ReflectionClass($this);
		$attr = new QAttrReader($rc);

		if($syntax = $attr->GetAttrib('syntax'))
			print("\nsyntax:\n\t$syntax\n");

		if($example = $attr->GetAttrib('example'))
			print("\nexample:\n\t$example\n");

		print("\nOptions:");

		foreach( $rc->getProperties() as $rp) {
			$attr = new QAttrReader($rp);
			$opt = $attr->GetAttrib('option');
			$descr = $attr->ToString();
			$value = $this->{$rp->getName()};
			print("\n\t$opt  [$value]");
			print("\n\t$descr\n");
		}
		die();
	}

	/**
	*  Dumps descendant's option to console.
	*/
	public function Dump()
	{
		$rc = new ReflectionClass($this);
		$attr = new QAttrReader($rc);

		foreach( $rc->getProperties() as $rp) {
			$attr = new QAttrReader($rp);
			if( $opt = $attr->GetAttrib('option') ) {
				$name = $rp->getName();
				$value = $this->{$rp->getName()};
				print("\n$name:\t[$value]");
			}
		}
		print("\n");
	}

	/**
	*  Sets value of option that matches
	*/
	private function SetOptionValue($arg)
	{
		list($optname, $value) = !strpos($arg,':')  ? array($arg,NULL) : explode(':',$arg);

		$rc = new ReflectionClass($this);
		foreach( $rc->getProperties() as $rp) {
			$attr = new QAttrReader($rp);
			$aliases = explode(' ',$attr->GetAttrib('option'));
			if( in_array($optname, $aliases) )
				return $this->{$rp->getName()} = ($value === NULL ? true : $value);
		}
		die("\nUnknown option: $optname");
	}
}
