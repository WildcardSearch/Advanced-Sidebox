<?php
/*
 * Advanced Sidebox Module
 *
 * Who's Online Avatar List (meta)
 *
 * This module is part of the Advanced Sidebox  default module pack. It can be installed and uninstalled like any other module. Even though it is included in the original installation, it is not necessary and can be completely removed by deleting the containing folder (ie modules/thisfolder).
 *
 * If you delete this folder from the installation pack this module will never be installed (and everything should work just fine without it). Don't worry, if you decide you want it back you can always download them again. The best move would be to install the entire package and try them out. Then be sure that the packages you don't want are uninstalled and then delete those folders from your server.
 *
 * This is a 'stereo' module, meaning that it outputs two different template variables to correspond with the two different box widths. If your module doesn't depend on the width of the sidebox its shown in (to size content) then set this option to false and simple output one 'mono' sidebox.
 *
 * This is a default portal box. Any changes from portal.php (MyBB 1.6.9) will be noted here.
 */
 
// Include a check for Advanced Sidebox
if(!defined("IN_MYBB") || !defined("ADV_SIDEBOX"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

function staff_online_box_asb_info()
{
	return array
	(
		"name"				=>	'Online Staff',
		"description"		=>	'lists the currently online staff\'s avatars',
		"stereo"			=>	true,
		"wrap_content"	=>	true
	);
}

function staff_online_box_asb_is_installed()
{
	global $db;
	
	// works just like a plugin
	$query = $db->simple_select('templates', 'title', "title='adv_sidebox_staff_online_left'");
	return $db->num_rows($query);
}

function staff_online_box_asb_install()
{
	global $db, $lang;
	
	if (!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}
	
	// the staff online avatar list parent template (left)
	$template_7_l = array(
        "title" => "adv_sidebox_staff_online_left",
        "template" => "<tr>
		<td class=\"trow1\">
			<span class=\"smalltext\">{\$lang->adv_sidebox_staff_online}</span>
		</td>
	</tr>
	<tr style=\"{\$adv_sidebox_hide}\">
		<td class=\"trow2\">{\$onlinemembers_l}</td>
	</tr>",
        "sid" => -1
    );
	
	$query = $db->simple_select('templates', 'title', "title='adv_sidebox_staff_online_left'");
	
	if($db->num_rows($query) == 1)
	{
		$db->update_query("templates", $template_7_l, "title='adv_sidebox_staff_online_left'");
	}
	else
	{
		$db->insert_query("templates", $template_7_l);
	}

	// the whosonline avatar list parent template (right)
	$template_7_r = array(
        "title" => "adv_sidebox_staff_online_right",
        "template" => "<tr>
		<td class=\"trow1\">
			<span class=\"smalltext\">{\$lang->adv_sidebox_staff_online}</span>
		</td>
	</tr>
	<tr style=\"{\$adv_sidebox_hide}\">
		<td class=\"trow2\">{\$onlinemembers_r}</td>
	</tr>",
        "sid" => -1
    );
	
	$query = $db->simple_select('templates', 'title', "title='adv_sidebox_staff_online_right'");
	
	if($db->num_rows($query) == 1)
	{
		$db->update_query("templates", $template_7_r, "title='adv_sidebox_staff_online_right'");
	}
	else
	{
		$db->insert_query("templates", $template_7_r);
	}
}

/*
 * This function is required. Clean up after yourself.
 */
function staff_online_box_asb_uninstall()
{
	global $db;
	
	// delete all the boxes of this type and the template as well
	$db->query("DELETE FROM " . TABLE_PREFIX . "sideboxes WHERE box_type='" . $db->escape_string('staff_online_box') . "'");
	
	// remove all custom templates.
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='adv_sidebox_staff_online_left'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='adv_sidebox_staff_online_right'");
}

function staff_online_box_asb_build_template()
{
	global $staff_online_box_l, $staff_online_box_r;
	global $db, $mybb, $templates, $lang, $cache;
	
	// Load global and custom language phrases
	if (!$lang->portal)
	{
		$lang->load('portal');
	}
	if (!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}
	
		// width
	$adv_sidebox_width_left = (int) $mybb->settings['adv_sidebox_width_left'];
	$adv_sidebox_width_right = (int) $mybb->settings['adv_sidebox_width_right'];
	
	$rowlength = (int) $mybb->settings['adv_sidebox_avatar_per_row'];
	$max_rows = (int) $mybb->settings['adv_sidebox_avatar_max_rows'];
	$row = 1;
	$avatar_count = 0;
	$enough_already = false;
	
	// user attempts to hide avatars from box by setting columns to 0 ?
	$adv_sidebox_hide = "";
	if ($rowlength < 1 || $rowlength > 100 || $max_rows < 1 || $max_rows > 100) {
		// lets provide our script some valid number to avoid errors
		// because we will must go through loop to count visitors anyway
		$rowlength = 1;
		$max_rows = 1;
		// we will hide part of table with avatars if user dont want them
		$adv_sidebox_hide = "display: none";
	}
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
	$staffcount_admin = 0;
	$staffcount_supermod = 0;
	$staffcount_mod = 0;
	$staffcount_staff = 0;

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
					$avatar_style_l = 'margin: ' . $avatar_margin_l . 'px; border: none;';
					$avatar_style_r = 'margin: ' . $avatar_margin_r . 'px; border: none;';
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
				if(in_array($user['usergroup'], array("3", "4", "6")) && $user['displaygroup'] == 0)
				{
					$user['username'] = format_name($user['username'], $user['usergroup'], $user['displaygroup']);
					$user['profilelink'] = get_profile_link($user['uid']);
					
					$userpermissions = user_permissions($user['uid']);
					if ($userpermissions['cancp'])
					{
						++$staffcount_admin;
					}
					elseif ($userpermissions['issupermod'])
					{
						++$staffcount_supermod;
					}
					elseif ($userpermissions['canmodcp'])
					{
						++$staffcount_mod;
					}
					else
					{
						++$staffcount_staff;
					}
					
					// if this is the last allowable avatar (conforming to ACP settings)
					if($avatar_count >= (($max_rows * $rowlength) - 1) && $avatar_count)
					{
						// check to see if we've already handled this, if so, do nothing
						if(!$enough_already)
						{
							// . . . if not, set a flag
							$enough_already = true;
							
							// . . . and insert link to the WOL full list
							$onlinemembers_l .= '<a href="' . $mybb->settings['bburl'] . '/online.php" title="' . $lang->adv_sidebox_see_all_title . '">'.$lang->adv_sidebox_see_all_alt.'</a>';
					
							$onlinemembers_r .= '<a href="' . $mybb->settings['bburl'] . '/online.php" title="' . $lang->adv_sidebox_see_all_title . '">'.$lang->adv_sidebox_see_all_alt.'</a>';
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
	
	if($staffcount_admin + $staffcount_supermod + $staffcount_mod + $staffcount_staff)
	{
		$staff_text = "";
		if ($staffcount_admin)
		{
		$staff_text .= $lang->sprintf($lang->adv_sidebox_staff_admin,$staffcount_admin);
		}
		if ($staffcount_supermod)
		{
			$staff_text .= $lang->sprintf($lang->adv_sidebox_staff_supermod,$staffcount_supermod);
		}
		if ($staffcount_mod)
		{
			$staff_text .= $lang->sprintf($lang->adv_sidebox_staff_mod,$staffcount_mod);
		}
		if ($staffcount_staff)
		{
			$staff_text .= $lang->sprintf($lang->adv_sidebox_staff_staff,$staffcount_staff);
		}
		$lang->adv_sidebox_staff_online = $lang->sprintf($lang->adv_sidebox_staff_online,$staff_text);
		eval("\$staff_online_box_l = \"" . $templates->get("adv_sidebox_staff_online_left") . "\";");
		eval("\$staff_online_box_r = \"" . $templates->get("adv_sidebox_staff_online_right") . "\";");
	}
	else
	{
		eval("\$staff_online_box_l = \"<tr><td class=\\\"trow1\\\">" . $lang->adv_sidebox_no_staff_online . "</td></tr>\";");
		eval("\$staff_online_box_r = \"<tr><td class=\\\"trow1\\\">" . $lang->adv_sidebox_no_staff_online . "</td></tr>\";");
	}
}

?>
