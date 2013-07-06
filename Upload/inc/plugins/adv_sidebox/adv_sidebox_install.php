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
		$extra_links = "<ul><li>{$settings_link}</li><li><a href=\"" . ADV_SIDEBOX_URL . "\" title=\"{$lang->adv_sidebox_manage_sideboxes}\">{$lang->adv_sidebox_manage_sideboxes}</a></li><li><a href=\"javascript:void()\" onclick=\"window.open('{$mybb->settings['bburl']}/inc/plugins/adv_sidebox/help/index.php?topic=install', 'mywindowtitle', 'width=840, height=520, scrollbars=yes')\" title=\"Help\">Help</a></li></ul>";
	}
	else
	{
		$extra_links = "<br />";
	}

	$adv_sidebox_description = "
<table width=\"100%\">
	<tbody>
		<tr>
			<td>{$lang->adv_sidebox_description1}<br/><br/>{$lang->adv_sidebox_description2}{$extra_links}
			</td>
			<td style=\"text-align: center;\"><img style=\"\" src=\"{$mybb->settings['bburl']}/inc/plugins/adv_sidebox/images/asb_logo_80.png\" alt=\"{$lang->adv_sidebox_logo}\" title=\"{$lang->adv_sidebox_logo}\"/><br /><br />
			<form action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\" target=\"_top\">
				<input type=\"hidden\" name=\"cmd\" value=\"_s-xclick\">
				<input type=\"hidden\" name=\"hosted_button_id\" value=\"VA5RFLBUC4XM4\">
				<input style=\"\" type=\"image\" src=\"https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif\" border=\"0\" name=\"submit\" alt=\"PayPal - The safer, easier way to pay online!\">
				<img alt=\"\" border=\"0\" src=\"https://www.paypalobjects.com/en_US/i/scr/pixel.gif\" width=\"1\" height=\"1\">
			</form>
			</td>
		</tr>
	</tbody>
</table>";

	$name = "<span style=\"font-familiy: arial; font-size: 1.5em; color: #2B387C; text-shadow: 2px 2px 2px #00006A;\">{$lang->adv_sidebox_name}</span>";
	$author = "</a></small></i><a href=\"http://www.rantcentralforums.com\" title=\"Rant Central\"><span style=\"font-family: Courier New; font-weight: bold; font-size: 1.2em; color: #0e7109;\">Wildcard</span></a><i><small><a>";

	// This array returns information about the plugin, some of which was prefabricated above based on whether the plugin has been installed or not.
	return array(
		"name"					=> $name,
		"description"			=> $adv_sidebox_description,
		"website"				=> "http://wildcardsearch.github.com/Advanced-Sidebox",
		"author"				=> $author,
		"authorsite"			=> "http://www.rantcentralforums.com",
		"version"				=> "1.7.2",
		"compatibility" 		=> "16*",
		"guid" 					=> "870e9163e2ae9b606a789d9f7d4d2462",
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
	global $db, $mybb;

	adv_sidebox_do_db_changes();

	// settings group and settings
	adv_sidebox_create_base_settings();

	require_once MYBB_ROOT . 'inc/plugins/adv_sidebox/adv_sidebox_classes.php';

	/*
	 * by simply creating the Sidebox_handler object we have validated and installed all modules
	 */
	$adv_sidebox = new Sidebox_handler('', true);
}

/*
 * adv_sidebox_activate()
 *
 * checks upgrade status by checking cached version info
 *
 * derived from the work of pavemen in MyBB Publisher
 */
function adv_sidebox_activate()
{
	// get the last cached version
	$old_version = adv_sidebox_get_cache_version();

	// if the upgrade script exists
	if(file_exists(MYBB_ROOT . '/inc/plugins/adv_sidebox/adv_sidebox_upgrade.php'))
	{
		/*
		 * this script will compare the current version of ASB to the cached version number and upgrade if necessary
		 */
		require_once MYBB_ROOT . '/inc/plugins/adv_sidebox/adv_sidebox_upgrade.php';
    }

	// now set the cache version
	adv_sidebox_set_cache_version();

	// change the permissions to on by default
	change_admin_permission('config', 'adv_sidebox');
}

/*
 * adv_sidebox_deactivate()
 *
 * simply disables admin permissions for side boxes
 */
function adv_sidebox_deactivate()
{
	// remove the permissions
	change_admin_permission('config', 'adv_sidebox', -1);
}

/* adv_sidebox_uninstall()
 *
 * DROP the table added to the DB and the column previously added to the mybb_users table (show_sidebox), delete the plugin settings, templates and stylesheets.
 */
function adv_sidebox_uninstall()
{
	global $db;

	// remove the modules first
	require_once MYBB_ROOT . 'inc/plugins/adv_sidebox/adv_sidebox_classes.php';

	/*
	 * load the handler (no script filter and true to load everything)
	 */
	$adv_sidebox = new Sidebox_handler('', true);

	// if there are add-on modules installed
	if(is_array($adv_sidebox->addons))
	{
		// uninstall them
		foreach($adv_sidebox->addons as $addon)
		{
			$addon->uninstall();
		}
	}

	// undo changes, delete tables, fields and settings
	adv_sidebox_undo_db_changes();

	// delete our cached version
	adv_sidebox_unset_cache_version();
}

/*
 * settings
 */

/*
 * adv_sidebox_get_settingsgroup()
 *
 * retrieves the plugin's settingsgroup gid if it exists
 * attempts to cache repeat queries
 */
function adv_sidebox_get_settingsgroup()
{
	static $adv_sidebox_settings_gid;

	// if we have already stored the value
	if(isset($adv_sidebox_settings_gid))
	{
		// don't waste a query
		$gid = (int) $adv_sidebox_settings_gid;
	}
	else
	{
		global $db;

		// otherwise we will have to query the db
		$query = $db->simple_select("settinggroups", "gid", "name='adv_sidebox_settings'", array("order_dir" => 'DESC'));
		$gid = (int) $db->fetch_field($query, 'gid');
	}
	return $gid;
}

/*
 * adv_sidebox_build_settings_url()
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

/*
 * adv_sidebox_build_settings_link()
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

	// does the group exist?
	if($gid)
	{
		// if so build the URL
		$url = adv_sidebox_build_settings_url($gid);

		// did we get a URL?
		if($url)
		{
			// if so build the link
			return "<a href=\"{$url}\" title=\"{$lang->adv_sidebox_plugin_settings}\">{$lang->adv_sidebox_plugin_settings}</a>";
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

	$gid = (int) adv_sidebox_get_settingsgroup();

	if($gid == 0)
	{
		$adv_sidebox_group = array(
			"gid" 					=> "NULL",
			"name" 				=> "adv_sidebox_settings",
			"title" 					=> "Advanced Sidebox",
			"description" 		=> $lang->adv_sidebox_settingsgroup_description,
			"disporder" 			=> "101",
			"isdefault" 			=> "no",
		);
		$db->insert_query("settinggroups", $adv_sidebox_group);

		// store the gid # for the settings
		$gid = $db->insert_id();
	}

	$adv_sidebox_settings[] = array
	(
		"sid"						=> "NULL",
		"name"					=> "adv_sidebox_on_index",
		"title"					=> $lang->adv_sidebox_show_on_index,
		"description"			=> "",
		"optionscode"		=> "yesno",
		"value"					=> '1',
		"disporder"			=> '10',
		"gid"						=> (int) $gid,
	);
	$adv_sidebox_settings[] = array
	(
		"sid"						=> "NULL",
		"name"					=> "adv_sidebox_on_forumdisplay",
		"title"					=> $lang->adv_sidebox_show_on_forumdisplay,
		"description"			=> "",
		"optionscode"		=> "yesno",
		"value"					=> '1',
		"disporder"			=> '20',
		"gid"						=> (int) $gid,
	);
	$adv_sidebox_settings[] = array
	(
		"sid"						=> "NULL",
		"name"					=> "adv_sidebox_on_showthread",
		"title"					=> $lang->adv_sidebox_show_on_threaddisplay,
		"description"			=> "",
		"optionscode"		=> "yesno",
		"value"					=> '1',
		"disporder"			=> '30',
		"gid"						=> (int) $gid,
	);
	$adv_sidebox_settings[] = array
	(
		"sid"						=> "NULL",
		"name"					=> "adv_sidebox_on_member",
		"title"					=> $lang->adv_sidebox_show_on_member,
		"description"			=> "",
		"optionscode"		=> "yesno",
		"value"					=> '1',
		"disporder"			=> '40',
		"gid"						=> (int) $gid,
	);
	$adv_sidebox_settings[] = array
	(
		"sid"						=> "NULL",
		"name"					=> "adv_sidebox_on_memberlist",
		"title"					=> $lang->adv_sidebox_show_on_memberlist,
		"description"			=> "",
		"optionscode"		=> "yesno",
		"value"					=> '1',
		"disporder"			=> '50',
		"gid"						=> (int) $gid,
	);
	$adv_sidebox_settings[] = array
	(
		"sid"						=> "NULL",
		"name"					=> "adv_sidebox_on_showteam",
		"title"					=> $lang->adv_sidebox_show_on_showteam,
		"description"			=> "",
		"optionscode"		=> "yesno",
		"value"					=> '1',
		"disporder"			=> '60',
		"gid"						=> (int) $gid,
	);
	$adv_sidebox_settings[] = array
	(
		"sid"						=> "NULL",
		"name"					=> "adv_sidebox_on_stats",
		"title"					=> $lang->adv_sidebox_show_on_stats,
		"description"			=> "",
		"optionscode"		=> "yesno",
		"value"					=> '1',
		"disporder"			=> '70',
		"gid"						=> (int) $gid,
	);
	$adv_sidebox_settings[] = array
	(
		"sid"						=> "NULL",
		"name"					=> "adv_sidebox_portal_replace",
		"title"					=> $lang->adv_sidebox_replace_portal_boxes,
		"description"			=> "",
		"optionscode"		=> "yesno",
		"value"					=> '1',
		"disporder"			=> '80',
		"gid"						=> (int) $gid,
	);
	$adv_sidebox_settings[] = array
	(
		"sid"						=> "NULL",
		"name"					=> "adv_sidebox_show_empty_boxes",
		"title"					=> $lang->adv_sidebox_show_empty_boxes . ":",
		"description"			=> $db->escape_string($lang->adv_sidebox_show_empty_boxes_desc),
		"optionscode"		=> "yesno",
		"value"					=> '1',
		"disporder"			=> '90',
		"gid"						=> (int) $gid,
	);
	$adv_sidebox_settings[] = array
	(
		"sid"						=> "NULL",
		"name"					=> "adv_sidebox_show_toggle_icons",
		"title"					=> $lang->adv_sidebox_show_toggle_icons,
		"description"			=> '',
		"optionscode"		=> "yesno",
		"value"					=> '0',
		"disporder"			=> '100',
		"gid"						=> (int) $gid,
	);
	$adv_sidebox_settings[] = array
	(
		"sid"						=> "NULL",
		"name"					=> "adv_sidebox_width_left",
		"title"					=> $lang->adv_sidebox_width . ":",
		"description"			=> "left",
		"optionscode"		=> "text",
		"value"					=> '160',
		"disporder"			=> '110',
		"gid"						=> (int) $gid,
	);
	$adv_sidebox_settings[] = array
	(
		"sid"						=> "NULL",
		"name"					=> "adv_sidebox_width_right",
		"title"					=> $lang->adv_sidebox_width . ":",
		"description"			=> "right",
		"optionscode"		=> "text",
		"value"					=> '160',
		"disporder"			=> '120',
		"gid"						=> (int) $gid,
	);

	$update_themes_link = "<ul><li><a href=\"" . ADV_SIDEBOX_URL . "&amp;action=update_theme_select\" title=\"\">{$lang->adv_sidebox_theme_exclude_select_update_link}</a><br />{$lang->adv_sidebox_theme_exclude_select_update_description}</li></ul>";

	$adv_sidebox_settings[] = array(
		"sid"						=> "NULL",
		"name"					=> "adv_sidebox_exclude_theme",
		"title"					=> $lang->adv_sidebox_theme_exclude_list . ":",
		"description"			=> $db->escape_string($lang->adv_sidebox_theme_exclude_list_description . $update_themes_link),
		"optionscode"		=> $db->escape_string(build_theme_exclude_select()),
		"value"					=> '',
		"disporder"			=> '130',
		"gid"						=> (int) $gid,
	);

	// loop through all the settings
	foreach($adv_sidebox_settings as $this_setting)
	{
		// does the setting already exist?
		$query = $db->simple_select('settings', 'sid', "name='{$this_setting['name']}'");
		if($db->num_rows($query) > 0)
		{
			// if so update the info (but leave the value alone)
			unset($this_setting['sid']);
			unset($this_setting['value']);
			$db->update_query('settings', $this_setting, "name='{$this_setting['name']}'");
		}
		else
		{
			// if not create it
			$db->insert_query('settings', $this_setting);
		}
	}
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
 * derived from the work of pavemen in MyBB Publisher
 */
function adv_sidebox_get_cache_version()
{
	global $cache, $mybb, $db;

	// get currently installed version, if there is one
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
 * derived from the work of pavemen in MyBB Publisher
 *
 */
function adv_sidebox_set_cache_version()
{
	global $cache;

	// get version from this plugin file
	$adv_sidebox_info = adv_sidebox_info();

	// update version cache to latest
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
 * derived from the work of pavemen in MyBB Publisher
 */
function adv_sidebox_unset_cache_version()
{
	global $cache;

	$wildcard_plugins = $cache->read('wildcard_plugins');
	unset($wildcard_plugins['versions']['adv_sidebox']);
	$cache->update('wildcard_plugins', $wildcard_plugins);

    return true;
}

/*
 * adv_sidebox_do_db_changes()
 *
 * makes all DB changes
 */
function adv_sidebox_do_db_changes()
{
	global $db;

	// create the table if it doesn't already exist.
	if($db->table_exists('sideboxes'))
	{
		if(!$db->field_exists('id', 'sideboxes'))
		{
			$db->write_query("ALTER TABLE ".TABLE_PREFIX."sideboxes ADD id id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY");
		}
		if(!$db->field_exists('display_order', 'sideboxes'))
		{
			$db->write_query("ALTER TABLE ".TABLE_PREFIX."sideboxes ADD display_order INT(10) NOT NULL");
		}
		if(!$db->field_exists('box_type', 'sideboxes'))
		{
			$db->write_query("ALTER TABLE ".TABLE_PREFIX."sideboxes ADD box_type VARCHAR(25) NOT NULL");
		}
		if(!$db->field_exists('display_name', 'sideboxes'))
		{
			$db->write_query("ALTER TABLE ".TABLE_PREFIX."sideboxes ADD display_name VARCHAR(32) NOT NULL");
		}
		if(!$db->field_exists('position', 'sideboxes'))
		{
			$db->write_query("ALTER TABLE ".TABLE_PREFIX."sideboxes ADD position INT(2)");
		}
		if(!$db->field_exists('show_on_index', 'sideboxes'))
		{
			$db->write_query("ALTER TABLE ".TABLE_PREFIX."sideboxes ADD show_on_index INT(2) DEFAULT 1");
		}
		if(!$db->field_exists('show_on_forumdisplay', 'sideboxes'))
		{
			$db->write_query("ALTER TABLE ".TABLE_PREFIX."sideboxes ADD show_on_forumdisplay INT(2) DEFAULT 1");
		}
		if(!$db->field_exists('show_on_showthread', 'sideboxes'))
		{
			$db->write_query("ALTER TABLE ".TABLE_PREFIX."sideboxes ADD show_on_showthread INT(2) DEFAULT 1");
		}
		if(!$db->field_exists('show_on_member', 'sideboxes'))
		{
			$db->write_query("ALTER TABLE ".TABLE_PREFIX."sideboxes ADD show_on_member INT(2) DEFAULT 1");
		}
		if(!$db->field_exists('show_on_memberlist', 'sideboxes'))
		{
			$db->write_query("ALTER TABLE ".TABLE_PREFIX."sideboxes ADD show_on_memberlist INT(2) DEFAULT 1");
		}
		if(!$db->field_exists('show_on_showteam', 'sideboxes'))
		{
			$db->write_query("ALTER TABLE ".TABLE_PREFIX."sideboxes ADD show_on_showteam INT(2) DEFAULT 1");
		}
		if(!$db->field_exists('show_on_stats', 'sideboxes'))
		{
			$db->write_query("ALTER TABLE ".TABLE_PREFIX."sideboxes ADD show_on_stats INT(2) DEFAULT 1");
		}
		if(!$db->field_exists('show_on_portal', 'sideboxes'))
		{
			$db->write_query("ALTER TABLE ".TABLE_PREFIX."sideboxes ADD show_on_portal INT(2) DEFAULT 1");
		}
		if(!$db->field_exists('groups', 'sideboxes'))
		{
			$db->write_query("ALTER TABLE ".TABLE_PREFIX."sideboxes ADD groups TEXT");
		}
		if(!$db->field_exists('settings', 'sideboxes'))
		{
			$db->write_query("ALTER TABLE ".TABLE_PREFIX."sideboxes ADD settings TEXT");
		}
		if(!$db->field_exists('wrap_content', 'sideboxes'))
		{
			$db->write_query("ALTER TABLE ".TABLE_PREFIX."sideboxes ADD wrap_content INT(2)");
		}
	}
	else
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
				show_on_memberlist INT(2),
				show_on_member INT(2),
				show_on_showteam INT(2),
				show_on_stats INT(2),
				groups TEXT,
				settings TEXT,
				wrap_content INT(2)
			) ENGINE=MyISAM{$collation};"
		);
	}

	// create the table if it doesn't already exist.
	if($db->table_exists('custom_sideboxes'))
	{
		if(!$db->field_exists('id', 'custom_sideboxes'))
		{
			$db->write_query("ALTER TABLE ".TABLE_PREFIX."custom_sideboxes ADD id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY");
		}
		if(!$db->field_exists('name', 'custom_sideboxes'))
		{
			$db->write_query("ALTER TABLE ".TABLE_PREFIX."custom_sideboxes ADD name VARCHAR(32) NOT NULL");
		}
		if(!$db->field_exists('description', 'custom_sideboxes'))
		{
			$db->write_query("ALTER TABLE ".TABLE_PREFIX."custom_sideboxes ADD description VARCHAR(128) NOT NULL");
		}
		if(!$db->field_exists('wrap_content', 'custom_sideboxes'))
		{
			$db->write_query("ALTER TABLE ".TABLE_PREFIX."custom_sideboxes ADD wrap_content INT(2) DEFAULT 0");
		}
		if(!$db->field_exists('content', 'custom_sideboxes'))
		{
			$db->write_query("ALTER TABLE ".TABLE_PREFIX."custom_sideboxes ADD content TEXT");
		}
	}
	else
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
	if(!$db->field_exists('show_sidebox', 'users'))
	{
		$db->write_query("ALTER TABLE ".TABLE_PREFIX."users ADD show_sidebox varchar(1) DEFAULT '1'");
	}
}

/*
 * adv_sidebox_undo_db_changes()
 *
 * undo all DB changes
 */
function adv_sidebox_undo_db_changes()
{
	global $db;

	// remove the table
	$db->drop_table('sideboxes');
	$db->drop_table('custom_sideboxes');

	// remove then column from the mybb_users table
	$db->write_query("ALTER TABLE ".TABLE_PREFIX."users DROP COLUMN show_sidebox");

	// remove all the core settings and the group
	$db->query("DELETE FROM " . TABLE_PREFIX . "settinggroups WHERE name='adv_sidebox_settings'");

	$settings_list = array
	(
		"adv_sidebox_on_index",
		"adv_sidebox_on_forumdisplay",
		"adv_sidebox_on_showthread",
		"adv_sidebox_on_member",
		"adv_sidebox_on_memberlist",
		"adv_sidebox_on_showteam",
		"adv_sidebox_on_stats",
		"adv_sidebox_portal_replace",
		"adv_sidebox_show_empty_boxes",
		"adv_sidebox_show_toggle_icons",
		"adv_sidebox_width_left",
		"adv_sidebox_width_right",
		"adv_sidebox_exclude_theme"
	);

	// delete them all
	foreach($settings_list as $setting)
	{
		$query = "DELETE FROM " . TABLE_PREFIX . "settings WHERE name='{$setting}'";
		$db->query($query);
	}

	rebuild_settings();
}

?>
