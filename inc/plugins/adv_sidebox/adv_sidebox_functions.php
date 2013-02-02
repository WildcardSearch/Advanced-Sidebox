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
		if($mybb->user['show_sidebox'] == 0)
		{
			return false;
		}
	}

	return true;
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

	$all_scripts = '<a href="' . ADV_SIDEBOX_URL . '"/>' . $lang->adv_sidebox_all . '</a>';
	$index_script = '<a href="' . ADV_SIDEBOX_URL . '&amp;action=manage_sideboxes&amp;mode=index"/>' . $lang->adv_sidebox_index . '</a>';
	$forum_script = '<a href="' . ADV_SIDEBOX_URL . '&amp;action=manage_sideboxes&amp;mode=forum"/>' . $lang->adv_sidebox_forum . '</a>';
	$thread_script = '<a href="' . ADV_SIDEBOX_URL . '&amp;action=manage_sideboxes&amp;mode=thread"/>' . $lang->adv_sidebox_thread . '</a>';
	$portal_script = '<a href="' . ADV_SIDEBOX_URL . '&amp;action=manage_sideboxes&amp;mode=portal"/>' . $lang->adv_sidebox_portal . '</a>';

	if(isset($filter))
	{
		switch($filter)
		{
			case 'index':
				$index_disabled = ' class="filter_link_active"'; // selected status
				$index_script = $lang->adv_sidebox_index;
				break;
			case 'forum':
				$forum_disabled = ' class="filter_link_active"';
				$forum_script = $lang->adv_sidebox_forum;
				break;
			case 'thread':
				$thread_disabled = ' class="filter_link_active"';
				$thread_script = $lang->adv_sidebox_thread;
				break;
			case 'portal':
				$portal_disabled = ' class="filter_link_active"';
				$portal_script = $lang->adv_sidebox_portal;
				break;
			default:
				$all_disabled = ' class="filter_link_active"';
				$all_scripts = $lang->adv_sidebox_all;
				break;
		}
	}
	else
	{
		$all_disabled = ' class="filter_link_active"';
		$all_scripts = $lang->adv_sidebox_all;
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
.filter_link_inactive:hover, .filter_link_inactive:hover a
{
	background: #02426c;
	color: #F2F2F2;
	text-decoration: none;
}
</style><span' . $all_disabled . '>' . $all_scripts . '</span><span' . $index_disabled . '>' . $index_script . '</span><span' . $forum_disabled . '>' . $forum_script . '</span><span' . $thread_disabled . '>' . $thread_script . '</span><span' . $portal_disabled . '>' . $portal_script . '</span>';
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
