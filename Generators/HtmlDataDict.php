<?php
include_once("Core/IGenerator.php");
include_once("Q/QTemplate.php");
include_once("Q/QFileWriter.php");
include_once("HtmlGenerator.php");


class HtmlDataDict extends HtmlGenerator implements IGenerator
{
	function Run(&$model, $dir)
	{
		@mkdir("$dir/lib");
		@mkdir("$dir/css");

		copy("Generators/templates/lib/jquery-1.6.4.min.js", "$dir/lib/jquery-1.6.4.min.js");
		copy("Generators/templates/lib/LICENSE", "$dir/lib/LICENSE");
		copy("Generators/templates/lib/springy.js", "$dir/lib/springy.js");
		copy("Generators/templates/lib/springyui.js", "$dir/lib/springyui.js");
		copy("Generators/templates/css/style.css", "$dir/css/style.css");

		// Generate individual pages...
		foreach($model->manifest as $object) {
			if( ! ($object instanceof Entity || $object instanceof Enumeration) )
				continue;

			$writer = new QFileWriter("$dir/$object->name.html");
			$template = new QTemplate("Generators/templates/DataDictionary/".strtolower(get_class($object)).".php");
			$data = array(
				'object' => $object,
				'namespace' => $model->namespace,
				'license' => $model->license,
				'helper' => $this
			);
			$content = $template->Load($data);

			$writer->Write($content);
			$writer->Close();
		}

		// Generate index page...
		$writer = new QFileWriter("$dir/index.html");
		$template = new QTemplate("Generators/templates/DataDictionary/index.php");
		$data = array(
			'index' => $model->packages,
			'namespace' => $model->namespace,
			'license' => $model->license
		);
		$content = $template->Load($data);
		$writer->Write($content);
		$writer->Close();
	}
}