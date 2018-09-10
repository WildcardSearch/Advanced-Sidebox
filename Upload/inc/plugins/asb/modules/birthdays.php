<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * ASB default module
 */

// disallow direct access
if (!defined('IN_MYBB') ||
	!defined('IN_ASB')) {
	die('Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.');
}

/**
 * provide info to ASB about the addon
 *
 * @return array
 */
function asb_birthdays_info()
{
	global $lang;

	if (!$lang->asb_addon) {
		$lang->load('asb_addon');
	}

	return array	(
		'title' => $lang->asb_birthdays_title,
		'description' => $lang->asb_birthdays_description,
		'wrap_content' => true,
		'version' => '1.0.0',
		'compatibility' => '2.1',
		'settings' => array(
			'timeframe' => array(
				'name' => 'timeframe',
				'title' => $lang->asb_birthdays_time_frame_title,
				'description' => $lang->asb_birthdays_time_frame_description,
				'optionscode' => <<<EOF
select
1={$lang->asb_birthdays_timeframe_optionscode_this_month}
2={$lang->asb_birthdays_timeframe_optionscode_today}
EOF
				,
				'value' => '2',
			),
		),
		'templates' => array(
			array(
				'title' => 'asb_birthdays',
				'template' => <<<EOF
				<tr>
					<td class="tcat">
						<span class="smalltext"><strong>{\$birthdaysTitle}</strong></span>
					</td>
				</tr>
				<tr>
					<td class="trow1">
						<span class="smalltext">{\$userList}</span>
					</td>
				</tr>
EOF
			),
			array(
				'title' => 'asb_birthdays_user_link',
				'template' => <<<EOF
<a href="{\$profileLink}" title="{\$userInfo}">{\$name}</a>
EOF
			),
		),
	);
}

/**
 * handles display of children of this addon at page load
 *
 * @param  array
 * @return bool success/fail
 */
function asb_birthdays_build_template($args)
{
	extract($args);
	global $$template_var, $lang;

	if (!$lang->asb_addon) {
		$lang->load('asb_addon');
	}

	$birthdays_status = asb_birthdays_get_birthdays($args);
	if (!$birthdays_status) {
		$$template_var = "<tr><td>{$lang->asb_birthdays_no_content}</td></tr>";
		return false;
	}

	$$template_var = $birthdays_status;
	return true;
}

/**
 * build the content based on settings
 *
 * @param  array
 * @return string
 */
function asb_birthdays_get_birthdays($args)
{
	global $mybb, $db, $lang, $templates, $cache;

	if (!$lang->asb_addon) {
		$lang->load('asb_addon');
	}

	extract($args);

	require_once MYBB_ROOT.'inc/functions_calendar.php';
	$day = my_date('j');
	$month = my_date('n');
	$year = my_date('Y');
	$today = ($settings['timeframe'] == 2);

	/**
	 * results by month return with a slighlty different
	 * structure so we have to modify the daily to match
	 */
	if (!$today) {
		$birthdaysTitle = $lang->asb_birthdays_this_months_birthdays;
		$birthdays = get_birthdays($month);
	} else {
		$birthdaysTitle = $lang->asb_birthdays_todays_birthdays;
		$birthdays = get_birthdays($month, $day);
		$birthdays = array("{$day}-{$month}" => $birthdays);
	}

	// build the user list
	$userList = $sep = '';
	foreach ($birthdays as $date => $users) {
		foreach ($users as $user) {
			if ($user['birthdayprivacy'] != 'all') {
				continue;
			}

			$name = format_name(htmlspecialchars_uni($user['username']), $user['usergroup'], $user['displaygroup']);
			$profileLink = get_profile_link($user['uid']);

			$birthday = my_date('F jS, Y', strtotime("{$year}-{$date}"));
			$userInfo = $lang->sprintf($lang->asb_birthdays_user_info, $user['age'], $birthday);

			eval("\$userList .= \$sep . \"{$templates->get('asb_birthdays_user_link')}\";");
			$sep = ', ';
		}
	}

	eval("\$returnValue = \"{$templates->get('asb_birthdays')}\";");
	return $returnValue;
}

?>
