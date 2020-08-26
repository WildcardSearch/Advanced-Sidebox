<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * https://www.rantcentralforums.com
 *
 * ASB default module
 */

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
		'title' => $lang->asb_latest_threads,
		'description' => $lang->asb_latest_threads_desc,
		'version' => '2.0.0',
		'compatibility' => '4.0',
		'noContentTemplate' => 'asb_latest_threads_no_content',
		'wrap_content' => true,
		'xmlhttp' => true,
		'settings' => array(
			'max_threads' => array(
				'name' => 'max_threads',
				'title' => $lang->asb_max_threads_title,
				'description' => $lang->asb_max_threads_desc,
				'optionscode' => 'text',
				'value' => '20',
			),
			'max_thread_title_length' => array(
				'name' => 'max_thread_title_length',
				'title' => $lang->asb_max_thread_title_length_title,
				'description' => $lang->asb_max_thread_title_length_desc,
				'optionscode' => 'text',
				'value' => '40',
			),
			'forum_show_list' => array(
				'name' => 'forum_show_list',
				'title' => $lang->asb_forum_show_list_title,
				'description' => $lang->asb_forum_show_list_desc,
				'optionscode' => 'text',
				'value' => '',
			),
			'forum_hide_list' => array(
				'name' => 'forum_hide_list',
				'title' => $lang->asb_forum_hide_list_title,
				'description' => $lang->asb_forum_hide_list_desc,
				'optionscode' => 'text',
				'value' => '',
			),
			'thread_show_list' => array(
				'name' => 'thread_show_list',
				'title' => $lang->asb_thread_show_list_title,
				'description' => $lang->asb_thread_show_list_desc,
				'optionscode' => 'text',
				'value' => '',
			),
			'thread_hide_list' => array(
				'name' => 'thread_hide_list',
				'title' => $lang->asb_thread_hide_list_title,
				'description' => $lang->asb_thread_hide_list_desc,
				'optionscode' => 'text',
				'value' => '',
			),
			'last_poster_avatar' => array(
				'name' => 'last_poster_avatar',
				'title' => $lang->asb_last_poster_avatar_title,
				'description' => $lang->asb_last_poster_avatar_desc,
				'optionscode' => 'yesno',
				'value' => '0',
			),
			'avatar_width' => array(
				'name' => 'avatar_width',
				'title' => $lang->asb_avatar_width_title,
				'description' => $lang->asb_avatar_width_desc,
				'optionscode' => 'text',
				'value' => '20%',
			),
			'new_threads_only' => array(
				'name' => 'new_threads_only',
				'title' => $lang->asb_new_threads_only_title,
				'description' => $lang->asb_new_threads_only_desc,
				'optionscode' => 'text',
				'value' => '0',
			),
			'important_threads_only' => array(
				'name' => 'important_threads_only',
				'title' => $lang->asb_important_threads_only_title,
				'description' => $lang->asb_important_threads_only_desc,
				'optionscode' => 'yesno',
				'value' => '0',
			),
			'xthreads' => array(
				'name' => 'xthreads',
				'title' => $lang->asb_load_xthreads_data_title,
				'description' => $lang->asb_load_xthreads_data_desc,
				'optionscode' => 'yesno',
				'value' => '0',
			),
			'showinportal' => array(
				'name' => 'showinportal',
				'title' => $lang->asb_showinportal_threads_only_title,
				'description' => $lang->asb_showinportal_threads_only_desc,
				'optionscode' => 'yesno',
				'value' => '0',
			),
		),
		'installData' => array(
			'templates' => array(
				array(
					'title' => 'asb_latest_threads_thread',
					'template' => <<<EOF
				<div class="{\$altbg} asb-latest-threads-thread">
					<div class="asb-latest-threads-container">
						{\$last_poster}
						<div class="asb-latest-threads-title-container">
							{\$gotounread} <a href="{\$mybb->settings[\'bburl\']}/{\$thread[\'threadlink\']}" title="{\$fullSubject}"><span class="asb-latestest-threads-thread-title">{\$thread[\'subject\']}</span></a><br />
							<span class="smalltext">(Replies: {\$formattedReplies}; Views: {\$formattedViews})</span>
						</div>
						<div class="asb-latest-threads-last-post-container">
							{\$lastPostLink}
						</div>
					</div>
				</div>
EOF
				),
				array(
					'title' => 'asb_latest_threads_gotounread',
					'template' => <<<EOF
<a class="asb-latest-threads-thread-gotounread" href="{\$thread[\'newpostlink\']}"><img src="{\$theme[\'imgdir\']}/jump.png" alt="{\$lang->asb_gotounread}" title="{\$lang->asb_gotounread}" /></a>
EOF
				),
				array(
					'title' => 'asb_latest_threads_last_poster_name',
					'template' => <<<EOF
{\$lastPostLink} by {\$lastposterlink}
EOF
				),
				array(
					'title' => 'asb_latest_threads_last_poster_avatar',
					'template' => <<<EOF
{\$avatar}
EOF
				),
				array(
					'title' => 'asb_latest_threads_last_poster_avatar_avatar',
					'template' => <<<EOF
<a href="{\$lastposter_profile_link}" class="asb-latest-threads-last-poster-avatar" style="background-image: url({\$avatarInfo[\'image\']});" title="{\$thread[\'lastposter\']}\'s profile"></a>
EOF
				),
				array(
					'title' => 'asb_latest_threads_last_post_link',
					'template' => <<<EOF
<a class="latest-threads-last-post-link" href="{\$thread[\'lastpostlink\']}" title="{\$lang->asb_latest_threads_lastpost}">{\$lang->asb_latest_threads_lastpost}</a>
EOF
				),
				array(
					'title' => 'asb_latest_threads_no_content',
					'template' => <<<EOF
<div class="asb-no-content-message">{\$lang->asb_latest_threads_no_threads}</div>

EOF
				),
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
function asb_latest_threads_get_content($settings, $script, $dateline, $template_var)
{
	global $db, $mybb, $templates, $lang, $cache, $gotounread, $theme;

	if (!$lang->asb_addon) {
		$lang->load('asb_addon');
	}

	if ($mybb->user['uid'] == 0) {
		$query = $db->query("
			SELECT fid
			FROM {$db->table_prefix}forums
			WHERE active != 0
			ORDER BY pid, disporder
		");
		$forumsread = my_unserialize($mybb->cookies['mybb']['forumread']);
	} else {
		$query = $db->query("
			SELECT f.fid, fr.dateline AS lastread
			FROM {$db->table_prefix}forums f
			LEFT JOIN {$db->table_prefix}forumsread fr
				ON (fr.fid=f.fid AND fr.uid='{$mybb->user['uid']}')
			WHERE f.active != 0
			ORDER BY pid, disporder
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
	require_once MYBB_ROOT.'inc/class_parser.php';
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

	$new_threads = '';
	if ((int) $settings['new_threads_only'] > 0) {
		// use admin's time limit
		$thread_time_limit = TIME_NOW - 60 * 60 * 24 * (int) $settings['new_threads_only'];
		$new_threads .= " AND t.dateline > {$thread_time_limit}";
	}

	if ($settings['important_threads_only']) {
		$important_threads = ' AND NOT t.sticky=0';
	}

	// build the exclude conditions
	$show['fids'] = asbBuildIdList($settings['forum_show_list'], 't.fid');
	$show['tids'] = asbBuildIdList($settings['thread_show_list'], 't.tid');
	$hide['fids'] = asbBuildIdList($settings['forum_hide_list'], 't.fid');
	$hide['tids'] = asbBuildIdList($settings['thread_hide_list'], 't.tid');
	$where['show'] = asbBuildSqlWhere($show, ' OR ');
	$where['hide'] = asbBuildSqlWhere($hide, ' OR ', ' NOT ');
	$query_where = $new_threads.$important_threads.$unviewwhere.$inactivewhere.asbBuildSqlWhere($where, ' AND ', ' AND ');

	if ($dateline &&
		$dateline !== TIME_NOW) {
		$newQuery = $db->simple_select('threads t', 'tid', "t.visible='1' AND t.closed NOT LIKE 'moved|%'{$query_where} AND t.lastpost > {$dateline}", array('limit' => 1));

		if ($db->num_rows($newQuery) < 1) {
			// no new content
			return false;
		}

		$db->free_result($newQuery);
	}

	$altbg = alt_trow();
	$threadlist = '';

	$xthreads = function_exists('xthreads_gettfcache') && $settings['xthreads'];

	$xt_fields = $xt_join_code = '';

	!(function_exists('ougc_showinportal_info') && $settings['showinportal']) || $query_where .= "AND t.showinportal='1'";

	if($xthreads)
	{
		$xt_join_code = "LEFT JOIN {$db->table_prefix}threadfields_data tfd ON (tfd.tid=t.tid)";

		$threadfield_cache = xthreads_gettfcache();

		if(!empty($threadfield_cache))
		{
			$fids = array_flip(array_map('intval', explode(',', $settings['forum_show_list'])));
			$all_fids = ($settings['forum_show_list'] == '');
			$xt_fields = '';
			foreach($threadfield_cache as $k => &$v) {
				$available = (!$v['forums']) || $all_fids;
				if(!$available)
					foreach(explode(',', $v['forums']) as $fid) {
						if(isset($fids[$fid])) {
							$available = true;
							break;
						}
					}
				if($available)
					$xt_fields .= ', tfd.`'.$v['field'].'` AS `xthreads_'.$v['field'].'`';
			}
		}
	}

	// query for the latest forum discussions
	$query = $db->query("
		SELECT t.*, u.username, u.avatar, u.usergroup, u.displaygroup{$xt_fields}
		FROM {$db->table_prefix}threads t
		LEFT JOIN {$db->table_prefix}users u ON (u.uid=t.lastposteruid)
		{$xt_join_code}
		WHERE t.visible='1' AND t.closed NOT LIKE 'moved|%'{$query_where}
		ORDER BY t.lastpost DESC
		LIMIT 0, ".(int) $settings['max_threads']
	);

	if ($db->num_rows($query) == 0) {
		// no content
		return false;
	}

	$threadCache = array();

	while ($thread = $db->fetch_array($query)) {
		$threadCache[$thread['tid']] = $thread;
	}

	$threadIds = implode(',', array_keys($threadCache));

	// fetch the read threads.
	if ($mybb->user['uid'] &&
		$mybb->settings['threadreadcut'] > 0) {
		$query = $db->simple_select('threadsread', 'tid,dateline', "uid='{$mybb->user['uid']}' AND tid IN({$threadIds})");
		while ($readThread = $db->fetch_array($query)) {
			$threadCache[$readThread['tid']]['lastread'] = $readThread['dateline'];
		}
	}

	$xt_tids = '';
	!$xthreads || $xt_tids = '0,'.implode(',', array_keys($threadCache));

	foreach ($threadCache as $thread) {
		$forumpermissions[$thread['fid']] = forum_permissions($thread['fid']);

		// make sure we can view this thread
		if ($forumpermissions[$thread['fid']]['canview'] == 0 ||
			$forumpermissions[$thread['fid']]['canviewthreads'] == 0 ||
			$forumpermissions[$thread['fid']]['canonlyviewownthreads'] == 1 &&
			$thread['uid'] != $mybb->user['uid']) {
			continue;
		}

		$formattedViews = my_number_format($thread['views']);
		$formattedReplies = my_number_format($thread['replies']);

		$lastpostdate = my_date($mybb->settings['dateformat'], $thread['lastpost']);
		$lastposttime = my_date($mybb->settings['timeformat'], $thread['lastpost']);

		$unit = '';
		if (my_strpos($settings['avatar_width'], '%') != my_strlen($settings['avatar_width']) - 1) {
			$unit = 'px';
		}

		$avatar_width = $settings['avatar_width'];

		$avatarInfo = format_avatar($thread['avatar']);

		eval("\$avatar = \"{$templates->get('asb_latest_threads_last_poster_avatar_avatar')}\";");

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

		$fullSubject = htmlspecialchars_uni($thread['subject']);
		$max_len = (int) $settings['max_thread_title_length'];
		if ($max_len > 0 &&
			my_strlen($thread['subject']) > $max_len) {
			$thread['subject'] = my_substr($thread['subject'], 0, $max_len).$lang->asb_latest_threads_ellipsis;
		}

		$thread['subject'] = htmlspecialchars_uni($parser->parse_badwords($thread['subject']));
		$thread['threadlink'] = get_thread_link($thread['tid']);
		$thread['lastpostlink'] = get_thread_link($thread['tid'], 0, 'lastpost');

		eval("\$lastPostLink = \"{$templates->get('asb_latest_threads_last_post_link')}\";");
		eval("\$last_poster = \"{$templates->get($lp_template)}\";");

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
			eval("\$gotounread = \"{$templates->get('asb_latest_threads_gotounread')}\";");
			$unreadpost = 1;
		}

		if($xthreads && !empty($threadfield_cache)) {
			xthreads_set_threadforum_urlvars('thread', $thread['tid']);
			xthreads_set_threadforum_urlvars('forum', $thread['fid']);

			$threadfields = array();
			
			foreach($threadfield_cache as $k => &$v) {
				if($v['forums'] && strpos(','.$v['forums'].',', ','.$thread['fid'].',') === false)
					continue;

				xthreads_get_xta_cache($v, $xt_tids);
				
				$threadfields[$k] =& $thread['xthreads_'.$k];
				xthreads_sanitize_disp($threadfields[$k], $v, ($thread['username'] !== '' ? $thread['username'] : $thread['threadusername']));
			}
		}

		eval("\$threadlist .= \"{$templates->get('asb_latest_threads_thread')}\";");
		$altbg = alt_trow();
	}

	if ($threadlist) {
		return $threadlist;
	}

	return false;
}

/**
 * insert peeker for creation date
 *
 * @return void
 */
function asb_latest_threads_settings_load()
{
	echo <<<EOF

	<script type="text/javascript">
	new Peeker($(".setting_last_poster_avatar"), $("#row_setting_avatar_width"), /1/, true);
	</script>
EOF;
}

?>
