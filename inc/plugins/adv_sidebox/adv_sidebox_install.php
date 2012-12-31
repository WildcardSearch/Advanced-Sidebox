<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB") || !defined("ADV_SIDEBOX"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

// Information about the plugin used by MyBB for display as well as to connect with updates
function adv_sidebox_info()
{
	global $db, $mybb, $lang;

	if (!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}
	
	$settings_link = adv_sidebox_build_settings_link();

	if($settings_link)
	{
		$settings_link = "<ul><li>{$settings_link}</li></ul>";
	}
	else
	{
		$settings_link = "<br />";
	}

	// This array returns information about the plugin, some of which was prefabricated above based on whether the plugin has been installed or not.
	return array(
		"name"			=> $lang->adv_sidebox_name,
		"description"	=> $lang->adv_sidebox_description1 . "<br/><br/>" . $lang->adv_sidebox_description2 . $settings_link,
		"website"		=> "https://github.com/WildcardSearch/Advanced-Sidebox",
		"author"		=> "Wildcard",
		"authorsite"	=> "http://www.rantcentralforums.com",
		"version"		=> "1.0",
		"compatibility" => "16*",
		"guid" 			=> "870e9163e2ae9b606a789d9f7d4d2462",
	);
}

// Checks to see if the plugin's settingsgroup is installed. If so then assume the plugin is installed.
function adv_sidebox_is_installed()
{
	return adv_sidebox_get_settingsgroup();
}

// Add a table (sideboxes) to the DB, a column to the mybb_users table (show_sidebox), install the plugin settings, check for existing modules and install any detected.
function adv_sidebox_install()
{
	global $db, $mybb, $lang;
	
	$errors = array();

	// create the table if it doesn't already exist.
	if (!$db->table_exists('sideboxes'))
	{
		$collation = $db->build_create_table_collation();
		$db->write_query
		(
			"CREATE TABLE " . TABLE_PREFIX . "sideboxes
			(
				id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				display_order INT(10) NOT NULL,
				box_type VARCHAR(25) NOT NULL,
				position INT(2),
				content TEXT
			) ENGINE=MyISAM{$collation};"
		);
	}

	// create the table if it doesn't already exist.
	if (!$db->table_exists('custom_sideboxes'))
	{
		$collation = $db->build_create_table_collation();
		$db->write_query
		(	
			"CREATE TABLE " . TABLE_PREFIX . "custom_sideboxes
			(
				id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				name VARCHAR(32) NOT NULL,
				description VARCHAR(128) NOT NULL,
				content TEXT
			) ENGINE=MyISAM{$collation};"
		);
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

	// put it all together
	$theme_select = 'php
<select multiple name=\"upsetting[adv_sidebox_exclude_theme][]\" size=\"' . $theme_count . '\">' . $theme_select . '</select>';

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

	$db->insert_query("settings", $adv_sidebox_setting_1);
	$db->insert_query("settings", $adv_sidebox_setting_2);
	$db->insert_query("settings", $adv_sidebox_setting_3);
	$db->insert_query("settings", $adv_sidebox_setting_4);
	$db->insert_query("settings", $adv_sidebox_setting_5);
	$db->insert_query("settings", $adv_sidebox_setting_6);
	$db->insert_query("settings", $adv_sidebox_setting_7);
	
	rebuild_settings();

	//modules
	$modules_dir = MYBB_ROOT. "inc/plugins/adv_sidebox/modules";
	$dir = opendir($modules_dir);

	// look for modules
	while(($module = readdir($dir)) !== false)
	{
		// a valid module is located in inc/plugins/adv_sidebox/modules/module_name and contains a file called adv_sidebox_module.php which contains (at a minimum) a function named module_name_info()
		if(is_dir($modules_dir."/".$module) && !in_array($module, array(".", "..")) && file_exists($modules_dir."/".$module."/adv_sidebox_module.php"))
		{
			require_once $modules_dir."/".$module."/adv_sidebox_module.php";

			$info_function = $module . '_info';
			$is_installed_function = $module . '_is_installed';

			if(function_exists($module . '_is_installed') && function_exists($module . '_info'))
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
						else
						{
							$error[$module] = 'A missing _install function prevented the installation of this module.';
						}
					}
					else
					{
						$error[$module] = 'Remains from a previous installation prevented the installation of this module.';
					}
				}
			}
		}
	}
	
	if(!empty($errors))
	{
		foreach($errors as $module => $this_error)
		{
			$module_errors .= 'Module: ' . $module . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Error: ' . $this_error . '<br />';
		}
		
		flash_message('Advanced Sidebox installed successfully, but couldn\'t install all detected modules:<br /><br />' . $module_errors, "success");
		admin_redirect(ADV_SIDEBOX_URL);
	}
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
		if(is_dir($modules_dir."/".$module) && !in_array($module, array(".", "..")) && file_exists($modules_dir."/".$module."/adv_sidebox_module.php"))
		{
			require_once $modules_dir."/".$module."/adv_sidebox_module.php";

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

	// remove the table
	$db->drop_table('sideboxes');
	$db->drop_table('custom_sideboxes');

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
}

function adv_sidebox_get_settingsgroup()
{
	global $db;

	$query = $db->simple_select("settinggroups", "gid", "name='adv_sidebox_settings'", array("order_dir" => 'DESC'));
	return $db->fetch_field($query, 'gid');
}

function adv_sidebox_build_settings_link()
{
	global $lang;
	
	if (!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}
		
	$gid = adv_sidebox_get_settingsgroup();
	
	if($gid)
	{
		return "<a href=\"" . $mybb->settings['bburl'] . "/admin/index.php?module=config-settings&amp;action=change&amp;gid=" . $gid . "\" target=\"_blank\">" . $lang->adv_sidebox_plugin_settings . "</a>";
	}
	return false;
}

?>