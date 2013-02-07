<?php
/*
 * This file contains the install functions for adv_sidebox.php
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

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB") || !defined("ADV_SIDEBOX"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

/* adv_sidebox_info()
 *
 * Information about the plugin used by MyBB for display as well as to connect with updates
 */
function adv_sidebox_info()
{
	global $db, $mybb, $lang;

	if(!$lang->adv_sidebox)
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
		"website"		=> "http://wildcardsearch.github.com/Advanced-Sidebox",
		"author"		=> "Wildcard",
		"authorsite"	=> "http://www.rantcentralforums.com",
		"version"		=> "1.4.1",
		"compatibility" => "16*",
		"guid" 			=> "870e9163e2ae9b606a789d9f7d4d2462",
	);
}

/* adv_sidebox_is_installed()
 *
 * Checks to see if the plugin's settingsgroup is installed. If so then assume the plugin is installed.
 */
function adv_sidebox_is_installed()
{
	return adv_sidebox_get_settingsgroup();
}

/* adv_sidebox_install()
 *
 * Add a table (sideboxes) to the DB, a column to the mybb_users table (show_sidebox), install the plugin settings, check for existing modules and install any detected.
 */
function adv_sidebox_install()
{
	global $db, $mybb, $lang;

	// create the table if it doesn't already exist.
	if(!$db->table_exists('sideboxes'))
	{
		$collation = $db->build_create_table_collation();
		$db->write_query
		(
			"CREATE TABLE " . TABLE_PREFIX . "sideboxes
			(
				id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				display_order INT(10) NOT NULL,
				box_type VARCHAR(25) NOT NULL,
				display_name VARCHAR(32) NOT NULL,
				position INT(2),
				show_on_index INT(2),
				show_on_forumdisplay INT(2),
				show_on_showthread INT(2),
				show_on_portal INT(2),
				groups TEXT,
				wrap_content INT(2),
				settings TEXT
			) ENGINE=MyISAM{$collation};"
		);
	}

	// create the table if it doesn't already exist.
	if(!$db->table_exists('custom_sideboxes'))
	{
		$collation = $db->build_create_table_collation();
		$db->write_query
		(
			"CREATE TABLE " . TABLE_PREFIX . "custom_sideboxes
			(
				id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
				name VARCHAR(32) NOT NULL,
				description VARCHAR(128) NOT NULL,
				wrap_content INT(2),
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
	if(!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}

	// settings group and settings
	adv_sidebox_create_base_settings();

	//modules
	require_once MYBB_ROOT . 'inc/plugins/adv_sidebox/adv_sidebox_classes.php';
	$dir = opendir(ADV_SIDEBOX_MODULES_DIR);

	// look for modules
	while(($module = readdir($dir)) !== false)
	{
		// a valid module is located in inc/plugins/adv_sidebox/modules/module_name and contains a file called adv_sidebox_module.php which contains (at a minimum) a function named module_name_asbnfo()
		if(is_dir(ADV_SIDEBOX_MODULES_DIR."/".$module) && !in_array($module, array(".", "..")) && file_exists(ADV_SIDEBOX_MODULES_DIR."/".$module."/adv_sidebox_module.php"))
		{
			$this_module[$module] = new Sidebox_addon($module);

			$this_module[$module]->install();
		}
	}
}

/*
 * adv_sidebox_activate()
 *
 * checks upgrade status by checking cached version info
 *
 * Derived from the work of pavemen in MyBB Publisher
 */
function adv_sidebox_activate()
{
	$old_version = adv_sidebox_get_cache_version() ;

	if(file_exists(MYBB_ROOT . '/inc/plugins/adv_sidebox/adv_sidebox_upgrade.php'))
	{
		require_once MYBB_ROOT . '/inc/plugins/adv_sidebox/adv_sidebox_upgrade.php';
    }

	adv_sidebox_set_cache_version();

 	rebuild_settings();
}

/* adv_sidebox_uninstall()
 *
 * DROP the table added to the DB and the column previously added to the mybb_users table (show_sidebox), delete the plugin settings, templates and stylesheets.
 */
function adv_sidebox_uninstall()
{
	global $db;

	//modules
	require_once MYBB_ROOT . 'inc/plugins/adv_sidebox/adv_sidebox_classes.php';
	$dir = opendir(ADV_SIDEBOX_MODULES_DIR);

	// look for modules
	while(($module = readdir($dir)) !== false)
	{
		// a valid module is located in inc/plugins/adv_sidebox/modules/module_name and contains a file called adv_sidebox_module.php which contains (at a minimum) a function named module_name_asbnfo()
		if(is_dir(ADV_SIDEBOX_MODULES_DIR."/".$module) && !in_array($module, array(".", "..")) && file_exists(ADV_SIDEBOX_MODULES_DIR."/".$module."/adv_sidebox_module.php"))
		{
			$this_module[$module] = new Sidebox_addon($module);

			$this_module[$module]->uninstall();
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

	rebuild_settings();

	adv_sidebox_unset_cache_version();
}

/*
 * Settings
  */
/* adv_sidebox_get_settingsgroup()
 *
 * retrieves the plugin's settingsgroup gid if it exists
 */
function adv_sidebox_get_settingsgroup()
{
	global $db;

	$query = $db->simple_select("settinggroups", "gid", "name='adv_sidebox_settings'", array("order_dir" => 'DESC'));
	return $db->fetch_field($query, 'gid');
}

/* adv_sidebox_build_settings_url()
 *
 * builds the url to modify plugin settings if given valid info
 *
 * @param - $gid is an integer representing a valid settingsgroup id
 */
function adv_sidebox_build_settings_url($gid)
{
	if($gid)
	{
		return "index.php?module=config-settings&amp;action=change&amp;gid=" . $gid;
	}
}

/* adv_sidebox_build_settings_link()
 *
 * builds a link to modify plugin settings if it exists
 */
function adv_sidebox_build_settings_link()
{
	global $lang;

	if(!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}

	$gid = adv_sidebox_get_settingsgroup();

	if($gid)
	{
		$url = adv_sidebox_build_settings_url($gid);

		if($url)
		{
			return "<a href=\"{$url}\" target=\"_blank\">" . $lang->adv_sidebox_plugin_settings . "</a>";
		}
	}
	return false;
}

/*
 * adv_sidebox_create_base_settings()
 *
 * separate function to create settings so that upgrade script can share it
 */
function adv_sidebox_create_base_settings()
{
	global $db, $lang;

	if(!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}

	$adv_sidebox_group = array(
		"gid" 				=> "NULL",
		"name" 				=> "adv_sidebox_settings",
		"title" 				=> "Advanced Sidebox",
		"description" 		=> $lang->adv_sidebox_settingsgroup_description,
		"disporder" 		=> "101",
		"isdefault" 			=> "no",
	);
	$db->insert_query("settinggroups", $adv_sidebox_group);

	// store the gid # for the settings
	$gid = $db->insert_id();

	$adv_sidebox_setting_1 = array(
		"sid"					=> "NULL",
		"name"				=> "adv_sidebox_on_index",
		"title"					=> $lang->adv_sidebox_show_on_index,
		"description"		=> "",
		"optionscode"	=> "yesno",
		"value"				=> '1',
		"disporder"		=> '10',
		"gid"					=> intval($gid),
	);
	$adv_sidebox_setting_2 = array(
		"sid"					=> "NULL",
		"name"				=> "adv_sidebox_on_forumdisplay",
		"title"					=> $lang->adv_sidebox_show_on_forumdisplay,
		"description"		=> "",
		"optionscode"	=> "yesno",
		"value"				=> '1',
		"disporder"		=> '20',
		"gid"					=> intval($gid),
	);
	$adv_sidebox_setting_3 = array(
		"sid"					=> "NULL",
		"name"				=> "adv_sidebox_on_showthread",
		"title"					=> $lang->adv_sidebox_show_on_threaddisplay,
		"description"		=> "",
		"optionscode"	=> "yesno",
		"value"				=> '1',
		"disporder"		=> '30',
		"gid"					=> intval($gid),
	);
	$adv_sidebox_setting_4 = array(
		"sid"					=> "NULL",
		"name"				=> "adv_sidebox_portal_replace",
		"title"					=> $lang->adv_sidebox_replace_portal_boxes,
		"description"		=> "",
		"optionscode"	=> "yesno",
		"value"				=> '1',
		"disporder"		=> '40',
		"gid"					=> intval($gid),
	);
	$adv_sidebox_setting_5 = array(
		"sid"					=> "NULL",
		"name"				=> "adv_sidebox_width_left",
		"title"					=> $lang->adv_sidebox_width . ":",
		"description"		=> "left",
		"optionscode"	=> "text",
		"value"				=> '240',
		"disporder"		=> '50',
		"gid"					=> intval($gid),
	);
	$adv_sidebox_setting_6 = array(
		"sid"					=> "NULL",
		"name"				=> "adv_sidebox_width_right",
		"title"					=> $lang->adv_sidebox_width . ":",
		"description"		=> "right",
		"optionscode"	=> "text",
		"value"				=> '240',
		"disporder"		=> '60',
		"gid"					=> intval($gid),
	);

	$update_themes_link = "<ul><li><a href=\"" . ADV_SIDEBOX_URL . "&amp;action=update_theme_select\" title=\"\">{$lang->adv_sidebox_theme_exclude_select_update_link}</a><br />{$lang->adv_sidebox_theme_exclude_select_update_description}</li></ul>";

	$adv_sidebox_setting_7 = array(
		"sid"					=> "NULL",
		"name"				=> "adv_sidebox_exclude_theme",
		"title"					=> $lang->adv_sidebox_theme_exclude_list . ":",
		"description"		=> $db->escape_string($lang->adv_sidebox_theme_exclude_list_description . $update_themes_link),
		"optionscode"	=> $db->escape_string(build_theme_exclude_select()),
		"value"				=> '',
		"disporder"		=> '70',
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
}

/*
 * versioning
 */

/*
 * adv_sidebox_get_cache_version()
 *
 * check cached version info
 *
 * Derived from the work of pavemen in MyBB Publisher
 */
function adv_sidebox_get_cache_version()
{
	global $cache, $mybb, $db;

	//get currently installed version, if there is one
	$wildcard_plugins = $cache->read('wildcard_plugins');
	if(is_array($wildcard_plugins))
	{
        return $wildcard_plugins['versions']['adv_sidebox'];
	}
    return 0;
}

/*
 * adv_sidebox_set_cache_version()
 *
 * set cached version info
 *
 * Derived from the work of pavemen in MyBB Publisher
 *
 */
function adv_sidebox_set_cache_version()
{
	global $cache;

	//get version from this plugin file
	$adv_sidebox_info = adv_sidebox_info();

	//update version cache to latest
	$wildcard_plugins = $cache->read('wildcard_plugins');
	$wildcard_plugins['versions']['adv_sidebox'] = $adv_sidebox_info['version'];
	$cache->update('wildcard_plugins', $wildcard_plugins);

    return true;
}

/*
 * adv_sidebox_unset_cache_version()
 *
 * remove cached version info
 *
 * Derived from the work of pavemen in MyBB Publisher
 */
function adv_sidebox_unset_cache_version()
{
	global $cache;

	$wildcard_plugins = $cache->read('wildcard_plugins');
	unset($wildcard_plugins['versions']['adv_sidebox']);
	$cache->update('wildcard_plugins', $wildcard_plugins);

    return true;
}

?>
