<?php
/*
 * Plugin Name: MentionMe for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * https://www.rantcentralforums.com
 *
 * this file defines an interface for the caching class
 */

interface WildcardPluginCacheInterface010200
{
	public function read($key = null);
	public function update($key = null, $val = null, $hard = false);
	public function save();
	public function clear($hard = false);
	public function getVersion();
	public function setVersion($version);
}