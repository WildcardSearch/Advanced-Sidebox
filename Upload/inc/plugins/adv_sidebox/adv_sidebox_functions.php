<?php
/*
 * This file contains various functions for the plugin
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

 /*
 * adv_sidebox_do_checks()
 *
 * avoid wasted execution by determining when and if code is necessary
 */
function adv_sidebox_do_checks()
{
	global $mybb, $theme;

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
 * @param - $filter is a string containing the script to show or 'all_scripts' to avoid filtering altogether
 */
function adv_sidebox_build_filter_links($filter)
{
	global $lang, $adv_sidebox;

	if(!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}

	// if there are active scripts . . .
	if(is_array($adv_sidebox->all_scripts))
	{
		$all_links = '';

		// loop through them
		foreach($adv_sidebox->all_scripts as $script)
		{
			// its complicated
			$base_name = $script;

			// fix for some weird naming
			switch($script)
			{
				case 'forumdisplay':
					$base_name = 'forum';
					break;
				case 'showthread':
					$base_name = 'thread';
					break;
			}

			// url
			$url = ADV_SIDEBOX_URL . '&amp;action=manage_sideboxes&amp;page=' . $base_name;

			// language
			$language_name = 'adv_sidebox_' . $base_name;

			// active filter?
			if($filter == $script)
			{
				// no link
				$all_links .= "<span class=\"filter_link_active\" title=\"{$lang->$language_name}\"/>{$lang->$language_name}</span>";
			}
			else
			{
				// link
				$all_links .= "<span onclick=\"document.location = '{$url}';\" class=\"filter_link_inactive\" title=\"{$lang->$language_name}\"/><a href=\"{$url}\" title=\"{$lang->$language_name}\"/>{$lang->$language_name}</a></span>";
			}
		}
	}

	// are we filtering?
	if($filter)
	{
		// if so then all scripts is a link
		$all_scripts = '<span class="filter_link_inactive" onclick="document.location = \'' . ADV_SIDEBOX_URL . '\';" title="' . $lang->adv_sidebox_all . '"/><a href="' . ADV_SIDEBOX_URL . '" title="' . $lang->adv_sidebox_all . '"/>' . $lang->adv_sidebox_all . '</a></span>';
	}
	else
	{
		// no link
		$all_scripts = '<span class="filter_link_active" title="' . $lang->adv_sidebox_all . '"/>' . $lang->adv_sidebox_all . '</span>';
	}

	// put it all together
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
</style>' . $all_scripts . $all_links;
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
	// Assign pattern and replace values.
	$pattern = array
	(
		"#\[quote=([\"']|&quot;|)(.*?)(?:\\1)(.*?)(?:[\"']|&quot;)?\](.*?)\[/quote\](\r\n?|\n?)#esi",
		"#\[quote\](.*?)\[\/quote\](\r\n?|\n?)#si",
		"#\[\/quote\](\r\n?|\n?)#si"
	);

	$replace = array
	(
		"",
		"",
		""
	);

	do
	{
		$message = preg_replace($pattern, $replace, $message, -1, $count);
	}
	while($count);

	$find = array
	(
		"#(\r\n*|\n*)<\/cite>(\r\n*|\n*)#",
		"#(\r\n*|\n*)<\/blockquote>#"
	);

	$replace = array
	(
		"",
		""
	);
	$message = preg_replace($find, $replace, $message);

	return $message;
}

?>
