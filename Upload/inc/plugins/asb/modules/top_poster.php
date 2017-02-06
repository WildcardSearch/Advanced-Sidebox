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
function asb_top_poster_info()
{
	global $lang;

	if (!$lang->asb_addon) {
		$lang->load('asb_addon');
	}

	return array	(
		"title" => $lang->asb_top_poster_title,
		"description" => $lang->asb_top_poster_desc,
		"wrap_content" => true,
		"version" => '1.2',
		"compatibility" => '2.1',
		"settings" => array(
			"time_frame" => array(
				"sid" => 'NULL',
				"name" => 'time_frame',
				"title" => $lang->asb_top_poster_time_frame_title,
				"description" => $lang->asb_top_poster_time_frame_desc,
				"optionscode" => "select
0={$lang->asb_top_poster_all_time_title}
1={$lang->asb_top_poster_one_day_title}
7={$lang->asb_top_poster_one_week_title}
14={$lang->asb_top_poster_two_weeks_title}
30={$lang->asb_top_poster_one_month_title}
90={$lang->asb_top_poster_three_months_title}
180={$lang->asb_top_poster_six_months_title}
365={$lang->asb_top_poster_one_year_title}",
				"value" => '1',
			),
			"max_posters" => array(
				"name" => 'max_posters',
				"title" => $lang->asb_top_poster_max_posters_title,
				"description" => $lang->asb_top_poster_max_posters_desc,
				"optionscode" => 'text',
				"value" => '1',
			),
			"avatar_size" => array(
				"name" => 'avatar_size',
				"title" => $lang->asb_top_poster_avatar_size_title,
				"description" => $lang->asb_top_poster_avatar_size_desc,
				"optionscode" => 'text',
				"value" => '',
			),
			"group_show_list" => array(
				"name" => 'group_show_list',
				"title" => $lang->asb_group_show_list_title,
				"description" => $lang->asb_group_show_list_desc,
				"optionscode" => 'text',
				"value" => '',
			),
			"group_hide_list" => array(
				"name" => 'group_hide_list',
				"title" => $lang->asb_group_hide_list_title,
				"description" => $lang->asb_group_hide_list_desc,
				"optionscode" => 'text',
				"value" => '',
			),
		),
		"templates" => array(
			array(
				"title" => 'asb_top_posters_multiple',
				"template" => <<<EOF
				<tr>
					<td class="{\$altbg}">
						<table cellspacing="0" cellpadding="{\$theme[\'tablespace\']}" width="100%">
						<tr>
							<td colspan="2" class="tcat" style="width:25%; font-size: .8em; text-align: center;">
								{\$top_poster_description}
							</td>
						</tr>
						{\$top_posters}
						</table>
					</td>
				</tr>
EOF
			),
			array(
				"title" => 'asb_top_posters_single',
				"template" => <<<EOF
				<tr style="text-align: center;">
					<td class="{\$altbg}">{\$top_poster_avatar}{\$top_poster_text}</td>
				</tr>
EOF
			),
			array(
				"title" => 'asb_top_poster',
				"template" => <<<EOF
				<tr>
					<td class="{\$altbg}" style="width:25%">
						{\$top_poster_avatar}
					</td>
					<td class="{\$altbg}" style="font-size: 1em;">
						{\$topposter_name}<br/>
						{\$top_poster_text}
					</td>
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

/**
 * handles display of children of this addon at page load
 *
 * @param  array info from child box
 * @return bool true on success, false on fail/no content
 */
function asb_top_poster_build_template($args)
{
	extract($args);
	global $$template_var, $db, $templates, $lang, $theme;

	if (!$lang->asb_addon) {
		$lang->load('asb_addon');
	}

	$limit = (int) $settings['max_posters'];
	if ($limit == 0) {
		$limit = 1;
	}

	$time_frame = (int) $settings['time_frame'];
	$timesearch = TIME_NOW - (86400 * $time_frame);

	// adjust language for time frame
	switch ($time_frame) {
	case 0:
		$top_poster_timeframe = $lang->asb_top_poster_all_time;
		$top_poster_timeframe_prelude = $lang->asb_top_poster_all_time_desc;
		break;
	case 7:
		$top_poster_timeframe = $lang->asb_top_poster_one_week;
		$top_poster_timeframe_prelude = $lang->asb_top_poster_one_week_desc;
		break;
	case 14:
		$top_poster_timeframe = $lang->asb_top_poster_two_weeks;
		$top_poster_timeframe_prelude = $lang->asb_top_poster_two_weeks_desc;
		break;
	case 30:
		$top_poster_timeframe = $lang->asb_top_poster_one_month;
		$top_poster_timeframe_prelude = $lang->asb_top_poster_one_month_desc;
		break;
	case 90:
		$top_poster_timeframe = $lang->asb_top_poster_three_months;
		$top_poster_timeframe_prelude = $lang->asb_top_poster_three_months_desc;
		break;
	case 180:
		$top_poster_timeframe = $lang->asb_top_poster_six_months;
		$top_poster_timeframe_prelude = $lang->asb_top_poster_six_months_desc;
		break;
	case 365:
		$top_poster_timeframe = $lang->asb_top_poster_one_year;
		$top_poster_timeframe_prelude = $lang->asb_top_poster_one_year_desc;
		break;
	default:
		$top_poster_timeframe = $lang->asb_top_poster_one_day;
		$top_poster_timeframe_prelude = $lang->asb_top_poster_one_day_desc;
	}

	// build user group exclusions (if any)
	$show = asb_build_id_list($settings['group_show_list'], 'u.usergroup');
	$hide = asb_build_id_list($settings['group_hide_list'], 'u.usergroup');
	$where['show'] = asb_build_SQL_where($show, ' OR ');
	$where['hide'] = asb_build_SQL_where($hide, ' OR ', ' NOT ');
	$group_where = asb_build_SQL_where($where, ' AND ', ' AND ');

	$group_by = 'p.uid';
	if ($db->type == 'pgsql') {
		$group_by = $db->build_fields_string('users', 'u.');
	}

	if ($time_frame > 0) {
		$query = $db->query("
		SELECT u.uid, u.username, u.usergroup, u.displaygroup, u.avatar, COUNT(*) AS poststoday
		FROM {$db->table_prefix}posts p
		LEFT JOIN {$db->table_prefix}users u ON (p.uid=u.uid)
		WHERE p.dateline > {$timesearch}{$group_where}
		GROUP BY {$group_by} ORDER BY poststoday DESC
		LIMIT {$limit}
		");
	} else {
		$query = $db->simple_select('users', 'uid, avatar, username, postnum, usergroup, displaygroup', "postnum > 0{$group_where}", array('order_by' => 'postnum', 'order_dir' => 'DESC', 'limit' => $limit));
	}

	$altbg = alt_trow();
	if ($db->num_rows($query) == 0) {
		// some defaults
		$top_poster = $lang->asb_top_poster_no_one;
		$top_poster_posts = $lang->asb_top_poster_no_posts;
		$top_poster_text = $lang->asb_top_poster_no_top_poster;
		$top_poster_avatar = '';
		eval("\$\$template_var = \"" . $templates->get('asb_top_posters_single') . "\";");
		$ret_val = false;
	} else {
		while ($user = $db->fetch_array($query)) {
			$ret_val = true;

			// default to guest
			$top_poster = $lang->guest;
			if ($user['uid']) {
				$username = format_name($user['username'], $user['usergroup'], $user['displaygroup']);
				$profile_link = get_profile_link($user['uid']);
				$top_poster = build_profile_link($username, $user['uid']);
			}

			$top_poster_posts = $user['poststoday'];
			if ($time_frame == 0) {
				$top_poster_posts = $user['postnum'];
			}
			$post_lang = $lang->asb_top_poster_posts;
			if ($top_poster_posts == 1) {
				$post_lang = $lang->asb_top_poster_post;
			}

			$top_poster_avatar_src = "{$theme['imgdir']}/default_avatar.png";
			if ($user['avatar'] != '') {
				$top_poster_avatar_src = $user['avatar'];
			}

			$settings['avatar_size'] = trim($settings['avatar_size']);
			if (my_strpos($settings['avatar_size'], '%') == my_strlen($settings['avatar_size']) - 1) {
				$settings['avatar_size'] = (int) $width * (my_substr($settings['avatar_size'], 0, my_strlen($settings['avatar_size']) - 1) / 100);
			}
			$avatar_width = (int) $width * .83;

			$top_poster_description = $lang->sprintf($lang->asb_top_poster_description, $top_poster_timeframe_prelude);

			if ($db->num_rows($query) == 1) {
				$top_poster_text = $lang->sprintf($lang->asb_top_poster_congrats, $top_poster, $top_poster_timeframe, $top_poster_posts, $post_lang);

				if ((int) $settings['avatar_size']) {
					$avatar_width = (int) $settings['avatar_size'];
				}
				eval("\$top_poster_avatar = \"" . $templates->get('asb_top_poster_avatar') . "\";");

				eval("\$\$template_var = \"" . $templates->get('asb_top_posters_single') . "\";");
			} else {
				$top_poster_text = $top_poster . '<br />' . $top_poster_posts;

				$avatar_width = (int) $avatar_width / 3;
				if ((int) $settings['avatar_size']) {
					$avatar_width = (int) $settings['avatar_size'];
				}
				eval("\$top_poster_avatar = \"" . $templates->get('asb_top_poster_avatar') . "\";");

				eval("\$top_posters .= \"" . $templates->get('asb_top_poster') . "\";");
			}
			$altbg = alt_trow();
		}

		if ($db->num_rows($query) > 1) {
			eval("\$\$template_var .= \"" . $templates->get('asb_top_posters_multiple') . "\";");
		}
	}
	return $ret_val;
}

?>
