<?
	$enum = $viewdata['object'];
	$namespace = $viewdata['namespace'];
	$license = $viewdata['license'];
	$values = $viewdata['assoc'];
	
	// Find longest property name in entity (for cosmetic purpose)
	$n = $maxlen = max(array_map('strlen', array_keys($enum->values)));
?>
/* <?= "$namespace -- $license\n" ?>
*/


/**
*  Carbon generated enumeration.
*  <?= "$enum->comment\n" ?>
*/
final class <?= "$enum->name\n" ?>
{
<? foreach($enum->values as $value): ?>
	public const <?= str_pad($value->name,$n) ?> = '<?= $values[$value->name] ?>'; // <?= "$value->comment"?> 
<? endforeach ?>
}