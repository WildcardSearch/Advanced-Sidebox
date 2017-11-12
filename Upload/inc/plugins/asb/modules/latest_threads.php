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
function asb_latest_threads_info()
{
	global $lang;

	if (!$lang->asb_addon) {
		$lang->load('asb_addon');
	}

	return array(
		"title" => $lang->asb_latest_threads,
		"description" => $lang->asb_latest_threads_desc,
		"version" => '1.1.3',
		"compatibility" => '2.1',
		"wrap_content" => true,
		"xmlhttp" => true,
		"settings" => array(
			"max_threads" => array(
				"sid" => 'NULL',
				"name" => 'max_threads',
				"title" => $lang->asb_max_threads_title,
				"description" => $lang->asb_max_threads_desc,
				"optionscode" => 'text',
				"value" => '20',
			),
			"max_thread_title_length" => array(
				"sid" => 'NULL',
				"name" => 'max_thread_title_length',
				"title" => $lang->asb_max_thread_title_length_title,
				"description" => $lang->asb_max_thread_title_length_desc,
				"optionscode" => 'text',
				"value" => '40',
			),
			"forum_show_list" => array(
				"sid" => 'NULL',
				"name" => 'forum_show_list',
				"title" => $lang->asb_forum_show_list_title,
				"description" => $lang->asb_forum_show_list_desc,
				"optionscode" => 'text',
				"value" => '',
			),
			"forum_hide_list" => array(
				"sid" => 'NULL',
				"name" => 'forum_hide_list',
				"title" => $lang->asb_forum_hide_list_title,
				"description" => $lang->asb_forum_hide_list_desc,
				"optionscode" => 'text',
				"value" => '',
			),
			"thread_show_list" => array(
				"sid" => 'NULL',
				"name" => 'thread_show_list',
				"title" => $lang->asb_thread_show_list_title,
				"description" => $lang->asb_thread_show_list_desc,
				"optionscode" => 'text',
				"value" => '',
			),
			"thread_hide_list" => array(
				"sid" => 'NULL',
				"name" => 'thread_hide_list',
				"title" => $lang->asb_thread_hide_list_title,
				"description" => $lang->asb_thread_hide_list_desc,
				"optionscode" => 'text',
				"value" => '',
			),
			"last_poster_avatar" => array(
				"sid" => 'NULL',
				"name" => 'last_poster_avatar',
				"title" => $lang->asb_last_poster_avatar_title,
				"description" => $lang->asb_last_poster_avatar_desc,
				"optionscode" => 'yesno',
				"value" => '0',
			),
			"avatar_width" => array(
				"sid" => 'NULL',
				"name" => 'avatar_width',
				"title" => $lang->asb_avatar_width_title,
				"description" => $lang->asb_avatar_width_desc,
				"optionscode" => 'text',
				"value" => '30',
			),
			"new_threads_only" => array(
				"sid" => 'NULL',
				"name" => 'new_threads_only',
				"title" => $lang->asb_new_threads_only_title,
				"description" => $lang->asb_new_threads_only_desc,
				"optionscode" => 'text',
				"value" => '0',
			),
			"important_threads_only" => array(
				"sid" => 'NULL',
				"name" => 'important_threads_only',
				"title" => $lang->asb_important_threads_only_title,
				"description" => $lang->asb_important_threads_only_desc,
				"optionscode" => 'yesno',
				"value" => '0',
			),
			"xmlhttp_on" => array(
				"sid" => 'NULL',
				"name" => 'xmlhttp_on',
				"title" => $lang->asb_xmlhttp_on_title,
				"description" => $lang->asb_xmlhttp_on_description,
				"optionscode" => 'text',
				"value" => '0',
			),
		),
		"templates" => array(
			array(
				"title" => 'asb_latest_threads_thread',
				"template" => <<<EOF
				<tr>
					<td class="{\$altbg}">
						{\$gotounread}<a href="{\$mybb->settings[\'bburl\']}/{\$thread[\'threadlink\']}" title="{\$fullSubject}"><strong>{\$thread[\'subject\']}</strong></a>
						<span class="smalltext"><br />
							{\$last_poster}<br />
							{\$lastpostdate} {\$lastposttime}<br />
							<strong>&raquo; </strong>{\$lang->asb_latest_threads_replies} {\$thread[\'replies\']}<br />
							<strong>&raquo; </strong>{\$lang->asb_latest_threads_views} {\$thread[\'views\']}
						</span>
					</td>
				</tr>
EOF
			),
			array(
				"title" => "asb_latest_threads_gotounread",
				"template" => <<<EOF
<a href="{\$thread[\'newpostlink\']}"><img src="{\$theme[\'imgdir\']}/jump.png" alt="{\$lang->asb_gotounread}" title="{\$lang->asb_gotounread}" /></a>
EOF
			),
			array(
				"title" => "asb_latest_threads_last_poster_name",
				"template" => <<<EOF
<a href="{\$thread[\'lastpostlink\']}" title="{\$lang->asb_latest_threads_lastpost}">{\$lang->asb_latest_threads_lastpost}:</a> {\$lastposterlink}
EOF
			),
			array(
				"title" => "asb_latest_threads_last_poster_avatar",
				"template" => <<<EOF
{\$lastposterlink}<br /><a href="{\$thread[\'lastpostlink\']}" title="{\$lang->asb_latest_threads_lastpost}">{\$lang->asb_latest_threads_lastpost}</a>
EOF
			),
		),
	);
}

/**
 * handles display of children of this addon at page load
 *
 * @param  array information from child box
 * @return bool success/fail
 */
function asb_latest_threads_build_template($args)
{
	extract($args);
	global $$template_var, $lang;

	if (!$lang->asb_addon) {
		$lang->load('asb_addon');
	}

	// get the threads (or at least attempt to)
	$all_threads = latest_threads_get_threadlist($settings, $width);

	if ($all_threads) {
		// if there are threads, show them
		$$template_var = $all_threads;
		return true;
	} else {
		// if not, show nothing
		$$template_var = <<<EOF
<tr><td class="trow1">{$lang->asb_latest_threads_no_threads}</td></tr>
EOF;
		return false;
	}
}

/**
 * handles display of children of this addon via AJAX
 *
 * @param  array information from child box
 * @return void
 */
function asb_latest_threads_xmlhttp($args)
{
	extract($args);
	global $db;

	// do a quick check to make sure we don't waste execution
	$query = $db->simple_select('posts', '*', "dateline > {$dateline}");

	if ($db->num_rows($query) > 0) {
		$all_threads = latest_threads_get_threadlist($settings, $width);

		if ($all_threads) {
			return $all_threads;
		}
	}
	return 'nochange';
}

/**
 * get the latest forum discussions
 *
 * @param  array settings
 * @param  int column width
 * @return string|bool html or success/fail
 */
function latest_threads_get_threadlist($settings, $width)
{
	global $db, $mybb, $templates, $lang, $cache, $gotounread, $theme;

	if (!$lang->asb_addon) {
		$lang->load('asb_addon');
	}

	if ($mybb->user['uid'] == 0) {
		$query = $db->query("
			SELECT
				fid
			FROM {$db->table_prefix}forums
			WHERE
				active != 0
			ORDER BY
				pid, disporder
		");
		$forumsread = my_unserialize($mybb->cookies['mybb']['forumread']);
	} else {
		$query = $db->query("
			SELECT
				f.fid, fr.dateline AS lastread
			FROM {$db->table_prefix}forums f
			LEFT JOIN {$db->table_prefix}forumsread fr ON (fr.fid=f.fid AND fr.uid='{$mybb->user['uid']}')
			WHERE
				f.active != 0
			ORDER BY
				pid, disporder
		");
	}

	while ($forum = $db->fetch_array($query)) {
		if ($mybb->user['uid'] == 0) {
			if ($forumsread[$forum['fid']]) {
				$forum['lastread'] = $forumsread[$forum['fid']];
			}
		}
		$readforums[$forum['fid']] = $forum['lastread'];
	}

	// Build a post parser
	require_once MYBB_ROOT . 'inc/class_parser.php';
	$parser = new postParser;

	// get forums user cannot view
	$unviewable = get_unviewable_forums(true);
	if ($unviewable) {
		$unviewwhere = " AND t.fid NOT IN ({$unviewable})";
	}

	// get inactive forums
	$inactive = get_inactive_forums();
	if ($inactive) {
		$inactivewhere = " AND t.fid NOT IN ({$inactive})";
	}

	// new threads only?
	if ((int) $settings['new_threads_only'] > 0) {
		// use admin's time limit
		$thread_time_limit = TIME_NOW - 60 * 60 * 24 * (int) $settings['new_threads_only'];
		$new_threads = " AND t.dateline > {$thread_time_limit}";
	}

	if ($settings['important_threads_only']) {
		$important_threads = ' AND NOT t.sticky=0';
	}

	// build the exclude conditions
	$show['fids'] = asb_build_id_list($settings['forum_show_list'], 't.fid');
	$show['tids'] = asb_build_id_list($settings['thread_show_list'], 't.tid');
	$hide['fids'] = asb_build_id_list($settings['forum_hide_list'], 't.fid');
	$hide['tids'] = asb_build_id_list($settings['thread_hide_list'], 't.tid');
	$where['show'] = asb_build_SQL_where($show, ' OR ');
	$where['hide'] = asb_build_SQL_where($hide, ' OR ', ' NOT ');
	$query_where = $new_threads . $important_threads . $unviewwhere . $inactivewhere . asb_build_SQL_where($where, ' AND ', ' AND ');

	$altbg = alt_trow();
	$threadlist = '';

	// query for the latest forum discussions
	$query = $db->query("
		SELECT
			t.*,
			u.username, u.avatar, u.usergroup, u.displaygroup
		FROM {$db->table_prefix}threads t
		LEFT JOIN {$db->table_prefix}users u ON (u.uid=t.lastposteruid)
		WHERE
			t.visible='1' AND t.closed NOT LIKE 'moved|%'{$query_where}
		ORDER BY
			t.lastpost DESC
		LIMIT
			0, " . (int) $settings['max_threads']
	);

	if ($db->num_rows($query) == 0) {
		// no content
		return false;
	}

	$thread_cache = array();

	while ($thread = $db->fetch_array($query)) {
		$thread_cache[$thread['tid']] = $thread;
	}

	$thread_ids = implode(",", array_keys($thread_cache));

	// fetch the read threads.
	if ($mybb->user['uid'] &&
		$mybb->settings['threadreadcut'] > 0) {
		$query = $db->simple_select('threadsread', 'tid,dateline', "uid='{$mybb->user['uid']}' AND tid IN({$thread_ids})");
		while ($readthread = $db->fetch_array($query)) {
			$thread_cache[$readthread['tid']]['lastread'] = $readthread['dateline'];
		}
	}

	foreach ($thread_cache as $thread) {
		$forumpermissions[$thread['fid']] = forum_permissions($thread['fid']);

		// make sure we can view this thread
		if ($forumpermissions[$thread['fid']]['canview'] == 0 ||
			$forumpermissions[$thread['fid']]['canviewthreads'] == 0 ||
			$forumpermissions[$thread['fid']]['canonlyviewownthreads'] == 1 &&
			$thread['uid'] != $mybb->user['uid']) {
			continue;
		}

		$lastpostdate = my_date($mybb->settings['dateformat'], $thread['lastpost']);
		$lastposttime = my_date($mybb->settings['timeformat'], $thread['lastpost']);

		if (strlen(trim($thread['avatar'])) == 0) {
			$thread['avatar'] = "{$theme['imgdir']}/default_avatar.png";
		}

		$settings['avatar_width'] = trim($settings['avatar_width']);
		if (my_strpos($settings['avatar_width'], '%') == my_strlen($settings['avatar_width']) - 1) {
			$settings['avatar_width'] = (int) $width * (my_substr($settings['avatar_width'], 0, my_strlen($settings['avatar_width']) - 1) / 100);
		}
		$avatar_width = (int) min($width / 2, max($width / 8, $settings['avatar_width']));
		$avatar = <<<EOF
<img src="{$thread['avatar']}" alt="{$thread['last_post']}" title="{$thread['lastposter']}'s profile" style="width: {$avatar_width}px;"/>
EOF;

		$formatted_name = format_name($thread['lastposter'], $thread['usergroup'], $thread['displaygroup']);

		$lastposter_profile_link = get_profile_link($thread['lastposteruid']);

		$formatted_name_profile_link = build_profile_link($formatted_name, $thread['lastposteruid']);

		$avatar_profile_link = build_profile_link($avatar, $thread['lastposteruid']);

		// don't link to guest's profiles (they have no profile).
		if ($thread['lastposteruid'] == 0) {
			$lastposterlink = $thread['lastposter'];
		} else {
			$lp_template = 'asb_latest_threads_last_poster_name';
			$lastposterlink = $formatted_name_profile_link;
			if ($settings['last_poster_avatar']) {
				$lastposterlink = $avatar_profile_link;
				$lp_template = 'asb_latest_threads_last_poster_avatar';
			}
		}

		$fullSubject = $thread['subject'];
		$max_len = (int) $settings['max_thread_title_length'];
		if ($max_len > 0 &&
			my_strlen($thread['subject']) > $max_len) {
			$thread['subject'] = my_substr($thread['subject'], 0, $max_len) . $lang->asb_latest_threads_ellipsis;
		}

		$thread['subject'] = htmlspecialchars_uni($parser->parse_badwords($thread['subject']));
		$thread['threadlink'] = get_thread_link($thread['tid']);
		$thread['lastpostlink'] = get_thread_link($thread['tid'], 0, "lastpost");

		eval("\$last_poster = \"" . $templates->get($lp_template) . "\";");

		$gotounread = '';
		$last_read = 0;

		if ($mybb->settings['threadreadcut'] > 0 &&
			$mybb->user['uid']) {
			$forum_read = $readforums[$thread['fid']];

			$read_cutoff = TIME_NOW-$mybb->settings['threadreadcut']*60*60*24;
			if ($forum_read == 0 ||
				$forum_read < $read_cutoff) {
				$forum_read = $read_cutoff;
			}
		} else {
			$forum_read = $forumsread[$thread['fid']];
		}

		if ($mybb->settings['threadreadcut'] > 0 &&
			$mybb->user['uid'] &&
			$thread['lastpost'] > $forum_read) {
			if ($thread['lastread']) {
				$last_read = $thread['lastread'];
			} else {
				$last_read = $read_cutoff;
			}
		} else {
			$last_read = my_get_array_cookie('threadread', $thread['tid']);
		}

		if ($forum_read > $last_read) {
			$last_read = $forum_read;
		}

		if ($thread['lastpost'] > $last_read &&
			$last_read) {
			$thread['newpostlink'] = get_thread_link($thread['tid'], 0, 'newpost');
			eval("\$gotounread = \"" . $templates->get("asb_latest_threads_gotounread") . "\";");
			$unreadpost = 1;
		}

		eval("\$threadlist .= \"".$templates->get("asb_latest_threads_thread")."\";");
		$altbg = alt_trow();
	}

	if ($threadlist) {
		return $threadlist;
	}
	// no content
	return false;
}

?>
