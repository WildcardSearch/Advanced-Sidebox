<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains an object wrapper for individual side boxes
 */

class SideboxObject extends StorableObject010000
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
	public $has_settings = false;

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
	function __construct($data = '')
	{
		$this->noStore[] = 'groups_array';
		$this->noStore[] = 'has_settings';
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
		if ($data &&
			parent::load($data)) {
			foreach (array('settings', 'groups', 'scripts', 'themes') as $property) {
				if ($this->$property) {
					// if so decode them
					$this->$property = json_decode($this->$property, true);
				}
			}

			$this->has_settings = (is_array($this->settings) && !empty($this->settings));
			return true;
		}
		return false;
	}
}

?>
