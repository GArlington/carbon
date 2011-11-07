<?php
include_once("Core/IGenerator.php");
include_once("Q/View.php");
include_once("Q/FileWriter.php");
include_once("HtmlHelpers.php");


class DataDictionary implements IGenerator
{
	function Run($model, $dir)
	{
		@mkdir("$dir");
		@mkdir("$dir/lib");
		@mkdir("$dir/css");

		copy("Generators/views/lib/jquery-1.6.4.min.js", "$dir/lib/jquery-1.6.4.min.js");
		copy("Generators/views/lib/LICENSE", "$dir/lib/LICENSE");
		copy("Generators/views/lib/springy.js", "$dir/lib/springy.js");
		copy("Generators/views/lib/springyui.js", "$dir/lib/springyui.js");
		copy("Generators/views/css/style.css", "$dir/css/style.css");

		// Generate individual pages...
		foreach($model->objDictionary as $object) {
			if( ! ($object instanceof Entity || $object instanceof Enumeration) )
				continue;

			$writer = new FileWriter("$dir/$object->name.html");
			$view = new View("Generators/views/DataDictionary/".strtolower(get_class($object)).".php");
			$viewdata = array(
				'object' => $object,
				'namespace' => $model->namespace,
				'license' => $model->license
			);
			$content = $view->Load($viewdata);

			$writer->Write($content);
			$writer->Close();
		}

		// Generate index page...
		$writer = new FileWriter("$dir/index.html");
		$view = new View("Generators/views/DataDictionary/index.php");
		$viewdata = array(
			'index' => $model->pkgDictionary,
			'namespace' => $model->namespace,
			'license' => $model->license
		);
		$content = $view->Load($viewdata);
		$writer->Write($content);
		$writer->Close();
	}
}