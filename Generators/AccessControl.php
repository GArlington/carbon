<?php
include_once("Core/IGenerator.php");
include_once("Q/View.php");
include_once("Q/FileWriter.php");
include_once("HtmlHelpers.php");


class AccessControl implements IGenerator
{
	function Run(&$model, $dir)
	{
		@mkdir("$dir");
		@mkdir("$dir/css");

		copy("Generators/views/css/style.css", "$dir/css/style.css");

		$writer = new FileWriter("$dir/index.html");
		$view = new View("Generators/views/AccessControl/index.php");
		$viewdata = array(
			'index' => $model->packages,
			'namespace' => $model->namespace,
			'license' => $model->license
		);
		$content = $view->Load($viewdata);
		$writer->Write($content);
		$writer->Close();
	}
}