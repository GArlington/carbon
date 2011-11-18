<?php
include_once("Core/IGenerator.php");
include_once("Q/QTemplate.php");
include_once("Q/QFileWriter.php");
include_once("HtmlHelpers.php");


class DataDictionary implements IGenerator
{
	function Run(&$model, $dir)
	{
		@mkdir("$dir/lib");
		@mkdir("$dir/css");

		copy("Generators/Templates/lib/jquery-1.6.4.min.js", "$dir/lib/jquery-1.6.4.min.js");
		copy("Generators/Templates/lib/LICENSE", "$dir/lib/LICENSE");
		copy("Generators/Templates/lib/springy.js", "$dir/lib/springy.js");
		copy("Generators/Templates/lib/springyui.js", "$dir/lib/springyui.js");
		copy("Generators/Templates/css/style.css", "$dir/css/style.css");

		// Generate individual pages...
		foreach($model->manifest as $object) {
			if( ! ($object instanceof Entity || $object instanceof Enumeration) )
				continue;

			$writer = new QFileWriter("$dir/$object->name.html");
			$view = new QTemplate("Generators/Templates/DataDictionary/".strtolower(get_class($object)).".php");
			$data = array(
				'object' => $object,
				'namespace' => $model->namespace,
				'license' => $model->license
			);
			$content = $view->Load($data);

			$writer->Write($content);
			$writer->Close();
		}

		// Generate index page...
		$writer = new QFileWriter("$dir/index.html");
		$view = new QTemplate("Generators/Templates/DataDictionary/index.php");
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