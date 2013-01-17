<?php
/*
 * This file contains the Admin Control Panel functions for this plugin
 *
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x v1.0
 * Copyright © 2012 Wildcard
 * http://www.rantcentralforums.com
 *
 * BASED UPON THE CONCEPT AND CODE CREATED BY NAYAR IN THE ORIGINAL SIDEBOX PLUGIN
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
	global $mybb, $db, $page, $lang, $plugins;

	if($page->active_action != 'adv_sidebox')
	{
		return false;
	}

	if(!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}

	// no action means the main page
	if(!$mybb->input['action'])
	{
		$mybb->input['action'] = 'manage_sideboxes';
	}

	if($mybb->input['action'] == 'manage_sideboxes')
	{
		adv_sidebox_manage_sideboxes();
	}

	if($mybb->input['action'] == 'edit_box')
	{
		adv_sidebox_admin_editbox();
	}

	if($mybb->input['action'] == 'manage_modules')
	{
		adv_sidebox_admin_manage_modules();
	}

	if($mybb->input['action'] == 'custom_boxes')
	{
		adv_sidebox_admin_custom_boxes();
	}

	// delete module
	if($mybb->input['action'] == 'install_addon')
	{
		// info given?
		if(isset($mybb->input['addon']))
		{
			require_once MYBB_ROOT . 'inc/plugins/adv_sidebox/adv_sidebox_classes.php';

			$this_module = new Sidebox_addon($mybb->input['addon']);

			$errors = $this_module->install();

			if(!$errors)
			{
				// tell them all is well
				flash_message($lang->adv_sidebox_install_addon_success, "success");
				admin_redirect(ADV_SIDEBOX_MODULES_URL);
			}
			else
			{
				// module no good
				flash_message($errors, "error");
				admin_redirect(ADV_SIDEBOX_MODULES_URL);
			}
		}
	}

	// uninstall module
	if($mybb->input['action'] == 'uninstall_addon')
	{
		// info given?
		if(isset($mybb->input['addon']))
		{
			require_once MYBB_ROOT . 'inc/plugins/adv_sidebox/adv_sidebox_classes.php';

			$this_module = new Sidebox_addon($mybb->input['addon']);

			$errors = $this_module->uninstall();

			if(!$errors)
			{
				// tell them all is well
				flash_message($lang->adv_sidebox_install_addon_success, "success");
				admin_redirect(ADV_SIDEBOX_MODULES_URL);
			}
			else
			{
				// module no good
				flash_message($errors, "error");
				admin_redirect(ADV_SIDEBOX_MODULES_URL);
			}
		}
		else
		{
			// :(
			flash_message($lang->adv_sidebox_uninstall_addon_failure, "error");
			admin_redirect(ADV_SIDEBOX_MODULES_URL);
		}
	}

	// delete module
	if($mybb->input['action'] == 'delete_addon')
	{
		// info goof?
		if(isset($mybb->input['addon']))
		{
			require_once MYBB_ROOT . 'inc/plugins/adv_sidebox/adv_sidebox_classes.php';

			$this_module = new Sidebox_addon($mybb->input['addon']);

			$errors = $this_module->remove();

			// yay
			flash_message($lang->adv_sidebox_delete_addon_success, "success");
			admin_redirect(ADV_SIDEBOX_URL . '&amp;action=manage_modules');
		}
		else
		{
			// why me?
			flash_message($lang->adv_sidebox_delete_addon_failure, "error");
			admin_redirect(ADV_SIDEBOX_URL . '&amp;action=manage_modules');
		}
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
				else
				{
					// weep
					flash_message($lang->adv_sidebox_theme_exclude_select_update_fail, "error");
					admin_redirect(adv_sidebox_build_settings_url($gid));
				}
			}
			else
			{
				// setting doesn't exist
				flash_message($lang->adv_sidebox_theme_exclude_select_update_fail, "error");
				admin_redirect(adv_sidebox_build_settings_url($gid));
			}
		}
		else
		{
			// settingsgroup doesn't exist
			flash_message($lang->adv_sidebox_theme_exclude_select_update_fail, "error");
			admin_redirect('index.php?module=config-settings');
		}
	}

	exit();
}

/*
 * adv_sidebox_manage_sideboxes()
 *
 * The default page
 */
function adv_sidebox_manage_sideboxes()
{
	global $mybb, $db, $page, $lang, $plugins;

	require_once MYBB_ROOT . 'inc/plugins/adv_sidebox/adv_sidebox_classes.php';
	require_once MYBB_ROOT . 'inc/plugins/adv_sidebox/adv_sidebox_functions.php';

	if (!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}

	// delete a sidebox
	if($mybb->input['function'] == 'delete_box')
	{
		// info given?
		if(isset($mybb->input['box']) && (int) $mybb->input['box'] > 0)
		{
			require_once MYBB_ROOT . 'inc/plugins/adv_sidebox/adv_sidebox_classes.php';

			$this_sidebox = new Sidebox();
			$this_sidebox->id = (int) $mybb->input['box'];

			$status = $this_sidebox->remove();
		}
		else
		{
			// no info, give error
			flash_message($lang->adv_sidebox_delete_box_failure, "error");
			admin_redirect(ADV_SIDEBOX_MAIN_URL . '&amp;mode=' . $mybb->input['mode']);
		}

		// success?
		if($status)
		{
			flash_message($lang->adv_sidebox_delete_box_success, "success");
			admin_redirect(ADV_SIDEBOX_MAIN_URL . '&amp;mode=' . $mybb->input['mode']);
		}
		else
		{
			// fail
			flash_message($lang->adv_sidebox_delete_box_failure, "error");
			admin_redirect(ADV_SIDEBOX_MAIN_URL . '&amp;mode=' . $mybb->input['mode']);
		}
	}

	$page->add_breadcrumb_item($lang->adv_sidebox_name);

	// add a little CSS
	$page->extra_header .= '<style type="text/css">
.asb_label
{
background: #EBF3FF;
color: #333;
font-weight: bold;
width: 100%
margin: auto auto;
padding: 5px;
border: 1px solid #85B1EE;
text-align: center;
}

.asb_label a:hover, a:active
{
color: #333;
}

.asb_label img
{
margin-bottom: -3px;
}
</style>';
	adv_sidebox_output_header();
	adv_sidebox_output_tabs('adv_sidebox');

	// add the only internal box type
	$box_types = array(
		'custom_box' 		=> $lang->adv_sidebox_custom
			);

	// get all the user-defined types
	$custom_box_types = get_all_custom_box_types();
	if(!empty($custom_box_types))
	{
		$box_types = array_merge($box_types, $custom_box_types);
	}

	// get all the box types from plugins
	$plugins->run_hooks('adv_sidebox_box_types', $box_types);

	// get all the box types from modules
	$all_modules = get_all_modules($box_types);

	if(!empty($all_modules))
	{
		adv_sidebox_count_modules($all_modules, $count_instmods, $count_uninstmods, $count_simpmods);
	}

	// Sideboxes table
	$left_table = new Table;
	$left_table->construct_header($lang->adv_sidebox_box_type);
	$left_table->construct_header($lang->adv_sidebox_scripts);
	$left_table->construct_header($lang->adv_sidebox_controls, array("colspan" => 2));

	$right_table = new Table;
	$right_table->construct_header($lang->adv_sidebox_box_type);
	$right_table->construct_header($lang->adv_sidebox_scripts);
	$right_table->construct_header($lang->adv_sidebox_controls, array("colspan" => 2));

	$sideboxes = array();
	$sideboxes = adv_sidebox_get_all_sideboxes();

	// if there are sideboxes . . .
	if(!empty($sideboxes))
	{
		$left_box = false;
		$right_box = false;

		foreach($sideboxes as $box)
		{
			if(adv_sidebox_filter_by_script($box, $mybb->input['mode']))
			{
				// if this is the first right box . . .
				if((int) $box->position)
				{
					// and add the label
					$right_box = true;

					$box->build_table_row($right_table);
				}
				else
				{
					// otherwise its a left box
					$left_box = true;
					$box->build_table_row($left_table);
				}
			}
		}
	}

	// if there were no left boxes . . .
	if(!$left_box)
	{
		// let them know
		$left_table->construct_cell('<span style="color: #888"><p>' . $lang->adv_sidebox_no_boxes_left . '</p></span>', array("colspan" => 5));
		$left_table->construct_row();
	}

	// if there were no right boxes . . .
	if(!$right_box)
	{
		// tell them what they already know
		$right_table->construct_cell('<span style="color: #888"><p>' . $lang->adv_sidebox_no_boxes_right . '</p></span>', array("colspan" => 5));
		$right_table->construct_row();
	}

	// output the box table
	echo('<table style="width: 100%;"><tr><td valign="top" style="width: 50%;">');
	$left_table->output('Left');
	echo('</td><td valign="top" style="width: 50%;">');
	$right_table->output('Right');
	echo('</td></tr></table><br />');

	$filter_links = adv_sidebox_build_filter_links($mybb->input['mode']);

	$module_info .= adv_sidebox_build_module_info_language($count_instmods, $count_uninstmods, $count_simpmods);

	// build link bar
	$module_info .= " - <a href=\"" . ADV_SIDEBOX_MODULES_URL . "\">{$lang->adv_sidebox_manage_modules}</a>";
	$settings_link = adv_sidebox_build_settings_link();
	echo('<div class="asb_label">' . $filter_links . '<br /><br /><a href="' . ADV_SIDEBOX_EDIT_URL . '&amp;mode=' . $mybb->input['mode'] . '"><img src="' . $mybb->settings['bburl'] . '/inc/plugins/adv_sidebox/images/add.png" /></a>&nbsp;<a href="' . ADV_SIDEBOX_EDIT_URL . '&amp;mode=' . $mybb->input['mode'] . '">' . $lang->adv_sidebox_add_new_box . '</a> - ' . $module_info . ' - ' . $settings_link . '</div>');

	$page->output_footer();
}

/*
 * adv_sidebox_admin_editbox()
 *
 * Edit an exisiting sidebox or create a new one
 */
function adv_sidebox_admin_editbox()
{
	global $lang, $mybb, $db, $plugins, $page;

	if (!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}

	require_once MYBB_ROOT . 'inc/plugins/adv_sidebox/adv_sidebox_classes.php';
	require_once MYBB_ROOT . 'inc/plugins/adv_sidebox/adv_sidebox_functions.php';

	// add the one internal type
	$box_types = array(
		'custom_box' 		=> $lang->adv_sidebox_custom
			);

	// get all the user-defined types
	$custom_box_types = get_all_custom_box_types();
	if(!empty($custom_box_types))
	{
		$box_types = array_merge($box_types, $custom_box_types);
	}

	// get all the plugin types
	$plugins->run_hooks('adv_sidebox_box_types', $box_types);

	// get all module types
	get_all_modules($box_types);

	// POSTing?
	if($mybb->request_method == "post")
	{
		// saving?
		if($mybb->input['save_box_submit'] == 'Save')
		{
			$this_sidebox = new Sidebox();

			// help them keep their display orders spaced
			if(!isset($mybb->input['display_order']) || (int) $mybb->input['display_order'] == 0)
			{
				$query = $db->simple_select('sideboxes', 'display_order');

				$this_sidebox->display_order = ((int) $db->num_rows($query) + 1) * 10;
			}
			else
			{
				// or back off if they entered a value
				$this_sidebox->display_order = (int) $mybb->input['display_order'];
			}

			// translate the position
			if($mybb->input['box_position'] == 'right')
			{
				$this_sidebox->position = 1;
			}

			// if this isn't a custom box . . .
			if($mybb->input['box_type_select'] != 'custom_box')
			{
				// don't store the content at all
				$this_sidebox->content = '';
			}
			else
			{
				// otherwise store it
				$this_sidebox->content = $mybb->input['box_content'];
			}

			foreach($mybb->input['script_select_box'] as $this_entry)
			{
				if($this_entry == 'all_scripts')
				{
					$this_sidebox->show_on_index = true;
					$this_sidebox->show_on_forumdisplay = true;
					$this_sidebox->show_on_showthread = true;
					$this_sidebox->show_on_portal = true;
				}
				else
				{
					if($this_entry == 'index.php')
					{
						$this_sidebox->show_on_index = true;
					}

					if($this_entry == 'forumdisplay.php')
					{
						$this_sidebox->show_on_forumdisplay = true;
					}

					if($this_entry == 'showthread.php')
					{
						$this_sidebox->show_on_showthread = true;
					}

					if($this_entry == 'portal.php')
					{
						$this_sidebox->show_on_portal = true;
					}
				}
			}

			$this_sidebox->box_type = $mybb->input['box_type_select'];
			$this_sidebox->id = (int) $mybb->input['box'];
			
			$this_module = new Sidebox_addon($this_sidebox->box_type);
			
			$this_sidebox->stereo = $this_module->stereo;
			$this_sidebox->wrap_content = $this_module->wrap_content;
			$this_sidebox->display_name = $box_types[$this_sidebox->box_type];

			$status = $this_sidebox->save();

			// success?
			if($status)
			{
				// yay
				flash_message($lang->adv_sidebox_save_success, "success");
				admin_redirect(ADV_SIDEBOX_MAIN_URL . '&amp;mode=' . $mybb->input['this_mode']);
			}
			else
			{
				// :(
				flash_message($lang->adv_sidebox_save_fail, "error");
				admin_redirect(ADV_SIDEBOX_MAIN_URL . '&amp;mode=' . $mybb->input['this_mode']);
			}
		}
	}

	$page->add_breadcrumb_item($lang->adv_sidebox_name, ADV_SIDEBOX_URL);
	$page->add_breadcrumb_item($lang->adv_sidebox_add_a_sidebox);

	// add the script to hide the content box if it is unnecessary
	$page->extra_header .= '<script type="text/javascript" src="./jscripts/peeker.js"></script>
	<script type="text/javascript">Event.observe(window, "load", function() {var peeker = new Peeker($("box_type_select"), $("box_content"), /custom_box/, false);});
	</script>';

	// output ACP page stuff
	adv_sidebox_output_header();
	adv_sidebox_output_tabs('adv_sidebox_add');

	$this_sidebox = new Sidebox($mybb->input['box']);

	if($this_sidebox->box_type != 'custom_box')
	{
		// some sample custom content
		$this_sidebox->content = '
		<tr>
			<td class="trow1">Place your custom content here. HTML can be used in conjunction with certain template variables, language variables and environment variables.</td>
		</tr>
		<tr>
			<td class="trow2">For example:</td>
		</tr>
		<tr>
			<td class="trow1"><strong>User:</strong> {$mybb->user[\'username\']}</td>
		</tr>
		<tr>
			<td class="trow2"><strong>UID:</strong> {$mybb->user[\'uid\']}</td>
		</tr>
		<tr>
			<td class="trow1"><strong>Theme name:</strong> {$theme[\'name\']}</td>
		</tr>';
	}

	if($this_sidebox->id == 0)
	{
		$selected_scripts = 'all_scripts';
	}
	else
	{
		$selected_scripts = array();

		if($this_sidebox->show_on_index && $this_sidebox->show_on_forumdisplay && $this_sidebox->show_on_showthread && $this_sidebox->show_on_portal)
		{
			$selected_scripts = 'all_scripts';
		}
		else
		{
			if($this_sidebox->show_on_index)
			{
				$selected_scripts[] = 'index.php';
			}
			if($this_sidebox->show_on_forumdisplay)
			{
				$selected_scripts[] = 'forumdisplay.php';
			}
			if($this_sidebox->show_on_showthread)
			{
				$selected_scripts[] = 'showthread.php';
			}
			if($this_sidebox->show_on_portal)
			{
				$selected_scripts[] = 'portal.php';
			}
		}
	}

	$form = new Form(ADV_SIDEBOX_EDIT_URL. "&amp;box=" . $this_sidebox->id, "post", "edit_box");
	$form_container = new FormContainer($lang->adv_sidebox_edit_box);

	$form_container->output_row($lang->adv_sidebox_box_type, $lang->adv_sidebox_type_desc, $form->generate_select_box('box_type_select', $box_types, $this_sidebox->box_type, array("id" => 'box_type_select')), array("id" => 'box_type_select_box'));
	$form_container->output_row($lang->adv_sidebox_content_title, $lang->adv_sidebox_content_desc, $form->generate_text_area('box_content', $this_sidebox->content, array("id" => 'box_content')), array("id" => 'box_content'));
	$form_container->output_row($lang->adv_sidebox_position, '', $form->generate_radio_button('box_position', 'left', $lang->adv_sidebox_position_left, array("checked" => ((int) $this_sidebox->position == 0))) . '&nbsp;&nbsp;' . $form->generate_radio_button('box_position', 'right', $lang->adv_sidebox_position_right, array("checked" => ((int) $this_sidebox->position != 0))));
	$form_container->output_row($lang->adv_sidebox_display_order, '', $form->generate_text_box('display_order', $this_sidebox->display_order));
	$form_container->output_row('Which Scripts?', '', $form->generate_select_box('script_select_box[]', array("all_scripts" => 'All Scripts', "index.php" => 'Index', "forumdisplay.php" => 'Forums', "showthread.php" => 'Threads', "portal.php" => 'Portal'), $selected_scripts, array("id" => 'script_select_box', "multiple" => true)), array("id" => 'script_select_box'));
	$form_container->output_row('', '', $form->generate_hidden_field('this_mode', $mybb->input['mode']));
	$form_container->end();

	$buttons[] = $form->generate_submit_button('Save', array('name' => 'save_box_submit'));
	$form->output_submit_wrapper($buttons);
	$form->end();

	$page->output_footer();
}

/*
 * adv_sidebox_admin_manage_modules()
 *
 * Install/Uninstall/Delete addons
 */
function adv_sidebox_admin_manage_modules()
{
	global $lang, $mybb, $db, $plugins, $page;

	if (!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}

	$page->add_breadcrumb_item($lang->adv_sidebox_name, ADV_SIDEBOX_URL);
	$page->add_breadcrumb_item($lang->adv_sidebox_manage_modules);

	// add a little CSS
	$page->extra_header .= '<style type="text/css">
.asb_label
{
	background: #EBF3FF;
	color: #333;
	font-weight: bold;
	width: 100%
	margin: auto auto;
	padding: 5px;
	border: 1px solid #85B1EE;
	text-align: center;
}

.asb_label a:hover, a:active
{
	color: #333;
}

.asb_label img
{
	margin-bottom: -3px;
}
</style>';
	adv_sidebox_output_header();
	adv_sidebox_output_tabs('adv_sidebox_modules');

	// add the one internal type
	$box_types = array(
		'custom_box' 		=> $lang->adv_sidebox_custom
			);

	// allow plugins to add types
	$plugins->run_hooks('adv_sidebox_box_types', $box_types);

	require_once MYBB_ROOT . 'inc/plugins/adv_sidebox/adv_sidebox_classes.php';
	require_once MYBB_ROOT . 'inc/plugins/adv_sidebox/adv_sidebox_functions.php';

	// get all module types
	$modules = get_all_modules($box_types);

	if(is_array($modules))
	{
		foreach($modules as $this_module)
		{
			if($this_module->module_type == 'simple')
			{
				$simple_modules[] = $this_module->base_name;
			}
			else
			{
				if($this_module->is_installed)
				{
					$installed_modules[] = $this_module->base_name;
				}
				else
				{
					$uninstalled_modules[] = $this_module->base_name;
				}
			}
		}
	}

	$table = new Table;
	$table->construct_header($lang->adv_sidebox_custom_box_name);
	$table->construct_header($lang->adv_sidebox_custom_box_desc);
	$table->construct_header($lang->adv_sidebox_controls, array("colspan" => 2));

	// if there are simple modules display them
	if(!empty($simple_modules))
	{
		$table->construct_cell("<div class=\"asb_label\">{$lang->adv_sidebox_simple_modules}</div>", array("colspan" => 4));
		$table->construct_row();

		foreach($simple_modules as $this_module)
		{
			if($box_info[$this_module]['stereo'] == true)
			{
				$type = $lang->adv_sidebox_modules_stereo;
			}
			else
			{
				$type = $lang->adv_sidebox_modules_mono;
			}

			$modules[$this_module]->build_table_row($table);
		}
	}

	// if there are installed modules display them
	if(!empty($installed_modules))
	{
		$table->construct_cell("<div class=\"asb_label\">{$lang->adv_sidebox_installed_modules}</div>", array("colspan" => 4));
		$table->construct_row();

		foreach($installed_modules as $this_module)
		{
			if($box_info[$this_module]['stereo'] == true)
			{
				$type = $lang->adv_sidebox_modules_stereo;
			}
			else
			{
				$type = $lang->adv_sidebox_modules_mono;
			}

			$modules[$this_module]->build_table_row($table);
		}
	}

	// If there are uninstalled modules display them
	if(!empty($uninstalled_modules))
	{
		$table->construct_cell("<div class=\"asb_label\">{$lang->adv_sidebox_uninstalled_modules}</div>", array("colspan" => 4));
		$table->construct_row();

		foreach($uninstalled_modules as $this_module)
		{
			if($box_info[$this_module]['stereo'] == true)
			{
				$type = $lang->adv_sidebox_modules_stereo;
			}
			else
			{
				$type = $lang->adv_sidebox_modules_mono;
			}

			$modules[$this_module]->build_table_row($table);
		}
	}

	// if there are no modules detected, tell them so
	if(empty($installed_modules) && empty($uninstalled_modules) && empty($simple_modules))
	{
		$table->construct_cell($lang->adv_sidebox_no_modules_detected, array("colspan" => 4));
		$table->construct_row();
	}
	$table->output();
	$page->output_footer();
}

/*
 * adv_sidebox_admin_custom_boxes()
 *
 * Handle user-defined box types
 */
function adv_sidebox_admin_custom_boxes()
{
	global $lang, $mybb, $db, $plugins, $page;

	if (!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}
	
	$info = adv_sidebox_info();
	
	if($mybb->input['mode'] == 'import')
	{
		if($mybb->request_method == "post")
		{
			if($mybb->input['import'])
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
					}
				}
				
				if($input_array['content'] && $input_array['checksum'] && my_strtolower(md5(base64_decode($input_array['content']))) == my_strtolower($input_array['checksum']))
				{
					unset($input_array['checksum']);
					
					$input_array['name'] = $db->escape_string($input_array['name']);
					$input_array['description'] = $db->escape_string($input_array['description']);
					$input_array['wrap_content'] = (int) $input_array['wrap_content'];
					$input_array['content'] = $db->escape_string(trim(base64_decode($input_array['content'])));
					
					$status = $db->insert_query('custom_sideboxes', $input_array);
					
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
				
				if($error)
				{
					flash_message($error, 'error');
					admin_redirect(ADV_SIDEBOX_IMPORT_URL);
				}
				else
				{
					flash_message($lang->adv_sidebox_custom_import_save_success, 'success');
					admin_redirect(ADV_SIDEBOX_CUSTOM_URL);
				}
			}
		}
		
		$page->add_breadcrumb_item($lang->adv_sidebox_name, ADV_SIDEBOX_URL);
		$page->add_breadcrumb_item($lang->adv_sidebox_custom_import);

		adv_sidebox_output_header();
		adv_sidebox_output_tabs('adv_sidebox_import');
		
		$form=new Form(ADV_SIDEBOX_IMPORT_URL, 'post', '', 1);
		$form_container = new FormContainer($lang->adv_sidebox_custom_import);
		$form_container->output_row($lang->adv_sidebox_custom_import_select_file, '', $form->generate_file_upload_box('file'));
		$form_container->end();
		
		$buttons[] = $form->generate_submit_button($lang->adv_sidebox_custom_import, array('name' => 'import'));
		$form->output_submit_wrapper($buttons);
		$form->end();
		
		$page->output_footer();
		exit();
	}
	
	if($mybb->input['mode'] == 'export')
	{
		if(isset($mybb->input['box']))
		{
			$query = $db->simple_select('custom_sideboxes', '*', "id='" . (int) $mybb->input['box'] . "'");
			
			$this_custom = $db->fetch_array($query);
			
			if(!$this_custom['id'])
			{
				flash_message($lang->adv_sidebox_custom_export_error,'error');
				admin_redirect(ADV_SIDEBOX_IMPORT_URL);
			}
			
			$xml='<?xml version="1.0" encoding="' . $lang->settings['charset'] . '"?>
<adv_sidebox version="' . $info['version'] . '" xmlns="' . $info['website'] . '">
	<custom_sidebox>
		<name><![CDATA[' . $this_custom['name'] . ']]></name>
		<description><![CDATA[' . $this_custom['description'] . ']]></description>
		<wrap_content><![CDATA[' . $this_custom['wrap_content'] . ']]></wrap_content>
		<content><![CDATA[' . base64_encode($this_custom['content']) . ']]></content>
		<checksum>' . md5($this_custom['content']) . '</checksum>
	</custom_sidebox>
</adv_sidebox>';
			
			$filename = implode('-', explode(' ', $this_custom['name']));

			header('Content-Disposition: attachment; filename=' . $filename . '.xml');
			header('Content-Type: application/xml');
			header('Content-Length: ' . strlen($xml));
			header('Pragma: no-cache');
			header('Expires: 0');
			echo $xml;
			exit();
		}
	}

	$page->add_breadcrumb_item($lang->adv_sidebox_name, ADV_SIDEBOX_URL);
	$page->add_breadcrumb_item($lang->adv_sidebox_custom_boxes);

	// add a little CSS
	$page->extra_header .= '<style type="text/css">
.asb_label
{
	background: #EBF3FF;
	color: #333;
	font-weight: bold;
	width: 100%
	margin: auto auto;
	padding: 5px;
	border: 1px solid #85B1EE;
	text-align: center;
}

.asb_label a:hover, a:active
{
	color: #333;
}

.asb_label img
{
	margin-bottom: -3px;
}
</style>';
	adv_sidebox_output_header();
	adv_sidebox_output_tabs('adv_sidebox_custom');

	// main page
	if(!$mybb->input['mode'])
	{
		// POSTing?
		if($mybb->request_method == "post")
		{
			// saving?
			if($mybb->input['save_box_submit'] == 'Save')
			{
				// get the info
				$this_box['name'] = $db->escape_string($mybb->input['box_name']);
				$this_box['description'] = $db->escape_string($mybb->input['box_description']);
				$this_box['content'] = $db->escape_string($mybb->input['box_content']);
				
				if($mybb->input['wrap_content'] == 'yes')
				{
					$this_box['wrap_content'] = true;
				}

				// updating or creating a new type?
				if(isset($mybb->input['box']))
				{
					$this_box['id'] = (int)$mybb->input['box'];

					$query = $db->simple_select('custom_sideboxes', 'id', "id='" . $this_box['id'] . "'");

					// its a bad record just store 0 to indicate its a new box
					if($db->num_rows($query) == 0)
					{
						$this_box['id'] = 0;
					}
				}
				else
				{
					$this_box['id'] = 0;
				}

				// update?
				If($this_box['id'])
				{
					// yes
					$status = $db->update_query('custom_sideboxes', $this_box, "id='" . $this_box['id'] . "'");
				}
				else
				{
					// no, create a new type
					$status = $db->insert_query('custom_sideboxes', $this_box);
				}

				// success?
				if($status)
				{
					// :)
					flash_message($lang->adv_sidebox_custom_box_save_success, "success");
					admin_redirect(ADV_SIDEBOX_CUSTOM_URL);
				}
				else
				{
					// :(
					flash_message($lang->adv_sidebox_custom_box_save_failure, "error");
					admin_redirect(ADV_SIDEBOX_CUSTOM_URL);
				}
			}
		}

		$table = new Table;
		$table->construct_header($lang->adv_sidebox_custom_box_name);
		$table->construct_header($lang->adv_sidebox_custom_box_desc);
		$table->construct_header($lang->adv_sidebox_controls, array("colspan" => 2));

		$query = $db->simple_select('custom_sideboxes');

		// if there are saved types . . .
		if($db->num_rows($query))
		{
			// display them
			while($this_custom = $db->fetch_array($query))
			{
				$table->construct_cell('<a href="' . ADV_SIDEBOX_CUSTOM_URL . '&amp;mode=edit_box&amp;box=' . $this_custom['id'] . '" title="Edit">' . $this_custom['name'] . '</a>', array("width" => '30%'));
				$table->construct_cell($this_custom['description'], array("width" => '60%'));
				
				$popup = new PopupMenu('box_' . $this_custom['id'], 'Options');
				$popup->add_item($lang->adv_sidebox_edit, ADV_SIDEBOX_CUSTOM_URL . "&amp;mode=edit_box&amp;box={$this_custom['id']}");
				$popup->add_item($lang->adv_sidebox_delete, ADV_SIDEBOX_CUSTOM_URL . "&amp;mode=delete_box&amp;box={$this_custom['id']}");
				$popup->add_item('Export', ADV_SIDEBOX_EXPORT_URL . "&amp;box={$this_custom['id']}");
				$table->construct_cell($popup->fetch(), array("width" => '10%'));
				$table->construct_row();
			}
		}
		else
		{
			// no saved types
			$table->construct_cell($lang->adv_sidebox_no_custom_boxes, array("colspan" => 4));
			$table->construct_row();
		}
		$table->output($lang->adv_sidebox_custom_box_types);

		// add link bar
		echo('<div class="asb_label"><a href="' . ADV_SIDEBOX_CUSTOM_URL . '&amp;mode=edit_box"><img src="' . $mybb->settings['bburl'] . '/inc/plugins/adv_sidebox/images/add.png" style="margin-bottom: -3px;"/></a>&nbsp<a href="' . ADV_SIDEBOX_CUSTOM_URL . '&amp;mode=edit_box">' . $lang->adv_sidebox_add_custom_box_types . '</a>&nbsp<a href="' . ADV_SIDEBOX_IMPORT_URL . '"><img src="' . $mybb->settings['bburl'] . '/inc/plugins/adv_sidebox/images/import.png" style="margin-bottom: -3px;"/></a>&nbsp<a href="' . ADV_SIDEBOX_IMPORT_URL . '" title="' . $lang->adv_sidebox_custom_import_box . '">' . $lang->adv_sidebox_custom_import_box . '</a></div>');
	}

	if($mybb->input['mode'] == 'edit_box')
	{
		// editing?
		if(isset($mybb->input['box']))
		{
			$query = $db->simple_select('custom_sideboxes', '*', "id='" . (int) $mybb->input['box'] . "'");

			// does it exist?
			if($db->num_rows($query) > 0)
			{
				$this_box = $db->fetch_array($query);

				$specify_box = "&amp;box=" . (int) $mybb->input['box'];
			}
		}
		else
		{
			// new box
			$specify_box = '';
			$this_box['content'] = '
		<tr>
			<td class="trow1">Place your custom content here. HTML can be used in conjunction with certain template variables, language variables and environment variables.</td>
		</tr>
		<tr>
			<td class="trow2">For example:</td>
		</tr>
		<tr>
			<td class="trow1"><strong>User:</strong> {$mybb->user[\'username\']}</td>
		</tr>
		<tr>
			<td class="trow2"><strong>UID:</strong> {$mybb->user[\'uid\']}</td>
		</tr>
		<tr>
			<td class="trow1"><strong>Theme name:</strong> {$theme[\'name\']}</td>
		</tr>';
			$this_box['wrap_content'] = true;
		}

		$form = new Form(ADV_SIDEBOX_CUSTOM_URL . $specify_box, "post", "edit_box");
		$form_container = new FormContainer($lang->adv_sidebox_edit_box);
		$form_container->output_row($lang->adv_sidebox_custom_box_name, $lang->adv_sidebox_add_custom_box_name_desc, $form->generate_text_box('box_name', $this_box['name'], array("id" => 'box_name')));
		$form_container->output_row($lang->adv_sidebox_custom_box_desc, $lang->adv_sidebox_add_custom_box_description_desc, $form->generate_text_box('box_description', $this_box['description'], array("id" => 'box_description')));
		$form_container->output_row($lang->adv_sidebox_custom_box_wrap_content, '', $form->generate_check_box('wrap_content', 'yes', $lang->adv_sidebox_custom_box_wrap_content_desc, array("checked" => $this_box['wrap_content'])));
		$form_container->output_row($lang->adv_sidebox_add_custom_box_edit, $lang->adv_sidebox_add_custom_box_edit_desc, $form->generate_text_area('box_content', $this_box['content'], array("id" => 'box_content', "rows" => '15', "cols" => '200')), array("id" => 'box_content'));
		$form_container->end();

		$buttons[] = $form->generate_submit_button('Save', array('name' => 'save_box_submit'));
		$form->output_submit_wrapper($buttons);
		$form->end();
	}

	if($mybb->input['mode'] == 'delete_box')
	{
		// info good?
		if(isset($mybb->input['box']))
		{
			// nuke it
			$status = $db->query("DELETE FROM " . TABLE_PREFIX . "custom_sideboxes WHERE id='" . (int) $mybb->input['box'] . "'");

			// success?
			if($status)
			{
				// delete all boxes of this type in use
				$db->query("DELETE FROM " . TABLE_PREFIX . "sideboxes WHERE box_type='" . (int) $mybb->input['box'] . "_asb_custom'");

				// :)
				flash_message($lang->adv_sidebox_add_custom_box_delete_success, "success");
				admin_redirect(ADV_SIDEBOX_CUSTOM_URL);
			}
			else
			{
				// :(
				flash_message($lang->adv_sidebox_add_custom_box_delete_failure, "error");
				admin_redirect(ADV_SIDEBOX_CUSTOM_URL);
			}
		}
		else
		{
			// :(
			flash_message($lang->adv_sidebox_add_custom_box_delete_failure, "error");
			admin_redirect(ADV_SIDEBOX_CUSTOM_URL);
		}
	}

	$page->output_footer();
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

	if (!$lang->adv_sidebox)
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

	if (!$lang->adv_sidebox)
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

	if (!$lang->adv_sidebox)
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

	if (!$lang->adv_sidebox)
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
	$sub_tabs['adv_sidebox_add'] = array
	(
		'title' 				=> $lang->adv_sidebox_add_new_box,
		'link' 					=> ADV_SIDEBOX_EDIT_URL . '&amp;mode=' . $mybb->input['mode'],
		'description'		=> $lang->adv_sidebox_add_new_box_desc
	);
	$sub_tabs['adv_sidebox_modules'] = array
	(
		'title'					=> $lang->adv_sidebox_manage_modules,
		'link'					=> ADV_SIDEBOX_MODULES_URL,
		'description'		=> $lang->adv_sidebox_manage_modules_desc
	);
	$sub_tabs['adv_sidebox_custom'] = array
	(
		'title'					=> $lang->adv_sidebox_custom_boxes,
		'link'					=> ADV_SIDEBOX_CUSTOM_URL,
		'description'		=> $lang->adv_sidebox_custom_boxes_desc
	);
	$sub_tabs['adv_sidebox_import'] = array
	(
		'title'					=> $lang->adv_sidebox_custom_import,
		'link'					=> ADV_SIDEBOX_IMPORT_URL,
		'description'		=> $lang->adv_sidebox_custom_import_description
	);
	$page->output_nav_tabs($sub_tabs, $current);
}

/*
 * get_all_custom_box_types()
 *
 * query for and return all user-defined box types
 */
function get_all_custom_box_types()
{
	global $db;

	$all_custom = array();

	$query = $db->simple_select('custom_sideboxes');

	if($db->num_rows($query) > 0)
	{
		while($this_type = $db->fetch_array($query))
		{
			$all_custom[$this_type['id'] . '_asb_custom'] = $this_type['name'];
		}
	}

	return $all_custom;
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

?>

