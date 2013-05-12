<?php
/*
 * This file contains class definitions for the template handling and editing system
 *
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

/*
 * probably unnecessary but I am a sucker for structure :-/
 */
interface Template_handler
{
	public function make_edits();
}

/*
 * the parent class for all template handlers
 *
 * provides basic services for a template handler as well as a serviceable static function that can be used to determine the appropriate class to create based upon THIS_SCRIPT
 */
abstract class Template_handlers implements Template_handler
{
	protected $template_name = '';

	protected $find_top = '';
	protected  $find_bottom = '';

	protected $insert_top = '';
	protected $insert_bottom = '';

	protected $replace_all = false;
	protected $replacement = '';

	protected $left_content;
	protected $right_content;

	protected $width_left;
	protected $width_right;

	/*
	 * __construct()
	 *
	 * called upon creation (of any child as well) to store content in an appropriate format IF it exists, if not this parent version of ::make_edits() will avoid wasted execution
	 *
	 * @param - 	$left_insert is the left column of side box content for this page
	 *						$right_insert '		' right '																	'
	 * 						$width_left is the width (specified in ACP) for left coumns
	 * 						$width_right is blah blah blah but right instead
	 */
	public function __construct($left_insert, $right_insert, $width_left, $width_right)
	{
		global $mybb;

		// store width upon construct (mostly here for custom implementations like portal)
		$this->width_left = $width_left;
		$this->width_right = $width_right;

		// if admin wants to show the toggle icons . . .
		if($mybb->settings['adv_sidebox_show_toggle_icons'])
		{
			// check the cookie
			if($mybb->cookies['asb_hide_left'] == 1)
			{
				// hide left
				$show_left_column = 'style="display: none;" ';
				$close_style_left = ' style="display: none; position: relative; top: 13px; left: 3px;"';
				$open_style_left = ' style="position: relative; top: 13px; left: 3px;"';
			}
			else
			{
				// show left
				$close_style_left = ' style="position: relative; top: 13px; left: 3px;"';
				$open_style_left = ' style="display: none; position: relative; top: 13px; left: 3px;"';
			}

			if($mybb->cookies['asb_hide_right'] == 1)
			{
				// hide right
				$show_right_column = 'style="display: none;" ';
				$close_style_right = ' style="display: none; position: relative; top: 13px; left: 3px;"';
				$open_style_right = ' style="position: relative; top: 13px; left: 3px;"';
			}
			else
			{
				// show right
				$close_style_right = ' style="position: relative; top: 13px; left: 3px;"';
				$open_style_right = ' style="display: none; position: relative; top: 13px; left: 3px;"';
			}

			// produce the links
			$toggle_left = '
			<td valign="top"><a id="asb_hide_column_left" href="javascript:void()"><img id="asb_left_close" src="inc/plugins/adv_sidebox/images/left_arrow.png" title="hide sideboxes" alt="<"' . $close_style_left . '/><img id="asb_left_open" src="inc/plugins/adv_sidebox/images/right_arrow.png" title="show sideboxes" alt=">"' . $open_style_left . '/></a></td>
			';
			$toggle_right = '
			<td valign="top"><a id="asb_hide_column_right" href="javascript:void()"><img id="asb_right_close" src="inc/plugins/adv_sidebox/images/right_arrow.png" title="hide sideboxes" alt=">"' . $close_style_right . '/><img id="asb_right_open" src="inc/plugins/adv_sidebox/images/left_arrow.png" title="show sideboxes" alt="<"' . $open_style_right . '/></a></td>
			';
		}

		// if there is content
		if($left_insert)
		{
			// wrap it in table tags and comment it
			$this->left_content = '
		<!-- start: adv_sidebox left column -->
		' . $toggle_left . '
		<td ' . $show_left_column . 'id="asb_left_column_id" width="' . $width_left . '" valign="top">' . $left_insert . '
			<!-- start: content pad -->
			<img src="inc/plugins/adv_sidebox/images/transparent.gif" width="' . $width_left . '" height="1" alt="" title=""/>
		<!-- end: content pad -->
		</td>
		<!-- end: adv_sidebox left column -->';
		}
		else
		{
			$this->left_content = '<td><a style="display: none;" id="asb_hide_column_left" href="javascript:void()"></a></td>';
		}

		if($right_insert)
		{
			$this->right_content = '
		<!-- start: adv_sidebox right column -->
		<td ' . $show_right_column . 'id="asb_right_column_id" width="' . $width_right . '" valign="top">' . $right_insert . '
		<!-- start: content pad -->
		<img src="inc/plugins/adv_sidebox/images/transparent.gif" width="' . $width_right . '" height="1" alt="" title=""/>
		<!-- end: content pad -->
		</td>
		' . $toggle_right . '
		<!-- end: adv_sidebox right column -->';
		}
		else
		{
			$this->right_content = '<td><a style="display: none;" id="asb_hide_column_right" href="javascript:void()"></a></td>';
		}
	}

	/*
	 * get_template_handler()
	 *
	 * static function that selectes an appropriate class based upon THIS_SCRIPT
	 *
	 * @param - 	$left_insert is the left column of side box content for this page
	 *						$right_insert '		' right '																	'
	 * 						$width_left is the width (specified in ACP) for left coumns
	 * 						$width_right is blah blah blah but right instead
	 */
	public static function get_template_handler($left_insert, $right_insert, $width_left, $width_right)
	{
		// remove the extension and add the suffix
		$script = substr(THIS_SCRIPT, 0, -4) . '_template_handler';

		// if this is a valid class
		if(class_exists($script))
		{
			// create and return it
			return new $script($left_insert, $right_insert, $width_left, $width_right);
		}
	}

	/*
	 * make_edits()
	 *
	 * the parent or 'final' ::make_edit() which handles the rudimentary tasks of editing a template for side boxes
	 */
	public function make_edits()
	{
		global $templates, $mybb;

		// replace everything on the page?
		if($this->replace_all == true)
		{
			// if there is content
			if($this->replacement)
			{
				// replace the existing page entirely
				$templates->cache[$this->template_name] = $this->replacement;
			}
			// otherwise don't :p
		}
		else
		{
			// if admin wants to show toggle icons
			if($mybb->settings['adv_sidebox_show_toggle_icons'])
			{
				// we will need this js
				$toggle_script = '<script type="text/javascript" src="jscripts/adv_sidebox.js"></script>';
			}

			// if there are columns stored
			if($this->insert_top || $this->insert_bottom)
			{
				// make the edits
				$templates->cache[$this->template_name] = str_replace($this->find_top, $toggle_script . $this->find_top . $this->insert_top, $templates->cache[$this->template_name]);
				$templates->cache[$this->template_name] = str_replace($this->find_bottom, $this->insert_bottom . $this->find_bottom, $templates->cache[$this->template_name]);
			}
		}
	}
}

/*
 * special extension of Template_handlers for portal.php - actually replaces the entire default page :-/ I hated to do it to ya
 */
class Portal_template_handler extends Template_handlers
{
	/*
	 * make_edits()
	 *
	 * this is the first line for portal.php and it flags itself as replacing the entire portal IF there is content - this is done because portal is very mean and will not allow my side boxes to take any of its space (plus portal has its own side boxes and they get in the way)
	 */
	public function make_edits()
	{
		// if either column has content . . .
		if($this->left_content || $this->right_content)
		{
			// we use this to put the squeeze on the announcements table
			$announce_width = (1000 - ($this->width_right + $this->width_left));

			// this means no search & replace just replace :-|
			$this->replace_all = true;
			$this->replacement = "
<html>
	<head>
		<title>" . '{$mybb->settings[\'bbname\']}' . "</title>
		" . '{$headerinclude}' . "
		<script type=\"text/javascript\" src=\"jscripts/adv_sidebox.js\"></script>
	</head>
	<body>
		" . '{$header}' . "
		<!-- start: adv_sidebox -->
		<table width=\"100%\" cellspacing=\"0\" cellpadding=\"" . '{$theme[\'tablespace\']}' . "\" border=\"0\">
			<tr>
				<!-- start: adv_sidebox left column -->
				{$this->left_content}
				<!-- start: adv_sidebox middle column (page contents of " . THIS_SCRIPT . ") -->
				<td valign=\"top\"><div style=\"max-width: {$announce_width}px min-width: {$announce_width}px\">" . '{$announcements}' . "</div></td>
				<!-- end: adv_sidebox middle column (page contents of " . THIS_SCRIPT . ") -->
				<!-- start: adv_sidebox right column -->
				{$this->right_content}
				<!-- end: adv_sidebox right column -->
			</tr>
		</table>
		<!-- end adv_sidebox -->
		" . '{$footer}' . "
	</body>
</html>";

			// indicate script and call the parent instance of this method
			$this->template_name = 'portal';
			parent::make_edits();
		}
		// if there is no content then chill
	}
}

/*
 * a mid-level class for most scripts - contains most of the info for replacement predefined with the rest ready for replacement if no info is passed
 */
 abstract class Simple_template_handlers extends Template_handlers
{
	/*
	 * make_edits()
	 *
	 * do most of the work for editing a standard layout MyBB page
	 */
	public function make_edits()
	{
		// if there is no info, go with the default values
		if(!$this->find_top)
		{
			$this->find_top = '{$header}';
		}

		if(!$this->find_bottom)
		{
			$this->find_bottom = '{$footer}';
		}

		if(!$this->insert_top)
		{
			$this->insert_top = '
	<!-- start: adv_sidebox -->
	<table width="100%" border="0" cellspacing="5">
		<tr>' . $this->left_content . '
			<!-- start: adv_sidebox middle column (page contents of ' . THIS_SCRIPT . ') -->
			<td width="auto" valign="top">';
		}

		if(!$this->insert_bottom)
		{
			$this->insert_bottom = '</td>
		<!-- end: adv_sidebox middle column (page contents of ' . THIS_SCRIPT . ') -->' . $this->right_content . '
	</tr>
</table>
<!-- end adv_sidebox -->';
		}

		// now go ahead with the real edits
		parent::make_edits();
	}
}

/*
 * concrete class for index.php
 */
class Index_template_handler extends Simple_template_handlers
{
	/*
	 * make_edits()
	 *
	 * set the script then call Simple_template_handler::make_edit()
	 */
	public function make_edits()
	{
		$this->template_name = 'index';
		parent::make_edits();
	}
}

/*
 * concrete class for forumdisplay.php
 */
class Forumdisplay_template_handler extends Simple_template_handlers
{
	/*
	 * make_edits()
	 *
	 * set the script and a custom replacement then call Simple_template_handler::make_edit()
	 */
	public function make_edits()
	{
		global $templates;

		// more than one {$multi_page} so we have to be sure we have the right one
		$newthread_div_open_pos = strpos($templates->cache['forumdisplay_threadlist'], '<div class="float_right">');
		$newthread_div_close_pos = strpos($templates->cache['forumdisplay_threadlist'], '</div>', $newthread_div_open_pos);
		$this->find_top = substr($templates->cache['forumdisplay_threadlist'], $newthread_div_open_pos, ($newthread_div_close_pos - $newthread_div_open_pos) + 6);
		$this->find_bottom = '{$inline_edit_js}';
		$this->template_name = 'forumdisplay_threadlist';
		parent::make_edits();
	}
}

/*
 * concrete class for showthread.php
 */
class Showthread_template_handler extends Simple_template_handlers
{
	/*
	 * make_edits()
	 *
	 * set the script and a custom replacement then call Simple_template_handler::make_edit()
	 */
	public function make_edits()
	{
		$this->find_top = '{$ratethread}';
		$this->template_name = 'showthread';
		parent::make_edits();
	}
}

/*
 * concrete class for memberlist.php
 */
class Memberlist_template_handler extends Simple_template_handlers
{
	/*
	 * make_edits()
	 *
	 * set the script and a custom replacement then call Simple_template_handler::make_edit()
	 */
	public function make_edits()
	{
		global $templates;

		// more than one {$multi_page} so we have to be sure we have the right one
		$header_pos = strpos($templates->cache['memberlist'], '{$header}');
		$multipage_pos = strpos($templates->cache['memberlist'], '{$multipage}');
		$this->find_top = substr($templates->cache['memberlist'], $header_pos, ($multipage_pos - $header_pos) + 12);

		$this->template_name = 'memberlist';
		parent::make_edits();
	}
}

/*
 * concrete class for member.php (profile)
 */
class Member_template_handler extends Simple_template_handlers
{
	/*
	 * make_edits()
	 *
	 * set the script and a custom replacement then call Simple_template_handler::make_edit()
	 */
	public function make_edits()
	{
		global $mybb;

		// only do the edits if the user is viewing a profile
		if($mybb->input['action'] == "profile")
		{
			$this->template_name = 'member_profile';
			parent::make_edits();
		}
	}
}

/*
 * concrete class for showteam.php
 */
class Showteam_template_handler extends Simple_template_handlers
{
	/*
	 * make_edits()
	 *
	 * set the script and then call Simple_template_handler::make_edit()
	 */
	public function make_edits()
	{
		$this->template_name = 'showteam';
		parent::make_edits();
	}
}

/*
 * concrete class for stats.php
 */
class Stats_template_handler extends Simple_template_handlers
{
	/*
	 * make_edits()
	 *
	 * set the script and then call Simple_template_handler::make_edit()
	 */
	public function make_edits()
	{
		$this->template_name = 'stats';
		parent::make_edits();
	}
}

?>
