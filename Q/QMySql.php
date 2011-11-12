<?php
include_once("QIDatabase.php");


/**
*  Implements MySql database access class.
*/
class QMySql implements QIDatabase
{
	/**
	*  Established connection to a MySql database.
	*  Connection string format:  user:password@host/schema
	*/
	function Connect($connstr)
	{
		
	}
	
	/**
	*  Closes database connection.
	*/
	function Close()
	{
		
	}
	
	/**
	*  Sets autocommit's boolean value.
	*/ 
	function SetAutoCommit($value)
	{
		
	}
	
	/**
	*  Commits current transaction.
	*/
	function Commit()
	{
		
	}	
	
	/**
	*  Rolls back current transaction.
	*/
	function Rollback()
	{
		
	}
	
	/**
	*  Executes given SQL statement and returns resultset.
	*/
	function Query($sql)
	{
		
	}
	
	/**
	*  Executes given SQL statement and returns number of affected rows.
	*/
	function Execute($sql)
	{
		
	}
	
	/**
	*  Returns last generated primary key id.
	function LastID()
	{
	
	}
	
	/**
	*  Returns last database error.
	*/
	function LastError()
	{
		
	}
}