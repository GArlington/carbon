<?php
	$Namespace = $viewdata['namespace'];
	$License = $viewdata['license'];
	$PackageObjects = $viewdata['index'];
?>
<!DOCTYPE html>
<html>
<head>
	<title><?=$Namespace?> Data Dictionary</title>
	<link rel="stylesheet" href="css/style.css" type="text/css" />
</head>
<body>
	<h1><?=$Namespace?> Data Dictionary</h1>

	<?foreach($PackageObjects as $packagename => $package):?>

		<h2><a name='<?=$packagename?>'></a><?=$packagename?></span></h2>
		<? if( count($package->entities) ): ?>
			<div class='index'>
			<h4>Entities</h4>
			<? foreach($package->entities as $entity): ?>
				<a class='<?= $entity->abstract ? 'abstract':'' ?>' href='<?=$entity->name?>.html'><?=$entity->name?></a>
			<? endforeach ?>
			<div class='break'></div>
			</div>
		<? endif ?>

		<? if( count($package->enumerations) ): ?>
			<div class='index'>
			<h4>Enumerations</h4>
			<? foreach($package->enumerations as $enumeration): ?>
				<a href='<?=$enumeration->name?>.html'><?=$enumeration->name?></a>
			<? endforeach ?>
			<div class='break'></div>
			</div>
		<? endif ?>

	<? endforeach ?>

	<div class='license'><?=$License?></div>
</body>
</html>
