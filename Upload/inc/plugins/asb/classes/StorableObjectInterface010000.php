<?php
/*
 * Wildcard Helper Classes
 * StorableObject Class Structure
 */

/**
 * standard interface for database storage/retrieval
 */
interface StorableObjectInterface010000
{
	public function load($data);
	public function save();
	public function remove($noCleanup = false);
}