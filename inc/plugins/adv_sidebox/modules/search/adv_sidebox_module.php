<?php
/*
 * Advanced Sidebox Module
 *
 * Search (meta)
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
function search_asb_info()
{
	return array
	(
		"name"				=>	'Search',
		"description"		=>	'simple textbox and button',
		"stereo"			=>	false
	);
}

function search_asb_is_installed()
{
	global $db;
	
	// works just like a plugin
	$query = $db->simple_select('templates', 'title', "title='adv_sidebox_search'");
	return $db->num_rows($query);
}

/*
 * This function is required. Make your mods here.
 */
function search_asb_install()
{
	global $db;
	
	// the search template
	$template_6 = array(
        "title" => "adv_sidebox_search",
        "template" => "<table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\">
	<tr>
		<td class=\"thead\"><strong>{\$lang->search_forums}</strong></td>
	</tr>
	<tr>
		<td class=\"trow1\" align=\"center\">
			<form method=\"post\" action=\"{\$mybb->settings[\'bburl\']}/search.php\">
				<input type=\"hidden\" name=\"action\" value=\"do_search\" />
				<input type=\"hidden\" name=\"postthread\" value=\"1\" />
				<input type=\"hidden\" name=\"forums\" value=\"all\" />
				<input type=\"hidden\" name=\"showresults\" value=\"threads\" />
				<input type=\"text\" class=\"textbox\" name=\"keywords\" value=\"\" />
				{\$gobutton}
			</form><br />
		<span class=\"smalltext\">
		(<a href=\"{\$mybb->settings[\'bburl\']}/search.php\">{\$lang->advanced_search}</a>)
		</span>
	</td>
	</tr>
</table>
<br />",
        "sid" => -1
    );
	$db->insert_query("templates", $template_6);
}

/*
 * This function is required. Clean up after yourself.
 */
function search_asb_uninstall()
{
	global $db;
	
	// delete all the boxes of this custom type and the template as well
	$db->query("DELETE FROM " . TABLE_PREFIX . "sideboxes WHERE box_type='" . $db->escape_string('search') . "'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='adv_sidebox_search'");
}

/*
 * This function is required. It is used by adv_sidebox.php to display the custom content in your sidebox.
 */
function search_asb_build_template()
{
	// don't forget to declare your variable! will not work without this
	global $search; // <-- important!
	
	global $db, $mybb, $templates, $lang, $gobutton;
	
	// Load global and custom language phrases
	if (!$lang->portal)
	{
		$lang->load('portal');
	}
	if (!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}
	
	eval("\$search = \"" . $templates->get("adv_sidebox_search") . "\";");
}

?>