<?php
	$namespace        = $data['namespace'];
	$license          = $data['license'];
	$enumeration      = $data['object'];
	$helper           = $data['helper'];
	$enumerationName  = $enumeration->name;
	$pkgName          = $enumeration->package->name;
	$pkgLink          = "<a href='index.html#$pkgName'>$pkgName</a>";

	$links = array();
	foreach($enumeration->interfaces as $interface)
		$links[] = "<a class='' href='$interface.html'>$interface</a>";
	$interfaces = count($links) ? implode(', ', $links) : '';

	$links = array();
	foreach($enumeration->hints as $hint)
		$links[] = "<span class='hint'>$hint->signature</span>";
	$hints = count($links) ? implode(' ', $links) : '';
 ?>
<!DOCTYPE html>
<html>
<head>
	<title><?= $pkgName ?>.<?= $enumerationName ?></title>
	<link rel="stylesheet" href="css/style.css" type="text/css" />
</head>
<body>
	<h1><?= $pkgLink ?>.<?= $enumerationName ?></h1>
	<p><?= $helper->Highlight($enumeration->comment) ?></p>

	<? if( $interfaces ): ?>
		<h2>Interfaces</h2>
		<p><?= $interfaces ?></p>
	<? endif ?>

	<? if( $hints ): ?>
		<h2>Hints</h2>
		<p><?= $hints ?></p>
	<? endif ?>

	<h2>Values</h2>
	<table class='data'>
		<tr><th>Name</th><th>Value</th><th>Hint</th><th>Comment</th></tr>
		<? foreach($enumeration->values as $value):
			$valHints = array();
			foreach($value->hints as $hint)
				$valHints[] = "<span class='hint'>$hint->signature</span>";
			$valHints = count($valHints) ? implode(' ', $valHints) : '';
			$xcomment = "";

			if( $value->origin && $value->origin->name!=$enumeration->name ) {
				$oname = $value->origin->name;
				$opkg = $value->origin->package->name;
				$xcomment = " <span class='autonote'>$oname</span>";
			}
		 ?>
			<tr>
				<td><?= $value->name ?><sup></td>
				<td><?= $value->value ?></td>
				<td><?= $valHints ?></td>
				<td><?= $helper->Highlight($value->comment).$xcomment ?></td>
			</tr>
		<? endforeach ?>
	</table>

	<div class='license'><?= $license ?></div>
</body>
</html>
