<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * https://www.rantcentralforums.com
 *
 * this file contains an object wrapper for script definitons
 */

class ScriptInfo extends PortableObject010102
{
	/**
	 * @var string
	 */
	protected $tid = 0;

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
	protected $width_left = '20';

	/**
	 * @var int
	 */
	protected $left_margin = '0.5';

	/**
	 * @var int
	 */
	protected $width_middle = '59';

	/**
	 * @var int
	 */
	protected $right_margin = '0.5';

	/**
	 * @var int
	 */
	protected $width_right = '20';

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
	 * @var bool
	 */
	protected $mobile_disabled = 0;

	/**
	 * @var string
	 */
	protected $tableName = 'asb_script_info';
}