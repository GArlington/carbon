<?php
	$Namespace = $viewdata['namespace'];
	$License = $viewdata['license'];
	$Enumeration = $viewdata['object'];
	$EnumerationName  = $Enumeration->name;
	$PackageName = $Enumeration->package->name;
	$PackageLink = "<a href='index.html#$PackageName'>$PackageName</a>";
	$links = array();
	foreach($Enumeration->hints as $hint)
		$links[] = "<span class='hint'>$hint->signature</span>";
	$Hints = count($links) ? implode(' ', $links) : '';
?>
<!DOCTYPE html>
<html>
<head>
	<title><?=$PackageName?>.<?=$EnumerationName?></title>
	<link rel="stylesheet" href="css/style.css" type="text/css" />
</head>
<body>
	<h1><?=$PackageLink?>.<?=$EnumerationName?></h1>
	<p><?= Highlight($Enumeration->comment) ?></p>

	<?if( $Hints ): ?>
		<h2>Hints</h2>
		<p><?= $Hints?></p>
	<?endif?>

	<h2>Values</h2>
	<table class='data'>
		<tr><th>Name</th><th>Value</th><th>Hint</th><th>Comment</th></tr>
		<? foreach($Enumeration->values as $value):
			$valHints = array();
			foreach($value->hints as $hint)
				$valHints[] = "<span class='hint'>$hint->signature</span>";
			$valHints = count($valHints) ? implode(' ', $valHints) : '';
		?>
			<tr>
				<td><?= $value->name ?><sup></td>
				<td><?= $value->value ?></td>
				<td><?= $valHints ?></td>
				<td><?= Highlight($value->comment) ?></td>
			</tr>
		<? endforeach ?>
	</table>

	<div class='license'><?=$License?></div>
</body>
</html>
