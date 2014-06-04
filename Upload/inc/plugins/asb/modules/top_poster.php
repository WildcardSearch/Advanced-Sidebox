<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * ASB default module
 */

// Include a check for Advanced Sidebox
if(!defined('IN_MYBB') || !defined('IN_ASB'))
{
	die('Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.');
}

/*
 * asb_top_poster_info()
 *
 * provide info to ASB about the addon
 *
 * @return: (array) the module info
 */
function asb_top_poster_info()
{
	global $lang;

	if(!$lang->asb_addon)
	{
		$lang->load('asb_addon');
	}

	return array	(
		"title" => $lang->asb_top_poster_title,
		"description" => $lang->asb_top_poster_desc,
		"wrap_content" => true,
		"version" => '1.1.4',
		"compatibility" => '2.1',
		"settings" => array(
			"time_frame" => array(
				"sid" => 'NULL',
				"name" => 'time_frame',
				"title" => $lang->asb_top_poster_time_frame_title,
				"description" => $lang->asb_top_poster_time_frame_desc,
				"optionscode" => "select
1={$lang->asb_top_poster_one_day_title}
7={$lang->asb_top_poster_one_week_title}
14={$lang->asb_top_poster_two_weeks_title}
30={$lang->asb_top_poster_one_month_title}
90={$lang->asb_top_poster_three_months_title}
180={$lang->asb_top_poster_six_months_title}
365={$lang->asb_top_poster_one_year_title}",
				"value" => '1'
			),
			"avatar_size" => array(
				"name" => 'avatar_size',
				"title" => $lang->asb_top_poster_avatar_size_title,
				"description" => $lang->asb_top_poster_avatar_size_desc,
				"optionscode" => 'text',
				"value" => ''
			),
		),
		"templates" => array(
			array(
				"title" => 'asb_top_poster',
				"template" => <<<EOF
				<tr style="text-align: center;">
					<td class="trow1">{\$top_poster_avatar}{\$top_poster_text}</td>
				</tr>
EOF
			),
			array(
				"title" => 'asb_top_poster_avatar',
				"template" => <<<EOF
<img src="{\$top_poster_avatar_src}" style="width: {\$avatar_width}px; margin-top: 10px;" alt="{\$lang->asb_top_poster_no_avatar}"/><br /><br />
EOF
			),
		),
	);
}

/*
 * asb_top_poster_build_template()
 *
 * handles display of children of this addon at page load
 *
 * @param - $args - (array) the specific information from the child box
 * @return: (bool) true on success, false on fail/no content
 */
function asb_top_poster_build_template($args)
{
	extract($args);
	global $$template_var, $db, $templates, $lang, $theme;

	if(!$lang->asb_addon)
	{
		$lang->load('asb_addon');
	}

	if(!$settings['time_frame'])
	{
		$settings['time_frame'] = 1;
	}
	$timesearch = TIME_NOW - (86400 * $settings['time_frame']);

	$group_by = 'p.uid';
	if($db->type == 'pgsql')
	{
		$group_by = $db->build_fields_string('users', 'u.');
	}

	$query = $db->query(<<<EOF
SELECT u.uid, u.username, u.usergroup, u.displaygroup, u.avatar, COUNT(*) AS poststoday
FROM {$db->table_prefix}posts p
LEFT JOIN {$db->table_prefix}users u ON (p.uid=u.uid)
WHERE p.dateline > {$timesearch}
GROUP BY {$group_by} ORDER BY poststoday DESC
LIMIT 1
EOF
);

	// some defaults
	$top_poster = $lang->asb_top_poster_no_one;
	$top_poster_posts = $lang->asb_top_poster_no_posts;
	$top_poster_text = $lang->asb_top_poster_no_top_poster;
	$top_poster_avatar = '';
	$ret_val = false;

	// adjust language for time frame
	switch ($settings['time_frame']) {
	case 7:
		$top_poster_timeframe = $lang->asb_top_poster_one_week;
		break;
	case 14:
		$top_poster_timeframe = $lang->asb_top_poster_two_weeks;
		break;
	case 30:
		$top_poster_timeframe = $lang->asb_top_poster_one_month;
		break;
	case 90:
		$top_poster_timeframe = $lang->asb_top_poster_three_months;
		break;
	case 180:
		$top_poster_timeframe = $lang->asb_top_poster_six_months;
		break;
	case 365:
		$top_poster_timeframe = $lang->asb_top_poster_one_year;
		break;
	default:
		$top_poster_timeframe = $lang->asb_top_poster_one_day;
	}

	$user = $db->fetch_array($query);

	// if we have a user . . .
	if($user['poststoday'])
	{
		// default to default :p
		$avatar_width = (int) $width * .83;
		if((int) $settings['avatar_size'])
		{
			$avatar_width = (int) $settings['avatar_size'];
		}

		// default to guest
		$top_poster = $lang->guest;
		if($user['uid'])
		{
			$username = format_name($user['username'], $user['usergroup'], $user['displaygroup']);
			$top_poster = build_profile_link($username, $user['uid']);
		}

		$top_poster_posts = $user['poststoday'];
		$post_lang = $lang->asb_top_poster_posts;
		if($top_poster_posts == 1)
		{
			$post_lang = $lang->asb_top_poster_post;
		}

		$top_poster_avatar_src = "{$theme['imgdir']}/default_avatar.gif";
		if($user['avatar'] != '')
		{
			$top_poster_avatar_src = $user['avatar'];
		}
		eval("\$top_poster_avatar = \"" . $templates->get('asb_top_poster_avatar') . "\";");

		$top_poster_text = $lang->sprintf($lang->asb_top_poster_congrats, $top_poster, $top_poster_timeframe, $top_poster_posts, $post_lang);
		$ret_val = true;
	}

	eval("\$\$template_var = \"" . $templates->get('asb_top_poster') . "\";");

	// return true if your box has something to show, or false if it doesn't.
	return $ret_val;
}

?>
