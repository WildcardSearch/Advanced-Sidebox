<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * ASB default module
 */

// Include a check for Advanced Sidebox
if (!defined('IN_MYBB') ||
	!defined('IN_ASB')) {
	die('Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.');
}

/**
 * provide info to ASB about the addon
 *
 * @return array module info
 */
function asb_whosonline_info()
{
	global $db, $lang, $theme;

	if (!$lang->asb_addon) {
		$lang->load('asb_addon');
	}

	return array(
		'title' => $lang->asb_wol,
		'description' => $lang->asb_wol_desc,
		'version' => '1.4.6',
		'compatibility' => '2.1',
		'wrap_content' => true,
		'xmlhttp' => true,
		'settings' =>	array(
			'show_avatars' => array(
				'name' => 'show_avatars',
				'title' => $lang->asb_show_avatars_title,
				'description' => $lang->asb_show_avatars_desc,
				'optionscode' => 'yesno',
				'value' => '1',
			),
			'asb_avatar_per_row' => array(
				'name' => 'asb_avatar_per_row',
				'title' => $lang->asb_wol_num_avatars_per_row_title,
				'description' => $lang->asb_wol_num_avatars_per_row_desc,
				'optionscode' => 'text',
				'value' => '4',
			),
			'asb_avatar_max_rows' => array(
				'name' => 'asb_avatar_max_rows',
				'title' => $lang->asb_wol_avatar_max_rows_title,
				'description' => $lang->asb_wol_avatar_max_rows_desc,
				'optionscode' => 'text',
				'value' => '3',
			),
			'asb_avatar_maintain_aspect' => array(
				'name' => 'asb_avatar_maintain_aspect',
				'title' => $lang->asb_wol_avatar_maintain_aspect_title,
				'description' => $lang->asb_wol_avatar_maintain_aspect_desc,
				'optionscode' => 'yesno',
				'value' => '0',
			),
			'xmlhttp_on' => array(
				'name' => 'xmlhttp_on',
				'title' => $lang->asb_xmlhttp_on_title,
				'description' => $lang->asb_xmlhttp_on_description,
				'optionscode' => 'text',
				'value' => '0',
			)
		),
		'templates' => array(
			array(
				'title' => 'asb_whosonline',
				'template' => <<<EOF
				<tr>
					<td class="trow1">
						<span class="smalltext">{\$lang->asb_wol_online_users} [<a href="online.php" title="Who\'s Online">{\$lang->asb_wol_complete_list}</a>]<br /><strong>&raquo;</strong> {\$lang->asb_wol_online_counts}</span>
					</td>
				</tr>
				<tr>
					<td class="trow2">
						<table style="text-align: center;">
							<tr>
								{\$onlinemembers}
							</tr>
						</table>
					</td>
				</tr>
EOF
			),
			array(
				'title' => 'asb_whosonline_memberbit_name',
				'template' => <<<EOF
{\$sep}<a href="{\$mybb->settings[\'bburl\']}/{\$user[\'profilelink\']}">{\$user[\'username\']}</a>
EOF
			),
			array(
				'title' => 'asb_whosonline_memberbit_avatar',
				'template' => <<<EOF
<td><a href="{\$mybb->settings[\'bburl\']}/{\$user[\'profilelink\']}"><img style="{\$avatar_width_style}{\$avatar_height_style}" src="{\$avatar_filename}" alt="{\$lang->asb_wol_avatar}" title="{\$user[\'username\']}\'s {\$lang->asb_wol_profile}"/></a></td>
EOF
			),
			array(
				'title' => 'asb_whosonline_memberbit_see_all',
				'template' => <<<EOF
<td><a href="{\$mybb->settings[\'bburl\']}/online.php" title="{\$lang->asb_wol_see_all_title}"><img style="{\$avatar_style}" src="{\$theme[\'imgdir\']}/asb/see_all.png" alt="{\$lang->asb_wol_see_all_alt}" title="{\$lang->asb_wol_see_all_title}" width="{\$avatar_width}px"/></a></td>
EOF
			),
		),
	);
}

/**
 * handles display of children of this addon at page load
 *
 * @param  array info from child box
 * @return bool success/fail
 */
function asb_whosonline_build_template($args)
{
	extract($args);
	global $$template_var, $lang;

	if (!$lang->asb_addon) {
		$lang->load('asb_addon');
	}

	// get the online members
	$all_onlinemembers = asb_whosonline_get_online_members($settings, $width);

	// if there are members online . . .
	if ($all_onlinemembers) {
		// set out template variable to the returned member list and return true
		$$template_var = $all_onlinemembers;
		return true;
	} else {
		// show the table only if there are threads
		$$template_var = <<<EOF
<tr><td class="trow1">{$lang->asb_wol_no_one_online}</td></tr>
EOF;
		return false;
	}
}

/**
 * handles display of children of this addon via AJAX
 *
 * @param  array information from child box
 * @return void
 */
function asb_whosonline_xmlhttp($args)
{
	extract($args);
	$all_onlinemembers = asb_whosonline_get_online_members($settings, $width);

	if ($all_onlinemembers) {
		return $all_onlinemembers;
	}
	return 'nochange';
}

/**
 * get the members currently online
 *
 * @param  array settings
 * @param  int column width
 * @return string|bool html or false
 */
function asb_whosonline_get_online_members($settings, $width)
{
	global $db, $mybb, $templates, $lang, $cache, $theme;

	// Load global and custom language phrases
	if (!$lang->asb_addon) {
		$lang->load('asb_addon');
	}

	$all_users = array();

	// width
	$rowlength = (int) $settings['asb_avatar_per_row'];
	if ($rowlength == 0) {
		return false;
	}

	$max_rows = (int) $settings['asb_avatar_max_rows'];
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
	$query = $db->write_query("
		SELECT s.sid, s.ip, s.uid, s.time, s.location, u.username, u.invisible, u.usergroup, u.displaygroup, u.avatar, u.avatardimensions
		FROM {$db->table_prefix}sessions s
		LEFT JOIN {$db->table_prefix}users u ON (s.uid=u.uid)
		WHERE s.time > '{$timesearch}'
		ORDER BY u.username ASC, s.time DESC
	");

	while ($user = $db->fetch_array($query)) {
		// create a key to test if this user is a search bot.
		$botkey = my_strtolower(str_replace('bot=', '', $user['sid']));

		if ($user['uid'] == '0') {
			++$guestcount;
		} elseif (my_strpos($user['sid'], 'bot=') !== false && $session->bots[$botkey]) {
			// The user is a search bot.
			$onlinemembers .= format_name($session->bots[$botkey], $session->botgroup);
			++$botcount;
		} else {
			$all_users[] = $user;
		}
	}

	foreach ($all_users as $user) {
		if ($doneusers[$user['uid']] < $user['time'] ||
			!$doneusers[$user['uid']]) {
			++$membercount;

			$doneusers[$user['uid']] = $user['time'];

			// If the user is logged in anonymously, update the count for that.
			if ($user['invisible'] == 1) {
				++$anoncount;
			}

			if ((($user['invisible'] == 1 &&
				($mybb->usergroup['canviewwolinvis'] == 1 ||
				$user['uid'] == $mybb->user['uid'])) ||
				$user['invisible'] != 1) &&
				$user['usergroup'] != 7) {
				$user['profilelink'] = get_profile_link($user['uid']);

				if ($settings['show_avatars']) {
					// If the user has an avatar then display it . . .
					if ($user['avatar'] != '') {
						$avatar_filename = $user['avatar'];
					} else {
						// . . . otherwise force the default avatar.
						$avatar_filename = "{$theme['imgdir']}/default_avatar.png";
					}

					$avatar_height_style = " min-height: {$avatar_height}px; max-height: {$avatar_height}px;";
					$avatar_width_style = " min-width: {$avatar_width}px; max-width: {$avatar_width}px;";
					if ($settings['asb_avatar_maintain_aspect']) {
						// Check the avatar's dimensions, then constrain it by its largest dimension
						$avatar_dimensions = explode('|', $user['avatardimensions']);

						if ($avatar_dimensions[0] > $avatar_dimensions[1]) {
							$avatar_height_style = '';
						} else {
							$avatar_width_style = '';
						}
					}

					// if this is the last allowable avatar (conforming to ACP settings)
					if ($avatar_count >= (($max_rows * $rowlength) - 1) &&
						$avatar_count) {
						// check to see if we've already handled this, if so, do nothing
						if (!$enough_already) {
							// . . . if not, set a flag
							$enough_already = true;

							// . . . and insert link to the WOL full list
							eval("\$onlinemembers .= \"" . $templates->get('asb_whosonline_memberbit_see_all', 1, 0) . "\";");
						}
					// . . . otherwise, add it to the list
					} else {
						eval("\$onlinemembers .= \"" . $templates->get('asb_whosonline_memberbit_avatar', 1, 0) . "\";");

						// If we reach the end of the row, insert a <br />
						if (($membercount - (($row - 1) * $rowlength)) == $rowlength) {
							$onlinemembers .= '</tr><tr>';
							++$row;
						}
						++$avatar_count;
					}
				} else {
					$user['username'] = format_name(trim($user['username']), $user['usergroup'], $user['displaygroup']);
					eval("\$onlinemembers .= \"" . $templates->get('asb_whosonline_memberbit_name', 1, 0) . "\";");
					$sep = $lang->comma . ' ';
				}
			} else {
				--$membercount;
			}
		}
	}

	if (!$settings['show_avatars']) {
		$onlinemembers = '<td>' . $onlinemembers . '</td>';
	}
	$onlinecount = $membercount + $guestcount + $botcount;

	// If we can see invisible users add them to the count
	if ($mybb->usergroup['canviewwolinvis'] == 1) {
		$onlinecount += $anoncount;
	}

	// If we can't see invisible users but the user is an invisible user increment the count by one
	if ($mybb->usergroup['canviewwolinvis'] != 1 &&
		$mybb->user['invisible'] == 1) {
		++$onlinecount;
	}

	// Most users online
	$mostonline = $cache->read('mostonline');
	if ($onlinecount > $mostonline['numusers']) {
		$time = TIME_NOW;
		$mostonline['numusers'] = $onlinecount;
		$mostonline['time'] = $time;
		$cache->update('mostonline', $mostonline);
	}
	$recordcount = $mostonline['numusers'];
	$recorddate = my_date($mybb->settings['dateformat'], $mostonline['time']);
	$recordtime = my_date($mybb->settings['timeformat'], $mostonline['time']);

	if ($onlinecount == 1) {
	  $lang->asb_wol_online_users = $lang->asb_wol_online_user;
	} else {
	  $lang->asb_wol_online_users = $lang->sprintf($lang->asb_wol_online_users, $onlinecount);
	}
	$lang->asb_wol_online_counts = $lang->sprintf($lang->asb_wol_online_counts, $membercount, $guestcount);

	if ($membercount) {
		eval("\$onlinemembers = \"" . $templates->get('asb_whosonline') . "\";");
		return $onlinemembers;
	} else {
		return false;
	}
}

?>
