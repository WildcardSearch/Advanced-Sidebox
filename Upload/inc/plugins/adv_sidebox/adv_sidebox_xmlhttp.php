<?php
/*
 * This file contains XMLHTTP(AJAX) routines for the plugin modules
 *
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright © 2013 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * Check out this project on GitHub: http://wildcardsearch.github.com/Advanced-Sidebox
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses
 */

// register as MyBB
define('IN_MYBB', 1);
define("NO_ONLINE", 1);
define('THIS_SCRIPT', 'adv_sidebox_xmlhttp.php');
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
		require_once 'adv_sidebox_functions.php';
		require_once 'adv_sidebox_classes.php';

		// attempt to load the module and side box requested
		$this_module = new Addon_type($mybb->input['box_type']);
		$this_sidebox = new Sidebox($box_id);

		// we need both objects to continue
		if($this_module instanceof Addon_type && $this_module->valid && $this_sidebox instanceof Sidebox && $this_sidebox->valid)
		{
			// get the individual side box settings to pass to the appropriate module
			$these_settings = $this_sidebox->get_settings();

			// send the correct width for the column where the side box is
			if($this_sidebox->get_position())
			{
				$this_column_width = (int) $mybb->settings['adv_sidebox_width_right'];
			}
			else
			{
				$this_column_width = (int) $mybb->settings['adv_sidebox_width_left'];
			}

			// then call the module's AJAX method and echo its return value
			echo($this_module->do_xmlhttp($dateline, $these_settings, $this_column_width));
		}
	}
}
exit();

?>
