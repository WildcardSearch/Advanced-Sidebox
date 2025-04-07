<?php
/*
 * Wildcard Helper Classes
 * PortableObject Class Structure
 */

/**
 * provides a standard interface for object import/export
 */
interface PortableObjectInterface010100
{
	public function export($options = '');
	public function import($xml);
	public function buildRow();
}