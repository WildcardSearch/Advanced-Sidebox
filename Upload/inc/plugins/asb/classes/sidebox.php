<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains an object wrapper for individual side boxes
 */

class Sidebox extends StorableObject
{
	protected $title;
	protected $title_link;
	protected $box_type;
	protected $position = 0;
	protected $display_order;

	protected $wrap_content = false;

	protected $scripts = array();
	protected $groups = array();
	protected $themes = array();

	protected $settings = array();
	public $has_settings = false;

	protected $table_name = 'asb_sideboxes';

	/*
	 * called upon creation
	 *
	 * @param mixed an associative array corresponding to both the class
	 * specs and the database table specs or a database table row ID
	 * @return void
	 */
	function __construct($data = '')
	{
		$this->no_store[] = 'groups_array';
		$this->no_store[] = 'has_settings';
		parent::__construct($data);
	}

	/*
	 * attempts to load the side box's data from the db, or if given no data create a blank object
	 *
	 * @param array fetched from the db or
	 * a valid ID # (__construct will feed 0 if no data is given)
	 * @return bool true on success, false on fail
	 */
	public function load($data)
	{
		if($data && parent::load($data))
		{
			foreach(array('settings', 'groups', 'scripts', 'themes') as $property)
			{
				if($this->$property)
				{
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
