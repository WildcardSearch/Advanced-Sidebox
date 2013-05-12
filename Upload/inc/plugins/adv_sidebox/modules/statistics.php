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
		"name"							=>	'Statistics',
		"description"					=>	'Forum statistics and figures',
		"wrap_content"				=>	true,
		"version"						=>	"1.1",
		"settings"						=>	array
													(
														"format_username"	=>	array
														(
															"sid"					=> "NULL",
															"name"				=> "format_username",
															"title"				=> "Format last username?",
															"description"		=> "(may use another query)",
															"optionscode"	=> "yesno",
															"value"				=> '0'
														)
													),
		"templates"					=>	array
													(
														array
														(
															        "title" => "adv_sidebox_statistics",
																	"template" => "
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
																	",
																	"sid" => -1
														)
													)
	);
}

/*
 * This function is required. It is used by adv_sidebox.php to display the custom content in your sidebox.
 */
function statistics_asb_build_template($settings, $template_var)
{
	// don't forget to declare your variable! will not work without this
	global $$template_var; // <-- important!

	global $mybb, $cache, $templates, $lang;

	// Load global and custom language phrases
	if(!$lang->portal)
	{
		$lang->load('portal');
	}
	if(!$lang->adv_sidebox)
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
		if($settings['format_username']['value'])
		{
			$last_user = get_user($statistics['lastuid']);
			$last_username = format_name($last_user['username'], $last_user['usergroup'], $last_user['displaygroup']);
		}
		else
		{
			$last_username = $statistics['lastusername'];
		}
	}
	$newestmember = build_profile_link($last_username, $statistics['lastuid']);

	eval("\$" . $template_var . " = \"" . $templates->get("adv_sidebox_statistics") . "\";");
	return true;
}

?>
