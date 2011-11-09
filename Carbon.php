<?php
include_once("Q/DirectoryIO.php");
include_once("Q/Introspector.php");
include_once("Core/Model.php");
include_once("CarbonOptions.php");

/*----------------------------------------------------------------------------
	PRINT BANNER AND PARSE COMMAND LINE OPTIONS
----------------------------------------------------------------------------*/

print("\nCarbon Builder 1.0\n");
$opt = new BuilderOptions();
$opt->Parse($argv);

/*----------------------------------------------------------------------------
	STARTUP STATISTICS RECORDING
----------------------------------------------------------------------------*/

$tmstart = time();

/*----------------------------------------------------------------------------
	LOAD MODEL
----------------------------------------------------------------------------*/

try {		
	$plugins = array();
	
	// Load dynamic model extension plugins:
	if( $opt->PluginsDir ) {
		foreach(DirectoryIO::GetFiles($opt->PluginsDir,"*.php") as $f)
			include_once($f);
			
		print("\n\nLoading model plugins...");		
		foreach(Introspector::GetImplementorsOf("IPlugin") as $plugin) {
			print("\n\t$plugin");
			$plugins[$plugin] = new $plugin();
		}	
	}
		
	// Load model...
	$model = new Model($opt->Namespace, $opt->License);
	$model->Load($opt->ModelDir, $plugins);

	// Create main output directory:
	@mkdir($opt->OutputDir);

	// Load generators:
	foreach(DirectoryIO::GetFiles($opt->GeneratorsDir,"*.php") as $f)
		include_once($f);

	// Run generators:
	print("\n\nRunning generators...");
	foreach(Introspector::GetImplementorsOf("IGenerator") as $generator) {
		$dir = "$opt->OutputDir/$generator";
		@mkdir($dir);
		print("\n\t$generator");
		$instance = new $generator();
		$instance->Run($model, $dir);
	}
}
catch( Exception $ex ) {
	die("\n\nERROR: ".$ex->getMessage()."\n");
}

/*----------------------------------------------------------------------------
	DISPLAY STATISTICS
----------------------------------------------------------------------------*/

$elapsed = time() - $tmstart;
$nobj = count($model->manifest);
$npkg = count($model->packages);
print("\n");
print("\nBuild finished in $elapsed seconds");
print("\nModel contains $nobj objects in $npkg packages.");
print("\n");
