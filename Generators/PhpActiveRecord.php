<?php
include_once("Core/IGenerator.php");


/**
*  Generates PHP active record objects.
*/
class PhpActiveReport implements IGenerator
{
	function Run(&$model, $dir)
	{
		foreach($model->manifest as $object) {
			$writer = new QFileWriter("$dir/$object->name.php");
			
			if( $object instanceof Enumeration )
				$content = $this->RenderEnumeration($object, $writer);
				
			$writer->Close();
		}
	}	
	
	private function RenderEnumeration($enum, $writer)
	{
		$writer->Writeln("<?php");
		$writer->Writeln();
		if( $enum->comment ) {
			$writer->Writeln("/**");
			$writer->Writeln("*  $enum->comment");
			$writer->Writeln("*/");
		}
		$writer->Writeln("class $enum->name");
		$writer->Writeln("{");
		$padding = $maxlen = max(array_map('strlen', array_keys($enum->values)));
		foreach($enum->values as $value) {
			$s = sprintf("\tpublic const %s = %s %s", str_pad($value->name,$padding), str_pad("'$value->value';",$padding+4), $value->comment ? "// $value->comment" : '');
			$writer->Writeln($s);
		}
		$writer->Writeln("}");
	}
	
	
}