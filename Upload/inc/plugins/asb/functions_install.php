<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2013 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains extra functions for install.php
 */

/*
 * asb_create_script_info()
 *
 * create the default script information rows (tailored to mimic the previous versions)
 *
 * @param - $return - (boolean) true to return an associative array of ScriptInfo objects (no save) / false to simply save
 *
 * return: mixed (see above dependency)
 */
function asb_create_script_info($return = false)
{
	require_once MYBB_ROOT . 'inc/plugins/asb/classes/script_info.php';
	$scripts = array(
		"index" => array(
			"title" => 'Index',
			"filename" => 'index.php',
			"template_name" => 'index',
			"hook" => 'index_start',
			"find_top" => '{$header}',
			"find_bottom" => '{$footer}',
			"active" => 1
		),
		"forumdisplay" => array(
			"title" => 'Forum Display',
			"filename" => 'forumdisplay.php',
			"template_name" => 'forumdisplay_threadlist',
			"hook" => 'forumdisplay_start',
			"find_top" => '<div class="float_right">
	{$newthread}
</div>',
			"find_bottom" => '{$inline_edit_js}',
			"active" => 1
		),
		"showthread" => array(
			"title" => 'Show Thread',
			"filename" => 'showthread.php',
			"template_name" => 'showthread',
			"hook" => 'showthread_start',
			"find_top" => '{$ratethread}',
			"find_bottom" => '{$footer}',
			"active" => 1
		),
		"member" => array(
			"title" => 'Member Profile',
			"filename" => 'member.php',
			"action" => 'profile',
			"template_name" => 'member_profile',
			"hook" => 'member_profile_start',
			"find_top" => '{$header}',
			"find_bottom" => '{$footer}',
			"active" => 1
		),
		"memberlist" => array(
			"title" => 'Member List',
			"filename" => 'memberlist.php',
			"template_name" => 'memberlist',
			"hook" => 'memberlist_start',
			"find_top" => '{$multipage}',
			"find_bottom" => '{$footer}',
			"active" => 1
		),
		"showteam" => array(
			"title" => 'Forum Team',
			"filename" => 'showteam.php',
			"template_name" => 'showteam',
			"hook" => 'showteam_start',
			"find_top" => '{$header}',
			"find_bottom" => '{$footer}',
			"active" => 1
		),
		"stats" => array(
			"title" => 'Statistics',
			"filename" => 'stats.php',
			"template_name" => 'stats',
			"hook" => 'stats_start',
			"find_top" => '{$header}',
			"find_bottom" => '{$footer}',
			"active" => 1
		),
		"portal" => array(
			"title" => 'Portal',
			"filename" => 'portal.php',
			"template_name" => 'portal',
			"hook" => 'portal_start',
			"replace_all" => 1,
			"replacement" => <<<EOF
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
			"active" => 1
		)
	);

	if($return == false)
	{
		foreach($scripts as $info)
		{
			$this_script = new ScriptInfo($info);
			$this_script->save();
		}
		return true;
	}
	else
	{
		foreach($scripts as $key => $info)
		{
			$ret_scripts[$key] = new ScriptInfo($info);
		}
		return $ret_scripts; // upgrade script will save these script defs
	}
}

/*
 * asb_build_theme_exclude_select()
 *
 * rebuilds the theme exclude list ACP setting. used in cases where themes are added after the installation of Advanced Sidebox and the admin would like to exclude that theme.
 *
 return: either a select box list of themes or an indicator that no themes are installed
 */
function asb_build_theme_exclude_select()
{
	global $db;

	// get all the themes that are not MasterStyles
	$query = $db->simple_select("themes", "tid, name", "NOT pid='0'");

	// create a theme counter so our box is tidy
	$theme_count = 0;

	if($db->num_rows($query) > 0)
	{
		// Create an option for each theme and insert code to unserialize each option and 'remember' settings
		while($this_theme = $db->fetch_array($query))
		{
			$this_theme['name'] = addcslashes($this_theme['name'], '"');
			$theme_select .= <<<EOF
	<option value=\"{$this_theme['tid']}\" " . (is_array(unserialize(\$setting['value'])) ? (\$setting['value'] != "" && in_array("{$this_theme['tid']}", unserialize(\$setting['value'])) ? "selected=\"selected\"":""):"") . ">{$this_theme['name']}</option>
EOF;
			++$theme_count;
		}
		$theme_count = min(5, $theme_count);

		// put it all together
		$theme_select = <<<EOF
php
<select multiple name=\"upsetting[asb_exclude_theme][]\" size=\"{$theme_count}\">
{$theme_select}
</select>

EOF;
	}
	else
	{
		$theme_select = <<<EOF
php
<select name=\"upsetting[asb_exclude_theme][]\" size=\"1\">
	<option value=\"0\">no themes!</option>
</select>

EOF;
	}
	return $theme_select;
}

/*
 * versioning
 */

/*
 * asb_get_cache_version()
 *
 * check cached version info
 *
 * derived from the work of pavemen in MyBB Publisher
 */
function asb_get_cache_version()
{
	global $cache;

	// get currently installed version, if there is one
	$asb = $cache->read('asb');
	if($asb['version'])
	{
        return $asb['version'];
	}
    return 0;
}

/*
 * asb_set_cache_version()
 *
 * set cached version info
 *
 * derived from the work of pavemen in MyBB Publisher
 *
 */
function asb_set_cache_version()
{
	global $cache;

	// get version from this plugin file
	$asb_info = asb_info();

	// update version cache to latest
	$asb = $cache->read('asb');
	$asb['version'] = $asb_info['version'];
	$cache->update('asb', $asb);
    return true;
}

/*
 * asb_unset_cache_version()
 *
 * remove cached version info
 *
 * derived from the work of pavemen in MyBB Publisher
 */
function asb_unset_cache_version()
{
	global $cache;

	$asb = $cache->read('asb');
	$asb = null;
	$cache->update('asb', $asb);
    return true;
}

?>
