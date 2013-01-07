<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x v1.1
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

// Load the install/admin routines only if in ACP.
if(defined("IN_ADMINCP"))
{
    require_once MYBB_ROOT . "inc/plugins/adv_sidebox/acp_functions.php";
}

global $settings;

// Hook only if necessary
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
	global $adv_sidebox_width_left, $adv_sidebox_width_right;
	global $adv_sidebox_inner_left, $adv_sidebox_inner_right;
	
	// will need all classes and functions here
	require_once MYBB_ROOT . 'inc/plugins/adv_sidebox/adv_sidebox_classes.php';
	require_once MYBB_ROOT . 'inc/plugins/adv_sidebox/adv_sidebox_functions.php';

	// don't waste execution if unnecessary
	if(!adv_sidebox_do_checks())
	{
		return false;
	}

	// store all the sideboxes here as objects
	$sideboxes = array();
	$sideboxes = adv_sidebox_get_all_sideboxes();
	
	// There is only one internal box type (custom),
	// but new types can come from saved custom boxes . . .
	$custom_box_list = get_all_custom_box_types_content();
	
	// . . . or modules
	$modules = get_all_modules();

	// loop through the sideboxes and sort them
	foreach($sideboxes as $this_box)
	{	
		// if this is a user-defined box . . .
		if($custom_box_list[$this_box->box_type])
		{
			// . . . then use the custom content as a replacement
			$content = $custom_box_list[$this_box->box_type];
		}
		else
		{
			// otherwise use the box's content
			$content = $this_box->content;
		}
		
		// Index
		if($this_box->show_on_index)
		{
			// 0 = left, otherwise right
			if($this_box->position)
			{
				$index_right_boxes .= $content;
			}
			else
			{
				$index_left_boxes .= $content;
			}
			
			$box_types['index.php'][$this_box->box_type] = true;
		}
		
		// Forum
		if($this_box->show_on_forumdisplay)
		{
			// 0 = left, otherwise right
			if($this_box->position)
			{
				$forum_right_boxes .= $content;
			}
			else
			{
				$forum_left_boxes .= $content;
			}
			
			$box_types['forumdisplay.php'][$this_box->box_type] = true;
		}
		
		// Thread
		if($this_box->show_on_showthread)
		{
			// 0 = left, otherwise right
			if($this_box->position)
			{
				$thread_right_boxes .= $content;
			}
			else
			{
				$thread_left_boxes .= $content;
			}
			
			$box_types['showthread.php'][$this_box->box_type] = true;
		}
		
		// Portal
		if($this_box->show_on_portal)
		{
			// 0 = left, otherwise right
			if($this_box->position)
			{
				$portal_right_boxes .= $content;
			}
			else
			{
				$portal_left_boxes .= $content;
			}
			
			$box_types['portal.php'][$this_box->box_type] = true;
		}
	}
	
	// no boxes?
	if((!$index_left_boxes && !$index_right_boxes) && THIS_SCRIPT == 'index.php')
	{
		return false;
	}
	
	if((!$forum_left_boxes && !$forum_right_boxes) && THIS_SCRIPT == 'forumdisplay.php')
	{
		return false;
	}
	
	if((!$thread_left_boxes && !$thread_right_boxes) && THIS_SCRIPT == 'showthread.php')
	{
		return false;
	}
	
	if((!$portal_left_boxes && !$portal_right_boxes) && THIS_SCRIPT == 'portal.php')
	{
		return false;
	}
	
	// width
	$adv_sidebox_width_left = (int) $mybb->settings['adv_sidebox_width_left'];
	$adv_sidebox_width_right = (int) $mybb->settings['adv_sidebox_width_right'];
	$adv_sidebox_inner_left = (int) ($mybb->settings['adv_sidebox_width_left'] * .83);
	$adv_sidebox_inner_right = (int) ($mybb->settings['adv_sidebox_width_right'] * .83);
	
	$index_left_boxes = adv_sidebox_pad_box($index_left_boxes, $adv_sidebox_width_left);
	$index_right_boxes = adv_sidebox_pad_box($index_right_boxes, $adv_sidebox_width_right);
	$forum_left_boxes = adv_sidebox_pad_box($forum_left_boxes, $adv_sidebox_width_left);
	$forum_right_boxes = adv_sidebox_pad_box($forum_right_boxes, $adv_sidebox_width_right);
	$thread_left_boxes = adv_sidebox_pad_box($thread_left_boxes, $adv_sidebox_width_left);
	$thread_right_boxes = adv_sidebox_pad_box($thread_right_boxes, $adv_sidebox_width_right);
	$portal_left_boxes = adv_sidebox_pad_box($portal_left_boxes, $adv_sidebox_width_left);
	$portal_right_boxes = adv_sidebox_pad_box($portal_right_boxes, $adv_sidebox_width_right);

	// Display boxes on index
	if($mybb->settings['adv_sidebox_on_index'] && THIS_SCRIPT == 'index.php')
	{
		if($index_left_boxes && !$index_right_boxes)
		{
			$templates->cache['index'] = str_replace('{$header}', '{$header}<table width="100%" border="0" cellspacing="5"><tr><td width="' . $adv_sidebox_width_left . '" valign="top">' . $index_left_boxes . '</td><td width="auto" valign="top">', $templates->cache['index']);
			$templates->cache['index'] = str_replace('{$footer}', '{$footer}</td></tr></table></div></div>', $templates->cache['index']);
		}
		elseif(!$index_left_boxes && $index_right_boxes)
		{
			$templates->cache['index'] = str_replace('{$header}', '{$header}<table width="100%" border="0" cellspacing="5"><tr><td width="auto" valign="top">', $templates->cache['index']);
			$templates->cache['index'] = str_replace('{$footer}', '{$footer}</td><td width="' . $adv_sidebox_width_right . '" valign="top">' . $index_right_boxes . '</td></tr></table>', $templates->cache['index']);
		}
		elseif($index_left_boxes && $index_right_boxes)
		{
			$templates->cache['index'] = str_replace('{$header}', '{$header}<table width="100%" border="0" cellspacing="5"><tr><td width="' . $adv_sidebox_width_left . '" valign="top">' . $index_left_boxes . '</td><td width="auto" valign="top">', $templates->cache['index']);
			$templates->cache['index'] = str_replace('{$footer}','{$footer}</td><td width="'.$adv_sidebox_width_right.'" valign="top">' . $index_right_boxes . '</td></tr></table></div></div>', $templates->cache['index']);
		}
	}

	//Display boxes on forumdisplay
	if($mybb->settings['adv_sidebox_on_forumdisplay'] && THIS_SCRIPT == 'forumdisplay.php')
	{
		if($forum_left_boxes && !$forum_right_boxes)
		{
			$templates->cache['forumdisplay'] = str_replace('{$header}', '{$header}<table width="100%"  border="0"><tr><td width="' . $adv_sidebox_width_left . '" valign="top">' . $forum_left_boxes . '</td><td width="auto" valign="top">', $templates->cache['forumdisplay']);
			$templates->cache['forumdisplay'] = str_replace('{$footer}', '{$footer}</td></tr></table>', $templates->cache['forumdisplay']);
		}
		elseif(!$forum_left_boxes && $forum_right_boxes)
		{
			$templates->cache['forumdisplay'] = str_replace('{$header}', '{$header}<table width="100%"  border="0"><tr><td width="auto" valign="top">', $templates->cache['forumdisplay']);
			$templates->cache['forumdisplay'] = str_replace('{$footer}', '{$footer}</td><td width="' . $adv_sidebox_width_right . '" valign="top">' . $forum_right_boxes . '</td></tr></table>', $templates->cache['forumdisplay']);
		}
		elseif($forum_left_boxes && $forum_right_boxes)
		{
			$templates->cache['forumdisplay'] = str_replace('{$header}', '{$header}<table width="100%"  border="0"><tr><td width="' . $adv_sidebox_width_left . '" valign="top">' . $forum_left_boxes . '</td><td width="auto" valign="top">', $templates->cache['forumdisplay']);
			$templates->cache['forumdisplay'] = str_replace('{$footer}', '{$footer}</td><td width="' . $adv_sidebox_width_right . '" valign="top">' . $forum_right_boxes . '</td></tr></table>', $templates->cache['forumdisplay']);
		}
	}
	
	//Display boxes on showthread
	if($mybb->settings['adv_sidebox_on_showthread'] && THIS_SCRIPT == 'showthread.php')
	{
		if($thread_left_boxes && !$thread_right_boxes)
		{
			$templates->cache['showthread'] = str_replace('{$header}', '{$header}<table width="100%"  border="0"><tr><td width="' . $adv_sidebox_width_left . '" valign="top">' . $thread_left_boxes . '</td></td><td width="auto" valign="top">',$templates->cache['showthread']);
			$templates->cache['showthread'] = str_replace('{$footer}', '{$footer}</td></tr></table>', $templates->cache['showthread']);
		}
		elseif(!$thread_left_boxes && $thread_right_boxes)
		{
			$templates->cache['showthread'] = str_replace('{$header}', '{$header}<table width="100%"  border="0"><tr><td width="auto" valign="top">', $templates->cache['showthread']);
			$templates->cache['showthread'] = str_replace('{$footer}', '{$footer}</td><td width="' . $adv_sidebox_width_right . '" valign="top">' . $thread_right_boxes . '</td></tr></table>', $templates->cache['showthread']);
		}
		elseif($thread_left_boxes && $thread_right_boxes)
		{
			$templates->cache['showthread'] = str_replace('{$header}', '{$header}<table width="100%"  border="0"><tr><td width="' . $adv_sidebox_width_left . '" valign="top">' . $thread_left_boxes . '</td></td><td width="auto" valign="top">', $templates->cache['showthread']);
			$templates->cache['showthread'] = str_replace('{$footer}', '{$footer}</td><td width="' . $adv_sidebox_width_right . '" valign="top">' . $thread_right_boxes . '</td></tr></table>', $templates->cache['showthread']);
		}
	}
	
	// Display additional boxes on portal (if 'Replace Portal Boxes With Custom' is set to yes)
	if($mybb->settings['adv_sidebox_portal_replace'] && THIS_SCRIPT == 'portal.php' && ($portal_left_boxes || $portal_right_boxes))
	{
		$this_template = '<html>
	<head>
		<title>{$mybb->settings[\'bbname\']}</title>
		{$headerinclude}
	</head>
	<body>
		{$header}
		<table width="100%" cellspacing="0" cellpadding="{$theme[\'tablespace\']}" border="0">
			<tr>';
		
		if($portal_left_boxes)
		{
			$this_template .= '
				<td valign="top" width="' . $adv_sidebox_width_left . '"><div style="max-width: ' . $adv_sidebox_width_left . 'px min-width: ' . $adv_sidebox_width_left . 'px">' . $portal_left_boxes . '</div></td>
				<td>&nbsp;</td>';
		}
		
		$this_template .= '
				<td style="max-width:' . (1000 - ($adv_sidebox_width_right + $adv_sidebox_width_left)) . 'px;"><div style="max-width: ' . (1000 - ($adv_sidebox_width_right + $adv_sidebox_width_left)) . 'px min-width: ' . (1000 - ($adv_sidebox_width_right + $adv_sidebox_width_left)) . 'px">{$announcements}</div></td>
				<td>&nbsp;</td>';
		
		if($portal_right_boxes)
		{
			$this_template .= '
				<td valign="top" width="' . $adv_sidebox_width_right . '"><div style="max-width: ' . $adv_sidebox_width_right . 'px min-width: ' . $adv_sidebox_width_right . 'px">' . $portal_right_boxes . '</div></td>';
		}
		
		$this_template .= '
			</tr>
		</table>
		{$footer}
	</body>
</html>';

		$templates->cache['portal'] = $this_template;
	}
	
	// this hook will allow a plugin to process its custom box type for display (you will first need to hook into adv_sidebox_add_type to add the box
	$plugins->run_hooks('adv_sidebox_output_end', $box_types);
	
	// if there are installed modules (simple or complex) . . .
	if(!empty($modules))
	{
		// . . . loop throught them
		foreach($modules as $module => $info)
		{
			// if admin is using this box type and it is for this script . . .
			if($box_types[THIS_SCRIPT][$module])
			{
				$modules[$module]->build_template();
			}
		}
	}
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

?>
