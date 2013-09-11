<?php
/*
 * Plug-in Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2013 WildcardSearch
 * http://www.wildcardsworld.com
 *
 * this file contains XMLHTTP (AJAX) routines for the plug-in modules
 */

// register as MyBB
define('IN_MYBB', 1);
define("NO_ONLINE", 1);
define('THIS_SCRIPT', 'xmlhttp.php');
require_once "../../../global.php";

// leaving it open to add more functionality later
if($mybb->input['action'] == 'do_module')
{
	if(isset($mybb->input['box_type']) && isset($mybb->input['dateline']))
	{
		// the name attribute for the side box's container table is formatted: (id)_main_(dateline) so we can break it down and get all the info we need to identify box and module
		$dateline_array = explode("_", $mybb->input['dateline']);
		$dateline = $dateline_array[count($dateline_array) - 1];
		$box_id = (int) $dateline_array[0];

		// get the ASB core stuff
		require_once 'functions_addon.php';
		require_once 'classes/xmlhttp.php';

		// attempt to load the module and side box requested
		$module = new Addon_type($mybb->input['box_type']);
		$sidebox = new Sidebox($box_id);

		// we need both objects to continue
		if($module instanceof Addon_type && $module->is_valid() && $sidebox instanceof Sidebox && $sidebox->is_valid())
		{
			// get the individual side box settings to pass to the appropriate module
			$settings = $sidebox->get('settings');

			// send the correct width for the column where the side box is
			if($sidebox->get('position'))
			{
				$width = (int) $mybb->settings['asb_width_right'];
			}
			else
			{
				$width = (int) $mybb->settings['asb_width_left'];
			}

			// then call the module's AJAX method and echo its return value
			echo($module->do_xmlhttp($dateline, $settings, $width));
		}
	}
}
exit();

?>
