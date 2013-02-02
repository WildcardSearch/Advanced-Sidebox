<?php
/*
 * Advanced Sidebox Module
*
* Online Staff (meta)
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
 * Advanced Sidebox module
*/
function staff_online_box_asb_info()
{
	global $db, $lang;

	if (!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}

	return array
	(
			"name"							=>	'Online Staff',
			"description"					=>	'Display online staff members list',
			"version"						=>	"5",
			"stereo"						=>	true,
			"wrap_content"					=>	true,
			"discarded_settings"			=>	array
			(
					"adv_sidebox_staff_online_bydetail",
					"adv_sidebox_staff_online_avatarsize",
					"adv_sidebox_staff_online_bytype",
					"adv_sidebox_staff_online_hideinfo"
			),
			"settings"						=>	array
			(
					"adv_sidebox_staff_online_bydetail" => array
					(
							"sid"				=> "NULL",
							"name"				=> "adv_sidebox_staff_online_bydetail",
							"title"				=> $db->escape_string($lang->adv_sidebox_staff_online_option_bydetail_title),
							"description"		=> $db->escape_string($lang->adv_sidebox_staff_online_option_bydetail_description),
							"optionscode"		=> "text",
							"value"				=> '5',
							"disporder"			=> '492'
					),
					"adv_sidebox_staff_online_avatarsize" => array
					(
							"sid"					=> "NULL",
							"name"				=> "adv_sidebox_staff_online_avatarsize",
							"title"				=> $db->escape_string($lang->adv_sidebox_staff_online_option_avatarsize_title),
							"description"		=> $db->escape_string($lang->adv_sidebox_staff_online_option_avatarsize_description),
							"optionscode"		=> "text",
							"value"				=> '36',
							"disporder"			=> '493'
					),
					"adv_sidebox_staff_online_bytype" => array
					(
							"sid"				=> "NULL",
							"name"				=> "adv_sidebox_staff_online_bytype",
							"title"				=> $db->escape_string($lang->adv_sidebox_staff_online_option_bytype_title),
							"description"		=> $db->escape_string($lang->adv_sidebox_staff_online_option_bytype_description),
							"optionscode"		=> "yesno",
							"value"				=> '1',
							"disporder"			=> '494'
					),
					"adv_sidebox_staff_online_hideinfo" => array
					(
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
 * Advanced Sidebox module
*/
function staff_online_box_asb_build_template($settings, $template_var)
{
	$left_var = $template_var . '_l';
	$right_var = $template_var . '_r';
	global $$left_var, $$right_var;
	global $db, $mybb, $templates, $lang, $cache;

	if (!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}

	$asbos_dn = "display: none;";
	$staff_online				= Array
	(
			"count_admins"				=> 0,
			"count_supermods"			=> 0,
			"count_mods"				=> 0,
			"count_others"				=> 0,
			"count_total"				=> 0,
			"hide_info"					=> $asbos_dn,
			"hide_admins"				=> $asbos_dn,
			"hide_supermods"			=> $asbos_dn,
			"hide_mods"					=> $asbos_dn,
			"hide_others"				=> $asbos_dn,
			"hide_total"				=> $asbos_dn,
			"hide_seemore"				=> $asbos_dn,
			"hide_ifnostaff"			=> $asbos_dn,
			"hide_ifstaff"				=> $asbos_dn,
			"lang_info"					=> $lang->adv_sidebox_staff_online_nostaff,
			"lang_admin" 				=> $lang->adv_sidebox_staff_online_admin,
			"lang_supermod"				=> $lang->adv_sidebox_staff_online_supermod,
			"lang_mod"					=> $lang->adv_sidebox_staff_online_mod,
			"lang_other"				=> $lang->adv_sidebox_staff_online_other,
			"bit_trow_x"				=> 1,
			"bit_username"				=> "",
			"bit_userid"				=> "",
			"bit_username_formatted"	=> "",
			"bit_useravatar"			=> "",
			"bit_useravatar_size"		=> intval($settings['adv_sidebox_staff_online_avatarsize']['value']),
			"bit_userprofile" 			=> "",
			"bit_usertype"				=> "",
			"bit_usertype_formatted"	=> "",
			"bit_max_to_show" 			=> intval($settings['adv_sidebox_staff_online_bydetail']['value'])
	);
	if ($staff_online["bit_useravatar_size"] < 0 || $staff_online["bit_useravatar_size"] > 1000)
	{
		$staff_online["bit_useravatar_size"] = 0;
	}
	if ($staff_online["bit_max_to_show"] < 0 || $staff_online["bit_max_to_show"] > 100)
	{
		$staff_online["bit_max_to_show"] = 0;
	}
	$bits_left					= "";
	$bits_right					= "";
	$bytype_enable				= intval($settings['adv_sidebox_staff_online_bytype']['value']) & 1;

	if ($mybb->usergroup['canviewonline'])
	{
		$timesearch = TIME_NOW - $mybb->settings['wolcutoff'];
		$query = $db->query("
				SELECT s.sid, s.ip, s.uid, s.time, s.location, u.username, u.invisible, u.usergroup, u.displaygroup, u.avatar
				FROM " . TABLE_PREFIX . "sessions s
				LEFT JOIN " . TABLE_PREFIX . "users u ON (s.uid=u.uid)
				WHERE s.time > '$timesearch'
				ORDER BY u.username ASC, s.time DESC
				");

		while($user = $db->fetch_array($query))
		{
			$userpermissions = user_permissions($user['uid']);
			$this_user_is = "nostaff";
			if ($user['uid'])
			{
				if (!$user['invisible'])
				{
					if ($userpermissions['cancp'])
					{
						$staff_online["count_admins"]++;
						$staff_online["count_total"]++;
						$this_user_is = $lang->adv_sidebox_staff_online_admin;
					}
					elseif ($userpermissions['issupermod'])
					{
						$staff_online["count_supermods"]++;
						$staff_online["count_total"]++;
						$this_user_is = $lang->adv_sidebox_staff_online_supermod;
					}
					elseif ($userpermissions['canmodcp'])
					{
						$staff_online["count_mods"]++;
						$staff_online["count_total"]++;
						$this_user_is = $lang->adv_sidebox_staff_online_mod;
					}
					elseif ($userpermissions['showforumteam'])
					{
						$staff_online["count_others"]++;
						$staff_online["count_total"]++;
						$this_user_is = $lang->adv_sidebox_staff_online_other;
					}
				}
			}
			if ($this_user_is != "nostaff" && $staff_online["count_total"] <= $staff_online["bit_max_to_show"])
			{
				if ($user['avatar'])
				{
					$staff_online["bit_useravatar"] = $user['avatar'];
				}
				else
				{
					$staff_online["bit_useravatar"] = $mybb->settings['bburl']."/images/default_avatar.gif";
				}
				$staff_online["bit_userprofile"] = $mybb->settings['bburl']."/member.php?action=profile&amp;uid=".$user['uid'];
				$staff_online["bit_username"] = $user['username'];
				$staff_online["bit_userid"] = $user['uid'];
				$staff_online["bit_usertype"] = $this_user_is;
				$staff_online["bit_username_formatted"] = format_name($staff_online["bit_username"], $user['usergroup'], $user['displaygroup']);
				$staff_online["bit_usertype_formatted"] = format_name($staff_online["bit_usertype"], $user['usergroup'], $user['displaygroup']);
				$staff_online["bit_trow_x"] ^= 3;

				eval("\$bits_left .= \"" . $templates->get("adv_sidebox_staff_online_bit_left") . "\";");
				eval("\$bits_right .= \"" . $templates->get("adv_sidebox_staff_online_bit_right") . "\";");
			}
		}
	}

	if ($staff_online["count_total"])
	{
		$staff_online["lang_info"] = $lang->sprintf($lang->adv_sidebox_staff_online_staff,$staff_online["count_total"]);
		if ($bytype_enable)
		{
			if ($staff_online["count_admins"])
			{
				$staff_online["hide_admins"]	= "";
			}
			if ($staff_online["count_supermods"])
			{
				$staff_online["hide_supermods"]	= "";
			}
			if ($staff_online["count_mods"])
			{
				$staff_online["hide_mods"]		= "";
			}
			if ($staff_online["count_others"])	{
				$staff_online["hide_others"]	= "";
			}
		}
		if ($staff_online["count_total"])
		{
			$staff_online["hide_total"]		= "";
		}
		if ($staff_online["count_total"] > $staff_online["bit_max_to_show"]) {
			$staff_online["hide_seemore"] = "";
		}
	}

	if (!$staff_online["count_total"])
	{
		$staff_online["hide_info"] = "";
	}
	elseif ((intval($settings['adv_sidebox_staff_online_hideinfo']['value']) & 1))
	{
		$staff_online["hide_info"] = "";
	}
	if ($staff_online["count_total"])
	{
		$staff_online["hide_ifnostaff"] = "";
	}
	else
	{
		$staff_online["hide_ifstaff"] = "";
	}

	eval("\$" . $left_var . " = \"" . $templates->get("adv_sidebox_staff_online_left") . "\";");
	eval("\$" . $right_var . " = \"" . $templates->get("adv_sidebox_staff_online_right") . "\";");
}
?>