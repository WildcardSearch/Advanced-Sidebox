<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains an object wrapper for script definitons
 */

// check dependencies
if (!class_exists('MalleableObject')) {
	require_once MYBB_ROOT . 'inc/plugins/asb/classes/MalleableObject.php';
}
if (!class_exists('StorableObject')) {
	require_once MYBB_ROOT . 'inc/plugins/asb/classes/StorableObject.php';
}
if (!class_exists('PortableObject')) {
	require_once MYBB_ROOT . 'inc/plugins/asb/classes/PortableObject.php';
}

class ScriptInfo extends PortableObject
{
	/**
	 * @var string
	 */
	protected $title = '';

	/**
	 * @var string
	 */
	protected $filename = '';

	/**
	 * @var string
	 */
	protected $action = '';

	/**
	 * @var string
	 */
	protected $page = '';

	/**
	 * @var int
	 */
	protected $width_left = 160;

	/**
	 * @var int
	 */
	protected $width_right = 160;

	/**
	 * @var string
	 */
	protected $template_name = '';

	/**
	 * @var string
	 */
	protected $hook = '';

	/**
	 * @var string
	 */
	protected $find_top = '';

	/**
	 * @var string
	 */
	protected $find_bottom = '';

	/**
	 * @var string
	 */
	protected $replace_all = false;

	/**
	 * @var string
	 */
	protected $replacement = '';

	/**
	 * @var string
	 */
	protected $replacement_template = '';

	/**
	 * @var bool
	 */
	protected $eval = false;

	/**
	 * @var bool
	 */
	protected $active = false;

	/**
	 * @var string
	 */
	protected $tableName = 'asb_script_info';
}

?>
