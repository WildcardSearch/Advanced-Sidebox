<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2013 WildcardSearch
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

// disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

// modules will check this
define("ADV_SIDEBOX", true);

// used by all module routines
define("ADV_SIDEBOX_MODULES_DIR", MYBB_ROOT. "inc/plugins/adv_sidebox/modules");

// Load the install/admin routines only if in ACP.
if(defined("IN_ADMINCP"))
{
    require_once MYBB_ROOT . "inc/plugins/adv_sidebox/acp_functions.php";
}

// only add the necessary hooks
adv_sidebox_add_forum_hooks();

/*
 * adv_sidebox_start()
 *
 * main routine. loads and displays any sideboxes on the script specified by the sidebox info
 *
 * Hooks: dependent upon ACP settings, one hook for each script and the hook is only added for that particular script
 */
function adv_sidebox_start()
{
	global $mybb;

	// a few general functions
	require_once MYBB_ROOT . 'inc/plugins/adv_sidebox/adv_sidebox_functions.php';

	// don't waste execution if unnecessary
	if(!adv_sidebox_do_checks())
	{
		return false;
	}

	// side box, side box type and handler classes
	require_once MYBB_ROOT . 'inc/plugins/adv_sidebox/adv_sidebox_classes.php';

	/*
	 * load the Sidebox_handler object, which in turn loads all the sideboxes, add-on modules and/or custom boxes we need for this particular script
	 */
	$adv_sidebox = new Sidebox_handler();

	// no boxes
	if(!$adv_sidebox->boxes_to_show)
	{
		// get out
		return false;
	}

	/*
	 * build all the templates, producing the content through modules and/or custom boxes
	 */
	$adv_sidebox->build_all_templates();

	// load the template handler class definitions
	require_once MYBB_ROOT . 'inc/plugins/adv_sidebox/adv_sidebox_class_template_handler.php';

	/*
	 * attempt to load a handler using a static method to select the correct class based on THIS_SCRIPT constant
	 */
	$template_handler = Template_handlers::get_template_handler($adv_sidebox->left_boxes, $adv_sidebox->right_boxes, (int) $mybb->settings['adv_sidebox_width_left'], (int) $mybb->settings['adv_sidebox_width_right']);

	// if we have a valid template handler object
	if($template_handler instanceof Template_handler)
	{
		// then edit the templates
		$template_handler->make_edits();
	}
}

/*
 * add only the appropriate hooks
 */
function adv_sidebox_add_forum_hooks()
{
	global $settings, $plugins;

	// build the hook and setting names from the script
	$base_name = substr(THIS_SCRIPT, 0, strlen(THIS_SCRIPT) - 4);
	$hook_name = $base_name . '_start';
	$setting_name = 'adv_sidebox_on_' . $base_name;

	// exceptions
	switch(THIS_SCRIPT)
	{
		case 'portal.php':
			$setting_name = 'adv_sidebox_portal_replace';
			break;
		case 'member.php':
			$hook_name = 'member_profile_start';
			break;
	}

	// if the setting for this script is set to yes . . .
	if($settings[$setting_name])
	{
		// add the hook
		$plugins->add_hook($hook_name, "adv_sidebox_start");
	}

	// Hooks for the User CP routine.
	$plugins->add_hook("usercp_options_end", "adv_sidebox_options");
	$plugins->add_hook("usercp_do_options_end", "adv_sidebox_options");
}

/*
 * adv_sidebox_options()
 *
 * Hooks: usercp_options_end, usercp_do_options_end
 *
 * add a checkbox to the User CP under Other Options to toggle the sideboxes
 */
function adv_sidebox_options()
{
	global $db, $mybb, $templates, $user, $lang;

	if(!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}

    // if the form is being submitted save the users choice.
	if($mybb->request_method == "post")
    {
		$update_array = array(
			"show_sidebox" => (int) $mybb->input['showsidebox']
		);

        $db->update_query("users", $update_array, "uid = '" . $user['uid'] . "'");
    }

	// don't be silly and waste a query :p (thanks Destroy666)
	if($mybb->user['show_sidebox'] > 0)
	{
		// checked
		$checked = 'checked="checked" ';
	}

	$usercp_option = '<td valign="top" width="1"><input type="checkbox" class="checkbox" name="showsidebox" id="showsidebox" value="1" ' . $checked . '/></td><td><span class="smalltext"><label for="showsidebox">' . $lang->adv_sidebox_show_sidebox . '</label></span></td></tr><tr><td valign="top" width="1"><input type="checkbox" class="checkbox" name="showredirect"';

    // update the template cache
	$find = '<td valign="top" width="1"><input type="checkbox" class="checkbox" name="showredirect"';
    $templates->cache['usercp_options'] = str_replace($find, $usercp_option, $templates->cache['usercp_options']);
}

?>
