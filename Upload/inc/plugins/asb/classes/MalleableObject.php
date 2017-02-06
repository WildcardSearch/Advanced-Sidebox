<?php
/*
 * Wildcard Helper Classes
 * MalleableObject Class Structure
 */

/**
 * provides standard data methods and validation for inheritance
*/
interface MalleableObjectInterface
{
	public function get($properties);
	public function set($properties, $value = '');
	public function isValid();
}

/**
 * provides standard data methods and validation for inheritance
 */
abstract class MalleableObject implements MalleableObjectInterface
{
	/**
	 * @var bool
	 */
	protected $valid = false;

	/**
	 * retrieves a named property or a list of properties
	 *
	 * @param  array|string property names or a single name
	 * @return array|mixed properties and values or a single value
	 */
	public function get($properties)
	{
		if (is_array($properties)) {
			$returnArray = array();
			foreach ($properties as $property) {
				if (property_exists($this, $property)) {
					$returnArray[$property] = $this->$property;
				}
			}
			return $returnArray;
		}
		else
		{
			if (property_exists($this, $properties)) {
				return $this->$properties;
			}
			return false;
		}
	}

	/**
	 * sets a single property or multiple properties at once
	 *
	 * @param  array|string propertie(s)
	 * @param  mixed the property value
	 * @return bool success/fail
	 */
	public function set($properties, $value = '')
	{
		if (is_array($properties)) {
			foreach ($properties as $property => $value) {
				if (property_exists($this, $property)) {
					$this->$property = $value;
				}
			}
			return true;
		} elseif (strlen($properties) > 0 &&
			property_exists($this, $properties) &&
			isset($value)) {
			$this->$properties = $value;
			return true;
		}
		return false;
	}

	/**
	 * allows access to the protected valid property
	 *
	 * @return bool the valid property value
	 */
	public function isValid()
	{
		return $this->valid;
	}
}

?>
