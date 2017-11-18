<?php
/*
 * Plugin Name: MentionMe for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * wrapper to handle our plugin's cache
 */

class AdvancedSideboxCache extends WildcardPluginCache010300
{
	/**
	 * @var  string cache key
	 */
	protected $cacheKey = 'asb';

	/**
	 * @var  string cache sub key
	 */
	protected $subKey = '';

	/**
	 * @return instance of the child class
	 */
	static public function getInstance()
	{
		static $instance;
		if (!isset($instance)) {
			$instance = new AdvancedSideboxCache;
		}
		return $instance;
	}
}

?>
