<?php
/*
 * Wildcard Helper Classes
 * MalleableObject Class Structure
 */

/**
 * provides standard data methods and validation for inheritance
 */
abstract class MalleableObject010000 implements MalleableObjectInterface010000
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
		} else {
			if (property_exists($this, $properties)) {
				return $this->$properties;
			}
			return false;
		}
	}

	/**
	 * sets a single property or multiple properties at once
	 *
	 * @param  array|string
	 * @param  mixed the property value
	 * @return bool success/fail
	 */
	public function set($properties, $value = '')
	{
		if (!is_array($properties)) {
			$properties = array($properties => $value);
		}

		foreach ($properties as $property => $value) {
			if (isset($value) &&
				property_exists($this, $property)) {
				$this->$property = $value;
			}
		}
		return true;
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