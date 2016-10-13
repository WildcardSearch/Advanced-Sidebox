<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains an object wrapper for individual custom boxes
 */

class Custom_type extends PortableObject
{
	protected $content;
	protected $base_name;
	protected $title;
	protected $description;
	protected $wrap_content = false;
	protected $table_name = 'asb_custom_sideboxes';

	/*
	 * called upon creation
	 *
	 * @param mixed an associative array corresponding to both the class specs
	 * and the database table specs or a database table row ID
	 * @return void
	 */
	public function __construct($data = '')
	{
		$this->no_store[] = 'base_name';
		parent::__construct($data);
	}

	/*
	 * attempts to load the side box's data from the db, or if given no data create a blank object
	 *
	 * @param array fetched from the db or
	 * int ID # (__construct will feed 0 if no data is given)
	 * @return bool true on success, false on fail
	 */
	public function load($data)
	{
		if(parent::load($data))
		{
			$this->base_name = 'asb_custom_' . $this->id;
			return true;
		}
		return false;
	}

	/*
	 * removes the custom side box from the database
	 *
	 * @param bool value and when true will prevent the removal of sideboxes using this custom type
	 * @return bool true on success, false on fail
	 */
	public function remove($no_cleanup = false)
	{
		// unless specifically requested otherwise clean up
		if(!$no_cleanup)
		{
			$this->remove_children();
		}
		return parent::remove();
	}

	/*
	 * delete all the side boxes of this type
	 * 
	 * @return void
	 */
	protected function remove_children()
	{
		global $db;

		// delete all boxes of this type in use
		$module = $db->escape_string(strtolower($this->base_name));
		$db->delete_query('asb_sideboxes', "LOWER(box_type)='{$module}'");
	}

	/*
	 * builds the content for the template variable used for this custom box
	 * 
	 * @return bool true on success, false on fail
	 */
	public function build_template($template_variable)
	{
		// note the double-$'s . . . we are declaring the base_name of this custom module as global so that our changes will take effect where they are needed
		global $$template_variable, $mybb, $lang;

		$content = $this->content;
		$ret_val = true;

		// if the user doesn't want content then at least make it validate
		if(strlen($content) == 0)
		{
			$ret_val = false;
			$content = '
	<tr>
		<td></td>
	</tr>';
		}
		else
		{
			$content = str_replace("\\'", "'", addslashes($content));
			eval("\${$template_variable} = \"" . $content . "\";");
		}
		return $ret_val;
	}
}

?>
