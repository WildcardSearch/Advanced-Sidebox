<?php
/*
 * Advanced Sidebox Module
 *
 * Latest Threads (install)
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
function latest_threads_is_installed()
{
	global $db;
	
	// works just like a plugin
	$query = $db->simple_select('templates', 'title', "title='adv_sidebox_latest_threads'");
	return $db->num_rows($query);
}

/*
 * This function is required. Make your mods here.
 */
function latest_threads_install()
{
	global $db;
	
	// latest threads parent template
	$template_9 = array(
        "title" => "adv_sidebox_latest_threads",
        "template" => "<table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\">
	<tr>
		<td class=\"thead\"><strong>{\$lang->latest_threads}</strong></td>
	</tr>
	{\$threadlist}
</table>
<br />",
        "sid" => -1
    );
	$db->insert_query("templates", $template_9);
	
	// latest threads child template
	$template_10 = array(
        "title" => "adv_sidebox_latest_threads_thread",
        "template" => "<tr>
<td class=\"{\$altbg}\">
	<a href=\"{\$mybb->settings[\'bburl\']}/{\$thread[\'newpostlink\']}\" title=\"{\$lang->adv_sidebox_gotounread}\"><img src=\"{\$mybb->settings[\'bburl\']}/images/jump.gif\" alt=\"jump\"/></a>&nbsp;<strong><a href=\"{\$mybb->settings[\'bburl\']}/{\$thread[\'threadlink\']}\">{\$thread[\'subject\']}</a></strong>
	<span class=\"smalltext\"><br />
		<a href=\"{\$thread[\'lastpostlink\']}\">{\$lang->latest_threads_lastpost}</a> {\$lastposterlink}<br />
		{\$lastpostdate} {\$lastposttime}<br />
		<strong>&raquo; </strong>{\$lang->latest_threads_replies} {\$thread[\'replies\']}<br />
		<strong>&raquo; </strong>{\$lang->latest_threads_views} {\$thread[\'views\']}
	</span>
</td>
</tr>",
        "sid" => -1
    );
	$db->insert_query("templates", $template_10);
}

/*
 * This function is required. Clean up after yourself.
 */
function latest_threads_uninstall()
{
	global $db;
	
	// delete all the boxes of this type and the template as well
	$db->query("DELETE FROM " . TABLE_PREFIX . "sideboxes WHERE box_type='" . $db->escape_string('{$latest_threads}') . "'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='adv_sidebox_latest_threads'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='adv_sidebox_latest_threads_thread'");
}

?>
