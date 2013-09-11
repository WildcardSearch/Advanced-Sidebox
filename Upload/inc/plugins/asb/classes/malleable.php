<?php
/*
 * Wildcard Helper Classes
 * MalleableObject Class Structure
 */

/*
 * MalleableObjectInterface
 *
 * for any object that has properties to be set or retrieved
 * provides standard data methods and validation for inheritance
*/
interface MalleableObjectInterface
{
	public function get($properties);
	public function set($properties, $value = '');
	public function is_valid();
}

/*
 * MalleableObject
 *
 * for any object that has properties to be set or retrieved
 * provides standard data methods and validation for inheritance
 */
abstract class MalleableObject implements MalleableObjectInterface
{
	protected $valid = false;

	/*
	 * get()
	 *
	 * retrieves a named property or a list of properties)
	 *
	 * @param - $properties - (mixed) an unindexed array of property names or a single property name
	 *
	 * returns - a keyed array of properties and values or a single value
	 */
	public function get($properties)
	{
		if(is_array($properties))
		{
			$return_array = array();
			foreach($properties as $property)
			{
				if(property_exists($this, $property))
				{
					$return_array[$property] = $this->$property;
				}
			}
			return $return_array;
		}
		else
		{
			if(property_exists($this, $properties))
			{
				return $this->$properties;
			}
			return false;
		}
	}

	/*
	 * set()
	 *
	 * sets a single property or multiple properties at once
	 *
	 * @param - $properties - (mixed) a keyed array of properties and their values or a single property name
	 * @param - $value - (mixed) any data type
	 */
	public function set($properties, $value = '')
	{
		if(is_array($properties))
		{
			foreach($properties as $property => $value)
			{
				if(property_exists($this, $property))
				{
					$this->$property = $value;
				}
			}
			return true;
		}
		elseif(strlen($properties) > 0 && isset($value))
		{
			$this->$properties = $value;
			return true;
		}
		return false;
	}

	/*
	 * public function is_valid()
	 *
	 * allows access to the protected valid property
	 */
	public function is_valid()
	{
		return $this->valid;
	}
}

?>
