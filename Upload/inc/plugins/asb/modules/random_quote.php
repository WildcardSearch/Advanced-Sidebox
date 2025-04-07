<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * https://www.rantcentralforums.com
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
function asb_random_quote_info()
{
	global $lang;

	if (!$lang->asb_addon) {
		$lang->load('asb_addon');
	}

	return array(
		'title' => $lang->asb_random_quotes,
		'description' => $lang->asb_random_quotes_desc,
		'wrap_content' => true,
		'xmlhttp' => true,
		'version' => '2.0.0',
		'compatibility' => '4.0',
		'noContentTemplate' => 'asb_random_quote_no_content',
		'settings' => array(
			'forum_show_list' => array(
				'sid' => 'NULL',
				'name' => 'forum_show_list',
				'title' => $lang->asb_forum_show_list_title,
				'description' => $lang->asb_forum_show_list_desc,
				'optionscode' => 'text',
				'value' => '',
			),
			'forum_hide_list' => array(
				'sid' => 'NULL',
				'name' => 'forum_hide_list',
				'title' => $lang->asb_forum_hide_list_title,
				'description' => $lang->asb_forum_hide_list_desc,
				'optionscode' => 'text',
				'value' => '',
			),
			'thread_show_list' => array(
				'sid' => 'NULL',
				'name' => 'thread_show_list',
				'title' => $lang->asb_thread_show_list_title,
				'description' => $lang->asb_thread_show_list_desc,
				'optionscode' => 'text',
				'value' => '',
			),
			'thread_hide_list' => array(
				'sid' => 'NULL',
				'name' => 'thread_hide_list',
				'title' => $lang->asb_thread_hide_list_title,
				'description' => $lang->asb_thread_hide_list_desc,
				'optionscode' => 'text',
				'value' => '',
			),
			'min_length' => array(
				'sid' => 'NULL',
				'name' => 'min_length',
				'title' => $lang->asb_random_quotes_min_quote_length_title,
				'description' => $lang->asb_random_quotes_min_quote_length_desc,
				'optionscode' => 'text',
				'value' => '20',
			),
			'max_length' => array(
				'sid' => 'NULL',
				'name' => 'max_length',
				'title' => $lang->asb_random_quotes_max_quote_length_title,
				'description' => $lang->asb_random_quotes_max_quote_length,
				'optionscode' => 'text',
				'value' => '160',
			),
			'default_text' => array(
				'sid' => 'NULL',
				'name' => 'default_text',
				'title' => $lang->asb_random_quotes_default_text_title,
				'description' => $lang->asb_random_quotes_default_text_desc,
				'optionscode' => 'text',
				'value' => '',
			),
			'important_threads_only' => array(
				'sid' => 'NULL',
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
					'title' => 'asb_random_quote_sidebox',
					'template' => <<<EOF
				<div class="tcat asb-random-quote-header">
					{\$thread_title_link}
				</div>
				<div class="trow1 asb-random-quote-user-info">
					<img class="asb-random-quote-user-avatar" src="{\$avatar_filename}" alt="{\$avatar_alt}" title="{\$avatar_alt}"/>&nbsp;<a class="asb-random-quote-user-link" href="{\$author_link}" title="{\$plain_text_username}"><span>{\$username}</span></a>
				</div>
				<div class="trow2 asb-random-quote-message">
					<span>{\$new_message}</span>
				</div>{\$read_more}
EOF
				),
				array(
					'title' => 'asb_random_quote_read_more',
					'template' => <<<EOF

				<div class="tfoot asb-random-quote-footer">
					<a href="{\$post_link}" title="{\$lang->asb_random_quotes_read_more_title}"><strong>{\$lang->asb_random_quotes_read_more}</strong></a>
				</div>
EOF
				),
				array(
					'title' => 'asb_random_quote_thread_title_link',
					'template' => <<<EOF
<a class="asb-random-quote-thread_title_link" href="{\$thread_link}" title="{\$lang->asb_random_quotes_read_more_threadlink_title}"><span>{\$rand_post[\'subject\']}</span></a>
EOF
				),
				array(
					'title' => 'asb_random_quote_no_content',
					'template' => <<<EOF
<div class="asb-no-content-message">{\$lang->asb_random_quotes_no_posts}</div>

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
function asb_random_quote_get_content($settings, $script, $dateline)
{
	global $db, $mybb, $templates, $lang, $theme;

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

	$post_query = $db->query("
		SELECT
			p.pid, p.message, p.fid, p.tid, p.subject, p.uid,
			u.username, u.usergroup, u.displaygroup, u.avatar,
			t.sticky
		FROM {$db->table_prefix}posts p
		LEFT JOIN {$db->table_prefix}users u ON (u.uid=p.uid)
		LEFT JOIN {$db->table_prefix}threads t ON (t.tid=p.tid)
		WHERE
			p.visible='1'{$query_where}
		ORDER BY
			RAND()
		LIMIT 1;"
	);

	// if there was 1...
	if ($db->num_rows($post_query) == 0) {
		return false;
	}

	$rand_post = $db->fetch_array($post_query);

	// build a post parser
	require_once MYBB_ROOT.'inc/class_parser.php';
	$parser = new postParser;

	// we just need the text and smilies (we'll parse them after we check length)
	$pattern = "|[[\/\!]*?[^\[\]]*?]|si";
	$new_message = asbStripUrls(preg_replace($pattern, '$1', $rand_post['message']));

	if (strlen($new_message) < $settings['min_length']) {
		if ($settings['default_text']) {
			$new_message = $settings['default_text'];
		} else {
			// nothing to show
			return false;
		}
	}

	if ($settings['max_length'] &&
		strlen($new_message) > $settings['max_length']) {
		$new_message = substr($new_message, 0, $settings['max_length']).'...';
	}

	// set up the user name link so that it displays correctly for the display group of the user
	$plain_text_username = htmlspecialchars_uni($rand_post['username']);
	$username = format_name($plain_text_username, $rand_post['usergroup'], $rand_post['displaygroup']);
	$author_link = get_profile_link($rand_post['uid']);
	$post_link = get_post_link($rand_post['pid'], $rand_post['tid']).'#pid'.$rand_post['pid'];
	$thread_link = get_thread_link($rand_post['tid']);

	// allow smilies, but kill
	$parser_options = array('allow_smilies' => 1);
	$new_message = str_replace(array('<br />', '/me'), array('', " * {$plain_text_username}"), $parser->parse_message($new_message.' ', $parser_options));

	$avatar_info = format_avatar($rand_post['avatar']);
	$avatar_filename = $avatar_info['image'];

	$avatar_alt = $lang->sprintf($lang->asb_random_quote_users_profile, $plain_text_username);

	eval("\$read_more = \"{$templates->get('asb_random_quote_read_more')}\";");

	if (my_strlen($rand_post['subject']) > 40) {
		$rand_post['subject'] = my_substr($rand_post['subject'], 0, 37).'...';
	}

	if (substr(my_strtolower($rand_post['subject']), 0, 3) == 're:') {
		$rand_post['subject'] = substr($rand_post['subject'], 3);
	}

	$rand_post['subject'] = htmlspecialchars_uni($parser->parse_badwords($rand_post['subject']));

	eval("\$thread_title_link = \"{$templates->get('asb_random_quote_thread_title_link')}\";");

	// eval() the template
	eval("\$this_quote = \"{$templates->get('asb_random_quote_sidebox')}\";");
	return $this_quote;
}