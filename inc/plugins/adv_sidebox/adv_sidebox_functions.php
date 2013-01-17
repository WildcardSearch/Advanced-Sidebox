<?php
/*
 * This file contains variousl functions for adv_sidebox.php and acp_functions.php
 *
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright © 2013 WildcardSearch
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

/*
 * adv_sidebox_get_all_sideboxes()
 *
 * retrieve all sideboxes from the database as a sorted list of objects (class Sidebox)
 */
function adv_sidebox_get_all_sideboxes()
{
	global $db;

	require_once MYBB_ROOT . 'inc/plugins/adv_sidebox/adv_sidebox_classes.php';
	$sideboxes = array();

	// Look for all sideboxes (if any)
	$query = $db->simple_select('sideboxes', '*', '', array("order_by" => 'position, display_order', "order_dir" => 'ASC'));

	// if there are sideboxes . . .
	if($db->num_rows($query) > 0)
	{
		while($this_box = $db->fetch_array($query))
		{
			// attempt to load each sidebox
			$sideboxes[] = new Sidebox($this_box);
		}
		return $sideboxes;
	}
	else
	{
		return false;
	}
}

/*
 * get_all_modules()
 *
 * loop through all installed modules and grab their info
 *
 * @param - &$box_types is an array with valid module base_names as keys and boolean values
 */
function get_all_modules(&$box_types = array())
{
	require_once MYBB_ROOT . 'inc/plugins/adv_sidebox/adv_sidebox_classes.php';

	//modules
	$dir = opendir(ADV_SIDEBOX_MODULES_DIR);

	$all_modules = array();

	// loop through all detected modules
	while(($module = readdir($dir)) !== false)
	{
		if(is_dir(ADV_SIDEBOX_MODULES_DIR . "/" . $module) && !in_array($module, array(".", "..")) && file_exists(ADV_SIDEBOX_MODULES_DIR . "/" . $module . "/adv_sidebox_module.php"))
		{
			$all_modules[$module] = new Sidebox_addon($module);
			$box_types[$module] = $all_modules[$module]->name;
		}
	}
	return $all_modules;
}

 /*
 * get_all_custom_box_types_content()
 *
 * loop through all user-defined boxes and return them
 */
function get_all_custom_box_types_content()
{
	global $db, $collapsed;

	$all_custom_types = array();

	$query = $db->simple_select('custom_sideboxes');

	// if there are user-defined box types grab them
	if($db->num_rows($query) > 0)
	{
		while($this_type = $db->fetch_array($query))
		{
			if($this_type['wrap_content'])
			{
				// Check if this sidebox is either expanded or collapsed and hide it as necessary.
				$expdisplay = '';
				$collapsed_name = 'asb_custom_' . $this_type['id'] . '_c';
				if(isset($collapsed[$collapsed_name]) && $collapsed[$collapsed_name] == "display: show;")
				{
					$expcolimage = "collapse_collapsed.gif";
					$expdisplay = "display: none;";
					$expaltext = "[+]";
				}
				else
				{
					$expcolimage = "collapse.gif";
					$expaltext = "[-]";
				}
				
				$this_type['content'] = '
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
	<thead>
		<tr>
			<td class="thead"><div class="expcolimage"><img src="{$theme[\'imgdir\']}/' . $expcolimage . '" id="asb_custom_' . $this_type['id'] . '_img" class="expander" alt="' . $expaltext . '" title="' . $expaltext . '" /></div><strong>' . $this_type['name'] . '</strong></td>
		</tr>
	</thead>
	<tbody style="' . $expdisplay . '" id="asb_custom_' . $this_type['id'] . '_e"><tr>
		<td class="trow1">' . $this_type['content'] . '</td></tr>
	</tbody>
</table><br />';
			}
			
			$all_custom[$this_type['id'] . '_asb_custom'] = $this_type['content'];
		}
	}

	return $all_custom;
}

/*
 * adv_sidebox_do_checks()
 *
 * avoid wasted execution by determining when and if code is necessary
 */
function adv_sidebox_do_checks()
{
	global $mybb;

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

	return true;
}

/*
 * adv_sidebox_pad_box()
 *
 * use a transparent image at the base of this sidebox column if it isn't empty
 *
 * @param - $box is a valid object of class Sidebox
 * @param - $width is the width of the current sidebox position
 */
function adv_sidebox_pad_box($box, $width)
{
	// if it is empty, leave it that way
	if($box)
	{
		// if not, pad it to ensure the width is constant regardless of content
		return $box . '<img src="inc/plugins/adv_sidebox/images/transparent.gif" width="' . $width . '" height="1" alt="" title=""/>';
	}
}

/*
 * adv_sidebox_count_modules()
 *
 * count modules by type
 *
 * @param - $modules is an array of objects of class Sidebox_addon
 *
 * obviously counters:
 * @param - &$installed
 * @param - &$uninstalled
 * @param - &$simple
 */
function adv_sidebox_count_modules($modules, &$installed, &$uninstalled, &$simple)
{
	// no modules, get out
	if(is_array($modules) && !empty($modules))
	{
		foreach($modules as $this_module)
		{
			if($this_module->module_type == 'simple')
			{
				++$simple;
			}
			else
			{
				if($this_module->is_installed)
				{
					++$installed;
				}
				else
				{
					++$uninstalled;
				}
			}
		}
	}
}

/*
 * adv_sidebox_build_module_info_language()
 *
 * build the description of modules' status
 *
 * obviously module counts
 * @param - $installed
 * @param - $uninstalled
 * @param - $simple
 */
function adv_sidebox_build_module_info_language($installed, $uninstalled, $simple)
{
	global $lang;

	if (!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}

	// get a total of all modules
	$count_allmods =  $uninstalled + $installed + $simple;

	// if there are any modules . . .
	if($count_allmods)
	{
		// more than 1?
		if($count_allmods > 1)
		{
			// plural language
			$module_info .= $lang->sprintf($lang->adv_sidebox_module_info_good_count, $lang->adv_sidebox_are, $count_allmods, $lang->adv_sidebox_module_plural);
		}
		else
		{
			// singular
			$module_info .= $lang->sprintf($lang->adv_sidebox_module_info_good_count, $lang->adv_sidebox_is, $count_allmods, $lang->adv_sidebox_module_singular);
		}

		// uninstalled modules?
		if($uninstalled)
		{
			// more than one?
			if($uninstalled > 1)
			{
				// plural language
				$module_info .= $lang->sprintf($lang->adv_sidebox_module_awaiting_install, $uninstalled, $lang->adv_sidebox_are);
			}
			else
			{
				// singular
				$module_info .= $lang->sprintf($lang->adv_sidebox_module_awaiting_install, $uninstalled, $lang->adv_sidebox_is);
			}
		}
		else
		{
			// all modules installed
			$module_info .= $lang->adv_sidebox_module_all_good;
		}
	}
	else
	{
		// no modules
		$module_info .= $lang->adv_sidebox_no_modules_detected;
	}

	return $module_info;
}

/*
 * adv_sidebox_filter_by_script()
 *
 * checks whether a box is set to display given a result filter. return true if the box is shown and false if not
 *
 * @param - $box is a valid object of class Sidebox
 * @param - $filter is a string containing the script to filter for or 'all_scripts' to avoid filtering altogether
 */
function adv_sidebox_filter_by_script($box, $filter)
{
	if(isset($filter))
	{
		switch($filter)
		{
			case 'index':
				return $box->show_on_index;
				break;
			case 'forum':
				return $box->show_on_forumdisplay;
				break;
			case 'thread':
				return $box->show_on_showthread;
				break;
			case 'portal':
				return $box->show_on_portal;
				break;
			default:
				return true;
				break;
		}
	}
	else
	{
		return true;
	}
}

/*
 * adv_sidebox_build_filter_links()
 *
 * build links for ACP Manage Sideboxes screen
 *
 * @param - $filter is a string containing the script to filter for or 'all_scripts' to avoid filtering altogether
 */
function adv_sidebox_build_filter_links($filter)
{
	global $lang;
	
	if (!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}
	
	// normal status
	$all_disabled = ' class="filter_link_inactive"';
	$index_disabled = ' class="filter_link_inactive"';
	$forum_disabled = ' class="filter_link_inactive"';
	$thread_disabled = ' class="filter_link_inactive"';
	$portal_disabled = ' class="filter_link_inactive"';

	if(isset($filter))
	{
		switch($filter)
		{
			case 'index':
				$index_disabled = ' class="filter_link_active"'; // selected status
				break;
			case 'forum':
				$forum_disabled = ' class="filter_link_active"';
				break;
			case 'thread':
				$thread_disabled = ' class="filter_link_active"';
				break;
			case 'portal':
				$portal_disabled = ' class="filter_link_active"';
				break;
			default:
				$all_disabled = ' class="filter_link_active"';
				break;
		}
	}
	else
	{
		$all_disabled = ' class="filter_link_active"';
	}

	return '
<style type="text/css">
.filter_link_inactive
{
	background: #9DC2F2;
	border: 2px solid #85B1EE;
	color: #000;
	padding: 5px;
	margin: 10px;
	position: relative;
	top: 5px;
}
.filter_link_active
{
	background: #F2F2F2;
	border: 2px solid #85B1EE;
	color: #000;
	padding: 5px;
	margin: 10px;
	position: relative;
	top: 5px;
}
</style><span' . $all_disabled . '><a href="' . ADV_SIDEBOX_URL . '"/>' . $lang->adv_sidebox_all . '</a></span><span' . $index_disabled . '><a href="' . ADV_SIDEBOX_URL . '&amp;action=manage_sideboxes&amp;mode=index"/>' . $lang->adv_sidebox_index . '</a></span><span' . $forum_disabled . '><a href="' . ADV_SIDEBOX_URL . '&amp;action=manage_sideboxes&amp;mode=forum"/>' . $lang->adv_sidebox_forum . '</a></span><span' . $thread_disabled . '><a href="' . ADV_SIDEBOX_URL . '&amp;action=manage_sideboxes&amp;mode=thread"/>' . $lang->adv_sidebox_thread . '</a></span><span' . $portal_disabled . '><a href="' . ADV_SIDEBOX_URL . '&amp;action=manage_sideboxes&amp;mode=portal"/>' . $lang->adv_sidebox_portal . '</a></span>';
}

/*
 * adv_sidebox_strip_quotes()
 *
 * strips all quote tags (and their contents) from a post message
 *
 * @param - $message is a string containung the unparsed message
 */
function adv_sidebox_strip_quotes($message)
{
	global $lang, $templates, $theme, $mybb;

	// Assign pattern and replace values.
	$pattern = array(
		"#\[quote=([\"']|&quot;|)(.*?)(?:\\1)(.*?)(?:[\"']|&quot;)?\](.*?)\[/quote\](\r\n?|\n?)#esi",
		"#\[quote\](.*?)\[\/quote\](\r\n?|\n?)#si"
	);

	$replace = array(
		"",
		""
	);

	do
	{
		$previous_message = $message;
		$message = preg_replace($pattern, $replace, $message, -1, $count);
	} while($count);

	if(!$message)
	{
		$message = $previous_message;
	}

	$find = array(
		"#(\r\n*|\n*)<\/cite>(\r\n*|\n*)#",
		"#(\r\n*|\n*)<\/blockquote>#"
	);

	$replace = array(
		"",
		""
	);
	$message = preg_replace($find, $replace, $message);
	
	return $message;
}

?>
