<?php
/*
 * Advanced Sidebox Module
 *
 * Recent Posts
 *
 * This module is part of the Advanced Sidebox default module pack. It can be installed and uninstalled like any other module. Even though it is included in the original installation, it is not necessary and can be completely removed by deleting the containing folder (ie modules/thisfolder).
 *
 * If you delete this folder from the installation pack this module will never be installed (and everything should work just fine without it). Don't worry, if you decide you want it back you can always download them again. The best move would be to install the entire package and try them out. Then be sure that the packages you don't want are uninstalled and then delete those folders from your server.
 *
 */

// Include a check for Advanced Sidebox
if(!defined("IN_MYBB") || !defined("ADV_SIDEBOX"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

function recent_posts_asb_info()
{
	global $lang;

	if(!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}

	return array
	(
		"name"							=>	'Recent Posts',
		"description"					=>	'Lists the latest posts',
		"version"						=>	"1.2.2",
		"wrap_content"				=>	true,
		"xmlhttp"						=>	true,
		"settings" => array
			(
				"max_posts"		=> array
				(
					"sid"					=> "NULL",
					"name"				=> "max_posts",
					"title"				=> $lang->adv_sidebox_recent_posts_max_title,
					"description"		=> $lang->adv_sidebox_recent_posts_max_description,
					"optionscode"	=> "text",
					"value"				=> '5'
				),
				"max_length"		=> array
				(
					"sid"					=> "NULL",
					"name"				=> "max_length",
					"title"				=> $lang->adv_sidebox_recent_posts_max_length_title,
					"description"		=> $lang->adv_sidebox_recent_posts_max_length_description,
					"optionscode"	=> "text",
					"value"				=> '20'
				),
				"xmlhttp_on" => array
				(
					"sid"					=> "NULL",
					"name"				=> "xmlhttp_on",
					"title"				=> $lang->adv_sidebox_xmlhttp_on_title,
					"description"		=> $lang->adv_sidebox_xmlhttp_on_description,
					"optionscode"	=> "text",
					"value"				=> '0'
				)
			),
		"templates" => array
			(
				array
				(
					"title" 			=> "adv_sidebox_recent_posts_post",
					"template" 	=> "
					<tr>
						<td style=\"text-align: center;\" class=\"tcat\">
							<a href=\"{\$mybb->settings[\'bburl\']}/{\$post[\'link\']}\" title=\"{\$post[\'subject\']}\">{\$post[\'subject\']}</a></td>
					</tr>
					<tr>
						<td class=\"{\$altbg}\">{\$post_excerpt}<span style=\"position: relative; float: right;\">{\$post_author} &mdash; {\$lastposttime}</span></td>
					</tr>",
					"sid"				=>	-1
				)
		)
	);
}


/*
 * recent_posts_asb_build_template()
 *
 * @param - (array) $settings
					individual sidebox settings applied to the module
 * @param - (string) $template_var
					encoded unique side box template variable name
 */
function recent_posts_asb_build_template($settings, $template_var)
{
	global $$template_var, $lang;

	// get the posts (or at least attempt to)
	$all_posts = recent_posts_get_postlist($settings);

	if($all_posts)
	{
		// if there are posts, show them
		$$template_var = $all_posts;
		return true;
	}
	else
	{
		// if not, show nothing
		$$template_var = '<tr><td class="trow1">' . $lang->adv_sidebox_recent_posts_no_posts . '</td></tr>';
		return false;
	}
}


/*
 * recent_posts_asb_xmlhttp()
 *
 * @param - (int) $dateline
					UNIX datestamp
 * @param - (array) $settings
					individual side box settings passed to the module
 */
function recent_posts_asb_xmlhttp($dateline, $settings)
{
	global $db;

	// do a quick check to make sure we don't waste execution
	$query = $db->simple_select('posts', '*', "dateline > {$dateline}");

	if($db->num_rows($query) > 0)
	{
		$all_posts = recent_posts_get_postlist($settings);

		if($all_posts)
		{
			return $all_posts;
		}
	}
	return 'nochange';
}

/*
 * recent_posts_get_postlist()
 *
 * @param - (array) $settings
					individual side box settings passed to the module
 */
function recent_posts_get_postlist($settings)
{
	global $db, $mybb, $templates, $lang, $cache, $postlist, $gotounread, $theme;

	// load custom language phrases
	if(!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}

	// get forums user cannot view
	$unviewable = get_unviewable_forums(true);
	if($unviewable)
	{
		$unviewwhere = "AND p.fid NOT IN ($unviewable)";
	}

	// Build a post parser
	require_once MYBB_ROOT."inc/class_parser.php";
	$parser = new postParser;

	$parser_options = array(
		'allow_html' => 1,
		'allow_mycode' => 1,
		'allow_imgcode' => 1,
		'allow_smilies' => 0,
		'allow_videocode' => 1,
		'filter_badwords' => 1,
		'me_username' => $user['username']
	);

	$altbg = alt_trow();
	$maxtitlelen = 48;
	$postlist = '';

	// Query for the latest forum discussions
	$query = $db->query
	("
		SELECT
			p.tid, p.pid, p.message, p.fid, p.dateline, p.subject,
			u.username, u.uid, u.displaygroup, u.usergroup
		FROM
			" . TABLE_PREFIX . "posts p
		LEFT JOIN
			" . TABLE_PREFIX . "users u ON (u.uid=p.uid)
		WHERE
			1=1 {$unviewwhere} AND p.visible='1'
		ORDER BY
			p.dateline DESC
		LIMIT
			0, " . (int) $settings['max_posts']['value']
	);

	if($db->num_rows($query) > 0)
	{
		$post_cache = array();

		while($post = $db->fetch_array($query))
		{
			$post_cache[$post['pid']] = $post;
		}

		foreach($post_cache as $post)
		{
			$forumpermissions[$post['fid']] = forum_permissions($post['fid']);

			// make sure we can view this post
			if($forumpermissions[$post['fid']]['canview'] == 0 || $forumpermissions[$post['fid']]['canviewthreads'] == 0 || $forumpermissions[$post['fid']]['canonlyviewownthreads'] == 1 && $post['uid'] != $mybb->user['uid'])
			{
				continue;
			}

			$lastposttime = my_date($mybb->settings['timeformat'], $post['dateline']);

			// don't link to guest's profiles (they have no profile).
			if($post['uid'] == 0)
			{
				$post_author = $post['username'];
			}
			else
			{
				$post_author_name = format_name($post['username'], $post['usergroup'], $post['displaygroup']);
				$post_author = build_profile_link($post_author_name, $post['uid']);
			}

			if(my_strlen($post['subject']) > $maxtitlelen)
			{
				$post['subject'] = my_substr($post['subject'], 0, $maxtitlelen) . "...";
			}

			if(substr(strtolower($post['subject']), 0, 3) == 're:')
			{
				$post['subject'] = substr($post['subject'], 3);
			}

			$post['subject'] = htmlspecialchars_uni($parser->parse_badwords($post['subject']));
			$post['link'] = get_thread_link($post['tid']) . "&amp;pid={$post['pid']}#pid{$post['pid']}";

			$gotounread = '';
			$last_read = 0;

			if($mybb->settings['threadreadcut'] > 0 && $mybb->user['uid'])
			{
				$forum_read = $readforums[$post['fid']];

				$read_cutoff = TIME_NOW - $mybb->settings['threadreadcut'] * 60 * 60 * 24;
				if($forum_read == 0 || $forum_read < $read_cutoff)
				{
					$forum_read = $read_cutoff;
				}
			}
			else
			{
				$forum_read = $forumsread[$post['fid']];
			}

			if($mybb->settings['threadreadcut'] > 0 && $mybb->user['uid'] && $post['dateline'] > $forum_read)
			{
				if($post['lastread'])
				{
					$last_read = $post['lastread'];
				}
				else
				{
					$last_read = $read_cutoff;
				}
			}
			else
			{
				$last_read = my_get_array_cookie("threadread", $post['tid']);
			}

			if($forum_read > $last_read)
			{
				$last_read = $forum_read;
			}

			if($post['dateline'] > $last_read && $last_read)
			{
				$post['subject'] = "<strong>{$post['subject']}</strong>";
				$unreadpost = 1;
			}

			$post_excerpt = $parser->text_parse_message(adv_sidebox_strip_url(strip_tags($parser->parse_message(adv_sidebox_strip_quotes($post['message']), $parser_options))));

			if(strlen($post_excerpt) > $settings['max_length']['value'])
			{
				$post_excerpt = substr($post_excerpt, 0, $settings['max_length']['value']) . ' . . .';
			}

			eval("\$postlist .= \"" . $templates->get("adv_sidebox_recent_posts_post") . "\";");
			$altbg = alt_trow();
		}

		if($postlist)
		{
			return $postlist;
		}
	}
	else
	{
		// no content
		return false;
	}
}

?>
