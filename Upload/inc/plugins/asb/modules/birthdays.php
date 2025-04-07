<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * https://www.rantcentralforums.com
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
		'version' => '2.0.0',
		'compatibility' => '4.0',
		'installData' => array(
			'templates' => array(
				array(
					'title' => 'asb_birthdays',
					'template' => <<<EOF
				<div class="tcat asb-birthdays-today-header">
					<span class="smalltext"><strong>{\$lang->asb_birthdays_todays_birthdays}</strong></span>
				</div>{\$todaysBirthdays}
				<div class="tcat asb-birthdays-header asb-birthdays-upcoming-header">
					<span class="smalltext"><strong>{\$lang->asb_birthdays_upcoming_birthdays}</strong></span>
				</div>{\$upcomingBirthdays}
EOF
				),
				array(
					'title' => 'asb_birthdays_user_row',
					'template' => <<<EOF
				<div class="{\$altbg} asb-birthdays-header asb-birthdays-user-row">
					{\$avatar}<a href="{\$profileLink}" title="{\$userInfo}">{\$name}</a><span class="asb-birthdays-age-text smalltext"> &nbsp; ({\$user[\'age\']})</span>
				</div>

EOF
				),
				array(
					'title' => 'asb_birthdays_no_birthdays',
					'template' => <<<EOF
				<div class="{\$altbg} asb-birthdays-no-birthdays">
					<span>{\$noBirthdays}</span>
				</div>

EOF
				),
				array(
					'title' => 'asb_birthdays_user_avatar',
					'template' => <<<EOF
<div class="asb-birthdays-user-avatar" style="background-image: url({\$avatarInfo[\'image\']});" title="{\$user[\'username\']}\'s profile"></div>

EOF
				),
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
function asb_birthdays_get_content($settings, $script, $dateline)
{
	global $mybb, $db, $lang, $templates, $cache;

	if (!$lang->asb_addon) {
		$lang->load('asb_addon');
	}

	require_once MYBB_ROOT.'inc/functions_calendar.php';
	$day = my_date('j');
	$month = my_date('n');
	$year = my_date('Y');

	$todaysBirthdayUsers = get_birthdays($month, $day);
	$upcomingBirthdayUsers = get_birthdays($month);

	$userAvatars = $userAvatarList = array();
	foreach ($upcomingBirthdayUsers as $users) {
		foreach ($users as $user) {
			$userAvatarList[] = $user['uid'];
		}
	}

	$userAvatarList = implode(',', $userAvatarList);
	if (!empty($userAvatarList)) {
		$query = $db->simple_select('users', 'uid, avatar, avatardimensions', "uid IN({$userAvatarList})");

		while ($user = $db->fetch_array($query)) {
			$userAvatars[$user['uid']] = format_avatar($user['avatar'], $user['avatardimensions'], '20x20');
		}
	}

	$alreadyDone = array();
	$altbg = 'trow1';
	$todaysBirthdays = '';
	foreach ($todaysBirthdayUsers as $user) {
		if ($user['birthdayprivacy'] != 'all') {
			continue;
		}

		$name = format_name(htmlspecialchars_uni($user['username']), $user['usergroup'], $user['displaygroup']);
		$profileLink = get_profile_link($user['uid']);

		$birthday = my_date('F jS, Y', strtotime("{$year}-{$day}-{$month}"));
		$userInfo = $lang->sprintf($lang->asb_birthdays_user_info, $user['age'], $birthday);

		$avatarInfo = $userAvatars[$user['uid']];
		eval("\$avatar = \"{$templates->get('asb_birthdays_user_avatar')}\";");

		eval("\$todaysBirthdays .= \"{$templates->get('asb_birthdays_user_row')}\";");

		$altbg = alt_trow();
		$alreadyDone[$user['uid']] = true;
	}

	if (!$todaysBirthdays) {
		$noBirthdays = $lang->asb_birthdays_no_birthdays_today;
		eval("\$todaysBirthdays = \"{$templates->get('asb_birthdays_no_birthdays')}\";");
	}

	// build the user list
	$altbg = 'trow1';
	$upcomingBirthdays = '';
	foreach ($upcomingBirthdayUsers as $date => $users) {
		foreach ($users as $user) {
			$dateParts = explode('-', $date);
			$userDay = $dateParts[0];
			if ($user['birthdayprivacy'] != 'all' ||
				$alreadyDone[$user['uid']] ||
				$userDay <= $day) {
				continue;
			}

			$name = format_name(htmlspecialchars_uni($user['username']), $user['usergroup'], $user['displaygroup']);
			$profileLink = get_profile_link($user['uid']);

			$birthday = my_date('F jS, Y', strtotime("{$year}-{$date}"));
			$userInfo = $lang->sprintf($lang->asb_birthdays_user_info, $user['age'], $birthday);

			$avatarInfo = $userAvatars[$user['uid']];
			eval("\$avatar = \"{$templates->get('asb_birthdays_user_avatar')}\";");

			eval("\$upcomingBirthdays .= \"{$templates->get('asb_birthdays_user_row')}\";");

			$altbg = alt_trow();
		}
	}

	if (!$upcomingBirthdays) {
		$noBirthdays = $lang->asb_birthdays_no_upcoming_birthdays;
		eval("\$upcomingBirthdays = \"{$templates->get('asb_birthdays_no_birthdays')}\";");
	}

	eval("\$returnValue = \"{$templates->get('asb_birthdays')}\";");
	return $returnValue;
}