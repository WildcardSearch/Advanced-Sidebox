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
function welcome_info()
{
	return array
	(
		"name"				=>	'Welcome',
		"description"		=>	'login for guest, info for member',
		"stereo"			=>	false
	);
}

function welcome_is_installed()
{
	global $db;
	
	// works just like a plugin
	$query = $db->simple_select('templates', 'title', "title='adv_sidebox_welcome'");
	return $db->num_rows($query);
}

/*
 * This function is required. Make your mods here.
 */
function welcome_install()
{
	global $db;
	
	// the parent template for the welcome box
	$template_1 = array(
        "title" => "adv_sidebox_welcome",
        "template" => "<table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\">
	<tr>
		<td class=\"thead\"><strong>{\$lang->welcome}</strong></td>
	</tr>
	<tr>
		<td class=\"trow1\">
			{\$welcometext}
		</td>
	</tr>
</table><br />",
        "sid" => -1
    );
	$db->insert_query("templates", $template_1);
	
	// a child template of the welcome box (member)
	$template_2 = array(
        "title" => "adv_sidebox_welcome_membertext",
        "template" => "<span class=\"smalltext\"><em>{\$lang->member_welcome_lastvisit}</em> {\$lastvisit}<br />
{\$lang->since_then}<br />
<strong>&raquo;</strong> {\$lang->new_announcements}<br />
<strong>&raquo;</strong> {\$lang->new_threads}<br />
<strong>&raquo;</strong> {\$lang->new_posts}<br /><br />
<a href=\"{\$mybb->settings[\'bburl\']}/search.php?action=getnew\">{\$lang->view_new}</a><br /><a href=\"{\$mybb->settings[\'bburl\']}/search.php?action=getdaily\">{\$lang->view_todays}</a>
</span>",
        "sid" => -1
    );
	$db->insert_query("templates", $template_2);
	
	// a child template of the welcome box (guest state)
	$template_3 = array(
        "title" => "adv_sidebox_welcome_guesttext",
        "template" => "<span class=\"smalltext\">{\$lang->guest_welcome_registration}</span><br />
<br />
<form method=\"post\" action=\"{\$mybb->settings[\'bburl\']}/member.php\"><input type=\"hidden\" name=\"action\" value=\"do_login\" />
	<input type=\"hidden\" name=\"url\" value=\"{\$portal_url}\" />
	{\$username}<br />&nbsp;&nbsp;<input type=\"text\" class=\"textbox\" name=\"username\" value=\"\" /><br /><br />
	{\$lang->password}<br />&nbsp;&nbsp;<input type=\"password\" class=\"textbox\" name=\"password\" value=\"\" /><br /><br />
	<label title=\"{\$lang->remember_me_desc}\"><input type=\"checkbox\" class=\"checkbox\" name=\"remember\" value=\"yes\" /> {\$lang->remember_me}</label><br /><br />
	<br /><input type=\"submit\" class=\"button\" name=\"loginsubmit\" value=\"{\$lang->login}\" />
</form>",
        "sid" => -1
    );
	$db->insert_query("templates", $template_3);
}

/*
 * This function is required. Clean up after yourself.
 */
function welcome_uninstall()
{
	global $db;
	
	// delete all the boxes of this custom type and the template as well
	$db->query("DELETE FROM " . TABLE_PREFIX . "sideboxes WHERE box_type='" . $db->escape_string('welcome') . "'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='adv_sidebox_welcome'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='adv_sidebox_welcome_membertext'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='adv_sidebox_welcome_guesttext'");
}

/*
 * This function is required. It is used by adv_sidebox.php to display the custom content in your sidebox.
 */
function welcome_build_template()
{
	// don't forget to declare your variable! will not work without this
	global $welcome; // <-- important!
	
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
	eval("\$welcome = \"" . $templates->get("adv_sidebox_welcome") . "\";");
	if($mybb->user['uid'] == 0)
	{
		$mybb->user['username'] = "";
	}
}

?>