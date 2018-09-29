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
		'templates' => array(
			array(
				'title' => 'asb_birthdays',
				'template' => <<<EOF
				<tr>
					<td class="tcat">
						<span class="smalltext"><strong>{\$lang->asb_birthdays_todays_birthdays}</strong></span>
					</td>
				</tr>{\$todaysBirthdays}
				<tr>
					<td class="tcat">
						<span class="smalltext"><strong>{\$lang->asb_birthdays_upcoming_birthdays}</strong></span>
					</td>
				</tr>{\$upcomingBirthdays}
EOF
			),
			array(
				'title' => 'asb_birthdays_user_row',
				'template' => <<<EOF
				<tr>
					<td class="{\$altbg}">
						{\$avatar} <span class="smalltext float_right">({\$user[\'age\']})</span><a href="{\$profileLink}" title="{\$userInfo}">{\$name}</a>
					</td>
				</tr>

EOF
			),
			array(
				'title' => 'asb_birthdays_no_birthdays',
				'template' => <<<EOF
				<tr>
					<td class="{\$altbg}">
						<span>{\$noBirthdays}</span>
					</td>
				</tr>

EOF
			),
			array(
				'title' => 'asb_birthdays_user_avatar',
				'template' => <<<EOF
<img src="{\$avatarInfo[\'image\']}" alt="avatar" title="{\$user[\'username\']}\'s profile"{\$avatarInfo[\'width_height\']} style="margin-bottom: -5px;" />

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

?>
