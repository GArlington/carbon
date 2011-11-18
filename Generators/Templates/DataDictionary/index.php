<?php
	$namespace = $data['namespace'];
	$license   = $data['license'];
	$packages  = $data['index'];
 ?>
<!DOCTYPE html>
<html>
<head>
	<title><?= $namespace ?> Data Dictionary</title>
	<link rel="stylesheet" href="css/style.css" type="text/css" />
</head>
<body>
	<h1><?= $namespace ?> Data Dictionary</h1>

	<? foreach($packages as $pkgname => $package): ?>

		<h2><a name='<?= $pkgname ?>'></a><?= $pkgname ?></span></h2>
		<? if( count($package->entities) ): ?>
			<div class='index'>
			<h4>Entities</h4>
			<? foreach($package->entities as $entity): ?>
				<a class='<?= $entity->abstract ? 'abstract':'' ?>' href='<?= $entity->name ?>.html'><?= $entity->name ?></a>
			<? endforeach ?>
			<div class='break'></div>
			</div>
		<? endif ?>

		<? if( count($package->enumerations) ): ?>
			<div class='index'>
			<h4>Enumerations</h4>
			<? foreach($package->enumerations as $enumeration): ?>
				<a href='<?= $enumeration->name ?>.html'><?= $enumeration->name ?></a>
			<? endforeach ?>
			<div class='break'></div>
			</div>
		<? endif ?>

	<? endforeach ?>

	<div class='license'><?= $license ?></div>
</body>
</html>
