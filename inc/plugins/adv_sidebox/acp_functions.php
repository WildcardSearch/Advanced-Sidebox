<?php
/*
 * This file contains the ACP functions for adv_sidebox.php
 *
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x v1.0
 * Copyright © 2012 Wildcard
 * http://www.rantcentralforums.com
 *
 * BASED UPON THE CONCEPT AND CODE CREATED BY NAYAR IN THE ORIGINAL SIDEBOX PLUGIN
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

 // Disallow direct access to this file for security reasons
if(!defined("IN_MYBB") || !defined("ADV_SIDEBOX"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

define('ADV_SIDEBOX_URL', 'index.php?module=config-plugins&amp;action=sidebox');

// Information about the plugin used by MyBB for display as well as to connect with updates
function adv_sidebox_info()
{
	global $db, $mybb, $lang;

	// Get the gid for the settings group (needed for settings link in description)
	$gid = (int) adv_sidebox_get_settingsgroup();
	
	if (!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}
	
	if($gid)
	{
		$settings_link = "<ul><li><a href=\"" . $mybb->settings['bburl'] . "/admin/index.php?module=config-settings&amp;action=change&amp;gid=" . $gid . "\" target=\"_blank\">" . $lang->adv_sidebox_plugin_settings . "</a></li></ul>";
	}
	else
	{
		$settings_link = "<br />";
	}

	// This array returns information about the plugin, some of which was prefabricated above based on whether the plugin has been installed or not.
	return array(
		"name"			=> $lang->adv_sidebox_name,
		"description"	=> $lang->adv_sidebox_description1 . "<br/><br/>" . $lang->adv_sidebox_description2 . $settings_link,
		"website"		=> "http://www.rantcentralforums.com",
		"author"		=> "Wildcard",
		"authorsite"	=> "http://www.rantcentralforums.com",
		"version"		=> "1.0",
		"compatibility" => "16*",
		"guid" 			=> "cf9d9318c4cc33463c16326b740935bd",
	);
}

// Checks to see if the plugin's settingsgroup is installed. If so then assume the plugin is installed.
function adv_sidebox_is_installed()
{
	return adv_sidebox_get_settingsgroup();
}

// Add a table (sideboxes) to the DB, a column to the mybb_users table (show_sidebox), install the plugin settings, install plugin templates and create a new stylesheet.
function adv_sidebox_install()
{
	global $db, $mybb, $lang;
	
	// create the table if it doesn't already exist.
	if (!$db->table_exists('sideboxes')) {
		$collation = $db->build_create_table_collation();
		$db->write_query("CREATE TABLE " . TABLE_PREFIX . "sideboxes(
			id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			display_order INT(10) NOT NULL,
			box_type VARCHAR(25) NOT NULL,
			position INT(2),
			content TEXT
			) ENGINE=MyISAM{$collation};");
	}
	
	// add column to the mybb_users table (but first check to see if it has been left behind in a previous installation.
	if($db->field_exists('show_sidebox', 'users'))
	{
		$db->write_query("ALTER TABLE ".TABLE_PREFIX."users DROP COLUMN show_sidebox");
	}
	$db->write_query("ALTER TABLE ".TABLE_PREFIX."users ADD show_sidebox varchar(1) DEFAULT '1'");
	
	// load language variables
	$lang->load("adv_sidebox");
	
	// settings group and settings
	$adv_sidebox_group = array(
		"gid" 				=> "NULL",
		"name" 				=> "adv_sidebox_settings",
		"title" 				=> "Advanced Sidebox",
		"description" 		=> $lang->adv_sidebox_settingsgroup_description,
		"disporder" 		=> "101",
		"isdefault" 			=> "no",
	);
	$db->insert_query("settinggroups", $adv_sidebox_group);
	$gid = $db->insert_id();
	$adv_sidebox_setting_1 = array(
		"sid"					=> "NULL",
		"name"				=> "adv_sidebox_on_index",
		"title"					=> $lang->adv_sidebox_show_on_index,
		"description"		=> "",
		"optionscode"	=> "yesno",
		"value"				=> '1',
		"disporder"		=> '2',
		"gid"					=> intval($gid),
	);
	$adv_sidebox_setting_2 = array(
		"sid"					=> "NULL",
		"name"				=> "adv_sidebox_on_forumdisplay",
		"title"					=> $lang->adv_sidebox_show_on_forumdisplay,
		"description"		=> "",
		"optionscode"	=> "yesno",
		"value"				=> '1',
		"disporder"		=> '2',
		"gid"					=> intval($gid),
	);
	$adv_sidebox_setting_3 = array(
		"sid"					=> "NULL",
		"name"				=> "adv_sidebox_on_showthread",
		"title"					=> $lang->adv_sidebox_show_on_threaddisplay,
		"description"		=> "",
		"optionscode"	=> "yesno",
		"value"				=> '1',
		"disporder"		=> '3',
		"gid"					=> intval($gid),
	);

	$adv_sidebox_setting_4 = array(
		"sid"					=> "NULL",
		"name"				=> "adv_sidebox_portal_replace",
		"title"					=> $lang->adv_sidebox_replace_portal_boxes,
		"description"		=> "",
		"optionscode"	=> "yesno",
		"value"				=> '1',
		"disporder"		=> '4',
		"gid"					=> intval($gid),
	);
	$adv_sidebox_setting_5 = array(
		"sid"					=> "NULL",
		"name"				=> "adv_sidebox_width_left",
		"title"					=> $lang->adv_sidebox_width . ":",
		"description"		=> "left",
		"optionscode"	=> "text",
		"value"				=> '240px',
		"disporder"		=> '5',
		"gid"					=> intval($gid),
	);
	$adv_sidebox_setting_6 = array(
		"sid"					=> "NULL",
		"name"				=> "adv_sidebox_width_right",
		"title"					=> $lang->adv_sidebox_width . ":",
		"description"		=> "right",
		"optionscode"	=> "text",
		"value"				=> '240px',
		"disporder"		=> '6',
		"gid"					=> intval($gid),
	);
	
	// Theme exclude list select box
	// Get all the themes that are not MasterStyles
	$query = $db->simple_select("themes", "tid, name, pid", "pid != '0'", array('order_by' => 'pid, name'));
	
	// Create a theme counter so our box is tidy
	$theme_count = 0;
	
	// Create an option for each theme and insert code to unserialize each option and 'remember' settings
	while($this_theme = $db->fetch_array($query))
	{
		$theme_select .= '<option value=\"' . $this_theme['tid'] . '\"" . (is_array(unserialize($setting[\'value\'])) ? ($setting[\'value\'] != "" && in_array("' . $this_theme['tid'] . '", unserialize($setting[\'value\'])) ? "selected=\"selected\"":""):"") . ">' . $this_theme['name'] . '</option>';
		
		++$theme_count;
	}
	
	$theme_select .= '</select>';
	
	// put it all together
	$theme_select = 'php
<select multiple name=\"upsetting[adv_sidebox_exclude_theme][]\" size=\"' . $theme_count . '\">' . $theme_select;

	$adv_sidebox_setting_7 = array(
		"sid"					=> "NULL",
		"name"				=> "adv_sidebox_exclude_theme",
		"title"					=> $lang->adv_sidebox_theme_exclude_list . ":",
		"description"		=> $lang->adv_sidebox_theme_exclude_list_description,
		"optionscode"	=> $db->escape_string($theme_select),
		"value"				=> '',
		"disporder"		=> '7',
		"gid"					=> intval($gid),
	);
	
	$adv_sidebox_setting_8 = array(
		"sid"					=> "NULL",
		"name"				=> "adv_sidebox_avatar_per_row",
		"title"					=> $db->escape_string($lang->adv_sidebox_wol_avatar_list),
		"description"		=> $lang->adv_sidebox_num_avatars_per_row . ":",
		"optionscode"	=> "text",
		"value"				=> '4',
		"disporder"		=> '8',
		"gid"					=> intval($gid),
	);
	
	$adv_sidebox_setting_9 = array(
		"sid"					=> "NULL",
		"name"				=> "adv_sidebox_avatar_max_rows",
		"title"					=> '',
		"description"		=> $lang->adv_sidebox_avatar_max_rows . ":",
		"optionscode"	=> "text",
		"value"				=> '3',
		"disporder"		=> '9',
		"gid"					=> intval($gid),
	);
	
	$db->insert_query("settings", $adv_sidebox_setting_1);
	$db->insert_query("settings", $adv_sidebox_setting_2);
	$db->insert_query("settings", $adv_sidebox_setting_3);
	$db->insert_query("settings", $adv_sidebox_setting_4);
	$db->insert_query("settings", $adv_sidebox_setting_5);
	$db->insert_query("settings", $adv_sidebox_setting_6);
	$db->insert_query("settings", $adv_sidebox_setting_7);
	$db->insert_query("settings", $adv_sidebox_setting_8);
	$db->insert_query("settings", $adv_sidebox_setting_9);

	rebuild_settings();
	
	//modules
	$modules_dir = MYBB_ROOT. "inc/plugins/adv_sidebox/modules";
	$dir = opendir($modules_dir);
	
	while(($module = readdir($dir)) !== false)
	{
		if(is_dir($modules_dir."/".$module) && !in_array($module, array(".", "..")) && file_exists($modules_dir."/".$module."/sidebox_meta.php"))
		{
			if(file_exists($modules_dir."/".$module."/sidebox_install.php"))
			{
				require_once $modules_dir."/".$module."/sidebox_install.php";
				
				$is_installed_function = $module . '_is_installed';
				
				if(function_exists($module . '_is_installed'))
				{
					if(!$is_installed_function())
					{
						if(function_exists($module . '_install'))
						{
							$install_function = $module . '_install';
							$install_function();
						}
					}
					else
					{
						if(function_exists($module . '_uninstall'))
						{
							$uninstall_function = $module . '_uninstall';
							$uninstall_function();
							
							if(function_exists($module . '_install'))
							{
								$install_function = $module . '_install';
								$install_function();
							}
						}
					}
				}
			}
		}
	}
	
	// create and install all custom templates
	adv_sidebox_install_templates();
}

// DROP the table added to the DB and the column previously added to the mybb_users table (show_sidebox), delete the plugin settings, templates and stylesheets.
function adv_sidebox_uninstall()
{
	global $db;
	
	//modules
	$modules_dir = MYBB_ROOT. "inc/plugins/adv_sidebox/modules";
	$dir = opendir($modules_dir);
	
	while(($module = readdir($dir)) !== false)
	{
		if(is_dir($modules_dir."/".$module) && !in_array($module, array(".", "..")) && file_exists($modules_dir."/".$module."/sidebox_meta.php"))
		{
			if(file_exists($modules_dir."/".$module."/sidebox_install.php"))
			{
				require_once $modules_dir."/".$module."/sidebox_install.php";
				
				$is_installed_function = $module . '_is_installed';
				
				if(function_exists($module . '_is_installed'))
				{
					if($is_installed_function())
					{
						if(function_exists($module . '_uninstall'))
						{
							$uninstall_function = $module . '_uninstall';
							$uninstall_function();
						}
					}
				}
			}
		}
	}
	
	// remove the table
	$db->drop_table('sideboxes');

	// remove then column from the mybb_users table
	$db->write_query("ALTER TABLE ".TABLE_PREFIX."users DROP COLUMN show_sidebox");

	$db->query("DELETE FROM ".TABLE_PREFIX."settinggroups WHERE name='adv_sidebox_settings'");
	$db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='adv_sidebox_on_index'");
	$db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='adv_sidebox_on_forumdisplay'");
	$db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='adv_sidebox_on_showthread'");
	$db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='adv_sidebox_portal_replace'");
	$db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='adv_sidebox_width_left'");
	$db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='adv_sidebox_width_right'");
	$db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='adv_sidebox_exclude_theme'");
	$db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='adv_sidebox_avatar_per_row'");
	$db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='adv_sidebox_avatar_max_rows'");
	
	rebuild_settings();
	
	// remove the templates
	adv_sidebox_remove_templates();
}

// Hook for ACP settings serialize
$plugins->add_hook("admin_config_settings_change", "adv_sidebox_serialize");

// Serialize the theme exclusion list selector
function adv_sidebox_serialize()
{
    global $mybb;
	
    $mybb->input['upsetting']['adv_sidebox_exclude_theme'] = serialize($mybb->input['upsetting']['adv_sidebox_exclude_theme']);
}

$plugins->add_hook('admin_page_output_nav_tabs_start', 'adv_sidebox_tabs_start');

function adv_sidebox_tabs_start(&$arguments)
{
    global $mybb, $lang;

    if (!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}
	
	if($mybb->input['module'] == 'config-plugins')
    {
        $arguments['sidebox'] = array('title' => $lang->adv_sidebox_name,
                                      'description' => $lang->adv_sidebox_page_desc,
                                      'link' => ADV_SIDEBOX_URL);
    }
}

$plugins->add_hook('admin_config_plugins_begin', 'adv_sidebox_plugins_begin');

function adv_sidebox_plugins_begin()
{
    global $mybb, $lang, $page, $db;
	
	if($mybb->input['action'] == 'sidebox')
    {
        $page->add_breadcrumb_item($lang->adv_sidebox_name, ADV_SIDEBOX_URL);

        switch($mybb->input['mode'])
        {
            case 'edit_box':
				edit_box();
                break;
				
			case 'delete_box':
				if(isset($mybb->input['box']) && (int) $mybb->input['box'] > 0)
				{
					$status = $db->query("DELETE FROM ".TABLE_PREFIX."sideboxes WHERE id='" . (int) $mybb->input['box'] . "'");
				}
				
				if($status)
				{
					flash_message("The box was deleted successfully", "success");
					admin_redirect(ADV_SIDEBOX_URL);
				}
				else
				{
					flash_message("Something went wrong!", "error");
					admin_redirect(ADV_SIDEBOX_URL);
				}
				break;
			
			case 'install_box':
				if(isset($mybb->input['addon']))
				{
					$this_module = $mybb->input['addon'];
					$modules_dir = MYBB_ROOT. "inc/plugins/adv_sidebox/modules";
					
					if(file_exists($modules_dir . "/" . $this_module . "/sidebox_install.php"))
					{
						require_once $modules_dir . "/" . $this_module . "/sidebox_install.php";
			
						if(function_exists($this_module . '_install'))
						{
							$install_function = $this_module . '_install';
							$install_function();
							
							flash_message("The new module was installed successfully", "success");
							admin_redirect(ADV_SIDEBOX_URL);
						}
					}
				}
				else
				{
					flash_message("The new module was fucked", "error");
					admin_redirect(ADV_SIDEBOX_URL);
				}
				break;

            case 'uninstall_box':
				if(isset($mybb->input['addon']))
				{
					$this_module = $mybb->input['addon'];
					$modules_dir = MYBB_ROOT. "inc/plugins/adv_sidebox/modules";
					
					if(file_exists($modules_dir . "/" . $this_module . "/sidebox_install.php"))
					{
						require_once $modules_dir . "/" . $this_module . "/sidebox_install.php";
			
						if(function_exists($this_module . '_uninstall'))
						{
							$install_function = $this_module . '_uninstall';
							$install_function();
							
							flash_message("The new module was uninstalled successfully", "success");
							admin_redirect(ADV_SIDEBOX_URL);
						}
					}
				}
				else
				{
					flash_message("The new module was fucked", "error");
					admin_redirect(ADV_SIDEBOX_URL);
				}
				break;

            default:
                adv_sidebox_page();
                break;
        }
    }
}

function adv_sidebox_page()
{
    global $mybb, $db, $page, $lang, $plugins;
	
	if (!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}
	
	$box_types = array(
		'{$custom_box}' 		=> $lang->adv_sidebox_custom,
		'{$sbwhosonline}' 	=> $lang->adv_sidebox_wol_avatar_list
			);
			
	// plugin hooks	
	$plugins->run_hooks('adv_sidebox_box_types', $box_types);
	
	//modules
	$modules_dir = MYBB_ROOT. "inc/plugins/adv_sidebox/modules";
	$dir = opendir($modules_dir);
	$uninstalled_modules = array();
	$installed_modules = array();
	
	while(($module = readdir($dir)) !== false)
	{
		if(is_dir($modules_dir."/".$module) && !in_array($module, array(".", "..")) && file_exists($modules_dir."/".$module."/sidebox_meta.php"))
		{
			if(file_exists($modules_dir."/".$module."/sidebox_install.php"))
			{
				require_once $modules_dir."/".$module."/sidebox_install.php";
				
				$is_installed_function = $module . '_is_installed';
				
				if(function_exists($module . '_is_installed'))
				{
					if(!$is_installed_function())
					{
						$uninstalled_modules[] = $module;
					}
					else
					{
						require_once $modules_dir."/".$module."/sidebox_meta.php";
			
						if(function_exists($module . '_add_type') && function_exists($module . '_build_template'))
						{
							$add_type_function = $module . '_add_type';
							$add_type_function($box_types);
						}
				
						$installed_modules[] = $module;
					}
				}
				else
				{
					$uninstalled_modules[] = $module;
				}
			}
			else
			{
				// there is no install, add the box_type now
				require_once $modules_dir . "/" . $module . "/sidebox_meta.php";
			
				if(function_exists($module . '_add_type') && function_exists($module . '_build_template'))
				{
					$add_type_function = $module . '_add_type';
					$add_type_function($box_types);
				}
			}
		}
	}

	adv_sidebox_output_header();
	adv_sidebox_output_tabs();
	
	$table = new Table;
	
	$table->construct_header($lang->adv_sidebox_id);
	$table->construct_header($lang->adv_sidebox_display_order);
	$table->construct_header($lang->adv_sidebox_box_type);
	$table->construct_header($lang->adv_sidebox_position);
	$table->construct_header($lang->adv_sidebox_controls, array("colspan" => 2));
	
	$query = $db->simple_select('sideboxes', 'id, display_order, box_type, position, content', '', array("order_by" => 'position, display_order', "order_dir" => 'ASC'));
	
	if($db->num_rows($query) > 0)
	{
		$left_box = false;
		$right_box = false;
		
		$table->construct_cell('<strong>Left Boxes</strong>', array("colspan" => 7));
		$table->construct_row();
		
		while($box = $db->fetch_array($query))
		{
			// if this is the first right box . . .
			if((int) $box['position'] && !$right_box)
			{
				// . . . and there weren't any left boxes . . .
				if(!$left_box)
				{
					// let them know
					$table->construct_cell('<span style="color: #888"><p>' . $lang->adv_sidebox_no_boxes_left . '</p></span>', array("colspan" => 7));
					$table->construct_row();
				}
				
				// and add the label
				$right_box = true;
				$table->construct_cell('<strong>Right Boxes</strong>', array("colspan" => 7));
				$table->construct_row();
			}
			elseif((int) $box['position'] == 0 && !$left_box)
			{
				// otherwise its a left box
				$left_box = true;
			}
			
			// merge left and right WOL boxes
			if($box['box_type'] == '{$sbwhosonline_l}' || $box['box_type'] == '{$sbwhosonline_r}')
			{
				$box['box_type'] = '{$sbwhosonline}';
			}
			
			// construct the table row.
			$table->construct_cell($box['id'], array("width" => '5%'));
			$table->construct_cell($box['display_order'], array("width" => '5%'));
			$table->construct_cell($box_types[$box['box_type']], array("width" => '10%'));
			$table->construct_cell(((int) $box['position'] ? $lang->adv_sidebox_position_right : $lang->adv_sidebox_position_left), array("width" => '5%'));
			$table->construct_cell('<a href="' . ADV_SIDEBOX_URL . '&amp;mode=edit_box&amp;box=' . $box['id'] . '"><img src="' . $mybb->settings['bburl'] . '/images/icons/pencil.gif" alt="' . $lang->adv_sidebox_edit . '" title="' . $lang->adv_sidebox_edit . '" />&nbsp;' . $lang->adv_sidebox_edit . '</a>', array("width" => '10%'));
			$table->construct_cell('<a href="' . ADV_SIDEBOX_URL . '&amp;mode=delete_box&amp;box=' . $box['id'] . '"><img src="' . $mybb->settings['bburl'] . '/images/usercp/delete.png" alt="' . $lang->adv_sidebox_edit . '" title="' . $lang->adv_sidebox_edit . '" />&nbsp;' . $lang->adv_sidebox_delete . '</a>', array("width" => '10%'));
			$table->construct_row();
		}
		
		// if there were no right boxes . . .
		if(!$right_box)
		{
			// add the label anyway
			$table->construct_cell('<strong>Right Boxes</strong>', array("colspan" => 6));
			$table->construct_row();

			// and tell them what they already know
			$table->construct_cell('<span style="color: #888"><p>' . $lang->adv_sidebox_no_boxes_right . '</p></span>', array("colspan" => 7));
			$table->construct_row();
		}
	}
	else
	{
		// no boxes? state the obvious
		$table->construct_cell('<span style="color: #888"><p>' . $lang->adv_sidebox_no_boxes . '</p></span>', array("colspan" => 7));
		$table->construct_row();
	}
	
	// output the box table
	$table->output();
	
	if(!empty($uninstalled_modules))
	{
		$install_links = '<br /><br /><strong>Uninstalled modules:</strong><br />';
		
		foreach($uninstalled_modules as $this_module)
		{
			// we need to get these add-on's descriptions
			require_once $modules_dir . "/" . $this_module . "/sidebox_meta.php";
		
			if(function_exists($this_module . '_add_type'))
			{
				$add_type_function = $this_module . '_add_type';
				$add_type_function($box_types);
			}
			
			$install_links .= '<a href="' . ADV_SIDEBOX_URL . '&amp;mode=install_box&amp;addon=' . $this_module . '"><img src="' . $mybb->settings['bburl'] . '/images/new.png" />&nbsp;Install ' . $box_types['{$' . $this_module . '}'] . '</a>&nbsp;&nbsp;&nbsp;&nbsp;';
		}
	}
	
	if(!empty($installed_modules))
	{
		$uninstall_links = '<br /><br /><strong>Installed modules:</strong><br />';
		
		foreach($installed_modules as $this_module)
		{
			$uninstall_links .= '<a href="' . ADV_SIDEBOX_URL . '&amp;mode=uninstall_box&amp;addon=' . $this_module . '"><img src="' . $mybb->settings['bburl'] . '/images/delete.png" />&nbsp;Uninstall ' . $box_types['{$' . $this_module . '}'] . '</a>&nbsp;&nbsp;&nbsp;&nbsp;';
		}
	}
	
	// and add link at the bottom
	echo('<hr><a href="' . ADV_SIDEBOX_URL . '&amp;mode=edit_box"><img src="' . $mybb->settings['bburl'] . '/images/add.png" />&nbsp;Add a new sidebox</a>' . $uninstall_links . $install_links);
	
	$page->output_footer();
}

function edit_box()
{
	global $mybb, $db, $page, $lang, $plugins;

	if (!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}
	
	// POSTing?
	if($mybb->request_method == "post")
	{
		// saving?
		if($mybb->input['save_box_submit'] == 'Save')
		{
			// help them keep their display orders spaced
			if(!isset($mybb->input['display_order']) || (int) $mybb->input['display_order'] == 0)
			{
				$query = $db->simple_select('sideboxes', 'display_order');
				
				$disp_order = ((int) $db->num_rows($query) + 1) * 10;
			}
			else
			{
				// or back off if they entered a value
				$disp_order = (int) $mybb->input['display_order'];
			}
			
			// translate the position
			if($mybb->input['box_position'] == 'right')
			{
				$pos = 1;
			}
			
			// if this isn't a custom box . . .
			if($mybb->input['box_type_select'] != '{$custom_box}')
			{
				// don't store the content at all
				$content = '';
			}
			else
			{
				// otherwise store it
				$content = $mybb->input['box_content'];
			}
			
			// is this a WOL box?
			if($mybb->input['box_type_select'] == '{$sbwhosonline}')
			{
				// if so edit the template variable to include positioning
				$box_type = '{$sbwhosonline' . ($pos ? '_r' : '_l') . '}';
			}
			else
			{
				// otherwise just send the var as-is
				$box_type = $mybb->input['box_type_select'];
			}
			
			// db array
			$this_box = array(
				"display_order"	=> (int) $disp_order,
				"box_type"	=>	$db->escape_string($box_type),
				"position"		=>	(int) $pos,
				"content"		=>	$db->escape_string($content)
			);
			
			// does this box already exist?
			if(isset($mybb->input['box']) && (int) $mybb->input['box'] > 0)
			{
				// if so update it
				$status = $db->update_query('sideboxes', $this_box, "id='" . (int) $mybb->input['box'] . "'");
			}
			else
			{
				// if not, create it
				$status = $db->insert_query('sideboxes', $this_box);
			}
			
			// success?
			if($status)
			{
				// yay
				flash_message($lang->adv_sidebox_save_success, "success");
				admin_redirect(ADV_SIDEBOX_URL);
			}
			else
			{
				// :(
				flash_message($lang->adv_sidebox_save_fail, "error");
				admin_redirect(ADV_SIDEBOX_URL . "&amp;mode=edit_box");
			}
		}
	}
	
	if (!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}
	
	$box_types = array(
		'{$custom_box}' 		=> $lang->adv_sidebox_custom,
		'{$sbwhosonline}' 	=> $lang->adv_sidebox_wol_avatar_list
			);
			
	// plugin hoof for add_type
	$plugins->run_hooks('adv_sidebox_box_types', $box_types);
	
	//modules
	$modules_dir = MYBB_ROOT. "inc/plugins/adv_sidebox/modules";
	$dir = opendir($modules_dir);
	
	while(($module = readdir($dir)) !== false)
	{
		if(is_dir($modules_dir."/".$module) && !in_array($module, array(".", "..")) && file_exists($modules_dir."/".$module."/sidebox_meta.php"))
		{
			if(file_exists($modules_dir."/".$module."/sidebox_install.php"))
			{
				require_once $modules_dir."/".$module."/sidebox_install.php";
				
				$is_installed_function = $module . '_is_installed';
				
				if(function_exists($module . '_is_installed'))
				{
					if($is_installed_function())
					{
						require_once $modules_dir."/".$module."/sidebox_meta.php";
			
						if(function_exists($module . '_add_type'))
						{
							$add_type_function = $module . '_add_type';
							$add_type_function($box_types);
						}
					}
				}
			}
			else
			{
				// there is no install, add the box_type now
				require_once $modules_dir."/".$module."/sidebox_meta.php";
			
				if(function_exists($module . '_add_type') && function_exists($module . '_build_template'))
				{
					$add_type_function = $module . '_add_type';
					$add_type_function($box_types);
				}
			}
		}
	}
	
	// add the script to hide the content box if it is unnecessary
	$page->extra_header .= '<script type="text/javascript" src="./jscripts/peeker.js"></script>
    <script type="text/javascript">Event.observe(window, "load", function() {var peeker = new Peeker($("box_type_select"), $("box_content"), /{\$custom_box}/, false);});
    </script>'; 
	
	// output ACP page stuff
	adv_sidebox_output_header();
	adv_sidebox_output_tabs();
	
	// editing?
	if(isset($mybb->input['box']))
	{
		// load the box from the db
		$box_id = (int) $mybb->input['box'];
		$query = $db->simple_select('sideboxes', 'id, display_order, box_type, position, content', "id='{$box_id}'", array("order_by" => 'display_order', "order_dir" => 'ASC'));
		
		// if it exists, store it for display
		if($db->num_rows($query) == 1)
		{
			$this_box = $db->fetch_array($query);
			$disp_order = (int) $this_box['display_order'];
		}
	}
	else
	{
		// if creating a new box give them some kind of display order that makes sense
		$query = $db->simple_select('sideboxes', 'display_order');
		$disp_order = ((int) $db->num_rows($query) + 1) * 10;
		
		// and some sample custom content
		$this_box['content'] = '<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
	<tr>
		<td class="thead"><strong>Custom Box</strong></td>
	</tr>
	<tr>
		<td class="trow1">Place your custom content here. HTML can be used in conjunction with certain template variables, language variables and environment variables.<br /><br />
		For example:<br /><br />
		<strong>User:</strong> {$mybb->user[\'username\']}<br />
		<strong>UID:</strong> {$mybb->user[\'uid\']}<br />
		<strong>Theme name:</strong> {$theme[\'name\']}</td></tr></table><br />';
	}
	
	if($this_box['box_type'] == '{$sbwhosonline_l}' || $this_box['box_type'] == '{$sbwhosonline_r}')
	{
		$this_box['box_type'] = '{$sbwhosonline}';
	}
	
	$form = new Form(ADV_SIDEBOX_URL . "&amp;mode=edit_box&amp;box=" . $this_box['id'], "post", "edit_box");
	$form_container = new FormContainer($lang->adv_sidebox_edit_box);
	
	$form_container->output_row($lang->adv_sidebox_box_type, $lang->adv_sidebox_type_desc, $form->generate_select_box('box_type_select', $box_types, $this_box['box_type'], array("id" => 'box_type_select')), array("id" => 'box_type_select_box'));
	$form_container->output_row($lang->adv_sidebox_content_title, $lang->adv_sidebox_content_desc, $form->generate_text_area('box_content', $this_box['content'], array("id" => 'box_content')), array("id" => 'box_content'));
	$form_container->output_row($lang->adv_sidebox_position, $lang->adv_sidebox_position_desc, $form->generate_radio_button('box_position', 'left', $lang->adv_sidebox_position_left, array("checked" => ((int) $this_box['position'] == 0))) . '&nbsp;&nbsp;' . $form->generate_radio_button('box_position', 'right', $lang->adv_sidebox_position_right, array("checked" => ((int) $this_box['position'] != 0))));
	$form_container->output_row($lang->adv_sidebox_display_order, '', $form->generate_text_box('display_order', $disp_order));
	$form_container->end();
	
	$buttons[] = $form->generate_submit_button('Save', array('name' => 'save_box_submit'));
	$form->output_submit_wrapper($buttons);
	$form->end();
		
	$page->output_footer();
}

function adv_sidebox_output_header()
{
    global $page, $lang;
	
	if (!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}
	
    $page->output_header($lang->adv_sidebox_name);
}

function adv_sidebox_output_tabs()
{
    global $page, $lang;

    $sub_tabs['plugins'] = array(
        'title' => $lang->plugins,
        'link' => 'index.php?module=config-plugins',
        'description' => $lang->plugins_desc
        );

    $sub_tabs['update_plugins'] = array(
        'title' => $lang->plugin_updates,
        'link' => 'index.php?module=config-plugins&amp;action=check',
        'description' => $lang->plugin_updates_desc
        );

    $sub_tabs['browse_plugins'] = array(
        'title' => $lang->browse_plugins,
        'link' => "index.php?module=config-plugins&amp;action=browse",
        'description' => $lang->browse_plugins_desc
        );

    // The missing tab will be added in the tab_start hook.
    $page->output_nav_tabs($sub_tabs, 'sidebox');
}

// Create modified versions of certain portal templates to use in the plugin
function adv_sidebox_install_templates()
{
	global $db, $theme, $mybb, $templates, $lang;
	
	// load portal language
	$lang->load('portal');
	
	
	
	// the whosonline avatar list parent template (left)
	$template_7_l = array(
        "title" => "adv_sidebox_whosonline_left",
        "template" => "<table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\">
	<tr>
		<td class=\"thead\"><strong>{\$lang->online}</strong></td>
	</tr>
	<tr>
		<td class=\"trow1\">
			<span class=\"smalltext\">{\$lang->online_users}<br /><strong>&raquo;</strong> {\$lang->online_counts}</span>
		</td>
	</tr>
	<tr>
		<td class=\"trow2\">{\$onlinemembers_l}</td>
	</tr>
</table>
<br />",
        "sid" => -1
    );
	$db->insert_query("templates", $template_7_l);
	
	// the whosonline avatar list parent template (right)
	$template_7_r = array(
        "title" => "adv_sidebox_whosonline_right",
        "template" => "<table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\">
	<tr>
		<td class=\"thead\"><strong>{\$lang->online}</strong></td>
	</tr>
	<tr>
		<td class=\"trow1\">
			<span class=\"smalltext\">
			{\$lang->online_users}<br /><strong>&raquo;</strong> {\$lang->online_counts}
			</span>
		</td>
	</tr>
	<tr>
		<td class=\"trow2\">{\$onlinemembers_r}</td>
	</tr>
</table>
<br />",
        "sid" => -1
    );
	$db->insert_query("templates", $template_7_r);
	
	// the whosonline avatar list child template (left)
	$template_8_l = array(
        "title" => "adv_sidebox_whosonline_memberbit_left",
        "template" => "<a href=\"{\$mybb->settings[\'bburl\']}/{\$user[\'profilelink\']}\">{\$user_avatar_l}</a>",
        "sid" => -1
    );
	$db->insert_query("templates", $template_8_l);
	
	// the whosonline avatar list child template (left)
	$template_8_r = array(
        "title" => "adv_sidebox_whosonline_memberbit_right",
        "template" => "<a href=\"{\$mybb->settings[\'bburl\']}/{\$user[\'profilelink\']}\">{\$user_avatar_r}</a>",
        "sid" => -1
    );
	$db->insert_query("templates", $template_8_r);
	
	/* $stylesheet = '
.test_class
{
	color: #f00;
}
';

	$new_stylesheet = array(
		'name'         	=> 'adv_sidebox.css',
		'tid'          		=> 1,
		'attachedto'   => '',
		'stylesheet'   	=> $stylesheet,
		'lastmodified' => TIME_NOW
	);

	$sid = $db->insert_query('themestylesheets', $new_stylesheet);
	$db->update_query('themestylesheets', array('cachefile' => "css.php?stylesheet={$sid}"), "sid='{$sid}'", 1);

	$query = $db->simple_select('themes', 'tid');
	while($theme = $db->fetch_array($query))
	{
		require_once MYBB_ADMIN_DIR.'inc/functions_themes.php';
		update_theme_stylesheet_list($theme['tid']);
	} */
}

function adv_sidebox_remove_templates()
{
	global $db, $mybb;
	
	// remove all custom templates.
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='adv_sidebox_whosonline_left'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='adv_sidebox_whosonline_right'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='adv_sidebox_whosonline_memberbit_left'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='adv_sidebox_whosonline_memberbit_right'");
	
	// remove style sheet
	$db->delete_query('themestylesheets', "name='adv_sidebox.css'");

	$query = $db->simple_select('themes', 'tid');
	while($theme = $db->fetch_array($query))
	{
		require_once MYBB_ADMIN_DIR.'inc/functions_themes.php';
		update_theme_stylesheet_list($theme['tid']);
	}
}

function adv_sidebox_get_settingsgroup()
{
	global $db;
	
	$query = $db->simple_select("settinggroups", "gid", "name='adv_sidebox_settings'", array("order_dir" => 'DESC'));
	return $db->fetch_field($query, 'gid');
}

?>