<?php
/*----------------------------------------------------------------------------
	STARTUP STATISTICS RECORDING
----------------------------------------------------------------------------*/

$tmstart = time();

/*----------------------------------------------------------------------------
	INSTALL OUR OWN ERROR HANDLER FOR CLEANER OUTPUT
----------------------------------------------------------------------------*/

set_error_handler("ErrorHandler");

/*----------------------------------------------------------------------------
	INCLUDE SUPPORT FILES
----------------------------------------------------------------------------*/

include_once("Q/QDirectory.php");
include_once("Q/QReflector.php");
include_once("Core/Model.php");
include_once("CarbonOptions.php");

/*----------------------------------------------------------------------------
	PRINT BANNER AND PARSE COMMAND LINE OPTIONS
----------------------------------------------------------------------------*/

print("\nCarbon Builder 1.0");
$opt = new BuilderOptions();
$opt->Parse($argv);

/*----------------------------------------------------------------------------
	RUN
----------------------------------------------------------------------------*/

try {
	$plugins = array();

	// Load dynamic model extension plugins:
	if( $opt->PluginsDir ) {
		foreach(QDirectory::GetFiles($opt->PluginsDir,"*.php") as $f)
			include_once($f);

		foreach(QReflector::GetImplementorsOf("IPlugin") as $plugin) {
			$plugins[$plugin] = new $plugin();
		}
	}

	// Load model...
	print("\n\nLoading model:");
	$model = new Model($opt->Namespace, $opt->License);	
	$model->Load($opt->ModelDir, $plugins);

	// Create main output directory:
	@mkdir($opt->OutputDir);

	// Load generators:
	foreach(QDirectory::GetFiles($opt->GeneratorsDir,"*.php") as $f)
		include_once($f);

	// Run generators:
	print("\n\nRunning generators:");
	foreach(QReflector::GetImplementorsOf("IGenerator") as $generator) {
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

/*----------------------------------------------------------------------------
	CUSTOM ERROR HANDLER
----------------------------------------------------------------------------*/
function ErrorHandler($errno, $errstr, $errfile, $errline)
{
	// if @ operator was prepended we ignore the error...
	if( error_reporting() == 0 )
		return true;

	switch ($errno) {
		case E_NOTICE:
		case E_USER_NOTICE:
			$errors = "Notice";
			break;
		case E_WARNING:
		case E_USER_WARNING:
			$errors = "Warning";
			break;
		case E_ERROR:
		case E_USER_ERROR:
			$errors = "Fatal Error";
			break;
		default:
			$errors = "Unknown";
			break;
	}
	die("\n\n$errors: $errstr\nfile: $errfile\nline: $errline\n");
}