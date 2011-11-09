<?php
include_once("Core/IPlugin.php");



class DummyPlugin implements IPlugin
{
	public function Run(&$model)
	{
		print("\nKilroy was here...");
	}	
}