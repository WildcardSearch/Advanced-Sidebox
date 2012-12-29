<?php
/*
 * Advanced Sidebox Module
 *
 * Welcome (meta)
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

/*
 * This function is required. It is used by acp_functions to add and describe your new sidebox.
 */
function welcome_add_type(&$box_types)
{
	/*
	 * just add your template variable to the $box_types array
	 *
	 * $box_types[''] <-- 	enter your template variable. it must be the same as the name of your add-on module enclosed in curly brackets {} and with a $
	 * = ''; <-- enter the description/name of your add-on.
	 */
	 $box_types['{$welcome}'] = 'Welcome';
}

/*
 * This function is required. It is used by adv_sidebox.php to display the custom content in your sidebox.
 */
function welcome_build_template(&$box_types)
{
	// don't forget to declare your variable! will not work without this
	global $welcome; // <-- important!
	
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
	
	/*
	 * check if the custom box type has been used by admin
	 *
	 * this is important because if the box hasn't been used it would be a waste to go any further
	*/
	if($box_types['{$welcome}'])
	{
		if($mybb->user['uid'] != 0)
		{
			// Get number of new posts, threads, announcements
			$query = $db->simple_select("posts", "COUNT(pid) AS newposts", "visible=1 AND dateline>'" . $mybb->user['lastvisit'] . "' $unviewwhere");
			$newposts = $db->fetch_field($query, "newposts");
			if($newposts)
			{
				// If there aren't any new posts, there is no point in wasting two more queries
				$query = $db->simple_select("threads", "COUNT(tid) AS newthreads", "visible=1 AND dateline>'" . $mybb->user['lastvisit'] . "' $unviewwhere");
				$newthreads = $db->fetch_field($query, "newthreads");

				$announcementsfids = explode(',', $mybb->settings['portal_announcementsfid']);
				if(is_array($announcementsfids))
				{
					foreach($announcementsfids as $fid)
					{
						$fid_array[] = intval($fid);	
					}
					
					$announcementsfids = implode(',', $fid_array);
					$query = $db->simple_select("threads", "COUNT(tid) AS newann", "visible=1 AND dateline>'" . $mybb->user['lastvisit'] . "' AND fid IN (" . $announcementsfids . ") $unviewwhere");
					$newann = $db->fetch_field($query, "newann");
				}

				if(!$newthreads)
				{
					$newthreads = 0;
				}

				if(!$newann)
				{
					$newann = 0;
				}
			}
			else
			{
				$newposts = 0;
				$newthreads = 0;
				$newann = 0;
			}

			// Make the text
			if($newann == 1)
			{
				$lang->new_announcements = $lang->new_announcement;
			}
			else
			{
				$lang->new_announcements = $lang->sprintf($lang->new_announcements, $newann);
			}
			if($newthreads == 1)
			{
				$lang->new_threads = $lang->new_thread;
			}
			else
			{
				$lang->new_threads = $lang->sprintf($lang->new_threads, $newthreads);
			}
			if($newposts == 1)
			{
				$lang->new_posts = $lang->new_post;
			}
			else
			{
				$lang->new_posts = $lang->sprintf($lang->new_posts, $newposts);
			}
			eval("\$welcometext = \"".$templates->get("adv_sidebox_welcome_membertext")."\";");

		}
		else
		{
			$lang->guest_welcome_registration = $lang->sprintf($lang->guest_welcome_registration, $mybb->settings['bburl'] . '/member.php?action=register');
			$mybb->user['username'] = $lang->guest;
			switch($mybb->settings['username_method'])
			{
				case 0:
					$username = $lang->username;
					break;
				case 1:
					$username = $lang->username1;
					break;
				case 2:
					$username = $lang->username2;
					break;
				default:
					$username = $lang->username;
					break;
			}
			eval("\$welcometext = \"" . $templates->get("portal_welcome_guesttext") . "\";");
		}
		$lang->welcome = $lang->sprintf($lang->welcome, $mybb->user['username']);
		eval("\$welcome = \"" . $templates->get("adv_sidebox_welcome") . "\";");
		if($mybb->user['uid'] == 0)
		{
			$mybb->user['username'] = "";
		}
	}
}

?>