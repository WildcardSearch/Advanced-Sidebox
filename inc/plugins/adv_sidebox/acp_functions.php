<?php
/*
 * This file contains the ACP functions for adv_sidebox.php
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

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB") || !defined("ADV_SIDEBOX"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

require_once MYBB_ROOT . "inc/plugins/adv_sidebox/adv_sidebox_install.php";

define('ADV_SIDEBOX_URL', 'index.php?module=config-adv_sidebox');
define('ADV_SIDEBOX_EDIT_URL', 'index.php?module=config-adv_sidebox&amp;action=edit_box');
define('ADV_SIDEBOX_DEL_URL', 'index.php?module=config-adv_sidebox&amp;action=delete_box');
define('ADV_SIDEBOX_MANAGE_URL', 'index.php?module=config-adv_sidebox&amp;action=manage_modules');
define('ADV_SIDEBOX_CUSTOM_URL', 'index.php?module=config-adv_sidebox&amp;action=custom_boxes');

$plugins->add_hook('admin_config_action_handler', 'adv_sidebox_admin_action');

function adv_sidebox_admin_action(&$action)
{
	$action['adv_sidebox'] = array('active' => 'adv_sidebox');
}

$plugins->add_hook('admin_config_menu', 'adv_sidebox_admin_menu');

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

function adv_sidebox_admin_permissions(&$admin_permissions)
{
	global $lang;
	
	if (!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}
	
	$admin_permissions['adv_sidebox'] = $lang->adv_sidebox_admin_permissions_desc;
}

$plugins->add_hook('admin_load', 'adv_sidebox_admin');

// main ACP page
function adv_sidebox_admin()
{
	global $mybb, $db, $page, $lang, $plugins;

	if($page->active_action != 'adv_sidebox')
	{
		return false;
	}
	
	if (!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}
	
	// no action means the main page
	if(!$mybb->input['action'])
	{
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
		$all_modules = get_all_module_info($count_instmods, $count_uninstmods, $count_simpmods);
		if(!empty($all_modules))
		{
			$box_types = array_merge($box_types, $all_modules);
		}

		// Sideboxes table
		$table = new Table;
		$table->construct_header($lang->adv_sidebox_id);
		$table->construct_header($lang->adv_sidebox_display_order);
		$table->construct_header($lang->adv_sidebox_box_type);
		$table->construct_header($lang->adv_sidebox_position);
		$table->construct_header($lang->adv_sidebox_controls, array("colspan" => 2));

		$query = $db->simple_select('sideboxes', '*', '', array("order_by" => 'position, display_order', "order_dir" => 'ASC'));

		// if there are sideboxes . . .
		if($db->num_rows($query) > 0)
		{
			$left_box = false;
			$right_box = false;

			$table->construct_cell("<div class=\"asb_label\"><strong>{$lang->adv_sidebox_position_left_boxes}</strong></div>", array("colspan" => 7));
			$table->construct_row();

			while($box = $db->fetch_array($query))
			{
				// if this is the first right box . . .
				if((int) $box['position'] && !$right_box)
				{
					// . . . and there weren't any left boxes . . .
					if(!$left_box)
					{
						// let them know
						$table->construct_cell('<span style="color: #888"><p>' . $lang->adv_sidebox_no_boxes_left . '</p></span>', array("colspan" => 7));
						$table->construct_row();
					}

					// and add the label
					$right_box = true;
					$table->construct_cell("<div class=\"asb_label\"><strong>{$lang->adv_sidebox_position_right_boxes}</strong></div>", array("colspan" => 7));
					$table->construct_row();
				}
				elseif((int) $box['position'] == 0 && !$left_box)
				{
					// otherwise its a left box
					$left_box = true;
				}

				// construct the table row.
				$table->construct_cell($box['id'], array("width" => '5%'));
				$table->construct_cell($box['display_order'], array("width" => '5%'));
				$table->construct_cell($box_types[$box['box_type']], array("width" => '10%'));
				$table->construct_cell(((int) $box['position'] ? $lang->adv_sidebox_position_right : $lang->adv_sidebox_position_left), array("width" => '5%'));
				$table->construct_cell('<a href="' . ADV_SIDEBOX_EDIT_URL . '&amp;box=' . $box['id'] . '"><img src="' . $mybb->settings['bburl'] . '/images/icons/pencil.gif" alt="' . $lang->adv_sidebox_edit . '" title="' . $lang->adv_sidebox_edit . '" />&nbsp;' . $lang->adv_sidebox_edit . '</a>', array("width" => '10%'));
				$table->construct_cell('<a href="' . ADV_SIDEBOX_DEL_URL . '&amp;box=' . $box['id'] . '"><img src="' . $mybb->settings['bburl'] . '/images/usercp/delete.png" alt="' . $lang->adv_sidebox_edit . '" title="' . $lang->adv_sidebox_edit . '" />&nbsp;' . $lang->adv_sidebox_delete . '</a>', array("width" => '10%'));
				$table->construct_row();
			}

			// if there were no right boxes . . .
			if(!$right_box)
			{
				// add the label anyway
				$table->construct_cell("<div class=\"asb_label\"><strong>{$lang->adv_sidebox_position_right_boxes}</strong></div>", array("colspan" => 6));
				$table->construct_row();

				// and tell them what they already know
				$table->construct_cell('<span style="color: #888"><p>' . $lang->adv_sidebox_no_boxes_right . '</p></span>', array("colspan" => 7));
				$table->construct_row();
			}
		}
		else
		{
			// no boxes? state the obvious
			$table->construct_cell('<span style="color: #888"><p>' . $lang->adv_sidebox_no_boxes . '</p></span>', array("colspan" => 7));
			$table->construct_row();
		}

		// output the box table
		$table->output('Sideboxes');

		// get a total of all modules
		$count_allmods =  $count_uninstmods + $count_instmods + $count_simpmods;

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
			if($count_uninstmods)
			{
				// more than one?
				if($count_uninstmods > 1)
				{
					// plural language
					$module_info .= $lang->sprintf($lang->adv_sidebox_module_awaiting_install, $count_uninstmods, $lang->adv_sidebox_are);
				}
				else
				{
					// singular
					$module_info .= $lang->sprintf($lang->adv_sidebox_module_awaiting_install, $count_uninstmods, $lang->adv_sidebox_is);
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
		
		// build link bar
		$module_info .= " - <a href=\"" . ADV_SIDEBOX_MANAGE_URL . "\">{$lang->adv_sidebox_manage_modules}</a>";
		$settings_link = adv_sidebox_build_settings_link();
		echo('<div class="asb_label"><a href="' . ADV_SIDEBOX_EDIT_URL . '"><img src="' . $mybb->settings['bburl'] . '/images/add.png" /></a>&nbsp;<a href="' . ADV_SIDEBOX_EDIT_URL . '">' . $lang->adv_sidebox_add_new_box . '</a> - ' . $module_info . ' - ' . $settings_link . '</div>');

		$page->output_footer();
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
	
	// delete a sidebox
	if($mybb->input['action'] == 'delete_box')
	{
		// info given?
		if(isset($mybb->input['box']) && (int) $mybb->input['box'] > 0)
		{
			// nuke it
			$status = $db->query("DELETE FROM " . TABLE_PREFIX . "sideboxes WHERE id='" . (int) $mybb->input['box'] . "'");
		}
		else
		{
			// no info, give error
			flash_message($lang->adv_sidebox_delete_box_failure, "error");
			admin_redirect(ADV_SIDEBOX_URL);
		}

		// success?
		if($status)
		{
			flash_message($lang->adv_sidebox_delete_box_success, "success");
			admin_redirect(ADV_SIDEBOX_URL);
		}
		else
		{
			// fail
			flash_message($lang->adv_sidebox_delete_box_failure, "error");
			admin_redirect(ADV_SIDEBOX_URL);
		}
	}
	
	// delete module
	if($mybb->input['action'] == 'install_addon')
	{
		// info given?
		if(isset($mybb->input['addon']))
		{
			$this_module = $mybb->input['addon'];
			
			// module file exist?
			if(file_exists(ADV_SIDEBOX_MODULES_DIR . "/" . $this_module . "/adv_sidebox_module.php"))
			{
				// require it
				require_once ADV_SIDEBOX_MODULES_DIR . "/" . $this_module . "/adv_sidebox_module.php";

				// install function exist?
				if(function_exists($this_module . '_install'))
				{
					// run it
					$install_function = $this_module . '_install';
					$install_function();

					// tell them all is well
					flash_message($lang->adv_sidebox_install_addon_success, "success");
					admin_redirect(ADV_SIDEBOX_MANAGE_URL);
				}
			}
		}
		else
		{
			// module no good
			flash_message($lang->adv_sidebox_install_addon_failure, "error");
			admin_redirect(ADV_SIDEBOX_MANAGE_URL);
		}
	}
	
	// uninstall module
	if($mybb->input['action'] == 'uninstall_addon')
	{
		// info given?
		if(isset($mybb->input['addon']))
		{
			$this_module = $mybb->input['addon'];
			
			// file exists?
			if(file_exists(ADV_SIDEBOX_MODULES_DIR . "/" . $this_module . "/adv_sidebox_module.php"))
			{
				require_once ADV_SIDEBOX_MODULES_DIR . "/" . $this_module . "/adv_sidebox_module.php";

				// function present?
				if(function_exists($this_module . '_uninstall'))
				{
					// uninstall
					$uninstall_function = $this_module . '_uninstall';
					$uninstall_function();

					// yay
					flash_message($lang->adv_sidebox_uninstall_addon_success, "success");
					admin_redirect(ADV_SIDEBOX_MANAGE_URL);
				}
			}
		}
		else
		{
			// :(
			flash_message($lang->adv_sidebox_uninstall_addon_failure, "error");
			admin_redirect(ADV_SIDEBOX_MANAGE_URL);
		}
	}
	
	// delete module
	if($mybb->input['action'] == 'delete_addon')
	{
		// info goof?
		if(isset($mybb->input['addon']))
		{
			$this_module = $mybb->input['addon'];
			
			// module exists?
			if(file_exists(ADV_SIDEBOX_MODULES_DIR . "/" . $this_module . "/adv_sidebox_module.php"))
			{
				require_once ADV_SIDEBOX_MODULES_DIR . "/" . $this_module . "/adv_sidebox_module.php";

				// function intact?
				if(function_exists($this_module . '_uninstall'))
				{
					// uninstall
					$uninstall_function = $this_module . '_uninstall';
					$uninstall_function();
				}
			}
			
			// nuke it
			rrmdir(ADV_SIDEBOX_MODULES_DIR . "/" . $this_module);

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

	exit();
}

// Handle user-defined box types
function adv_sidebox_admin_custom_boxes()
{
	global $lang, $mybb, $db, $plugins, $page;

	if (!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
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
				
		$query = $db->simple_select('custom_sideboxes');
		
		$table = new Table;
		$table->construct_header($lang->adv_sidebox_custom_box_name);
		$table->construct_header($lang->adv_sidebox_custom_box_desc);
		$table->construct_header($lang->adv_sidebox_controls, array("colspan" => 2));
	
		// if there are saved types . . .
		if($db->num_rows($query))
		{	
			// display them
			while($this_custom = $db->fetch_array($query))
			{
				$table->construct_cell($this_custom['name']);
				$table->construct_cell($this_custom['description']);
				$table->construct_cell("<a href=\"" . ADV_SIDEBOX_CUSTOM_URL . "&amp;mode=edit_box&amp;box={$this_custom['id']}\"><img src=\"{$mybb->settings['bburl']}/images/icons/pencil.gif\" alt=\"{$lang->adv_sidebox_edit}\" title=\"{$lang->adv_sidebox_edit}\" />&nbsp;{$lang->adv_sidebox_edit}</a>");
				$table->construct_cell("<a href=\"" . ADV_SIDEBOX_CUSTOM_URL . "&amp;mode=delete_box&amp;box={$this_custom['id']}\"><img src=\"{$mybb->settings['bburl']}/images/usercp/delete.png\" alt=\"{$lang->adv_sidebox_edit}\" title=\"{$lang->adv_sidebox_edit}\" />&nbsp;{$lang->adv_sidebox_delete}</a>");
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
		echo('<div class="asb_label"><a href="' . ADV_SIDEBOX_CUSTOM_URL . '&amp;mode=edit_box"><img src="' . $mybb->settings['bburl'] . '/images/add.png" style="margin-bottom: -3px;"/></a>&nbsp;<a href="' . ADV_SIDEBOX_CUSTOM_URL . '&amp;mode=edit_box">' . $lang->adv_sidebox_add_custom_box_types . '</a></div>');
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
		}
		
		$form = new Form(ADV_SIDEBOX_CUSTOM_URL . $specify_box, "post", "edit_box");
		$form_container = new FormContainer($lang->adv_sidebox_edit_box);
		$form_container->output_row($lang->adv_sidebox_custom_box_name, $lang->adv_sidebox_add_custom_box_name_desc, $form->generate_text_box('box_name', $this_box['name'], array("id" => 'box_name')));
		$form_container->output_row($lang->adv_sidebox_custom_box_desc, $lang->adv_sidebox_add_custom_box_description_desc, $form->generate_text_box('box_description', $this_box['description'], array("id" => 'box_description')));
		$form_container->output_row($lang->adv_sidebox_add_custom_box_edit, $lang->adv_sidebox_add_custom_box_edit_desc, $form->generate_text_area('box_content', $this_box['content'], array("id" => 'box_content')), array("id" => 'box_content'));
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

	// get all module types
	$box_info = get_all_modules_full($box_types);
	
	foreach($box_info as $this_module)
	{
		$key = $this_module['mod_name'];
		
		switch((int) $this_module['mod_type'])
		{
			case 2:
				$installed_modules[] = $key;
				break;
			case 1:
				$uninstalled_modules[] = $key;
				break;
			case 0:
				$simple_modules[] = $key;
				break;
		}
	}
	
	$table = new Table;
	$table->construct_header($lang->adv_sidebox_custom_box_name);
	$table->construct_header($lang->adv_sidebox_custom_box_desc);
	$table->construct_header($lang->adv_sidebox_box_type);
	$table->construct_header($lang->adv_sidebox_controls, array("colspan" => 2));

	// if there are simple modules display them
	if(!empty($simple_modules))
	{
		$table->construct_cell("<div class=\"asb_label\">{$lang->adv_sidebox_simple_modules}</div>", array("colspan" => 5));
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
		
			$table->construct_cell($box_types[$this_module]);
			$table->construct_cell($box_info[$this_module]['description']);
			$table->construct_cell($type);
			$table->construct_cell('');
			$table->construct_cell('<a href="' . ADV_SIDEBOX_URL . '&amp;action=delete_addon&amp;addon=' . $this_module . '" onclick="return confirm(\'' . $lang->adv_sidebox_modules_del_warning . '\');"><img src="' . $mybb->settings['bburl'] . '/images/invalid.gif" />&nbsp;' . $lang->adv_sidebox_delete . '</a>');
			$table->construct_row();
		}
	}

	// if there are installed modules display them
	if(!empty($installed_modules))
	{
		$table->construct_cell("<div class=\"asb_label\">{$lang->adv_sidebox_installed_modules}</div>", array("colspan" => 5));
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
		
			$table->construct_cell($box_types[$this_module]);
			$table->construct_cell($box_info[$this_module]['description']);
			$table->construct_cell($type);
			$table->construct_cell('<a href="' . ADV_SIDEBOX_URL . '&amp;action=uninstall_addon&amp;addon=' . $this_module . '"><img src="' . $mybb->settings['bburl'] . '/images/new.png" />&nbsp;' . $lang->adv_sidebox_uninstall . '</a>');
			$table->construct_cell('<a href="' . ADV_SIDEBOX_URL . '&amp;action=delete_addon&amp;addon=' . $this_module . '" onclick="return confirm(\'' . $lang->adv_sidebox_modules_del_warning . '\');"><img src="' . $mybb->settings['bburl'] . '/images/invalid.gif" />&nbsp;' . $lang->adv_sidebox_delete . '</a>');
			$table->construct_row();
		}
	}

	// If there are uninstalled modules display them
	if(!empty($uninstalled_modules))
	{
		$table->construct_cell("<div class=\"asb_label\">{$lang->adv_sidebox_uninstalled_modules}</div>", array("colspan" => 5));
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
		
			$table->construct_cell($box_types[$this_module]);
			$table->construct_cell($box_info[$this_module]['description']);
			$table->construct_cell($type);
			$table->construct_cell('<a href="' . ADV_SIDEBOX_URL . '&amp;action=install_addon&amp;addon=' . $this_module . '"><img src="' . $mybb->settings['bburl'] . '/images/new.png" />&nbsp;' . $lang->adv_sidebox_install . '</a>');
			$table->construct_cell('<a href="' . ADV_SIDEBOX_URL . '&amp;action=delete_addon&amp;addon=' . $this_module . '" onclick="return confirm(\'' . $lang->adv_sidebox_modules_del_warning . '\');"><img src="' . $mybb->settings['bburl'] . '/images/invalid.gif" />&nbsp;' . $lang->adv_sidebox_delete . '</a>');
			$table->construct_row();
		}
	}
	
	// if there are no modules detected, tell them so
	if(empty($installed_modules) && empty($uninstalled_modules) && empty($simple_modules))
	{
		$table->construct_cell($lang->adv_sidebox_no_modules_detected, array("colspan" => 3));
		$table->construct_row();
	}
	$table->output();
	$page->output_footer();
}

function adv_sidebox_admin_editbox()
{
	global $lang, $mybb, $db, $plugins, $page;
	
	if (!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}

	// POSTing?
	if($mybb->request_method == "post")
	{
		// saving?
		if($mybb->input['save_box_submit'] == 'Save')
		{
			// help them keep their display orders spaced
			if(!isset($mybb->input['display_order']) || (int) $mybb->input['display_order'] == 0)
			{
				$query = $db->simple_select('sideboxes', 'display_order');

				$disp_order = ((int) $db->num_rows($query) + 1) * 10;
			}
			else
			{
				// or back off if they entered a value
				$disp_order = (int) $mybb->input['display_order'];
			}

			// translate the position
			if($mybb->input['box_position'] == 'right')
			{
				$pos = 1;
			}

			// if this isn't a custom box . . .
			if($mybb->input['box_type_select'] != 'custom_box')
			{
				// don't store the content at all
				$content = '';
			}
			else
			{
				// otherwise store it
				$content = $mybb->input['box_content'];
			}

			// db array
			$this_box = array(
				"display_order"	=> (int) $disp_order,
				"box_type"	=>	$db->escape_string($mybb->input['box_type_select']),
				"position"		=>	(int) $pos,
				"content"		=>	$db->escape_string($content)
			);

			// does this box already exist?
			if(isset($mybb->input['box']) && (int) $mybb->input['box'] > 0)
			{
				// if so update it
				$status = $db->update_query('sideboxes', $this_box, "id='" . (int) $mybb->input['box'] . "'");
			}
			else
			{
				// if not, create it
				$status = $db->insert_query('sideboxes', $this_box);
			}

			// success?
			if($status)
			{
				// yay
				flash_message($lang->adv_sidebox_save_success, "success");
				admin_redirect(ADV_SIDEBOX_URL);
			}
			else
			{
				// :(
				flash_message($lang->adv_sidebox_save_fail, "error");
				admin_redirect(ADV_SIDEBOX_EDIT_URL);
			}
		}
	}

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
	$box_info = get_all_modules_full($box_types);

	$page->add_breadcrumb_item($lang->adv_sidebox_name, ADV_SIDEBOX_URL);
	$page->add_breadcrumb_item($lang->adv_sidebox_add_a_sidebox);

	// add the script to hide the content box if it is unnecessary
	$page->extra_header .= '<script type="text/javascript" src="./jscripts/peeker.js"></script>
	<script type="text/javascript">Event.observe(window, "load", function() {var peeker = new Peeker($("box_type_select"), $("box_content"), /custom_box/, false);});
	</script>';

	// output ACP page stuff
	adv_sidebox_output_header();
	adv_sidebox_output_tabs('adv_sidebox_add');
	
	// editing?
	if(isset($mybb->input['box']))
	{
		// load the box from the db
		$box_id = (int) $mybb->input['box'];
		$query = $db->simple_select('sideboxes', 'id, display_order, box_type, position, content', "id='{$box_id}'", array("order_by" => 'display_order', "order_dir" => 'ASC'));

		// if it exists, store it for display
		if($db->num_rows($query) == 1)
		{
			$this_box = $db->fetch_array($query);
			$disp_order = (int) $this_box['display_order'];
		}
	}
	else
	{
		// if creating a new box give them some kind of display order that makes sense
		$query = $db->simple_select('sideboxes', 'display_order');
		$disp_order = ((int) $db->num_rows($query) + 1) * 10;

		// and some sample custom content
		$this_box['content'] = '<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
	<tr>
		<td class="thead"><strong>Custom Box</strong></td>
	</tr>
	<tr>
		<td class="trow1">Place your custom content here. HTML can be used in conjunction with certain template variables, language variables and environment variables.<br /><br />
		For example:<br /><br />
		<strong>User:</strong> {$mybb->user[\'username\']}<br />
		<strong>UID:</strong> {$mybb->user[\'uid\']}<br />
		<strong>Theme name:</strong> {$theme[\'name\']}</td></tr></table><br />';
	}

	$form = new Form(ADV_SIDEBOX_EDIT_URL . "&amp;box=" . $this_box['id'], "post", "edit_box");
	$form_container = new FormContainer($lang->adv_sidebox_edit_box);

	$form_container->output_row($lang->adv_sidebox_box_type, $lang->adv_sidebox_type_desc, $form->generate_select_box('box_type_select', $box_types, $this_box['box_type'], array("id" => 'box_type_select')), array("id" => 'box_type_select_box'));
	$form_container->output_row($lang->adv_sidebox_content_title, $lang->adv_sidebox_content_desc, $form->generate_text_area('box_content', $this_box['content'], array("id" => 'box_content')), array("id" => 'box_content'));
	$form_container->output_row($lang->adv_sidebox_position, $lang->adv_sidebox_position_desc, $form->generate_radio_button('box_position', 'left', $lang->adv_sidebox_position_left, array("checked" => ((int) $this_box['position'] == 0))) . '&nbsp;&nbsp;' . $form->generate_radio_button('box_position', 'right', $lang->adv_sidebox_position_right, array("checked" => ((int) $this_box['position'] != 0))));
	$form_container->output_row($lang->adv_sidebox_display_order, '', $form->generate_text_box('display_order', $disp_order));
	$form_container->end();

	$buttons[] = $form->generate_submit_button('Save', array('name' => 'save_box_submit'));
	$form->output_submit_wrapper($buttons);
	$form->end();

	$page->output_footer();
}

$plugins->add_hook("admin_config_settings_change", "adv_sidebox_serialize");

// Serialize the theme exclusion list selector
function adv_sidebox_serialize()
{
    global $mybb;

    $mybb->input['upsetting']['adv_sidebox_exclude_theme'] = serialize($mybb->input['upsetting']['adv_sidebox_exclude_theme']);
}

function adv_sidebox_output_header()
{
    global $page, $lang;

	if (!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}

    $page->output_header($lang->adv_sidebox_name);
}

function adv_sidebox_output_tabs($current)
{
	global $page, $lang;
	
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
		'link' 					=> ADV_SIDEBOX_EDIT_URL,
		'description'		=> $lang->adv_sidebox_add_new_box_desc
	);
	$sub_tabs['adv_sidebox_modules'] = array
	(
		'title'					=> $lang->adv_sidebox_manage_modules,
		'link'					=> ADV_SIDEBOX_MANAGE_URL,
		'description'		=> $lang->adv_sidebox_manage_modules_desc
	);
	$sub_tabs['adv_sidebox_custom'] = array
	(
		'title'					=> $lang->adv_sidebox_custom_boxes,
		'link'					=> ADV_SIDEBOX_CUSTOM_URL,
		'description'		=> $lang->adv_sidebox_custom_boxes_desc
	);
	
	$page->output_nav_tabs($sub_tabs, $current);
}

function rrmdir($dir)
{
	if (is_dir($dir))
	{
		$objects = scandir($dir);

		foreach ($objects as $object)
		{
			if ($object != "." && $object != "..")
			{
				if (filetype($dir."/".$object) == "dir")
				{
					rrmdir($dir."/".$object);
				}
				else
				{
					unlink($dir."/".$object);
				}
			}
		}

		reset($objects);
		rmdir($dir);
	}
}

// query for and return all user-defined box types
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

function get_all_module_info(&$installed, &$uninstalled, &$simple)
{
	//modules
	$dir = opendir(ADV_SIDEBOX_MODULES_DIR);
	
	$all_types = array();
	
	while(($module = readdir($dir)) !== false)
	{
		if(is_dir(ADV_SIDEBOX_MODULES_DIR . "/" . $module) && !in_array($module, array(".", "..")) && file_exists(ADV_SIDEBOX_MODULES_DIR . "/" . $module . "/adv_sidebox_module.php"))
		{
			require_once ADV_SIDEBOX_MODULES_DIR . "/" . $module . "/adv_sidebox_module.php";

			$is_installed_function = $module . '_is_installed';

			if(function_exists($module . '_is_installed'))
			{
				if(!$is_installed_function())
				{
					++$uninstalled;
				}
				else
				{
					if(function_exists($module . '_info'))
					{
						$info_function = $module . '_info';
						$this_info = $info_function();
						
						$all_types[$module] = $this_info['name'];

						++$installed;
					}
				}
			}
			else
			{
				// there is no install, add the box_type now
				if(function_exists($module . '_info'))
				{
					$info_function = $module . '_info';
					$this_info = $info_function();
					
					$all_types[$module] = $this_info['name'];

					++$simple;
				}
			}
		}
	}
	
	return $all_types;
}

// detect and get info on all modules
//
// returns box_info and modifies $box_types
function get_all_modules_full(&$box_types)
{
	//modules
	$dir = opendir(ADV_SIDEBOX_MODULES_DIR);
	
	// loop through the modules directory
	while(($module = readdir($dir)) !== false)
	{
		// check if the directory exists, if the module file is present and make sure we aren't dealing with 'up' directories
		if(is_dir(ADV_SIDEBOX_MODULES_DIR."/".$module) && !in_array($module, array(".", "..")) && file_exists(ADV_SIDEBOX_MODULES_DIR."/".$module."/adv_sidebox_module.php"))
		{
			require_once ADV_SIDEBOX_MODULES_DIR . "/" . $module . "/adv_sidebox_module.php";

			$is_installed_function = $module . '_is_installed';

			// is_installed function present?
			if(function_exists($module . '_is_installed'))
			{
				if(!$is_installed_function())
				{
					// uninstalled
					if(function_exists($module . '_info'))
					{
						$info_function = $module . '_info';
						$this_info = $info_function();
						
						$box_types[$module] = $this_info['name'];
						
						$box_info[$module]['name'] = $this_info['name'];
						$box_info[$module]['description'] = $this_info['description'];
						$box_info[$module]['mod_type'] = 1; // 1 for uninstalled complex module
						$box_info[$module]['mod_name'] = $module;
						
						$box_info[$module]['stereo'] = $this_info['stereo'];
					}
				}
				else
				{
					if(function_exists($module . '_info'))
					{
						$info_function = $module . '_info';
						$this_info = $info_function();
						
						$box_types[$module] = $this_info['name'];
						$box_info[$module]['name'] = $this_info['name'];
						$box_info[$module]['description'] = $this_info['description'];
						$box_info[$module]['mod_type'] = 2; // 2 for installed complex
						$box_info[$module]['mod_name'] = $module;
						
						$box_info[$module]['stereo'] = $this_info['stereo'];
					}
				}
			}
			else
			{
				if(function_exists($module . '_info'))
				{
					$info_function = $module . '_info';
					$this_info = $info_function();
					
					$box_types[$module] = $this_info['name'];
					$box_info[$module]['name'] = $this_info['name'];
					$box_info[$module]['description'] = $this_info['description'];
					$box_info[$module]['mod_type'] = 0; // 0 for simple
					$box_info[$module]['mod_name'] = $module;
					
					$box_info[$module]['stereo'] = $this_info['stereo'];
				}
			}
		}
	}
	return $box_info;
}

?>
