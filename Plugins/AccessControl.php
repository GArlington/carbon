<?php
include_once("Core/IPlugin.php");


/**
*  Implements access control tables from enumerations
*  that expose the access() hint.
*/
class AccessControl implements IPlugin
{
	/**
	*  Execute plugin.
	*/
	public function Run(&$model)
	{
		foreach($model->manifest as $object) {
			if( !($object instanceof Enumeration) )
				continue;

			if( ($hint=$object->GetHint("access"))==null )
				continue;

			$fqn = sprintf("%s.%s", $object->package->name, $object->name );
			$this->ValidateHintReferences($fqn, $hint, $model->manifest);

			// Create new entity in object's package...
			$entity = new Entity($object->package);
			$entity->name = "AC_$object->name";

			$entity->AddProperty( Property::Make("Role", "Role") );
			$entity->AddProperty( Property::Make("Operation", $object->name) );

			foreach($hint->params as $param) {
				$p = Property::Make($param, $param);
				$p->SetHint("scope");
				$entity->AddProperty($p);
			}

			$object->package->AddEntity($entity, $model->manifest);
		}
	}

	/**
	*  Ensures that hint targets are valid manifest objects.
	*/
	private function ValidateHintReferences($fqn, $hint, &$manifest)
	{
		foreach($hint->params as $param)
			if( !isset($manifest[$param]) )
				throw new Exception("Invalid hint target '$param' in $fqn");
	}
}