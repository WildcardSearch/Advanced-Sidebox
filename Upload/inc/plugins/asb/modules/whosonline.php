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

/*
 * asb_whosonline_info()
 *
 * used by the core to identify and process the module
 */
function asb_whosonline_info()
{
	global $db, $lang, $theme;

	if(!$lang->asb_addon)
	{
		$lang->load('asb_addon');
	}

	return array
	(
		"title" => $lang->asb_wol,
		"description" => $lang->asb_wol_desc,
		"version" => "1.4",
		"wrap_content" => true,
		"xmlhttp" => true,
		"discarded_settings" => array
		(
			"asb_avatar_per_row",
			"asb_avatar_max_rows"
		),
		"settings" =>	array
		(
			"show_avatars" => array
			(
				"sid" => "NULL",
				"name" => "show_avatars",
				"title" => $lang->asb_show_avatars_title,
				"description" => $lang->asb_show_avatars_desc,
				"optionscode" => "yesno",
				"value" => '1'
			),
			"asb_avatar_per_row" => array
			(
				"sid" => "NULL",
				"name" => "asb_avatar_per_row",
				"title" => $lang->asb_wol_num_avatars_per_row_title,
				"description" => $lang->asb_wol_num_avatars_per_row_desc,
				"optionscode" => "text",
				"value" => '4'
			),
			"asb_avatar_max_rows" => array
			(
				"sid" => "NULL",
				"name" => "asb_avatar_max_rows",
				"title" => $lang->asb_wol_avatar_max_rows_title,
				"description" => $lang->asb_wol_avatar_max_rows_desc,
				"optionscode" => "text",
				"value" => '3'
			),
			"xmlhttp_on" => array
			(
				"sid" => "NULL",
				"name" => "xmlhttp_on",
				"title" => $lang->asb_xmlhttp_on_title,
				"description" => $lang->asb_xmlhttp_on_description,
				"optionscode" => "text",
				"value" => '0'
			)
		),
		"templates" =>	array
		(
			array
			(
				"title" => "asb_whosonline",
				"template" => <<<EOF
				<tr>
					<td class="trow1">
						<span class="smalltext">{\$lang->asb_wol_online_users} [<a href="online.php" title="Who\'s On-line">Complete List</a>]<br /><strong>&raquo;</strong> {\$lang->asb_wol_online_counts}</span>
					</td>
				</tr>
				<tr>
					<td class="trow2">
						<table>
							<tr>
								{\$onlinemembers}
							</tr>
						</table>
					</td>
				</tr>
EOF
				,
				"sid" => -1
			),
			array
			(
				"title" => "asb_whosonline_memberbit_name",
				"template" => <<<EOF
{\$sep}<a href="{\$mybb->settings[\'bburl\']}/{\$user[\'profilelink\']}">{\$user[\'username\']}</a>
EOF
				,
				"sid" => -1
			),
			array
			(
				"title" => "asb_whosonline_memberbit_avatar",
				"template" => <<<EOF
<td><a href="{\$mybb->settings[\'bburl\']}/{\$user[\'profilelink\']}">{\$user_avatar}</a></td>
EOF
				,
				"sid" => -1
			)
		)
	);
}

/*
 * asb_whosonline_build_template($settings, $template_var, $width)
 *
 * @param - $settings
					contains admin settings for the side box (defined above)
 * @param - $template_var
					contains the template variable name that is globally linked to this side box
 * @param - $width
					the width of the column in which this side box will display
 */
function asb_whosonline_build_template($args)
{
	foreach(array('settings', 'template_var', 'width') as $key)
	{
		$$key = $args[$key];
	}
	global $$template_var, $lang;

	if(!$lang->asb_addon)
	{
		$lang->load('asb_addon');
	}

	// get the on-line members
	$all_onlinemembers = asb_whosonline_get_online_members($settings, $width);

	// if there are members on-line . . .
	if($all_onlinemembers)
	{
		// set out template variable to the returned member list and return true
		$$template_var = $all_onlinemembers;
		return true;
	}
	else
	{
		// show the table only if there are threads
		$$template_var = <<<EOF
<tr><td class="trow1">{$lang->asb_wol_no_one_online}</td></tr>
EOF;
		return false;
	}
}

/*
 * asb_whosonline_xmlhttp($dateline, $settings, $width)
 *
 *
 */
function asb_whosonline_xmlhttp($args)
{
	foreach(array('settings', 'dateline', 'width') as $key)
	{
		$$key = $args[$key];
	}
	$all_onlinemembers = asb_whosonline_get_online_members($settings, $width);

	if($all_onlinemembers)
	{
		return $all_onlinemembers;
	}
	return 'nochange';
}

function asb_whosonline_get_online_members($settings, $width)
{
	global $db, $mybb, $templates, $lang, $cache, $theme;

	// Load global and custom language phrases
	if(!$lang->asb_addon)
	{
		$lang->load('asb_addon');
	}

	$all_users = array();

	// width
	$rowlength = (int) $settings['asb_avatar_per_row']['value'];
	$max_rows = (int) $settings['asb_avatar_max_rows']['value'];
	$row = 1;
	$avatar_count = 0;
	$enough_already = false;
	$sep = '';

	// Scale the avatars based on the width of the side boxes in Admin CP
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
		// create a key to test if this user is a search bot.
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
			$all_users[] = $user;
		}
	}

	foreach($all_users as $user)
	{
		if($doneusers[$user['uid']] < $user['time'] || !$doneusers[$user['uid']])
		{
			++$membercount;

			$doneusers[$user['uid']] = $user['time'];

			// If the user is logged in anonymously, update the count for that.
			if($user['invisible'] == 1)
			{
				++$anoncount;
			}

			if((($user['invisible'] == 1 && ($mybb->usergroup['canviewwolinvis'] == 1 || $user['uid'] == $mybb->user['uid'])) || $user['invisible'] != 1) && $user['usergroup'] != 7)
			{
				$user['profilelink'] = get_profile_link($user['uid']);

				if($settings['show_avatars']['value'])
				{
					// If the user has an avatar then display it . . .
					if($user['avatar'] != "")
					{
						$avatar_filename = $user['avatar'];
					}
					else
					{
						// . . . otherwise force the default avatar.
						$avatar_filename = "{$theme['imgdir']}/default_avatar.gif";
					}

					$user_avatar = <<<EOF
<img style="width: 100%; min-width: {$avatar_width}px; max-width: {$avatar_width}px; min-height: {$avatar_height}px; max-height: {$avatar_height}px;" src="{$avatar_filename}" alt="{$lang->asb_wol_avatar}" title="{$user['username']}'s {$lang->asb_wol_avatar_lc}"/>
EOF;

					// if this is the last allowable avatar (conforming to ACP settings)
					if($avatar_count >= (($max_rows * $rowlength) - 1) && $avatar_count)
					{
						// check to see if we've already handled this, if so, do nothing
						if(!$enough_already)
						{
							// . . . if not, set a flag
							$enough_already = true;

							// . . . and insert link to the WOL full list
							$onlinemembers .= <<<EOF
<a href="{$mybb->settings['bburl']}/online.php" title="{$lang->asb_wol_see_all_title}"><img style="{$avatar_style}" src="{$mybb->settings['bburl']}/inc/plugins/asb/images/see_all.gif" alt="{$lang->asb_wol_see_all_alt}" title="{$lang->asb_wol_see_all_title}" width="{$avatar_width}px"/></a>
EOF;
						}
					}
					// . . . otherwise, add it to the list
					else
					{
						eval("\$onlinemembers .= \"" . $templates->get("asb_whosonline_memberbit_avatar", 1, 0) . "\";");

						// If we reach the end of the row, insert a <br />
						if(($membercount - (($row - 1) * $rowlength)) == $rowlength)
						{
							$onlinemembers .= "</tr><tr>";
							++$row;
						}
						++$avatar_count;
					}
				}
				else
				{
					$user['username'] = format_name(trim($user['username']), $user['usergroup'], $user['displaygroup']);
					eval("\$onlinemembers .= \"" . $templates->get("asb_whosonline_memberbit_name", 1, 0) . "\";");
					$sep = ', ';
				}
			}
			else
			{
				--$membercount;
			}
		}
	}

	if(!$settings['show_avatars']['value'])
	{
		$onlinemembers = '<td>' . $onlinemembers . '</td>';
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
	  $lang->asb_wol_online_users = $lang->asb_wol_online_user;
	}
	else
	{
	  $lang->asb_wol_online_users = $lang->sprintf($lang->asb_wol_online_users, $onlinecount);
	}
	$lang->asb_wol_online_counts = $lang->sprintf($lang->asb_wol_online_counts, $membercount, $guestcount);

	if($membercount)
	{
		eval("\$onlinemembers = \"" . $templates->get("asb_whosonline") . "\";");
		return $onlinemembers;
	}
	else
	{
		return false;
	}
}

?>
