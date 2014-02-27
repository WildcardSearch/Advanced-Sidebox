<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains an object wrapper for individual side boxes
 */

class Sidebox extends StorableObject
{
	protected $title;
	protected $box_type;
	protected $position = 0;
	protected $display_order;

	protected $wrap_content = false;

	protected $scripts = array();
	protected $groups = array();

	protected $settings = array();
	public $has_settings = false;

	protected $table_name = 'asb_sideboxes';

	/*
	 * __construct()
	 *
	 * called upon creation
	 *
	 * @param - $data - (mixed) an associative array corresponding to both the class
	 * specs and the database table specs or a database table row ID
	 * @return: n/a
	 */
	function __construct($data = '')
	{
		$this->no_store[] = 'groups_array';
		$this->no_store[] = 'has_settings';
		parent::__construct($data);
	}

	/*
	 * load()
	 *
	 * attempts to load the side box's data from the db, or if given no data create a blank object
	 *
	 * @param - $data can be an array fetched from the db or
	 * a valid ID # (__construct will feed 0 if no data is given)
	 * @return: (bool) true on success, false on fail
	 */
	public function load($data)
	{
		if($data && parent::load($data))
		{
			// are there settings?
			if($this->settings)
			{
				// if so decode them
				$this->settings = json_decode($this->settings, true);

				// if they seem legit
				$this->has_settings = (is_array($this->settings) && !empty($this->settings));
			}

			// are there settings?
			if($this->groups)
			{
				// if so decode them
				$this->groups = json_decode($this->groups, true);
			}

			// are there settings?
			if($this->scripts)
			{
				// if so decode them
				$this->scripts = json_decode($this->scripts, true);
			}
			return true;
		}
		return false;
	}
}

?>
