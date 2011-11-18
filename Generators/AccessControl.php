<?php
include_once("Core/IGenerator.php");
include_once("Q/QTemplate.php");
include_once("Q/QFileWriter.php");
include_once("HtmlHelpers.php");


class AccessControl implements IGenerator
{
	function Run(&$model, $dir)
	{
		@mkdir("$dir/css");

		copy("Generators/Templates/css/style.css", "$dir/css/style.css");

		$writer = new QFileWriter("$dir/index.html");
		$view = new QTemplate("Generators/Templates/AccessControl/index.php");
		$data = array(
			'index' => $model->packages,
			'namespace' => $model->namespace,
			'license' => $model->license
		);
		$content = $view->Load($data);
		$writer->Write($content);
		$writer->Close();
	}
}