<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains extra functions for install.php
 */

/**
 * create the default script information rows (tailored to mimic the previous versions)
 *
 * @param  bool return associative array?
 * @return array|true see above dependency
 */
function asb_create_script_info($return = false)
{
	$scripts = array(
		"index" => array(
			"title" => 'Index',
			"filename" => 'index.php',
			"template_name" => 'index',
			"hook" => 'index_start',
			"find_top" => '{$header}',
			"find_bottom" => '{$footer}',
			"replace_all" => 0,
			"eval" => 0,
			"active" => 1,
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
			"replace_all" => 0,
			"eval" => 0,
			"active" => 1,
		),
		"showthread" => array(
			"title" => 'Show Thread',
			"filename" => 'showthread.php',
			"template_name" => 'showthread',
			"hook" => 'showthread_start',
			"find_top" => '{$ratethread}',
			"find_bottom" => '{$footer}',
			"replace_all" => 0,
			"eval" => 0,
			"active" => 1,
		),
		"member" => array(
			"title" => 'Member Profile',
			"filename" => 'member.php',
			"action" => 'profile',
			"template_name" => 'member_profile',
			"hook" => 'member_profile_start',
			"find_top" => '{$header}',
			"find_bottom" => '{$footer}',
			"replace_all" => 0,
			"eval" => 0,
			"active" => 1,
		),
		"memberlist" => array(
			"title" => 'Member List',
			"filename" => 'memberlist.php',
			"template_name" => 'memberlist',
			"hook" => 'memberlist_start',
			"find_top" => '{$multipage}',
			"find_bottom" => '{$footer}',
			"replace_all" => 0,
			"eval" => 0,
			"active" => 1,
		),
		"showteam" => array(
			"title" => 'Forum Team',
			"filename" => 'showteam.php',
			"template_name" => 'showteam',
			"hook" => 'showteam_start',
			"find_top" => '{$header}',
			"find_bottom" => '{$footer}',
			"replace_all" => 0,
			"eval" => 0,
			"active" => 1,
		),
		"stats" => array(
			"title" => 'Statistics',
			"filename" => 'stats.php',
			"template_name" => 'stats',
			"hook" => 'stats_start',
			"find_top" => '{$header}',
			"find_bottom" => '{$footer}',
			"replace_all" => 0,
			"eval" => 0,
			"active" => 1,
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
			"eval" => 0,
			"active" => 1,
		),
	);

	if ($return == false) {
		foreach ($scripts as $info) {
			$this_script = new ScriptInfo($info);
			$this_script->save();
		}
		return true;
	} else {
		foreach ($scripts as $key => $info) {
			$ret_scripts[$key] = new ScriptInfo($info);
		}
		return $ret_scripts; // upgrade script will save these script defs
	}
}

/**
 * rebuilds the theme exclude list ACP setting
 *
 * @return string|bool html or false
 */
function asb_build_theme_exclude_select()
{
	$all_themes = asb_get_all_themes(true);

	$theme_count = min(5, count($all_themes));
	if ($theme_count == 0) {
		return $theme_select = <<<EOF
php
<select name=\"upsetting[asb_exclude_theme][]\" size=\"1\">
	<option value=\"0\">no themes!</option>
</select>

EOF;
	}

	// Create an option for each theme and insert code to unserialize each option and 'remember' settings
	foreach ($all_themes as $tid => $name) {
		$name = addcslashes($name, '"');
		$theme_select .= <<<EOF
<option value=\"{$tid}\" " . (is_array(unserialize(\$setting['value'])) ? (\$setting['value'] != "" && in_array("{$tid}", unserialize(\$setting['value'])) ? "selected=\"selected\"":""):"") . ">{$name}</option>
EOF;
	}

	// put it all together
	return <<<EOF
php
<select multiple name=\"upsetting[asb_exclude_theme][]\" size=\"{$theme_count}\">
{$theme_select}
</select>

EOF;
}

?>
