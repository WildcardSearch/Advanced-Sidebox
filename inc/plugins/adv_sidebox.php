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

global $settings;

// hook only if necessary
if($settings['adv_sidebox_on_index'])
{
	$plugins->add_hook("index_start", "adv_sidebox_start");
}

if($settings['adv_sidebox_on_forumdisplay'])
{
	$plugins->add_hook("forumdisplay_start", "adv_sidebox_start");
}

if($settings['adv_sidebox_on_showthread'])
{
	$plugins->add_hook("showthread_start", "adv_sidebox_start");
}

if($settings['adv_sidebox_portal_replace'])
{
	$plugins->add_hook("portal_start", "adv_sidebox_start");
}

/*
 * adv_sidebox_start()
 *
 * main routine. loads and displays any sideboxes on the script specified by the sidebox info
 *
 * Hooks: index_start, forumdisplay_start, showthread_start, portal_start (disabled from ACP settings)
 *
 * Check both admin and user settings and if applicable display the sideboxes.
 */
function adv_sidebox_start()
{
	global $mybb, $templates, $plugins;

	// will need all classes and functions here
	require_once MYBB_ROOT . 'inc/plugins/adv_sidebox/adv_sidebox_classes.php';
	require_once MYBB_ROOT . 'inc/plugins/adv_sidebox/adv_sidebox_functions.php';

	// don't waste execution if unnecessary
	if(!adv_sidebox_do_checks())
	{
		return false;
	}

	$adv_sidebox = new Sidebox_handler(THIS_SCRIPT);

	// no boxes
	if(!$adv_sidebox->boxes_to_show)
	{
		// get out
		return false;
	}

	// width
	$adv_sidebox_width_left = (int) $mybb->settings['adv_sidebox_width_left'];
	$adv_sidebox_width_right = (int) $mybb->settings['adv_sidebox_width_right'];

	// display boxes (unless we're on portal)
	if($mybb->settings['adv_sidebox_on_' . $adv_sidebox->script_base_name] && in_array(THIS_SCRIPT, array("index.php", "forumdisplay.php", "showthread.php")))
	{
		// prepare left and right side box column if there is content
		if($adv_sidebox->left_boxes)
		{
			$left_insert = '
		<!-- start: adv_sidebox left column -->
		<td width="' . $adv_sidebox_width_left . '" valign="top">' . $adv_sidebox->left_boxes . '
		</td>
		<!-- end: adv_sidebox left column -->';
		}
		if($adv_sidebox->right_boxes)
		{
			$right_insert = '
		<!-- start: adv_sidebox right column -->
		<td width="' . $adv_sidebox_width_right . '" valign="top">' . $adv_sidebox->right_boxes . '
		</td>
		<!-- end: adv_sidebox right column -->';
		}

		// if either column has content then perform the insertion
		if($adv_sidebox->left_boxes || $adv_sidebox->right_boxes)
		{
			$templates->cache[$adv_sidebox->script_base_name] = str_replace('{$header}', '{$header}
<!-- start: adv_sidebox -->
<table width="100%" border="0" cellspacing="5">
	<tr>' . $left_insert . '
		<!-- start: adv_sidebox middle column (page contents of ' . THIS_SCRIPT . ') -->
		<td width="auto" valign="top">', $templates->cache[$adv_sidebox->script_base_name]);
			$templates->cache[$adv_sidebox->script_base_name] = str_replace('{$footer}','
		</td>
		<!-- end: adv_sidebox middle column (page contents of ' . THIS_SCRIPT . ') -->' . $right_insert . '
	</tr>
</table>
<!-- end adv_sidebox -->
{$footer}', $templates->cache[$adv_sidebox->script_base_name]);
		}
	}
	// display additional boxes on portal (if 'Replace Portal Boxes With Custom' is set to yes)
	elseif($mybb->settings['adv_sidebox_portal_replace'] && THIS_SCRIPT == 'portal.php' && ($adv_sidebox->left_boxes || $adv_sidebox->right_boxes))
	{
		$this_template = '<html>
	<head>
		<title>{$mybb->settings[\'bbname\']}</title>
		{$headerinclude}
	</head>
	<body>
		{$header}
		<!-- start: adv_sidebox -->
		<table width="100%" cellspacing="0" cellpadding="{$theme[\'tablespace\']}" border="0">
			<tr>';

		if($adv_sidebox->left_boxes)
		{
			$this_template .= '
				<!-- start: adv_sidebox left column -->
				<td valign="top" width="' . $adv_sidebox_width_left . '"><div style="max-width: ' . $adv_sidebox_width_left . 'px min-width: ' . $adv_sidebox_width_left . 'px">' . $adv_sidebox->left_boxes . '</div></td>
				<td>&nbsp;</td>
				<!-- end: adv_sidebox left column -->';
		}

		$this_template .= '
				<!-- start: adv_sidebox middle column (page contents of ' . THIS_SCRIPT . ') -->
				<td style="max-width:' . (1000 - ($adv_sidebox_width_right + $adv_sidebox_width_left)) . 'px;"><div style="max-width: ' . (1000 - ($adv_sidebox_width_right + $adv_sidebox_width_left)) . 'px min-width: ' . (1000 - ($adv_sidebox_width_right + $adv_sidebox_width_left)) . 'px">{$announcements}</div></td>
				<td>&nbsp;</td>
				<!-- end: adv_sidebox middle column (page contents of ' . THIS_SCRIPT . ') -->';

		if($adv_sidebox->right_boxes)
		{
			$this_template .= '
				<!-- start: adv_sidebox right column -->
				<td valign="top" width="' . $adv_sidebox_width_right . '"><div style="max-width: ' . $adv_sidebox_width_right . 'px min-width: ' . $adv_sidebox_width_right . 'px">' . $adv_sidebox->right_boxes . '</div></td>
				<!-- end: adv_sidebox right column -->';
		}

		$this_template .= '
			</tr>
		</table>
		<!-- end adv_sidebox -->
		{$footer}
	</body>
</html>';

		$templates->cache['portal'] = $this_template;
	}

	// build all the templates, producing the content through modules and custom boxes
	$adv_sidebox->build_all_templates();
}

// Hooks for the User CP routine.
$plugins->add_hook("usercp_options_end", "adv_sidebox_options");
$plugins->add_hook("usercp_do_options_end", "adv_sidebox_options");

/*
 * adv_sidebox_options()
 *
 * Hooks: usercp_options_end, usercp_do_options_end
 *
 * Add a checkbox to the User CP under Other Options to toggle the sideboxes
 */
function adv_sidebox_options()
{
	global $db, $mybb, $templates, $user, $lang;

	if(!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}

    // If the form is being submitted save the users choice.
	if($mybb->request_method == "post")
    {
		$update_array = array(
			"show_sidebox" => intval($mybb->input['showsidebox'])
		);

        $db->update_query("users", $update_array, "uid = '" . $user['uid'] . "'");
    }

	// Get the users setting and display the checkbox accordingly (checked/unchecked)
	$query = $db->simple_select("users", "show_sidebox", "uid = '".$user['uid']."' AND show_sidebox='1'", array("order_dir" => 'DESC'));

	if($db->num_rows($query) > 0)
	{
		// checked
		$checked = 'checked="checked" ';
	}

	$usercp_option = '<td valign="top" width="1"><input type="checkbox" class="checkbox" name="showsidebox" id="showsidebox" value="1" ' . $checked . '/></td><td><span class="smalltext"><label for="showsidebox">' . $lang->adv_sidebox_show_sidebox . '</label></span></td></tr><tr><td valign="top" width="1"><input type="checkbox" class="checkbox" name="showredirect"';

    // Update the template cache
	$find = '<td valign="top" width="1"><input type="checkbox" class="checkbox" name="showredirect"';
    $templates->cache['usercp_options'] = str_replace($find, $usercp_option, $templates->cache['usercp_options']);
}

?>
