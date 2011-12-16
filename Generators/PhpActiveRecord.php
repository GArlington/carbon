<?php
include_once("Core/IGenerator.php");


/**
*  Generates PHP active record objects.
*/
class PhpActiveRecord implements IGenerator
{
	function Run(&$model, $dir)
	{
		foreach($model->manifest as $object) {
			$writer = new QFileWriter("$dir/$object->name.php");
			$view = new QTemplate("Generators/Templates/PhpActiveRecord/".get_class($object).".php");

			if( $object instanceof Entity )
				$assoc = $this->GetEntityDefaults($object);
			else
				$assoc = $this->GetEnumerationValues($object);
			$data = array(
				'object' => $object,
				'namespace' => $model->namespace,
				'license' => $model->license,
				'assoc' => $assoc
			);
			if( $object instanceof Entity )
				$data['includes'] = $this->GetRequiredFilenames($object);

			$content = $view->Load($data);
			$writer->Write("<?php\n$content");
			$writer->Close();
		}
	}

	/**
	*  Returns entity's required files (for complex types properties).
	*/
	private function GetRequiredFilenames($entity)
	{
		$result = array();
		foreach( $entity->properties as $property )
			if( !in_array($property->type, Model::$BASE_TYPES) && !in_array($property->type, $result) )
				$result[] = $property->type;
		return $result;
	}

	/**
	*  Returns entity's default values indexed by property name.
	*/
	private function GetEntityDefaults($entity)
	{
		$result = array();
		foreach( $entity->properties as $property ) {
			$default = $property->default;
			if( $property->type == 'text' || $property->type == 'time' )
				$default = "'".str_replace("'", "\\'",$default)."'";
			elseif($property->type == 'integer')
				$default = (int)$default;
			elseif($property->type == 'real')
				$default = (double)$default;
			elseif($property->type == 'logical')
				$default = $default=='true' ? 'true' : 'false';
			elseif($property->type == 'binary')
				$default = "''";
			else
				$default = "0";
			$result[$property->name] = $default;
		}
		return $result;
	}

	/**
	*  Returns enumeration values' effective value (not name)
	*/
	private function GetEnumerationValues($enum)
	{
		$result = array();
		foreach( $enum->values as $value )
			$result[$value->name] = $value->value;
		return $result;
	}


}