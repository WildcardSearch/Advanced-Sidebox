<?php
/*
 * Advanced Sidebox Module
 *
 * Statistics (meta)
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

function statistics_asb_info()
{
	return array
	(
		"name"				=>	'Statistics',
		"description"		=>	'forum statistics and figures',
		"stereo"			=>	false
	);
}

function statistics_asb_is_installed()
{
	global $db;
	
	$query = $db->simple_select('templates', 'title', "title='adv_sidebox_statistics'");
	return $db->num_rows($query);
}

/*
 * This function is required. Make your mods here.
 */
function statistics_asb_install()
{
	global $db;
	
	// the statistics template
	$template_5 = array(
        "title" => "adv_sidebox_statistics",
        "template" => "<table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\">
	<tr>
		<td class=\"thead\"><strong>{\$lang->forum_stats}</strong></td>
	</tr>
	<tr>
		<td class=\"trow1\">
			<span class=\"smalltext\">
			<strong>&raquo; </strong>{\$lang->num_members} {\$statistics[\'numusers\']}<br />
			<strong>&raquo; </strong>{\$lang->latest_member} {\$newestmember}<br />
			<strong>&raquo; </strong>{\$lang->num_threads} {\$statistics[\'numthreads\']}<br />
			<strong>&raquo; </strong>{\$lang->num_posts} {\$statistics[\'numposts\']}
			<br /><br /><a href=\"{\$mybb->settings[\'bburl\']}/stats.php\">{\$lang->full_stats}</a>
			</span>
		</td>
	</tr>
</table>
<br />",
        "sid" => -1
    );
	$db->insert_query("templates", $template_5);
}

/*
 * This function is required. Clean up after yourself.
 */
function statistics_asb_uninstall()
{
	global $db;
	
	// delete all the boxes of this custom type and the template as well
	$db->query("DELETE FROM " . TABLE_PREFIX . "sideboxes WHERE box_type='" . $db->escape_string('{$statistics}') . "'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='adv_sidebox_statistics'");
}

/*
 * This function is required. It is used by adv_sidebox.php to display the custom content in your sidebox.
 */
function statistics_asb_build_template()
{
	// don't forget to declare your variable! will not work without this
	global $statistics; // <-- important!
	
	global $mybb, $cache, $templates, $lang;
	
	// Load global and custom language phrases
	if (!$lang->portal)
	{
		$lang->load('portal');
	}
	if (!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}
	
	// get forum statistics
	$statistics = $cache->read("stats");
	$statistics['numthreads'] = my_number_format($statistics['numthreads']);
	$statistics['numposts'] = my_number_format($statistics['numposts']);
	$statistics['numusers'] = my_number_format($statistics['numusers']);
	
	if(!$statistics['lastusername'])
	{
		$newestmember = "<strong>" . $lang->no_one . "</strong>";
	}
	else
	{
		$newestmember = build_profile_link($statistics['lastusername'], $statistics['lastuid']);
	}
	eval("\$statistics = \"" . $templates->get("adv_sidebox_statistics") . "\";");
}

?>