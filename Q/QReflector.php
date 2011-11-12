<?php


class QReflector
{
	/**
	*  Returns list of all classes implementing given interface.
	*/
	public static function GetImplementorsOf($interface)
	{
		$result = array();
		foreach(get_declared_classes() as $class) {
			$rc = new ReflectionClass($class);
			if( $rc->implementsInterface($interface) )
				$result[] = $class;
		}
		return $result;
	}
}