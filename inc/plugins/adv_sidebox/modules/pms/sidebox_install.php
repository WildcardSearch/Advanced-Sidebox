<?php
/*
 * Advanced Sidebox Module
 *
 * Private Messages (install)
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
 * This function is required. If it is missing the add-on will not install.
 */
function pms_is_installed()
{
	global $db;
	
	// works just like a plugin
	$query = $db->simple_select('templates', 'title', "title='adv_sidebox_pms'");
	return $db->num_rows($query);
}

/*
 * This function is required. Make your mods here.
 */
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
 * This function is required. Clean up after yourself.
 */
function pms_uninstall()
{
	global $db;
	
	// delete all the boxes of this custom type and the template as well
	$db->query("DELETE FROM " . TABLE_PREFIX . "sideboxes WHERE box_type='" . $db->escape_string('{$pms}') . "'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='adv_sidebox_pms'");
}

?>
