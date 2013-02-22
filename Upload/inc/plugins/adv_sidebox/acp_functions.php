<?php
/*
 * This file contains the Admin Control Panel functions for this plugin
 *
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2013 WildcardSearch
 *
 * Visit this project page on GitHub: http://wildcardsearch.github.com/Advanced-Sidebox
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
if(!defined("IN_MYBB") || !defined("ADV_SIDEBOX"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

define('ADV_SIDEBOX_URL', 'index.php?module=config-adv_sidebox');
define('ADV_SIDEBOX_MAIN_URL', 'index.php?module=config-adv_sidebox&amp;action=manage_sideboxes');
define('ADV_SIDEBOX_EDIT_URL', 'index.php?module=config-adv_sidebox&amp;action=edit_box');
define('ADV_SIDEBOX_DEL_URL', ADV_SIDEBOX_MAIN_URL . '&amp;function=delete_box');
define('ADV_SIDEBOX_MODULES_URL', 'index.php?module=config-adv_sidebox&amp;action=manage_modules');
define('ADV_SIDEBOX_CUSTOM_URL', 'index.php?module=config-adv_sidebox&amp;action=custom_boxes');
define('ADV_SIDEBOX_IMPORT_URL', ADV_SIDEBOX_CUSTOM_URL . '&amp;mode=import');
define('ADV_SIDEBOX_EXPORT_URL', ADV_SIDEBOX_CUSTOM_URL . '&amp;mode=export');

require_once MYBB_ROOT . "inc/plugins/adv_sidebox/adv_sidebox_install.php";

$plugins->add_hook('admin_load', 'adv_sidebox_admin');

/*
 * adv_sidebox_admin()
 *
 * main sorting page
 */
function adv_sidebox_admin()
{
	global $mybb, $db, $page, $lang, $adv_sidebox;

	define("ADV_SIDEBOX_HELP", $mybb->settings['bburl'] . "/inc/plugins/adv_sidebox/help/index.php");

	if($page->active_action != 'adv_sidebox')
	{
		return false;
	}

	if(!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}

	// get all the sidebox, addon and custom box info sorted and ready for use in all ACP pages
	// calling _construct with the $acp=true loads all info (instead of just enough to display)
	require_once MYBB_ROOT . 'inc/plugins/adv_sidebox/adv_sidebox_classes.php';
	$adv_sidebox = new Sidebox_handler($mybb->input['page'], true);

	// no action means the main page
	if(!$mybb->input['action'])
	{
		$mybb->input['action'] = 'manage_sideboxes';
	}

	if($mybb->input['action'] == 'xmlhttp')
	{
		adv_sidebox_sidebox_xmlhttp();
	}

	if($mybb->input['action'] == 'manage_sideboxes')
	{
		adv_sidebox_admin_main();
	}

	if($mybb->input['action'] == 'edit_box')
	{
		adv_sidebox_admin_edit();
	}

	if($mybb->input['action'] == 'manage_modules')
	{
		adv_sidebox_admin_manage_modules();
	}

	if($mybb->input['action'] == 'custom_boxes')
	{
		adv_sidebox_admin_custom_boxes();
	}

	if($mybb->input['action'] == 'delete_box')
	{
		if(isset($mybb->input['box']))
		{
			if($adv_sidebox->sideboxes[$mybb->input['box']]->valid)
			{
				$status = $adv_sidebox->sideboxes[$mybb->input['box']]->remove();
			}

			if($status)
			{
				flash_message($lang->adv_sidebox_delete_box_success, "success");
			}
		}
		else
		{
			flash_message($lang->adv_sidebox_delete_box_failure, "error");
		}
		admin_redirect(ADV_SIDEBOX_URL);
	}

	// delete module
	if($mybb->input['action'] == 'delete_addon')
	{
		// info goof?
		if(isset($mybb->input['addon']))
		{
			$this_module = $mybb->input['addon'];

			if($adv_sidebox->addons[$this_module]->valid)
			{
				$status = $adv_sidebox->addons[$this_module]->remove();
			}

			if($status)
			{
				// yay
				flash_message($lang->adv_sidebox_delete_addon_success, "success");
				admin_redirect(ADV_SIDEBOX_URL . '&amp;action=manage_modules');
			}
		}

		// why me?
		flash_message($lang->adv_sidebox_delete_addon_failure, "error");
		admin_redirect(ADV_SIDEBOX_URL . '&amp;action=manage_modules');
	}

	// update the theme exclude select box (in ACP settings) to reflect all themes
	if($mybb->input['action'] == 'update_theme_select')
	{
		$gid = adv_sidebox_get_settingsgroup();

		// is the group installed?
		if($gid)
		{
			$query = $db->simple_select('settings', '*', "name='adv_sidebox_exclude_theme'");

			// is the setting created?
			if($db->num_rows($query) == 1)
			{
				// update the setting
				$update_array = $db->fetch_array($query);
				$update_array['optionscode']	=	$db->escape_string(build_theme_exclude_select());
				$status = $db->update_query('settings', $update_array, "sid='" . $update_array['sid'] . "'");

				// success?
				if($status)
				{
					// tell them :)
					flash_message($lang->adv_sidebox_theme_exclude_select_update_success, "success");
					admin_redirect(adv_sidebox_build_settings_url($gid));
				}
			}
		}

		// settingsgroup doesn't exist
		flash_message($lang->adv_sidebox_theme_exclude_select_update_fail, "error");
		admin_redirect('index.php?module=config-settings');
	}

	exit();
}

/*
 * adv_sidebox_admin_main()
 *
 * main side box management page - drag and drop and standard controls for side boxes
 */
function adv_sidebox_admin_main()
{
	global $mybb, $db, $page, $lang, $adv_sidebox;

	// if there are add-on modules
	if(is_array($adv_sidebox->addons))
	{
		// display them
		foreach($adv_sidebox->addons as $module)
		{
			$box_type = $module->get_base_name();
			$title = $module->get_name();
			$title_url = ADV_SIDEBOX_MAIN_URL . "&amp;action=edit_box&addon={$box_type}";
			$title_link = "<a class=\"add_box_link\" href=\"{$title_url}\" title=\"Add a new side box of this type\">{$title}</a>";
			$id = "{$box_type}";

			// add the HTML
			$modules .= "<div id=\"{$id}\" class=\"draggable box_type\">
				{$title_link}
			</div>\n";

			// build the js to enable dragging
			$module_script .= "new Draggable('{$id}', { revert: true });
			";
		}
	}

	// if there are custom boxes
	if(is_array($adv_sidebox->custom))
	{
		// display them
		foreach($adv_sidebox->custom as $module)
		{
			$box_type = $module->get_base_name();
			$title = $module->get_name();
			$title_url = ADV_SIDEBOX_MAIN_URL . "&amp;action=edit_box&addon={$box_type}";
			$title_link = "<a class=\"add_box_link\" href=\"{$title_url}\" title=\"Add a new side box of this type\">{$title}</a>";
			$id = "{$box_type}";

			// add the HTML
			$custom_boxes .= "<div id=\"{$id}\" class=\"draggable custom_type\">
				{$title_link}
			</div>\n";

			// build the js to enable dragging
			$module_script .= "new Draggable('{$id}', { revert: true });
			";
		}
	}

	// if there are side boxes
	if(is_array($adv_sidebox->sideboxes))
	{
		// display them
		foreach($adv_sidebox->sideboxes as $sidebox)
		{
			// build the side box
			$box = adv_sidebox_build_sidebox_info($sidebox);

			// and sort it by position
			if($sidebox->get_position())
			{
				$right_boxes .= $box;
			}
			else
			{
				$left_boxes .= $box;
			}
		}
	}

	$page->add_breadcrumb_item($lang->adv_sidebox_name);

	// establish the sortable columns
	$page->extra_header .= <<<EOF
	<script language="JavaScript">
			columns = ['left_column','right_column', 'trash_column'];
	</script>
EOF;

	// custom CSS
	$page->extra_header .= '<link rel="stylesheet" type="text/css" href="' . $mybb->settings['bburl'] . '/inc/plugins/adv_sidebox/adv_sidebox_acp.css" media="screen" />';

	// scriptaculous drag and drop and effects
	$page->extra_header .= "<script src=\"../jscripts/scriptaculous.js?load=effects,dragdrop,controls\" type=\"text/javascript\"></script>\n";

	// modal forms
	$page->extra_header .=  "<script src=\"jscripts/imodal.js\" type=\"text/javascript\"></script>\n";
	$page->extra_header .=  "<link rel=\"stylesheet\" type=\"text/css\" href=\"styles/default/imodal.css\" />\n";

	// custom JS
	$page->extra_header .=  "<script src=\"/jscripts/adv_sidebox_acp.js\" type=\"text/javascript\"></script>\n";

	adv_sidebox_output_header();
	adv_sidebox_output_tabs('adv_sidebox');

	// build the display
	$html = <<<EOF
	<div class="demo" id="droppable_container">
		<table width="100%" class="back_drop">
			<tr>
				<td width="18%" class="column_head">Add-on Modules</td>
				<td width="18%" class="column_head">Custom</td>
				<td width="30%" class="column_head">Left</td>
				<td width="30%" class="column_head">Right</td>
			</tr>
			<tr>
				<td id="addon_menu" valign="top" rowspan="2">
					{$modules}
				</td>
				<td id="custom_menu" valign="top" rowspan="2">
					{$custom_boxes}
				</td>
				<td id="left_column" valign="top" class="column forum_column">
					{$left_boxes}
				</td>
				<td id="right_column" valign="top" class="column forum_column">
					{$right_boxes}
				</td>
			</tr>
			<tr height="45px;">
				<td id="trash_column" class="column trashcan" colspan="2"></td>
			</tr>
		</table>
	</div>
	<script type="text/javascript">
		// <![CDATA[
			build_sortable('left_column');
			build_sortable('right_column');
			build_sortable('trash_column');
			$$("a[id^='edit_sidebox_']").invoke
			(
				'observe',
				'click',
				function(event)
				{
					Event.stop(event);
				}
			);
			$$('.del_icon').each
			(
				function(e)
				{
					e.remove();
				}
			);
			$$('.add_box_link').each
			(
				function(e)
				{
					e.replace(e.innerHTML);
				}
			);
			{$module_script}
		// ]]>
	 </script>
EOF;
	// and display it
	echo($html);

	// output the link menu and MyBB footer
	adv_sidebox_output_footer('manage_sideboxes');
}

/*
 * adv_sidebox_sidebox_xmlhttp()
 *
 * handler for AJAX side box routines
 */
function adv_sidebox_sidebox_xmlhttp()
{
	global $db, $mybb, $adv_sidebox;

	// if ordering (or trashing)
	if($mybb->input['mode'] == 'order')
	{
		$left_column = '';
		$right_column = '';
		parse_str($mybb->input['data']);

		if($mybb->input['pos'] == 'trash_column')
		{
			// if there is anything in the sidebox
			if(is_array($trash_column) && !empty($trash_column))
			{
				// loop through them all
				foreach($trash_column as $id)
				{
					// and if they are valid side boxes
					if($adv_sidebox->sideboxes[$id]->valid)
					{
						// remove them
						$adv_sidebox->sideboxes[$id]->remove();

						// return the removed side boxes id to the AJAX object (so that the div can be destroyed as well)
						echo($id);
					}
				}
			}
		}
		elseif($mybb->input['pos'] == 'right_column')
		{
			$pos = 1;
			$this_column = $right_column;
			$side = 'right';
		}
		elseif($mybb->input['pos'] == 'left_column')
		{
			$pos = 0;
			$this_column = $left_column;
			$side = 'left';
		}

		// if there are side boxes in this column after the move (this function is called by onUpdate)
		if(is_array($this_column) && !empty($this_column))
		{
			$disp_order = 1;

			// loop through all the side boxes in this column
			foreach($this_column as $id)
			{
				$has_changed = false;

				// get some info
				$this_order = (int) ($disp_order * 10);
				++$disp_order;
				$current_order = $adv_sidebox->sideboxes[$id]->get_display_order();
				$original_pos = $adv_sidebox->sideboxes[$id]->get_position();

				// if the order has edited
				if($current_order != $this_order)
				{
					// handle it
					$adv_sidebox->sideboxes[$id]->set_display_order($this_order);
					$has_changed = true;
				}

				// if the position has changed
				if($original_pos != $pos)
				{
					// alter it
					$adv_sidebox->sideboxes[$id]->set_position($side);
					$has_changed = true;
				}

				// if the side box has been modified
				if($has_changed != false)
				{
					// save it
					$adv_sidebox->sideboxes[$id]->save();
				}
			}
		}
	}
	// this routine allows the side box's visibility tool tip and links to be handled by JS after the side box is created
	elseif($mybb->input['mode'] == 'build_info')
	{
		// bad info?
		if($mybb->input['box'] > 0)
		{
			// we have to redo our observance of the edit links (all of them) when this one is added/updated
			$script = <<<EOF
<script type="text/javascript">
	$$("a[id^='edit_sidebox_']").invoke
	(
		'observe',
		'click',
		function(event)
		{
			// stop the link from redirecting the user-- set up this way so that if JS is disabled the user goes to a standard form rather than a modal edit form
			Event.stop(event);

			// create the modal edit box dialogue
			new MyModal
			(
				{
					type: 'ajax',
					url: this.readAttribute('href') + '&ajax=1'
				}
			);
		}
	);
</script>
EOF;
			// this HTML output will be directly stored in the side box's representative <div>
			echo adv_sidebox_build_sidebox_info($adv_sidebox->sideboxes[$mybb->input['box']], false, true) . $script;
		}
	}
}

/*
 * adv_sidebox_admin_edit()
 *
 * handles the modal/JavaScript edit box and also (as a backup) displays a standard form for those with JavaScript disabled
 */
function adv_sidebox_admin_edit()
{
	global $page, $lang, $mybb, $db, $adv_sidebox, $page;

	// saving?
	if($mybb->request_method == 'post')
	{
		// start with a new box
		$this_sidebox = new Sidebox();

		// if called by JS
		if($mybb->input['ajax'] == 1)
		{
			// the position will be stored in a hidden field
			$this_sidebox->set_position($mybb->input['pos']);
		}
		else
		{
			// otherwise we get our position from the form field
			$this_sidebox->set_position($mybb->input['box_position']);
		}

		// store it
		$position = $this_sidebox->get_position();

		// help them keep their display orders spaced
		if(!isset($mybb->input['display_order']) || (int) $mybb->input['display_order'] == 0)
		{
			// get a total number of sideboxes on the same side and put it at the bottom
			$query = $db->simple_select('sideboxes', 'display_order', "position='{$position}'");

			$display_order = (int) (($db->num_rows($query) + 1) * 10);
		}
		else
		{
			// or back off if they entered a value
			$display_order = (int) $mybb->input['display_order'];
		}

		$this_sidebox->set_display_order($display_order);

		// if we are handling an AJAX request
		if($mybb->input['ajax'] == 1)
		{
			// then we need to convert the input to an array
			$script_list = explode(",", $mybb->input['script_select_box'][0]);
			$group_list = explode(",", $mybb->input['group_select_box'][0]);
		}
		else
		{
			// otherwise store all the scripts as is
			if(is_array($mybb->input['script_select_box']))
			{
				$script_list = $mybb->input['script_select_box'];
			}
			else
			{
				// if there are no scripts then make sure we pass an empty array
				$script_list = array();
			}

			// same with the groups
			if(is_array($mybb->input['group_select_box']))
			{
				$group_list = $mybb->input['group_select_box'];
			}
			else
			{
				$group_list = array();
			}
		}

		// store them
		$this_sidebox->set_scripts($script_list);
		$this_sidebox->set_groups($group_list);

		// box_type
		$this_sidebox->set_box_type($mybb->input['addon']);

		// store it locally
		$module = $this_sidebox->get_box_type();

		// id
		$this_sidebox->set_id($mybb->input['box']);

		// is this side box create by an add-on module?
		if($adv_sidebox->addons[$module]->valid)
		{
			$this_sidebox->set_wrap_content($adv_sidebox->addons[$module]->get_wrap_content());
			$addon_settings = $adv_sidebox->addons[$module]->get_settings();

			// if the parent module has settings . . .
			if(is_array($addon_settings))
			{
				$settings = array();

				// loop through them
				foreach($addon_settings as $setting)
				{
					// and if the setting has a value
					if(isset($mybb->input[$setting['name']]))
					{
						// store it
						$setting['value'] = $mybb->input[$setting['name']];
						$settings[$setting['name']] = $setting;
					}
				}

				$this_sidebox->set_settings($settings);
			}
		}
		else
		{
			// did this box come from a custom static box?
			if($adv_sidebox->custom[$module]->valid)
			{
				// then use its wrap_content property
				$this_sidebox->set_wrap_content($adv_sidebox->custom[$module]->get_wrap_content());
			}
			else
			{
				// otherwise wrap the box
				$this_sidebox->set_wrap_content(true);
			}
		}

		// if the text field isn't empty . . .
		if(isset($mybb->input['box_title']) && $mybb->input['box_title'])
		{
			// use it
			$this_sidebox->set_display_name($mybb->input['box_title']);
		}
		else
		{
			// otherwise, check the hidden field (original title)
			if(isset($mybb->input['current_title']) && $mybb->input['current_title'])
			{
				// if it exists, use it
				$this_sidebox->set_display_name($mybb->input['current_title']);
			}
			else
			{
				// otherwise use the default title
				$this_sidebox->set_display_name($adv_sidebox->box_types[$this_sidebox->get_box_type()]);
			}
		}

		// save the side box
		$status = $this_sidebox->save();

		// AJAX?
		if($mybb->input['ajax'] == 1)
		{
			// get some info
			$id = (int) $this_sidebox->get_id();
			$column_id = 'left_column';
			if($position)
			{
				$column_id = 'right_column';
			}

			// creating a new box?
			if($mybb->input['box'] == '' || $mybb->input['box'] == 0)
			{
				// then escape the title
				$box_title = addcslashes($this_sidebox->get_display_name(), "'");

				// and create the new <div> representation of the side box (title only it will be filled in later by the updater)
				$script = "<script type=\"text/javascript\">$('{$column_id}').highlight(); var new_box=document.createElement('div'); new_box.innerHTML='{$box_title}'; new_box.id='sidebox_{$id}'; new_box.setAttribute('class','sidebox'); new_box.style.position='relative'; $('{$column_id}').appendChild(new_box); build_sortable('{$column_id}'); build_droppable('{$column_id}'); new Ajax.Updater('sidebox_{$id}', \"index.php?module=config-adv_sidebox&action=xmlhttp&mode=build_info&box={$id}\",{ method:\"get\", evalScripts: true });</script>";
			}
			else
			{
				// if the box exists just update it
				$script = "<script type=\"text/javascript\">new Ajax.Updater('sidebox_{$id}', \"index.php?module=config-adv_sidebox&action=xmlhttp&mode=build_info&box={$id}\",{ method:\"get\" });</script>";
			}
			// the modal box will eval any scripts passed as output (that don't contain invalid characters.
			echo($script);
			die;
		}
		else
		{
			// if in the standard form handle it with a redirect
			flash_message($lang->adv_sidebox_save_success, "success");
			admin_redirect('index.php?module=config-adv_sidebox');
		}
	}

	// attempt to load the specified box
	$this_sidebox = new Sidebox((int) $mybb->input['box']);
	$box_id = (int) $this_sidebox->get_id();
	$module = $mybb->input['addon'];
	$pos = (int) $mybb->input['pos'];

	// AJAX?
	if($mybb->input['ajax'] == 1)
	{
		// the content is much different
		echo "<div id=\"ModalContentContainer\"><div class=\"ModalTitle\">Add A New Sidebox<a href=\"javascript:;\" id=\"modalClose\" class=\"float_right modalClose\">&nbsp;</a></div><div class=\"ModalContent\">";
		$form = new Form("", "post", "modal_form");
	}
	else
	{
		// standard form stuff
		$page->add_breadcrumb_item($lang->adv_sidebox_name, ADV_SIDEBOX_URL);
		$page->add_breadcrumb_item($lang->adv_sidebox_add_a_sidebox);

		// add a little CSS
		$page->extra_header .= '<link rel="stylesheet" type="text/css" href="' . $mybb->settings['bburl'] . '/inc/plugins/adv_sidebox/adv_sidebox_acp.css" media="screen" />';
		adv_sidebox_output_header();
		$form = new Form('index.php?module=config-adv_sidebox&action=edit_box&box=' . $this_sidebox->get_id() . '&addon=' . $module, "post", "modal_form");
	}

	$tabs = array
	(
		"general"				=>	'General',
		"permissions"		=>	'Permissions',
		"pages"				=>	'Pages',
		"settings"				=>	'Settings'
	);

	// in the modal version we only need a Setttings tab if the current module type has settings
	$do_settings = true;
	if(!$this_sidebox->has_settings && !$adv_sidebox->addons[$module]->has_settings)
	{
		unset($tabs["settings"]);
		$do_settings = false;
	}
	reset($tabs);

	// AJAX - output tabs
	if($mybb->input['ajax'] == 1)
	{
		$page->output_tab_control($tabs, false);
	}

	$custom_title = 0;

	// if $this_sidebox exists it will have a non-zero id property . . .
	if($this_sidebox->get_id() == 0)
	{
		// if it doesn't then this is a new box, check the page view filter to try to predict which script the user will want
		if(isset($mybb->input['page']))
		{
			// start them out with the script they are viewing for Which Scripts
			switch($mybb->input['page'])
			{
				case 'index':
					$selected_scripts[] = 'index.php';
					break;
				case 'forum':
					$selected_scripts[] = 'forumdisplay.php';
					break;
				case 'thread':
					$selected_scripts[] = 'showthread.php';
					break;
				case 'member':
					$selected_scripts[] = 'member.php';
					break;
				case 'memberlist':
					$selected_scripts[] = 'memberlist.php';
					break;
				case 'showteam':
					$selected_scripts[] = 'showteam.php';
					break;
				case 'stats':
					$selected_scripts[] = 'stats.php';
					break;
				case 'portal':
					$selected_scripts[] = 'portal.php';
					break;
				// or all scripts if not filtering sideboxes
				default:
					$selected_scripts[] = 'all_scripts';
			}
		}
		else
		{
			// if page isn't set at all then just start out with all scripts
			$selected_scripts = 'all_scripts';
		}

		$custom_title = 0;
		$current_title = '';
	}
	else
	{
		// . . . otherwise we are editing so pull the actual info from the sidebox
		$selected_scripts = $this_sidebox->get_scripts();

		$module = $this_sidebox->get_box_type();

		// is this sidebox from an add-on?
		if($adv_sidebox->addons[$module]->valid == true)
		{
			// check the name of the add-on against the display name of the sidebox, if they differ . . .
			if($this_sidebox->get_display_name() != $adv_sidebox->addons[$module]->get_name())
			{
				// then this box has a custom title
				$custom_title = 1;
			}
		}
		// is this side box from a custom static box?
		elseif($adv_sidebox->custom[$module]->valid == true)
		{
			// if so, then is the title different than the original?
			if($this_sidebox->get_display_name() != $adv_sidebox->custom[$module]->get_name())
			{
				// custom title
				$custom_title = 1;
			}
		}
		else
		{
			// default title
			$custom_title = 0;
		}
	}

	// custom title?
	if($custom_title == 1)
	{
		// alter the descrption
		$current_title = '<br /><em>' . $lang->adv_sidebox_current_title . '</em><br /><br /><strong>' . $this_sidebox->get_display_name() . '</strong><br />' . $lang->adv_sidebox_current_title_info;
	}
	else
	{
		// default description
		$current_title = '<br />' . $lang->adv_sidebox_default_title_info;
	}

	// current editing text
	if($adv_sidebox->addons[$module]->valid)
	{
		$currently_editing = '"' . $adv_sidebox->addons[$module]->get_name() . '"';
	}
	else
	{
		if($adv_sidebox->custom[$module]->valid)
		{
			$currently_editing = '"' . $adv_sidebox->custom[$module]->get_name() . '"';
		}
		else
		{
			$currently_editing = 'a custom';
		}
	}

	$box_action = 'Creating';
	if(isset($mybb->input['box']))
	{
		$box_action = 'Editing';
	}

	echo "<div id=\"tab_general\">\n";
	$form_container = new FormContainer('<h3>' . $box_action . ' A New ' . $currently_editing . ' Side Box</h3>');

	if($mybb->input['ajax'] != 1)
	{
		// box title
		$form_container->output_row($lang->adv_sidebox_custom_title, $current_title, $form->generate_text_box('box_title'), 'box_title', array("id" => 'box_title'));

		// position
		$form_container->output_row($lang->adv_sidebox_position, '', $form->generate_radio_button('box_position', 'left', $lang->adv_sidebox_position_left, array("checked" => ($this_sidebox->get_position() == 0))) . '&nbsp;&nbsp;' . $form->generate_radio_button('box_position', 'right', $lang->adv_sidebox_position_right, array("checked" => ($this_sidebox->get_position() != 0))));

		// display order
		$form_container->output_row($lang->adv_sidebox_display_order, '', $form->generate_text_box('display_order', $this_sidebox->get_display_order()));
	}
	else
	{
		// box title
		$form_container->output_row('', '', $form->generate_text_box('box_title') . '<br />' . $current_title, 'box_title', array("id" => 'box_title'));
	}

	// hidden forms to pass info to post
	$form_container->output_row('', '', $form->generate_hidden_field('current_title', $this_sidebox->get_display_name()) . $form->generate_hidden_field('pos', $pos));
	$form_container->end();

	echo "</div><div id=\"tab_permissions\">\n";
	$form_container = new FormContainer($lang->adv_sidebox_which_groups);

	// prepare options for which groups
	$options = array();
	$groups = array();
	$options['all'] = $lang->adv_sidebox_all_groups;
	$options['guests'] = $lang->adv_sidebox_guests;

	// look for all groups except Super Admins
	$query = $db->simple_select("usergroups", "gid, title", "gid != '1'", array('order_by' => 'gid'));
	while($usergroup = $db->fetch_array($query))
	{
		// store them their titles by groud id
		$options[(int)$usergroup['gid']] = $usergroup['title'];
	}

	// do we have groups stored?
	if(is_array($this_sidebox->groups_array) && !empty($this_sidebox->groups_array))
	{
		// then use them
		$groups = $this_sidebox->groups_array;
	}
	else
	{
		// otherwise just start with all groups
		$groups = 'all';
	}

	// which groups
	$form_container->output_row('', '', $form->generate_select_box('group_select_box[]', $options, $groups, array('id' => 'group_select_box', 'multiple' => true, 'size' => 5)));
	$form_container->output_row('', '', $form->generate_hidden_field('this_group_count', count($options)));

	$form_container->end();

	echo "</div><div id=\"tab_pages\">\n";
	$form_container = new FormContainer($lang->adv_sidebox_which_scripts);

	// prepare for which scripts
	$choices = array();
	$choices["all_scripts"] = $lang->adv_sidebox_all;

	// are there active scripts?
	if(is_array($adv_sidebox->all_scripts))
	{
		// loop through them
		foreach($adv_sidebox->all_scripts as $script)
		{
			// prepare info
			$filename = $script . '.php';
			$language_name = 'adv_sidebox_' . $script;

			// exceptions
			switch($script)
			{
				case 'forumdisplay':
					$language_name = 'adv_sidebox_forum';
					break;
				case 'showthread':
					$language_name = 'adv_sidebox_thread';
					break;
			}

			// store the script as a choice
			$choices[$filename] = $lang->$language_name;
		}
	}

	// if there are few scripts to choose from, alter the layout and/or wording of choices
	switch(count($choices))
	{
		case 3:
			$choices['all_scripts'] = $lang->adv_sidebox_both_scripts;
			break;
		case 2:
			unset($choices['all_scripts']);
			break;
		case 1:
			$choices['all_scripts'] = $lang->adv_sidebox_all_scripts_disabled;
			break;
	}

	// which scripts
	$form_container->output_row('', '', $form->generate_select_box('script_select_box[]', $choices, $selected_scripts, array("id" => 'script_select_box', "multiple" => true)));
	$form_container->end();

	if($do_settings)
	{
		echo "</div><div id=\"tab_settings\">\n";

		$form_container = new FormContainer("Custom Module Settings");

		if($box_id)
		{
			$sidebox_settings = $this_sidebox->get_settings();
		}
		else
		{
			$sidebox_settings = $adv_sidebox->addons[$module]->get_settings();
		}

		if(is_array($sidebox_settings))
		{
			foreach($sidebox_settings as $setting)
			{
				// allow the handler to build module settings
				$adv_sidebox->build_setting($form, $form_container, $setting, $box_id, $module);
			}
		}

		$form_container->end();
	}

	if($mybb->input['ajax'] == 1)
	{
		echo "</div><div class=\"ModalButtonRow\">";

		$buttons[] = $form->generate_submit_button('Cancel', array('id' => 'modalCancel'));
		$buttons[] = $form->generate_submit_button('Save', array('id' => 'modalSubmit'));
		$form->output_submit_wrapper($buttons);
		echo "</div>";
		$form->end();
		echo "</div>";
	}
	else
	{
		// finish form and page
		$buttons[] = $form->generate_submit_button('Save', array('name' => 'save_box_submit'));
		$form->output_submit_wrapper($buttons);
		$form->end();
	}
}

/*
 * adv_sidebox_admin_manage_modules()
 *
 * view and delete addons
 */
function adv_sidebox_admin_manage_modules()
{
	global $lang, $mybb, $db, $page, $adv_sidebox;

	$page->add_breadcrumb_item($lang->adv_sidebox_name, ADV_SIDEBOX_URL);
	$page->add_breadcrumb_item($lang->adv_sidebox_manage_modules);

	// add a little CSS
	$page->extra_header .= '<link rel="stylesheet" type="text/css" href="' . $mybb->settings['bburl'] . '/inc/plugins/adv_sidebox/adv_sidebox_acp.css" media="screen" />';
	adv_sidebox_output_header();
	adv_sidebox_output_tabs('adv_sidebox_modules');

	$table = new Table;
	$table->construct_header($lang->adv_sidebox_custom_box_name);
	$table->construct_header($lang->adv_sidebox_custom_box_desc);
	$table->construct_header($lang->adv_sidebox_controls);

	// if there are installed modules display them
	if(!empty($adv_sidebox->addons) && is_array($adv_sidebox->addons))
	{
		foreach($adv_sidebox->addons as $this_module)
		{
			$this_module->build_table_row($table);
		}
	}
	else
	{
		$table->construct_cell('<span style="color: #888;">' . $lang->adv_sidebox_no_modules_detected . '</span>', array("colspan" => 3));
		$table->construct_row();
	}

	$table->output();

	// build link bar and ACP footer
	adv_sidebox_output_footer('addons');
}

/*
 * adv_sidebox_admin_custom_boxes()
 *
 * Handle user-defined box types
 */
function adv_sidebox_admin_custom_boxes()
{
	global $lang, $mybb, $db, $page, $adv_sidebox;

	if($mybb->input['mode'] == 'export')
	{
		if(isset($mybb->input['box']) && (int) $mybb->input['box'] > 0)
		{
			if(!$adv_sidebox->custom['asb_custom_' . $mybb->input['box']]->valid)
			{
				flash_message($lang->adv_sidebox_custom_export_error,'error');
				admin_redirect(ADV_SIDEBOX_EXPORT_URL);
			}

			$adv_sidebox->custom['asb_custom_' . $mybb->input['box']]->export();
			exit();
		}
	}

	if($mybb->input['mode'] == 'delete_box')
	{
		// info good?
		if(isset($mybb->input['box']))
		{
			// nuke it
			$this_box = new Custom_type((int) $mybb->input['box']);

			$status = $this_box->remove();

			// success?
			if($status)
			{
				// delete all boxes of this type in use
				$db->query("DELETE FROM " . TABLE_PREFIX . "sideboxes WHERE box_type='asb_custom_" . (int) $mybb->input['box'] . "'");

				// :)
				flash_message($lang->adv_sidebox_add_custom_box_delete_success, "success");
				admin_redirect(ADV_SIDEBOX_CUSTOM_URL);
			}
		}

		// :(
		flash_message($lang->adv_sidebox_add_custom_box_delete_failure, "error");
		admin_redirect(ADV_SIDEBOX_CUSTOM_URL);
	}

		// POSTing?
	if($mybb->request_method == "post")
	{
		if($mybb->input['mode'] == 'import')
		{
			if(!$_FILES['file'] || $_FILES['file']['error'] == 4)
			{
				$error = $lang->adv_sidebox_custom_import_no_file;
			}
			elseif($_FILES['file']['error'])
			{
				$error = $lang->sprintf($lang->adv_sidebox_custom_import_file_error, $_FILES['file']['error']);
			}
			else
			{
				if(!is_uploaded_file($_FILES['file']['tmp_name']))
				{
					$error = $lang->adv_sidebox_custom_import_file_upload_error;
				}
				else
				{
					$contents = @file_get_contents($_FILES['file']['tmp_name']);
					@unlink($_FILES['file']['tmp_name']);
					if(!trim($contents))
					{
						$error = $lang->adv_sidebox_custom_import_file_empty;
					}
				}
			}

			if(!$error)
			{
				require_once MYBB_ROOT . 'inc/class_xml.php';
				$parser = new XMLParser($contents);
				$tree = $parser->get_tree();

				if(!is_array($tree) || !is_array($tree['adv_sidebox']) || !is_array($tree['adv_sidebox']['attributes']) || !is_array($tree['adv_sidebox']['custom_sidebox']))
				{
					$error = $lang->adv_sidebox_custom_import_file_empty;
				}

				if(!$error)
				{
					foreach($tree['adv_sidebox']['custom_sidebox'] as $property => $value)
					{
						if($property == 'tag' || $property == 'value')
						{
							continue;
						}
						$input_array[$property] = $value['value'];
					}

					if($input_array['content'] && $input_array['checksum'] && my_strtolower(md5(base64_decode($input_array['content']))) == my_strtolower($input_array['checksum']))
					{
						$this_custom = new Custom_type(0);

						$this_custom->set_name($input_array['name']);
						$this_custom->set_description($input_array['description']);
						$this_custom->set_wrap_content((int) $input_array['wrap_content']);
						$this_custom->set_content(trim(base64_decode($input_array['content'])));

						$status = $this_custom->save();

						if(!$status)
						{
							$error = $lang->adv_sidebox_custom_import_save_fail;
						}
					}
					else
					{
						if($input_array['content'])
						{
							$error = $lang->adv_sidebox_custom_import_file_corrupted;
						}
						else
						{
							$error = $lang->adv_sidebox_custom_import_file_empty;
						}
					}
				}
			}

			if($error)
			{
				flash_message($error, 'error');
				admin_redirect(ADV_SIDEBOX_CUSTOM_URL);
			}
			else
			{
				flash_message($lang->adv_sidebox_custom_import_save_success, 'success');
				admin_redirect(ADV_SIDEBOX_CUSTOM_URL . '&amp;box=' . $this_custom->get_id());
			}
		}
		else
		{
			// saving?
			if($mybb->input['save_box_submit'] == 'Save')
			{
				$this_custom = new Custom_type((int) $mybb->input['box']);

				// get the info
				$this_custom->set_name($mybb->input['box_name']);
				$this_custom->set_description($mybb->input['box_description']);
				$this_custom->set_content($mybb->input['box_content']);

				if($mybb->input['wrap_content'] == 'yes')
				{
					$this_custom->set_wrap_content(true);
				}

				$status = $this_custom->save();

				// success?
				if($status)
				{
					// :)
					flash_message($lang->adv_sidebox_custom_box_save_success, "success");
				}
				else
				{
					// :(
					flash_message($lang->adv_sidebox_custom_box_save_failure, "error");
				}
				admin_redirect(ADV_SIDEBOX_CUSTOM_URL . '&amp;box=' . $this_custom->get_id());
			}
		}
	}

	$page->add_breadcrumb_item($lang->adv_sidebox_name, ADV_SIDEBOX_URL);
	$page->add_breadcrumb_item($lang->adv_sidebox_custom_boxes);

	// add a little CSS
	$page->extra_header .= '<link rel="stylesheet" type="text/css" href="' . $mybb->settings['bburl'] . '/inc/plugins/adv_sidebox/adv_sidebox_acp.css" media="screen" />';

	$queryadmin = $db->simple_select('adminoptions', '*', "uid='{$mybb->user['uid']}'");
	$admin_options = $db->fetch_array($queryadmin);

	if($admin_options['codepress'] != 0)
	{
		$page->extra_header .= '<link type="text/css" href="./jscripts/codepress/languages/codepress-mybb.css" rel="stylesheet" id="cp-lang-style" />
<script type="text/javascript" src="./jscripts/codepress/codepress.js"></script>
<script type="text/javascript">
CodePress.language=\'mybb\';
</script>';
	}

	adv_sidebox_output_header();
	adv_sidebox_output_tabs('adv_sidebox_custom');

	$table = new Table;
	$table->construct_header($lang->adv_sidebox_custom_box_name);
	$table->construct_header($lang->adv_sidebox_custom_box_desc);
	$table->construct_header($lang->adv_sidebox_controls, array("colspan" => 2));

	// if there are saved types . . .
	if(is_array($adv_sidebox->custom) && !empty($adv_sidebox->custom))
	{
		// display them
		foreach($adv_sidebox->custom as $this_custom)
		{
			$this_custom->build_table_row($table);
		}
	}
	else
	{
		// no saved types
		$table->construct_cell($lang->adv_sidebox_no_custom_boxes, array("colspan" => 4));
		$table->construct_row();
	}
	$table->output($lang->adv_sidebox_custom_box_types);

	echo('<br /><br />');

	$this_box = new Custom_type((int) $mybb->input['box']);

	// editing?
	if($this_box->get_id())
	{
		$specify_box = "&amp;box=" . $this_box->get_id();
		$currently_editing = ' - Editing: <strong>' . $this_box->get_name() . '</strong>';
	}
	else
	{
		// new box
		$specify_box = '';
		$this_box->set_content('
<tr>
	<td class="trow1">Place your custom content here. (HTML)</td>
</tr>
<tr>
	<td class="trow2">For example:</td>
</tr>
<tr>
	<td class="trow1"><strong>my custom content</td>
</tr>');
		$this_box->set_wrap_content(true);
	}

	$new_box_link = '<a href="' . ADV_SIDEBOX_CUSTOM_URL . '" title="' . $lang->adv_sidebox_add_custom_box_types . '"><img src="' . $mybb->settings['bburl'] . '/inc/plugins/adv_sidebox/images/add.png" style="margin-bottom: -3px;"/></a>&nbsp<a href="' . ADV_SIDEBOX_CUSTOM_URL . '" title="' . $lang->adv_sidebox_add_custom_box_types . '">' . $lang->adv_sidebox_add_custom_box_types . '</a><br /><br />';
	echo($new_box_link);

	$form = new Form(ADV_SIDEBOX_CUSTOM_URL . $specify_box, "post", "edit_box");
	$form_container = new FormContainer($lang->adv_sidebox_edit_box);

	$form_container->output_cell('Name');
	$form_container->output_cell('Description');
	$form_container->output_cell('Wrap Content?');
	$form_container->output_row('');

	//name
	$form_container->output_cell($form->generate_text_box('box_name', $this_box->get_name(), array("id" => 'box_name')));

	// description
	$form_container->output_cell($form->generate_text_box('box_description', $this_box->get_description()));

	// wrap content?
	$form_container->output_cell($form->generate_check_box('wrap_content', 'yes', $lang->adv_sidebox_custom_box_wrap_content_desc, array("checked" => $this_box->get_wrap_content())));
	$form_container->output_row('');

	$form_container->output_cell('Content' . $currently_editing, array("colspan" => 3));
	$form_container->output_row('');

	// content
	$form_container->output_cell($form->generate_text_area('box_content', $this_box->get_content(), array("id" => 'box_content', 'class' => 'codepress mybb', 'style' => 'width: 100%; height: 240px;')), array("colspan" => 3));
	$form_container->output_row('');

	// finish form
	$form_container->end();
	$buttons[] = $form->generate_submit_button('Save', array('name' => 'save_box_submit'));
	$form->output_submit_wrapper($buttons);
	$form->end();

	if($admin_options['codepress'] != 0)
	{
		echo '<script type="text/javascript">
Event.observe(\'edit_box\',\'submit\',function()
{
	if($(\'box_content_cp\'))
	{
		var area=$(\'box_content_cp\');
		area.id=\'box_content\';
		area.value=box_content.getCode();
		area.disabled=false;
	}
});
</script>';
	}

	echo('<br /><br />');

	$import_form = new Form(ADV_SIDEBOX_IMPORT_URL, 'post', '', 1);
	$import_form_container = new FormContainer($lang->adv_sidebox_custom_import);
	$import_form_container->output_row($lang->adv_sidebox_custom_import_select_file, '', $import_form->generate_file_upload_box('file'));
	$import_form_container->end();
	$import_buttons[] = $import_form->generate_submit_button($lang->adv_sidebox_custom_import, array('name' => 'import'));
	$import_form->output_submit_wrapper($import_buttons);
	$import_form->end();

	// build link bar and ACP footer
	adv_sidebox_output_footer('custom');
}

$plugins->add_hook('admin_config_action_handler', 'adv_sidebox_admin_action');

/*
 * adv_sidebox_admin_action()
 *
 * enables the new menu item
 *
 * @param - &$action is the current ACP action
 */
function adv_sidebox_admin_action(&$action)
{
	$action['adv_sidebox'] = array('active' => 'adv_sidebox');
}

$plugins->add_hook('admin_config_menu', 'adv_sidebox_admin_menu');

/*
 * adv_sidebox_admin_menu()
 *
 * Add an entry to the ACP Config page menu
 *
 * @param - &$sub_menu is the menu array we will add a member to.
 */
function adv_sidebox_admin_menu(&$sub_menu)
{
	global $lang;

	if(!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}

	end($sub_menu);
	$key = (key($sub_menu)) + 10;
	$sub_menu[$key] = array
	(
		'id' 		=> 'adv_sidebox',
		'title' 	=> $lang->adv_sidebox_name,
		'link' 		=> ADV_SIDEBOX_URL
	);
}

$plugins->add_hook('admin_config_permissions', 'adv_sidebox_admin_permissions');

/*
 * adv_sidebox_admin_permissions()
 *
 * Add an entry to admin permissions list
 *
 * @param - &$admin_permissions is the array of permission types we are adding an element to
 */
function adv_sidebox_admin_permissions(&$admin_permissions)
{
	global $lang;

	if(!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}

	$admin_permissions['adv_sidebox'] = $lang->adv_sidebox_admin_permissions_desc;
}

$plugins->add_hook("admin_config_settings_change", "adv_sidebox_serialize");

/*
 * adv_sidebox_serialize()
 *
 * Serialize the theme exclusion list selector
 */
function adv_sidebox_serialize()
{
    global $mybb;

    $mybb->input['upsetting']['adv_sidebox_exclude_theme'] = serialize($mybb->input['upsetting']['adv_sidebox_exclude_theme']);
}

/*
 * adv_sidebox_output_header()
 *
 * Output ACP headers for our pages
 */
function adv_sidebox_output_header()
{
    global $page, $lang;

	if(!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}

    $page->output_header($lang->adv_sidebox_name);
}

/*
 * adv_sidebox_output_tabs()
 *
 * Output ACP tabs for our pages
 *
 * @param - $current is the tab currently being viewed
 */
function adv_sidebox_output_tabs($current)
{
	global $page, $lang, $mybb;

	if(!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}

    // set up tabs
	$sub_tabs['adv_sidebox'] = array
	(
		'title' 				=> $lang->adv_sidebox_manage_sideboxes,
		'link' 					=> ADV_SIDEBOX_URL,
		'description' 		=> $lang->adv_sidebox_manage_sideboxes_desc
	);
	$sub_tabs['adv_sidebox_custom'] = array
	(
		'title'					=> $lang->adv_sidebox_custom_boxes,
		'link'					=> ADV_SIDEBOX_CUSTOM_URL,
		'description'		=> $lang->adv_sidebox_custom_boxes_desc
	);
	$sub_tabs['adv_sidebox_modules'] = array
	(
		'title'					=> $lang->adv_sidebox_manage_modules,
		'link'					=> ADV_SIDEBOX_MODULES_URL,
		'description'		=> $lang->adv_sidebox_manage_modules_desc
	);
	$page->output_nav_tabs($sub_tabs, $current);
}

/*
 * adv_sidebox_output_footer()
 *
 * Output ACP footers for our pages
 */
function adv_sidebox_output_footer($page_key)
{
    global $page;

	echo(adv_sidebox_build_footer_menu($page_key));
	$page->output_footer();
}

/*
 * build_theme_exclude_select()
 *
 * rebuilds the theme exclude list ACP setting. used in cases where themes are added after the installation of Advanced Sidebox and the admin would like to exclude that theme.
 */
function build_theme_exclude_select()
{
	global $db;

	// Theme exclude list select box
	// Get all the themes that are not MasterStyles
	$query = $db->simple_select("themes", "tid, name, pid", "pid != '0'", array('order_by' => 'pid, name'));

	// Create a theme counter so our box is tidy
	$theme_count = 0;

	// Create an option for each theme and insert code to unserialize each option and 'remember' settings
	while($this_theme = $db->fetch_array($query))
	{
		$theme_select .= '<option value=\"' . $this_theme['tid'] . '\"" . (is_array(unserialize($setting[\'value\'])) ? ($setting[\'value\'] != "" && in_array("' . $this_theme['tid'] . '", unserialize($setting[\'value\'])) ? "selected=\"selected\"":""):"") . ">' . $this_theme['name'] . '</option>';

		++$theme_count;
	}

	// put it all together
	$theme_select = 'php
<select multiple name=\"upsetting[adv_sidebox_exclude_theme][]\" size=\"' . $theme_count . '\">' . $theme_select . '</select>';

	return $theme_select;
}

/*
 * adv_sidebox_build_settings_menu_link()
 *
 * produces a link to the plugin settings with icon
 */
function adv_sidebox_build_settings_menu_link()
{
	global $lang;

	return '<a href="' . adv_sidebox_build_settings_url(adv_sidebox_get_settingsgroup()) . '" title="' . $lang->adv_sidebox_plugin_settings . '"/><img src="styles/default/images/icons/custom.gif" alt="' . $lang->adv_sidebox_plugin_settings . '"/></a>&nbsp;' . adv_sidebox_build_settings_link();
}

/*
 * adv_sidebox_build_help_link()
 *
 * produces a link to a particular page in the plugin help system (with icon) specified by topic
 *
 * @param - $topic is the intended page's topic keyword
 */
function adv_sidebox_build_help_link($topic = '')
{
	global $mybb, $lang;

	if(!$topic)
	{
		$topic = 'main';
	}

	return '<a href="javascript:void()" onclick="window.open(\'' . ADV_SIDEBOX_HELP . '?topic=' . $topic . '\', \'mywindowtitle\', \'width=840, height=520, scrollbars=yes\')" title="Help"><img src="' . $mybb->settings['bburl'] . '/images/toplinks/help.gif" alt="help"/></a>&nbsp;<a href="javascript:void()" onclick="window.open(\'' . ADV_SIDEBOX_HELP . '?topic=' . $topic . '\', \'mywindowtitle\', \'width=840, height=520, scrollbars=yes\')" title="Help">Help</a>';
}

/*
 * adv_sidebox_build_footer_menu()
 *
 * @param - $page_key is the topic key name for the current page
 */
function adv_sidebox_build_footer_menu($page_key = '')
{
	global $mybb, $lang, $adv_sidebox;

	// a few general functions
	require_once MYBB_ROOT . 'inc/plugins/adv_sidebox/adv_sidebox_functions.php';

	if(!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}

	if(!$page_key)
	{
		$page_key = 'main';
	}

	$help_link = '&nbsp;' . adv_sidebox_build_help_link($page_key);
	$settings_link = '&nbsp;' . adv_sidebox_build_settings_menu_link();

	switch($page_key)
	{
		case "manage_sideboxes":
			$filter_links = adv_sidebox_build_filter_links($mybb->input['page']) . '<br /><br /><br />';
			break;
		case "edit_box":
			$settings_link = '';
			break;
		case "custom":
			break;
		case "edit_custom":
			$settings_link = '';
			break;
		case "import_custom":
			$settings_link = '';
			break;
		case "addons":
			$module_info = $adv_sidebox->build_addon_language() . ' -';
			break;
	}

	return '<div class="asb_label">' . $filter_links . $module_info . $settings_link . $help_link . '</div>';
}

/*
 * adv_sidebox_build_permissions_table()
 *
 * @param - $id is the numeric id of the sidebox
 */
function adv_sidebox_build_permissions_table($id)
{
	if($id)
	{
		global $db, $adv_sidebox, $lang;

		// prepare options for which groups
		$options = array();
		$groups = array();
		$options[0] = 'Guests';

		// look for all groups except Super Admins
		$query = $db->simple_select("usergroups", "gid, title", "gid != '1'", array('order_by' => 'gid'));
		while($usergroup = $db->fetch_array($query))
		{
			// store them their titles by groud id
			$options[(int)$usergroup['gid']] = $usergroup['title'];
		}

		// do we have groups stored?
		if(is_array($adv_sidebox->sideboxes[$id]->groups_array) && !empty($adv_sidebox->sideboxes[$id]->groups_array))
		{
			// then use them
			$groups = $adv_sidebox->sideboxes[$id]->groups_array;
		}
		else
		{
			// otherwise just start with all groups
			$groups = 'all';
		}

		$scripts = $adv_sidebox->sideboxes[$id]->get_scripts();

		$do_add = true;

		if(!is_array($scripts) && $scripts == 'all_scripts')
		{
			if($groups[0] == 'all')
			{
				return 'Globally Visible';
			}
			else
			{
				$scripts = $adv_sidebox->all_scripts;
				$do_add = false;
			}
		}

		if(is_array($adv_sidebox->all_scripts))
		{
			$all_group_count = count($options);
			$info = '<table width="100%" class="box_info"><tr><td class="group_header"><strong>Visibility</strong></td>';

			foreach($options as $gid => $title)
			{
				$info .= '<td title="' . $title . '" class="group_header">' . $gid . '</td>';
			}

			$info .= '</tr>';
			foreach($adv_sidebox->all_scripts as $script)
			{
				$script_langauge = 'adv_sidebox_abbr_' . $script;

				$info .= '<tr><td class="script_header">' . $lang->$script_langauge . '</td>';

				if($do_add)
				{
					$script .= '.php';
				}
				if(in_array($script, $scripts))
				{
					if($groups[0] == 'all' || $groups == '' || empty($groups))
					{
						$x = 1;
						while($x <= $all_group_count)
						{
							$info .= '<td class="info_cell on"></td>';
							++$x;
						}
					}
					else
					{
						if(is_array($options))
						{
							foreach($options as $gid => $title)
							{
								if(in_array($gid, $groups))
								{
									$info .= '<td class="info_cell on"></td>';
								}
								else
								{
									$info .= '<td class="info_cell off"></td>';
								}
							}
						}
					}
				}
				else
				{
					$x = 1;
					while($x <= $all_group_count)
					{
						$info .= '<td class="info_cell off"></td>';
						++$x;
					}
				}

				$info .= '</tr>';
			}

			$info .= '</table>';
		}
		else
		{
			$info = 'All Scripts Are Deactivated';
		}

		return $info;
	}
}

/*
 * adv_sidebox_build_sidebox_info()
 *
 * @param - $sidebox Sidebox type object xD
 * @param - $wrap specifies whether to produce the <div> or just the contents
 * @param - $ajax specifies whether to produce the delete link or not
 */
function adv_sidebox_build_sidebox_info($sidebox, $wrap = true, $ajax = false)
{
	// must be a valid object
	if($sidebox instanceof Sidebox)
	{
		$title = $sidebox->get_display_name();
		$id = $sidebox->get_id();
		$pos = $sidebox->get_position();
		$module = $sidebox->get_box_type();

		// visibility table
		$visibility = '<span class="custom info">' . adv_sidebox_build_permissions_table($id) . '</span>';

		// edit link
		$edit_link = "index.php?module=config-adv_sidebox&action=edit_box&box={$id}&addon={$module}&pos={$pos}";
		$edit_icon = "<a href=\"{$edit_link}\" class=\"info_icon\" id=\"edit_sidebox_{$id}\" title=\"Edit\"><img src=\"../inc/plugins/adv_sidebox/images/edit.png\" height=\"18\" width=\"18\" alt=\"Edit\"/></a>";

		// delete link (only used if JS is disabled)
		if(!$ajax)
		{
			$delete_link = "index.php?module=config-adv_sidebox&action=delete_box&box={$id}";
			$delete_icon = "<a href=\"{$delete_link}\" class=\"del_icon\" title=\"Delete\"><img src=\"../inc/plugins/adv_sidebox/images/delete.png\" height=\"18\" width=\"18\" alt=\"Delete\"/></a>";
		}

		// the content
		$box = "<span class=\"tooltip\"><img class=\"info_icon\" src=\"../inc/plugins/adv_sidebox/images/visibility.png\" alt=\"Information\" height=\"18\" width=\"18\"/>{$visibility}</span>{$edit_icon}{$delete_icon}{$title}";

		// the <div> (if applicable)
		if($wrap)
		{
			$box = "<div id=\"sidebox_{$id}\" class=\"sidebox\">" . $box . "</div>\n";
		}

		// return the content (which will either be stored in a string and displayed by adv_sidebox_main() or will be stored directly in the <div> when called from AJAX
		return $box;
	}
}

?>
