<?php
/*
 * This file contains class definitions for the entire project
 *
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2013 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * Check out this project on GitHub: http://wildcardsearch.github.com/Advanced-Sidebox
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses
 */

/*
 * wrapper for individual sideboxes
 */
class Sidebox
{
	private $id;
	private $display_name;
	private $box_type;
	private $position = 0;
	private $display_order;

	private $wrap_content = false;
	public $valid = false;

	private $show_on_index = false;
	private $show_on_forumdisplay = false;
	private $show_on_showthread = false;
	private $show_on_member = false;
	private $show_on_memberlist = false;
	private $show_on_showteam = false;
	private $show_on_stats = false;
	private $show_on_portal = false;

	private $groups;
	public $groups_array;

	private $settings;
	public $has_settings = false;

	/*
	 * __construct() called upon creation
	 *
	 * @param - $sidebox can be an array fetched from db,
	 * 						a valid ID # or
	 *						left blank to create a new sidebox
	 */
	function __construct($sidebox = 0)
	{
		// try to load the sidebox
		$this->load($sidebox);
	}

	/*
	 * load()
	 *
	 * attempts to load the sidebox's data from the db, or if given no data create a blank object
	 *
	 * @param - $data can be an array fetched from the db or
	 *						a valid ID # (__construct will feed 0 if no data is given)
	 */
	private function load($data)
	{
		global $db;

		// if data isn't an array, try it as an ID
		if(!is_array($data))
		{
			// if the ID is 0 then there is nothing to go on
			if((int) $data)
			{
				// otherwise check the db
				$this_query = $db->simple_select('sideboxes', '*', "id='{$data}'");

				// if it exists
				if($db->num_rows($this_query))
				{
					// store the data
					$data = $db->fetch_array($this_query);
				}
			}
		}

		// ID = 0 means nothing to do
		if($data['id'])
		{
			// good id? then store the data in our object and validate
			$this->valid = true;

			$this->id = (int) $data['id'];
			$this->display_name = $data['display_name'];
			$this->box_type = $data['box_type'];
			$this->position = (int) $data['position'];

			$this->display_order = (int) $data['display_order'];
			$this->wrap_content = (int) $data['wrap_content'];

			$this->show_on_index = $data['show_on_index'];
			$this->show_on_forumdisplay = $data['show_on_forumdisplay'];
			$this->show_on_showthread = $data['show_on_showthread'];
			$this->show_on_member = $data['show_on_member'];
			$this->show_on_memberlist = $data['show_on_memberlist'];
			$this->show_on_showteam = $data['show_on_showteam'];
			$this->show_on_stats = $data['show_on_stats'];
			$this->show_on_portal = $data['show_on_portal'];

			// if there are groups
			if($data['groups'] != null)
			{
				// load the group permissions
				$this->groups = $data['groups'];

				// convert them to an array as well
				$this->groups_array = explode(",", $this->groups);
			}
			else
			{
				// otherwise allow all groups
				$this->groups = 'all';
			}

			// are there settings?
			if($data['settings'])
			{
				// if so decode them
				$this->settings = json_decode($data['settings'], true);

				// if they seem legit
				if(is_array($this->settings))
				{
					// set a marker
					$this->has_settings = true;
				}
			}
		}
	}

	/*
	 * save()
	 *
	 * can be called upon any existing sidebox to save the object to the db
	 */
	public function save()
	{
		global $db;

		// set up db array
		$this_box = array(
			"display_name"					=>	$db->escape_string($this->display_name),
			"box_type"							=>	$db->escape_string($this->box_type),
			"position"							=>	(int) $this->position,
			"display_order"					=> 	(int) $this->display_order,
			"wrap_content"					=>	(int) $this->wrap_content,
			"show_on_index"				=>	(int) $this->show_on_index,
			"show_on_forumdisplay"	=>	(int) $this->show_on_forumdisplay,
			"show_on_showthread"	=>	(int) $this->show_on_showthread,
			"show_on_member"			=>	(int) $this->show_on_member,
			"show_on_memberlist"		=>	(int) $this->show_on_memberlist,
			"show_on_showteam"		=>	(int) $this->show_on_showteam,
			"show_on_stats"				=>	(int) $this->show_on_stats,
			"show_on_portal"				=>	(int) $this->show_on_portal,
			"groups"								=>	$db->escape_string($this->groups),
			"settings"							=>	$db->escape_string(json_encode($this->settings))
		);

		// ID means update an existing box
		if($this->id > 0)
		{
			$status = $db->update_query('sideboxes', $this_box, "id='" . (int) $this->id . "'");
		}
		else
		{
			// otherwise insert a new box
			$status = $db->insert_query('sideboxes', $this_box);
		}

		return $status;
	}

	/*
	 * remove()
	 *
	 * removes the sidebox from the database
	 */
	public function remove()
	{
		if($this->id)
		{
			global $db;

			// attempt to delete it and return the result
			return $db->query("DELETE FROM " . TABLE_PREFIX . "sideboxes WHERE id='" . (int) $this->id . "'");
		}
	}

	/*
	 * build_table_row()
	 *
	 * can be called on any exisiting sidebox object
	 *
	 * @param - $this_table must be a valid object of class Table
	 */
	public function build_table_row($this_table)
	{
		global $mybb, $lang;

		if(!$lang->adv_sidebox)
		{
			$lang->load('adv_sidebox');
		}

		// construct the table row
		if($this_table instanceof Table)
		{
			// name (edit link)
			$this_table->construct_cell('<a href="' . ADV_SIDEBOX_EDIT_URL . '&amp;mode=' . $mybb->input['mode'] . '&amp;box=' . $this->id . '">' . $this->display_name . '</a>', array("width" => '30%'));

			// scripts
			$this_table->construct_cell($this->build_script_list(), array("width" => '30%'));

			// prepare group info
			// all groups enabled?
			if($this->groups == 'all')
			{
				// get that language
				$groups = $lang->adv_sidebox_all_groups;
			}
			else
			{
				// otherwise display a list
				if(is_array($this->groups_array))
				{
					foreach($this->groups_array as $group)
					{
						if($group == 'guests')
						{
							$group = '0';
						}

						if($groups != '')
						{
							$groups .= ',';
						}

						$groups .= $group;
					}
				}
			}

			// groups
			$this_table->construct_cell($groups, array("width" => '20%'));

			// options popup
			$popup = new PopupMenu('box_' . $this->id, $lang->adv_sidebox_options);

			// edit
			$popup->add_item($lang->adv_sidebox_edit, ADV_SIDEBOX_EDIT_URL . '&amp;mode=' . $mybb->input['mode'] . '&amp;box=' . $this->id);

			// delete
			$popup->add_item($lang->adv_sidebox_delete, ADV_SIDEBOX_DEL_URL . '&amp;mode=' . $mybb->input['mode'] . '&amp;box=' . $this->id);

			// popup cell
			$this_table->construct_cell($popup->fetch(), array("width" => '20%'));

			// finish row
			$this_table->construct_row();
		}
	}

	/*
	 * get_content()
	 *
	 * replaces content property by building each side box's content based upon object properties
	 *
	 * if a sidebox's wrap_content property is true it will be 'wrapped' in a table with a header and expander
	 */
	public function get_content()
	{
		global $collapsed;

		// get the base variable name
		$template_var = $this->build_template_variable();

		// if it is valid (anything but '' or null)
		if($template_var)
		{
			// create a template variable of that name
			$content = '{$' . $template_var . '}';
		}
		else
		{
			// otherwise no content
			return false;
		}

		// if we are building header and expander . . .
		if($this->wrap_content)
		{
			// check if this sidebox is either expanded or collapsed and hide it as necessary.
			$expdisplay = '';
			$collapsed_name = $this->box_type . '_' . $this->id . '_c';
			if(isset($collapsed[$collapsed_name]) && $collapsed[$collapsed_name] == "display: show;")
			{
				$expcolimage = "collapse_collapsed.gif";
				$expdisplay = "display: none;";
				$expaltext = "[+]";
			}
			else
			{
				$expcolimage = "collapse.gif";
				$expaltext = "[-]";
			}

			$content =  '<!-- sideboxstart: adv_sidebox header and expander for side box #' . $this->id . ' -->
			<table style="word-wrap: break-word;" border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder ' . $this->box_type . '_main_' . $this->id . '">
				<thead>
					<tr>
						<td class="thead"><div class="expcolimage"><img src="{$theme[\'imgdir\']}/' . $expcolimage . '" id="' . $this->box_type . '_' . $this->id . '_img" class="expander" alt="' . $expaltext . '" title="' . $expaltext . '" /></div><strong>' . $this->display_name . '</strong>
						</td>
					</tr>
				</thead>
				<tbody style="' . $expdisplay . '" id="' . $this->box_type . '_' . $this->id . '_e">
					' . $content . '
				</tbody>
			</table><br />
			<!-- end: adv_sidebox header and expander for side box #' . $this->id . ' ASB-->';
		}

		// if there is anything to return
		if($content)
		{
			// give it up
			return '
			<!-- start side box #' . $this->id . ' - box type ' . $this->box_type . ' -->
			' . $content . '
			<!-- end side box #' . $this->id . ' - box type ' . $this->box_type . ' -->';
		}

		// otherwise return failure
		return false;
	}

	/*
	 * build_template_variable()
	 *
	 * renders a template variable based on side box properties
	 */
	public function build_template_variable()
	{
		return $this->box_type . '_' . $this->id;
	}

	/*
	 * build_script_list()
	 *
	 * builds a comma seperated list of scripts that this sidebox will display on, 'All Scripts' if all, a single name if 1, a deactivated message if none.
	 */
	public function build_script_list()
	{
		global $settings;

		$script_list = $this->get_scripts(true);
		$plain_script_list = $this->get_scripts();
		$new_list = array();

		$count = 0;

		if(is_array($script_list) && is_array($plain_script_list))
		{
			foreach($plain_script_list as $script)
			{
				$base_name = substr($script, 0, strlen($script) - 4);
				$language_name = 'adv_sidebox_' . $base_name;
				$setting_name = 'adv_sidebox_on_' . $base_name;
				
				if($script = 'portal')
				{
					$setting_name = 'adv_sidebox_portal_replace';
				}

				if($settings[$setting_name])
				{
					$new_list[] = '<span title="enabled script" style="color: #32CD32;">' . $script_list[$count] . '</span>';
				}
				else
				{
					$new_list[] = '<span title="disabled script" style="color: #888;">' . $script_list[$count] . '</span>';
				}

				++$count;
			}

			// return a comma space separated list
			$return_val = implode(", ", $new_list);
		}
		else
		{
			$return_val = $script_list;
		}

		// if there are scripts . . .
		if($return_val)
		{
			// return them
			return $return_val;
		}
		else
		{
			// otherwise the side box is deactivated so mark it
			return '<span style="color: red;"><strong>Deactivated</strong></span>';
		}
	}

	/*
	 * get_scripts()
	 *
	 * returns an array containing the various scripts this box is allowed for or a string indicating all scripts (suitable for list box)
	 */
	public function get_scripts($return_language = false)
	{
		// do we need to return language?
		if($return_language)
		{
			// gonna need this
			global $lang;
			if(!$lang->adv_sidebox)
			{
				$lang->load('adv_sidebox');
			}

			// return human intelligible labels
			$index_text = $lang->adv_sidebox_index;
			$forum_text = $lang->adv_sidebox_forum;
			$thread_text = $lang->adv_sidebox_thread;
			$member_text = $lang->adv_sidebox_member;
			$memberlist_text = $lang->adv_sidebox_memberlist;
			$showteam_text = $lang->adv_sidebox_showteam;
			$stats_text = $lang->adv_sidebox_stats;
			$portal_text = $lang->adv_sidebox_portal;
			$all_text = $lang->adv_sidebox_all;
		}
		else
		{
			// if not then just return script filenames
			$index_text = 'index.php';
			$forum_text = 'forumdisplay.php';
			$thread_text = 'showthread.php';
			$member_text = 'member.php';
			$memberlist_text = 'memberlist.php';
			$showteam_text = 'showteam.php';
			$stats_text = 'stats.php';
			$portal_text = 'portal.php';
			$all_text = 'all_scripts';
		}

		// all scripts?
		if($this->show_on_index && $this->show_on_forumdisplay && $this->show_on_showthread && $this->show_on_member && $this->show_on_memberlist && $this->show_on_showteam && $this->show_on_stats && $this->show_on_portal)
		{
			// yes? mark it
			return $all_text;
		}
		else
		{
			// no? check and set them individually
			if($this->show_on_index)
			{
				$selected_scripts[] = $index_text;
			}
			if($this->show_on_forumdisplay)
			{
				$selected_scripts[] = $forum_text;
			}
			if($this->show_on_showthread)
			{
				$selected_scripts[] = $thread_text;
			}
			if($this->show_on_member)
			{
				$selected_scripts[] = $member_text;
			}
			if($this->show_on_memberlist)
			{
				$selected_scripts[] = $memberlist_text;
			}
			if($this->show_on_showteam)
			{
				$selected_scripts[] = $showteam_text;
			}
			if($this->show_on_stats)
			{
				$selected_scripts[] = $stats_text;
			}
			if($this->show_on_portal)
			{
				$selected_scripts[] = $portal_text;
			}
			return $selected_scripts;
		}
	}

	/*
	 * set_scripts()
	 *
	 * @param - $script_array is a list of the current scripts allowed for this side box
	 *
	 * set side box script permissions based on the data in $script_array
	 */
	public function set_scripts(array $script_array)
	{
		// it really should be an array . . . :-/
		if(is_array($script_array))
		{
			// store all the scripts
			foreach($script_array as $this_entry)
			{
				// all scripts?
				if($this_entry == 'all_scripts')
				{
					$this->show_on_index = true;
					$this->show_on_forumdisplay = true;
					$this->show_on_showthread = true;
					$this->show_on_member = true;
					$this->show_on_memberlist = true;
					$this->show_on_showteam = true;
					$this->show_on_stats = true;
					$this->show_on_portal = true;
				}
				else
				{
					$var_to_set = 'show_on_' . substr($this_entry, 0, strlen($this_entry) - 4);
					$this->$var_to_set = true;
				}
			}
		}
	}

	/*
	 * get_display_name()
	 *
	 * handler for $this->display_name
	 */
	public function get_display_name()
	{
		return $this->display_name;
	}

	/*
	 * set_display_name()
	 *
	 * handler for $this->display_name
	 *
	 * @param - $name is the value to store
	 */
	public function set_display_name($name)
	{
		$this->display_name = $name;
	}

	/*
	 * get_settings()
	 *
	 * handler for $this->settings
	 */
	public function get_settings()
	{
		return $this->settings;
	}

	/*
	 * set_settings()
	 *
	 * handler for $this->settings
	 *
	 * @param - $settings is the value to store
	 */
	public function set_settings($settings)
	{
		$this->settings = $settings;
	}

	/*
	 * get_wrap_content()
	 *
	 * handler for $this->wrap_content
	 */
	public function get_wrap_content()
	{
		return $this->wrap_content;
	}

	/*
	 * set_wrap_content()
	 *
	 * handler for $this->wrap_content
	 *
	 * @param - $wrap_content is the value to store
	 */
	public function set_wrap_content($wrap_content)
	{
		$this->wrap_content = (int) $wrap_content;
	}

	/*
	 * get_id()
	 *
	 * handler for $this->id
	 */
	public function get_id()
	{
		return (int) $this->id;
	}

	/*
	 * set_id()
	 *
	 * handler for $this->id
	 *
	 * @param - $id is the value to store
	 */
	public function set_id($id)
	{
		$this->id = $id;
	}

	/*
	 * get_box_type()
	 *
	 * handler for $this->box_type
	 */
	public function get_box_type()
	{
		return $this->box_type;
	}

	/*
	 * set_box_type()
	 *
	 * handler for $this->box_type
	 *
	 * @param - $type is the value to store
	 */
	public function set_box_type($type)
	{
		$this->box_type = $type;
	}

	/*
	 * get_position()
	 *
	 * handler for $this->position
	 */
	public function get_position()
	{
		return (int) $this->position;
	}

	/*
	 * set_position()
	 *
	 * handler for $this->position
	 *
	 * @param - $position is the value to store
	 */
	public function set_position($position = 0)
	{
		switch($position)
		{
			case 'right':
			case 1:
				$this->position = 1;
				break;
			default:
				$this->position = 0;
		}
	}

	/*
	 * get_display_order()
	 *
	 * handler for $this->display_order
	 */
	public function get_display_order()
	{
		return (int) $this->display_order;
	}

	/*
	 * set_display_order()
	 *
	 * handler for $this->display_order
	 *
	 * @param - $order is the value to store
	 */
	public function set_display_order($order)
	{
		if($order)
		{
			$this->display_order = $order;
		}
	}

	/*
	 * set_groups()
	 *
	 * handler for $this->groups
	 *
	 * @param - $groups_array is an array of values to store
	 */
	public function set_groups(array $groups_array)
	{
		$allowedgroups = array();

		// valid info?
		if(is_array($groups_array))
		{
			// loop through each and interpret and store them
			foreach($groups_array as $gid)
			{
				// all means no need to go further
				if($gid == "all")
				{
					$allowedgroups = "all";
					break;
				}

				// guests require a little trickery xD
				if($gid == 'guests')
				{
					$key = 'guests';
				}
				else
				{
					// any other group just store it
					$key = (int) $gid;
				}
				$allowedgroups[$key] = $key;
			}

			// if everything is selected besides All User Groups then it is still all . . .
			if(count($allowedgroups) == (int) $mybb->input['this_group_count'] - 1)
			{
				// '' signifies showing everything (see below)
				$allowedgroups = '';
			}
		}

		// working with an array?
		if(is_array($allowedgroups))
		{
			// convert it to a comma-separated string list
			$allowedgroups = implode(",", $allowedgroups);
		}

		// anything to check?
		if($allowedgroups)
		{
			// store the group list
			$this->groups = $allowedgroups;
		}
		else
		{
			// otherwise enable for all groups
			$this->groups = 'all';

			/*
			 * the reason that no_groups=all_groups is that side boxes from earlier versions (< 1.4) didn't have group permissions and therefore didn't store those values-- so the default value for side boxes is to be shown to all groups
			 */
		}
	}
}

/*
 * base class for box types
 */
abstract class Sidebox_type
{
	protected $base_name;
	protected $name;
	protected $description;

	public $valid = false;

	protected $wrap_content = false;

	/*
	 * remove_children()
	 *
	 * delete all the side boxes of this type
	 */
	protected function remove_children()
	{
		global $db;

		// delete all boxes of this type in use
		$db->query("DELETE FROM " . TABLE_PREFIX . "sideboxes WHERE box_type='" . $this->base_name . "'");
	}

	/*
	 * get_base_name()
	 *
	 * handler for $this->base_name
	 */
	public function get_base_name()
	{
		return $this->base_name;
	}

	/*
	 * set_base_name()
	 *
	 * handler for $this->base_name
	 *
	 * @param - $name is the value to store
	 */
	public function set_base_name($name)
	{
		$this->base_name = $name;
	}

	/*
	 * get_name()
	 *
	 * handler for $this->name
	 */
	public function get_name()
	{
		return $this->name;
	}

	/*
	 * set_name()
	 *
	 * handler for $this->name
	 *
	 * @param - $name is the value to store
	 */
	public function set_name($name)
	{
		$this->name = $name;
	}

	/*
	 * get_description()
	 *
	 * handler for $this->description
	 */
	public function get_description()
	{
		return $this->description;
	}

	/*
	 * set_description()
	 *
	 * handler for $this->description
	 *
	 * @param - $description is the value to store
	 */
	public function set_description($description)
	{
		$this->description = $description;
	}

	/*
	 * get_wrap_content()
	 *
	 * handler for $this->wrap_content
	 */
	public function get_wrap_content()
	{
		return $this->wrap_content;
	}

	/*
	 * set_wrap_content()
	 *
	 * handler for $this->wrap_content
	 *
	 * @param - $wrap_content is the value to store
	 */
	public function set_wrap_content($wrap_content)
	{
		$this->wrap_content = $wrap_content;
	}
}

/*
 * Sidebox_type extended for custom boxes
 */
class Custom_type extends Sidebox_type
{
	private $id;
	private $content;

	/*
	 * __construct()
	 *
	 * either creates a new Custom_type object or loads an existing box from the db
	 *
	 * @param - $data is either an int TID of the database record of this custom box or an associative array pulled from the database externally
	 */
	function __construct($data = 0)
	{
		// attempt to load the box
		$this->load($data);
	}

	/*
	 * load()
	 *
	 * @param - $data
	 */
	private function load($data = 0)
	{
		global $db;

		// if $data is a scalar value . . .
		if(!is_array($data) && $data)
		{
			// check the db
			$query = $db->simple_select('custom_sideboxes', '*', "id='{$data}'");

			// if it exists . . .
			if($db->num_rows($query) == 1)
			{
				// store the $data
				$data = $db->fetch_array($query);
			}
		}

		// if we have data (either from the calling script or from a load above) . . .
		if(is_array($data))
		{
			// store the data
			$this->id = $data['id'];
			$this->base_name = 'asb_custom_' . $this->id;
			$this->name = $data['name'];
			$this->description = $data['description'];
			$this->wrap_content = (int) $data['wrap_content'];
			$this->content = $data['content'];

			$this->valid = true;
		}
	}

	/*
	 * save()
	 *
	 * saves the data currently in the object
	 */
	public function save()
	{
		global $db;

		// set up the array
		$data = array
		(
			"name"				=>	$db->escape_string($this->name),
			"description"		=>	$db->escape_string($this->description),
			"wrap_content"	=>	(int) $this->wrap_content,
			"content"				=>	$db->escape_string($this->content)
		);

		// if we have a ID . . .
		if($this->id > 0)
		{
			// . . . attempt an update and return success/fail
			return $db->update_query('custom_sideboxes', $data, "id='{$this->id}'");
		}
		else
		{
			// . . . otherwise attempt an insert and return success/fail
			return $db->insert_query('custom_sideboxes', $data);
		}
	}

	/*
	 * remove()
	 *
	 * removes the custom sidebox from the database
	 *
	 * @param - $no_cleanup is a boolean value and when true will prevent the removal of sideboxes using this custom type
	 */
	public function remove($no_cleanup = false)
	{
		// don't waste time on bad info
		if($this->id)
		{
			global $db;

			// unless specifically requested otherwise clean up
			if(!$no_cleanup)
			{
				$this->remove_children();
			}

			// attempt to delete it and return the result
			return $db->query("DELETE FROM " . TABLE_PREFIX . "custom_sideboxes WHERE id='" . (int) $this->id . "'");
		}
	}

	/*
	 * export()
	 *
	 * exports a custom box type as XML
	 */
	public function export()
	{
		global $lang;

		// get the plugin info for versioning
		$info = adv_sidebox_info();

		// set up the XML
		$xml = '<?xml version="1.0" encoding="' . $lang->settings['charset'] . '"?>
<adv_sidebox version="' . $info['version'] . '" xmlns="' . $info['website'] . '">
	<custom_sidebox>
		<name><![CDATA[' . $this->name . ']]></name>
		<description><![CDATA[' . $this->description . ']]></description>
		<wrap_content><![CDATA[' . $this->wrap_content . ']]></wrap_content>
		<content><![CDATA[' . base64_encode($this->content) . ']]></content>
		<checksum>' . md5($this->content) . '</checksum>
	</custom_sidebox>
</adv_sidebox>';

		// replace spaces with dashes in the filename
		$filename = implode('-', explode(' ', $this->name));

		// send out headers (opens a save dialogue)
		header('Content-Disposition: attachment; filename=' . $filename . '.xml');
		header('Content-Type: application/xml');
		header('Content-Length: ' . strlen($xml));
		header('Pragma: no-cache');
		header('Expires: 0');
		echo $xml;
	}

	/*
	 * build_template()
	 *
	 * builds the template variable used for this custom box
	 */
	public function build_template($template_variable)
	{
		// note the double-$'s . . . we are declaring the base_name of this custom module as global so that our changes will take effect where they are needed
		global $$template_variable;

		$content = $this->content;
		$ret_val = true;

		// if the user doesn't want content then at least make it validate
		if(strlen($content) < 3)
		{
			$ret_val = false;
			$content = '
	<tr>
		<td></td>
	</tr>';
		}

		// store the content
		$$template_variable = $content;
		return $ret_val;
	}

	/*
	 * build_table_row()
	 *
	 * renders html with details for this custom box
	 *
	 * @param - $this_table is a valid object of class DefaultTable
	 */
	public function build_table_row($this_table)
	{
		global $lang;

		if(!$lang->adv_sidebox)
		{
			$lang->load('adv_sidebox');
		}

		// valid table?
		if($this_table instanceof Table)
		{
			// name (edit link)
			$this_table->construct_cell('<a href="' . ADV_SIDEBOX_CUSTOM_URL . '&amp;mode=edit_box&amp;box=' . $this->id . '" title="' . $lang->adv_sidebox_edit . '">' . $this->name . '</a>', array("width" => '30%'));

			// description
			$this_table->construct_cell($this->description, array("width" => '60%'));

			// options popup
			$popup = new PopupMenu('box_' . $this->id, $lang->adv_sidebox_options);

			// edit
			$popup->add_item($lang->adv_sidebox_edit, ADV_SIDEBOX_CUSTOM_URL . "&amp;mode=edit_box&amp;box={$this->id}");

			// delete
			$popup->add_item($lang->adv_sidebox_delete, ADV_SIDEBOX_CUSTOM_URL . "&amp;mode=delete_box&amp;box={$this->id}", 'return confirm(\'' . $lang->adv_sidebox_custom_del_warning . '\');');

			// export
			$popup->add_item($lang->adv_sidebox_custom_export, ADV_SIDEBOX_EXPORT_URL . "&amp;box={$this->id}");

			// popup cell
			$this_table->construct_cell($popup->fetch(), array("width" => '10%'));

			// finish the table
			$this_table->construct_row();
		}
	}

	/*
	 * get_id()
	 *
	 * handler for $this->id
	 */
	public function get_id()
	{
		return $this->id;
	}

	/*
	 * set_id()
	 *
	 * handler for $this->id
	 *
	 * @param - $id is the value to set
	 */
	public function set_id($id)
	{
		$this->id = $id;
	}

	/*
	 * get_content()
	 *
	 * handler for $this->content
	 */
	public function get_content()
	{
		return $this->content;
	}

	/*
	 * set_content()
	 *
	 * handler for $this->content
	 *
	 * @param - $content is the value to set
	 */
	public function set_content($content)
	{
		$this->content = $content;
	}
}

/*
 * Sidebox_type extended for add-on modules
 */
class Addon_type extends Sidebox_type
{
	private $author;
	private $author_site;

	private $settings;
	public $has_settings;

	private $templates;

	private $is_installed = false;
	private $is_upgraded = false;

	private $old_version;
	private $version;

	private $discarded_settings;
	private $discarded_templates;

	/*
	 * __construct()
	 *
	 * called upon creation. loads module if possible and attempts to validate
	 */
	function __construct($module)
	{
		// no input, no go
		if($module)
		{
			$this->load($module);
		}
	}

	/*
	 * load()
	 *
	 * attempts to load a module by name.
	 */
	private function load($module)
	{
		global $db;

		// input is necessary
		if($module)
		{
			$this->base_name = $module;

			$this_info = $this->get_info();

			if($this_info && is_array($this_info))
			{
				// validate and store data
				$this->valid = true;
				$this->name = $this_info['name'];
				$this->description = $this_info['description'];

				// if no author is specified assume this addon is default
				if(!$this_info['author'])
				{
					$this_info['author'] = 'Wildcard';
				}
				if(!$this_info['author_site'])
				{
					$this_info['author_site'] = 'http://wildcardsearch.github.com/Advanced-Sidebox';
				}

				$this->author = $this_info['author'];
				$this->author_site = $this_info['author_site'];

				$this->wrap_content = $this_info['wrap_content'];

				$this->settings = $this_info['settings'];
				$this->discarded_settings = $this_info['discarded_settings'];

				$this->templates = $this_info['templates'];
				$this->discarded_templates = $this_info['discarded_templates'];

				// version control
				$this->version = $this_info['version'];
				$this->old_version = $this->get_cache_version();

				// if this module needs to be upgraded . . .
				if(version_compare($this->old_version, $this->version, '<') || $this->old_version == '' || $this->old_version == 0)
				{
					// get-r-done
					$this->upgrade();
				}
				else
				{
					// otherwise mark upgrade status
					$this->is_installed = true;
					$this->is_upgraded = true;
				}

				$this->has_settings = is_array($this->settings);
			}
		}
	}

	/*
	 * install()
	 *
	 * install templates if they exist to allow the add-on module to function correctly
	 */
	private function install($no_cleanup = false)
	{
		global $db;

		// already installed? unless $no_cleanup is specifically asked for . . .
		if($this->is_installed && !$no_cleanup)
		{
			// . . . remove the leftovers before installing
			$status = $this->uninstall();
		}

		// if there are templates . . .
		if(is_array($this->templates))
		{
			// loop through them
			foreach($this->templates as $template)
			{
				$query = $db->simple_select('templates', '*', "title='{$template['title']}'");

				// if it exists, update
				if($db->num_rows($query) == 1)
				{
					$status = $db->update_query("templates", $template, "title='{$template['title']}'");
				}
				else
				{
					// if not, create a new template
					$status = $db->insert_query("templates", $template);
				}

				if(!$status)
				{
					$error = true;
				}
			}
		}

		return $error;
	}

	/*
	 * uninstall()
	 *
	 * remove any templates used by the module and clean up any boxes created using this add-on module
	 *
	 * @param - $no_cleanup, when true instructs the method to leave any side boxes that use this module behind when uninstalling. this is useful for when we want to upgrade an add-on without losing admin's work
	 */
	public function uninstall($no_cleanup = false)
	{
		global $db;

		// installed?
		if($this->is_installed)
		{
			$this->unset_cache_version();

			// if there are templates . . .
			if(is_array($this->templates))
			{
				// remove them all
				foreach($this->templates as $template)
				{
					$status = $db->query("DELETE FROM " . TABLE_PREFIX . "templates WHERE title='{$template['title']}'");
				}

				if(!$status)
				{
					$error = true;
				}

				// unless specifically asked not to, delete any boxes that use this module
				if(!$no_cleanup)
				{
					$this->remove_children();
				}
			}
		}

		return $error;
	}

	/*
	 * upgrade()
	 *
	 * called upon addon version change to verify module's templates/settings
	 * discarded templates and ACP settings (from pre-1.4) are removed
	 */
	private function upgrade()
	{
		global $db;

		// don't waste time if everything is in order
		if(!$this->is_upgraded)
		{
			$this->unset_cache_version();

			// if there are settings left over from a previous installation . . .
			if(is_array($this->discarded_settings))
			{
				// delete them all
				foreach($this->discarded_settings as $setting)
				{
					$status = $db->query("DELETE FROM " . TABLE_PREFIX . "settings WHERE name='{$setting}'");
				}

				if(!$status)
				{
					$error = true;
				}
			}

			// if any templates were dropped in this version
			if(is_array($this->discarded_templates))
			{
				// delete them
				foreach($this->discarded_templates as $template)
				{
					$status = $db->query("DELETE FROM " . TABLE_PREFIX . "templates WHERE title='{$template}'");

					if(!$status)
					{
						$error = true;
					}
				}
			}

			// now install the updated module ($no_cleanup = true signifies no uninstall first)
			$this->install(true);

			// update the version cache and the upgrade is complete
			$this->is_upgraded = $this->set_cache_version();
			$this->is_installed = true;

			return $error;
		}
	}

	/*
	 * remove()
	 *
	 * uninstalls (if necessary) and physically deletes the module from the server
	 */
	public function remove()
	{
		// make sure no trash is left behind
		$this->uninstall();

		// nuke it
		$filename = ADV_SIDEBOX_MODULES_DIR . "/" . $this->base_name . '.php';
		@unlink($filename);

		return !file_exists($filename);
	}

	/*
	 * get_cache_version()
	 *
	 * version control derived from the work of pavemen in MyBB Publisher
	 */
	private function get_cache_version()
	{
		global $cache, $mybb, $db;

		// get currently installed version, if there is one
		$wildcard_plugins = $cache->read('wildcard_plugins');

		if(is_array($wildcard_plugins))
		{
			return $wildcard_plugins['versions']['adv_sidebox_' . $this->base_name];
		}
		return 0;
	}

	/*
	 * set_cache_version()
	 *
	 * version control derived from the work of pavemen in MyBB Publisher
	 */
	private function set_cache_version()
	{
		global $cache;

		//update version cache to latest
		$wildcard_plugins = $cache->read('wildcard_plugins');
		$wildcard_plugins['versions']['adv_sidebox_' . $this->base_name] = $this->version;
		$cache->update('wildcard_plugins', $wildcard_plugins);

		return true;
	}

	/*
	 * unset_cache_version()
	 *
	 * version control derived from the work of pavemen in MyBB Publisher
	 */
	private function unset_cache_version()
	{
		global $cache;

		$wildcard_plugins = $cache->read('wildcard_plugins');
		unset($wildcard_plugins['versions']['adv_sidebox_' . $this->base_name]);
		$cache->update('wildcard_plugins', $wildcard_plugins);

		return true;
	}

	/*
	 * get_info()
	 *
	 * gather information from the module
	 */
	private function get_info()
	{
		if($this->base_name)
		{
			$module = $this->base_name;

			// if there is a valid module file . . .
			if(file_exists(ADV_SIDEBOX_MODULES_DIR . "/" . $module . ".php"))
			{
				// require the module for inspection/info
				require_once ADV_SIDEBOX_MODULES_DIR . "/" . $module . ".php";

				// if the info function exists . . .
				if(function_exists($module . '_asb_info'))
				{
					// get the data
					$info_function = $module . '_asb_info';
					return $info_function();
				}
			}
		}
		return false;
	}

	/*
	 * build_template()
	 *
	 * runs template building code for the current module referenced by this object
	 */
	public function build_template($settings, $template_variable, $width)
	{
		$module = $this->base_name;

		// if the file is intact . . .
		if(file_exists(ADV_SIDEBOX_MODULES_DIR . "/" . $module . ".php"))
		{
			// . . . run the module's template building code.
			require_once ADV_SIDEBOX_MODULES_DIR . "/" . $module . ".php";

			$build_template_function = $this->base_name . '_asb_build_template';

			if(function_exists($build_template_function))
			{
				return $build_template_function($settings, $template_variable, $width);
			}
		}
	}

	/*
	 * build_table_row()
	 *
	 * ACP module management page function to build a table row for the current sidebox object
	 *
	 * @param - $this_table must be a valid object of the Table class
	 */
	public function build_table_row($this_table)
	{
		global $mybb, $lang;

		if(!$lang->adv_sidebox)
		{
			$lang->load('adv_sidebox');
		}

		// valid table object?
		if($this_table instanceof Table)
		{
			// name
			$this_table->construct_cell($this->name);

			// description
			$this_table->construct_cell($this->description);

			// options popup
			$popup = new PopupMenu('module_' . $this->base_name, $lang->adv_sidebox_options);

			// delete
			$popup->add_item($lang->adv_sidebox_delete, ADV_SIDEBOX_URL . '&amp;action=delete_addon&amp;addon=' . $this->base_name, 'return confirm(\'' . $lang->adv_sidebox_modules_del_warning . '\');');

			// popup cell
			$this_table->construct_cell($popup->fetch(), array("width" => '10%'));

			// finish row
			$this_table->construct_row();
		}
	}

	/*
	 * get_settings()
	 *
	 * handler for $this->settings
	 */
	public function get_settings()
	{
		return $this->settings;
	}
}

/*
 * sidebox/addon/custom_box controller/wrapper
 */
class Sidebox_handler
{
	public $left_boxes;
	public $right_boxes;

	public $sideboxes;
	public $boxes_to_show = false;
	private $used_box_types;

	private $script;
	public $script_base_name;
	public $all_scripts;

	private $users_groups;

	public $box_types;

	public $addons;
	private $addons_used;

	public $custom;

	/*
	 * __construct()
	 *
	 * called upon object creation constructs a new handler and attempts to load all necessary objects and properties
	 *
	 * @param - $script is a string containing the active MyBB PHP script filename (or in some cases a shortened psuedonym) and controls sorting within the handler object
	 * @param - $acp allows the handler to avoid wasted execution when called for the ACP
	 */
	public function __construct($script = '', $acp = false)
	{
		// make sure the script is in a format that works in all classes
		$this->process_script($script);

		// attempt to load the handler
		$this->load($acp);
	}

	/*
	 * load()
	 *
	 * attempts to load all side boxes, addons, custom_boxes and establish properties to be used by ASB and ASB module functions
	 *
	 * @param - $acp, if true, will avoid wasted execution when in outside ACP by only loading necessary side boxes and modules and/or custom boxes for that script
	 */
	private function load($acp = false)
	{
		global $db, $mybb;

		$this->all_scripts = array();
		$known_scripts = array("index", "forumdisplay", "showthread", "member", "memberlist", "showteam", "stats");

		// loop through all the known scripts
		foreach($known_scripts as $script)
		{
			// and if there is a maching setting in ACP (that is on)
			if($mybb->settings['adv_sidebox_on_' . $script])
			{
				// add the script to the master list-- this controls which scripts the entire plugin has to work with both inside this object and also in acp_functions.php and adv_sidebox_functions.php (no script filtering is necessary anywhere else)
				$this->all_scripts[] = $script;
			}
		}

		// an exception
		if($mybb->settings['adv_sidebox_portal_replace'])
		{
			$this->all_scripts[] = "portal";
		}

		// load everything detected (sideboxes will be filtered by script if applicable)
		$this->get_users_groups($acp);
		$this->boxes_to_show = $this->get_all_sideboxes($acp);
		$this->get_all_addons($acp);
		$this->get_all_custom_boxes();

		// just produce a list of all possible box types-- used by ACP functions
		$this->compile_box_types();
	}

	/*
	 * get_all_sideboxes()
	 *
	 * retrieve all sideboxes from the db (filtered by script if applicable)
	 *
	 * @param - $acp if true prevents group filtering (unnecessary and counter-intuitive in ACP)
	 */
	private function get_all_sideboxes($acp = false)
	{
		global $db;

		$this->used_box_types = array();
		$this->addons_used = array();
		$this->sideboxes = array();
		$where = '';

		// filter by script if applicable
		if($this->script_base_name && in_array($this->script_base_name, $this->all_scripts))
		{
			$where = "show_on_" . $this->script_base_name . "='1'";
		}

		// Look for all sideboxes (if any)
		$query = $db->simple_select('sideboxes', '*', $where, array("order_by" => 'position, display_order', "order_dir" => 'ASC'));

		$can_view = false;

		// if there are sideboxes . . .
		if($db->num_rows($query) > 0)
		{
			// loop through them all
			while($this_box = $db->fetch_array($query))
			{
				// attempt to load the side box
				$test_box = new Sidebox($this_box);

				$can_view = false;

				// if we aren't in ACP . . .
				if(!$acp)
				{
					// if the side box has group permissions . . .
					if(is_array($test_box->groups_array))
					{
						// loop through them
						foreach($test_box->groups_array as $gid)
						{
							// if we come across an 'all' entry then the user is good to go no matter their group
							if($gid == 'all')
							{
								$can_view = true;
								break;
							}

							// if the current user is a member of multiple groups . . .
							if(is_array($this->users_groups))
							{
								// check the current group against that list . . .
								if(in_array($gid, $this->users_groups))
								{
									// . . . and if it is found mark them to the good
									$can_view = true;
									break;
								}
							}
						}
					}
					else
					{
						// if the box has a non-array then show it to all (to catch upgraded side boxes from < 1.4)
						$can_view = true;
					}
				}
				else
				{
					// if in ACP load all side boxes
					$can_view = true;
				}

				// if permission is granted . . .
				if($can_view)
				{
					// add the side box
					$this->sideboxes[$test_box->get_id()] = $test_box;

					// if not in ACP
					if(!$acp)
					{
						// save wasted execution by saving info for all used modules/custom_boxes
						$box_type = $test_box->get_box_type();
						$this->used_box_types[$test_box->get_id()] = $box_type;
						$this->addons_used[$box_type] = true;
					}
				}
			}

			// true indicates that there is content to show
			return true;
		}
	}

	/*
	 * get_all_addons()
	 *
	 * load all addon modules
	 */
	private function get_all_addons($acp = false)
	{
		$this->addons = array();

		// if we aren't in ACP . . .
		if(!$acp)
		{
			// and there are used modules
			if(is_array($this->addons_used))
			{
				// loop through the modules that are being used
				foreach($this->addons_used as $module => $throw_away)
				{
					// and load them
					$this->addons[$module] = new Addon_type($module);
				}
			}
		}
		else
		{
			// otherwise load all detected modules
			foreach(new DirectoryIterator(ADV_SIDEBOX_MODULES_DIR) as $file)
			{
				// skip directories, '.' '..' and non PHP files
				if($file->isDot() || $file->isDir() || $file->getExtension() != 'php') continue;

				// extract the base_name from the module filename
				$filename = $file->getFilename();
				$module = substr($filename, 0, strlen($filename) - 4);

				// atempt to load the module
				$this->addons[$module] = new Addon_type($module);
			}
		}
	}

	/*
	 * build_addon_language()
	 *
	 * probably overly complicated but this method produces grammatically correct language to describe the state of addons in the plugin
	 */
	public function build_addon_language()
	{
		global $lang;

		if(!$lang->adv_sidebox)
		{
			$lang->load('adv_sidebox');
		}

		$total_addons = count($this->addons);

		// if there are any modules . . .
		if($total_addons)
		{
			// more than 1?
			if($total_addons > 1)
			{
				// plural language
				$module_info .= $lang->sprintf($lang->adv_sidebox_module_info_good_count, $lang->adv_sidebox_are, $total_addons, $lang->adv_sidebox_module_plural);
			}
			else
			{
				// singular
				$module_info .= $lang->sprintf($lang->adv_sidebox_module_info_good_count, $lang->adv_sidebox_is, $total_addons, $lang->adv_sidebox_module_singular);
			}

			$module_info .= $lang->adv_sidebox_module_all_good;
		}
		else
		{
			// no modules
			$module_info .= $lang->adv_sidebox_no_modules_detected;
		}

		return $module_info;
	}

	/*
	 * get_all_custom_boxes()
	 *
	 * retrieve all the custom box type from the db and store them as an array of objects
	 */
	private function get_all_custom_boxes()
	{
		global $db;

		$this->custom = array();

		$query = $db->simple_select('custom_sideboxes');

		// if ther are custom boxes . . .
		if($db->num_rows($query) > 0)
		{
			// fetch them
			while($box = $db->fetch_array($query))
			{
				// and attempt to load each custom box type
				$this->custom['asb_custom_' . $box['id']] = new Custom_type($box);
			}
		}
	}

	/*
	 * build_all_templates()
	 *
	 * executes build_template methods for all used custom box types and addon modules allowing plugins to do the same
	 */
	public function build_all_templates()
	{
		global $plugins, $mybb;

		$this->left_boxes = '';
		$this->right_boxes = '';

		// don't waste time if there are no sideboxes to build templates for
		if($this->boxes_to_show && is_array($this->used_box_types))
		{
			// create this array to catch any sidebox types that aren't custom or addon, these will be added by plugins (if that ever happens :p )
			$box_types = array();

			// loop through all used types
			foreach($this->used_box_types as $this_box => $module)
			{
				// get the template variable
				$this_template_variable = $this->sideboxes[$this_box]->build_template_variable();

				// get the correct width to send (1 = right, 0 = left)
				if($this->sideboxes[$this_box]->get_position())
				{
					$this_column_width = (int) $mybb->settings['adv_sidebox_width_right'];
					$this_position = 1;
				}
				else
				{
					$this_column_width = (int) $mybb->settings['adv_sidebox_width_left'];
					$this_position = 0;
				}

				// if this type was created by an addon module . . .
				if($this->addons[$module]->valid)
				{
					// if this side box doesn't have any settings, but the add-on module it was derived from does . . .
					if($this->sideboxes[$this_box]->has_settings == false && $this->addons[$module]->has_settings)
					{
						// . . . this side box hasn't been upgraded to the new on-board settings system. Use the settings (and values) from the add-on module as default settings
						$this->sideboxes[$this_box]->set_settings($this->addons[$module]->get_settings());
					}

					// build the template
					// pass settings, template variable name and column width
					$result = $this->addons[$module]->build_template($this->sideboxes[$this_box]->get_settings(), $this_template_variable, $this_column_width);
				}
				// or if it is a custom static box . . .
				elseif($this->custom[$module]->valid)
				{
					// build the custom box template
					$result = $this->custom[$module]->build_template($this_template_variable);
				}
				else
				{
					// otherwise it is an external plugin-created type (or it is invalid)
					$box_types[$module] = true;
				}

				// this hook will allow a plugin to process its custom box type for display (you will first need to hook into adv_sidebox_box_types to add the box
				$plugins->run_hooks('adv_sidebox_output_end', $box_types, $result);

				if($result || (!$result && $mybb->settings['adv_sidebox_show_empty_boxes']))
				{
					$content = $this->sideboxes[$this_box]->get_content();

					if($content)
					{
						if($this_position)
						{
							$this->right_boxes .= $content;
						}
						else
						{
							$this->left_boxes .= $content;
						}
					}
				}
			}
		}
	}

	/*
	 * get_users_groups()
	 *
	 * gets all the groups the current user belongs to
	 *
	 * @param - $acp if true prevents group filtering (unnecessary and counter-intuitive in ACP)
	 */
	private function get_users_groups($acp = false)
	{
		global $mybb;

		$this->users_groups = array();

		// if not in ACP
		if(!$acp)
		{
			// and not a guest . . .
			if($mybb->user['uid'] > 0)
			{
				// add the main group
				if($mybb->user['usergroup'])
				{
					$this->users_groups[] = (int) $mybb->user['usergroup'];
				}

				// add any additional groups
				if($mybb->user['additionalgroups'])
				{
					$additional = array();
					$additional = explode(",", $mybb->user['additionalgroups']);

					// if more than one . . .
					if(is_array($additional))
					{
						// merge the arrays
						$this->users_groups = array_merge($this->users_groups, $additional);
					}
					else
					{
						// otherwise just add an index to the existing array
						$this->users_groups[] = (int) $additional;
					}
				}
			}
			else
			{
				$this->users_groups[] = 'guests';
			}
		}
	}

	/*
	 * compile_box_types()
	 *
	 * gather all available addon, custom and plugin box_types for internal and external use
	 */
	private function compile_box_types()
	{
		global $plugins;

		$this->box_types = array();

		// get user-defined static types
		if(is_array($this->custom))
		{
			foreach($this->custom as $module)
			{
				$this->box_types[$module->get_base_name()] = $module->get_name();
			}
		}

		// get addon modules
		if(is_array($this->addons))
		{
			foreach($this->addons as $module)
			{
				$this->box_types[$module->get_base_name()] = $module->get_name();
			}
		}

		// get all the plugin types
		$plugins->run_hooks('adv_sidebox_box_types', $this->box_types);

		$box_types_lowercase = array_map('strtolower', $this->box_types);

		array_multisort($box_types_lowercase, SORT_ASC, SORT_STRING, $this->box_types);
	}

	/*
	 * process_script()
	 *
	 * ensure that the script properties are in a valid format
	 *
	 * @param - $script is a string that either contains the active PHP script's filename or a shortened psuedonym
	 */
	private function process_script($script = '', $acp = false)
	{
		if(!$script && !$acp)
		{
			$script = THIS_SCRIPT;
		}

		// if no script then we don't need to filter . . .
		if($script)
		{
			// otherwise check it
			switch($script)
			{
				case 'index':
				case 'index.php':
					$this->script = 'index.php';
					$this->script_base_name = 'index';
					break;
				case 'forum':
				case 'forumdisplay.php':
					$this->script = 'forumdisplay.php';
					$this->script_base_name = 'forumdisplay';
					break;
				case 'thread':
				case 'showthread.php':
					$this->script = 'showthread.php';
					$this->script_base_name = 'showthread';
					break;
				case 'member':
				case 'member.php':
					$this->script = 'member.php';
					$this->script_base_name = 'member';
					break;
				case 'memberlist':
				case 'memberlist.php':
					$this->script = 'memberlist.php';
					$this->script_base_name = 'memberlist';
					break;
				case 'showteam':
				case 'showteam.php':
					$this->script = 'showteam.php';
					$this->script_base_name = 'showteam';
					break;
				case 'stats':
				case 'stats.php':
					$this->script = 'stats.php';
					$this->script_base_name = 'stats';
					break;
				case 'portal':
				case 'portal.php':
					$this->script = 'portal.php';
					$this->script_base_name = 'portal';
					break;
			}
		}
	}

	/*
	 * build_peekers()
	 *
	 * automates the process of hiding inactive addon settings in the edit box page
	 */
	public function build_peekers($more_peekers = '')
	{
		// if there are addons (custom boxes can't contain settings)
		if(is_array($this->addons))
		{
			// loop through them
			foreach($this->addons as $module)
			{
				// if the module has settings . . .
				if($module->has_settings)
				{
					$module_settings = $module->get_settings();

					// if the setting seem valid . . .
					if(is_array($module_settings))
					{
						// loop through them
						foreach($module_settings as $setting)
						{
							// attach an event handler to only show these settings when appropriate
							$element_name = "{$setting['name']}";
							$element_id = "setting_{$setting['name']}";
							$peekers .= '
			var peeker = new Peeker
			(
				$("box_type_select"),
				$("' . $element_id  . '"),
				/' . $module->get_base_name() . '/,
				false
			);';
						}
					}
				}
			}
		}

		// if there were peekers to show then return content
		if($peekers)
		{
			return '<script type="text/javascript" src="./jscripts/peeker.js"></script>
	<script type="text/javascript">
		Event.observe
		(
			window,
			"load",
			function()
			{
				' . $peekers . $more_peekers . '
			}
		);
	</script>';
		}
	}

	/*
	 * build_settings()
	 *
	 * in the edit/add sidebox page all settings for all addon modules are created on load (but hidden until called)
	 *
	 * @param - $this_form is a valid object of class DefaultForm
	 * @param - $this_form_container is a valid object of class DefaultFormContainer
	 * @param - $this_box is an integer representing the currently loaded box (edit) or 0 if adding a new sidebox
	 */
	public function build_settings($this_form, $this_form_container, $this_box)
	{
		// if there are addons
		if(is_array($this->addons))
		{
			// loop through them
			foreach($this->addons as $module)
			{
				// if this module as settings
				if($module->has_settings)
				{
					$module_settings = $module->get_settings();

					// if the settings seem valid . . .
					if(is_array($module_settings))
					{
						// loop through them
						foreach($module_settings as $setting)
						{
							// create each element with unique id and name properties
							$options = "";
							$type = explode("\n", $setting['optionscode']);
							$type[0] = trim($type[0]);
							$element_name = "{$setting['name']}";
							$element_id = "setting_{$setting['name']}";

							if($this_box)
							{
								// if editing and the current box uses this module . . .
								if($this->sideboxes[$this_box]->get_box_type() == $module->get_base_name())
								{
									$sidebox_settings = $this->sideboxes[$this_box]->get_settings();
									// if there are settings (values mostly). . .
									if(is_array($sidebox_settings))
									{
										// get the values
										foreach($sidebox_settings as $this_box_setting)
										{
											// if the current setting has a stored value
											if($this_box_setting['name'] == $setting['name'])
											{
												// store to be included in the produced HTML value property
												$setting['value'] = $this_box_setting['value'];
											}
										}
									}
								}
							}

							// prepare labels
							$this_label = '<strong>' . $setting['title'] . '</strong>';
							$this_desc = '<i>' . $setting['description'] . '</i>';

							// sort by type
							if($type[0] == "text" || $type[0] == "")
							{
								$this_form_container->output_row($this_label, $this_desc, $this_form->generate_text_box($element_name, $setting['value'], array('id' => $element_id)), $element_name, array("id" => $element_id));
							}
							else if($type[0] == "textarea")
							{
								$this_form_container->output_row($this_label, $this_desc, $this_form->generate_text_area($element_name, $setting['value'], array('id' => $element_id)), $element_name, array('id' => $element_id));
							}
							else if($type[0] == "yesno")
							{
								$this_form_container->output_row($this_label, $this_desc, $this_form->generate_yes_no_radio($element_name, $setting['value'], true, array('id' => $element_id.'_yes', 'class' => $element_id), array('id' => $element_id.'_no', 'class' => $element_id)), $element_name, array('id' => $element_id));
							}
							else if($type[0] == "onoff")
							{
								$this_form_container->output_row($this_label, $this_desc, $this_form->generate_on_off_radio($element_name, $setting['value'], true, array('id' => $element_id.'_on', 'class' => $element_id), array('id' => $element_id.'_off', 'class' => $element_id)), $element_name, array('id' => $element_id));
							}
							else if($type[0] == "cpstyle")
							{
								$dir = @opendir(MYBB_ROOT.$config['admin_dir']."/styles");
								while($folder = readdir($dir))
								{
									if($file != "." && $file != ".." && @file_exists(MYBB_ROOT.$config['admin_dir']."/styles/$folder/main.css"))
									{
										$folders[$folder] = ucfirst($folder);
									}
								}
								closedir($dir);
								ksort($folders);

								$this_form_container->output_row($this_label, $this_desc, $this_form->generate_select_box($element_name, $folders, $setting['value'], array('id' => $element_id)), $element_name, array('id' => $element_id));
							}
							else if($type[0] == "language")
							{
								$languages = $lang->get_languages();
								$this_form_container->output_row($this_label, $this_desc, $this_form->generate_select_box($element_name, $languages, $setting['value'], array('id' => $element_id)), $element_name, array('id' => $element_id));
							}
							else if($type[0] == "adminlanguage")
							{
								$languages = $lang->get_languages(1);
								$this_form_container->output_row($this_label, $this_desc, $this_form->generate_select_box($element_name, $languages, $setting['value'], array('id' => $element_id)), $element_name, array('id' => $element_id));
							}
							else if($type[0] == "passwordbox")
							{
								$this_form_container->output_row($this_label, $this_desc, $this_form->generate_password_box($element_name, $setting['value'], array('id' => $element_id)), $element_name, array('id' => $element_id));
							}
							else if($type[0] == "php")
							{
								$setting['optionscode'] = substr($setting['optionscode'], 3);
								eval("\$setting_code = \"".$setting['optionscode']."\";");
							}
							else
							{
								for($i=0; $i < count($type); $i++)
								{
									$optionsexp = explode("=", $type[$i]);
									if(!$optionsexp[1])
									{
										continue;
									}
									$title_lang = "setting_{$setting['name']}_{$optionsexp[0]}";
									if($lang->$title_lang)
									{
										$optionsexp[1] = $lang->$title_lang;
									}

									if($type[0] == "select")
									{
										$option_list[$optionsexp[0]] = htmlspecialchars_uni($optionsexp[1]);
									}
									else if($type[0] == "radio")
									{
										if($setting['value'] == $optionsexp[0])
										{
											$option_list[$i] = $this_form->generate_radio_button($element_name, $optionsexp[0], htmlspecialchars_uni($optionsexp[1]), array('id' => $element_id.'_'.$i, "checked" => 1, 'class' => $element_id));
										}
										else
										{
											$option_list[$i] = $this_form->generate_radio_button($element_name, $optionsexp[0], htmlspecialchars_uni($optionsexp[1]), array('id' => $element_id.'_'.$i, 'class' => $element_id));
										}
									}
									else if($type[0] == "checkbox")
									{
										if($setting['value'] == $optionsexp[0])
										{
											$option_list[$i] = $this_form->generate_check_box($element_name, $optionsexp[0], htmlspecialchars_uni($optionsexp[1]), array('id' => $element_id.'_'.$i, "checked" => 1, 'class' => $element_id));
										}
										else
										{
											$option_list[$i] = $this_form->generate_check_box($element_name, $optionsexp[0], htmlspecialchars_uni($optionsexp[1]), array('id' => $element_id.'_'.$i, 'class' => $element_id));
										}
									}
								}
								if($type[0] == "select")
								{
									$this_form_container->output_row($this_label, $this_desc, $this_form->generate_select_box($element_name, $option_list, $setting['value'], array('id' => $element_id)), $element_name, array('id' => $element_id));
								}
								else
								{
									$setting_code = implode("<br />", $option_list);
								}
								$option_list = array();
							}
							// Do we have a custom language variable for this title or description?
							$title_lang = "setting_".$setting['name'];
							$desc_lang = $title_lang."_desc";
							if($lang->$title_lang)
							{
								$setting['title'] = $lang->$title_lang;
							}
							if($lang->$desc_lang)
							{
								$setting['description'] = $lang->$desc_lang;
							}
						}
					}
				}
			}
		}
	}
}

?>
