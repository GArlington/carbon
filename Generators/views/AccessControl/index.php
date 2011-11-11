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
					<?
					$scope = array();
					foreach($hint->params as $param)
						$scope[] = "<span class='hint'>$param</span>";
					?>
					<div style='font-size:larger;margin-left:75px;margin-bottom:10px;'>Scope: <?= count($scope) ? implode(' ', $scope) : 'Global' ?></div>
					<table class='data'>
						<tr><th>Operation</th><th>Comment</th></tr>
					<? foreach($enumeration->values as $value): ?>
						<tr><td><?= $value->name ?></td><td><?= $value->comment ?></td></tr>
					<? endforeach ?>
					</table>
				<? endif ?>
			<? endforeach ?>					
		<? endif ?>
	<? endforeach ?>

	<div class='license'><?= $license ?></div>
</body>
</html>
