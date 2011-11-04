<?php
	$Namespace = $viewdata['namespace'];
	$License = $viewdata['license'];
	$Entity = $viewdata['object'];

	$EntityName  = $Entity->name;
	$PackageName = $Entity->package->name;
	$PackageLink = "<a href='index.html#$PackageName'>$PackageName</a>";

	$links = array();
	foreach($Entity->hints as $hint)
		$links[] = "<span class='hint'>$hint->signature</span>";
	$Hints = count($links) ? implode(' ', $links) : '';

	$links = array();
	foreach($Entity->interfaces as $interface)
		$links[] = "<a class='' href='$interface.html'>$interface</a>";
	$Interfaces = count($links) ? implode(', ', $links) : '';

	$links = array();
	foreach($Entity->refby as $ref)
		if( !$ref->abstract )
			$links[] = "<a href='$ref->name.html'>$ref->name</a>";
	$ReferencedBy = count($links) ? implode(', ', $links) : '';

	$References = array();
	foreach($Entity->properties as $property) {
		$t = $property->typeref;
		if( $t && $t instanceof Entity && !$t->abstract && !in_array($t->name,$References ) )
			$References[] = $t->name;
	}

	// Graph nodes and edges...
	$Nodes = array();
	$Edges = array();
	$Nodes[$EntityName] = "{label:'$EntityName', color:'d00'}";

	foreach($References as $name)
		if( !isset($Nodes[$name]) ) {
			$Nodes[$name] = "{label:'$name', color:'#000'}";
			$Edges[] = array($EntityName, $name);
		}

	foreach($Entity->refby as $ref) {
		if( $ref->abstract )
			continue;

		if( !isset($Nodes[$ref->name]) )
			$Nodes[$ref->name] = "{label:'$ref->name', color:'#888'}";
		$Edges[] = array($ref->name, $EntityName);
	}
 ?>
<!DOCTYPE html>
<html>
<head>
	<title><?= $PackageName?>.<?= $EntityName?></title>
	<link rel="stylesheet" href="css/style.css" type="text/css" />
	<script src="lib/jquery-1.6.4.min.js"></script>
	<script src="lib/springy.js"></script>
	<script src="lib/springyui.js"></script>
</head>
<body>
	<? $class=$Entity->abstract?" class='abstract'":'' ?>
	<h1<?=$class?>><?= $PackageLink?>.<?= $EntityName?></h1>
	<p><?=Highlight($Entity->comment) ?></p>

	<?if( $Interfaces ): ?>
		<h2>Interfaces</h2>
		<p><?= $Interfaces?></p>
	<?endif?>

	<?if( $ReferencedBy ): ?>
		<h2>Referenced by</h2>
		<p><?= $ReferencedBy?></p>
	<?endif?>

	<?if( $Hints ): ?>
		<h2>Hints</h2>
		<p><?= $Hints?></p>
	<?endif?>

	<h2>Properties</h2>
	<table class='data'>
		<tr><th>Name</th><th>Type</th><th>Default</th><th>Constraints</th><th>Hints</th><th>Comment</th></tr>

		<? foreach($Entity->properties as $property):
			// Constraints:
			$constraints = array();
			foreach($property->constraints as $constraint)
				$constraints[] = $constraint->signature;
			$constraints = implode(' ', $constraints);

			// CSS Class:
			$classes = array();
			if( $property->HasConstraint('required') )
				$classes[] = 'required';
			if( $property->interface && $property->interface!=$Entity->name )
				$classes[] = 'abstract';
			$type = $property->typeref ? "<a href='$property->type.html'>$property->type</a>" : $property->rawtype;

			$propHints = array();
			foreach($property->hints as $hint)
				$propHints[] = "<span class='hint'>$hint->signature</span>";
			$propHints = count($propHints) ? implode(' ', $propHints) : '';
		 ?>
			<tr>
				<td class='<?= implode(' ',$classes) ?>'><?= $property->name ?></td>
				<td><?= $type ?></td>
				<td><?= $property->default ?></td>
				<td><?= $constraints ?></td>
				<td><?= $propHints ?></td>
				<td><?= Highlight($property->comment) ?></td>
			</tr>
		<? endforeach ?>
	</table>

	<?if( count($Entity->uniques) ): ?>
	<h2>Unicity constraints</h2>
	<table class='dict'>
		<? foreach($Entity->uniques as $name=>$unique): ?>
		<tr>
			<th><?= $name?></th>
			<td>(<?=implode(', ',$unique->ref) ?>)</td>
		</tr>
		<? endforeach ?>
	</table>
	<?endif?>

	<?if( count($Entity->indexes) ): ?>
	<h2>Indexes</h2>
	<table class='dict'>
		<? foreach($Entity->indexes as $name=>$index): ?>
		<tr>
			<th><?= $name?></th>
			<td>(<?=implode(', ',$index->ref) ?>)</td>
		</tr>
		<? endforeach ?>
	</table>
	<?endif?>



	<?
		$n = count($Nodes);
		// calculate canevas size by allocating a certain amount pixel real estate per entity...
		if( !$Entity->abstract && $n>0 ):
			$w = $h = 200 + sqrt($n * 3000);
	?>
		<canvas class='relationships' id="model" width="<?=$w?>" height="<?=$h?>">
		</canvas>

		<script>
			var graph = new Graph();

			<?foreach($Nodes as $name=>$attr): ?>
				var <?= $name?> = graph.newNode(<?= $attr?>);
			<? endforeach ?>

			<?foreach($Edges as $node): ?>
				graph.newEdge(<?= $node[0] ?>, <?= $node[1] ?>);
			<? endforeach ?>

			jQuery(function(){
				var springy = jQuery('#model').springy({
					graph: graph
				});
			});
		</script>
	<? endif ?>


	<div class='license'><?= $License?></div>
</body>
</html>
