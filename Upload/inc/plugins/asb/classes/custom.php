<?php
/*
 * Plug-in Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2013 WildcardSearch
 * http://www.wildcardsworld.com
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

	public function __construct($data)
	{
		$this->table_name = 'asb_custom_sideboxes';
		$this->no_store[] = 'base_name';
		parent::__construct($data);
	}

	public function load($data)
	{
		if($data)
		{
			if(parent::load($data))
			{
				$this->base_name = 'asb_custom_' . $this->id;
				return true;
			}
		}
		return false;
	}

	/*
	 * remove()
	 *
	 * removes the custom side box from the database
	 *
	 * @param - $no_cleanup is a boolean value and when true will prevent the removal of sideboxes using this custom type
	 */
	public function remove($no_cleanup = false)
	{
		// don't waste time on bad info
		if($this->id)
		{
			// unless specifically requested otherwise clean up
			if(!$no_cleanup)
			{
				$this->remove_children();
			}
			return parent::remove();
		}
	}

	/*
	 * remove_children()
	 *
	 * delete all the side boxes of this type
	 */
	protected function remove_children()
	{
		global $db;

		// delete all boxes of this type in use
		$module = $db->escape_string(strtolower($this->base_name));
		$db->delete_query('asb_sideboxes', "LOWER(box_type)='{$module}'");
	}

	/*
	 * build_template()
	 *
	 * builds the content for the template variable used for this custom box
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
