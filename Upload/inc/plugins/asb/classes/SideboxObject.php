<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * https://www.rantcentralforums.com
 *
 * this file contains an object wrapper for individual side boxes
 */

class SideboxObject extends StorableObject010001
{
	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $title_link;

	/**
	 * @var string
	 */
	protected $box_type;

	/**
	 * @var int
	 */
	protected $position = 0;

	/**
	 * @var int
	 */
	protected $display_order;

	/**
	 * @var bool
	 */
	protected $wrap_content = false;

	/**
	 * @var array
	 */
	protected $scripts = array();

	/**
	 * @var array
	 */
	protected $groups = array();

	/**
	 * @var array
	 */
	protected $themes = array();

	/**
	 * @var array
	 */
	protected $settings = array();

	/**
	 * @var bool
	 */
	public $hasSettings = false;

	/**
	 * @var string
	 */
	protected $tableName = 'asb_sideboxes';

	/**
	 * constructor
	 *
	 * @param  array|int data or id
	 * @return void
	 */
	function __construct($data='')
	{
		$this->noStore[] = 'hasSettings';
		parent::__construct($data);
	}

	/**
	 * create a sidebox/load a side box
	 *
	 * @param  array|int data or id
	 * @return bool true on success, false on fail
	 */
	public function load($data)
	{
		if (!parent::load($data)) {
			return false;
		}

		foreach (array('settings', 'groups', 'scripts', 'themes') as $property) {
			if (property_exists($this, $property) &&
				isset($this->$property)) {
				// if so decode them
				$this->$property = json_decode($this->$property, true);
			}
		}

		$this->hasSettings = (is_array($this->settings) && !empty($this->settings));

		return true;
	}
}