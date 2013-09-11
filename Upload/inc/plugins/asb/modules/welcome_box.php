<?php
/*
 * Plug-in Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2013 WildcardSearch
 * http://www.wildcardsworld.com
 *
 * ASB default module
 */

// Include a check for Advanced Sidebox
if(!defined("IN_MYBB") || !defined("IN_ASB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

function asb_welcome_box_info()
{
	global $lang;

	if(!$lang->asb_addon)
	{
		$lang->load('asb_addon');
	}

 	return array
	(
		"title" => $lang->asb_welcome,
		"description" => $lang->asb_welcome_desc,
		"wrap_content" => true,
		"version" => "1.3.1",
		"templates" => array
		(
			array
			(
				"title" => "asb_welcome",
				"template" => <<<EOF
				<tr>
					<td class=\"trow1\">
						{\$welcometext}
					</td>
				</tr>
EOF
				,
				"sid" => -1
			),
			array
			(
				"title" => "asb_welcome_membertext",
				"template" => <<<EOF
				{\$user_avatar}<span class="smalltext"><em>{\$lang->asb_welcome_member_welcome_lastvisit}:</em> {\$lastvisit}<br />
				{\$lang->since_then}<br />
				<strong>&raquo;</strong> {\$lang->asb_welcome_new_announcements}<br />
				<strong>&raquo;</strong> {\$lang->asb_welcome_new_threads}<br />
				<strong>&raquo;</strong> {\$lang->asb_welcome_new_posts}<br /><br />
				<a href="{\$mybb->settings[\'bburl\']}/search.php?action=getnew">{\$lang->asb_welcome_view_new}</a><br /><a href="{\$mybb->settings[\'bburl\']}/search.php?action=getdaily">{\$lang->asb_welcome_view_todays}</a>
				</span>
EOF
				,
				"sid" => -1
			),
			array
			(
				"title" => "asb_welcome_guesttext",
				"template" => <<<EOF
				<span class="smalltext">{\$lang->asb_welcome_guest_welcome_registration}</span><br />
				<br />
				<form method="post" action="{\$mybb->settings[\'bburl\']}/member.php"><input type="hidden" name="action" value="do_login"/>
					<input type="hidden" name="url" value="member.php"/>
					{\$username}<br />&nbsp;&nbsp;<input style="width: 95%;" type="text" class="textbox" name="username"/><br /><br />
					{\$lang->asb_welcome_password}<br />&nbsp;&nbsp;<input style="width: 95%;" type="password" class="textbox" name="password"/><br /><br />
					<label title="{\$lang->asb_welcome_remember_me_desc}"><input type="checkbox" class="checkbox" name="remember" value="yes"/> {\$lang->asb_welcome_remember_me}</label><br /><br />
					<input type="submit" class="button" name="loginsubmit" value="{\$lang->login}"/>
				</form>
EOF
				,
				"sid" => -1
			)
		)
	);
}

function asb_welcome_box_build_template($args)
{
	foreach(array('settings', 'template_var', 'width') as $key)
	{
		$$key = $args[$key];
	}
	global $$template_var; // <-- important!

	global $db, $mybb, $templates, $lang, $lastvisit, $theme, $user_avatar;

	// Load global and custom language phrases
	if(!$lang->asb_addon)
	{
		$lang->load('asb_addon');
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
			$lang->asb_welcome_new_announcements = $lang->asb_welcome_new_announcement;
		}
		else
		{
			$lang->asb_welcome_new_announcements = $lang->sprintf($lang->asb_welcome_new_announcements, $newann ? $newann : 'no');
		}
		if($newthreads == 1)
		{
			$lang->asb_welcome_new_threads = $lang->asb_welcome_new_thread;
		}
		else
		{
			$lang->asb_welcome_new_threads = $lang->sprintf($lang->asb_welcome_new_threads, $newthreads ? $newthreads : 'no');
		}
		if($newposts == 1)
		{
			$lang->asb_welcome_new_posts = $lang->asb_welcome_new_post;
		}
		else
		{
			$lang->asb_welcome_new_posts = $lang->sprintf($lang->asb_welcome_new_posts, $newposts ? $newposts : 'no');
		}

		$avatar_width = (int) ($width / 5);

		// If the user has an avatar then display it . . .
		if($mybb->user['avatar'] != "")
		{
			$avatar_filename = $mybb->user['avatar'];
		}
		else
		{
			// . . . otherwise force the default avatar.
			$avatar_filename = "{$theme['imgdir']}/default_avatar.gif";
		}

		$user_avatar = <<<EOF
<span style="float: right;"><img src="{$avatar_filename}" width="{$avatar_width}" alt="{$mybb->user['username']}'s avatar"/>&nbsp;</span>
EOF;

		eval("\$welcometext = \"" . $templates->get("asb_welcome_membertext") . "\";");
	}
	else
	{
		$lang->asb_welcome_guest_welcome_registration = $lang->sprintf($lang->asb_welcome_guest_welcome_registration, $mybb->settings['bburl'] . '/member.php?action=register');
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
		eval("\$welcometext = \"" . $templates->get("asb_welcome_guesttext") . "\";");
	}

	$lang->welcome = $lang->sprintf($lang->welcome, $mybb->user['username']);
	eval("\$" . $template_var . " = \"" . $templates->get("asb_welcome") . "\";");
	return true;
}

?>
