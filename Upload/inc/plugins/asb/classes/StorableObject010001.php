<?php
/*
 * Wildcard Helper Classes
 * StorableObject Class Structure
 */

/**
 * standard object for db storage/retrieval
 */
abstract class StorableObject010001 extends MalleableObject010000 implements StorableObjectInterface010000
{
	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var array
	 */
	protected $data = array();

	/**
	 * @var string
	 */
	protected $tableName = '';

	/*
	 * @var array
	 */
	protected $noStore = array(
		'id', 'valid', 'data', 'tableName', 'noStore'
	);

	/**
	 * constructor
	 *
	 * @param  array|int data or id
	 * @return void
	 */
	public function __construct($data = '')
	{
		$this->valid = $this->load($data);
	}

	/**
	 * load the object from the database
	 *
	 * @param  array|int
	 * @return bool
	 */
	public function load($data)
	{
		if (!isset($data) ||
			!$data ||
			(int) $data == 0) {
			return false;
		}

		if (!is_array($data) &&
			$this->tableName) {
			// attempt to load the object by ID
			global $db;
			$data = (int) $data;
			$query = $db->simple_select($this->tableName, '*', "id='{$data}'");

			// if it exists
			if ($db->num_rows($query) == 1) {
				// store it in our passed var
				$data = $db->fetch_array($query);
			}
		}

		// if we have a (hopefully) valid array
		if (is_array($data) &&
			!empty($data)) {
			// store it in the object
			foreach ($data as $key => $val) {
				if (property_exists($this, $key)) {
					$this->$key = $this->data[$key] = $val;
				}
			}
			return true;
		}
		// new blank object
		return false;
	}

	/**
	 * stores the objects data in the database
	 *
	 * @return mixed|bool return of db wrapper or false
	 */
	public function save()
	{
		global $db;

		$this->data = array();
		foreach ($this as $property => $value) {
			if (in_array($property, $this->noStore) || !isset($this->$property)) {
				continue;
			}

			switch (gettype($this->$property)) {
                case 'boolean':
                    $this->data[$property] = (bool) $value;
                    break;
                case 'integer':
                    $this->data[$property] = (int) $value;
                    break;
                case 'NULL':
                    $this->data[$property] = null;
                    break;
                case 'double':
                    $this->data[$property] = (float) $value;
                    break;
                case 'string':
                    $this->data[$property] = $db->escape_string($value);
                    break;
                case 'array':
                case 'object':
                case 'resource':
                    $this->data[$property] = $db->escape_string(json_encode($value));
                    break;
                default:
                    break;
			}
		}
		$this->data['dateline'] = TIME_NOW;

		// insert or update depending upon the content of ID
		if ($this->id) {
			// return true/false
			return $db->update_query($this->tableName, $this->data, "id='{$this->id}'");
		} else {
			// return the ID on success/false on fail
			return $this->id = $db->insert_query($this->tableName, $this->data);
		}
	}

	/**
	 * remove the object from the database
	 *
	 * @return mixed|bool return of db wrapper or false
	 */
	public function remove($noCleanup = false)
	{
		// valid ID and DB info?
		if (!$this->id ||
			!$this->tableName) {
			return false;
		}

		global $db;
		return $db->delete_query($this->tableName, "id='{$this->id}'");
	}
}