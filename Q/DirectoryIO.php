<?php


class DirectoryIO
{
	/**
	*  Returns array of files in given directory.
	*/
	public static function GetFiles($path, $mask, $recurse=false)
	{
		if( !($dir=opendir($path)) )
			return null;

		$result = array();
		while( ($file = readdir($dir)) !== false ) {
			if( substr($file,0,1)=='.' )
				continue;

			if( $recurse && is_dir("$path/$file")  ) {
				$subresult = self::GetFiles("$path/$file", $mask, $recurse);
				$result = array_merge($result, $subresult);
			}
			elseif( !is_dir("$path/$file") && self::FileMatch($mask, $file) )
				$result[] = "$path/$file";
		}
		closedir($dir);
		return $result;
	}


	/**
	*  Returns true if given file name matches file mask.
	*/
	private static function FileMatch($pattern, $file)
	{
		$pattern = str_replace('.','\.',$pattern);
		$pattern = str_replace('?','.?',$pattern);
		$pattern = str_replace('*','.*',$pattern);
		return preg_match("/^$pattern/", $file);
	}
}