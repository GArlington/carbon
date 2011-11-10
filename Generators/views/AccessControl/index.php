<?php
	$namespace = $viewdata['namespace'];
	$license   = $viewdata['license'];
	$packages  = $viewdata['index'];
 ?>
<!DOCTYPE html>
<html>
<head>
	<title><?= $namespace ?> Access Controllers</title>
	<link rel="stylesheet" href="css/style.css" type="text/css" />
</head>
<body>
	<h1><?= $namespace ?> Access Controllers</h1>

	<? foreach($packages as $pkgname => $package): ?>
		<? if( count($package->enumerations) ): ?>
			<? foreach($package->enumerations as $enumeration): ?>
				<? if( $hint=$enumeration->GetHint("access") ): ?>
					<h2><?= $pkgname ?>.<?= $enumeration->name ?></h2>
					<div class='index'>
					<h3>Scope: <?= count($hint->params) ? implode(', ', $hint->params) : 'Global' ?></h3>
					<ul>
					<? foreach($enumeration->values as $value): ?>
						<li><?= $value->name ?></li>
					<? endforeach ?>
					</ul>
					</div>
				<? endif ?>
			<? endforeach ?>					
		<? endif ?>
	<? endforeach ?>

	<div class='license'><?= $license ?></div>
</body>
</html>
