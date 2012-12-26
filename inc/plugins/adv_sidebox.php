<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x v1.0
 * Copyright © 2012 Wildcard
 * http://www.rantcentralforums.com
 *
 * BASED UPON THE CONCEPT AND CODE CREATED BY NAYAR IN THE ORIGINAL SIDEBOX PLUGIN
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses
 */

 // Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

// Load the install/admin routines only if in Admin CP.
if(defined("IN_ADMINCP"))
{
    require_once MYBB_ROOT . "inc/plugins/adv_sidebox/acp_functions.php";
}

global $settings;

// Hooks for the main routine
if($settings['adv_sidebox_on_index'])
{
	$plugins->add_hook("index_start", "adv_sidebox_start");
}

if($settings['adv_sidebox_on_forumdisplay'])
{
	$plugins->add_hook("forumdisplay_start", "adv_sidebox_start");
}

if($settings['adv_sidebox_on_showthread'])
{
	$plugins->add_hook("showthread_start", "adv_sidebox_start");
}

if($settings['adv_sidebox_portal_replace'])
{
	$plugins->add_hook("portal_start", "adv_sidebox_start");
}

// Check both admin and user settings and if applicable display the sideboxes.
function adv_sidebox_start()
{
	global $mybb, $db, $lang, $templates, $cache, $theme, $thread, $alttrow;
	global $sbwelcome, $sbpms, $sbsearch, $sbstats, $sbwhosonline_l, $sbwhosonline_r, $sblatestthreads, $gobutton, $lastvisit;
	$portal_url='member.php';
	
	// If the ACP settings indicate that the current script doesn't display the sideboxes then there is no need to go any further.
	if(THIS_SCRIPT == 'index.php' && !$mybb->settings['adv_sidebox_on_index']  || THIS_SCRIPT == 'forumdisplay.php' && !$mybb->settings['adv_sidebox_on_forumdisplay'] || THIS_SCRIPT == 'showthread.php' && !$mybb->settings['adv_sidebox_on_showthread'])
	{
		return false;
	}

	// If the EXCLUDE list isn't empty . . .
	if(is_array(unserialize($mybb->settings['adv_sidebox_exclude_theme'])))
	{
		// . . . and this theme is listed.
		if(in_array($theme['tid'], unserialize($mybb->settings['adv_sidebox_exclude_theme'])))
		{
			// no sidebox
			return false;
		}
	}
	
	// If the current user is not a guest and has disabled the sidebox display in UCP then do not display the sideboxes
	if($mybb->user['uid'] != 0)
	{
		$query = $db->simple_select("users", "show_sidebox", "uid = '".$mybb->user['uid']."' AND show_sidebox='1'", array("order_dir" => 'DESC'));
		if ($db->num_rows($query) == 0)
		{
			return false;
		}
	}

	// Build a post parser
	require_once MYBB_ROOT."inc/class_parser.php";
	$parser = new postParser;

	// Load global and custom language phrases
	if (!$lang->portal)
	{
		$lang->load('portal');
	}
	if (!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}

	// Look for all sideboxes (if any)
	$query = $db->simple_select('sideboxes', 'id, box_type, position, content', '', array("order_by" => 'display_order', "order_dir" => 'ASC'));
	
	// set up counters for both sides and box_types
	$box_types = array(
		'{$custom_box}' 		=> 0,
		'{$sbwelcome}' 		=> 0,
		'{$sbpms}' 				=> 0,
		'{$sbsearch}' 			=> 0,
		'{$sbstats}' 				=> 0,
		'{$sbwhosonline}' 	=> 0,
		'{$sbwhosonline_l}' 	=> 0,
		'{$sbwhosonline_r}' 	=> 0,
		'{$sblatestthreads}' 	=> 0
			);
	
	// if there are sideboxes . . .
	if($db->num_rows($query) > 0)
	{
		// . . . loop through each one and sort them by position
		while($this_box = $db->fetch_array($query))
		{
			// if this is a custom box . . .
			if($this_box['box_type'] == '{$custom_box}')
			{
				// . . . then use the custom content as a replacement
				$content = $this_box['content'];
			}
			// if this is a WOL box and the user has either set max rows or avatars per row to 0 (or blank). . .
			elseif(($this_box['box_type'] == '{$sbwhosonline_l}' || $this_box['box_type'] == '{$sbwhosonline_r}') && ((int) $mybb->settings['adv_sidebox_avatar_max_rows'] == 0 || (int) $mybb->settings['adv_sidebox_avatar_per_row'] == 0))
			{
				// . . . display nothing
				$content = '';
			}
			else
			{
				// . . . otherwise just use the content of box_type
				$content = $this_box['box_type'];
			}
			
			if((int) $this_box['position'] > 0)
			{
				$right_boxes .= $content;
			}
			else
			{
				$left_boxes .= $content;
			}
			
			// we'll check this array later to reduce wasted code
			// no need to parse templates if the box_type is unused
			$box_types[$this_box['box_type']] = true;
		}
	}
	else
	{
		// if there are no sideboxes, gtfo
		return false;
	}
	
	// lump the left and right WOL lists together
	if(($box_types['{$sbwhosonline_l}'] || $box_types['{$sbwhosonline_r}']) && (int) $mybb->settings['adv_sidebox_avatar_max_rows'])
	{
		$box_types['{$sbwhosonline}'] = true;
	}

	// width
	$adv_sidebox_width_left = (int) $mybb->settings['adv_sidebox_width_left'];
	$adv_sidebox_width_right = (int) $mybb->settings['adv_sidebox_width_right'];

	// Display boxes on index
	if($mybb->settings['adv_sidebox_on_index'] && THIS_SCRIPT == 'index.php')
	{
		if($left_boxes && !$right_boxes)
		{
			$templates->cache['index'] = str_replace('{$header}', '{$header}<table width="100%" border="0" cellspacing="5"><tr><td width="'.$adv_sidebox_width_left.'" valign="top">' . $left_boxes . '</td><td width="auto" valign="top">',$templates->cache['index']);
			$templates->cache['index'] = str_replace('{$footer}','{$footer}</td></tr></table></div></div>',$templates->cache['index']);
		}
		elseif(!$left_boxes && $right_boxes)
		{
			$templates->cache['index'] = str_replace('{$header}', '{$header}<table width="100%" border="0" cellspacing="5"><tr><td width="auto" valign="top">',$templates->cache['index']);
			$templates->cache['index'] = str_replace('{$footer}','{$footer}</td><td width="'.$adv_sidebox_width_right.'" valign="top">'.$right_boxes.'</td></tr></table>',$templates->cache['index']);
		}
		elseif($left_boxes && $right_boxes)
		{
			$templates->cache['index'] = str_replace('{$header}', '{$header}<table width="100%" border="0" cellspacing="5"><tr><td width="'.$adv_sidebox_width_left.'" valign="top">' . $left_boxes . '</td><td width="auto" valign="top">',$templates->cache['index']);
			$templates->cache['index'] = str_replace('{$footer}','{$footer}</td></td><td width="'.$adv_sidebox_width_right.'" valign="top">'.$right_boxes.'</td></tr></table></div></div>',$templates->cache['index']);
		}
	}

	//Display boxes on forumdisplay
	if($mybb->settings['adv_sidebox_on_forumdisplay'] && THIS_SCRIPT == 'forumdisplay.php')
	{
		if($left_boxes && !$right_boxes)
		{
			$templates->cache['forumdisplay'] = str_replace('{$header}','{$header}<table width="100%"  border="0"><tr><td width="'.$adv_sidebox_width_left.'" valign="top">'.$left_boxes.'</td><td width="auto" valign="top">',$templates->cache['forumdisplay']);
			$templates->cache['forumdisplay'] = str_replace('{$footer}','{$footer}</td></tr></table>',$templates->cache['forumdisplay']);
		}
		elseif(!$left_boxes && $right_boxes)
		{
			$templates->cache['forumdisplay'] = str_replace('{$header}','{$header}<table width="100%"  border="0"><tr><td width="auto" valign="top">',$templates->cache['forumdisplay']);
			$templates->cache['forumdisplay'] = str_replace('{$footer}','{$footer}</td><td width="'.$adv_sidebox_width_right.'" valign="top">'.$right_boxes.'</td></tr></table>',$templates->cache['forumdisplay']);
		}
		elseif($left_boxes && $right_boxes)
		{
			$templates->cache['forumdisplay'] = str_replace('{$header}','{$header}<table width="100%"  border="0"><tr><td width="'.$adv_sidebox_width_left.'" valign="top">'.$left_boxes.'</td><td width="auto" valign="top">',$templates->cache['forumdisplay']);
			$templates->cache['forumdisplay'] = str_replace('{$footer}','{$footer}</td><td width="'.$adv_sidebox_width_right.'" valign="top">'.$right_boxes.'</td></tr></table>',$templates->cache['forumdisplay']);
		}
	}
	
	//Display boxes on showthread
	if($mybb->settings['adv_sidebox_on_showthread'] && THIS_SCRIPT == 'showthread.php')
	{
		if($left_boxes && !$right_boxes)
		{
			$templates->cache['showthread'] = str_replace('{$header}','	{$header}<table width="100%"  border="0"><tr><td width="'.$adv_sidebox_width_left.'" valign="top">'.$left_boxes.'</td></td><td width="auto" valign="top">',$templates->cache['showthread']);
			$templates->cache['showthread'] = str_replace('{$footer}','{$footer}</td></tr></table>',$templates->cache['showthread']);
		}
		elseif(!$left_boxes && $right_boxes)
		{
			$templates->cache['showthread'] = str_replace('{$header}','	{$header}<table width="100%"  border="0"><tr><td width="auto" valign="top">',$templates->cache['showthread']);
			$templates->cache['showthread'] = str_replace('{$footer}','{$footer}</td><td width="'.$adv_sidebox_width_right.'" valign="top">'.$right_boxes.'</td></tr></table>',$templates->cache['showthread']);
		}
		elseif($left_boxes && $right_boxes)
		{
			$templates->cache['showthread'] = str_replace('{$header}','	{$header}<table width="100%"  border="0"><tr><td width="'.$adv_sidebox_width_left.'" valign="top">'.$left_boxes.'</td></td><td width="auto" valign="top">',$templates->cache['showthread']);
			$templates->cache['showthread'] = str_replace('{$footer}','{$footer}</td><td width="'.$adv_sidebox_width_right.'" valign="top">'.$right_boxes.'</td></tr></table>',$templates->cache['showthread']);
		}
	}
	
	// Display additional boxes on portal (if 'Replace Portal Boxes With Custom' is set to yes)
	if($mybb->settings['adv_sidebox_portal_replace'] && THIS_SCRIPT == 'portal.php')
	{
		$templates->cache['portal'] = str_replace('{$welcome}', '', $templates->cache['portal']);
		$templates->cache['portal'] = str_replace('{$search}', '', $templates->cache['portal']);
		$templates->cache['portal'] = str_replace('{$pms}', '', $templates->cache['portal']);
		$templates->cache['portal'] = str_replace('{$stats}', '', $templates->cache['portal']);
		$templates->cache['portal'] = str_replace('{$whosonline}', '', $templates->cache['portal']);
		$templates->cache['portal'] = str_replace('{$latestthreads}', $left_boxes, $templates->cache['portal']);
	}

	//*** Codes taken from portal.php ***
	//*** Updated to MyBB 1.6.9

	// get forums user cannot view
	$unviewable = get_unviewable_forums(true);
	if($unviewable)
	{
		$unviewwhere = " AND fid NOT IN ($unviewable)";
	}

	// If user is known, welcome them
	if($mybb->settings['portal_showwelcome'] != 0 && $box_types['{$sbwelcome}'])
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
		eval("\$sbwelcome = \"" . $templates->get("adv_sidebox_welcome") . "\";");
		if($mybb->user['uid'] == 0)
		{
			$mybb->user['username'] = "";
		}
	}

	// Private messages box
	if($mybb->settings['portal_showpms'] != 0 && $box_types['{$sbpms}'])
	{
		if($mybb->user['uid'] != 0 && $mybb->user['receivepms'] != 0 && $mybb->usergroup['canusepms'] != 0 && $mybb->settings['enablepms'] != 0)
		{
			switch($db->type)
			{
				case "sqlite":
				case "pgsql":
					$query = $db->simple_select("privatemessages", "COUNT(*) AS pms_total", "uid='" . $mybb->user['uid'] . "'");
					$messages['pms_total'] = $db->fetch_field($query, "pms_total");
					
					$query = $db->simple_select("privatemessages", "COUNT(*) AS pms_unread", "uid='" . $mybb->user['uid'] . "' AND CASE WHEN status = '0' AND folder = '0' THEN TRUE ELSE FALSE END");
					$messages['pms_unread'] = $db->fetch_field($query, "pms_unread");
					break;
				default:
					$query = $db->simple_select("privatemessages", "COUNT(*) AS pms_total, SUM(IF(status='0' AND folder='1','1','0')) AS pms_unread", "uid='" . $mybb->user['uid'] . "'");
					$messages = $db->fetch_array($query);
			}

			// the SUM() thing returns "" instead of 0
			if($messages['pms_unread'] == "")
			{
				$messages['pms_unread'] = 0;
			}
			$lang->pms_received_new = $lang->sprintf($lang->pms_received_new, $mybb->user['username'], $messages['pms_unread']);
			eval("\$sbpms = \"" . $templates->get("adv_sidebox_pms") . "\";");
		}
	}

	// get forum statistics
	if($mybb->settings['portal_showstats'] != 0 && $box_types['{$sbstats}'])
	{
		$stats = $cache->read("stats");
		$stats['numthreads'] = my_number_format($stats['numthreads']);
		$stats['numposts'] = my_number_format($stats['numposts']);
		$stats['numusers'] = my_number_format($stats['numusers']);
		if(!$stats['lastusername'])
		{
			$newestmember = "<strong>" . $lang->no_one . "</strong>";
		}
		else
		{
			$newestmember = build_profile_link($stats['lastusername'], $stats['lastuid']);
		}
		eval("\$sbstats = \"" . $templates->get("adv_sidebox_stats") . "\";");
	}

	// search box
	if($mybb->settings['portal_showsearch'] != 0 && $box_types['{$sbsearch}'])
	{
		eval("\$sbsearch = \"" . $templates->get("adv_sidebox_search") . "\";");
	}

	// get the online user avatar list
	if($mybb->settings['portal_showwol'] != 0 && $mybb->usergroup['canviewonline'] != 0 && $box_types['{$sbwhosonline}'])
	{
		$rowlength = (int) $mybb->settings['adv_sidebox_avatar_per_row'];
		$max_rows = (int) $mybb->settings['adv_sidebox_avatar_max_rows'];
		$row = 1;
		$avatar_count = 0;
		$enough_already = false;
		
		// Scale the avatars based on the width of the sideboxes in Admin CP
		$avatar_width_l = (int) ($adv_sidebox_width_left * .83) / $rowlength;
		$avatar_height_l = (int) ($adv_sidebox_width_left * .83) / $rowlength;
		$avatar_margin_l = (int) ($adv_sidebox_width_left * .83) *.02;
		$avatar_width_r = (int) ($adv_sidebox_width_right * .83) / $rowlength;
		$avatar_height_r = (int) ($adv_sidebox_width_right * .83) / $rowlength;
		$avatar_margin_r = (int) ($adv_sidebox_width_right *.83) *.02;
		
		$timesearch = TIME_NOW - $mybb->settings['wolcutoff'];
		$guestcount = 0;
		$membercount = 0;
		$onlinemembers = '';
		$query = $db->query("
			SELECT s.sid, s.ip, s.uid, s.time, s.location, u.username, u.invisible, u.usergroup, u.displaygroup, u.avatar
			FROM " . TABLE_PREFIX . "sessions s
			LEFT JOIN " . TABLE_PREFIX . "users u ON (s.uid=u.uid)
			WHERE s.time > '$timesearch'
			ORDER BY u.username ASC, s.time DESC
		");
		while($user = $db->fetch_array($query))
		{

			// Create a key to test if this user is a search bot.
			$botkey = my_strtolower(str_replace("bot=", '', $user['sid']));

			if($user['uid'] == "0")
			{
				++$guestcount;
			}
			elseif(my_strpos($user['sid'], "bot=") !== false && $session->bots[$botkey])
			{
				// The user is a search bot.
				$onlinemembers .= format_name($session->bots[$botkey], $session->botgroup);
				++$botcount;
			}
			else
			{
				if($doneusers[$user['uid']] < $user['time'] || !$doneusers[$user['uid']])
				{
					++$membercount;

					$doneusers[$user['uid']] = $user['time'];

					// If the user is logged in anonymously, update the count for that.
					if($user['invisible'] == 1)
					{
						++$anoncount;
					
						// The invisible mark just throws off the layout here.
						// Instead we will border the avatar if the user is invisibile.
						$avatar_style_l = 'margin: ' . ($avatar_margin_l / 2) . 'px; position:relative; top:-' . ($avatar_margin_l / 4) . 'px; border: ' . ($avatar_margin_l / 2) . 'px ridge #ff3333;';
						$avatar_style_r = 'margin: ' . ($avatar_margin_r / 2) . 'px; position:relative; top:-' . ($avatar_margin_r / 4) . 'px; border: ' . ($avatar_margin_r / 2) . 'px ridge #ff3333;';
					}
					else
					{
						$avatar_style_l = 'margin: ' . $avatar_margin . 'px; border: none;';
						$avatar_style_r = 'margin: ' . $avatar_margin . 'px; border: none;';
					}

					// If the user has an avatar then display it . . .
					if ($user['avatar'] != "")
					{
						$avatar_filename = $user['avatar'];
					}
					else
					{
						// . . . otherwise force the default avatar.
						$avatar_filename = "images/default_avatar.gif";
					}
					
					$user_avatar_l = '<img style="' . $avatar_style_l . '" src="' . $avatar_filename . '" alt="' . $lang->adv_sidebox_avatar . '" title="' . $user['username'] . '\'s ' . $lang->adv_sidebox_avatar_lc . '" width="' . $avatar_width_l . 'px" height="' . $avatar_height_l . 'px"/>';
					
					$user_avatar_r = '<img style="' . $avatar_style_r . '" src="' . $avatar_filename . '" alt="' . $lang->adv_sidebox_avatar . '" title="' . $user['username'] . '\'s ' . $lang->adv_sidebox_avatar_lc . '" width="' . $avatar_width_r . 'px" height="' . $avatar_height_r . 'px"/>';

					// If we reach the end of the row, insert a <br />
					if (($membercount - (($row - 1) * $rowlength)) == $rowlength)
					{
						$user_avatar_l .= "<br />";
						$user_avatar_r .= "<br />";
						$row = $row + 1;
					}

					if(($user['invisible'] == 1 && ($mybb->usergroup['canviewwolinvis'] == 1 || $user['uid'] == $mybb->user['uid'])) || $user['invisible'] != 1)
					{
						$user['username'] = format_name($user['username'], $user['usergroup'], $user['displaygroup']);
						$user['profilelink'] = get_profile_link($user['uid']);
						
						// if this is the last allowable avatar (conforming to ACP settings)
						if($avatar_count >= (($max_rows * $rowlength) - 1) && $avatar_count)
						{
							// check to see if we've already handled this, if so, do nothing
							if(!$enough_already)
							{
								// . . . if not, set a flag
								$enough_already = true;
								
								// . . . and insert an image linking to the WOL full list
								$onlinemembers_l .= '<a href="' . $mybb->settings['bburl'] . '/online.php"><img style="' . $avatar_style_l . '" src="images/see_all.gif" alt="' . $lang->adv_sidebox_see_all_alt . '" title="' . adv_sidebox_see_all_alt . '" width="' . $avatar_width_l . 'px" height="' . $avatar_height_l . 'px"/></a>';
						
								$onlinemembers_r .= '<a href="' . $mybb->settings['bburl'] . '/online.php"><img style="' . $avatar_style_r . '" src="images/see_all.gif" alt="' . $lang->adv_sidebox_see_all_alt . '" title="' . adv_sidebox_see_all_alt . '" width="' . $avatar_width_r . 'px" height="' . $avatar_height_r . 'px"/></a>';
							}
						}
						// . . . otherwise, add this avy to the list
						else
						{
							eval("\$onlinemembers_l .= \"".$templates->get("adv_sidebox_whosonline_memberbit_left", 1, 0)."\";");
							eval("\$onlinemembers_r .= \"".$templates->get("adv_sidebox_whosonline_memberbit_right", 1, 0)."\";");
						
							++$avatar_count;
						}
					}
				}
			}
		}

		$onlinecount = $membercount + $guestcount + $botcount;

		// If we can see invisible users add them to the count
		if($mybb->usergroup['canviewwolinvis'] == 1)
		{
			$onlinecount += $anoncount;
		}

		// If we can't see invisible users but the user is an invisible user increment the count by one
		if($mybb->usergroup['canviewwolinvis'] != 1 && $mybb->user['invisible'] == 1)
		{
			++$onlinecount;
		}

		// Most users online
		$mostonline = $cache->read("mostonline");
		if($onlinecount > $mostonline['numusers'])
		{
			$time = TIME_NOW;
			$mostonline['numusers'] = $onlinecount;
			$mostonline['time'] = $time;
			$cache->update("mostonline", $mostonline);
		}
		$recordcount = $mostonline['numusers'];
		$recorddate = my_date($mybb->settings['dateformat'], $mostonline['time']);
		$recordtime = my_date($mybb->settings['timeformat'], $mostonline['time']);

		if($onlinecount == 1)
		{
		  $lang->online_users = $lang->online_user;
		}
		else
		{
		  $lang->online_users = $lang->sprintf($lang->online_users, $onlinecount);
		}
		$lang->online_counts = $lang->sprintf($lang->online_counts, $membercount, $guestcount);
		
		eval("\$sbwhosonline_l = \"" . $templates->get("adv_sidebox_whosonline_left") . "\";");
		eval("\$sbwhosonline_r = \"" . $templates->get("adv_sidebox_whosonline_right") . "\";");
	}

	// Latest forum discussions
	if($mybb->settings['portal_showdiscussions'] != 0 && $mybb->settings['portal_showdiscussionsnum'] && $box_types['{$sblatestthreads}'])
	{
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
			eval("\$sblatestthreads = \"" . $templates->get("adv_sidebox_latest_threads") . "\";");
		}
	}
}

// Hooks for the User CP routine.
$plugins->add_hook("usercp_options_end", "adv_sidebox_options");
$plugins->add_hook("usercp_do_options_end", "adv_sidebox_options");

// Add a checkbox to the User CP under Other Options to toggle the sideboxes
function adv_sidebox_options()
{
	global $db, $mybb, $templates, $user, $lang;
	
	if (!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}

    // If the form is being submitted save the users choice.
	if($mybb->request_method == "post")
    {
		$update_array = array(
			"show_sidebox" => intval($mybb->input['showsidebox'])
		);

        $db->update_query("users", $update_array, "uid = '" . $user['uid'] . "'");
    }

	// Get the users setting and display the checkbox accordingly (checked/unchecked)
	$query = $db->simple_select("users", "show_sidebox", "uid = '".$user['uid']."' AND show_sidebox='1'", array("order_dir" => 'DESC'));

	if ($db->num_rows($query) > 0)
	{
		// checked
		$checked = 'checked="checked" ';
	}

	$usercp_option = '<td valign="top" width="1"><input type="checkbox" class="checkbox" name="showsidebox" id="showsidebox" value="1" ' . $checked . '/></td><td><span class="smalltext"><label for="showsidebox">' . $lang->adv_sidebox_show_sidebox . '</label></span></td></tr><tr><td valign="top" width="1"><input type="checkbox" class="checkbox" name="showredirect"';

    // Update the template cache
	$find = '<td valign="top" width="1"><input type="checkbox" class="checkbox" name="showredirect"';
    $templates->cache['usercp_options'] = str_replace($find, $usercp_option, $templates->cache['usercp_options']);
}

?>