<?php
/*
 * ****========================================================================
 * module 'Online Staff' v1.0 (c) 2013 by Avril,
 * for 'Advanced Sidebox' by Wildcard.
 * 
 * You may get latest version of 'Online Staff' module at
 * http://avril-gh.github.com/Online-Staff-module
 * 
 * THIS MODULE REQUIRE 'ADVANCED SIDEBOX' INSTALLED !
 * 
 * You may get latest version of 'Advanced Sidebox' plugin for MyBB at
 * github.com/WildcardSearch/Advanced-Sidebox
 * ============================================================================
 * LICENSE :
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free Software Foundation,
 * either version 3 of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with this program.
 * If not, see http://www.gnu.org/licenses/
 * ============================================================================
 * You may receive support for this module wherever possible,
 * however under condition that your copy of this module is in its latest
 * original and unmodified official release version.
 * ============================================================================
 * 
 * 'Online Staff' module - short Readme :
 * If you actually looking inside this file,
 * then perhaps these informations may be useful to you.
 * 
 * 1. 'Staff Online' module use following templates :
 * Global Templates -> adv_sidebox_staff_online_left
 * Global Templates -> adv_sidebox_staff_online_right
 * Global Templates -> adv_sidebox_staff_online_bit_left
 * Global Templates -> adv_sidebox_staff_online_bit_right
 * 
 * 2. 'Staff Online' module use following variables
 * 
 * General use variables :
 * 
 *	$staff_online["count_admins"]			- value
 *	$staff_online["count_supermods"]		- value
 *	$staff_online["count_mods"]				- value
 *	$staff_online["count_others"]			- value
 *	$staff_online["count_total"]			- value
 *
 *  $staff_online["lang_info"]				- string from language file
 *	$staff_online["lang_admin"] 			- string from language file
 *	$staff_online["lang_supermod"]			- string from language file
 *	$staff_online["lang_mod"]				- string from language file
 *	$staff_online["lang_other"]				- string from language file
 *
 * Dynamic CSS variables used to hide various template blocks.
 * Contain "display: none;" or "" depending on content of related general use variables.
 * eg. If $staff_online["count_admins"] = 0 then $staff_online["hide_admins"] = "display: none;" else $staff_online["hide_admins"] = ""
 * 
 *	$staff_online["hide_info"]				- depends on ACP setting
 *	$staff_online["hide_admins"]			- depends on online admins count
 *	$staff_online["hide_supermods"]			- depends on online super moderators count
 *	$staff_online["hide_mods"]				- depends on online moderators count
 *	$staff_online["hide_others"]			- depends on online staff members count
 *	$staff_online["hide_total"]				- depends on total count of online staff
 *	$staff_online["hide_seemore"]			- set to "" if count of online staff is > than set in ACP
 *	$staff_online["hide_ifnostaff"]			- set to "" if there are no staff online
 *	$staff_online["hide_ifstaff"]			- set to "" if there are staff online
 *
 *	$staff_online[$bits_left]				- contain collection of bit templates
 *	$staff_online[$bits_right]				- contain collection of bit templates
 *											(bit template is used to output detailed staff member info) 
 *				
 * Bit variables
 * (used to create detailed info for every staff member online in bit template)
 *
 *	$staff_online["bit_trow_x"]				- value used in conjunction with class="trowX"
 *									  		used to create interlaced style in tables.
 *                                    		It changes betwin 1 and 2 every time bit is output
 *
 *	$staff_online["bit_username"]			- user name
 *	$staff_online["bit_userid"]				- user id
 *	$staff_online["bit_username_formatted"]	- formatted username (group color ect)
 *	$staff_online["bit_useravatar"]			- user avatar
 *	$staff_online["bit_useravatar_size"]	- avatar size (set in ACP)
 *	$staff_online["bit_userprofile"] 		- link to user profile
 *	$staff_online["bit_usertype"]			- type of user (eg. moderator)
 *	$staff_online["bit_usertype_formatted"]	- formatted type of user (group color ect)
 * 
 * ============================================================================
 * For more informations about styling and techniques
 * used to build 'Online Staff' module template blocks
 * you may reffer to http://avril-gh.github.com/Online-Staff-module
 * ============================================================================
 */

// this file may not be executed from outside of script
if(!defined("IN_MYBB") || !defined("ADV_SIDEBOX")) {
	die("You need MyBB and Advanced Sidebox plugin installed and properly initialised to use this.");
}

/*
 * Advanced Sidebox module - staff online
 * module info
 */
function staff_online_box_asb_info()
{
	global $db, $lang;
	
	if (!$lang->adv_sidebox_staff_online_box)
	{
		$lang->load('adv_sidebox_staff_online_box');
	}
	
	return array
	(
		"name"							=>	'Online Staff',
		"description"					=>	'Display online staff members list',
		"version"						=>	"2",
		"author"						=>	'Avril',
		"author_site"				=>	'http://avril-gh.github.com/Online-Staff-module',
		"stereo"						=>	true,
		"wrap_content"				=>	true,
		"discarded_settings"	=>	array
													(
														"adv_sidebox_staff_online_bydetail",
														"adv_sidebox_staff_online_avatarsize",
														"adv_sidebox_staff_online_bytype",
														"adv_sidebox_staff_online_hideinfo"
													),
		"settings"						=>	array
													(
														array
														(
															"sid"				=> "NULL",
															"name"				=> "adv_sidebox_staff_online_bydetail",
															"title"				=> $db->escape_string($lang->adv_sidebox_staff_online_option_bydetail_title),
															"description"		=> $db->escape_string($lang->adv_sidebox_staff_online_option_bydetail_description),
															"optionscode"		=> "text",
															"value"				=> '5',
															"disporder"			=> '492'
														),
														array
														(
															"sid"				=> "NULL",
															"name"				=> "adv_sidebox_staff_online_avatarsize",
															"title"				=> $db->escape_string($lang->adv_sidebox_staff_online_option_avatarsize_title),
															"description"		=> $db->escape_string($lang->adv_sidebox_staff_online_option_avatarsize_description),
															"optionscode"		=> "text",
															"value"				=> '36',
															"disporder"			=> '493'
														),
														array
														(
															"sid"				=> "NULL",
															"name"				=> "adv_sidebox_staff_online_bytype",
															"title"				=> $db->escape_string($lang->adv_sidebox_staff_online_option_bytype_title),
															"description"		=> $db->escape_string($lang->adv_sidebox_staff_online_option_bytype_description),
															"optionscode"		=> "yesno",
															"value"				=> '1',
															"disporder"			=> '494'
														),
														array(
															"sid"				=> "NULL",
															"name"				=> "adv_sidebox_staff_online_hideinfo",
															"title"				=> $db->escape_string($lang->adv_sidebox_staff_online_option_hideinfo_title),
															"description"		=> $db->escape_string($lang->adv_sidebox_staff_online_option_hideinfo_description),
															"optionscode"		=> "yesno",
															"value"				=> '1',
															"disporder"			=> '495'
														)
													),
		"templates"					=>	array
													(
														array
														(
																		"title" 	=> "adv_sidebox_staff_online_left",
																		"template"	=> "
	<tr style=\"{\$staff_online[\'hide_info\']}\">
		<td class=\"trow1\">
			<span class=\"smalltext\">{\$staff_online[\'lang_info\']}</span>
		</td>
	</tr>

	<tr class=\"trow2\" style=\"{\$staff_online[\'hide_admins\']}\">
		<td>
			<span class=\"smalltext\"> &raquo; {\$staff_online[\'count_admins\']} {\$staff_online[\'lang_admin\']}(s).</span>
		</td>
	</tr>
	<tr class=\"trow1\" style=\"{\$staff_online[\'hide_supermods\']}\">
		<td>
			<span class=\"smalltext\"> &raquo; {\$staff_online[\'count_supermods\']} {\$staff_online[\'lang_supermod\']}(s).</span>
		</td>
	</tr>
	<tr class=\"trow2\" style=\"{\$staff_online[\'hide_mods\']}\">
		<td>
			<span class=\"smalltext\"> &raquo; {\$staff_online[\'count_mods\']} {\$staff_online[\'lang_mod\']}(s).</span>
		</td>
	</tr>
	<tr class=\"trow1\" style=\"{\$staff_online[\'hide_others\']}\">
		<td>
			<span class=\"smalltext\"> &raquo; {\$staff_online[\'count_others\']} {\$staff_online[\'lang_other\']}(s).</span>
		</td>
	</tr>

	{\$bits_left}
	<tr style=\"{\$staff_online[\'hide_seemore\']}\">
		<td class=\"trow1\">
			<a href=\"{\$mybb->settings[\'bburl\']}/showteam.php\" title=\"{\$lang->adv_sidebox_staff_online_findother}\">
				<span class=\"smalltext\"> &raquo; {\$lang->adv_sidebox_staff_online_findother}</span>
			</a>
		</td>
	</tr>
																				",
																				"sid"		=> -1
														),
														array
														(
															"title" 	=> "adv_sidebox_staff_online_right",
															"template"	=> "
	<tr style=\"{\$staff_online[\'hide_info\']}\">
		<td class=\"trow1\">
			<span class=\"smalltext\">{\$staff_online[\'lang_info\']}</span>
		</td>
	</tr>

	<tr class=\"trow2\" style=\"{\$staff_online[\'hide_admins\']}\">
		<td>
			<span class=\"smalltext\"> &raquo; {\$staff_online[\'count_admins\']} {\$staff_online[\'lang_admin\']}(s).</span>
		</td>
	</tr>
	<tr class=\"trow1\" style=\"{\$staff_online[\'hide_supermods\']}\">
		<td>
			<span class=\"smalltext\"> &raquo; {\$staff_online[\'count_supermods\']} {\$staff_online[\'lang_supermod\']}(s).</span>
		</td>
	</tr>
	<tr class=\"trow2\" style=\"{\$staff_online[\'hide_mods\']}\">
		<td>
			<span class=\"smalltext\"> &raquo; {\$staff_online[\'count_mods\']} {\$staff_online[\'lang_mod\']}(s).</span>
		</td>
	</tr>
	<tr class=\"trow1\" style=\"{\$staff_online[\'hide_others\']}\">
		<td>
			<span class=\"smalltext\"> &raquo; {\$staff_online[\'count_others\']} {\$staff_online[\'lang_other\']}(s).</span>
		</td>
	</tr>

	{\$bits_right}
	<tr style=\"{\$staff_online[\'hide_seemore\']}\">
		<td class=\"trow1\">
			<a href=\"{\$mybb->settings[\'bburl\']}/showteam.php\" title=\"{\$lang->adv_sidebox_staff_online_findother}\">
				<span class=\"smalltext\"> &raquo; {\$lang->adv_sidebox_staff_online_findother}</span>
			</a>
		</td>
	</tr>
						",
						"sid"		=> -1
			),
			array
			(
				"title" 	=> "adv_sidebox_staff_online_bit_left",
				"template"	=> "
	<tr>
		<td class=\"trow{\$staff_online[\'bit_trow_x\']}\">

			<a href=\"{\$staff_online[\'bit_userprofile\']}\" title=\"{\$staff_online[\'bit_usertype\']} : {\$staff_online[\'bit_username\']}\">
				<img style=\"float: left; margin-left: 5px; margin-right: 10px;\" src=\"{\$staff_online[\'bit_useravatar\']}\" alt=\"{\$staff_online[\'bit_usertype\']} - {\$staff_online[\'bit_username\']}\" height=\"{\$staff_online[\'bit_useravatar_size\']}px\" width=\"{\$staff_online[\'bit_useravatar_size\']}px\" />
				<span>{\$staff_online[\'bit_usertype_formatted\']} {\$staff_online[\'bit_username_formatted\']}</span><br/>
			</a>

			<a href=\"{\$mybb->settings[\'bburl\']}/private.php?action=send&amp;uid={\$staff_online[\'bit_userid\']}\" title=\"{\$staff_online[\'bit_usertype\']} : {\$staff_online[\'bit_username\']}\">
				<span class=\"smalltext\">{\$lang->adv_sidebox_staff_online_askhelp}</span>
			</a>

		</td>
	</tr>
																	",
																	"sid"		=> -1
														),
														array
														(
															"title" 	=> "adv_sidebox_staff_online_bit_right",
															"template"	=> "
	<tr>
		<td class=\"trow{\$staff_online[\'bit_trow_x\']}\">

			<a href=\"{\$staff_online[\'bit_userprofile\']}\" title=\"{\$staff_online[\'bit_usertype\']} : {\$staff_online[\'bit_username\']}\">
				<img style=\"float: left; margin-left: 5px; margin-right: 10px;\" src=\"{\$staff_online[\'bit_useravatar\']}\" alt=\"{\$staff_online[\'bit_usertype\']} - {\$staff_online[\'bit_username\']}\" height=\"{\$staff_online[\'bit_useravatar_size\']}px\" width=\"{\$staff_online[\'bit_useravatar_size\']}px\" />
				<span>{\$staff_online[\'bit_usertype_formatted\']} {\$staff_online[\'bit_username_formatted\']}</span><br/>
			</a>

			<a href=\"{\$mybb->settings[\'bburl\']}/private.php?action=send&amp;uid={\$staff_online[\'bit_userid\']}\" title=\"{\$staff_online[\'bit_usertype\']} : {\$staff_online[\'bit_username\']}\">
				<span class=\"smalltext\">{\$lang->adv_sidebox_staff_online_askhelp}</span>
			</a>

		</td>
	</tr>
																	",
																	"sid"		=> -1
														)
													)
	);
}

/*
 * Advanced Sidebox module - staff online plus
 * prepare module content
 */
function staff_online_box_asb_build_template($settings)
{
	global $staff_online_box_l, $staff_online_box_r;
	global $db, $mybb, $templates, $lang, $cache;

//	====----
	$staff_online_debug = false;		// dev mode. Multiply staff member x10 for bits test.
//	====----
	
	if (!$lang->adv_sidebox_staff_online_box) {
		$lang->load('adv_sidebox_staff_online_box');
	}

//	Init variables to state as there are no staff members online
	$staff_online				= Array (
	
//	Main template			 		- General use variables (value)
	"count_admins"				=> 0,
	"count_supermods"			=> 0,
	"count_mods"				=> 0,
	"count_others"				=> 0,
	"count_total"				=> 0,

//  Main template			 		- 'CSS logic contitional' variables
//									  When related general variable = 0
//									  then its set to "display: none;"
//									  When related general variable > 0
//									  then its set to ""
//									  Can be used to apply logic to template
//									  with CSS only
	"hide_info"					=> "display: none;",
	"hide_admins"				=> "display: none;",
	"hide_supermods"			=> "display: none;",
	"hide_mods"					=> "display: none;",
	"hide_others"				=> "display: none;",
	"hide_total"				=> "display: none;",
	"hide_seemore"				=> "display: none;",
	"hide_ifnostaff"			=> "display: none;",
	"hide_ifstaff"				=> "display: none;",
				
//	Main template					- Language file strings
	"lang_info"					=> $lang->adv_sidebox_staff_online_nostaff,
	"lang_admin" 				=> $lang->adv_sidebox_staff_online_admin,
	"lang_supermod"				=> $lang->adv_sidebox_staff_online_supermod,
	"lang_mod"					=> $lang->adv_sidebox_staff_online_mod,
	"lang_other"				=> $lang->adv_sidebox_staff_online_other,

//	Bit template					- value used in conjunction with class="trowX"
//									  used to create interlaced style in tables.
//                                    It changes betwin 1 and 2 every time bit is output
	"bit_trow_x"				=> 1,

//	Bit template		 			- data related to current staff member
//									  when enumerating.
	"bit_username"				=> "",
	"bit_userid"				=> "",
	"bit_username_formatted"	=> "",
	"bit_useravatar"			=> "",
	"bit_useravatar_size"		=> intval($settings[1]),
	"bit_userprofile" 			=> "",
	"bit_usertype"				=> "",
	"bit_usertype_formatted"	=> "",
	"bit_max_to_show" 			=> intval($settings[0])
	);

	// prevent faulty input
	if ($staff_online["bit_useravatar_size"] < 0 || $staff_online["bit_useravatar_size"] > 1000) {
		$staff_online["bit_useravatar_size"] = 0;
	}
	if ($staff_online["bit_max_to_show"] < 0 || $staff_online["bit_max_to_show"] > 100) {
		$staff_online["bit_max_to_show"] = 0;
	}
	
	//	Main template					- collection of data created by repeating bit template
	//									  for every visible staff member
	$bits_left					= "";
	$bits_right					= "";
	//									- ACP option show by type block
	$bytype_enable				= intval($settings[2]) & 1;

	// Prepare debug if enabled
	if ($staff_online_debug) {
		$xTestMultiply=10;
	}else{
		$xTestMultiply=1;
	}
	
//	Main loop - modify variables if required and output as box
	
	// if this user can view whos online then build list of staff members online for him
	if ($mybb->usergroup['canviewonline']) {
		
		// Get online users
		$timesearch = TIME_NOW - $mybb->settings['wolcutoff'];
		$query = $db->query("
			SELECT s.sid, s.ip, s.uid, s.time, s.location, u.username, u.invisible, u.usergroup, u.displaygroup, u.avatar
			FROM " . TABLE_PREFIX . "sessions s
			LEFT JOIN " . TABLE_PREFIX . "users u ON (s.uid=u.uid)
				WHERE s.time > '$timesearch'
				ORDER BY u.username ASC, s.time DESC
				");
	
		// and loop through them to get staff members.
		while($user = $db->fetch_array($query)) {

			// DEBUG If enabled - multiplying staff member for test output.
			for ($xTest = 0; $xTest < $xTestMultiply; $xTest++) {

				$userpermissions = user_permissions($user['uid']);
				
				$this_user_is = "nostaff";
				if ($user['uid']) {									// not a guest
					if (!$user['invisible']) {						// and not invisible
						if ($userpermissions['cancp']) {
							$staff_online["count_admins"]++;
							$staff_online["count_total"]++;
							$this_user_is = $lang->adv_sidebox_staff_online_admin;
						} elseif ($userpermissions['issupermod']) {
							$staff_online["count_supermods"]++;
							$staff_online["count_total"]++;
							$this_user_is = $lang->adv_sidebox_staff_online_supermod;
						} elseif ($userpermissions['canmodcp']) {
							$staff_online["count_mods"]++;
							$staff_online["count_total"]++;
							$this_user_is = $lang->adv_sidebox_staff_online_mod;
						} elseif ($userpermissions['showforumteam']) {
							$staff_online["count_others"]++;
							$staff_online["count_total"]++;
							$this_user_is = $lang->adv_sidebox_staff_online_other;
						}
					}
				}
				
				// if user has been countedin as visible staff member,
				// and there are space to put member bit
				if ($this_user_is != "nostaff" && $staff_online["count_total"] <= $staff_online["bit_max_to_show"]) {
				
					// Set bit variables for this staff member
					if ($user['avatar']) {			// if have avatar just get it
						$staff_online["bit_useravatar"] = $user['avatar'];
					} else {						// else use default avatar
						$staff_online["bit_useravatar"] = $mybb->settings['bburl']."/images/default_avatar.gif";
					}
					$staff_online["bit_userprofile"] = $mybb->settings['bburl']."/member.php?action=profile&amp;uid=".$user['uid'];
					$staff_online["bit_username"] = $user['username'];
					$staff_online["bit_userid"] = $user['uid'];
					$staff_online["bit_usertype"] = $this_user_is;
					$staff_online["bit_username_formatted"] = format_name($staff_online["bit_username"], $user['usergroup'], $user['displaygroup']);
					$staff_online["bit_usertype_formatted"] = format_name($staff_online["bit_usertype"], $user['usergroup'], $user['displaygroup']);
					$staff_online["bit_trow_x"] ^= 3;
				
					// add bit template to stack
					eval("\$bits_left .= \"" . $templates->get("adv_sidebox_staff_online_bit_left") . "\";");
					eval("\$bits_right .= \"" . $templates->get("adv_sidebox_staff_online_bit_right") . "\";");
				}
			}
		}
	}
	
	// did we found any visible staff member online to show ?
	// (if viewer have no privilages to see online members,
	// then they wasnt even counted and this will be 0
	// as well as other variables which are allready set to default - no staff online)
	if ($staff_online["count_total"]) {
		// There are some visible staff members online to show and viewer may see them.
		
		// update info message, (total staff members online ect)
		$staff_online["lang_info"] = $lang->sprintf($lang->adv_sidebox_staff_online_staff,$staff_online["count_total"]);
		
		// unhide fields related to visible staff members and ACP setting
		if ($bytype_enable) {
			if ($staff_online["count_admins"])		$staff_online["hide_admins"]	= "";
			if ($staff_online["count_supermods"])	$staff_online["hide_supermods"]	= "";
			if ($staff_online["count_mods"])		$staff_online["hide_mods"]		= "";
			if ($staff_online["count_others"])		$staff_online["hide_others"]	= "";
		}
		if ($staff_online["count_total"])		$staff_online["hide_total"]		= "";
	
		// display see more
		// if there are more staff online but bits count was limited in ACP settings
		if ($staff_online["count_total"] > $staff_online["bit_max_to_show"]) {
			$staff_online["hide_seemore"] = "";
		}		
		
	}

	// show Info block according to ACP settings and staff online presence.
	if (!$staff_online["count_total"]) {
		// There are no staff online. We cant show just empty box.
		// Info about no staff will be shown regardles from ACP setting
		$staff_online["hide_info"] = "";
	}elseif ((intval($settings[3]) & 1)){
		// There are staff online and perhaps user want to use other info block for it.
		// Hide info block if ACP setting is set to hide it.
		$staff_online["hide_info"] = "";
	}
	
	// Set related CSS logic for templating.
	if ($staff_online["count_total"]) {
		$staff_online["hide_ifnostaff"] = "";
	}else{
		$staff_online["hide_ifstaff"] = "";
	}
	
	// Finally merge staff online box content with its template
	eval("\$staff_online_box_l = \"" . $templates->get("adv_sidebox_staff_online_left") . "\";");
	eval("\$staff_online_box_r = \"" . $templates->get("adv_sidebox_staff_online_right") . "\";");
	
}

?>