<?php
/*
 * Wildcard Helper Classes
 * StorableObject Class Structure
 */

/*
 * standard interface for database storage/retrieval
 *
 * load()
 *
 * load the object from the DB and validate it (hinges on a stored ID)
 *
 * @param - $data - (mixed) either the integer database row ID/Object ID or an associative array of the database row
 *
 * save()
 *
 * this method simply saves the data stored in the object to the database (if it is valid)
 *
 * remove()
 *
 * delete the table row for the object
 */
interface StorableObjectInterface
{
	public function load($data);
	public function save();
	public function remove($no_cleanup = false);
}

/*
 * standard object for db storage/retrieval
 *
 * provides a generic wrapper for any object that can be stored/retrieved in/from the DB
 * inherits property manipulation methods from MalleableObject and abides by both the inherited MalleableObjectInterface and StorableObjectInterface interfaces
 */
abstract class StorableObject extends MalleableObject implements StorableObjectInterface
{
	// doubles as both the objects ID and the ID of the table row
	protected $id;

	// an associative array of the database table row
	protected $data = array();

	// the database table associated with this object
	protected $table_name = '';
	
	protected $no_store = array
		(
			'id', 'valid', 'data', 'table_name', 'no_store'
		);

	/*
	 * __construct()
	 *
	 * @param $data
	 *	either an integer representing the db ID/Object ID or an associative array of the database table row
	 */
	public function __construct($data = '')
	{
		// if there is data
		if($data)
		{
			// attempt to load it and return the results
			$this->valid = $this->load($data);
			return;
		}
		// new object
		$this->valid = false;
	}

	/*
	 * load()
	 *
	 * @param $data
	 *	either an integer representing the db ID/Object ID or an associative array of the database table row
	 */
	public function load($data)
	{
		// is the data scalar? (and if so, do we have a table name?)
		if(!is_array($data) && $this->table_name)
		{
			// attempt to load the object by ID
			global $db;
			$data = (int) $data;
			$query = $db->simple_select($this->table_name, '*', "id='{$data}'");

			// if it exists
			if($db->num_rows($query) == 1)
			{
				// store it in our passed var
				$data = $db->fetch_array($query);
			}
		}

		// if we have a (hopefully) valid array
		if(is_array($data) && !empty($data))
		{
			// store it in the object
			foreach($data as $key => $val)
			{
				if(property_exists($this, $key))
				{
					$this->$key = $this->data[$key] = $val;
				}
			}
			return true;
		}
		// new blank object
		return false;
	}

	/*
	 * save()
	 *
	 * stores the objects data in the database
	 */
	public function save()
	{
		// if we have a table name stored
		if($this->table_name)
		{
			global $db;

			$this->data = array();
			foreach($this as $property => $value)
			{
				if(in_array($property, $this->no_store))
				{
					continue;
				}

				switch(gettype($this->$property))
				{
					case "boolean":
						$this->data[$property] = (bool) $value;
						break;
					case "integer":
						$this->data[$property] = (int) $value;
						break;
					case "NULL":
						$this->data[$property] = NULL;
						break;
					case "double":
						$this->data[$property] = (float) $value;
						break;
					case "string":
						$this->data[$property] = $db->escape_string($value);
						break;
					case "array":
					case "object":
					case "resource":
						$this->data[$property] = $db->escape_string(json_encode($value));
						break;
					default:
						continue;
				}
			}
			$this->data['dateline'] = TIME_NOW;

			// insert or update depending upon the content of ID
			if($this->id)
			{
				// return true/false
				return $db->update_query($this->table_name, $this->data, "id='{$this->id}'");
			}
			else
			{
				// return the ID on success/false on fail
				return $this->id = $db->insert_query($this->table_name, $this->data);
			}
		}
		// fail
		return false;
	}

	/*
	 * remove()
	 *
	 * remove the object from the database
	 */
	public function remove($no_cleanup = false)
	{
		// valid ID and DB info?
		if($this->id && $this->table_name)
		{
			// nuke it and return true/false
			global $db;
			$db->delete_query($this->table_name, "id='{$this->id}'");
			return true;
		}
		return false;
	}
}

?>
