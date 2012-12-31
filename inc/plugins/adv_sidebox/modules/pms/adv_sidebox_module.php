<?php
/*
 * Advanced Sidebox Module
 *
 * Private Messages (meta)
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
function pms_info()
{
	return array
	(
		"name"				=>	'Private Messages',
		"description"		=>	'lists the user\'s PM info',
		"stereo"			=>	false
	);
}

function pms_is_installed()
{
	global $db;
	
	// works just like a plugin
	$query = $db->simple_select('templates', 'title', "title='adv_sidebox_pms'");
	return $db->num_rows($query);
}

function pms_install()
{
	global $db;
	
	// the pm template
	$template_4 = array(
        "title" => "adv_sidebox_pms",
        "template" => "<table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\">
	<tr>
		<td class=\"thead\"><strong><a href=\"{\$mybb->settings[\'bburl\']}/private.php\">{\$lang->private_messages}</a></strong></td>
	</tr>
	<tr>
		<td class=\"trow1\">
			<span class=\"smalltext\">{\$lang->pms_received_new}<br /><br />
			<strong>&raquo; </strong> <strong>{\$messages[\'pms_unread\']}</strong> {\$lang->pms_unread}<br />
			<strong>&raquo; </strong> <strong>{\$messages[\'pms_total\']}</strong> {\$lang->pms_total}</span>
		</td>
	</tr>
</table>
<br />",
        "sid" => -1
    );
	$db->insert_query("templates", $template_4);
}

/*
 * Clean up after yourself.
 */
function pms_uninstall()
{
	global $db;
	
	// delete all the boxes of this custom type and the template as well
	$db->query("DELETE FROM " . TABLE_PREFIX . "sideboxes WHERE box_type='" . $db->escape_string('pms') . "'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='adv_sidebox_pms'");
}

/*
 * This function is required. It is used by adv_sidebox.php to display the custom content in your sidebox.
 */
function pms_build_template()
{
	// don't forget to declare your variable! will not work without this
	global $pms; // <-- important!
	
	global $db, $mybb, $templates, $lang;
	
	// Load global and custom language phrases
	if (!$lang->portal)
	{
		$lang->load('portal');
	}
	if (!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}
	
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
		eval("\$pms = \"" . $templates->get("adv_sidebox_pms") . "\";");
	}
}

?>