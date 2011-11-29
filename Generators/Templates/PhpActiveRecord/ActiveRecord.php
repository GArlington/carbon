<?
	$Copyright = $data['Copyright'];
?>
/* <?= "$Copyright\n" ?>
*/


/**
*  Active Record base class.
*/
class ActiveRecord
{
	private $_defaults;
	private $_values;

	/**
	*  Constructor.
	*  Import know values from source.
	*  Unknown values are ignored.
	*/
	protected function __construct($source=null)
	{
		if( !$source )
			return;

		if( is_array($source) ) {
			foreach($source as $name=>$value)
				if( isset($this->_defaults[$name]) )
					$this->values[$name] = $value;
		}
		elseif( $source instanceof $this ) {
			foreach($this->_defaults as $name=>$value)
				$this->$name = $source->$name;
		}
		else
			throw new Exception("Incompatible entity type");
	}

	/**
	*  Sets entity default value.
	*  Default values are required for ALL properties.
	*/
	protected function SetDefault($name, $value)
	{
		$this->_defaults[$name] = $value;
	}

	/**
	*  Returns property value or default value if not set.
	*/
	public function __get($name)
	{
		if( isset($_values[$name]) )
			return $_values[$name];
		if( isset($_defaults[$name]) )
			return $_defaults[$name];
		throw new Exception("Unknown property '$name'");
	}

	/**
	*  Returns property value or default value if not set.
	*/
	public function __set($name, $value)
	{
		if( isset($_defaults[$name]) )
			$_values[$name] = $value;
		else
			throw new UnknownPropertyException($name);
	}

	public function Save()
	{
	}


	/**
	*  Returns entity matching given ID.
	*/
	public static function Get($id)
	{
		$table = get_class($this);
		$id = mysqli_escape_string($id);
		$sql = "SELECT * FROM $table WHERE ID='$id'";
	}

	/**
	*  Returns first entity matching criteria.
	*/
	public static function GetFirst($criteria)
	{
	}

	/**
	*  Returns every entities matching given criteria.
	*/
	public static function GetAll($criteria, $limit=0)
	{
	}

	/**
	*  Deletes entity matching given ID.
	*/
	public static function Delete($id)
	{
	}

	/**
	*  Deletes every entity matching given criteria.
	*/
	public static function DeleteAll($criteria)
	{
	}
}