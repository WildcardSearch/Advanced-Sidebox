<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains an object wrapper for script definitons
 */

class ScriptInfo extends PortableObject010102
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
	protected $width_left = '160px';

	/**
	 * @var int
	 */
	protected $width_right = '160px';

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
