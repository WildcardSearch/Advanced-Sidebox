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
	public $content;

	public $valid = false;

	public $show_on_index = false;
	public $show_on_forumdisplay = false;
	public $show_on_showthread = false;
	public $show_on_portal = false;
	
	public $groups;
	public $groups_array;
	
	public $settings;
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
			
			// load the group permissions
			$this->groups = $data['groups'];
			
			// if there are specific groups used . . .
			if($this->groups != null)
			{
				// convert them to an array as well
				$this->groups_array = explode(",", $this->groups);
			}

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
			
			// are there settings?
			if($data['settings'])
			{
				// if so decode them
				$this->settings = json_decode($data['settings']);
				
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
			"show_on_portal"				=>	(int) $this->show_on_portal,
			"groups"							=>	$db->escape_string($this->groups),
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
	function remove()
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
	function build_table_row($this_table)
	{
		global $mybb, $lang;

		if(!$lang->adv_sidebox)
		{
			$lang->load('adv_sidebox');
		}

		if($this_table instanceof Table)
		{
			// construct the table row
			
			// name (edit link)
			$this_table->construct_cell('<a href="' . ADV_SIDEBOX_EDIT_URL . '&amp;mode=' . $mybb->input['mode'] . '&amp;box=' . $this->id . '">' . $this->display_name . '</a>', array("width" => '30%'));
			
			// scripts
			$this_table->construct_cell($this->build_script_list(), array("width" => '30%'));
			
			// prepare group info
			if($this->groups == 'all')
			{
				$groups = $lang->adv_sidebox_all_groups;
			}
			else
			{
				$groups = $this->groups;
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
	 * build_script_list()
	 *
	 * builds a comma seperated list of scripts that this sidebox will display on, 'All Scripts' if all, a single name if 1, nothing if none.
	 */
	function build_script_list()
	{
		global $lang;
		
		if(!$lang->adv_sidebox)
		{
			$lang->load('adv_sidebox');
		}
		
		// if all scripts be brief
		if($this->show_on_index && $this->show_on_forumdisplay && $this->show_on_showthread && $this->show_on_portal)
		{
			return $lang->adv_sidebox_all;
		}
		else
		{
			// otherwise, break it down
			$script_list = array();

			if($this->show_on_index)
			{
				$script_list[] = $lang->adv_sidebox_index;
			}

			if($this->show_on_forumdisplay)
			{
				$script_list[] = $lang->adv_sidebox_forum;
			}

			if($this->show_on_showthread)
			{
				$script_list[] = $lang->adv_sidebox_thread;
			}

			if($this->show_on_portal)
			{
				$script_list[] = $lang->adv_sidebox_portal;
			}
			// return a comma space separated list
			
			$return_val = implode(", ", $script_list);
			
			// if there are scripts . . .
			if($return_val)
			{
				// return them
				return $return_val;
			}
			else
			{
				// otherwise the side box is inactive so mark it
				return '<span style="color: red;"><strong>Deactivated</strong></span>';
			}
		}
	}
	
	/*
	 * build_wrapped_content()
	 *
	 * if a sidebox's wrap_content property is true it will be 'wrapped' in a table with a header and expander
	 */
	function build_wrapped_content()
	{
		global $collapsed;
		
		// Check if this sidebox is either expanded or collapsed and hide it as necessary.
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
		
		// prevents empty tbody section in custom box
		// when user do not provide any content for it
		if(!$this->content)
		{
			// user want it empty ? let it be.
			$this->content = '
	<tr>
		<td class="trow1"></td>
	</tr>';
		}
		
		return '
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
	<thead>
		<tr>
			<td class="thead"><div class="expcolimage"><img src="{$theme[\'imgdir\']}/' . $expcolimage . '" id="' . $this->box_type . '_' . $this->id . '_img" class="expander" alt="' . $expaltext . '" title="' . $expaltext . '" /></div><strong>' . $this->display_name . '</strong>
			</td>
		</tr>
	</thead>
	<tbody style="' . $expdisplay . '" id="' . $this->box_type . '_' . $this->id . '_e">
		' . $this->content . '
	</tbody>
</table><br />';
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
	public $author;
	public $author_site;
	
	public $stereo = false;
	public $wrap_content = false;
	public $valid = false;
	public $module_type;
	
	public $settings;
	public $templates;

	public $is_installed = false;
	public $is_upgraded = false;
	public $old_version;
	public $version;
	public $discarded_settings;
	public $discarded_templates;

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
		global $db;
		
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
					
					// if no author is specified assume this addon is default
					if(!$this_info['author'])
					{
						$this_info['author'] = 'Wildcard';
					}
					if(!$this_info['author_site'])
					{
						$this_info['author_site'] = 'https://github.com/WildcardSearch/Advanced-Sidebox';
					}

					$this->author = $this_info['author'];
					$this->author_site = $this_info['author_site'];
					
					$this->wrap_content = $this_info['wrap_content'];
					$this->stereo = $this_info['stereo'];
					
					$this->settings = $this_info['settings'];
					$this->discarded_settings = $this_info['discarded_settings'];
					
					$this->templates = $this_info['templates'];
					$this->discarded_templates = $this_info['discarded_templates'];
					
					// if this addon needs templates(s) to work, it is considered complex
					if(is_array($this->templates))
					{
						$this->module_type = 'complex';
						
						// if the first template seems valid . . .
						if($this->templates[0]['title'])
						{
							// see if it exists
							$query = $db->simple_select('templates', '*', "title='{$this->templates[0]['title']}'");
							
							// if so then mark this addon as installed
							if($db->num_rows($query) == 1)
							{
								$this->is_installed = true;
							}
						}
					}
					else
					{
						// otherwise it is a simple module
						$this->module_type = 'simple';
						$this->is_installed = false;
						$this->is_upgraded = true;
					}
					
					if(is_array($this->settings))
					{
						$this->has_settings = true;
					}
					
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
						$this->is_upgraded = true;
					}
				}
			}
		}
	}

	/*
	 * install()
	 *
	 * access the given module's install routine
	 */
	function install($no_cleanup = false)
	{
		global $db;
		
		// already installed? unless $no_cleanup is specifically asked for . . .
		if($this->is_installed && !$no_cleanup)
		{
			// . . . remove the leftovers before installing
			$status = $this->uninstall();
		}

		if(is_array($this->templates))
		{
			foreach($this->templates as $template)
			{
				$query = $db->simple_select('templates', '*', "title='{$template['title']}'");
				
				if($db->num_rows($query) == 1)
				{
					$db->update_query("templates", $template, "title='{$template['title']}'");
				}
				else
				{
					$db->insert_query("templates", $template);
				}
			}
		}
	}

	/*
	 * uninstall()
	 *
	 * access the given module's uninstall routine
	 */
	function uninstall($no_cleanup = false)
	{
		global $db;
		
		// installed?
		if($this->is_installed)
		{
			if(is_array($this->templates))
			{
				foreach($this->templates as $template)
				{
					$status = $db->query("DELETE FROM " . TABLE_PREFIX . "templates WHERE title='{$template['title']}'");
				}
				
				if(!$no_cleanup)
				{
					$status = $db->query("DELETE FROM " . TABLE_PREFIX . "sideboxes WHERE box_type='{$this->base_name}'");
				}
			}
			
			$this->unset_cache_version();
		}
	}

	/*
	 * upgrade()
	 *
	 * called upon addon version change to verify module's templates/settings
	 * discarded templates and ACP settings (from pre-1.4) are removed
	 */
	function upgrade()
	{
		global $db;
		
		// don't waste time if everything is in order
		if(!$this->is_upgraded)
		{
			// if there are settings left over from a pre-1.4 module installation
			if(is_array($this->discarded_settings))
			{
				// delete them all
				foreach($this->discarded_settings as $setting)
				{
					$status = $db->query("DELETE FROM " . TABLE_PREFIX . "settings WHERE name='{$setting}'");
				}
			}
			
			// update any sideboxes created with the older version of this module to contain the correct settings (default values)
			$query = $db->update_query('sideboxes', array("settings" => $db->escape_string(json_encode($this->settings))), "box_type='{$this->base_name}'");
			
			// if any templates were dropped in this version
			if(is_array($this->discarded_templates))
			{
				// delete them
				foreach($this->discarded_templates as $template)
				{
					$status = $db->query("DELETE FROM " . TABLE_PREFIX . "templates WHERE title='{$template}'");
				}
			}
			
			// now install the new templates
			$this->install(true);
			
			// update the version cache and the upgrade is complete
			$this->is_upgraded = $this->set_cache_version();
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
		@my_rmdir_recursive(ADV_SIDEBOX_MODULES_DIR . "/" . $this->base_name);
		@rmdir(ADV_SIDEBOX_MODULES_DIR . "/" . $this->base_name);
	}

	/*
	 * get_cache_version()
	 *
	 * version control derived from the work of pavemen in MyBB Publisher
	 */
	function get_cache_version()
	{
		global $cache, $mybb, $db;

		//get currently installed version, if there is one
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
	function set_cache_version()
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
	function unset_cache_version()
	{
		global $cache;

		$wildcard_plugins = $cache->read('wildcard_plugins');
		unset($wildcard_plugins['versions']['adv_sidebox_' . $this->base_name]);
		$cache->update('wildcard_plugins', $wildcard_plugins);
		
		return true;
	}

	/*
	 * build_template()
	 *
	 * runs template building code for the current module referenced by this object
	 */
	function build_template($settings)
	{
		// if the files are intact . . .
		if(file_exists(ADV_SIDEBOX_MODULES_DIR . "/" . $this->base_name . "/adv_sidebox_module.php"))
		{
			// . . . run the module's template building code.
			require_once ADV_SIDEBOX_MODULES_DIR . "/" . $this->base_name . "/adv_sidebox_module.php";

			if(function_exists($this->base_name . '_asb_build_template'))
			{
				$build_template_function = $this->base_name . '_asb_build_template';
				$build_template_function($settings);
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

		// valid table object?
		if($this_table instanceof Table)
		{
			// name
			$this_table->construct_cell($this->name);
			
			// description
			$this_table->construct_cell($this->description);
			
			// author (site link)
			$this_table->construct_cell('<a href="' . $this->author_site . '">' . $this->author . '</a>');
			
			// channel prep
			if($this->stereo)
			{
				$channel_info = $lang->adv_sidebox_modules_stereo;
			}
			else
			{
				$channel_info = $lang->adv_sidebox_modules_mono;
			}
			
			// channels
			$this_table->construct_cell($channel_info);
			
			// options popup
			$popup = new PopupMenu('module_' . $this->base_name, $lang->adv_sidebox_options);

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

			// delete
			$popup->add_item($lang->adv_sidebox_delete, ADV_SIDEBOX_URL . '&amp;action=delete_addon&amp;addon=' . $this->base_name);
			
			// popup cell
			$this_table->construct_cell($popup->fetch(), array("width" => '10%'));
			
			// finish row
			$this_table->construct_row();
		}
	}
}

/*
 * wrapper for custom static boxes
 */
class Sidebox_custom
{
	public $id;
	public $base_name;
	public $name;
	public $description;
	public $content;
	public $wrap_content;
	
	/*
	 * __construct()
	 *
	 * either creates a new Sidebox_custom object or loads an existing box from the db
	 *
	 * @param - $data is either an int TID of the database record of this custom box or an associative array pulled from the database externally
	 */
	function __construct($data)
	{
		// attempt to load the box
		$this->load($data);
	}
	
	/*
	 * load()
	 *
	 * @param - $data
	 */
	function load($data)
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
		}
	}
	
	/*
	 * save()
	 *
	 * saves whatever data is currently in the object
	 */
	function save()
	{
		global $db;
		
		// set up the array
		$data = array
		(
			"name"				=>	$db->escape_string($this->name),
			"description"		=>	$db->escape_string($this->description),
			"wrap_content"	=>	(int) $this->wrap_content,
			"content"			=>	$db->escape_string($this->content)
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
	function remove($no_cleanup = false)
	{
		// don't waste time on bad info
		if($this->id)
		{
			global $db;

			// unless specifically requested otherwise clean up
			if(!$no_cleanup)
			{
				// delete all boxes of this type in use
				$db->query("DELETE FROM " . TABLE_PREFIX . "sideboxes WHERE box_type='" . $this->base_name . "'");
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
	function export()
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
	function build_template()
	{
		// note the double-$'s . . . we are declaring the base_name of this custom module as global so that our eval will take effect where it is needed
		global $$this->base_name;
		
		$content = $this->content;
		
		// if the user doesn't want content then at least make it validate
		if(!$content)
		{
			$content = '
	<tr>
		<td></td>
	</tr>';
		}
		
		// store the content
		eval("\$" . $this->base_name . " = \"" . addslashes($content) . "\";");
	}
	
	/*
	 * build_table_row()
	 *
	 * @param - $this_table is a valid object of class DefaultTable
	 */
	function build_table_row($this_table)
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
			$popup->add_item($lang->adv_sidebox_delete, ADV_SIDEBOX_CUSTOM_URL . "&amp;mode=delete_box&amp;box={$this->id}");
			
			// export
			$popup->add_item($lang->adv_sidebox_custom_export, ADV_SIDEBOX_EXPORT_URL . "&amp;box={$this->id}");
			
			// popup cell
			$this_table->construct_cell($popup->fetch(), array("width" => '10%'));
			
			// finish the table
			$this_table->construct_row();
		}
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
	public $used_box_types;
	
	public $script;
	public $script_base_name;
	
	public $users_groups;
	
	public $box_types;
	
	public $addons;
	public $installed_addons;
	public $uninstalled_addons;
	public $simple_addons;
	public $total_addons;
	
	public $custom;

	/*
	 * __construct()
	 *
	 * called upon object creation constructs a new handler and attempts to load all necessary objects and properties
	 *
	 * @param - $script is a string containing the active MyBB PHP script filename (or in some cases a shortened psuedonym) and controls sorting within the handler object
	 * @param - $acp = false
	 */
	function __construct($script = '', $acp = false)
	{
		// make sure the script is in a format that works in all classes
		$this->process_script($script);
		
		// attempt to load the handler
		$this->load($acp);
	}
	
	/*
	 * load()
	 *
	 * attempts to load all sideboxes, addons, custom_boxes and establish properties to be used by ASB and ASB module functions
	 *
	 * @param - $acp, if true, will avoid wasted execution when in ACP by only loading necessary properties
	 */
	function load($acp = false)
	{
		global $db;
		
		// load everything detected (sideboxes will be filtered by script if applicable)
		$this->get_users_groups($acp);
		$this->boxes_to_show = $this->get_all_sideboxes($acp);
		$this->get_all_addons();
		$this->get_all_custom_boxes();
		
		// if we are in ACP, string conversion, template building and column padding/sorting won't be necessary . . .
		if($acp)
		{
			// just produce a list of all possible box types
			$this->compile_box_types();
		}
		else
		{
			// . . . otherwise load and sort all sideboxes
			$this->used_box_types = array();
			$this->left_boxes = '';
			$this->right_boxes = '';
			
			// if there are sideboxes . . .
			if(is_array($this->sideboxes))
			{			
				// loop through and sort them
				foreach($this->sideboxes as $this_box)
				{
					// wrap the content if applicable
					if($this_box->wrap_content)
					{
						$content = $this_box->build_wrapped_content();
					}
					else
					{
						$content = $this_box->content;
					}
					
					// sort by position (0 = left, non-zero = right)
					if($this_box->position)
					{
						$this->right_boxes .= $content;
					}
					else
					{
						$this->left_boxes .= $content;
					}
					
					// save wasted execution by producing an array of all used modules/custom_boxes
					// used when building templates
					$this->used_box_types[$this_box->id] = $this_box->box_type;
				}
				
				// if the columns contain viable content then pad them to ensure they remain a consistent width
				$this->left_boxes = $this->pad_column($this->left_boxes);
				$this->right_boxes = $this->pad_column($this->right_boxes);
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
	function get_users_groups($acp = false)
	{
		global $mybb;
		
		$this->users_groups = array();
		
		// if not a guest and not in ACP
		if($mybb->user['uid'] > 0 && !$acp)
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
	}
	
	/*
	 * compile_box_types()
	 *
	 * gather all available addon, custom and plugin box_types for internal and external use
	 */
	function compile_box_types()
	{
		global $plugins;
		
		// get user-defined static types
		if(is_array($this->custom))
		{
			foreach($this->custom as $module)
			{	
				$this->box_types[$module->base_name] = $module->name;
			}
		}
		
		// get addon modules
		if(is_array($this->addons))
		{
			foreach($this->addons as $module)
			{	
				$this->box_types[$module->base_name] = $module->name;
			}
		}
		
		// get all the plugin types
		$plugins->run_hooks('adv_sidebox_box_types', $this->box_types);
	}
	
	/*
	 * get_all_sideboxes()
	 *
	 * retrieve all sideboxes from the db (filtered by script if applicable)
	 *
	 * @param - $acp if true prevents group filtering (unnecessary and counter-intuitive in ACP)
	 */
	function get_all_sideboxes($acp = false)
	{
		global $db;

		$this->sideboxes = array();
		$where = '';
		
		// filter by script if applicable
		if($this->script_base_name && in_array($this->script_base_name, array("index", "forumdisplay", "showthread", "portal")))
		{
			$where = "show_on_" . $this->script_base_name . "='1'";
		}

		// Look for all sideboxes (if any)
		$query = $db->simple_select('sideboxes', '*', $where, array("order_by" => 'position, display_order', "order_dir" => 'ASC'));

		// if there are sideboxes . . .
		if($db->num_rows($query) > 0)
		{
			// loop throug them all
			while($this_box = $db->fetch_array($query))
			{
				// attempt to load the side box
				$test_box = new Sidebox($this_box);
				
				// if we aren't in ACP . . .
				if(!$acp)
				{
					// if the side box has multiple group permissions . . .
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
							else
							{
								// otherwise the user is in one group
								if($this->users_groups)
								{
									// if it matches . . .
									if($this->users_groups == $gid)
									{
										// they are cool
										$can_view = true;
										break;
									}
								}
							}
						}
					}
				}
				else
				{
					// if in ACP show all side boxes
					$can_view = true;
				}
				
				// permission is granted . . .
				if($can_view)
				{
					// add the side box
					$this->sideboxes[$this_box['id']] = $test_box;
				}
			}
			
			// true indicates that there is content to show
			return true;
		}
	}
	
	/*
	 * get_all_addons()
	 *
	 * attempts to load all addon modules
	 */
	function get_all_addons()
	{
		//modules
		$dir = opendir(ADV_SIDEBOX_MODULES_DIR);

		$this->addons = array();

		// loop through all detected modules
		while(($module = readdir($dir)) !== false)
		{
			if(is_dir(ADV_SIDEBOX_MODULES_DIR . "/" . $module) && !in_array($module, array(".", "..")) && file_exists(ADV_SIDEBOX_MODULES_DIR . "/" . $module . "/adv_sidebox_module.php"))
			{
				$this->addons[$module] = new Sidebox_addon($module);
				
				// update handler counts
				if($this->addons[$module]->module_type == 'complex')
				{
					if($this->addons[$module]->is_installed)
					{
						++$this->installed_addons;
					}
					else
					{
						++$this->uninstalled_addons;
					}
				}
				else
				{
					++$this->simple_addons;
				}
			}
		}
		
		// get a total
		$this->total_addons = $this->installed_addons + $this->uninstalled_addons + $this->simple_addons;
	}
	
	/*
	 * build_addon_language()
	 *
	 * probably overly complicated but this method produces grammatically correct language to describe the state of addons in the plugin
	 */
	function build_addon_language()
	{
		global $lang;

		if(!$lang->adv_sidebox)
		{
			$lang->load('adv_sidebox');
		}

		// if there are any modules . . .
		if($this->total_addons)
		{
			// more than 1?
			if($this->total_addons > 1)
			{
				// plural language
				$module_info .= $lang->sprintf($lang->adv_sidebox_module_info_good_count, $lang->adv_sidebox_are, $this->total_addons, $lang->adv_sidebox_module_plural);
			}
			else
			{
				// singular
				$module_info .= $lang->sprintf($lang->adv_sidebox_module_info_good_count, $lang->adv_sidebox_is, $this->total_addons, $lang->adv_sidebox_module_singular);
			}

			// uninstalled modules?
			if($this->uninstalled_addons)
			{
				// more than one?
				if($this->uninstalled_addons > 1)
				{
					// plural language
					$module_info .= $lang->sprintf($lang->adv_sidebox_module_awaiting_install, $this->uninstalled_addons, $lang->adv_sidebox_are);
				}
				else
				{
					// singular
					$module_info .= $lang->sprintf($lang->adv_sidebox_module_awaiting_install, $this->uninstalled_addons, $lang->adv_sidebox_is);
				}
			}
			else
			{
				// all modules installed
				$module_info .= $lang->adv_sidebox_module_all_good;
			}
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
	function get_all_custom_boxes()
	{
		global $db;
		
		$this->custom = array();
		
		$query = $db->simple_select('custom_sideboxes');

		// if ther are custom boxes . . .
		if($db->num_rows($query) > 0)
		{
			// fetch them
			while($this_box = $db->fetch_array($query))
			{
				// and attempt to load each custom box type
				$this->custom['asb_custom_' . $this_box['id']] = new Sidebox_custom($this_box);
			}
		}
	}
	
	/*
	 * build_all_templates()
	 *
	 * executes build_template methods for all used custom box types and addon modules allowing plugins to do the same
	 */
	function build_all_templates()
	{
		global $plugins;
		
		// don't waste time if there are no sideboxes to build templates for
		if($this->boxes_to_show && is_array($this->used_box_types))
		{
			// create this array to catch any sidebox types that aren't custom or addon, these will be added by plugins (if that ever happens :p )
			$box_types = array();
			
			// loop through all used types
			foreach($this->used_box_types as $this_box => $module)
			{	
				// if this type was created by an addon module . . .
				if($this->addons[$module])
				{
					// build the template
					$this->addons[$module]->build_template($this->sideboxes[$this_box]->settings);
				}
				// or if it is a custom static box . . .
				elseif($this->custom[$module])
				{
					// build the custom box template
					$this->custom[$module]->build_template();
				}
				else
				{
					// otherwise it is an external plugin-created type (or it is invalid)
					$box_types[$module] = true;
				}
			}
			
			// this hook will allow a plugin to process its custom box type for display (you will first need to hook into adv_sidebox_add_type to add the box
			$plugins->run_hooks('adv_sidebox_output_end', $box_types);
		}
	}

	/*
	 * pad_column()
	 *
	 * uses a transparent image to ensure that sideboxes remain at a consistent width
	 *
	 * @param - $content is a string with template HTML for a column of sideboxes
	 */
	function pad_column($content)
	{
		// if it is empty, leave it that way
		if($content)
		{
			// if not, pad it to ensure the width is constant regardless of content
			return $content . '<img src="inc/plugins/adv_sidebox/images/transparent.gif" width="' . $width . '" height="1" alt="" title=""/>';
		}
	}
	
	/*
	 * process_script()
	 *
	 * ensure that the script properties are in a valid format
	 *
	 * @param - $script is a string that either contains the active PHP script's filename or a shortened psuedonym
	 */
	function process_script($script)
	{
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
	function build_peekers()
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
					// if the setting seem valid . . .
					if(is_array($module->settings))
					{
						// loop through them
						foreach($module->settings as $setting)
						{
							// attach an event handler to only show these settings when appropriate
							$element_name = "{$setting['name']}";
							$element_id = "setting_{$setting['name']}";
							$peekers .= '
			var peeker = new Peeker
			(
				$("box_type_select"),
				$("' . $element_id  . '"),
				/' . $module->base_name . '/,
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
				' . $peekers . '	
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
	function build_settings($this_form, $this_form_container, $this_box)
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
					// if the settings seem valid . . .
					if(is_array($module->settings))
					{
						// loop through them
						foreach($module->settings as $setting)
						{
							// create each element with unique id and name properties
							$options = "";
							$type = explode("\n", $setting['optionscode']);
							$type[0] = trim($type[0]);
							$element_name = "{$setting['name']}";
							$element_id = "setting_{$setting['name']}";
							
							// if editing and the current box uses this module . . .
							if($this->sideboxes[$this_box]->box_type == $module->base_name)
							{
								// if there are settings (values mostly). . .
								if(is_array($this->sideboxes[$this_box]->settings))
								{
									// get the values
									foreach($this->sideboxes[$this_box]->settings as $this_box_setting)
									{
										// if the current setting has a stored value
										if($this_box_setting->name == $setting['name'])
										{
											// store to be included in the produced HTML value property
											$setting['value'] = $this_box_setting->value;
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
