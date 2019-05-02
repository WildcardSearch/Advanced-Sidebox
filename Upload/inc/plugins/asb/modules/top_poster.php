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
		'title' => $lang->asb_top_poster_title,
		'description' => $lang->asb_top_poster_desc,
		'wrap_content' => true,
		'version' => '2.0.3',
		'compatibility' => '4.0',
		'settings' => array(
			'time_frame' => array(
				'name' => 'time_frame',
				'title' => $lang->asb_top_poster_time_frame_title,
				'description' => $lang->asb_top_poster_time_frame_desc,
				'optionscode' => <<<EOF
select
0={$lang->asb_top_poster_all_time_title}
1={$lang->asb_top_poster_one_day_title}
7={$lang->asb_top_poster_one_week_title}
14={$lang->asb_top_poster_two_weeks_title}
30={$lang->asb_top_poster_one_month_title}
90={$lang->asb_top_poster_three_months_title}
180={$lang->asb_top_poster_six_months_title}
365={$lang->asb_top_poster_one_year_title}
EOF
				,
				'value' => '1',
			),
			'threads_only' => array(
				'name' => 'threads_only',
				'title' => $lang->asb_top_poster_threads_only_title,
				'description' => $lang->asb_top_poster_threads_only_desc,
				'optionscode' => 'yesno',
				'value' => '0',
			),
			'fid' => array(
				'name' => 'fid',
				'title' => $lang->asb_top_poster_fid_title,
				'description' => $lang->asb_top_poster_fid_desc,
				'optionscode' => 'text',
				'value' => '',
			),
			'tid' => array(
				'name' => 'tid',
				'title' => $lang->asb_top_poster_tid_title,
				'description' => $lang->asb_top_poster_tid_desc,
				'optionscode' => 'text',
				'value' => '',
			),
			'max_posters' => array(
				'name' => 'max_posters',
				'title' => $lang->asb_top_poster_max_posters_title,
				'description' => $lang->asb_top_poster_max_posters_desc,
				'optionscode' => 'text',
				'value' => '1',
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
					'title' => 'asb_top_posters_multiple',
					'template' => <<<EOF
				<div class="tcat asb-top-poster-description">
					{\$top_poster_description}
				</div>
				<div class="{\$altbg} asb-top-poster-posters">{\$top_posters}
				</div>
EOF
				),
				array(
					'title' => 'asb_top_posters_single',
					'template' => <<<EOF
				<div class="{\$altbg} asb-top-poster-posters-single">{\$top_poster_avatar}{\$top_poster_text}</div>
EOF
				),
				array(
					'title' => 'asb_top_poster',
					'template' => <<<EOF
				<div class="asb-top-poster-poster-avatar">
					{\$top_poster_avatar}
				</div>
				<div class="asb-top-poster-poster-text">
					{\$top_poster_text}
				</div>
EOF
				),
				array(
					'title' => 'asb_top_poster_avatar',
					'template' => <<<EOF
<img class="asb-top-poster-avatar" src="{\$top_poster_avatar_src}" alt="{\$lang->asb_top_poster_no_avatar}" />
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
 * @return bool true on success, false on fail/no content
 */
function asb_top_poster_build_template($settings, $template_var, $script)
{
	global $$template_var, $db, $templates, $lang, $theme, $parser;

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
		$timesearch = 0;
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
	$show = asbBuildIdList($settings['group_show_list'], 'u.usergroup');
	$hide = asbBuildIdList($settings['group_hide_list'], 'u.usergroup');
	$where['show'] = asbBuildSqlWhere($show, ' OR ');
	$where['hide'] = asbBuildSqlWhere($hide, ' OR ', ' NOT ');
	$group_where = asbBuildSqlWhere($where, ' AND ', ' AND ');

	$forum_where = $thread_where = $extraCongrats = '';
	$tid = (int) $settings['tid'];
	$fid = (int) $settings['fid'];
	$threadsOnly = (bool) $settings['threads_only'];

	if ($tid &&
		!$threadsOnly) {
		$thread_where = " AND p.tid='{$tid}'";
		$threadQuery = $db->simple_select('threads', 'subject', "tid='{$tid}'");
		if ($db->num_rows($threadQuery) > 0) {
			require_once MYBB_ROOT.'inc/class_parser.php';
			$parser = new postParser;

			$threadTitle = $db->fetch_field($threadQuery, 'subject');
			$threadTitle = htmlspecialchars_uni($parser->parse_badwords($threadTitle));
			$threadUrl = get_thread_link($tid);
			$threadLink = <<<EOF
<a href="{$threadUrl}">{$threadTitle}</a>
EOF;
			$extraCongrats = $lang->sprintf($lang->asb_top_poster_specific_thread_congrats, $threadLink);
		}
	} elseif ($fid) {
		$forum_where = " AND p.fid='{$fid}'";
		if ($threadsOnly) {
			$forum_where = " AND t.fid='{$fid}'";
		}

		$forumQuery = $db->simple_select('forums', 'name', "fid='{$fid}'");
		if ($db->num_rows($forumQuery) > 0) {
			$forumTitle = $db->fetch_field($forumQuery, 'name');
			$forumUrl = get_forum_link($fid);
			$forumLink = <<<EOF
<a href="{$forumUrl}">{$forumTitle}</a>
EOF;
			$extraCongrats = $lang->sprintf($lang->asb_top_poster_specific_forum_congrats, $forumLink);
		}
	}

	$group_by = 'p.uid';
	if ($db->type == 'pgsql') {
		$group_by = $db->build_fields_string('users', 'u.');
	}

	// all-time top poster (or thread starter) with no specified thread
	// can use the simple query
	if ($time_frame <= 0 &&
		!$tid &&
		!$fid) {
		$fieldName = 'postnum';
		if ($threadsOnly) {
			$fieldName = 'threadnum';
		}

		$query = $db->simple_select('users', "uid, avatar, username, {$fieldName} as totalposts, usergroup, displaygroup", "{$fieldName} > 0{$group_where}", array('order_by' => $fieldName, 'order_dir' => 'DESC', 'limit' => $limit));
	} elseif ($threadsOnly) {
		$group_by = 't.uid';

		$query = $db->query("
		SELECT u.uid, u.username, u.usergroup, u.displaygroup, u.avatar, COUNT(t.tid) AS totalposts
		FROM {$db->table_prefix}threads t
		LEFT JOIN {$db->table_prefix}users u ON (t.uid=u.uid)
		WHERE t.dateline > {$timesearch}{$group_where}{$forum_where}
		GROUP BY {$group_by}
		ORDER BY totalposts DESC
		LIMIT {$limit}
		");
	} else {
		$query = $db->query("
		SELECT u.uid, u.username, u.usergroup, u.displaygroup, u.avatar, COUNT(p.pid) AS totalposts
		FROM {$db->table_prefix}posts p
		LEFT JOIN {$db->table_prefix}users u ON (p.uid=u.uid)
		WHERE p.dateline > {$timesearch}{$group_where}{$thread_where}{$forum_where}
		GROUP BY {$group_by}
		ORDER BY totalposts DESC
		LIMIT {$limit}
		");
	}

	// error
	$altbg = alt_trow();
	if ($db->num_rows($query) == 0) {
		// some defaults
		$top_poster = $lang->asb_top_poster_no_one;
		$top_poster_posts = $lang->asb_top_poster_no_posts;
		$top_poster_text = $lang->asb_top_poster_no_top_poster;
		$top_poster_avatar = '';
		eval("\$\$template_var = \"{$templates->get('asb_top_posters_single')}\";");
		return false;
	}

	while ($user = $db->fetch_array($query)) {
		$top_poster = $lang->guest;
		if ($user['uid']) {
			$username = format_name($user['username'], $user['usergroup'], $user['displaygroup']);
			$profile_link = get_profile_link($user['uid']);
			$top_poster = build_profile_link($username, $user['uid']);
		}

		$top_poster_posts = $user['totalposts'];

		$post_lang = $lang->asb_top_poster_posts;
		if ($threadsOnly) {
			$post_lang = $lang->asb_top_poster_threads;
		}

		if ($top_poster_posts == 1) {
			$post_lang = $lang->asb_top_poster_post;
			if ($threadsOnly) {
				$post_lang = $lang->asb_top_poster_thread;
			}
		}

		$avatar_info = format_avatar($user['avatar']);
		$top_poster_avatar_src = $avatar_info['image'];

		if ($db->num_rows($query) == 1) {
			if ($time_frame == 0) {
				if ($threadsOnly) {
					$top_poster_text = $lang->sprintf($lang->asb_top_poster_congrats_all_time_threads, $top_poster, $top_poster_posts, $post_lang, $extraCongrats);
				} else {
					$top_poster_text = $lang->sprintf($lang->asb_top_poster_congrats_all_time, $top_poster, $top_poster_posts, $post_lang, $extraCongrats);
				}
			} else {
				if ($threadsOnly) {
					$top_poster_text = $lang->sprintf($lang->asb_top_poster_congrats_threads, $top_poster, $top_poster_timeframe, $top_poster_posts, $post_lang, $extraCongrats);
				} else {
					$top_poster_text = $lang->sprintf($lang->asb_top_poster_congrats, $top_poster, $top_poster_timeframe, $top_poster_posts, $post_lang, $extraCongrats);
				}
			}

			$avatar_width = (int) $width * .75;
			if ((int) $settings['avatar_size']) {
				$avatar_width = (int) $settings['avatar_size'];
			}

			eval("\$top_poster_avatar = \"{$templates->get('asb_top_poster_avatar')}\";");

			eval("\$\$template_var = \"{$templates->get('asb_top_posters_single')}\";");
		} else {
			$top_poster_description = $lang->sprintf($lang->asb_top_poster_description, $top_poster_timeframe_prelude).$extraCongrats;
			if ($threadsOnly) {
				$top_poster_description = $lang->sprintf($lang->asb_top_poster_description_threads, $top_poster_timeframe_prelude).$extraCongrats;
			}
			$top_poster_text = $top_poster.'<br />'.$top_poster_posts;

			eval("\$top_poster_avatar = \"{$templates->get('asb_top_poster_avatar')}\";");

			eval("\$top_posters .= \"{$templates->get('asb_top_poster')}\";");
		}
		$altbg = alt_trow();
	}

	if ($db->num_rows($query) > 1) {
		eval("\$\$template_var .= \"{$templates->get('asb_top_posters_multiple')}\";");
	}
	return true;
}

?>
