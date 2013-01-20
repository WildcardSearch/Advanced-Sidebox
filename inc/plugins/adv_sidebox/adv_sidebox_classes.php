<?php
/*
 * This file contains class definitions for the entire project
 *
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright © 2013 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * Check out this project on GitHub: https://github.com/WildcardSearch/Advanced-Sidebox
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
	public $id;
	public $display_name;
	public $box_type;
	public $position = 0;
	public $display_order;
	public $stereo = false;
	public $wrap_content = false;
	public $wrapped_content = '';
	public $content;

	public $valid = false;

	public $show_on_index = false;
	public $show_on_forumdisplay = false;
	public $show_on_showthread = false;
	public $show_on_portal = false;

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

		// if nothing loaded then the sidebox is new (and unavailable for admin to use)
		if($this->content)
		{
			$this->valid = true;
		}
	}

	/*
	 * load()
	 *
	 * attempts to load the sidebox's data from the db, or if given no data create a blank object
	 *
	 * @param - $data can be an array fetched from the db or
	 *						a valid ID # (__construct will feed 0 if no data is given)
	 */
	function load($data)
	{
		global $db, $collapsed;

		// if data isn't an array, try it as an ID
		if(!is_array($data))
		{
			//if the ID is 0 then there is nothing to go on
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
			// good id? then store the data in our object
			$this->id = (int) $data['id'];
			$this->display_name = $data['display_name'];
			$this->box_type = $data['box_type'];
			$this->position = (int) $data['position'];
			$this->display_order = (int) $data['display_order'];
			$this->stereo = (int) $data['stereo'];
			$this->wrap_content = (int) $data['wrap_content'];

			$this->show_on_index = $data['show_on_index'];
			$this->show_on_forumdisplay = $data['show_on_forumdisplay'];
			$this->show_on_showthread = $data['show_on_showthread'];
			$this->show_on_portal = $data['show_on_portal'];

			$this->stereo = $data['stereo'];

			// stereo boxes get a little special consideration
			if($this->stereo)
			{
				// split the template variable into two channels
				if($this->position)
				{
					$this->content = '{$' . $this->box_type . '_r}';
				}
				else
				{
					$this->content = '{$' . $this->box_type . '_l}';
				}
			}
			else
			{
				// otherwise just build a template variable for this sidebox
				$this->content = '{$' . $this->box_type . '}';
			}
			
			// In most cases we will be wrapping the boxes with a header and expander
			if($this->wrap_content)
			{
				// Check if this sidebox is either expanded or collapsed and hide it as necessary.
				$expdisplay = '';
				$collapsed_name = 'asb_' . $this->box_type . '_' . $this->id . '_c';
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
				
				$this->wrapped_content = '
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
	<thead>
		<tr>
			<td class="thead"><div class="expcolimage"><img src="{$theme[\'imgdir\']}/' . $expcolimage . '" id="asb_' . $this->box_type . '_' . $this->id . '_img" class="expander" alt="' . $expaltext . '" title="' . $expaltext . '" /></div><strong>' . $this->display_name . '</strong></td>
		</tr>
	</thead>
	<tbody style="' . $expdisplay . '" id="asb_' . $this->box_type . '_' . $this->id . '_e">' . $this->content . '
	</tbody>
</table><br />';
			}
		}
	}

	/*
	 * save()
	 *
	 * can be called upon any existing sidebox
	 */
	function save()
	{
		global $db;

		// set up db array
		$this_box = array(
			"display_name"				=>	$db->escape_string($this->display_name),
			"box_type"						=>	$db->escape_string($this->box_type),
			"position"							=>	(int) $this->position,
			"display_order"					=> 	(int) $this->display_order,
			"stereo"							=>	(int) $this->stereo,
			"wrap_content"					=>	(int) $this->wrap_content,
			"content"							=>	$db->escape_string($this->content),
			"show_on_index"				=>	(int) $this->show_on_index,
			"show_on_forumdisplay"	=>	(int) $this->show_on_forumdisplay,
			"show_on_showthread"	=>	(int) $this->show_on_showthread,
			"show_on_portal"				=>	(int) $this->show_on_portal
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
	 * build_table_row()
	 *
	 * can be called on any exisiting sidebox object
	 *
	 * @param - $this_table must be a valid object of class Table
	 */
	function build_table_row($this_table)
	{
		global $mybb, $lang;

		if (!$lang->adv_sidebox)
		{
			$lang->load('adv_sidebox');
		}

		if($this_table instanceof Table)
		{
			// construct the table row.
			$this_table->construct_cell('<a href="' . ADV_SIDEBOX_EDIT_URL . '&amp;mode=' . $mybb->input['mode'] . '&amp;box=' . $this->id . '">' . $this->display_name . '</a>', array("width" => '40%'));
			$this_table->construct_cell($this->build_script_list(), array("width" => '40%'));
			
			$popup = new PopupMenu('box_' . $this->id, 'Options');
			$popup->add_item($lang->adv_sidebox_edit, ADV_SIDEBOX_EDIT_URL . '&amp;mode=' . $mybb->input['mode'] . '&amp;box=' . $this->id);
			$popup->add_item($lang->adv_sidebox_delete, ADV_SIDEBOX_DEL_URL . '&amp;mode=' . $mybb->input['mode'] . '&amp;box=' . $this->id);
			$this_table->construct_cell($popup->fetch(), array("width" => '20%'));
			
			$this_table->construct_row();
		}
	}

	/*
	 * build_script_list()
	 *
	 * builds a comma seperated list of scripts that this sidebox will display on, 'All Scripts' if all, a single name if 1, nothing if none.
	 */
	function build_script_list()
	{
		// if all scripts be brief
		if($this->show_on_index && $this->show_on_forumdisplay && $this->show_on_showthread && $this->show_on_portal)
		{
			return 'All Scripts';
		}
		else
		{
			// otherwise, break it down
			$script_list = array();

			if($this->show_on_index)
			{
				$script_list[] = 'Index';
			}

			if($this->show_on_forumdisplay)
			{
				$script_list[] = 'Forum';
			}

			if($this->show_on_showthread)
			{
				$script_list[] = 'Thread';
			}

			if($this->show_on_portal)
			{
				$script_list[] = 'Portal';
			}
			// return a comma space separated list
			return implode(", ", $script_list);
		}
	}

	/*
	 * remove()
	 *
	 * removes the sidebox from the database
	 */
	function remove()
	{
		// if this is a valid module
		if($this->id)
		{
			global $db;

			// attempt to delete it and return the result
			return $db->query("DELETE FROM " . TABLE_PREFIX . "sideboxes WHERE id='" . (int) $this->id . "'");
		}
	}
}

/*
 * wrapper for modules
 */
class Sidebox_addon
{
	public $base_name = '';
	public $name = '';
	public $description = '';
	public $stereo = false;
	public $wrap_content = false;
	public $valid = false;
	public $module_type;

	public $is_installed = false;

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
	function load($module)
	{
		// input is necessary
		if($module)
		{
			// if the directory exists, it isn't . or .. and it contains a valid module file . . .
			if(is_dir(ADV_SIDEBOX_MODULES_DIR . "/" . $module) && !in_array($module, array(".", "..")) && file_exists(ADV_SIDEBOX_MODULES_DIR . "/" . $module . "/adv_sidebox_module.php"))
			{
				// require the module for inspection/info
				require_once ADV_SIDEBOX_MODULES_DIR . "/" . $module . "/adv_sidebox_module.php";

				// if the info function exists . . .
				if(function_exists($module . '_asb_info'))
				{
					// get the data
					$info_function = $module . '_asb_info';
					$this_info = $info_function();

					// validate and store data
					$this->valid = true;
					$this->base_name = $module;
					$this->name = $this_info['name'];
					$this->description = $this_info['description'];
					$this->wrap_content = $this_info['wrap_content'];

					if($this_info['stereo'])
					{
						$this->stereo = true;
					}

					// if the is_installed() function exists
					if(function_exists($module . '_asb_is_installed'))
					{
						// check whether it is installed and flag it complex
						$is_installed_function = $module . '_asb_is_installed';

						$this->is_installed = $is_installed_function();
						$this->module_type = 'complex';
					}
					else
					{
						// otherwise it is a simple module
						$this->module_type = 'simple';
						$this->is_installed = false;
					}
				}
				else
				{
					// bad module
					$this->valid = false;
				}
			}
			else
			{
				$this->valid = false;
			}
		}
	}

	/*
	 * build_template()
	 *
	 * runs template building code for the current module referenced by this object
	 */
	function build_template()
	{
		// if the files are intact . . .
		if(file_exists(ADV_SIDEBOX_MODULES_DIR . "/" . $this->base_name . "/adv_sidebox_module.php"))
		{
			// . . . run the module's template building code.
			require_once ADV_SIDEBOX_MODULES_DIR . "/" . $this->base_name . "/adv_sidebox_module.php";

			if(function_exists($this->base_name . '_asb_build_template'))
			{
				$build_template_function = $this->base_name . '_asb_build_template';
				$build_template_function();
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
	function build_table_row($this_table)
	{
		global $mybb, $lang;

		if (!$lang->adv_sidebox)
		{
			$lang->load('adv_sidebox');
		}

		$this_table->construct_cell($this->name);
		$this_table->construct_cell($this->description);
		
		$popup = new PopupMenu('module_' . $this->base_name, 'Options');

		// complex modules get install/uninstall links
		if($this->module_type == 'complex')
		{
			// installed?
			if($this->is_installed)
			{
				// uninstall link
				$popup->add_item($lang->adv_sidebox_uninstall, ADV_SIDEBOX_URL . '&amp;action=uninstall_addon&amp;addon=' . $this->base_name);
			}
			else
			{
				// install link
				$popup->add_item($lang->adv_sidebox_install, ADV_SIDEBOX_URL . '&amp;action=install_addon&amp;addon=' . $this->base_name);
			}
		}

		$popup->add_item($lang->adv_sidebox_delete, ADV_SIDEBOX_URL . '&amp;action=delete_addon&amp;addon=' . $this->base_name);
		$this_table->construct_cell($popup->fetch(), array("width" => '10%'));
		
		$this_table->construct_row();
	}

	/*
	 * install()
	 *
	 * access the given modules install routine
	 */
	function install($no_cleanup = false)
	{
		// only complex modules can install/uninstall
		if($this->module_type == 'complex')
		{
			// already installed? unless $no_cleanup is specifically asked for . . .
			if($this->is_installed && !$no_cleanup)
			{
				// . . . remove the leftovers before installing
				$status = $this->uninstall();
			}

			// validate the module
			if(is_dir(ADV_SIDEBOX_MODULES_DIR . "/" . $this->base_name) && !in_array($this->base_name, array(".", "..")) && file_exists(ADV_SIDEBOX_MODULES_DIR . "/" . $this->base_name . "/adv_sidebox_module.php"))
			{
				require_once ADV_SIDEBOX_MODULES_DIR . "/" . $this->base_name . "/adv_sidebox_module.php";

				// and if the install routine exists, run it
				if(function_exists($this->base_name . '_asb_install'))
				{
					$install_function = $this->base_name . '_asb_install';
					$install_function();
				}
			}
		}
	}

	/*
	 * uninstall()
	 *
	 * access the given module's uninstall routine
	 */
	function uninstall()
	{
		// only complex modules can be installed/uninstalled
		if($this->module_type == 'complex')
		{
			// installed?
			if($this->is_installed)
			{
				// validate the module
				if(is_dir(ADV_SIDEBOX_MODULES_DIR . "/" . $this->base_name) && !in_array($this->base_name, array(".", "..")) && file_exists(ADV_SIDEBOX_MODULES_DIR . "/" . $this->base_name . "/adv_sidebox_module.php"))
				{
					require_once ADV_SIDEBOX_MODULES_DIR . "/" . $this->base_name . "/adv_sidebox_module.php";

					// and uninstall it if possible
					if(function_exists($this->base_name . '_asb_uninstall'))
					{
						$uninstall_function = $this->base_name . '_asb_uninstall';
						$uninstall_function();
					}
				}
			}
		}
	}

	/*
	 * remove()
	 *
	 * uninstalls (if necessary) and physically deletes the module from the server
	 */
	function remove()
	{
		// make sure no trash is left behind
		$this->uninstall();

		// nuke it
		my_rmdir_recursive(ADV_SIDEBOX_MODULES_DIR . "/" . $this->base_name);
		rmdir(ADV_SIDEBOX_MODULES_DIR . "/" . $this->base_name);
	}
}

?>
