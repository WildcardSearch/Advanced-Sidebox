<?php
/*
 * Advanced Sidebox Module
 *
 * Welcome
 *
 * This module is part of the Advanced Sidebox  default module pack. It can be installed and uninstalled like any other module. Even though it is included in the original installation, it is not necessary and can be completely removed by deleting the containing folder (ie modules/thisfolder).
 *
 * If you delete this folder from the installation pack this module will never be installed (and everything should work just fine without it). Don't worry, if you decide you want it back you can always download them again. The best move would be to install the entire package and try them out. Then be sure that the packages you don't want are uninstalled and then delete those folders from your server.
 */

// Include a check for Advanced Sidebox
if(!defined("IN_MYBB") || !defined("ADV_SIDEBOX"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

function welcome_box_asb_info()
{
	return array
	(
		"name"				=>	'Welcome',
		"description"		=>	'Login for guest, info for member',
		"wrap_content"	=>	true,
		"version"			=>	"1",
		"templates"					=>	array
													(
														array(
															"title" => "adv_sidebox_welcome",
															"template" => "
	<tr>
		<td class=\"trow1\">
			{\$welcometext}
		</td>
	</tr>
															",
															"sid" => -1
														),
														array
														(
															"title" => "adv_sidebox_welcome_membertext",
															"template" => "
			<span style=\"float: right;\"><img src=\"{\$mybb->settings[\'bburl\']}/{\$mybb->user[\'avatar\']}\" height=\"50\" width=\"50\" alt=\"{\$mybb->user[\'username\']}\'s avatar\"/>&nbsp;</span><span class=\"smalltext\"><em>{\$lang->member_welcome_lastvisit}</em> {\$lastvisit}<br />
			{\$lang->since_then}<br />
			<strong>&raquo;</strong> {\$lang->new_announcements}<br />
			<strong>&raquo;</strong> {\$lang->new_threads}<br />
			<strong>&raquo;</strong> {\$lang->new_posts}<br /><br />
			<a href=\"{\$mybb->settings[\'bburl\']}/search.php?action=getnew\">{\$lang->view_new}</a><br /><a href=\"{\$mybb->settings[\'bburl\']}/search.php?action=getdaily\">{\$lang->view_todays}</a>
			</span>
														",
															"sid" => -1
														),
														array
														(
															"title" => "adv_sidebox_welcome_guesttext",
															"template" => "
			<span class=\"smalltext\">{\$lang->guest_welcome_registration}</span><br />
			<br />
			<form method=\"post\" action=\"{\$mybb->settings[\'bburl\']}/member.php\"><input type=\"hidden\" name=\"action\" value=\"do_login\" />
				<input type=\"hidden\" name=\"url\" value=\"{\$portal_url}\" />
				{\$username}<br />&nbsp;&nbsp;<input type=\"text\" class=\"textbox\" name=\"username\" value=\"\" /><br /><br />
				{\$lang->password}<br />&nbsp;&nbsp;<input type=\"password\" class=\"textbox\" name=\"password\" value=\"\" /><br /><br />
				<label title=\"{\$lang->remember_me_desc}\"><input type=\"checkbox\" class=\"checkbox\" name=\"remember\" value=\"yes\" /> {\$lang->remember_me}</label><br /><br />
				<br /><input type=\"submit\" class=\"button\" name=\"loginsubmit\" value=\"{\$lang->login}\" />
			</form>
															",
															"sid" => -1
														)
													)
	);
}

function welcome_box_asb_build_template($settings, $template_var)
{
	// don't forget to declare your variable! will not work without this
	global $$template_var; // <-- important!

	global $db, $mybb, $templates, $lang, $lastvisit;

	$portal_url='member.php';

	// Load global and custom language phrases
	if (!$lang->portal)
	{
		$lang->load('portal');
	}
	if (!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}

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
	eval("\$" . $template_var . " = \"" . $templates->get("adv_sidebox_welcome") . "\";");
	if($mybb->user['uid'] == 0)
	{
		$mybb->user['username'] = "";
	}
}

?>
