<?php
/*
 * Plug-in Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2013 WildcardSearch
 * http://www.wildcardsworld.com
 *
 * This file contains the install functions for acp.php
 */

// disallow direct access to this file for security reasons
if(!defined("IN_MYBB") || !defined("IN_ASB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

/* asb_info()
 *
 * Information about the plug-in used by MyBB for display as well as to connect with updates
 */
function asb_info()
{
	global $mybb, $lang;

	if(!$lang->asb)
	{
		$lang->load('asb');
	}

	$extra_links = "<br />";
	$settings_link = asb_build_settings_link();
	if($settings_link)
	{
		if(file_exists(MYBB_ROOT . 'inc/plugins/adv_sidebox/acp_functions.php'))
		{
			$remove_link = <<<EOF

		<li>
			<span style="color: red;">{$lang->asb_remove_old_files_desc}</span><br /><a href="{$mybb->settings['bburl']}/inc/plugins/asb/cleanup.php" title="{$lang->asb_remove_old_files}">{$lang->asb_remove_old_files}</a>
		</li>
EOF;
		}

		$settings_link = <<<EOF
	<li style="list-style-image: url(styles/default/images/icons/custom.gif)">
		{$settings_link}
	</li>
EOF;
		$url = ASB_URL;
		$extra_links = <<<EOF
<ul>
	{$settings_link}
	<li>
		<a href="{$url}" title="{$lang->asb_manage_sideboxes}">{$lang->asb_manage_sideboxes}</a>
	</li>{$remove_link}
	<li>
		<a href="javascript:void()" onclick="window.open('{$mybb->settings['bburl']}/inc/plugins/asb/help/index.php?topic=install', 'mywindowtitle', 'width=840, height=520, scrollbars=yes')" title="{$lang->asb_help}">{$lang->asb_help}</a>
	</li>
</ul>
EOF;
	}

	$button_pic = $mybb->settings['bburl'] . '/inc/plugins/asb/images/donate.gif';
	$border_pic = $mybb->settings['bburl'] . '/inc/plugins/asb/images/pixel.gif';
	$asb_description = <<<EOF
<table width="100%">
	<tbody>
		<tr>
			<td>
				{$lang->asb_description1}<br/><br/>{$lang->asb_description2}{$extra_links}
			</td>
			<td style="text-align: center;">
				<img src="{$mybb->settings['bburl']}/inc/plugins/asb/images/asb_logo_80.png" alt="{$lang->asb_logo}" title="{$lang->asb_logo}"/><br /><br />
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
					<input type="hidden" name="cmd" value="_s-xclick">
					<input type="hidden" name="hosted_button_id" value="VA5RFLBUC4XM4">
					<input type="image" src="{$button_pic}" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
					<img alt="" border="0" src="{$border_pic}" width="1" height="1">
				</form>
			</td>
		</tr>
	</tbody>
</table>
EOF;

	$name = <<<EOF
<span style="font-familiy: arial; font-size: 1.5em; color: #2B387C; text-shadow: 2px 2px 2px #00006A;">{$lang->asb}</span>
EOF;
	$author = <<<EOF
</a></small></i><a href="http://www.rantcentralforums.com" title="Rant Central"><span style="font-family: Courier New; font-weight: bold; font-size: 1.2em; color: #0e7109;">Wildcard</span></a><i><small><a>
EOF;

	// This array returns information about the plug-in, some of which was prefabricated above based on whether the plugin has been installed or not.
	return array
	(
		"name"					=> $name,
		"description"			=> $asb_description,
		"website"				=> "https://github.com/WildcardSearch/Advanced-Sidebox",
		"author"				=> $author,
		"authorsite"			=> "http://www.rantcentralforums.com",
		"version"				=> "2.0.2",
		"compatibility" 		=> "16*",
		"guid" 					=> "870e9163e2ae9b606a789d9f7d4d2462",
	);
}

/* asb_is_installed()
 *
 * Checks to see if the plug-in's settings group is installed. If so then assume the plug-in is installed.
 */
function asb_is_installed()
{
	global $db;
	return asb_get_settingsgroup();
}

/* asb_install()
 *
 * Add tables, a column to the mybb_users table (show_sidebox), install the plug-in setting group (asb_settings), settings, a template (asb_wrapped_sidebox), check for existing modules and install any detected.
 */
function asb_install()
{
	global $db, $mybb, $lang;

	if(!$lang->asb)
	{
		$lang->load('asb');
	}

	// settings tables, templates, groups and setting groups
	require_once MYBB_ROOT . 'inc/plugins/asb/functions_install.php';
	require_once MYBB_ROOT . 'inc/plugins/asb/classes/installer.php';

	$installer = new WildcardPluginInstaller(MYBB_ROOT . 'inc/plugins/asb/install_data.php');
	$installer->install();
	asb_create_script_info();

	@unlink(MYBB_ROOT . 'inc/plugins/adv_sidebox.php');
}

/*
 * asb_activate()
 *
 *  version control (a la pavemen), upgrade if necessary and change permissions for ASB
 */
function asb_activate()
{
	// get the last cached version
	require_once MYBB_ROOT . 'inc/plugins/asb/functions_install.php';

	// if we just upgraded . . .
	$old_version = asb_get_cache_version();
	$info = asb_info();
	if(version_compare($old_version, $info['version'], '<'))
	{
		global $lang;
		if(!$lang->asb)
		{
			$lang->load('asb');
		}

		require_once MYBB_ROOT . 'inc/plugins/asb/classes/installer.php';
		$installer = new WildcardPluginInstaller(MYBB_ROOT . 'inc/plugins/asb/install_data.php');
		$installer->install();
	}
	asb_set_cache_version();

	// change the permissions to on by default
	change_admin_permission('config', 'asb');
}

/*
 * asb_deactivate()
 *
 * simply disables admin permissions for side boxes
 */
function asb_deactivate()
{
	// remove the permissions
	change_admin_permission('config', 'asb', -1);
}

/* asb_uninstall()
 *
 * DROP the table added to the DB and the column previously added to the mybb_users table (show_sidebox), delete the plug-in settings, templates and style sheets.
 */
function asb_uninstall()
{
	global $mybb;

	require_once MYBB_ROOT . 'inc/plugins/asb/classes/forum.php';
	// remove the modules first
	$addons = asb_get_all_modules();

	// if there are add-on modules installed
	if(is_array($addons))
	{
		// uninstall them
		foreach($addons as $addon)
		{
			$addon->uninstall();
		}
	}

	require_once MYBB_ROOT . 'inc/plugins/asb/functions_install.php';
	require_once MYBB_ROOT . 'inc/plugins/asb/classes/installer.php';

	$installer = new WildcardPluginInstaller(MYBB_ROOT . 'inc/plugins/asb/install_data.php');
	$installer->uninstall();

	// delete our cached version
	asb_unset_cache_version();
}

/*
 * settings
 */

/*
 * asb_get_settingsgroup()
 *
 * retrieves the plug-in's settings group gid if it exists
 * attempts to cache repeat calls
 */
function asb_get_settingsgroup()
{
	static $asb_settings_gid;

	// if we have already stored the value
	if(isset($asb_settings_gid))
	{
		// don't waste a query
		$gid = (int) $asb_settings_gid;
	}
	else
	{
		global $db;

		// otherwise we will have to query the db
		$query = $db->simple_select("settinggroups", "gid", "name='asb_settings'");
		$gid = (int) $db->fetch_field($query, 'gid');
	}
	return $gid;
}

/*
 * asb_build_settings_url()
 *
 * builds the url to modify plug-in settings if given valid info
 *
 * @param - $gid is an integer representing a valid settings group id
 */
function asb_build_settings_url($gid)
{
	if($gid)
	{
		return "index.php?module=config-settings&amp;action=change&amp;gid=" . $gid;
	}
}

/*
 * asb_build_settings_link()
 *
 * builds a link to modify plug-in settings if it exists
 */
function asb_build_settings_link()
{
	global $lang;

	if(!$lang->asb)
	{
		$lang->load('asb');
	}

	$gid = asb_get_settingsgroup();

	// does the group exist?
	if($gid)
	{
		// if so build the URL
		$url = asb_build_settings_url($gid);

		// did we get a URL?
		if($url)
		{
			// if so build the link
			return "<a href=\"{$url}\" title=\"{$lang->asb_plugin_settings}\">{$lang->asb_plugin_settings}</a>";
		}
	}
	return false;
}

?>
