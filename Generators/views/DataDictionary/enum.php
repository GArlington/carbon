<?php
	$Namespace = $viewdata['namespace'];
	$License = $viewdata['license'];
	$Enum = $viewdata['object'];
	$EnumName  = $Enum->name;
	$PackageName = $Enum->package->name;
	$PackageLink = "<a href='index.html#$PackageName'>$PackageName</a>";
	$links = array();
	foreach($Enum->hints as $hint)
		$links[] = "<span class='hint'>$hint->signature</span>";
	$Hints = count($links) ? implode(' ', $links) : '';
?>
<!DOCTYPE html>
<html>
<head>
	<title><?=$PackageName?>.<?=$EnumName?></title>
	<link rel="stylesheet" href="css/style.css" type="text/css" />
</head>
<body>
	<h1><?=$PackageLink?>.<?=$EnumName?></h1>
	<p><?= Highlight($Enum->comment) ?></p>

	<?if( $Hints ): ?>
		<h2>Hints</h2>
		<p><?= $Hints?></p>
	<?endif?>

	<h2>Values</h2>
	<table class='data'>
		<tr><th>Name</th><th>Value</th><th>Hint</th><th>Comment</th></tr>
		<? foreach($Enum->members as $member):
			$valHints = array();
			foreach($member->hints as $hint)
				$valHints[] = "<span class='hint'>$hint->signature</span>";
			$valHints = count($valHints) ? implode(' ', $valHints) : '';
		?>
			<tr>
				<td><?= $member->name ?><sup></td>
				<td><?= $member->value ?></td>
				<td><?= $valHints ?></td>
				<td><?= Highlight($member->comment) ?></td>
			</tr>
		<? endforeach ?>
	</table>

	<div class='license'><?=$License?></div>
</body>
</html>
