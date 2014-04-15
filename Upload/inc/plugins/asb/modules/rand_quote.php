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
 * asb_rand_quote_info()
 *
 * provide info to ASB about the addon
 *
 * @return: (array) the module info
 */
function asb_rand_quote_info()
{
	global $lang;

	if(!$lang->asb_addon)
	{
		$lang->load('asb_addon');
	}

	return array(
		"title" => $lang->asb_random_quotes,
		"description" => $lang->asb_random_quotes_desc,
		"wrap_content" => true,
		"xmlhttp" => true,
		"version" => '1.5.1',
		"compatibility" => '2.1',
		"settings" => array(
			"forum_show_list" => array(
				"sid" => 'NULL',
				"name" => 'forum_show_list',
				"title" => $lang->asb_forum_show_list_title,
				"description" => $lang->asb_forum_show_list_desc,
				"optionscode" => 'text',
				"value" => ''
			),
			"forum_hide_list" => array(
				"sid" => 'NULL',
				"name" => 'forum_hide_list',
				"title" => $lang->asb_forum_hide_list_title,
				"description" => $lang->asb_forum_hide_list_desc,
				"optionscode" => 'text',
				"value" => ''
			),
			"thread_show_list" => array(
				"sid" => 'NULL',
				"name" => 'thread_show_list',
				"title" => $lang->asb_thread_show_list_title,
				"description" => $lang->asb_thread_show_list_desc,
				"optionscode" => 'text',
				"value" => ''
			),
			"thread_hide_list" => array(
				"sid" => 'NULL',
				"name" => 'thread_hide_list',
				"title" => $lang->asb_thread_hide_list_title,
				"description" => $lang->asb_thread_hide_list_desc,
				"optionscode" => 'text',
				"value" => ''
			),
			"min_length" => array(
				"sid" => 'NULL',
				"name" => 'min_length',
				"title" => $lang->asb_random_quotes_min_quote_length_title,
				"description" => $lang->asb_random_quotes_min_quote_length_desc,
				"optionscode" => 'text',
				"value" => '20'
			),
			"max_length" => array(
				"sid" => 'NULL',
				"name" => 'max_length',
				"title" => $lang->asb_random_quotes_max_quote_length_title,
				"description" => $lang->asb_random_quotes_max_quote_length,
				"optionscode" => 'text',
				"value" => '160'
			),
			"default_text" => array(
				"sid" => 'NULL',
				"name" => 'default_text',
				"title" => $lang->asb_random_quotes_default_text_title,
				"description" => $lang->asb_random_quotes_default_text_desc,
				"optionscode" => 'text',
				"value" => ''
			),
			"important_threads_only" => array(
				"sid" => 'NULL',
				"name" => 'important_threads_only',
				"title" => $lang->asb_important_threads_only_title,
				"description" => $lang->asb_important_threads_only_desc,
				"optionscode" => 'yesno',
				"value" => '0'
			),
			"xmlhttp_on" => array(
				"sid" => 'NULL',
				"name" => 'xmlhttp_on',
				"title" => $lang->asb_xmlhttp_on_title,
				"description" => $lang->asb_xmlhttp_on_description,
				"optionscode" => 'text',
				"value" => '0'
			)
		),
		"discarded_templates" => array(
			'rand_quote_sidebox',
		),
		"templates" => array(
			array(
				"title" => 'asb_rand_quote_sidebox',
				"template" => <<<EOF
				<tr>
					<td class="tcat">
						{\$thread_title_link}
					</td>
				</tr>
				<tr>
					<td class="trow1">
						<img style="padding: 4px; width: 15%; vertical-align: middle;" src="{\$avatar_filename}" alt="{\$avatar_alt}" title="{\$avatar_alt}"/>&nbsp;<a  style="vertical-align: middle;" href="{\$author_link}" title="{\$plain_text_username}"><span style="font-size: {\$username_font_size}px;">{\$username}</span></a>
					</td>
				</tr>
				<tr>
					<td class="trow2">
						<span style="font-size: {\$message_font_size}px;">{\$new_message}</span>
					</td>
				</tr>{\$read_more}
EOF
			),
			array(
				"title" => 'asb_rand_quote_read_more',
				"template" => <<<EOF

				<tr>
					<td class="tfoot">
						<div style="text-align: center;"><a href="{\$post_link}" title="{\$lang->asb_random_quotes_read_more_title}"><strong>{\$lang->asb_random_quotes_read_more}</strong></a></div>
					</td>
				</tr>
EOF
			)
		)
	);
}

/*
 * asb_rand_quote_build_template()
 *
 * handles display of children of this addon at page load
 *
 * @param - $args - (array) the specific information from the child box
 * @return: (bool) true on success, false on fail/no content
 */
function asb_rand_quote_build_template($args)
{
	extract($args);

	global $$template_var, $lang;

	if(!$lang->asb_addon)
	{
		$lang->load('asb_addon');
	}

	$this_quote = asb_rand_quote_get_quote($settings, $width);
	if($this_quote)
	{
		$$template_var = $this_quote;
		return true;
	}
	else
	{
		// show the table only if there are posts
		$$template_var = <<<EOF
		<tr>
					<td class="trow1">
						{$lang->asb_random_quotes_no_posts}
					</td>
				</tr>
EOF;
		return false;
	}
}

/*
 * asb_rand_quote_xmlhttp()
 *
 * handles display of children of this addon via AJAX
 *
 * @param - $args - (array) the specific information from the child box
 * @return: n/a
 */
function asb_rand_quote_xmlhttp($args)
{
	extract($args);

	// get a quote and return it
	$this_quote = asb_rand_quote_get_quote($settings, $width);
	if($this_quote)
	{
		return $this_quote;
	}
	return 'nochange';
}

/*
 * asb_rand_quote_get_quote()
 *
 * get random quotes
 *
 * @param - $settings (array) individual side box settings passed to the module
 * @param - $width - (int) the width of the column in which the child is positioned
 * @return: (mixed) a (string) containing the HTML side box markup or
 * (bool) false on fail/no content
 */
function asb_rand_quote_get_quote($settings, $width)
{
	global $db, $mybb, $templates, $lang, $theme;

	if(!$lang->asb_addon)
	{
		$lang->load('asb_addon');
	}

	// get forums user cannot view
	$unviewable = get_unviewable_forums(true);
	if($unviewable)
	{
		$unviewwhere = " AND p.fid NOT IN ({$unviewable})";
	}

	if($settings['important_threads_only'])
	{
		$important_threads = ' AND NOT t.sticky=0';
	}

	// build the exclude conditions
	$show['fids'] = asb_build_id_list($settings['forum_show_list'], 'p.fid');
	$show['tids'] = asb_build_id_list($settings['thread_show_list'], 'p.tid');
	$hide['fids'] = asb_build_id_list($settings['forum_hide_list'], 'p.fid');
	$hide['tids'] = asb_build_id_list($settings['thread_hide_list'], 'p.tid');
	$where['show'] = asb_build_SQL_where($show, ' OR ');
	$where['hide'] = asb_build_SQL_where($hide, ' OR ', ' NOT ');
	$query_where = $important_threads . $unviewwhere . asb_build_SQL_where($where, ' AND ', ' AND ');

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

	// if there was 1 . . .
	if($db->num_rows($post_query) == 0)
	{
		return false;
	}

	$rand_post = $db->fetch_array($post_query);

	// build a post parser
	require_once MYBB_ROOT . 'inc/class_parser.php';
	$parser = new postParser;

	// we just need the text and smilies (we'll parse them after we check length)
	$pattern = "|[[\/\!]*?[^\[\]]*?]|si";
	$new_message = asb_strip_url(preg_replace($pattern, '$1', $rand_post['message']));

	// get some dimensions that make sense in relation to column width
	$asb_width = (int) $width;
	$asb_inner_size = $asb_width * .83;
	$avatar_size = (int) ($asb_inner_size / 5);
	$font_size = $asb_width / 4.5;

	$font_size = max(10, min(16, $font_size));
	$username_font_size = (int) ($font_size * .9);
	$title_font_size = (int) ($font_size * .65);
	$message_font_size = (int) $font_size;

	if(strlen($new_message) < $settings['min_length'])
	{
		if($settings['default_text'])
		{
			$new_message = $settings['default_text'];
		}
		else
		{
			// nothing to show
			return false;
		}
	}

	if($settings['max_length'] && strlen($new_message) > $settings['max_length'])
	{
		$new_message = substr($new_message, 0, $settings['max_length']) . ' . . .';
	}

	// set up the user name link so that it displays correctly for the display group of the user
	$plain_text_username = htmlspecialchars_uni($rand_post['username']);
	$username = format_name($plain_text_username, $rand_post['usergroup'], $rand_post['displaygroup']);
	$author_link = get_profile_link($rand_post['uid']);
	$post_link = get_post_link($rand_post['pid'], $rand_post['tid']) . '#pid' . $rand_post['pid'];
	$thread_link = get_thread_link($rand_post['tid']);

	// allow smilies, but kill
	$parser_options = array("allow_smilies" => 1);
	$new_message = str_replace(array('<br />', '/me'), array('', " * {$plain_text_username}"), $parser->parse_message($new_message . ' ', $parser_options));

	// if the user has an avatar then display it, otherwise force the default avatar.
	$avatar_filename = "{$theme['imgdir']}/default_avatar.gif";
	if($rand_post['avatar'] != '')
	{
		$avatar_filename = $rand_post['avatar'];
	}

	$avatar_alt = $lang->sprintf($lang->asb_random_quote_users_profile, $plain_text_username);

	eval("\$read_more = \"" . $templates->get('asb_rand_quote_read_more') . "\";");

	if(my_strlen($rand_post['subject']) > 40)
	{
		$rand_post['subject'] = my_substr($rand_post['subject'], 0, 40) . ' . . .';
	}

	if(substr(strtolower($rand_post['subject']), 0, 3) == 're:')
	{
		$rand_post['subject'] = substr($rand_post['subject'], 3);
	}

	$rand_post['subject'] = htmlspecialchars_uni($parser->parse_badwords($rand_post['subject']));

	$thread_title_link = <<<EOF
<strong><a href="{$thread_link}" title="{$lang->asb_random_quotes_read_more_threadlink_title}"><span style="font-size: {$title_font_size}px;">{$rand_post['subject']}</span></a></strong>
EOF;

	// eval() the template
	eval("\$this_quote = \"" . $templates->get("asb_rand_quote_sidebox") . "\";");
	return $this_quote;
}

?>
