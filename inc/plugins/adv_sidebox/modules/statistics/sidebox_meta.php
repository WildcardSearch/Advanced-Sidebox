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

/*
 * This function is required. It is used by acp_functions to add and describe your new sidebox.
 */
function statistics_add_type(&$box_types)
{
	/*
	 * just add your template variable to the $box_types array
	 *
	 * $box_types[''] <-- 	enter your template variable. it must be the same as the name of your add-on module enclosed in curly brackets {} and with a $
	 * = ''; <-- enter the description/name of your add-on.
	 */
	 $box_types['{$statistics}'] = 'Statistics';
}

/*
 * This function is required. It is used by adv_sidebox.php to display the custom content in your sidebox.
 */
function statistics_build_template(&$box_types)
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
	
	/*
	 * check if the custom box type has been used by admin
	 *
	 * this is important because if the box hasn't been used it would be a waste to go any further
	*/
	// Private messages box
	if($box_types['{$statistics}'])
	{
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
}

?>