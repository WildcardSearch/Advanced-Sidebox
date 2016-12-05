<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * ASB default module
 */

// Include a check for Advanced Sidebox
if (!defined('IN_MYBB') ||
	!defined('IN_ASB')) {
	die('Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.');
}

/*
 * provide info to ASB about the addon
 *
 * @return array the module info
 */
function asb_statistics_info()
{
	global $lang;

	if (!$lang->asb_addon) {
		$lang->load('asb_addon');
	}

 	return array(
		"title" => $lang->asb_stats,
		"description" => $lang->asb_stats_desc,
		"wrap_content" => true,
		"version" => '1.2',
		"compatibility" => '2.1',
		"settings" => array(
			"format_username" => array(
				"sid" => 'NULL',
				"name" => 'format_username',
				"title" => $lang->asb_stats_format_usernames_title,
				"description" => $lang->asb_stats_format_usernames_desc,
				"optionscode" => 'yesno',
				"value" => '0'
			),
		),
		"templates" => array(
			array(
				"title" => 'asb_statistics',
				"template" => <<<EOF
				<tr>
					<td class="trow1">
						<span class="smalltext">
						<strong>&raquo; </strong>{\$lang->asb_stats_num_members}: {\$statistics[\'numusers\']}<br />
						<strong>&raquo; </strong>{\$lang->asb_stats_latest_member}: {\$newestmember}<br />
						<strong>&raquo; </strong>{\$lang->asb_stats_num_threads}: {\$statistics[\'numthreads\']}<br />
						<strong>&raquo; </strong>{\$lang->asb_stats_num_posts}: {\$statistics[\'numposts\']}
						<br /><br /><a href="{\$mybb->settings[\'bburl\']}/stats.php">{\$lang->asb_stats_full_stats}</a>
						</span>
					</td>
				</tr>
EOF
			)
		)
	);
}

/*
 * handles display of children of this addon at page load
 *
 * @param array the specific information from the child box
 * @return bool true on success, false on fail/no content
 */
function asb_statistics_build_template($args)
{
	extract($args);
	global $$template_var, $mybb, $cache, $templates, $lang;

	// Load global and custom language phrases
	if (!$lang->asb_addon) {
		$lang->load('asb_addon');
	}

	// get forum statistics
	$statistics = $cache->read("stats");
	$statistics['numthreads'] = my_number_format($statistics['numthreads']);
	$statistics['numposts'] = my_number_format($statistics['numposts']);
	$statistics['numusers'] = my_number_format($statistics['numusers']);

	$newestmember = "<strong>{$lang->asb_stats_no_one}</strong>";
	if ($statistics['lastusername']) {
		if ($settings['format_username']) {
			$last_user = get_user($statistics['lastuid']);
			$last_username = format_name($last_user['username'], $last_user['usergroup'], $last_user['displaygroup']);
		} else {
			$last_username = $statistics['lastusername'];
		}
		$newestmember = build_profile_link($last_username, $statistics['lastuid']);
	}

	eval("\$" . $template_var . " = \"" . $templates->get('asb_statistics') . "\";");
	return true;
}

?>
