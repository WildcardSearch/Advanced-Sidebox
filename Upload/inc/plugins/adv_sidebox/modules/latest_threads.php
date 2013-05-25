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
	global $lang;

	if(!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}

	return array
	(
		"name"							=>	'Latest Threads',
		"description"					=>	'Lists the latest forum threads',
		"version"						=>	"1.0.3",
		"wrap_content"				=>	true,
		"xmlhttp"						=>	true,
		"settings" => array
			(
				"latest_threads_max"		=> array
				(
					"sid"					=> "NULL",
					"name"				=> "latest_threads_max",
					"title"				=> $lang->adv_sidebox_latest_threads_max_title,
					"description"		=> $lang->adv_sidebox_latest_threads_max,
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


/*
 * latest_threads_asb_build_template()
 *
 * @param - (array) $settings
					individual sidebox settings applied to the module
 * @param - (string) $template_var
					encoded unique side box template variable name
 */
function latest_threads_asb_build_template($settings, $template_var)
{
	global $$template_var, $lang;

	// get the threads (or at least attempt to)
	$all_threads = latest_threads_get_threadlist($settings);

	if($all_threads)
	{
		// if there are threads, show them
		$$template_var = $all_threads;
		return true;
	}
	else
	{
		// if not, show nothing
		$$template_var = '<tr><td class="trow1">' . $lang->adv_sidebox_latest_threads_no_threads . '</td></tr>';
		return false;
	}
}


/*
 * latest_threads_asb_xmlhttp()
 *
 * @param - (int) $dateline
					UNIX datestamp
 * @param - (array) $settings
					individual side box settings passed to the module
 */
function latest_threads_asb_xmlhttp($dateline, $settings)
{
	global $db;

	// do a quick check to make sure we don't waste execution
	$query = $db->simple_select('posts', '*', "dateline > {$dateline}");

	if($db->num_rows($query) > 0)
	{
		$all_threads = latest_threads_get_threadlist($settings);

		if($all_threads)
		{
			return $all_threads;
		}
	}
	return 'nochange';
}

/*
 * latest_threads_get_threadlist()
 *
 * @param - (array) $settings
					individual side box settings passed to the module
 */
function latest_threads_get_threadlist($settings)
{
	global $db, $mybb, $templates, $lang, $cache, $threadlist, $gotounread, $theme;

	// load custom language phrases
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

	$threads = array();

	if($mybb->user['uid'] == 0)
	{
		$query = $db->query
		("
			SELECT
				fid
			FROM
				" . TABLE_PREFIX . "forums
			WHERE
				active != 0
			ORDER BY
				pid, disporder
		");

		$forumsread = my_unserialize($mybb->cookies['mybb']['forumread']);
	}
	else
	{
		$query = $db->query
		("
			SELECT
				f.fid, fr.dateline AS lastread
			FROM
				" . TABLE_PREFIX . "forums f
			LEFT JOIN
				" . TABLE_PREFIX . "forumsread fr ON (fr.fid=f.fid AND fr.uid='{$mybb->user['uid']}')
			WHERE
				f.active != 0
			ORDER BY
				pid, disporder
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
	$firstid = ' id="latest_threads_firstrow"';

	// Query for the latest forum discussions
	$query = $db->query
	("
		SELECT
			t.*, u.username,
			lp.usergroup, lp.displaygroup
		FROM
			" . TABLE_PREFIX . "threads t
		LEFT JOIN
			" . TABLE_PREFIX . "users u ON (u.uid=t.uid)
		LEFT JOIN
			" . TABLE_PREFIX . "users lp ON (lp.uid=t.lastposteruid)
		WHERE
			1=1 $unviewwhere AND t.visible='1' AND t.closed NOT LIKE 'moved|%'
		ORDER BY
			t.lastpost DESC
		LIMIT
			0, " . (int) $settings['latest_threads_max']['value']
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

			// make sure we can view this thread
			if($forumpermissions[$thread['fid']]['canview'] == 0 || $forumpermissions[$thread['fid']]['canviewthreads'] == 0 || $forumpermissions[$thread['fid']]['canonlyviewownthreads'] == 1 && $thread['uid'] != $mybb->user['uid'])
			{
				continue;
			}

			$lastpostdate = my_date($mybb->settings['dateformat'], $thread['lastpost']);
			$lastposttime = my_date($mybb->settings['timeformat'], $thread['lastpost']);

			// don't link to guest's profiles (they have no profile).
			if($thread['lastposteruid'] == 0)
			{
				$lastposterlink = $thread['lastposter'];
			}
			else
			{
				$last_poster_name = format_name($thread['lastposter'], $thread['usergroup'], $thread['displaygroup']);
				$lastposterlink = build_profile_link($last_poster_name, $thread['lastposteruid']);
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
			eval("\$all_threads = \"" . $templates->get("adv_sidebox_latest_threads") . "\";");
			return $all_threads;
		}
	}
	else
	{
		// no content
		return false;
	}
}

?>
