<?php
/*
 * Advanced Sidebox Module
 *
 * Search
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

function search_box_asb_info()
{
	return array
	(
		"name"				=>	'Search',
		"description"		=>	'Simple textbox and button',
		"wrap_content"	=>	true,
		"version"			=>	"1",
		"templates"					=>	array
													(
														array
														(
															"title" => "adv_sidebox_search",
															"template" => "
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
															",
															"sid" => -1
														)
													)
	);
}

/*
 * This function is required. It is used by adv_sidebox.php to display the custom content in your sidebox.
 */
function search_box_asb_build_template($settings, $template_var)
{
	// don't forget to declare your variable! will not work without this
	global $$template_var; // <-- important!

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

	eval("\$" . $template_var . " = \"" . $templates->get("adv_sidebox_search") . "\";");
}

?>
