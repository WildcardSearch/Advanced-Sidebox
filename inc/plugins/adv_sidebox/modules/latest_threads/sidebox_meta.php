<?php
/*
 * Advanced Sidebox Module
 *
 * Latest Threads (meta)
 *
 * This module is part of the Advanced Sidebox  default module pack. It can be installed and uninstalled like any other module. Even though it is included in the original installation, it is not necessary and can be completely removed by deleting the containing folder (ie modules/thisfolder).
 *
 * If you delete this folder from the installation pack this module will never be installed (and everything should work just fine without it). Don't worry, if you decide you want it back you can always download them again. The best move would be to install the entire package and try them out. Then be sure that the packages you don't want are uninstalled and then delete those folders from your server.
 *
 * This is a default portal box. Any changes from portal.php (MyBB 1.6.9) will be noted here.
 */
 
// Include a check for Advanced Sidebox
if(!defined("IN_MYBB") || !defined("ADV_SIDEBOX"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

function latest_threads_add_type(&$box_types)
{
	 $box_types['{$latest_threads}'] = 'Latest Threads';
}

function latest_threads_build_template(&$box_types)
{
	global $latest_threads; // <-- important!
	
	global $db, $mybb, $templates, $lang;
	
	// Load global and custom language phrases
	if (!$lang->portal)
	{
		$lang->load('portal');
	}
	if (!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}
	
	if($box_types['{$latest_threads}'])
	{
		// get forums user cannot view
		$unviewable = get_unviewable_forums(true);
		if($unviewable)
		{
			$unviewwhere = "AND fid NOT IN ($unviewable)";
		}
		
		// Build a post parser
		require_once MYBB_ROOT."inc/class_parser.php";
		$parser = new postParser;
		
		$altbg = alt_trow();
		$maxtitlelen = 48;
		$threadlist = '';
		$threadlength = 0;

		// Query for the latest forum discussions
		$query = $db->query("
			SELECT t.*, u.username
			FROM " . TABLE_PREFIX . "threads t
			LEFT JOIN " . TABLE_PREFIX . "users u ON (u.uid=t.uid)
			WHERE 1=1 $unviewwhere AND t.visible='1' AND t.closed NOT LIKE 'moved|%'
			ORDER BY t.lastpost DESC
			LIMIT 0, " . $mybb->settings['portal_showdiscussionsnum']
		);
		while($thread = $db->fetch_array($query))
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
			$thread['newpostlink'] = get_thread_link($thread['tid'], 0, "newpost");
			eval("\$threadlist .= \"".$templates->get("adv_sidebox_latest_threads_thread")."\";");
			$altbg = alt_trow();
		}

		if($threadlist)
		{
			// Show the table only if there are threads
			eval("\$latest_threads = \"" . $templates->get("adv_sidebox_latest_threads") . "\";");
		}
	}
}

?>