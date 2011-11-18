<?
	$enum = $data['object'];
	$namespace = $data['namespace'];
	$license = $data['license'];
	$values = $data['assoc'];

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
	public const <?= str_pad($value->name,$n) ?> = '<?= $values[$value->name] ?>'; <?= ($value->comment ?  "// $value->comment" : '') . "\r\n" ?>
<? endforeach ?>
}