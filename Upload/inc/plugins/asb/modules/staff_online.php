<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * https://www.rantcentralforums.com
 *
 * ASB default module
 */

// this file may not be executed from outside of script
if (!defined('IN_MYBB') ||
	!defined('IN_ASB')) {
	die('You need MyBB and Advanced Sidebox installed and properly initialized to use this script.');
}

/**
 * provide info to ASB about the addon
 *
 * @return array module info
 */
function asb_staff_online_info()
{
	global $lang;

	if (!$lang->asb_addon) {
		$lang->load('asb_addon');
	}

 	return array(
		'title' => $lang->asb_staff_online,
		'description' => $lang->asb_staff_online_desc,
		'version' => '2.0.0',
		'compatibility' => '4.0',
		'noContentTemplate' => 'asb_staff_online_no_content',
		'wrap_content' => true,
		'xmlhttp' => true,
		'settings' => array(
			'max_staff' => array(
				'name' => 'max_staff',
				'title' => $lang->asb_staff_online_max_staff_title,
				'description' => $lang->asb_staff_online_max_staff_desc,
				'optionscode' => 'text',
				'value' => '5',
			),
			'group_show_list' => array(
				'name' => 'group_show_list',
				'title' => $lang->asb_group_show_list_title,
				'description' => $lang->asb_group_show_list_desc,
				'optionscode' => 'text',
				'value' => '',
			),
			'group_hide_list' => array(
				'name' => 'group_hide_list',
				'title' => $lang->asb_group_hide_list_title,
				'description' => $lang->asb_group_hide_list_desc,
				'optionscode' => 'text',
				'value' => '',
			),
		),
		'installData' => array(
			'templates' => array(
				array(
					'title' => 'asb_staff_online_bit',
					'template' => <<<EOF
				<div class="asb-staff-online-row trow1">
					<a class="asb-staff-online-avatar" href="{\$staff_profile_link}" style="background-image: url({\$staff_avatar_filename});" title="{\$staff_avatar_title}"></a>
					<div class="asb-staff-online-user-container">
						<div class="asb-staff-online-username">
							<a href="{\$staff_profile_link}" title="{\$staff_link_title}">{\$staff_username}</a>
						</div>
						<div class="asb-staff-online-badge">
							{\$staff_badge}
						</div>
					</div>
				</div>
EOF
				),
				array(
					'title' => 'asb_staff_online_no_content',
					'template' => <<<EOF
<div class="asb-no-content-message">{\$lang->asb_staff_online_no_staff_online}</div>

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
function asb_staff_online_get_content($settings, $script, $dateline)
{
	global $db, $mybb, $templates, $lang, $cache, $theme;

	if (!$lang->asb_addon) {
		$lang->load('asb_addon');
	}

	// get our setting value
	$max_rows = (int) $settings['max_staff'];

	// if max_rows is set to 0 then show nothing
	if (!$max_rows) {
		return false;
	}

	// store our users and groups here
	$usergroups = array();
	$users = array();

	// build user group exclusions (if any)
	$show = asbBuildIdList($settings['group_show_list'], 'gid');
	$hide = asbBuildIdList($settings['group_hide_list'], 'gid');
	$where['show'] = asbBuildSqlWhere($show, ' OR ');
	$where['hide'] = asbBuildSqlWhere($hide, ' OR ', ' NOT ');
	$group_where = asbBuildSqlWhere($where, ' AND ', ' AND ');

	// get all the groups admin has specified should be shown on showteam.php
	$query = $db->simple_select('usergroups', 'gid, title, usertitle, image', "showforumteam=1{$group_where}", array('order_by' => 'disporder'));
	while ($usergroup = $db->fetch_array($query)) {
		// store them in our array
		$usergroups[$usergroup['gid']] = $usergroup;
	}

	// get all the users of those specific groups
	$groups_in = implode(',', array_keys($usergroups));

	// if there were no groups...
	if (!$groups_in) {
		// there is nothing to show
		return false;
	}

	// set the time based on ACP settings
	$timesearch = TIME_NOW - $mybb->settings['wolcutoff'];

	// get all the users that are in staff groups that have been online within the allowed cutoff time
	$query = $db->query("
		SELECT
			s.sid, s.ip, s.uid, s.time, s.location,
			u.username, u.invisible, u.usergroup, u.displaygroup, u.avatar
		FROM {$db->table_prefix}sessions s
		LEFT JOIN {$db->table_prefix}users u ON (s.uid=u.uid)
		WHERE
			(displaygroup IN ({$groups_in}) OR (displaygroup='0' AND usergroup IN ({$groups_in}))) AND s.time > '{$timesearch}'
		ORDER BY
			u.username ASC, s.time DESC
	");

	// loop through our users
	while ($user = $db->fetch_array($query)) {
		// if displaygroup is not 0, display primary group
		if ($user['displaygroup'] != 0) {
			// then use this group
			$group = $user['displaygroup'];
		} else {
			// otherwise use the primary group
			$group = $user['usergroup'];
		}

		// if this user group is in a staff group then add the info to the list
		if ($usergroups[$group]) {
			$usergroups[$group]['user_list'][$user['uid']] = $user;
		}
	}

	// make sure we start from nothing
	$grouplist = '';
	$counter = 1;

	// loop through each user group
	foreach ($usergroups as $usergroup) {
		// if there are no users or we have reached our limit...
		if (!isset($usergroup['user_list']) || $counter > $max_rows) {
			// skip an iteration
			continue;
		}

		// we use this for the alternating table row bgcolor
		$bgcolor = '';

		// loop through all users
		foreach ($usergroup['user_list'] as $user) {
			// if we are over our limit
			if ($counter > $max_rows) {
				// don't add any more
				continue;
			}

			// prepare the info
			// alt and title for image are the same
			$staff_avatar_alt = $staff_avatar_title = "{$user['username']}'s profile";

			// if the user has an avatar then display it, otherwise force the default avatar.
			$avatar_info = format_avatar($user['avatar']);
			$staff_avatar_filename = $avatar_info['image'];

			// avatar properties
			$staff_avatar_dimensions = '100%';

			// user name link properties
			$staff_link_title = $user['username'];
			$staff_username = format_name($user['username'], $user['usergroup'], $user['displaygroup']);

			// link (for avatar and user name)
			$staff_profile_link = get_profile_link($user['uid']);

			// badge alt and title are the same
			$staff_badge_alt = $staff_badge_title = $usergroup['usertitle'];

			// if the user's group has a badge image...
			$staff_badge = $staff_badge_alt;
			if ($usergroup['image']) {
				// store it (if nothing is store alt property will display group default usertitle)
				$staff_badge_filename = $usergroup['image'];

				$staff_badge = <<<EOF
<img src="{$staff_badge_filename}" alt="{$staff_badge_alt}" title="{$staff_badge_title}" />
EOF;
			}

			// give us an alternating bgcolor
			$bgcolor = alt_trow();

			// incremenet the counter
			++$counter;

			// add this row to the table
			eval("\$online_staff .= \"{$templates->get("asb_staff_online_bit")}\";");
		}
	}

	// if there were staff members online...
	if ($online_staff) {
		// show them
		return $online_staff;
	}

	return false;
}

?>
