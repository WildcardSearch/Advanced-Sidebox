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
function asb_recent_posts_info()
{
	global $lang;

	if (!$lang->asb_addon) {
		$lang->load('asb_addon');
	}

	return array(
		'title' => $lang->asb_recent_posts,
		'description' => $lang->asb_recent_posts_desc,
		'version' => '2.0.1',
		'compatibility' => '4.0',
		'noContentTemplate' => 'asb_recent_posts_no_content',
		'wrap_content' => true,
		'xmlhttp' => true,
		'settings' => array(
			'max_posts' => array(
				'name' => 'max_posts',
				'title' => $lang->asb_recent_posts_max_title,
				'description' => $lang->asb_recent_posts_max_description,
				'optionscode' => 'text',
				'value' => '5',
			),
			'max_length' => array(
				'name' => 'max_length',
				'title' => $lang->asb_recent_posts_max_length_title,
				'description' => $lang->asb_recent_posts_max_length_description,
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
			'important_threads_only' => array(
				'name' => 'important_threads_only',
				'title' => $lang->asb_important_threads_only_title,
				'description' => $lang->asb_important_threads_only_desc,
				'optionscode' => 'yesno',
				'value' => '0',
			),
		),
		'installData' => array(
			'templates' => array(
				array(
					'title' => 'asb_recent_posts_post',
					'template' => <<<EOF
				<div class="tcat asb-recent-posts-title">
					<a style="font-weight: bold;" href="{\$mybb->settings[\'bburl\']}/{\$post[\'link\']}" title="{\$post[\'subject\']}">{\$post[\'subject\']}</a>
				</div>
				<div class="{\$altbg} asb-recent-posts-excerpt">
					{\$post_excerpt}<span>{\$post_author} &mdash; {\$lastposttime}</span>
				</div>
EOF
				),
				array(
					'title' => 'asb_recent_posts_no_content',
					'template' => <<<EOF
<div class="asb-no-content-message">{\$lang->asb_recent_posts_no_posts}</div>

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
 * @return bool sucess/fail
 */
function asb_recent_posts_get_content($settings, $script, $dateline)
{
	global $db, $mybb, $templates, $lang, $cache, $postlist, $gotounread, $theme;

	// load custom language phrases
	if (!$lang->asb_addon) {
		$lang->load('asb_addon');
	}

	// get forums user cannot view
	$unviewable = get_unviewable_forums(true);
	if ($unviewable) {
		$unviewwhere = " AND p.fid NOT IN ({$unviewable})";
	}

	// get inactive forums
	$inactive = get_inactive_forums();
	if ($inactive) {
		$inactivewhere = " AND p.fid NOT IN ({$inactive})";
	}

	if ($settings['important_threads_only']) {
		$important_threads = ' AND NOT t.sticky=0';
	}

	// build the exclude conditions
	$show['fids'] = asbBuildIdList($settings['forum_show_list'], 'p.fid');
	$show['tids'] = asbBuildIdList($settings['thread_show_list'], 'p.tid');
	$hide['fids'] = asbBuildIdList($settings['forum_hide_list'], 'p.fid');
	$hide['tids'] = asbBuildIdList($settings['thread_hide_list'], 'p.tid');
	$where['show'] = asbBuildSqlWhere($show, ' OR ');
	$where['hide'] = asbBuildSqlWhere($hide, ' OR ', ' NOT ');
	$query_where = $important_threads.$unviewwhere.$inactivewhere.asbBuildSqlWhere($where, ' AND ', ' AND ');

	if ($dateline &&
		$dateline !== TIME_NOW) {
		$newQuery = $db->simple_select('posts p', 'pid', "p.visible='1'{$query_where} AND p.dateline > {$dateline}", array('limit' => 1));

		if ($db->num_rows($newQuery) < 1) {
			// no new content
			return false;
		}

		$db->free_result($newQuery);
	}

	// Query for the latest forum discussions
	$query = $db->query("
		SELECT p.tid, p.pid, p.message, p.fid, p.dateline, p.subject,
			u.username, u.uid, u.displaygroup, u.usergroup,
			t.sticky
		FROM {$db->table_prefix}posts p
		LEFT JOIN {$db->table_prefix}users u ON (u.uid=p.uid)
		LEFT JOIN {$db->table_prefix}threads t ON (t.tid=p.tid)
		WHERE
			p.visible='1'{$query_where}
		ORDER BY
			p.dateline DESC
		LIMIT
			0, ".(int) $settings['max_posts']
	);

	if ($db->num_rows($query) == 0) {
		// no content
		return false;
	}

	$altbg = alt_trow();
	$maxtitlelen = 48;
	$postlist = '';

	// Build a post parser
	require_once MYBB_ROOT.'inc/class_parser.php';
	$parser = new postParser;

	$post_cache = array();
	while ($post = $db->fetch_array($query)) {
		$post_cache[$post['pid']] = $post;
	}

	foreach ($post_cache as $post) {
		$forumpermissions[$post['fid']] = forum_permissions($post['fid']);

		// make sure we can view this post
		if($forumpermissions[$post['fid']]['canview'] == 0 || $forumpermissions[$post['fid']]['canviewthreads'] == 0 || $forumpermissions[$post['fid']]['canonlyviewownthreads'] == 1 && $post['uid'] != $mybb->user['uid'])
		{
			continue;
		}

		$lastposttime = my_date($mybb->settings['timeformat'], $post['dateline']);

		// don't link to guest's profiles (they have no profile).
		if ($post['uid'] == 0) {
			$post_author = $post['username'];
		} else {
			$post_author_name = format_name($post['username'], $post['usergroup'], $post['displaygroup']);
			$post_author = build_profile_link($post_author_name, $post['uid']);
		}

		if (substr(strtolower($post['subject']), 0, 3) == 're:') {
			$post['subject'] = substr($post['subject'], 3);
		}

		$max_len = (int) $settings['max_thread_title_length'];
		if ($max_len > 0 &&
			my_strlen($post['subject']) > $max_len) {
			$post['subject'] = my_substr($post['subject'], 0, $max_len).$lang->asb_recent_posts_title_ellipsis;
		}

		$post['subject'] = htmlspecialchars_uni($parser->parse_badwords($post['subject']));
		$post['link'] = get_post_link($post['pid'], $post['tid'])."#pid{$post['pid']}";

		// we just need the text and smilies (we'll parse them after we check length)
		$pattern = "|[[\/\!]*?[^\[\]]*?]|si";
		$post_excerpt = strip_tags(str_replace('<br />', '', asbStripUrls(preg_replace($pattern, '$1', $post['message']))));
		$post_excerpt = htmlspecialchars_uni($parser->parse_badwords($post_excerpt));

		if ($settings['max_length'] &&
			strlen($post_excerpt) > $settings['max_length']) {
			$post_excerpt = my_substr($post_excerpt, 0, $settings['max_length']).$lang->asb_recent_posts_ellipsis;
		}

		eval("\$postlist .= \"{$templates->get('asb_recent_posts_post')}\";");
		$altbg = alt_trow();
	}

	return $postlist;
}

?>
