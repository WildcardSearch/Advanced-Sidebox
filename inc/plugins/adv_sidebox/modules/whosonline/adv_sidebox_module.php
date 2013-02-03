<?php
/*
 * Advanced Sidebox Module
 *
 * Who's Online Avatar List
 *
 * This module is part of the Advanced Sidebox  default module pack. It can be installed and uninstalled like any other module. Even though it is included in the original installation, it is not necessary and can be completely removed by deleting the containing folder (ie modules/thisfolder).
 *
 * If you delete this folder from the installation pack this module will never be installed (and everything should work just fine without it). Don't worry, if you decide you want it back you can always download them again. The best move would be to install the entire package and try them out. Then be sure that the packages you don't want are uninstalled and then delete those folders from your server.
 *
 * This is a 'stereo' module, meaning that it outputs two different template variables to correspond with the two different box widths. If your module doesn't depend on the width of the sidebox its shown in (to size content) then set this option to false and simple output one 'mono' sidebox.
 */

// Include a check for Advanced Sidebox
if(!defined("IN_MYBB") || !defined("ADV_SIDEBOX"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

function whosonline_asb_info()
{
	global $db, $lang;

	if(!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}

	return array
	(
		"name"							=>	'Who\'s Online',
		"description"					=>	'Currently online members\' avatars',
		"version"						=>	"3",
		"wrap_content"				=>	true,
		"discarded_settings"	=>	array
													(
														"adv_sidebox_avatar_per_row",
														"adv_sidebox_avatar_max_rows"
													),
		"settings"						=>	array
													(
														"adv_sidebox_avatar_per_row"	=> array
														(
															"sid"					=> "NULL",
															"name"				=> "adv_sidebox_avatar_per_row",
															"title"				=> $lang->adv_sidebox_wol_avatar_list,
															"description"		=> $lang->adv_sidebox_num_avatars_per_row . ":",
															"optionscode"	=> "text",
															"value"				=> '4'
														),
														"adv_sidebox_avatar_max_rows"	=> array
														(
															"sid"					=> "NULL",
															"name"				=> "adv_sidebox_avatar_max_rows",
															"title"					=> '',
															"description"		=> $lang->adv_sidebox_avatar_max_rows . ":",
															"optionscode"	=> "text",
															"value"				=> '3'
														)
													),
		"discarded_templates"	=>	array
													(
														"adv_sidebox_whosonline_left",
														"adv_sidebox_whosonline_right",
														"adv_sidebox_whosonline_memberbit_left",
														"adv_sidebox_whosonline_memberbit_right"
													),
		"templates"					=>	array
													(
														array
														(
															"title" => "adv_sidebox_whosonline",
															"template" => "
	<tr>
		<td class=\"trow1\">
			<span class=\"smalltext\">{\$lang->online_users}<br /><strong>&raquo;</strong> {\$lang->online_counts}</span>
		</td>
	</tr>
	<tr style=\"{\$adv_sidebox_hide}\">
		<td class=\"trow2\">{\$onlinemembers}</td>
	</tr>
															",
															"sid" => -1
														),
														array
														(
															"title" => "adv_sidebox_whosonline_memberbit",
															"template" => "
			<a href=\"{\$mybb->settings[\'bburl\']}/{\$user[\'profilelink\']}\">{\$user_avatar}</a>",
															"sid" => -1
														)
													)
	);
}

function whosonline_asb_build_template($settings, $template_var, $width)
{
	global $$template_var;

	global $db, $mybb, $templates, $lang, $cache;

	// Load global and custom language phrases
	if(!$lang->portal)
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

	//die(var_dump($settings));

	$rowlength = (int) $settings['adv_sidebox_avatar_per_row']['value'];
	$max_rows = (int) $settings['adv_sidebox_avatar_max_rows']['value'];
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
		// we will hide part of box with avatars if user dont want them
		$adv_sidebox_hide = "display: none";
	}
	// Scale the avatars based on the width of the sideboxes in Admin CP
	$avatar_height = $avatar_width = (int) ($width * .83) / $rowlength;
	$avatar_margin = (int) ($avatar_width *.02);

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
					$avatar_style = 'margin: ' . ($avatar_margin / 2) . 'px; position:relative; top:-' . ($avatar_margin / 4) . 'px; border: ' . ($avatar_margin / 2) . 'px ridge #ff3333;';
				}
				else
				{
					$avatar_style = 'margin: ' . $avatar_margin . 'px; border: none;';
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

				$user_avatar = '<img style="' . $avatar_style . '" src="' . $avatar_filename . '" alt="' . $lang->adv_sidebox_avatar . '" title="' . $user['username'] . '\'s ' . $lang->adv_sidebox_avatar_lc . '" width="' . $avatar_width . 'px" height="' . $avatar_height . 'px"/>';

				// If we reach the end of the row, insert a <br />
				if (($membercount - (($row - 1) * $rowlength)) == $rowlength)
				{
					$user_avatar .= "<br />";
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

							// . . . and insert link to the WOL full list
							$onlinemembers .= '<a href="' . $mybb->settings['bburl'] . '/online.php" title="' . $lang->adv_sidebox_see_all_title . '">'.$lang->adv_sidebox_see_all_alt.'</a>';
						}
					}
					// . . . otherwise, add this avy to the list
					else
					{
						eval("\$onlinemembers .= \"".$templates->get("adv_sidebox_whosonline_memberbit", 1, 0)."\";");

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

	if($membercount)
	{
		eval("\$" . $template_var . " = \"" . $templates->get("adv_sidebox_whosonline") . "\";");
	}
	else
	{
		eval("\$" . $template_var . " = \"<tr><td class=\\\"trow1\\\">" . $lang->adv_sidebox_noone_online . "</td></tr>\";");
	}
}

?>
