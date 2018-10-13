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
		'version' => '1.3.2',
		'compatibility' => '2.1',
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
			'xmlhttp_on' => array(
				'name' => 'xmlhttp_on',
				'title' => $lang->asb_xmlhttp_on_title,
				'description' => $lang->asb_xmlhttp_on_description,
				'optionscode' => 'text',
				'value' => '0',
			),
		),
		'templates' => array(
			array(
				'title' => 'asb_recent_posts_post',
				'template' => <<<EOF
				<tr>
					<td style="text-align: center;" class="tcat">
						<a style="font-weight: bold;" href="{\$mybb->settings[\'bburl\']}/{\$post[\'link\']}" title="{\$post[\'subject\']}">{\$post[\'subject\']}</a>
					</td>
				</tr>
				<tr>
					<td class="{\$altbg}">{\$post_excerpt}<span style="position: relative; float: right;">{\$post_author} &mdash; {\$lastposttime}</span></td>
				</tr>
EOF
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
function asb_recent_posts_build_template($args)
{
	extract($args);
	global $$template_var, $lang;

	if (!$lang->asb_addon) {
		$lang->load('asb_addon');
	}

	// get the posts (or at least attempt to)
	$all_posts = recent_posts_get_postlist($settings);

	if ($all_posts) {
		// if there are posts, show them
		$$template_var = $all_posts;
		return true;
	} else {
		// if not, show nothing
		$$template_var = <<<EOF
<tr><td class="trow1">{$lang->asb_recent_posts_no_posts}</td></tr>
EOF;
		return false;
	}
}

/**
 * handles display of children of this addon via AJAX
 *
 * @param  array info from child box
 * @return void
 */
function asb_recent_posts_xmlhttp($args)
{
	extract($args);
	$all_posts = recent_posts_get_postlist($settings);

	if (!$all_posts) {
		return 'nochange';
	}
	return $all_posts;
}

/**
 * get random quotes
 *
 * @param  array settings
 * @return string|bool html or success/fail
 */
function recent_posts_get_postlist($settings)
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
	$show['fids'] = asb_build_id_list($settings['forum_show_list'], 'p.fid');
	$show['tids'] = asb_build_id_list($settings['thread_show_list'], 'p.tid');
	$hide['fids'] = asb_build_id_list($settings['forum_hide_list'], 'p.fid');
	$hide['tids'] = asb_build_id_list($settings['thread_hide_list'], 'p.tid');
	$where['show'] = asb_build_SQL_where($show, ' OR ');
	$where['hide'] = asb_build_SQL_where($hide, ' OR ', ' NOT ');
	$query_where = $important_threads . $unviewwhere . $inactivewhere . asb_build_SQL_where($where, ' AND ', ' AND ');

	$altbg = alt_trow();
	$maxtitlelen = 48;
	$postlist = '';

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
			0, " . (int) $settings['max_posts']
	);

	if ($db->num_rows($query) == 0) {
		// no content
		return false;
	}

	// Build a post parser
	require_once MYBB_ROOT . 'inc/class_parser.php';
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
			$post['subject'] = my_substr($post['subject'], 0, $max_len) . $lang->asb_recent_posts_title_ellipsis;
		}

		$post['subject'] = htmlspecialchars_uni($parser->parse_badwords($post['subject']));
		$post['link'] = get_post_link($post['pid'], $post['tid']) . "#pid{$post['pid']}";

		// we just need the text and smilies (we'll parse them after we check length)
		$pattern = "|[[\/\!]*?[^\[\]]*?]|si";
		$post_excerpt = strip_tags(str_replace('<br />', '', asb_strip_url(preg_replace($pattern, '$1', $post['message']))));

		if ($settings['max_length'] &&
			strlen($post_excerpt) > $settings['max_length']) {
			$post_excerpt = my_substr($post_excerpt, 0, $settings['max_length']) . $lang->asb_recent_posts_ellipsis;
		}

		eval("\$postlist .= \"" . $templates->get('asb_recent_posts_post') . "\";");
		$altbg = alt_trow();
	}
	return $postlist;
}

?>
