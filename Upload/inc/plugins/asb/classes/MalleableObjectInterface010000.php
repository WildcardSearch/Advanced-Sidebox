<?php
/*
 * Wildcard Helper Classes
 * MalleableObjectInterface Structure
 */

/**
 * provides standard data methods and validation for inheritance
*/
interface MalleableObjectInterface010000
{
	public function get($properties);
	public function set($properties, $value = '');
	public function isValid();
}