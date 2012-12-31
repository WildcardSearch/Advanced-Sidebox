<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x v1.0
 * Copyright © 2012 Wildcard
 * http://www.rantcentralforums.com
 *
 * Check out this project on GitHub: https://github.com/WildcardSearch/Advanced-Sidebox
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

 // Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

// other modules will check this
define("ADV_SIDEBOX", true);

// used by all module routines
define("ADV_SIDEBOX_MODULES_DIR", MYBB_ROOT. "inc/plugins/adv_sidebox/modules");

// Load the install/admin routines only if in Admin CP.
if(defined("IN_ADMINCP"))
{
    require_once MYBB_ROOT . "inc/plugins/adv_sidebox/acp_functions.php";
}

global $settings;

// Hooks for the main routine
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

// Check both admin and user settings and if applicable display the sideboxes.
function adv_sidebox_start()
{
	global $mybb, $db, $lang, $templates, $theme, $plugins;

	// If the ACP settings indicate that the current script doesn't display the sideboxes then there is no need to go any further.
	if(THIS_SCRIPT == 'index.php' && !$mybb->settings['adv_sidebox_on_index']  || THIS_SCRIPT == 'forumdisplay.php' && !$mybb->settings['adv_sidebox_on_forumdisplay'] || THIS_SCRIPT == 'showthread.php' && !$mybb->settings['adv_sidebox_on_showthread'])
	{
		return false;
	}

	// If the EXCLUDE list isn't empty . . .
	if(is_array(unserialize($mybb->settings['adv_sidebox_exclude_theme'])))
	{
		// . . . and this theme is listed.
		if(in_array($theme['tid'], unserialize($mybb->settings['adv_sidebox_exclude_theme'])))
		{
			// no sidebox
			return false;
		}
	}
	
	// If the current user is not a guest and has disabled the sidebox display in UCP then do not display the sideboxes
	if($mybb->user['uid'] != 0)
	{
		if ($mybb->user['show_sidebox'] == 0)
		{
			return false;
		}
	}

	// Load global and custom language phrases
	if (!$lang->portal)
	{
		$lang->load('portal');
	}
	if (!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}

	// temporary storage used to sort boxes
	$all_boxes = array();
	
	// Look for all sideboxes (if any)
	$query = $db->simple_select('sideboxes', 'id, box_type, position, content', '', array("order_by" => 'display_order', "order_dir" => 'ASC'));
		
	// if there are sideboxes . . .
	if($db->num_rows($query) > 0)
	{
		while($this_box = $db->fetch_array($query))
		{
			// add them all to this array
			$all_boxes[] = $this_box;
		}
	}
	else
	{
		return false;
	}
	
	// There is only one internal box type (custom),
	// but nox types can come from saved custom boxes . . .
	$custom_box_list = get_all_custom_box_types_content();
	
	// . . . or modules/plugins
	$boxes_info = get_installed_box_types();

	// loop through the boxes and sort them
	foreach($all_boxes as $this_box)
	{	
		// if this is a user-defined box . . .
		if($custom_box_list[$this_box['box_type']])
		{
			// . . . then use the custom content as a replacement
			$content = $custom_box_list[$this_box['box_type']];
		}
		// if this is a custom box . . .
		elseif($this_box['box_type'] == 'custom_box')
		{
			// . . . then use the custom content as a replacement
			$content = $this_box['content'];
		}
		else
		{
			// 'stereo'boxes are width-depepndent and so have to be seperated into 'channels'
			if($boxes_info[$this_box['box_type']]['stereo'] == true)
			{
				if((int) $this_box['position'] > 0)
				{
					$content = '{$' . $this_box['box_type'] . '_r}';
				}
				else
				{
					$content = '{$' . $this_box['box_type'] . '_l}';
				}
			}
			else
			{
				// mono boxes appear the same for both sides
				$content = '{$' . $this_box['box_type'] . '}';
			}
		}
		
		// 0 = left, otherwise right
		if((int) $this_box['position'] > 0)
		{
			$right_boxes .= $content;
		}
		else
		{
			$left_boxes .= $content;
		}
		
		// we'll check this array later to reduce wasted code
		// no need to parse templates if the box_type is unused
		$box_types[$this_box['box_type']] = true;
	}
	
	// no boxes?
	if(!$left_boxes && !$right_boxes)
	{
		return false;
	}
	
	// width
	$adv_sidebox_width_left = (int) $mybb->settings['adv_sidebox_width_left'];
	$adv_sidebox_width_right = (int) $mybb->settings['adv_sidebox_width_right'];

	// Display boxes on index
	if($mybb->settings['adv_sidebox_on_index'] && THIS_SCRIPT == 'index.php')
	{
		if($left_boxes && !$right_boxes)
		{
			$templates->cache['index'] = str_replace('{$header}', '{$header}<table width="100%" border="0" cellspacing="5"><tr><td width="'.$adv_sidebox_width_left.'" valign="top">' . $left_boxes . '</td><td width="auto" valign="top">',$templates->cache['index']);
			$templates->cache['index'] = str_replace('{$footer}','{$footer}</td></tr></table></div></div>',$templates->cache['index']);
		}
		elseif(!$left_boxes && $right_boxes)
		{
			$templates->cache['index'] = str_replace('{$header}', '{$header}<table width="100%" border="0" cellspacing="5"><tr><td width="auto" valign="top">',$templates->cache['index']);
			$templates->cache['index'] = str_replace('{$footer}','{$footer}</td><td width="'.$adv_sidebox_width_right.'" valign="top">'.$right_boxes.'</td></tr></table>',$templates->cache['index']);
		}
		elseif($left_boxes && $right_boxes)
		{
			$templates->cache['index'] = str_replace('{$header}', '{$header}<table width="100%" border="0" cellspacing="5"><tr><td width="'.$adv_sidebox_width_left.'" valign="top">' . $left_boxes . '</td><td width="auto" valign="top">',$templates->cache['index']);
			$templates->cache['index'] = str_replace('{$footer}','{$footer}</td></td><td width="'.$adv_sidebox_width_right.'" valign="top">'.$right_boxes.'</td></tr></table></div></div>',$templates->cache['index']);
		}
	}

	//Display boxes on forumdisplay
	if($mybb->settings['adv_sidebox_on_forumdisplay'] && THIS_SCRIPT == 'forumdisplay.php')
	{
		if($left_boxes && !$right_boxes)
		{
			$templates->cache['forumdisplay'] = str_replace('{$header}','{$header}<table width="100%"  border="0"><tr><td width="'.$adv_sidebox_width_left.'" valign="top">'.$left_boxes.'</td><td width="auto" valign="top">',$templates->cache['forumdisplay']);
			$templates->cache['forumdisplay'] = str_replace('{$footer}','{$footer}</td></tr></table>',$templates->cache['forumdisplay']);
		}
		elseif(!$left_boxes && $right_boxes)
		{
			$templates->cache['forumdisplay'] = str_replace('{$header}','{$header}<table width="100%"  border="0"><tr><td width="auto" valign="top">',$templates->cache['forumdisplay']);
			$templates->cache['forumdisplay'] = str_replace('{$footer}','{$footer}</td><td width="'.$adv_sidebox_width_right.'" valign="top">'.$right_boxes.'</td></tr></table>',$templates->cache['forumdisplay']);
		}
		elseif($left_boxes && $right_boxes)
		{
			$templates->cache['forumdisplay'] = str_replace('{$header}','{$header}<table width="100%"  border="0"><tr><td width="'.$adv_sidebox_width_left.'" valign="top">'.$left_boxes.'</td><td width="auto" valign="top">',$templates->cache['forumdisplay']);
			$templates->cache['forumdisplay'] = str_replace('{$footer}','{$footer}</td><td width="'.$adv_sidebox_width_right.'" valign="top">'.$right_boxes.'</td></tr></table>',$templates->cache['forumdisplay']);
		}
	}
	
	//Display boxes on showthread
	if($mybb->settings['adv_sidebox_on_showthread'] && THIS_SCRIPT == 'showthread.php')
	{
		if($left_boxes && !$right_boxes)
		{
			$templates->cache['showthread'] = str_replace('{$header}','	{$header}<table width="100%"  border="0"><tr><td width="'.$adv_sidebox_width_left.'" valign="top">'.$left_boxes.'</td></td><td width="auto" valign="top">',$templates->cache['showthread']);
			$templates->cache['showthread'] = str_replace('{$footer}','{$footer}</td></tr></table>',$templates->cache['showthread']);
		}
		elseif(!$left_boxes && $right_boxes)
		{
			$templates->cache['showthread'] = str_replace('{$header}','	{$header}<table width="100%"  border="0"><tr><td width="auto" valign="top">',$templates->cache['showthread']);
			$templates->cache['showthread'] = str_replace('{$footer}','{$footer}</td><td width="'.$adv_sidebox_width_right.'" valign="top">'.$right_boxes.'</td></tr></table>',$templates->cache['showthread']);
		}
		elseif($left_boxes && $right_boxes)
		{
			$templates->cache['showthread'] = str_replace('{$header}','	{$header}<table width="100%"  border="0"><tr><td width="'.$adv_sidebox_width_left.'" valign="top">'.$left_boxes.'</td></td><td width="auto" valign="top">',$templates->cache['showthread']);
			$templates->cache['showthread'] = str_replace('{$footer}','{$footer}</td><td width="'.$adv_sidebox_width_right.'" valign="top">'.$right_boxes.'</td></tr></table>',$templates->cache['showthread']);
		}
	}
	
	// Display additional boxes on portal (if 'Replace Portal Boxes With Custom' is set to yes)
	if($mybb->settings['adv_sidebox_portal_replace'] && THIS_SCRIPT == 'portal.php')
	{
		$templates->cache['portal'] = str_replace('{$welcome}', '', $templates->cache['portal']);
		$templates->cache['portal'] = str_replace('{$search}', '', $templates->cache['portal']);
		$templates->cache['portal'] = str_replace('{$pms}', '', $templates->cache['portal']);
		$templates->cache['portal'] = str_replace('{$stats}', '', $templates->cache['portal']);
		$templates->cache['portal'] = str_replace('{$whosonline}', '', $templates->cache['portal']);
		$templates->cache['portal'] = str_replace('{$latestthreads}', $left_boxes, $templates->cache['portal']);
	}

	// this hook will allow a plugin to process its custom box type for display (you will first need to hook into adv_sidebox_add_type to add the box
	$plugins->run_hooks('adv_sidebox_output_end', $box_types);
	
	// if there are installed modules (simple or complex) . . .
	if(!empty($boxes_info))
	{
		// . . . loop throught them
		foreach($boxes_info as $module => $info)
		{
			// if admin is using this box type . . .
			if($box_types[$module])
			{
				// . . . and the files are intact . . .
				if(file_exists(ADV_SIDEBOX_MODULES_DIR."/".$module."/adv_sidebox_module.php"))
				{
					// . . . run the module's template building code.
					require_once ADV_SIDEBOX_MODULES_DIR."/".$module."/adv_sidebox_module.php";
					
					if(function_exists($module . '_asb_build_template'))
					{
						$build_template_function = $module . '_asb_build_template';
						$build_template_function();
					}
				}
			}
		}
	}
}

// Hooks for the User CP routine.
$plugins->add_hook("usercp_options_end", "adv_sidebox_options");
$plugins->add_hook("usercp_do_options_end", "adv_sidebox_options");

// Add a checkbox to the User CP under Other Options to toggle the sideboxes
function adv_sidebox_options()
{
	global $db, $mybb, $templates, $user, $lang;
	
	if (!$lang->adv_sidebox)
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

	if ($db->num_rows($query) > 0)
	{
		// checked
		$checked = 'checked="checked" ';
	}

	$usercp_option = '<td valign="top" width="1"><input type="checkbox" class="checkbox" name="showsidebox" id="showsidebox" value="1" ' . $checked . '/></td><td><span class="smalltext"><label for="showsidebox">' . $lang->adv_sidebox_show_sidebox . '</label></span></td></tr><tr><td valign="top" width="1"><input type="checkbox" class="checkbox" name="showredirect"';

    // Update the template cache
	$find = '<td valign="top" width="1"><input type="checkbox" class="checkbox" name="showredirect"';
    $templates->cache['usercp_options'] = str_replace($find, $usercp_option, $templates->cache['usercp_options']);
}

/*
 * loop through all installed modules and grab their info
 */
function get_installed_box_types()
{
	//modules
	$dir = opendir(ADV_SIDEBOX_MODULES_DIR);
	
	$all_box_types = array();

	while(($module = readdir($dir)) !== false)
	{
		if(is_dir(ADV_SIDEBOX_MODULES_DIR . "/" . $module) && !in_array($module, array(".", "..")) && file_exists(ADV_SIDEBOX_MODULES_DIR . "/" . $module . "/adv_sidebox_module.php"))
		{
			require_once ADV_SIDEBOX_MODULES_DIR."/".$module."/adv_sidebox_module.php";

			$is_installed_function = $module . '_asb_is_installed';

			if(function_exists($module . '_asb_is_installed'))
			{
				if($is_installed_function())
				{
					if(function_exists($module . '_asb_info'))
					{
						$info_function = $module . '_asb_info';
						$this_info = $info_function();
						
						$all_box_types['name'] = $this_info['name'];
						$all_box_types['description'] = $this_info['description'];
						$all_box_types['stereo'] = $this_info['stereo'];
						$all_box_types['module_type'] = 'complex';
						$all_box_types['status'] = true;
					}
				}
			}
			else
			{
				if(function_exists($module . '_asb_info'))
				{
					$info_function = $module . '_asb_info';
					$this_info = $info_function();
					
					$all_box_types['name'] = $this_info['name'];
					$all_box_types['description'] = $this_info['description'];
					$all_box_types['stereo'] = $this_info['stereo'];
					$all_box_types['module_type'] = 'simple';
					$all_box_types['status'] = true;
				}
			}
		}
		$output[$module] = $all_box_types;
	}
	return $output;
}

// loop through all user-defined boxes and return them
function get_all_custom_box_types_content()
{
	global $db;
	
	$all_custom_types = array();
	
	$query = $db->simple_select('custom_sideboxes');
	
	if($db->num_rows($query) > 0)
	{
		while($this_type = $db->fetch_array($query))
		{
			$all_custom[$this_type['id'] . '_asb_custom'] = $this_type['content'];
		}
	}
	
	return $all_custom;
}

?>
