<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * This file contains the install functions for acp.php
 */

// disallow direct access to this file for security reasons
if (!defined('IN_MYBB') ||
	!defined('IN_ASB')) {
	die('Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.');
}

/**
 * information about the plugin used by MyBB for display as well as to connect with updates
 *
 * @return array plugin info
 */
function asb_info()
{
	global $mybb, $lang, $cp_style, $cache;

	if (!$lang->asb) {
		$lang->load('asb');
	}

	$extra_links = '<br />';
	$settings_link = asb_build_settings_link();
	if ($settings_link) {
		if (file_exists(MYBB_ROOT . 'inc/plugins/asb/cleanup.php') &&
		   file_exists(MYBB_ROOT . 'inc/plugins/adv_sidebox/acp_functions.php')) {
			$remove_link = <<<EOF

		<li>
			<span style="color: red;">{$lang->asb_remove_old_files_desc}</span><br /><a href="{$mybb->settings['bburl']}/inc/plugins/asb/cleanup.php" title="{$lang->asb_remove_old_files}">{$lang->asb_remove_old_files}</a>
		</li>
EOF;
		}

		// only show Manage Sideboxes link if active
		$plugin_list = $cache->read('plugins');
		$manage_link = '';
		if (!empty($plugin_list['active']) &&
			is_array($plugin_list['active']) &&
			in_array('asb', $plugin_list['active'])) {
			$url = ASB_URL;
			$manage_link = <<<EOF
	<li style="list-style-image: url(styles/{$cp_style}/images/asb/manage.png)">
		<a href="{$url}" title="{$lang->asb_manage_sideboxes}">{$lang->asb_manage_sideboxes}</a>
	</li>
EOF;
		}

		$settings_link = <<<EOF
	<li style="list-style-image: url(styles/{$cp_style}/images/asb/settings.png)">
		{$settings_link}
	</li>
EOF;
		$extra_links = <<<EOF
<ul>
	{$settings_link}
	{$manage_link}{$remove_link}
	<li style="list-style-image: url(styles/{$cp_style}/images/asb/help.png)">
		<a href="https://github.com/WildcardSearch/Advanced-Sidebox/wiki/Help-Installation" title="{$lang->asb_help}">{$lang->asb_help}</a>
	</li>
</ul>
EOF;

		$button_pic = "styles/{$cp_style}/images/asb/donate.png";
		$border_pic = "styles/{$cp_style}/images/asb/pixel.png";
		$asb_description = <<<EOF
<table width="100%">
	<tbody>
		<tr>
			<td>
				{$lang->asb_description1}<br/><br/>{$lang->asb_description2}{$extra_links}
			</td>
			<td style="text-align: center;">
				<img src="styles/{$cp_style}/images/asb/logo.png" alt="{$lang->asb_logo}" title="{$lang->asb_logo}"/><br /><br />
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
	} else {
		$asb_description = $lang->asb_description1;
	}

	$name = <<<EOF
<span style="font-familiy: arial; font-size: 1.5em; color: #2B387C; text-shadow: 2px 2px 2px #00006A;">{$lang->asb}</span>
EOF;
	$author = <<<EOF
</a></small></i><a href="http://www.rantcentralforums.com" title="Rant Central"><span style="font-family: Courier New; font-weight: bold; font-size: 1.2em; color: #0e7109;">Wildcard</span></a><i><small><a>
EOF;

	// This array returns information about the plugin, some of which was prefabricated above based on whether the plugin has been installed or not.
	return array(
		"name" => $name,
		"description" => $asb_description,
		"website" => 'https://github.com/WildcardSearch/Advanced-Sidebox',
		"author" => $author,
		"authorsite" => 'http://www.rantcentralforums.com',
		"version" => ASB_VERSION,
		"compatibility" => '18*',
		"codename" => 'asb',
	);
}

/**
 * check to see if the plugin's settings group is installed-- assume the plugin is installed if so
 *
 * @return bool true if installed, false if not
 */
function asb_is_installed()
{
	return asb_get_settingsgroup();
}

/**
 * add tables, a column to the mybb_users table (show_sidebox),
 * install the plugin setting group (asb_settings), settings, templates and
 * check for existing modules and install any detected
 *
 * @return void
 */
function asb_install()
{
	global $lang;

	if (!$lang->asb) {
		$lang->load('asb');
	}

	// settings tables, templates, groups and setting groups
	require_once MYBB_ROOT . 'inc/plugins/asb/functions_install.php';
	AdvancedSideboxInstaller::getInstance()->install();

	$addons = asb_get_all_modules();
	foreach ($addons as $addon) {
		$addon->install();
	}

	asb_create_script_info();

	@unlink(MYBB_ROOT . 'inc/plugins/adv_sidebox.php');
}

/**
 * handle version control (a la pavemen), upgrade if necessary and
 * change permissions for ASB
 *
 * @return void
 */
function asb_activate()
{
	global $asbOldVersion;

	$myCache = AdvancedSideboxCache::getInstance();

	require_once MYBB_ROOT . 'inc/plugins/asb/functions_install.php';

	// if we just upgraded . . .
	$asbOldVersion = $myCache->getVersion();
	if (isset($asbOldVersion) &&
		$asbOldVersion &&
		version_compare($asbOldVersion, ASB_VERSION, '<')) {
		require_once MYBB_ROOT . 'inc/plugins/asb/upgrade.php';
	}

	$myCache->setVersion(ASB_VERSION);

	// change the permissions to on by default
	change_admin_permission('config', 'asb');
}

/**
 * disable admin permissions
 *
 * @return void
 */
function asb_deactivate()
{
	// remove the permissions
	change_admin_permission('config', 'asb', -1);
}

/**
 * drop the table added to the DB and the column added to
 * the mybb_users table (show_sidebox),
 * delete the plugin settings, templates and style sheets
 *
 * @return void
 */
function asb_uninstall()
{
	if (!defined('IN_ASB_UNINSTALL')) {
		define('IN_ASB_UNINSTALL', true);
	}

	global $mybb;

	// remove the modules first
	$addons = asb_get_all_modules();

	// if there are add-on modules installed
	if (is_array($addons)) {
		// uninstall them
		foreach ($addons as $addon) {
			$addon->uninstall();
		}
	}

	require_once MYBB_ROOT . 'inc/plugins/asb/functions_install.php';
	AdvancedSideboxInstaller::getInstance()->uninstall();

	// delete our cached version
	AdvancedSideboxCache::getInstance()->clear();
}

/*
 * settings
 */

/**
 * retrieves the plugin's settings group gid if it exists
 * attempts to cache repeat calls
 *
 * @return int gid
 */
function asb_get_settingsgroup()
{
	static $asb_settings_gid;

	// if we have already stored the value
	if (isset($asb_settings_gid)) {
		// don't waste a query
		$gid = (int) $asb_settings_gid;
	} else {
		global $db;

		// otherwise we will have to query the db
		$query = $db->simple_select('settinggroups', 'gid', "name='asb_settings'");
		$gid = (int) $db->fetch_field($query, 'gid');
	}
	return $gid;
}

/**
 * builds the url to modify plugin settings if given valid info
 *
 * @param  int settings group id
 * @return string url
 */
function asb_build_settings_url($gid)
{
	if ($gid) {
		return 'index.php?module=config-settings&amp;action=change&amp;gid=' . $gid;
	}
}

/**
 * builds a link to modify plugin settings if it exists
 *
 * @return string html
 */
function asb_build_settings_link()
{
	global $lang;

	if (!$lang->asb) {
		$lang->load('asb');
	}

	$gid = asb_get_settingsgroup();

	// does the group exist?
	if ($gid) {
		// if so build the URL
		$url = asb_build_settings_url($gid);

		// did we get a URL?
		if ($url) {
			// if so build the link
			return "<a href=\"{$url}\" title=\"{$lang->asb_plugin_settings}\">{$lang->asb_plugin_settings}</a>";
		}
	}
	return false;
}

?>
