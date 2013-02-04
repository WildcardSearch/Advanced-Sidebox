<?php
/*
 * Advanced Sidebox Module
 *
 * Latest Threads
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

function latest_threads_asb_info()
{
	return array
	(
		"name"							=>	'Latest Threads',
		"description"					=>	'Lists the latest forum threads',
		"version"						=>	"2",
		"wrap_content"				=>	true,
		"discarded_settings"	=>	array
													(
														"adv_sidebox_latest_threads_max"
													),
		"settings"						=>	array
													(
														"latest_threads_max"		=> array
														(
															"sid"					=> "NULL",
															"name"				=> "latest_threads_max",
															"title"				=> "Thread Limit",
															"description"		=> "maximum number of threads to display",
															"optionscode"	=> "text",
															"value"				=> '20'
														)
													),
		"templates"					=>	array
													(
														array
														(
															"title" 			=> "adv_sidebox_latest_threads",
															"template" 	=> "{\$threadlist}",
															"sid"				=>	-1
														),
														array
														(
															"title" => "adv_sidebox_latest_threads_thread",
															"template" => "
	<tr>
		<td class=\"{\$altbg}\">
			{\$gotounread}<a href=\"{\$mybb->settings[\'bburl\']}/{\$thread[\'threadlink\']}\" title=\"{\$thread[\'subject\']}\"><strong>{\$thread[\'subject\']}</strong></a>
			<span class=\"smalltext\"><br />
				<a href=\"{\$thread[\'lastpostlink\']}\" title=\"{\$lang->adv_sidebox_latest_threads_lastpost}\">{\$lang->adv_sidebox_latest_threads_lastpost}</a> {\$lastposterlink}<br />
				{\$lastpostdate} {\$lastposttime}<br />
				<strong>&raquo; </strong>{\$lang->adv_sidebox_latest_threads_replies} {\$thread[\'replies\']}<br />
				<strong>&raquo; </strong>{\$lang->adv_sidebox_latest_threads_views} {\$thread[\'views\']}
			</span>
		</td>
	</tr>
															",
															"sid"				=>	-1
														),
														array
														(
															"title" => "adv_sidebox_latest_threads_gotounread",
															"template" => "<a href=\"{\$thread[\'newpostlink\']}\"><img src=\"{\$theme[\'imgdir\']}/jump.gif\" alt=\"{\$lang->adv_sidebox_gotounread}\" title=\"{\$lang->adv_sidebox_gotounread}\" /></a>",
															"sid"				=>	-1
														)
													)
	);
}

function latest_threads_asb_build_template($settings, $template_var)
{
	global $$template_var;
	global $db, $mybb, $templates, $lang, $cache, $threadlist, $gotounread, $theme;

	// Load custom language phrases
	if(!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}

	// get forums user cannot view
	$unviewable = get_unviewable_forums(true);
	if($unviewable)
	{
		$unviewwhere = "AND fid NOT IN ($unviewable)";
	}

	// Read some values we will be using
	$forumcache = $cache->read("forums");

	$threads = array();

	if($mybb->user['uid'] == 0)
	{
		// Build a forum cache.
		$query = $db->query("
			SELECT fid
			FROM ".TABLE_PREFIX."forums
			WHERE active != 0
			ORDER BY pid, disporder
		");

		$forumsread = my_unserialize($mybb->cookies['mybb']['forumread']);
	}
	else
	{
		// Build a forum cache.
		$query = $db->query("
			SELECT f.fid, fr.dateline AS lastread
			FROM ".TABLE_PREFIX."forums f
			LEFT JOIN ".TABLE_PREFIX."forumsread fr ON (fr.fid=f.fid AND fr.uid='{$mybb->user['uid']}')
			WHERE f.active != 0
			ORDER BY pid, disporder
		");
	}

	while($forum = $db->fetch_array($query))
	{
		if($mybb->user['uid'] == 0)
		{
			if($forumsread[$forum['fid']])
			{
				$forum['lastread'] = $forumsread[$forum['fid']];
			}
		}
		$readforums[$forum['fid']] = $forum['lastread'];
	}

	// Build a post parser
	require_once MYBB_ROOT."inc/class_parser.php";
	$parser = new postParser;

	$altbg = alt_trow();
	$maxtitlelen = 48;
	$threadlist = '';

	// Query for the latest forum discussions
	$query = $db->query("
		SELECT t.*, u.username
		FROM " . TABLE_PREFIX . "threads t
		LEFT JOIN " . TABLE_PREFIX . "users u ON (u.uid=t.uid)
		WHERE 1=1 $unviewwhere AND t.visible='1' AND t.closed NOT LIKE 'moved|%'
		ORDER BY t.lastpost DESC
		LIMIT 0, " . (int) $settings['latest_threads_max']['value']
	);

	if($db->num_rows($query) > 0)
	{
		$thread_cache = array();

		while($thread = $db->fetch_array($query))
		{
			$thread_cache[$thread['tid']] = $thread;
		}

		$thread_ids = implode(",", array_keys($thread_cache));

		// Fetch the read threads.
		if($mybb->user['uid'] && $mybb->settings['threadreadcut'] > 0)
		{
			$query = $db->simple_select("threadsread", "tid,dateline", "uid='".$mybb->user['uid']."' AND tid IN(" . $thread_ids . ")");
			while($readthread = $db->fetch_array($query))
			{
				$thread_cache[$readthread['tid']]['lastread'] = $readthread['dateline'];
			}
		}

		foreach($thread_cache as $thread)
		{
			$forumpermissions[$thread['fid']] = forum_permissions($thread['fid']);

			// Make sure we can view this thread
			if($forumpermissions[$thread['fid']]['canview'] == 0 || $forumpermissions[$thread['fid']]['canviewthreads'] == 0 || $forumpermissions[$thread['fid']]['canonlyviewownthreads'] == 1 && $thread['uid'] != $mybb->user['uid'])
			{
				continue;
			}

			$lastpostdate = my_date($mybb->settings['dateformat'], $thread['lastpost']);
			$lastposttime = my_date($mybb->settings['timeformat'], $thread['lastpost']);

			// Don't link to guest's profiles (they have no profile).
			if($thread['lastposteruid'] == 0)
			{
				$lastposterlink = $thread['lastposter'];
			}
			else
			{
				$lastposterlink = build_profile_link($thread['lastposter'], $thread['lastposteruid']);
			}

			if(my_strlen($thread['subject']) > $maxtitlelen)
			{
				$thread['subject'] = my_substr($thread['subject'], 0, $maxtitlelen) . "...";
			}

			$thread['subject'] = htmlspecialchars_uni($parser->parse_badwords($thread['subject']));
			$thread['threadlink'] = get_thread_link($thread['tid']);
			$thread['lastpostlink'] = get_thread_link($thread['tid'], 0, "lastpost");

			$gotounread = '';
			$last_read = 0;

			if($mybb->settings['threadreadcut'] > 0 && $mybb->user['uid'])
			{
				$forum_read = $readforums[$thread['fid']];

				$read_cutoff = TIME_NOW-$mybb->settings['threadreadcut']*60*60*24;
				if($forum_read == 0 || $forum_read < $read_cutoff)
				{
					$forum_read = $read_cutoff;
				}
			}
			else
			{
				$forum_read = $forumsread[$thread['fid']];
			}

			if($mybb->settings['threadreadcut'] > 0 && $mybb->user['uid'] && $thread['lastpost'] > $forum_read)
			{
				if($thread['lastread'])
				{
					$last_read = $thread['lastread'];
				}
				else
				{
					$last_read = $read_cutoff;
				}
			}
			else
			{
				$last_read = my_get_array_cookie("threadread", $thread['tid']);
			}

			if($forum_read > $last_read)
			{
				$last_read = $forum_read;
			}

			if($thread['lastpost'] > $last_read && $last_read)
			{
				$thread['newpostlink'] = get_thread_link($thread['tid'], 0, "newpost");
				eval("\$gotounread = \"" . $templates->get("adv_sidebox_latest_threads_gotounread") . "\";");
				$unreadpost = 1;
			}

			eval("\$threadlist .= \"".$templates->get("adv_sidebox_latest_threads_thread")."\";");
			$altbg = alt_trow();
		}

		if($threadlist)
		{
			// Show the table only if there are threads
			eval("\$" . $template_var . " = \"" . $templates->get("adv_sidebox_latest_threads") . "\";");
		}
	}
	else
	{
		// Show the table only if there are threads
		eval("\$" . $template_var . " = \"<tr><td class=\\\"trow1\\\">" . $lang->adv_sidebox_latest_threads_no_threads . "</td></tr>\";");
	}
}

?>
