<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * https://www.rantcentralforums.com
 *
 * this file contains an object wrapper for individual custom boxes
 */

class CustomSidebox extends PortableObject010102
{
	/**
	 * @var string
	 */
	protected $content;

	/**
	 * @var string
	 */
	protected $baseName;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @var bool
	 */
	protected $wrap_content = false;

	/**
	 * @var string
	 */
	protected $tableName = 'asb_custom_sideboxes';

	/**
	 * constructor
	 *
	 * @param  array|int data or id
	 * @return void
	 */
	public function __construct($data='')
	{
		$this->noStore[] = 'baseName';
		parent::__construct($data);
	}

	/**
	 * attempts to load the side box's data from the db, or if given no data create a blank object
	 *
	 * @param  array|int data or id
	 * @return bool true on success, false on fail
	 */
	public function load($data)
	{
		if (parent::load($data)) {
			$this->baseName = 'asb_custom_'.$this->id;
			return true;
		}
		return false;
	}

	/**
	 * removes the custom side box from the database
	 *
	 * @param  bool prevent removal of sideboxes of this type?
	 * @return bool success/fail
	 */
	public function remove($noCleanup=false)
	{
		// unless specifically requested otherwise clean up
		if ($noCleanup !== true) {
			$this->removeChildren();
		}

		return parent::remove();
	}

	/**
	 * delete all the side boxes of this type
	 *
	 * @return void
	 */
	protected function removeChildren()
	{
		global $db;

		// delete all boxes of this type in use
		$module = $db->escape_string(strtolower($this->baseName));
		$db->delete_query('asb_sideboxes', "LOWER(box_type)='{$module}'");
	}

	/**
	 * builds the content for the template variable used for this custom box
	 *
	 * @param  string
	 * @return bool success/fail
	 */
	public function buildContent($template_variable)
	{
		global $$template_variable, $mybb, $lang;

		$content = $this->content;
		$returnVal = true;

		// if the user doesn't want content then so be it
		if (strlen($content) == 0) {
			$returnVal = false;
			$$template_variable = '&nbsp;';
		} else {
			$content = str_replace("\\'", "'", addslashes($content));
			eval("\${$template_variable} = \"{$content}\";");
		}

		return $returnVal;
	}
}