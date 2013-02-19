<?php
/*
 * Advanced Sidebox Module
 *
 * Private Messages
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

function private_messages_asb_info()
{
	return array
	(
		"name"						=>	'Private Messages',
		"description"				=>	'Lists the user\'s PM info',
		"wrap_content"			=>	true,
		"version"						=>	"1",
		"templates"					=>	array
													(
														array
														(
															"title" => "adv_sidebox_pms",
															"template" => "
					<tr>
						<td class=\"trow1\">
							<span class=\"smalltext\">{\$lang->pms_received_new}<br /><br />
							<strong>&raquo; </strong> <strong>{\$mybb->user[\'pms_unread\']}</strong> {\$lang->pms_unread}<br />
							<strong>&raquo; </strong> <strong>{\$mybb->user[\'pms_total\']}</strong> {\$lang->pms_total}</span>
						</td>
					</tr>
															",
															"sid" => -1
														)
													)
	);
}

function private_messages_asb_build_template($settings, $template_var)
{
	// don't forget to declare your variable! will not work without this
	global $$template_var; // <-- important!

	global $db, $mybb, $templates, $lang;

	// Load global and custom language phrases
	if(!$lang->portal)
	{
		$lang->load('portal');
	}
	if(!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}

	if($mybb->user['uid'] == 0)
	{
		// guest
		$$template_var = $lang->sprintf("<tr><td class='trow1'>{$lang->adv_sidebox_pms_no_messages}</td></tr>","<a href=\"{$mybb->settings['bburl']}/member.php?action=login\">{$lang->adv_sidebox_pms_login}</a>", "<a href=\"{$mybb->settings['bburl']}/member.php?action=register\">{$lang->adv_sidebox_pms_register}</a>");
		$ret_val = false;
	}
	else
	{
		// has the user disabled pms?
		if($mybb->user['receivepms'])
		{
			// does admin allow pms?
			if(!$mybb->usergroup['canusepms'] || !$mybb->settings['enablepms'])
			{
				// if not tell them
				$$template_var = $lang->sprintf("<tr><td class='trow1'>{$lang->adv_sidebox_pms_disabled_by_admin}</td></tr>", "<a href=\"{$mybb->settings['bburl']}/usercp.php?action=options\">{$lang->adv_sidebox_pms_usercp}</a>");
				$ret_val = false;
			}
			else
			{
				// if so show the user their PM info
				$lang->pms_received_new = $lang->sprintf($lang->pms_received_new, $mybb->user['username'], $mybb->user['pms_unread']);

				eval("\$" . $template_var . " = \"" . $templates->get("adv_sidebox_pms") . "\";");
				$ret_val = true;
			}
		}
		else
		{
			// user has disabled PMs
			$$template_var = $lang->sprintf("<tr><td class='trow1'>{$lang->adv_sidebox_pms_user_disabled_pms}</td></tr>", "<a href=\"{$mybb->settings['bburl']}/usercp.php?action=options\">{$lang->adv_sidebox_pms_usercp}</a>");
			$ret_val = false;
		}
	}

	return $ret_val;
}

?>
