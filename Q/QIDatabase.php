<?php


/**
*  Q database interface.
*/
interface QIDatabase
{
	function Connect($connstr);
	function Close();
	
	function SetAutoCommit($value);
	function Commit();	
	function Rollback();
	
	function Query($sql);
	function Execute($sql);
	
	function LastID();
	function LastError();
}