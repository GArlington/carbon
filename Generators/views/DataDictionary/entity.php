<?php
	$namespace = $viewdata['namespace'];
	$license   = $viewdata['license'];
	$entity    = $viewdata['object'];

	$entityName = $entity->name;
	$pkgName   = $entity->package->name;
	$pkgLink   = "<a href='index.html#$pkgName'>$pkgName</a>";

	$links = array();
	foreach($entity->hints as $hint)
		$links[] = "<span class='hint'>$hint->signature</span>";
	$hints = count($links) ? implode(' ', $links) : '';

	$links = array();
	foreach($entity->interfaces as $interface)
		$links[] = "<a class='' href='$interface.html'>$interface</a>";
	$interfaces = count($links) ? implode(', ', $links) : '';

	$links = array();
	foreach($entity->refby as $ref)
		if( !$ref->abstract )
			$links[] = "<a href='$ref->name.html'>$ref->name</a>";
	$referencedBy = count($links) ? implode(', ', $links) : '';

	$references = array();
	foreach($entity->properties as $property) {
		$t = $property->typeref;
		if( $t && $t instanceof Entity && !$t->abstract && !in_array($t->name,$references ) )
			$references[] = $t->name;
	}

	// Graph nodes and edges...
	$nodes = array();
	$edges = array();
	$nodes[$entityName] = "{label:'$entityName', color:'d00'}";

	foreach($references as $name)
		if( !isset($nodes[$name]) ) {
			$nodes[$name] = "{label:'$name', color:'#000'}";
			$edges[] = array($entityName, $name);
		}

	foreach($entity->refby as $ref) {
		if( $ref->abstract )
			continue;

		if( !isset($nodes[$ref->name]) )
			$nodes[$ref->name] = "{label:'$ref->name', color:'#888'}";
		$edges[] = array($ref->name, $entityName);
	}

	// calculate canevas size by estimating surface per entity...
	$n = count($nodes);
	$canevasSize = (!$entity->abstract && $n>0) ? (300+sqrt($n*10000)) : 0;
 ?>
<!DOCTYPE html>
<html>
<head>
	<title><?= $pkgName ?>.<?= $entityName ?></title>
	<link rel="stylesheet" href="css/style.css" type="text/css" />
	<script src="lib/jquery-1.6.4.min.js"></script>
	<script src="lib/springy.js"></script>
	<script src="lib/springyui.js"></script>
</head>
<body>
	<? $class=$entity->abstract?" class='abstract'":'' ?>
	<h1<?= $class ?>><?= $pkgLink ?>.<?= $entityName ?></h1>
	<p><?= Highlight($entity->comment) ?></p>

	<?if( $interfaces ): ?>
		<h2>Interfaces</h2>
		<p><?= $interfaces ?></p>
	<?endif ?>

	<?if( $referencedBy ): ?>
		<h2>Referenced by</h2>
		<p><?= $referencedBy ?></p>
	<?endif ?>

	<?if( $hints ): ?>
		<h2>Hints</h2>
		<p><?= $hints ?></p>
	<?endif ?>

	<h2>Properties</h2>
	<table class='data'>
		<tr><th>Name</th><th>Type</th><th>Default</th><th>Constraints</th><th>Hints</th><th>Comment</th></tr>

		<?  foreach($entity->properties as $property):
			// Constraints:
			$constraints = array();
			foreach($property->constraints as $constraint)
				$constraints[] = $constraint->signature;
			$constraints = implode(' ', $constraints);

			// CSS Class:
			$classes = array();
			if( $property->HasConstraint('required') )
				$classes[] = 'required';
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
		<?  endforeach ?>
	</table>

	<?if( count($entity->uniques) ): ?>
	<h2>Unicity constraints</h2>
	<table class='dict'>
		<?  foreach($entity->uniques as $name=>$unique): ?>
		<tr>
			<th><?= $name ?></th>
			<td>(<?= implode(', ',$unique->ref) ?>)</td>
		</tr>
		<?  endforeach ?>
	</table>
	<?endif ?>

	<?if( count($entity->indexes) ): ?>
	<h2>Indexes</h2>
	<table class='dict'>
		<?  foreach($entity->indexes as $name=>$index): ?>
		<tr>
			<th><?= $name ?></th>
			<td>(<?= implode(', ',$index->ref) ?>)</td>
		</tr>
		<?  endforeach ?>
	</table>
	<?endif ?>



	<? if( $canevasSize ): ?>
		<canvas class='relationships' id="model" width="<?= $canevasSize ?>" height="<?= $canevasSize ?>">
		</canvas>
		<script>
			var graph = new Graph();

			<?foreach($nodes as $name=>$attr): ?>
				var <?= $name ?> = graph.newNode(<?= $attr ?>);
			<?  endforeach ?>

			<?foreach($edges as $node): ?>
				graph.newEdge(<?= $node[0] ?>, <?= $node[1] ?>);
			<?  endforeach ?>

			jQuery(function(){
				var springy = jQuery('#model').springy({
					graph: graph
				});
			});
		</script>
	<?  endif ?>


	<div class='license'><?= $license ?></div>
</body>
</html>
