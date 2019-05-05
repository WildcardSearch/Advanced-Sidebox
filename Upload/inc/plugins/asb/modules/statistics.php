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

/**
 * provide info to ASB about the addon
 *
 * @return array module info
 */
function asb_statistics_info()
{
	global $lang;

	if (!$lang->asb_addon) {
		$lang->load('asb_addon');
	}

 	return array(
		'title' => $lang->asb_stats,
		'description' => $lang->asb_stats_desc,
		'wrap_content' => true,
		'version' => '2.0.0',
		'compatibility' => '4.0',
		'settings' => array(
			'format_username' => array(
				'name' => 'format_username',
				'title' => $lang->asb_stats_format_usernames_title,
				'description' => $lang->asb_stats_format_usernames_desc,
				'optionscode' => 'yesno',
				'value' => '0',
			),
		),
		'installData' => array(
			'templates' => array(
				array(
					'title' => 'asb_statistics',
					'template' => <<<EOF
				<div class="trow1 asb-statistics-container">
					<div class="asb-statistics-main">
						<ul class="asb-statistics-list">
							<li>{\$lang->asb_stats_num_members}: {\$statistics[\'numusers\']}</li>
							<li>{\$lang->asb_stats_latest_member}: {\$newestmember}</li>
							<li>{\$lang->asb_stats_num_threads}: {\$statistics[\'numthreads\']}</li>
							<li>{\$lang->asb_stats_num_posts}: {\$statistics[\'numposts\']}</li>
						</ul>
					</div>
					<div class="asb-statistics-full-link tfoot">
						<a href="{\$mybb->settings[\'bburl\']}/stats.php">{\$lang->asb_stats_full_stats}</a>
					</div>
				</div>
EOF
				),
			),
		),
	);
}

/**
 * handles display of children of this addon at page load
 *
 * @param  array info from child box
 * @return bool success/fail
 */
function asb_statistics_get_content($settings, $script)
{
	global $mybb, $cache, $templates, $lang;

	// Load global and custom language phrases
	if (!$lang->asb_addon) {
		$lang->load('asb_addon');
	}

	// get forum statistics
	$statistics = $cache->read('stats');
	$statistics['numthreads'] = my_number_format($statistics['numthreads']);
	$statistics['numposts'] = my_number_format($statistics['numposts']);
	$statistics['numusers'] = my_number_format($statistics['numusers']);

	$newestmember = "<strong>{$lang->asb_stats_no_one}</strong>";
	if ($statistics['lastusername']) {
		if ($settings['format_username']) {
			$last_user = get_user($statistics['lastuid']);
			$last_username = format_name(htmlspecialchars_uni($last_user['username']), $last_user['usergroup'], $last_user['displaygroup']);
		} else {
			$last_username = htmlspecialchars_uni($statistics['lastusername']);
		}
		$newestmember = build_profile_link($last_username, $statistics['lastuid']);
	}

	eval("\$content = \"{$templates->get('asb_statistics')}\";");

	return $content;
}

?>
