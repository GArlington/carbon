<?
	$entity = $viewdata['object'];
	$namespace = $viewdata['namespace'];
	$license = $viewdata['license'];
	$defaults = $viewdata['assoc'];
	$includes = $viewdata['includes'];
	
	// Find longest property name in entity (for cosmetic purpose)
	$n = $maxlen = max(array_map('strlen', array_keys($entity->properties)));
?>
/* <?= "$namespace -- $license\n" ?>
*/
include_once('ActiveRecord.php');
<? foreach($includes as $include): ?>
include_once('<?= $include ?>.php');
<? endforeach ?>

/**
*  Carbon generated entity.
*  <?= "$entity->comment\n" ?>
*/
class <?= $entity->name ?> extends ActiveRecord
{
<? foreach($entity->properties as $property): ?>
	const <?= str_pad($property->name,$n) ?> = <?= str_pad("'$property->name';",$n+3) ?> // <?= $property->rawtype ?> -- <?= "$property->comment\n" ?>
<? endforeach ?>
	
	/**
	*  Constructor.
	*/
	public function __construct($source)
	{
<? foreach($entity->properties as $property): ?>
		$this->SetDefault(self::<?= $property->name ?>, <?= $defaults[$property->name] ?> );
<? endforeach ?>

		parent::__construct($source);
	}
}