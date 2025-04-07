<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * https://www.rantcentralforums.com
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

	$asbDescription = $lang->asb_description1;
	$extraLinks = '<br />';
	$settingsLink = asbBuildSettingsLink();
	if ($settingsLink) {
		// only show Manage Sideboxes link if active
		$pluginList = $cache->read('plugins');
		$manageLink = '';
		if (!empty($pluginList['active']) &&
			is_array($pluginList['active']) &&
			in_array('asb', $pluginList['active'])) {
			$url = ASB_URL;
			$manageLink = <<<EOF
	<li style="list-style-image: url(styles/{$cp_style}/images/asb/manage.png)">
		<a href="{$url}" title="{$lang->asb_manage_sideboxes}">{$lang->asb_manage_sideboxes}</a>
	</li>
EOF;
		}

		$settingsLink = <<<EOF
	<li style="list-style-image: url(styles/{$cp_style}/images/asb/settings.png)">
		{$settingsLink}
	</li>
EOF;
		$extraLinks = <<<EOF
<ul>
	{$settingsLink}
	{$manageLink}
	<li style="list-style-image: url(styles/{$cp_style}/images/asb/help.png)">
		<a href="https://github.com/WildcardSearch/Advanced-Sidebox/wiki/Help-Installation" title="{$lang->asb_help}">{$lang->asb_help}</a>
	</li>
</ul>
EOF;

		$asbDescription = <<<EOF
<table width="100%">
	<tbody>
		<tr>
			<td>
				{$lang->asb_description1}<br/><br/>{$lang->asb_description2}{$extraLinks}
			</td>
			<td style="text-align: center;">
				<img src="styles/{$cp_style}/images/asb/logo.png" alt="{$lang->asb_logo}" title="{$lang->asb_logo}"/><br /><br />
				<a href="https://paypal.me/wildcardsearch"><img src="styles/{$cp_style}/images/asb/donate.png" style="outline: none; border: none;" /></a>
			</td>
		</tr>
	</tbody>
</table>
EOF;
	}

	$name = <<<EOF
<span style="font-familiy: arial; font-size: 1.5em; color: #2B387C; text-shadow: 2px 2px 2px #00006A;">{$lang->asb}</span>
EOF;

	$author = <<<EOF
</a></small></i><a href="https://www.rantcentralforums.com" title="Rant Central"><span style="font-family: Courier New; font-weight: bold; font-size: 1.2em; color: #0e7109;">Wildcard</span></a><i><small><a>
EOF;

	// This array returns information about the plugin, some of which was prefabricated above based on whether the plugin has been installed or not.
	return array(
		'name' => $name,
		'description' => $asbDescription,
		'website' => 'https://github.com/WildcardSearch/Advanced-Sidebox',
		'author' => $author,
		'authorsite' => 'https://www.rantcentralforums.com',
		'version' => ASB_VERSION,
		'compatibility' => '18*',
		'codename' => 'asb',
	);
}

/**
 * check to see if the plugin's settings group is installed-- assume the plugin is installed if so
 *
 * @return bool true if installed, false if not
 */
function asb_is_installed()
{
	return asbGetSettingsGroup();
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

	AdvancedSideboxInstaller::getInstance()->install();

	$addons = asbGetAllModules();
	foreach ($addons as $addon) {
		$addon->install();
	}

	asbCreateScriptInfo();
}

/**
 * handle version control, upgrade if necessary, and
 * change permissions for ASB
 *
 * @return void
 */
function asb_activate()
{
	global $asbOldVersion;

	$myCache = AdvancedSideboxCache::getInstance();

	// if we just upgraded...
	$asbOldVersion = $myCache->getVersion();
	if (isset($asbOldVersion) &&
		$asbOldVersion &&
		version_compare($asbOldVersion, ASB_VERSION, '<')) {
		require_once MYBB_ROOT.'inc/plugins/asb/upgrade.php';
	}

	$myCache->setVersion(ASB_VERSION);

	// change the permissions to on by default
	change_admin_permission('config', 'asb');

	require_once MYBB_ROOT . '/inc/adminfunctions_templates.php';
	find_replace_templatesets('usercp_options', "#" . preg_quote('{$board_style}') . "#i", '{$asbShowSideboxes}{$board_style}');
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

	require_once MYBB_ROOT . '/inc/adminfunctions_templates.php';
	find_replace_templatesets('usercp_options', "#" . preg_quote('{$asbShowSideboxes}') . "#i", '');
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
	$addons = asbGetAllModules();

	// if there are add-on modules installed
	if (is_array($addons)) {
		// uninstall them
		foreach ($addons as $addon) {
			$addon->uninstall();
		}
	}

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
function asbGetSettingsGroup()
{
	static $gid;

	// if we have already stored the value
	if (!isset($gid)) {
		global $db;

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
function asbBuildSettingsUrl($gid)
{
	if ($gid) {
		return 'index.php?module=config-settings&amp;action=change&amp;gid='.$gid;
	}
}

/**
 * builds a link to modify plugin settings if it exists
 *
 * @return string html
 */
function asbBuildSettingsLink()
{
	global $lang;

	if (!$lang->asb) {
		$lang->load('asb');
	}

	$gid = asbGetSettingsGroup();

	// does the group exist?
	if ($gid) {
		// if so build the URL
		$url = asbBuildSettingsUrl($gid);

		// did we get a URL?
		if ($url) {
			// if so build the link
			return "<a href=\"{$url}\" title=\"{$lang->asb_plugin_settings}\">{$lang->asb_plugin_settings}</a>";
		}
	}

	return false;
}

/**
 * create the default script information rows (tailored to mimic the previous versions)
 *
 * @param  bool return associative array?
 * @return array|true see above dependency
 */
function asbCreateScriptInfo($return=false)
{
	$scripts = array(
		'index' => array(
			'title' => 'Index',
			'filename' => 'index.php',
			'template_name' => 'index',
			'hook' => 'index_start',
			'find_top' => '{$header}',
			'find_bottom' => '{$footer}',
			'replace_all' => 0,
			'eval' => 0,
			'active' => 1,
		),
		'forumdisplay' => array(
			'title' => 'Forum Display',
			'filename' => 'forumdisplay.php',
			'template_name' => 'forumdisplay_threadlist',
			'hook' => 'forumdisplay_start',
			'find_top' => '<div class="float_right">
	{$newthread}
</div>',
			'find_bottom' => '{$inline_edit_js}',
			'replace_all' => 0,
			'eval' => 0,
			'active' => 1,
		),
		'showthread' => array(
			'title' => 'Show Thread',
			'filename' => 'showthread.php',
			'template_name' => 'showthread',
			'hook' => 'showthread_start',
			'find_top' => '{$ratethread}',
			'find_bottom' => '{$footer}',
			'replace_all' => 0,
			'eval' => 0,
			'active' => 1,
		),
		'member' => array(
			'title' => 'Member Profile',
			'filename' => 'member.php',
			'action' => 'profile',
			'template_name' => 'member_profile',
			'hook' => 'member_profile_start',
			'find_top' => '{$header}',
			'find_bottom' => '{$footer}',
			'replace_all' => 0,
			'eval' => 0,
			'active' => 1,
		),
		'memberlist' => array(
			'title' => 'Member List',
			'filename' => 'memberlist.php',
			'template_name' => 'memberlist',
			'hook' => 'memberlist_start',
			'find_top' => '{$multipage}',
			'find_bottom' => '{$footer}',
			'replace_all' => 0,
			'eval' => 0,
			'active' => 1,
		),
		'showteam' => array(
			'title' => 'Forum Team',
			'filename' => 'showteam.php',
			'template_name' => 'showteam',
			'hook' => 'showteam_start',
			'find_top' => '{$header}',
			'find_bottom' => '{$footer}',
			'replace_all' => 0,
			'eval' => 0,
			'active' => 1,
		),
		'stats' => array(
			'title' => 'Statistics',
			'filename' => 'stats.php',
			'template_name' => 'stats',
			'hook' => 'stats_start',
			'find_top' => '{$header}',
			'find_bottom' => '{$footer}',
			'replace_all' => 0,
			'eval' => 0,
			'active' => 1,
		),
		'portal' => array(
			'title' => 'Portal',
			'filename' => 'portal.php',
			'template_name' => 'portal',
			'hook' => 'portal_start',
			'replace_all' => 1,
			'replacement' => <<<EOF
<html>
<head>
<title>{\$mybb->settings['bbname']}</title>
{\$headerinclude}
</head>
<body>
{\$header}
{\$asb_left}
{\$announcements}
{\$asb_right}
{\$footer}
</body>
</html>
EOF
			,
			'eval' => 0,
			'active' => 1,
		),
	);

	if ($return == false) {
		foreach ($scripts as $info) {
			$script = new ScriptInfo($info);
			$script->save();
		}

		return true;
	} else {
		foreach ($scripts as $key => $info) {
			$returnArray[$key] = new ScriptInfo($info);
		}

		return $returnArray; // upgrade script will save these script defs
	}
}

/**
 * rebuilds the theme exclude list ACP setting
 *
 * @return string|bool html or false
 */
function asbBuildThemeExcludeSelect()
{
	global $lang;
	if (!$lang->asb) {
		$lang->load('asb');
	}

	$allThemes = asbGetAllThemes(true);

	$themeCount = min(5, count($allThemes));
	if ($themeCount == 0) {
		return <<<EOF
php
<select name=\"upsetting[asb_exclude_theme][]\" size=\"1\">
	<option value=\"0\">{$lang->asb_theme_exclude_no_themes}</option>
</select>

EOF;
	}

	// Create an option for each theme and insert code to unserialize each option and 'remember' settings
	foreach ($allThemes as $tid => $name) {
		$name = addcslashes($name, '"');
		$themeSelect .= <<<EOF
<option value=\"{$tid}\" ".(is_array(unserialize(\$setting['value'])) ? (\$setting['value'] != "" && in_array("{$tid}", unserialize(\$setting['value'])) ? "selected=\"selected\"":""):"").">{$name}</option>
EOF;
	}

	// put it all together
	return <<<EOF
php
<select multiple name=\"upsetting[asb_exclude_theme][]\" size=\"{$themeCount}\">
{$themeSelect}
</select>

EOF;
}