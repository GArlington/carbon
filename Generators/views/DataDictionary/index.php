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

	<?foreach($PackageObjects as $packagename => $objects):?>
		<h2><a name='<?=$packagename?>'></a><?=$packagename?></span></h2>
		<div class='index'>
		<?
		foreach($objects as $objectname => $object):
			$class = get_class($object);
			if( $object instanceof Entity && $object->abstract )
				$class.=' abstract';

		?>
			<a class='<?=$class?>' href='<?=$objectname?>.html'><?=$objectname?></a>
		<? endforeach ?>
		<div class='break'></div>
		</div>
	<? endforeach ?>

	<div class='license'><?=$License?></div>
</body>
</html>
