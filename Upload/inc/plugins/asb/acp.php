<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * https://www.rantcentralforums.com
 *
 * this file contains the ACP functionality and depends upon install.php
 * for plugin info and installation routines
 */

// disallow direct access to this file for security reasons
if (!defined('IN_MYBB') ||
	!defined('IN_ASB')) {
	die('Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.');
}

define('ASB_URL', 'index.php?module=config-asb');
require_once MYBB_ROOT.'inc/plugins/asb/functions_acp.php';
require_once MYBB_ROOT.'inc/plugins/asb/install.php';

/**
 * the ACP page router
 *
 * @return void
 */
$plugins->add_hook('admin_load', 'asb_admin');
function asb_admin()
{
	// globalize as needed to save wasted work
	global $page;

	if ($page->active_action != 'asb') {
		// not our turn
		return false;
	}

	// now load up, this is our time
	global $mybb, $lang, $html, $scripts, $allScripts, $min;

	if (!$lang->asb) {
		$lang->load('asb');
	}

	if ($mybb->settings['asb_minify_js']) {
		$min = '.min';
	}

	// URL, link and image markup generator
	$html = new HTMLGenerator010000(ASB_URL, array('addon', 'pos', 'topic', 'ajax'));

	$scripts = asbGetAllScripts(true);
	if (is_array($scripts[0]) &&
		!empty($scripts[0])) {
		foreach ($scripts[0] as $filename => $script) {
			$allScripts[$filename] = $script['title'];
		}
	} else {
		$scripts[0] = $allScripts = array();
	}

	// if there is an existing function for the action
	$pageFunction = 'asb_admin_'.$mybb->input['action'];
	if (function_exists($pageFunction)) {
		// run it
		$pageFunction();
	} else {
		// default to the main page
		asb_admin_manage_sideboxes();
	}

	exit();
}

/**
 * main side box management page - drag and drop and standard controls for side boxes
 *
 * @return void
 */
function asb_admin_manage_sideboxes()
{
	global $mybb, $db, $page, $lang, $html, $scripts, $allScripts, $min, $cp_style;

	$addons = asbGetAllModules();

	// if there are add-on modules
	if (is_array($addons)) {
		// display them
		foreach ($addons as $module) {
			if (!$module->isValid()) {
				continue;
			}

			$id = $type = $module->get('baseName');
			$title = $module->get('title');
			$titleUrl = $html->url(array('action' => 'edit_box', 'addon' => $type));
			$titleLink = $html->link($titleUrl, $title, array('class' => 'add_box_link'));

			// add the HTML
			$modules .= <<<EOF

						<div id="asb-{$id}" class="draggable box_type" title="{$module->get('description')}">
							{$titleLink}
						</div>

EOF;
		}
	}

	$custom = asbGetAllCustomBoxes();

	// if there are custom boxes
	if (is_array($custom)) {
		// display them
		foreach ($custom as $module) {
			$id = $type = $module->get('baseName');
			$title = $module->get('title');
			$titleUrl = $html->url(array('action' => 'edit_box', 'addon' => $type));
			$titleLink = $html->link($titleUrl, $title, array('class' => 'add_box_link', 'title' => $lang->asb_add_new_sidebox));

			// add the HTML
			$customBoxes .= <<<EOF

						<div id="{$id}" class="draggable custom_type" title="{$module->get('description')}">
							{$titleLink}
						</div>

EOF;
		}
	}

	$sideboxes = asbGetAllSideBoxes($mybb->input['page']);

	// if there are side boxes
	if (is_array($sideboxes)) {
		// display them
		foreach ($sideboxes as $sidebox) {
			// build the side box
			$box = asbBuildSideBoxInfo($sidebox);

			// and sort it by position
			if ($sidebox->get('position')) {
				$rightBoxes .= $box;
			} else {
				$leftBoxes .= $box;
			}
		}
	}

	$page->add_breadcrumb_item($lang->asb_manage_sideboxes);

	// set up the page header
	$page->extra_header .= <<<EOF
	<script type="text/javascript">
	<!--
	lang.deleting_sidebox = "{$lang->asb_ajax_deleting_sidebox}";
	// -->
	</script>
	<link rel="stylesheet" type="text/css" href="styles/{$cp_style}/asb/global.css" media="screen" />
	<script type="text/javascript" src="jscripts/peeker.js"></script>
	<script src="jscripts/asb/asb_sideboxes{$min}.js" type="text/javascript"></script>

EOF;

	$page->output_header("{$lang->asb} - {$lang->asb_manage_sideboxes}");
	asbOutputTabs('asb');

	$filterText = '';
	if ($mybb->input['page']) {
		$filterText = $lang->sprintf($lang->asb_filter_label, $allScripts[$mybb->input['page']]);
	}

	// build the display
	$markup = <<<EOF

	<div class="container">{$filterText}
		<table width="100%" class="content">
			<thead>
				<tr>
					<th width="18%" class="column_head">{$lang->asb_addon_modules}</th>
					<th width="18%" class="column_head">{$lang->asb_custom}</th>
					<th width="30%" class="column_head">{$lang->asb_position_left}</th>
					<th width="30%" class="column_head">{$lang->asb_position_right}</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td id="addon_menu" valign="top" rowspan="2">{$modules}</td>
					<td id="custom_menu" valign="top" rowspan="2">{$customBoxes}</td>
					<td id="left_column" valign="top" class="column forum_column sortable droppable">{$leftBoxes}</td>
					<td id="right_column" valign="top" class="column forum_column sortable droppable">{$rightBoxes}</td>
				</tr>
				<tr id="bottomRight" height="45px;">
					<td id="trash_column" class="column trashcan sortable" colspan="2" style="background: #f4f4f4 url(styles/{$cp_style}/images/asb/trashcan_bg.png) no-repeat center;"></td>
				</tr>
			</tbody>
		</table>
	</div>

EOF;
	// and display it
	echo($markup);

	// output the link menu and MyBB footer
	asbOutputFooter('manage_sideboxes');
}

/**
 * handles the modal/JavaScript edit box and also (as a backup) displays a standard form for those with JavaScript disabled
 *
 * @return void
 */
function asb_admin_edit_box()
{
	global $page, $lang, $mybb, $db, $html, $scripts, $allScripts, $min, $cp_style;

	$ajax = ($mybb->input['ajax'] == 1);

	$sidebox = new SideboxObject($mybb->input['id']);
	$id = (int) $sidebox->get('id');

	$position = (int) $mybb->input['box_position'];
	if ($ajax) {
		$position = (int) $mybb->input['pos'];

		if ($id) {
			$position = (int) $sidebox->get('position');
		}
	}

	$isCustom = $isModule = false;
	$customTitle = 0;

	$module = $mybb->input['addon'];
	$parent = new SideboxModule($module);
	if (!$parent->isValid()) {
		// did this box come from a custom static box?
		$namePieces = explode('_', $module);
		$customId = $namePieces[count($namePieces) - 1];

		$parent = new CustomSidebox($customId);

		if ($parent->isValid()) {
			$isCustom = true;
		} else {
			if (!$ajax) {
				flash_message($lang->asb_edit_fail_bad_module, 'error');
				admin_redirect($html->url());
			}

			die('<error>asb</error>');
		}
	} else {
		$isModule = true;
	}

	// saving?
	if ($mybb->request_method == 'post') {
		$sidebox->set('position', $position);

		// display order
		if (!isset($mybb->input['display_order']) ||
			(int) $mybb->input['display_order'] == 0) {
			// get a total number of side boxes on the same side and put it at the bottom
			$query = $db->simple_select('asb_sideboxes', 'display_order', "position='{$position}'");
			$displayOrder = (int) (($db->num_rows($query) + 1) * 10);
		} else {
			/*
			 * or back off if they entered a value
			 * (standard, non-modal interface for
			 * when JS fails or isn't allowed)
			 */
			$displayOrder = (int) $mybb->input['display_order'];
		}

		$sidebox->set('display_order', $displayOrder);

		// scripts
		$scriptArray = $mybb->input['script_select_box'];
		if ($scriptArray[0] == 'all_scripts' ||
			(count($scriptArray) >= count($allScripts))) {
			$scriptArray = array();
		}

		$sidebox->set('scripts', $scriptArray);

		// groups
		$groupArray = $mybb->input['group_select_box'];
		if ($groupArray[0] == 'all') {
			$groupArray = array();
		}

		$sidebox->set('groups', $groupArray);

		// themes
		$themeArray = $mybb->input['theme_select_box'];
		if ($themeArray[0] == 'all_themes') {
			$themeArray = array();
		}

		$sidebox->set('themes', $themeArray);

		// box type
		$sidebox->set('box_type', $module);

		$sidebox->set('wrap_content', true);
		if ($isModule) {
			$sidebox->set('wrap_content', $parent->get('wrap_content'));
			$addonSettings = $parent->get('settings');

			// if the parent module has settings...
			if (is_array($addonSettings) &&
				!empty($addonSettings)) {
				// loop through them
				$settings = array();
				foreach ($addonSettings as $setting) {
					// and if the setting has a value
					if (isset($mybb->input[$setting['name']])) {
						// store it
						$settings[$setting['name']] = $mybb->input[$setting['name']];
					}
				}

				$settings = $parent->doSettingsSave($settings);
				$sidebox->set('settings', $settings);
			}
		} elseif ($isCustom) {
			// use its wrap_content property
			$sidebox->set('wrap_content', $parent->get('wrap_content'));
		}

		// title
		if ($mybb->input['box_title']) {
			// if the text input isn't empty use it
			$sidebox->set('title', $mybb->input['box_title']);
		} else {
			// otherwise, check the hidden field (original title)
			if ($mybb->input['current_title']) {
				// if it exists, use it
				$sidebox->set('title', $mybb->input['current_title']);
			} else {
				// otherwise use the default title
				$sidebox->set('title', $parent->get('title'));
			}
		}

		$sidebox->set('title_link', trim($mybb->input['title_link']));

		// save the side box
		$newId = $sidebox->save();
		asbCacheHasChanged();

		// No AJAX?
		if (!$ajax) {
			// if in the standard form handle it with a redirect
			flash_message($lang->asb_save_success, 'success');
			admin_redirect('index.php?module=config-asb');
		}

		// AJAX

		$columnKey = 'left_column';
		if ($position) {
			$columnKey = 'right_column';
		}

		// creating a new box?
		$buildScript = '';
		if ($id == 0) {
			// grab the insert id
			$id = $newId;

			// then escape the title
			$boxTitle = addcslashes($sidebox->get('title'), "'");

			/*
			 * create the new <div> representation of the side box
			 * (title only it will be filled in later by the updater)
			 */
			$buildScript = "ASB.sidebox.createDiv({$id}, '{$boxTitle}', '{$columnKey}'); ";
		}

		// update the side box after we're done via AJAX
		$script = <<<EOF
<script type="text/javascript">
{$buildScript}ASB.sidebox.updateDiv({$id});
</script>
EOF;

		// the modal box will eval() any scripts passed as output (that are valid).
		echo($script);
		exit;
	}

	/** Edit Page/Modal **/

	if ($id == 0) {
		$pageTitle = $lang->asb_add_a_sidebox;

		// this is a new box, check the page view filter to try to predict which script the user will want
		if ($mybb->input['page']) {
			// start them out with the script they are viewing for Which Scripts
			$selectedScripts[] = $mybb->input['page'];
		} else {
			// if page isn't set at all then just start out with all scripts
			$selectedScripts = 'all_scripts';
		}

		$customTitle = 0;
		$currentTitle = '';
	} else {
		$pageTitle = $lang->asb_edit_a_sidebox;

		// ...otherwise we are editing so pull the actual info from the side box
		$selectedScripts = $sidebox->get('scripts');
		if (empty($selectedScripts)) {
			$selectedScripts = 'all_scripts';
		} elseif (isset($selectedScripts[0]) &&
			strlen($selectedScripts[0]) == 0) {
			$scriptWarning = <<<EOF
<span style="color: red;">{$lang->asb_all_scripts_deactivated}</span><br />
EOF;
		}

		// check the name of the add-on/custom against the display name of the sidebox, if they differ...
		if ($sidebox->get('title') != $parent->get('title')) {
			// then this box has a custom title
			$customTitle = 1;
		}
	}

	// AJAX?
	if ($ajax) {
		echo <<<EOF
<div class="modal" style="width: 540px;">
<script src="jscripts/tabs.js" type="text/javascript"></script>
<script src="jscripts/asb/asb_modal.js" type="text/javascript"></script>

EOF;
	} else {
		// standard form stuff
		$page->add_breadcrumb_item($lang->asb);
		$page->add_breadcrumb_item($pageTitle);

		// add a little CSS
		$page->extra_header .= <<<EOF
	<link rel="stylesheet" type="text/css" href="styles/{$cp_style}/asb/global.css" media="screen" />
	<script type="text/javascript" src="jscripts/peeker.js"></script>
	<script src="jscripts/tabs.js" type="text/javascript"></script>

EOF;
		$page->output_header("{$lang->asb} - {$pageTitle}");
	}

	$form = new Form($html->url(array('action' => 'edit_box', 'id' => $id, 'addon' => $module)), 'post', 'modal_form');

	$tabs = array(
		'general' => $lang->asb_modal_tab_general,
		'permissions' => $lang->asb_modal_tab_permissions,
		'pages' => $lang->asb_modal_tab_pages,
		'themes' => $lang->asb_modal_tab_themes,
		'settings' => $lang->asb_modal_tab_settings,
	);

	// we only need a 'Settings' tab if the current module type has settings
	$doSettings = true;
	if (!$sidebox->hasSettings &&
		!$parent->hasSettings) {
		unset($tabs['settings']);
		$doSettings = false;
	}

	reset($tabs);

	$observeOnLoad = false;
	if (!$ajax) {
		$observeOnLoad = true;
	}

	$page->output_tab_control($tabs, $observeOnLoad);

	// custom title?
	if ($customTitle == 1) {
		// alter the descrption
		$currentTitle = <<<EOF
<br /><em>{$lang->asb_current_title}</em>&nbsp;<strong>{$sidebox->get('title')}</strong><br /><br />{$lang->asb_current_title_info}<br />
EOF;
	} else {
		// default description
		$currentTitle = $lang->asb_default_title_info;
	}

	// current editing text
	$currentlyEditing = '"'.$parent->get('title').'"';

	$boxAction = $lang->asb_creating;
	if (isset($mybb->input['id'])) {
		$boxAction = $lang->asb_editing;
	}

	echo "\n<div id=\"tab_general\" style=\"width: auto;\">\n";
	$formContainer = new FormContainer('<h3>'.$lang->sprintf($lang->asb_new_sidebox_action, $boxAction, $currentlyEditing).'</h3>');

	if (!$ajax) {
		// box title
		$formContainer->output_row($lang->asb_custom_title, $currentTitle, $form->generate_text_box('box_title').$form->generate_hidden_field('current_title', $sidebox->get('title')), 'box_title', array('id' => 'box_title'));

		// title link
		$formContainer->output_row($lang->asb_title_link, $lang->asb_title_link_desc, $form->generate_text_box('title_link', $sidebox->get('title_link')), 'title_link', array('id' => 'title_link'));

		// position
		$formContainer->output_row($lang->asb_position, '', $form->generate_radio_button('box_position', 0, $lang->asb_position_left, array('checked' => ($sidebox->get('position') == 0))).'&nbsp;&nbsp;'.$form->generate_radio_button('box_position', 1, $lang->asb_position_right, array('checked' => ($sidebox->get('position') != 0))));

		// display order
		$formContainer->output_row($lang->asb_display_order, '', $form->generate_text_box('display_order', $sidebox->get('display_order')));
	} else {
		// box title
		$formContainer->output_row($lang->asb_title, $currentTitle, $form->generate_text_box('box_title'), 'box_title', array('id' => 'box_title'));

		// title link and hidden fields
		$formContainer->output_row($lang->asb_title_link, $lang->asb_title_link_desc, $form->generate_text_box('title_link', $sidebox->get('title_link')).$form->generate_hidden_field('current_title', $sidebox->get('title')).$form->generate_hidden_field('display_order', $sidebox->get('display_order')).$form->generate_hidden_field('pos', $position), 'title_link', array('id' => 'title_link'));
	}

	$formContainer->end();

	echo "\n</div>\n<div id=\"tab_permissions\" style=\"text-align: center; width: auto;\">\n";
	$formContainer = new FormContainer($lang->asb_which_groups);

	// prepare options for which groups
	$options = array();
	$groups = array();
	$options['all'] = $lang->asb_all_groups;
	$options[0] = $lang->asb_guests;

	// look for all groups except Super Admins
	$query = $db->simple_select('usergroups', 'gid, title', "gid != '1'", array('order_by' => 'gid'));
	while ($usergroup = $db->fetch_array($query)) {
		// store them their titles by group id
		$options[(int)$usergroup['gid']] = $usergroup['title'];
	}

	// do we have groups stored?
	$groups = $sidebox->get('groups');
	if (empty($groups)) {
		$groups = 'all';
	}

	// which groups
	$formContainer->output_row('', $scriptWarning, $form->generate_select_box('group_select_box[]', $options, $groups, array('id' => 'group_select_box', 'multiple' => true, 'size' => 5)));
	$formContainer->output_row('', '', $form->generate_hidden_field('this_group_count', count($options)));

	$formContainer->end();

	echo "\n</div>\n<div id=\"tab_pages\" style=\"text-align: center; width: auto;\">\n";
	$formContainer = new FormContainer($lang->asb_which_scripts);

	// prepare for which scripts
	$choices = array('all_scripts' => $lang->asb_all);

	// are there active scripts?
	if (is_array($allScripts) &&
		!empty($allScripts)) {
		// loop through them
		foreach ($allScripts as $filename => $title) {
			// store the script as a choice
			$choices[$filename] = $title;
		}
	}

	// if there are few scripts to choose from, alter the layout and/or wording of choices
	switch (count($choices)) {
		case 3:
			$choices['all_scripts'] = $lang->asb_both_scripts;
			break;
		case 2:
			unset($choices['all_scripts']);
			$selectedScripts = array_flip($choices);
			break;
		case 1:
			$choices['all_scripts'] = $lang->asb_all_scripts_disabled;
			break;
	}

	// which scripts
	$formContainer->output_row('', $scriptWarning, $form->generate_select_box('script_select_box[]', $choices, $selectedScripts, array('id' => 'script_select_box', 'multiple' => true, 'size' => 5)));
	$formContainer->end();

	echo "\n</div>\n<div id=\"tab_themes\" style=\"text-align: center; width: auto;\">\n";
	$formContainer = new FormContainer($lang->asb_which_themes);

	// do we have themes stored?
	$themes = $sidebox->get('themes');
	if (empty($themes)) {
		$themes = 'all_themes';
	}

	$choices = array('all_themes' => 'All Themes') + asbGetAllThemes();

	// which scripts
	$formContainer->output_row('', '', $form->generate_select_box('theme_select_box[]', $choices, $themes, array('id' => 'theme_select_box', 'multiple' => true, 'size' => 5)));
	$formContainer->end();

	if ($doSettings) {
		echo "</div>\n<div id=\"tab_settings\" style=\"overflow: auto; clear: both; max-height: 360px;\">\n";

		$formContainer = new FormContainer($lang->asb_modal_tab_settings_desc);

		$settings = $parent->get('settings');

		if ($id) {
			$sidebox_settings = $sidebox->get('settings');

			foreach ($settings as $name => $value) {
				if (isset($sidebox_settings[$name])) {
					$settings[$name]['value'] = $sidebox_settings[$name];
				}
			}
		}

		foreach ((array) $settings as $setting) {
			// allow the handler to build module settings
			SideboxModule::buildSetting($setting, $form, $formContainer);
		}

		$formContainer->end();

		$parent->doSettingsLoad();
	}

	echo "</div>\n";

	// AJAX gets a little different wrap-up
	if ($ajax) {
		$buttons[] = $form->generate_submit_button($lang->asb_cancel, array('onclick' => '$.modal.close(); return false;'));
		$buttons[] = $form->generate_submit_button($lang->asb_save, array('id' => 'modalSubmit'));
		$form->output_submit_wrapper($buttons);
		$form->end();
		echo "\n</div>\n";
	} else {
		echo "\n</div>\n";
		// finish form and page
		$buttons[] = $form->generate_submit_button($lang->asb_save, array('name' => 'save_box_submit'));
		$form->output_submit_wrapper($buttons);
		$form->end();

		// output the link menu and MyBB footer
		asbOutputFooter('edit_box');
	}
}

/**
 * handle user-defined box types
 *
 * @return void
 */
function asb_admin_custom_boxes()
{
	global $lang, $mybb, $db, $page, $html, $min, $cp_style;

	if ($mybb->input['mode'] == 'export') {
		if ((int) $mybb->input['id'] == 0) {
			flash_message($lang->asb_custom_export_error, 'error');
			admin_redirect($html->url(array('action' => 'custom_boxes')));
		}

		$custom = new CustomSidebox($mybb->input['id']);
		if (!$custom->isValid()) {
			flash_message($lang->asb_custom_export_error, 'error');
			admin_redirect($html->url(array('action' => 'custom_boxes')));
		}

		$custom->export(array('version' => ASB_CUSTOM_VERSION));
		exit();
	}

	if ($mybb->input['mode'] == 'delete') {
		// info good?
		if ((int) $mybb->input['id'] == 0) {
			flash_message($lang->asb_add_custom_box_delete_failure, 'error');
			admin_redirect($html->url(array('action' => 'custom_boxes')));
		}

		// nuke it
		$custom = new CustomSidebox($mybb->input['id']);

		// success?
		if (!$custom->remove()) {
			flash_message($lang->asb_add_custom_box_delete_failure, 'error');
			admin_redirect($html->url(array('action' => 'custom_boxes')));
		}

		// :)
		flash_message($lang->asb_add_custom_box_delete_success, 'success');
		asbCacheHasChanged();
		admin_redirect($html->url(array('action' => 'custom_boxes')));
	}

	// POSTing?
	if ($mybb->request_method == 'post') {
		if ($mybb->input['mode'] == 'import') {
			if (!$_FILES['file'] ||
				$_FILES['file']['error'] == 4) {
				flash_message($lang->asb_custom_import_no_file, 'error');
				admin_redirect($html->url(array('action' => 'custom_boxes')));
			}

			if ($_FILES['file']['error']) {
				flash_message($lang->sprintf($lang->asb_custom_import_file_error, $_FILES['file']['error']), 'error');
				admin_redirect($html->url(array('action' => 'custom_boxes')));
			}

			if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
				flash_message($lang->asb_custom_import_file_upload_error, 'error');
				admin_redirect($html->url(array('action' => 'custom_boxes')));
			}

			$contents = @file_get_contents($_FILES['file']['tmp_name']);
			@unlink($_FILES['file']['tmp_name']);

			if (!trim($contents)) {
				flash_message($lang->asb_custom_import_file_empty, 'error');
				admin_redirect($html->url(array('action' => 'custom_boxes')));
			}

			require_once MYBB_ROOT.'inc/class_xml.php';
			$parser = new XMLParser($contents);
			$tree = $parser->get_tree();

			if (!is_array($tree) ||
				empty($tree)) {
				flash_message($lang->asb_custom_import_file_empty, 'error');
				admin_redirect($html->url(array('action' => 'custom_boxes')));
			}

			if (!is_array($tree['asb_custom_sideboxes']) ||
				empty($tree['asb_custom_sideboxes'])) {
				flash_message($lang->asb_custom_import_file_empty, 'error');
				admin_redirect($html->url(array('action' => 'custom_boxes')));
			} else {
				$custom = new CustomSidebox;

				if (!$custom->import($contents)) {
					flash_message($lang->asb_custom_import_fail_generic, 'error');
					admin_redirect($html->url(array('action' => 'custom_boxes')));
				}
			}

			if (!$custom->save()) {
				flash_message($lang->asb_custom_box_save_failure, 'error');
				admin_redirect($html->url(array('action' => 'custom_boxes')));
			}

			flash_message($lang->asb_custom_import_save_success, 'success');
			admin_redirect($html->url(array('action' => 'custom_boxes', 'id' => $custom->get('id'))));
		} else {
			// saving?
			if ($mybb->input['save_box_submit'] == 'Save') {
				$id = (int) $mybb->input['id'];

				if (!$mybb->input['box_name'] ||
					!$mybb->input['box_content']) {
					$redirectArray = array(
						'action' => 'custom_boxes',
						'mode' => 'edit',
					);

					if ($id) {
						$redirectArray['id'] = $id;
					}

					flash_message($lang->asb_custom_box_save_failure_no_content, 'error');
					admin_redirect($html->url($redirectArray));
				}

				$custom = new CustomSidebox($id);

				// get the info
				$custom->set('title', $mybb->input['box_name']);
				$custom->set('description', $mybb->input['box_description']);
				$custom->set('content', $mybb->input['box_content']);
				$custom->set('wrap_content', ($mybb->input['wrap_content'] == 'yes'));

				// success?
				if (!$custom->save()) {
					flash_message($lang->asb_custom_box_save_failure, 'error');
					admin_redirect($html->url(array('action' => 'custom_boxes', 'id' => $custom->get('id'))));
				}

				flash_message($lang->asb_custom_box_save_success, 'success');
				asbCacheHasChanged();
				admin_redirect($html->url(array('action' => 'custom_boxes', 'id' => $custom->get('id'))));
			}
		}
	}

	$page->add_breadcrumb_item($lang->asb, $html->url());

	if ($mybb->input['mode'] == 'edit') {
		$query = $db->simple_select('adminoptions', '*', "uid='{$mybb->user['uid']}'");
		$adminOptions = $db->fetch_array($query);

		if ($adminOptions['codepress'] != 0) {
			$page->extra_header .= <<<EOF
<link href="./jscripts/codemirror/lib/codemirror.css" rel="stylesheet">
<link href="./jscripts/codemirror/theme/mybb.css?ver=1804" rel="stylesheet">
<script src="./jscripts/codemirror/lib/codemirror.js"></script>
<script src="./jscripts/codemirror/mode/xml/xml.js"></script>
<script src="./jscripts/codemirror/mode/javascript/javascript.js"></script>
<script src="./jscripts/codemirror/mode/css/css.js"></script>
<script src="./jscripts/codemirror/mode/htmlmixed/htmlmixed.js"></script>
<link href="./jscripts/codemirror/addon/dialog/dialog-mybb.css" rel="stylesheet">
<script src="./jscripts/codemirror/addon/dialog/dialog.js"></script>
<script src="./jscripts/codemirror/addon/search/searchcursor.js"></script>
<script src="./jscripts/codemirror/addon/search/search.js"></script>
<script src="./jscripts/codemirror/addon/fold/foldcode.js"></script>
<script src="./jscripts/codemirror/addon/fold/xml-fold.js"></script>
<script src="./jscripts/codemirror/addon/fold/foldgutter.js"></script>
<link href="./jscripts/codemirror/addon/fold/foldgutter.css" rel="stylesheet">
EOF;
		}

		$custom = new CustomSidebox((int) $mybb->input['id']);

		$action = $lang->asb_add_custom;
		if ($custom->get('id')) {
			$specifyBox = '&amp;id='.$custom->get('id');
			$currentlyEditing = $lang->asb_editing.': <strong>'.$custom->get('title').'</strong>';
			$action = $lang->asb_edit.' '.$custom->get('title');
		} else {
			// new box
			$specifyBox = '';
			$sampleContent = <<<EOF
	<div class="tcat">{$lang->asb_sample_content_tcat}</div>
	<div class="trow1">{$lang->asb_sample_content_trow1}</div>
	<div class="trow2">{$lang->asb_sample_content_trow2}</div>
	<div class="trow_sep">{$lang->asb_sample_content_trow_sep}</div>
	<div class="trow1">{$lang->asb_sample_content_trow1}</div>
	<div class="trow2">{$lang->asb_sample_content_trow2}</div>
	<div class="tfoot">{$lang->asb_sample_content_tfoot}</div>
EOF;
			$custom->set('content', $sampleContent);
			$custom->set('wrap_content', true);
		}

		$page->extra_header .= <<<EOF
	<link rel="stylesheet" type="text/css" href="styles/{$cp_style}/asb/global.css" media="screen" />
EOF;

		$page->add_breadcrumb_item($lang->asb_custom_boxes, $html->url(array('action' => 'custom_boxes')));
		$page->add_breadcrumb_item($lang->asb_add_custom);
		$page->output_header("{$lang->asb} - {$action}");
		asbOutputTabs('asb_add_custom');

		$onClick = <<<EOF
if (!$('#box_name').val() || !editor.getValue()) { $.jGrowl('{$lang->asb_custom_box_save_failure_no_content}', { theme: 'jgrowl_error' }); return false; }
EOF;

		$form = new Form($html->url(array('action' => 'custom_boxes')).$specifyBox, 'post', 'edit_box');
		$formContainer = new FormContainer($currentlyEditing);

		$formContainer->output_row_header($lang->asb_name);
		$formContainer->output_row_header($lang->asb_description);
		$formContainer->output_row_header($lang->asb_custom_box_wrap_content);

		// name
		$formContainer->output_cell($form->generate_text_box('box_name', $custom->get('title'), array('id' => 'box_name')));

		// description
		$formContainer->output_cell($form->generate_text_box('box_description', $custom->get('description')));

		// wrap content?
		$formContainer->output_cell($form->generate_check_box('wrap_content', 'yes', $lang->asb_custom_box_wrap_content_desc, array('checked' => $custom->get('wrap_content'))));
		$formContainer->construct_row();

		$formContainer->output_cell('Content:', array('colspan' => 3));
		$formContainer->construct_row();

		// content
		$formContainer->output_cell($form->generate_text_area('box_content', $custom->get('content'), array('id' => 'box_content', 'class' => '', 'style' => 'width: 100%; height: 500px;')), array('colspan' => 3));
		$formContainer->construct_row();

		// finish form
		$formContainer->end();
		$buttons[] = $form->generate_submit_button('Save', array('name' => 'save_box_submit', 'onclick' => $onClick));
		$form->output_submit_wrapper($buttons);
		$form->end();

		if ($adminOptions['codepress'] != 0) {
			echo <<<EOF
		<script type="text/javascript">
			var editor = CodeMirror.fromTextArea(document.getElementById("box_content"), {
				lineNumbers: true,
				lineWrapping: true,
				foldGutter: true,
				gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"],
				viewportMargin: Infinity,
				indentWithTabs: true,
				indentUnit: 4,
				mode: "text/html",
				theme: "mybb",
			});
		</script>
EOF;
		}

		// build link bar and ACP footer
		asbOutputFooter('edit_custom');
	}

	$page->extra_header .= <<<EOF
	<link rel="stylesheet" type="text/css" href="styles/{$cp_style}/asb/global.css" media="screen" />
EOF;

	$page->add_breadcrumb_item($lang->asb_custom_boxes);
	$page->output_header("{$lang->asb} - {$lang->asb_custom_boxes}");
	asbOutputTabs('asb_custom');

	$newBoxUrl = $html->url(array('action' => 'custom_boxes', 'mode' => 'edit'));
	$newBoxLink = $html->link($newBoxUrl, $lang->asb_add_custom_box_types, array('style' => 'font-weight: bold;', 'title' => $lang->asb_add_custom_box_types, 'icon' => "styles/{$cp_style}/images/asb/add.png"), array('alt' => '+', 'style' => 'margin-bottom: -3px;', 'title' => $lang->asb_add_custom_box_types));
	echo($newBoxLink.'<br /><br />');

	$table = new Table;
	$table->construct_header($lang->asb_name);
	$table->construct_header($lang->asb_custom_box_desc);
	$table->construct_header($lang->asb_controls, array('colspan' => 2));

	$customBoxes = asbGetAllCustomBoxes();

	// if there are saved types...
	if (is_array($customBoxes) &&
		!empty($customBoxes)) {
		// display them
		foreach ($customBoxes as $custom) {
			$data = $custom->get('data');
			// name (edit link)
			$editUrl = $html->url(array('action' => 'custom_boxes', 'mode' => 'edit', 'id' => $data['id']));
			$editLink = $html->link($editUrl, $data['title'], array('title' => $lang->asb_edit, 'style' => 'font-weight: bold;'));

			$table->construct_cell($editLink, array('width' => '30%'));

			// description
			if ($data['description']) {
				$description = $data['description'];
			} else {
				$description = "<em>{$lang->asb_no_description}</em>";
			}
			$table->construct_cell($description, array('width' => '60%'));

			// options popup
			$popup = new PopupMenu('box_'.$data['id'], $lang->asb_options);

			// edit
			$popup->add_item($lang->asb_edit, $editUrl);

			// delete
			$popup->add_item($lang->asb_delete, $html->url(array('action' => 'custom_boxes', 'mode' => 'delete', 'id' => $data['id'])), "return confirm('{$lang->asb_custom_del_warning}');");

			// export
			$popup->add_item($lang->asb_custom_export, $html->url(array('action' => 'custom_boxes', 'mode' => 'export', 'id' => $data['id'])));

			// popup cell
			$table->construct_cell($popup->fetch(), array('width' => '10%'));

			// finish the table
			$table->construct_row();
		}
	} else {
		// no saved types
		$table->construct_cell($lang->asb_no_custom_boxes, array('colspan' => 4));
		$table->construct_row();
	}

	$table->output($lang->asb_custom_box_types);

	echo('<br /><br />');

	$importForm = new Form($html->url(array('action' => 'custom_boxes', 'mode' => 'import')), 'post', '', 1);
	$importFormContainer = new FormContainer($lang->asb_custom_import);
	$importFormContainer->output_row($lang->asb_custom_import_select_file, '', $importForm->generate_file_upload_box('file'));
	$importFormContainer->end();

	$importForm->output_submit_wrapper(array(
		$importForm->generate_submit_button($lang->asb_custom_import, array('name' => 'import')),
	));

	$importForm->end();

	// build link bar and ACP footer
	asbOutputFooter('custom');
}

/**
 * navigate to the script definition you wish to modify
 *
 * @return void
 */
function asb_admin_manage_scripts()
{
	global $mybb, $db, $page, $lang, $html, $min, $cp_style;

	$page->add_breadcrumb_item($lang->asb, $html->url());

	$page->extra_header .= <<<EOF
<link rel="stylesheet" type="text/css" href="styles/{$cp_style}/asb/global.css" media="screen" />

EOF;

	$page->add_breadcrumb_item($lang->asb_manage_scripts);
	$page->output_header("{$lang->asb} - {$lang->asb_manage_scripts}");
	asbOutputTabs('asb_scripts');

	$table = new Table;
	$table->construct_header($lang->asb_title, array('width' => '100%'));

	$allThemes = array(0 => 'Master');
	$query = $db->simple_select('themes', 'name, tid', "pid > 0");
	while ($theme = $db->fetch_array($query)) {
		$allThemes[$theme['tid']] = $theme['name'];
	}

	foreach ($allThemes as $tid => $name) {
		$viewUrl = $html->url(array('action' => 'view_scripts', 'tid' => $tid));
		$table->construct_cell($html->link($viewUrl, $name, array('style' => 'font-weight: bold;')));
		$table->construct_row();
	}

	$table->output('Select a theme');

	// output the link menu and MyBB footer
	asbOutputFooter('manage_scripts');
}

/**
 * add/edit/delete script info
 *
 * @return void
 */
function asb_admin_view_scripts()
{
	global $mybb, $db, $page, $lang, $html, $min, $cp_style;

	$page->add_breadcrumb_item($lang->asb, $html->url());

	$tid = isset($mybb->input['tid']) ? (int) $mybb->input['tid'] : 0;

	$viewingMaster = false;
	$theme = get_theme($tid);
	if (!$theme) {
		if ($tid) {
			flash_message('The specified theme does not exist.', 'error');
			admin_redirect($html->url(array('action' => 'manage_scripts')));
		}

		// master
		$viewingMaster = true;
		$theme = array(
			'name' => 'Master Copy',
		);
	}

	if ($mybb->request_method == 'post') {
		if ($mybb->input['mode'] == 'edit') {
			$id = (int) $mybb->input['id'];
			$redirectUrl = $html->url(array('action' => 'view_scripts', 'mode' => 'edit', 'tid' => $tid, 'id' => $id));

			$mybb->input['action'] = $mybb->input['script_action'];
			$script = new ScriptInfo($mybb->input);

			$totalWidth = $mybb->get_input('width_left', \MyBB::INPUT_FLOAT) + $mybb->get_input('width_right', \MyBB::INPUT_FLOAT) + $mybb->get_input('width_middle', \MyBB::INPUT_FLOAT) + $mybb->get_input('left_margin', \MyBB::INPUT_FLOAT) + $mybb->get_input('right_margin', \MyBB::INPUT_FLOAT);

			if ($totalWidth > 100) {
				flash_message($lang->asb_script_save_width_error, 'error');
				admin_redirect($redirectUrl);
			}

			$keys = $script->get(array('filename', 'action', 'page'));
			$dupeId = asbFindDuplicateScriptByFilename($keys, $tid);
			if ($dupeId) {
				asbConfirmScriptOverwrite($dupeId, $tid);
			}

			if (!$script->save()) {
				flash_message($lang->asb_script_save_fail, 'error');
				admin_redirect($redirectUrl);
			}

			asbCacheHasChanged();

			flash_message($lang->asb_script_save_success, 'success');
			admin_redirect($html->url(array('action' => 'view_scripts', 'tid' => $tid)));
		} elseif ($mybb->input['mode'] == 'overwrite') {
			$id = (int) $mybb->input['id'];
			$redirectUrl = $html->url(array('action' => 'view_scripts', 'mode' => 'edit', 'tid' => $tid, 'id' => $id));

			$script = new ScriptInfo($id);

			if (!$script->isValid()) {
				flash_message($lang->asb_script_save_fail, 'error');
				admin_redirect($redirectUrl);
			}

			$script->set($mybb->input['data']);

			if (!$script->save()) {
				flash_message($lang->asb_script_save_fail, 'error');
				admin_redirect($redirectUrl);
			}

			asbCacheHasChanged();

			flash_message($lang->asb_script_save_success, 'success');
			admin_redirect($html->url(array('action' => 'view_scripts', 'tid' => $tid)));
		} elseif ($mybb->input['mode'] == 'import') {
			$overWrite = isset($mybb->input['overwrite']) && $mybb->input['overwrite'];

			if ($overWrite) {
				$contents = $mybb->input['contents'];
			} else {
				if (!$_FILES['file'] ||
					$_FILES['file']['error'] == 4) {
					flash_message($lang->asb_custom_import_no_file, 'error');
					admin_redirect($html->url(array('action' => 'view_scripts')));
				}

				if ($_FILES['file']['error']) {
					flash_message($lang->sprintf($lang->asb_custom_import_file_error, $_FILES['file']['error']), 'error');
					admin_redirect($html->url(array('action' => 'view_scripts')));
				}

				if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
					flash_message($lang->asb_custom_import_file_upload_error, 'error');
					admin_redirect($html->url(array('action' => 'view_scripts')));
				}

				$contents = @file_get_contents($_FILES['file']['tmp_name']);
				@unlink($_FILES['file']['tmp_name']);
			}

			if (strlen(trim($contents)) == 0) {
				flash_message($lang->asb_custom_import_file_empty, 'error');
				admin_redirect($html->url(array('action' => 'view_scripts')));
			}

			$script = new ScriptInfo;
			if (!$script->import($contents)) {
				flash_message($lang->asb_script_import_fail, 'error');
				admin_redirect($html->url(array('action' => 'view_scripts')));
			}

			$script->set('tid', $tid);

			if (!$overWrite) {
				$keys = $script->get(array('filename', 'action', 'page'));
				if (asbFindDuplicateScriptByFilename($keys, $tid)) {
					asbConfirmScriptImportOverwrite($contents, $tid);
				} elseif (!$viewingMaster &&
					asbFindDuplicateScriptByFilename($keys) === false) {
					$script->set('tid', 0);
				}
			}

			if (!$script->save()) {
				flash_message($lang->asb_script_import_fail, 'error');
			}

			asbCacheHasChanged();

			flash_message($lang->asb_script_import_success, 'success');
			admin_redirect($html->url(array('action' => 'view_scripts', 'tid' => $tid)));
		} elseif ($mybb->input['mode'] == 'inline') {
			$redirect = $html->url(array('action' => 'view_scripts', 'tid' => $tid));

			if (!is_array($mybb->input['asb_inline_ids']) ||
				empty($mybb->input['asb_inline_ids'])) {
				flash_message($lang->asb_inline_selection_error, 'error');
				admin_redirect($redirect);
			}

			$job_count = 0;
			foreach ($mybb->input['asb_inline_ids'] as $id => $throwAway) {
				$script = new ScriptInfo($id);
				if (!$script->isValid()) {
					continue;
				}

				$deleted = false;
				switch ($mybb->input['inline_action']) {
					case 'update_width':
						$action = $lang->asb_updated;
						$changed = false;
						$totalWidth = 0;

						foreach (array('width_left', 'left_margin', 'width_middle', 'right_margin', 'width_right') as $key) {
							if (isset($mybb->input[$key][$id])) {
								$totalWidth += $mybb->input[$key][$id];
								$script->set($key, $mybb->input[$key][$id]);
								$changed = true;
							}
						}

						if ($totalWidth > 100) {
							flash_message($lang->asb_script_save_width_error, 'error');
							admin_redirect($redirect);
						}

						if ($changed == false) {
							continue 2;
						}

						$scriptTid = (int) $script->get('tid');
						if (!$viewingMaster &&
							$scriptTid === 0) {
							$script->set(array('id' => 0, 'tid' => $tid));
						}

						$script->save();
						break;
					case 'delete':
						$action = $lang->asb_deleted;
						if (!$script->remove()) {
							continue 2;
						}

						$deleted = true;
						break;
					case 'activate':
						$action = $lang->asb_activated;
						if ($script->get('active')) {
							continue 2;
						}

						$script->set('active', 1);
						$script->save();
						break;
					case 'deactivate':
						$action = $lang->asb_deactivated;
						if (!$script->get('active')) {
							continue 2;
						}

						$script->set('active', 0);
						$script->save();
						break;
					default:
						continue 2;
				}

				++$job_count;
			}

			$status = 'error';
			$objectTitle = $lang->asb_script_definitions;
			if ($job_count > 0) {
				$status = 'success';
				asbCacheHasChanged();

				if ($job_count == 1) {
					$objectTitle = $lang->asb_script_definition;
				}
			}

			flash_message($lang->sprintf($lang->asb_inline_success, $job_count, $objectTitle, $action), $status);
			admin_redirect($redirect);
		}
	}

	if (in_array($mybb->input['mode'], array('delete', 'revert')) &&
		$mybb->input['id']) {
		$script = new ScriptInfo((int) $mybb->input['id']);

		$failMessage = $lang->asb_script_delete_fail;
		$successMessage = $lang->asb_script_delete_success;
		if ($mybb->input['mode'] == 'revert') {
			$failMessage = $lang->asb_script_revert_fail;
			$successMessage = $lang->asb_script_revert_success;
		}

		if (!$script->remove()) {
			flash_message($failMessage, 'error');
		} else {
			flash_message($successMessage, 'success');
			asbCacheHasChanged();
		}
	} elseif ($mybb->input['mode'] == 'export' && $mybb->input['id']) {
		$script = new ScriptInfo((int) $mybb->input['id']);

		if (!$script->export(array('version' => ASB_SCRIPT_VERSION))) {
			flash_message($lang->asb_script_export_fail, 'error');
			admin_redirect($html->url(array('action' => 'view_scripts')));
		}
		exit;
	} elseif (($mybb->input['mode'] == 'activate' ||
			$mybb->input['mode'] == 'deactivate') &&
		$mybb->input['id']) {
		$script = new ScriptInfo((int) $mybb->input['id']);
		$script->set('active', ($mybb->input['mode'] == 'activate'));

		if (!$script->save()) {
			$action = ($mybb->input['mode'] == 'activate') ? $lang->asb_script_activate_fail : $lang->asb_script_deactivate_fail;
			flash_message($action, 'error');
		} else {
			$action = ($mybb->input['mode'] == 'activate') ? $lang->asb_script_activate_success : $lang->asb_script_deactivate_success;
			flash_message($action, 'success');
			asbCacheHasChanged();
		}
		admin_redirect($html->url(array('action' => 'view_scripts')));
	}

	if ($mybb->input['mode'] == 'edit') {
		$script = new ScriptInfo((int) $mybb->input['id']);

		$detectedShow = ' style="display: none;"';
		$buttonText = $lang->asb_add;
		$filename = '';

		$data = array(
			'active' => 'false',
			'find_top' => '{$header}',
			'find_bottom' => '{$footer}',
			'replace_all' => 0,
			'eval' => 0,
			'width_left' => '20',
			'left_margin' => '0.5',
			'width_middle' => '59',
			'right_margin' => '0.5',
			'width_right' => '20',
		);

		$action = $lang->asb_edit_script;
		if ($script->isValid()) {
			$data = $script->get('data');

			$detectedInfo = asbDetectScriptInfo($data['filename'], array(
				'hook' => $data['hook'],
				'action' => $data['action'],
				'template' => $data['template_name'],
			));

			$detectedShow = '';
			$buttonText = $lang->asb_update;
			$filename = $data['filename'];
			$action = "{$lang->asb_edit} {$data['title']}";
		}

		$lang->asb_edit_script = $action;

		$query = $db->simple_select('adminoptions', '*', "uid='{$mybb->user['uid']}'");
		$adminOptions = $db->fetch_array($query);

		if ($adminOptions['codepress'] != 0) {
			$page->extra_header .= <<<EOF
	<link href="./jscripts/codemirror/lib/codemirror.css" rel="stylesheet">
	<link href="./jscripts/codemirror/theme/mybb.css?ver=1804" rel="stylesheet">
	<script src="./jscripts/codemirror/lib/codemirror.js"></script>
	<script src="./jscripts/codemirror/mode/xml/xml.js"></script>
	<script src="./jscripts/codemirror/mode/javascript/javascript.js"></script>
	<script src="./jscripts/codemirror/mode/css/css.js"></script>
	<script src="./jscripts/codemirror/mode/htmlmixed/htmlmixed.js"></script>
	<link href="./jscripts/codemirror/addon/dialog/dialog-mybb.css" rel="stylesheet">
	<script src="./jscripts/codemirror/addon/dialog/dialog.js"></script>
	<script src="./jscripts/codemirror/addon/search/searchcursor.js"></script>
	<script src="./jscripts/codemirror/addon/search/search.js"></script>
	<script src="./jscripts/codemirror/addon/fold/foldcode.js"></script>
	<script src="./jscripts/codemirror/addon/fold/xml-fold.js"></script>
	<script src="./jscripts/codemirror/addon/fold/foldgutter.js"></script>
	<link href="./jscripts/codemirror/addon/fold/foldgutter.css" rel="stylesheet">
EOF;
		}

		$page->extra_header .= <<<EOF
	<script type="text/javascript" src="./jscripts/peeker.js"></script>
	<script type="text/javascript" src="jscripts/asb/asb_scripts{$min}.js"></script>
	<script type="text/javascript">
	<!--
		ASB.scripts.setup('{$filename}', {
			nothing_found: '{$lang->asb_ajax_nothing_found}',
			hooks: '{$lang->asb_ajax_hooks}',
			actions: '{$lang->asb_ajax_actions}',
			templates: '{$lang->asb_ajax_templates}',
			error_file_name_empty: '{$lang->asb_ajax_file_name_empty}',
			error_file_does_not_exist: '{$lang->asb_ajax_file_does_not_exist}',
			error_file_empty: '{$lang->asb_ajax_file_empty}',
		});
	// -->
	</script>
	<link rel="stylesheet" type="text/css" href="styles/{$cp_style}/asb/global.css" media="screen" />

EOF;

		$page->add_breadcrumb_item($lang->asb_view_scripts, $html->url(array('action' => 'view_scripts')));
		$page->add_breadcrumb_item($lang->asb_edit_script);
		$page->output_header("{$lang->asb} - {$lang->asb_view_scripts} - {$lang->asb_edit_script}");
		asbOutputTabs('asb_edit_script');

		// editing master definition
		if ($viewingMaster) {
			$page->output_alert('You are editing the master definition. Any changes made to this copy will be reflected in all unmodified copies of this definition in every theme.');
		} elseif ((int) $data['tid'] !== $tid) {
			// creating new definition
			$page->output_alert('You are creating a custom copy of the master definition for use with this theme. Changes made to the master copy will no longer affect this definition until you revert.');

			$data['id'] = 0;
		}

		$spinner = <<<EOF
<div class="ajax_spinners" style="display: none;">
	<img src="../images/spinner.gif" alt="{$lang->asb_detecting}..."/><br /><br />
</div>
EOF;

		$form = new Form($html->url(array('action' => 'view_scripts', 'mode' => 'edit')), 'post', 'edit_script');
		$formContainer = new FormContainer($lang->asb_edit_script);

		$formContainer->output_row("{$lang->asb_title}:", $lang->asb_title_desc, $form->generate_text_box('title', $data['title']));

		$formContainer->output_row("{$lang->asb_filename}:", $lang->asb_filename_desc, '<div id="file_info"></div>'.$form->generate_text_box('filename', $data['filename'], array('id' => 'filename')));

		$formContainer->output_row("{$lang->asb_action}:", $lang->sprintf($lang->asb_scriptvar_generic_desc, strtolower($lang->asb_action)), "{$spinner}<div id=\"action_list\"{$detectedShow}>{$detectedInfo['actions']}</div>".$form->generate_text_box('script_action', $data['action'], array('id' => 'action')));
		$formContainer->output_row($lang->asb_page, $lang->sprintf($lang->asb_scriptvar_generic_desc, strtolower($lang->asb_page)), $form->generate_text_box('page', $data['page']));

		$formContainer->output_row($lang->asb_disable_for_mobile_title, $lang->asb_disable_for_mobile_description, $form->generate_yes_no_radio('mobile_disabled', (int)$data['mobile_disabled'] ?? 0, true, array('id' => 'mobile_disabled_yes', 'class' => 'mobile_disabled'), array('id' => 'mobile_disabled_no', 'class' => 'mobile_disabled')), '', array(), array('id' => 'mobile_disabled'));

		$formContainer->output_row($lang->asb_width_left, $lang->asb_width_left_desc, $form->generate_text_box('width_left', $data['width_left']));
		$formContainer->output_row($lang->asb_left_margin, $lang->asb_left_margin_desc, $form->generate_text_box('left_margin', $data['left_margin']));
		$formContainer->output_row($lang->asb_width_middle, $lang->asb_width_middle_desc, $form->generate_text_box('width_middle', $data['width_middle']));
		$formContainer->output_row($lang->asb_right_margin, $lang->asb_right_margin_desc, $form->generate_text_box('right_margin', $data['right_margin']));
		$formContainer->output_row($lang->asb_width_right, $lang->asb_width_right_desc, $form->generate_text_box('width_right', $data['width_right']));

		$formContainer->output_row("{$lang->asb_output_to_vars}?", $lang->sprintf($lang->asb_output_to_vars_desc, '<span style="font-family: courier; font-weight: bold; font-size: 1.2em;">$asb_left</span> and <span style="font-family: courier; font-weight: bold; font-size: 1.2em;";>$asb_right</span>'), $form->generate_yes_no_radio('eval', $data['eval'], true, array('id' => 'eval_yes', 'class' => 'eval'), array('id' => 'eval_no', 'class' => 'eval')), '', array(), array('id' => 'var_output'));

		$formContainer->output_row("{$lang->asb_template}:", $lang->asb_template_desc, "{$spinner}<div id=\"template_list\"{$detectedShow}>{$detectedInfo['templates']}</div>".$form->generate_text_box('template_name', $data['template_name'], array('id' => 'template_name')), '', array(), array('id' => 'template_row'));
		$formContainer->output_row("{$lang->asb_hook}:", $lang->asb_hook_desc, "{$spinner}<div id=\"hook_list\"{$detectedShow}>{$detectedInfo['hooks']}</div>".$form->generate_text_box('hook', $data['hook'], array('id' => 'hook')), '', array(), array('id' => 'hook_row'));

		$formContainer->output_row($lang->asb_header_search_text, $lang->asb_header_search_text_desc, $form->generate_text_area('find_top', $data['find_top'], array('id' => 'find_top', 'class' => '', 'style' => 'width: 100%;', 'rows' => '3')), '', array(), array('id' => 'header_search'));
		$formContainer->output_row($lang->asb_footer_search_text, $lang->asb_footer_search_text_desc, $form->generate_text_area('find_bottom', $data['find_bottom'], array('id' => 'find_bottom', 'class' => '', 'style' => 'width: 100%; height: 100px;')).$form->generate_hidden_field('tid', $tid).$form->generate_hidden_field('id', $data['id']).$form->generate_hidden_field('active', $data['active']).$form->generate_hidden_field('action', 'view_scripts').$form->generate_hidden_field('mode', 'edit'), '', array(), array('id' => 'footer_search'));

		$formContainer->output_row($lang->asb_replace_template, $lang->asb_replace_template_desc, $form->generate_yes_no_radio('replace_all', $data['replace_all'], true, array('id' => 'replace_all_yes', 'class' => 'replace_all'), array('id' => 'replace_all_no', 'class' => 'replace_all')), '', array(), array('id' => 'replace_all'));

		$formContainer->output_row($lang->asb_replacement_content, $lang->asb_replacement_content_desc, $form->generate_text_area('replacement', $data['replacement'], array('id' => 'replacement', 'class' => '', 'style' => 'width: 100%; height: 240px;')), '', array(), array('id' => 'replace_content'));

		$formContainer->end();

		$buttons = array($form->generate_submit_button($buttonText, array('name' => 'add')));
		$form->output_submit_wrapper($buttons);
		$form->end();

		// output CodeMirror scripts if necessary
		if ($adminOptions['codepress'] != 0) {
			echo <<<EOF
		<script type="text/javascript">
			var options = {
					lineNumbers: true,
					lineWrapping: true,
					foldGutter: true,
					gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"],
					viewportMargin: Infinity,
					indentWithTabs: true,
					indentUnit: 4,
					mode: "text/html",
					theme: "mybb"
				},
				editorFindTop,
				editorFindBottom,
				editorReplacement;

				editorFindTop = CodeMirror.fromTextArea(document.getElementById("find_top"), options).setSize('100%', 80);
				editorFindBottom = CodeMirror.fromTextArea(document.getElementById("find_bottom"), options).setSize('100%', 80);
				editorReplacement = CodeMirror.fromTextArea(document.getElementById("replacement"), options).setSize('100%', 300);
		</script>

EOF;
		}

		// output the link menu and MyBB footer
		asbOutputFooter('edit_scripts');

		exit;
	}

	$page->extra_header .= <<<EOF
<link rel="stylesheet" type="text/css" href="styles/{$cp_style}/asb/global.css" media="screen" />
<script type="text/javascript" src="jscripts/asb/asb_inline{$min}.js"></script>
<script type="text/javascript">
<!--
ASB.inline.setup({
	go: '{$lang->go}',
	noSelection: '{$lang->asb_inline_selection_error}',
});
// -->
</script>

EOF;

	$page->add_breadcrumb_item($lang->asb_manage_scripts, $html->url(array('action' => 'manage_scripts')));
	$page->add_breadcrumb_item("{$lang->asb_view_scripts} ({$theme['name']})");
	$page->output_header("{$lang->asb} - {$lang->asb_view_scripts} - {$theme['name']}");
	asbOutputTabs('asb_view_scripts');

	$newScriptUrl = $html->url(array('action' => 'view_scripts', 'mode' => 'edit', 'tid' => $tid));
	$newScriptLink = $html->link($newScriptUrl, $lang->asb_add_new_script, array('style' => 'font-weight: bold;', 'title' => $lang->asb_add_new_script, 'icon' => "styles/{$cp_style}/images/asb/add.png"), array('alt' => '+', 'title' => $lang->asb_add_new_script, 'style' => 'margin-bottom: -3px;'));
	echo($newScriptLink.'<br /><br />');

	$form = new Form($html->url(array('action' => 'view_scripts', 'mode' => 'inline')), 'post', 'inline_form');

	$table = new Table;
	$table->construct_header($lang->asb_title, array('width' => '20%'));
	$table->construct_header($lang->asb_filename, array('width' => '10%'));
	$table->construct_header($lang->asb_action, array('width' => '8%'));
	$table->construct_header($lang->asb_page, array('width' => '8%'));
	$table->construct_header($lang->asb_width_left, array('width' => '8%'));
	$table->construct_header($lang->asb_left_margin, array('width' => '8%'));
	$table->construct_header($lang->asb_width_middle, array('width' => '8%'));
	$table->construct_header($lang->asb_right_margin, array('width' => '8%'));
	$table->construct_header($lang->asb_width_right, array('width' => '8%'));
	$table->construct_header($lang->asb_status, array('width' => '8%'));
	$table->construct_header($lang->asb_controls, array('width' => '10%'));
	$table->construct_header($form->generate_check_box('', '', '', array('id' => 'asb_select_all')), array('style' => 'width: 1%'));

	$where = "tid IN(0,{$tid})";
	$deleteScriptText = $lang->asb_revert;
	$deleteScriptKey = 'revert';
	if ($viewingMaster) {
		$deleteScriptText = $lang->asb_delete;
		$deleteScriptKey = 'delete';
		$where = 'tid=0';
	}

	$query = $db->simple_select('asb_script_info', '*', $where, array('order_by' => 'title', 'order_dir' => 'ASC'));

	$allScripts = array();
	while ($data = $db->fetch_array($query)) {
		$isMaster = (int) $data['tid'] === 0;
		$key = asbBuildScriptFilename($data);

		if (!$viewingMaster &&
			$isMaster &&
			isset($allScripts[$key]['tid']) &&
			$allScripts[$key]['tid'] > 0) {
			continue;
		}

		$allScripts[$key] = $data;
	}

	if (count($allScripts) > 0) {
		foreach ($allScripts as $data) {
			$inherited = (int) $data['tid'] === 0;
			$statusMessage = 'custom';
			$statusColor = '#32cd32';
			if ($inherited) {
				$statusMessage = 'inherited';
				$statusColor = 'grey';
			}

			$editUrl = $html->url(array('action' => 'view_scripts', 'mode' => 'edit', 'tid' => $tid, 'id' => $data['id']));
			$activateUrl = $html->url(array('action' => 'view_scripts', 'mode' => 'activate', 'tid' => $tid, 'id' => $data['id']));
			$deactivateUrl = $html->url(array('action' => 'view_scripts', 'mode' => 'deactivate', 'tid' => $tid, 'id' => $data['id']));
			$activateLink = $html->link($activateUrl, $lang->asb_inactive, array('style' => 'font-weight: bold; color: red;', 'title' => $lang->asb_inactive_desc));
			$deactivateLink = $html->link($deactivateUrl, $lang->asb_active, array('style' => 'font-weight: bold; color: green', 'title' => $lang->asb_active_desc));
			$none = <<<EOF
<span style="color: gray;"><em>{$lang->asb_none}</em></span>
EOF;

			$inheritedText = ' <span class="smalltext float_right" style="color: '.$statusColor.';">(master)</span>';
			if (!$viewingMaster) {
				$inheritedText = ' <span class="smalltext float_right" style="color: '.$statusColor.';">('.$statusMessage.')</span>';
			}

			$table->construct_cell($html->link($editUrl, $data['title'], array('style' => 'font-weight: bold;')).$inheritedText);
			$table->construct_cell($data['filename']);
			$table->construct_cell($data['action'] ? $data['action'] : $none);
			$table->construct_cell($data['page'] ? $data['page'] : $none);
			$table->construct_cell($form->generate_text_box("width_left[{$data['id']}]", $data['width_left'], array('class' => 'asb-dimension-cell')).'%');
			$table->construct_cell($form->generate_text_box("left_margin[{$data['id']}]", $data['left_margin'], array('class' => 'asb-dimension-cell')).'%');
			$table->construct_cell($form->generate_text_box("width_middle[{$data['id']}]", $data['width_middle'], array('class' => 'asb-dimension-cell')).'%');
			$table->construct_cell($form->generate_text_box("right_margin[{$data['id']}]", $data['right_margin'], array('class' => 'asb-dimension-cell')).'%');
			$table->construct_cell($form->generate_text_box("width_right[{$data['id']}]", $data['width_right'], array('class' => 'asb-dimension-cell')).'%');
			$table->construct_cell($data['active'] ? $deactivateLink : $activateLink);

			// options popup
			$popup = new PopupMenu("script_{$data['id']}", $lang->asb_options);

			// edit
			$popup->add_item($lang->asb_edit, $editUrl);

			// export
			$popup->add_item($lang->asb_custom_export, $html->url(array('action' => 'view_scripts', 'mode' => 'export', 'tid' => $tid, 'id' => $data['id'])));

			// delete
			if ($viewingMaster ||
				$data['tid'] > 0) {
				$popup->add_item($deleteScriptText, $html->url(array('action' => 'view_scripts', 'mode' => $deleteScriptKey, 'tid' => $tid, 'id' => $data['id'])), "return confirm('{$lang->asb_script_del_warning}');");
			}

			// popup cell
			$table->construct_cell($popup->fetch());
			$table->construct_cell($form->generate_check_box("asb_inline_ids[{$data['id']}]", '', '', array('class' => 'asb_check')));
			$table->construct_row();
			$table->construct_row();
		}
	} else {
		$table->construct_cell("<span style=\"color: gray;\"><em>{$lang->asb_no_scripts}</em></span>", array('colspan' => 9));
		$table->construct_row();
	}

	$inline = <<<EOF
<div>
	<span>
		<strong>{$lang->asb_inline_title}:</strong>&nbsp;
		<select name="inline_action">
			<option value="update_width">{$lang->asb_update_width}</option>
			<option value="activate">{$lang->asb_activate}</option>
			<option value="deactivate">{$lang->asb_deactivate}</option>
			<option value="delete">{$lang->asb_delete}</option>
		</select>
		<input id="asb_inline_submit" type="submit" class="button" name="asb_inline_submit" value="{$lang->go} (0)"/>
		<input id="asb_inline_clear" type="button" class="button" name="asb_inline_clear" value="{$lang->clear}"/>
		<input type="hidden" name="tid" value="{$tid}" />
	</span>
</div>
EOF;

	$table->construct_cell('', array('colspan' => 4, 'style' =>'border-right: none;'));
	$table->construct_cell($inline, array('colspan' => 5, 'style' =>'border-left: none;'));
	$table->construct_row();

	$table->output($lang->asb_script_info);
	$form->end();

	$form = new Form($html->url(array('action' => 'view_scripts', 'mode' => 'import')), 'post', '', 1);
	$formContainer = new FormContainer($lang->asb_custom_import);
	$formContainer->output_row($lang->asb_custom_import_select_file, '', $form->generate_file_upload_box('file').$form->generate_hidden_field('tid', $tid));
	$formContainer->end();
	$importButtons[] = $form->generate_submit_button($lang->asb_custom_import, array('name' => 'import'));
	$form->output_submit_wrapper($importButtons);
	$form->end();

	// output the link menu and MyBB footer
	asbOutputFooter('view_scripts');
}

/**
 * view and delete add-ons
 *
 * @return void
 */
function asb_admin_manage_modules()
{
	global $lang, $mybb, $db, $page, $html, $min, $cp_style;

	$page->extra_header .= <<<EOF
	<link rel="stylesheet" type="text/css" href="styles/{$cp_style}/asb/global.css" media="screen" />

EOF;

	$page->add_breadcrumb_item($lang->asb, $html->url());
	$page->add_breadcrumb_item($lang->asb_manage_modules);

	$page->output_header("{$lang->asb} - {$lang->asb_manage_modules}");
	asbOutputTabs('asb_modules');

	$table = new Table;
	$table->construct_header($lang->asb_name, array('width' => '22%'));
	$table->construct_header($lang->asb_description, array('width' => '55%'));
	$table->construct_header($lang->asb_modules_author, array('width' => '15%'));
	$table->construct_header($lang->asb_controls, array('width' => '8%'));

	$addons = asbGetAllModules();

	// if there are installed modules display them
	if (!empty($addons) &&
		is_array($addons)) {
		foreach ($addons as $module) {
			$data = $module->get(array('title', 'description', 'baseName', 'author', 'author_site', 'module_site', 'version', 'public_version', 'compatibility'));

			$out_of_date = '';
			if (!$data['compatibility'] ||
				version_compare(ASB_MIN_MODULE_VERSION, $data['compatibility'], '>')) {
				$out_of_date = <<<EOF
<br /><span style="color: red;">{$lang->asb_module_out_of_date}</span>
EOF;
			}

			$version = $data['version'];
			if ($data['public_version']) {
				$version = $data['public_version'];
			}

			// title
			$table->construct_cell($html->link($data['module_site'], $data['title'], array('style' => 'font-weight: bold;'))." ({$version})");

			// description
			$table->construct_cell($data['description'].$out_of_date);

			if ($data['author'] == 'Wildcard') {
				$data['author'] = 'default';
			}

			$author = $data['author'];
			if ($data['author_site']) {
				$author = $html->link($data['author_site'], $data['author'], array('style' => 'font-weight: bold;'));
			}

			// author
			$table->construct_cell($author);

			// options pop-up
			$popup = new PopupMenu('module_'.$data['baseName'], $lang->asb_options);

			// delete
			$popup->add_item($lang->asb_delete, $html->url(array('action' => 'delete_addon', 'addon' => $data['baseName'])), "return confirm('{$lang->asb_modules_del_warning}');");

			// pop-up cell
			$table->construct_cell($popup->fetch(), array('width' => '10%'));

			// finish row
			$table->construct_row();
		}
	} else {
		$table->construct_cell("<span style=\"color: gray;\">{$lang->asb_no_modules_detected}</span>", array('colspan' => 3));
		$table->construct_row();
	}
	$table->output($lang->asb_addon_modules);

	// build link bar and ACP footer
	asbOutputFooter('addons');
}

/**
 * handler for AJAX side box routines
 *
 * @return void
 */
function asb_admin_xmlhttp()
{
	global $db, $mybb;

	// if ordering (or trashing)
	if ($mybb->input['mode'] == 'order') {
		parse_str($mybb->input['data']);

		${$mybb->input['pos']} = $sidebox;

		if ($mybb->input['pos'] == 'trash_column') {
			// if there is nothing in the column
			if (!is_array($trash_column) ||
				empty($trash_column)) {
				exit;
			}

			// loop through them all
			$ids = array();
			foreach ($trash_column as $id) {
				$sidebox = new SideboxObject($id);
				$sidebox->remove();

				// return the removed side boxes id to the SideboxObject object (so that the div can be destroyed as well)
				$ids[] = $id;
			}
			asbCacheHasChanged();
			$ids = implode(',', $ids);
			echo($ids);
			exit;
		} elseif($mybb->input['pos'] == 'right_column') {
			$position = 1;
			$this_column = $right_column;
		} elseif($mybb->input['pos'] == 'left_column') {
			$position = 0;
			$this_column = $left_column;
		}

		// if there are side boxes in this column after the move (this function is called by onUpdate)
		if (!is_array($this_column) ||
			empty($this_column)) {
			return;
		}

		$disp_order = 1;

		// loop through all the side boxes in this column
		foreach ($this_column as $id) {
			$has_changed = false;
			$sidebox = new SideboxObject($id);
			$this_order = (int) ($disp_order * 10);
			++$disp_order;

			// if the order has been edited
			if ($sidebox->get('display_order') != $this_order) {
				// handle it
				$sidebox->set('display_order', $this_order);
				$has_changed = true;
			}

			// if the position has changed
			if ($sidebox->get('position') != $position) {
				// alter it
				$sidebox->set('position', $position);
				$has_changed = true;
			}

			// if the side box has been modified
			if ($has_changed != false) {
				// save it
				$sidebox->save();
				asbCacheHasChanged();
			}
		}
		// this routine allows the side box's visibility tool tip and links to be handled by JS after the side box is created
	} elseif($mybb->input['mode'] == 'build_info' && (int) $mybb->input['id'] > 0) {
		$id = (int) $mybb->input['id'];
		$sidebox = new SideboxObject($id);

		// we have to reaffirm our observance of the edit link when it is added/updated
		$script = <<<EOF
<script type="text/javascript">
$("#edit_sidebox_{$id}").click(function(event) {
	// stop the link from redirecting the user-- set up this way so that if JS is disabled the user goes to a standard form rather than a modal edit form
		event.preventDefault();

	$.get($(this).prop("href") + '&ajax=1', function (html) {
		$(html).appendTo('body').modal({
			fadeDuration: 250,
			zIndex: (typeof modal_zindex !== 'undefined' ? modal_zindex : 9999),
		});
	});
});
</script>
EOF;
		// this HTML output will be directly stored in the side box's representative <div>
		echo(asbBuildSideBoxInfo($sidebox, false, true).$script);
		/*
         * searches for hooks, templates and actions and returns an
         * array of JSON encoded select box HTML for any that are found
         */
	} elseif($mybb->input['mode'] == 'analyze_script' &&
		trim($mybb->input['filename'])) {
		$content = asbDetectScriptInfo($mybb->input['filename'], $mybb->input['selected']);

		header('Content-type: application/json');
		echo(json_encode($content));
	}
}

/**
 * remove a side box (only still around for those without JS...like who, idk)
 *
 * @return void
 */
function asb_admin_delete_box()
{
	global $mybb, $lang, $html;

	if ((int) $mybb->input['id'] == 0) {
		flash_message($lang->asb_delete_box_failure, 'error');
		admin_redirect($html->url());
	}

	$sidebox = new SideboxObject($mybb->input['id']);
	if (!$sidebox->remove()) {
		flash_message($lang->asb_delete_box_failure, 'error');
	} else {
		flash_message($lang->asb_delete_box_success, 'success');
		asbCacheHasChanged();
	}

	admin_redirect($html->url());
}

/**
 * completely remove an add-on module
 *
 * @return void
 */
function asb_admin_delete_addon()
{
	global $mybb, $html, $lang;

	// info goof?
	if (!isset($mybb->input['addon']) ||
		strlen(trim($mybb->input['addon'])) == 0) {
		flash_message($lang->asb_delete_addon_failure, 'error');
		admin_redirect($html->url(array('action' => 'manage_modules')));
	}

	$module = new SideboxModule($mybb->input['addon']);
	if (!$module->remove()) {
		flash_message($lang->asb_delete_addon_failure, 'error');
	} else {
		flash_message($lang->asb_delete_addon_success, 'success');
		asbCacheHasChanged();
	}

	admin_redirect($html->url(array('action' => 'manage_modules')));
}

/**
 * confirm overwriting a script definition on save
 *
 * @param  string
 * @param  int
 * @return void
 */
function asbConfirmScriptOverwrite($id, $tid)
{
	global $html, $mybb, $db, $page, $lang, $html, $min, $cp_style;;

	$page->extra_header .= <<<EOF
<link rel="stylesheet" type="text/css" href="styles/{$cp_style}/asb/global.css" media="screen" />

EOF;

	$page->add_breadcrumb_item($lang->asb, $html->url());
	$page->add_breadcrumb_item($lang->asb_overwrite_confirmation);
	$page->output_header("{$lang->asb} - {$lang->asb_overwrite_confirmation}");
	asbOutputTabs('asb_overwrite_confirmation');

	$form = new Form($html->url(array('action' => 'view_scripts', 'mode' => 'overwrite')), 'post', '', 1);

	$dataElements = '';
	foreach ($mybb->input as $key => $val) {
		$dataElements .= $form->generate_hidden_field("data[{$key}]", $val);
	}

	$formContainer = new FormContainer($lang->asb_overwrite_confirmation);
	$formContainer->output_row($lang->asb_overwrite_confirmation, '', $lang->asb_save_overwrite_warning.$form->generate_hidden_field('overwrite', true).$dataElements.$form->generate_hidden_field('id', $id).$form->generate_hidden_field('tid', $tid));
	$formContainer->end();

	$confirmationButtons[] = $form->generate_submit_button($lang->asb_confirm, array('name' => 'confirm'));
	$confirmationButtons[] = $form->generate_submit_button($lang->asb_cancel, array('name' => 'cancel'));
	$form->output_submit_wrapper($confirmationButtons);
	$form->end();

	// output the link menu and MyBB footer
	asbOutputFooter('manage_scripts');
	exit;
}

/**
 * confirm overwriting a script definition on import
 *
 * @param  string
 * @param  int
 * @return void
 */
function asbConfirmScriptImportOverwrite($contents, $tid)
{
	global $html, $mybb, $db, $page, $lang, $html, $min, $cp_style;;

	if (empty($contents)) {
		flash_message('Invalid script.', 'error');
		admin_redirect($html->url(array('action' => 'view_scripts', 'tid' => (int) $tid)));
	}

	$page->extra_header .= <<<EOF
<link rel="stylesheet" type="text/css" href="styles/{$cp_style}/asb/global.css" media="screen" />

EOF;

	$page->add_breadcrumb_item($lang->asb, $html->url());
	$page->add_breadcrumb_item($lang->asb_overwrite_confirmation);
	$page->output_header("{$lang->asb} - {$lang->asb_overwrite_confirmation}");
	asbOutputTabs('asb_overwrite_confirmation');

	$form = new Form($html->url(array('action' => 'view_scripts', 'mode' => 'import')), 'post', '', 1);

	$formContainer = new FormContainer($lang->asb_overwrite_confirmation);
	$formContainer->output_row($lang->asb_overwrite_confirmation, '', $lang->asb_import_overwrite_warning.$form->generate_hidden_field('overwrite', true).$form->generate_hidden_field('contents', $contents).$form->generate_hidden_field('tid', $tid));
	$formContainer->end();

	$confirmationButtons[] = $form->generate_submit_button($lang->asb_confirm, array('name' => 'confirm'));
	$confirmationButtons[] = $form->generate_submit_button($lang->asb_cancel, array('name' => 'cancel'));
	$form->output_submit_wrapper($confirmationButtons);
	$form->end();

	// output the link menu and MyBB footer
	asbOutputFooter('manage_scripts');
	exit;
}

/**
 * rebuild the theme exclude list.
 *
 * @return void
 */
function asb_admin_update_theme_select()
{
	// is the group installed?
	$gid = asbGetSettingsGroup();
	if ((int) $gid == 0) {
		flash_message($lang->asb_theme_exclude_select_update_fail, 'error');
		admin_redirect('index.php?module=config-settings');
	}

	global $db, $lang;

	if (!$lang->asb) {
		$lang->load('asb');
	}

	$status = asbUpdateThemeSelectSetting();

	if (!$status) {
		flash_message($lang->asb_theme_exclude_select_update_fail, 'error');
		admin_redirect('index.php?module=config-settings');
	}

	flash_message($lang->asb_theme_exclude_select_update_success, 'success');
	admin_redirect(asbBuildSettingsUrl($gid));
}

/**
 * serialize the theme exclusion list selector
 *
 * @return void
 */
$plugins->add_hook('admin_config_settings_change', 'asb_admin_config_settings_change');
function asb_admin_config_settings_change()
{
	global $mybb;

	/* only serialize our setting if it is being saved
     * (thanks to Tanweth for helping me find this)
     *
     * we are checking for the existence of asb_show_empty_boxes
     * because checking for asb_exclude_theme fails if deselecting
     * all themes:
     * https://github.com/WildcardSearch/Advanced-Sidebox/issues/148
     */
	if (isset($mybb->input['upsetting']['asb_show_empty_boxes'])) {
		$mybb->input['upsetting']['asb_exclude_theme'] = serialize($mybb->input['upsetting']['asb_exclude_theme']);
	}
}

/**
 * @param  array items on the config tab
 * @return void
 */
$plugins->add_hook('admin_config_action_handler', 'asb_admin_config_action_handler');
function asb_admin_config_action_handler(&$action)
{
	$action['asb'] = array('active' => 'asb');
}

/**
 * add an entry to the ACP Config page menu
 *
 * @param  array menu
 * @return void
 */
$plugins->add_hook('admin_config_menu', 'asb_admin_config_menu');
function asb_admin_config_menu(&$sub_menu)
{
	global $lang;
	if (!isset($lang->asb)) {
		$lang->load('asb');
	}

	end($sub_menu);
	$key = (key($sub_menu)) + 10;
	$sub_menu[$key] = array(
		'id' => 'asb',
		'title' => $lang->asb,
		'link' => ASB_URL
	);
}

/**
 * add an entry to admin permissions list
 *
 * @param  array permission types
 * @return void
 */
$plugins->add_hook('admin_config_permissions', 'asb_admin_config_permissions');
function asb_admin_config_permissions(&$admin_permissions)
{
	global $lang;

	if (!$lang->asb) {
		$lang->load('asb');
	}
	$admin_permissions['asb'] = $lang->asb_admin_permissions_desc;
}

/**
 * update the theme exclude selector after any themes are added or deleted
 *
 * @return void
 */
$plugins->add_hook('admin_style_themes_import_commit', 'asbUpdateThemeSelectSetting');
$plugins->add_hook('admin_style_themes_duplicate_commit', 'asbUpdateThemeSelectSetting');
$plugins->add_hook('admin_style_themes_add_commit', 'asbUpdateThemeSelectSetting');
$plugins->add_hook('admin_style_themes_delete_commit', 'asbUpdateThemeSelectSetting');
function asbUpdateThemeSelectSetting()
{
	global $db, $lang;

	if (!$lang->asb) {
		$lang->load('asb');
	}

	$query = $db->simple_select('settings', '*', "name='asb_exclude_theme'");

	// is the setting created?
	if ($db->num_rows($query) == 0) {
		return false;
	}

	// update the setting
	return $db->update_query('settings', array('optionscode' => $db->escape_string(asbBuildThemeExcludeSelect())), "name='asb_exclude_theme'");
}

/**
 * update the theme exclude selector after any themes are edited
 *
 * @return void
 */
$plugins->add_hook('admin_style_themes_edit_commit', 'asbUpdateThemeSelectSettingOnEdit');
function asbUpdateThemeSelectSettingOnEdit()
{
	global $db, $update_array, $theme;

	// the edit commit hook is before the actualy update so we have to update the title ourselves.
	$db->update_query('themes', array('name' => $update_array['name']), "tid='{$theme['tid']}'");

	asbUpdateThemeSelectSetting();
}

/**
 * add our user sidebox preference setting in user edit
 *
 * @param  array options
 * @return void
 */
$plugins->add_hook('admin_user_users_edit_other_options', 'asb_admin_user_users_edit_other_options');
function asb_admin_user_users_edit_other_options($other_options)
{
	global $mybb, $user, $lang, $form, $form_container;

	if (!$lang->asb) {
		$lang->load('asb');
	}

	$checked = false;
	if ($user['show_sidebox'] > 0) {
		$checked = true;
	}

	array_unshift($other_options, $form->generate_check_box('show_sidebox', 1, 'Show side boxes', array("checked" => $mybb->input['show_sidebox'])));
	return $other_options;
}

/**
 * update the user sidebox preference when saving the user
 *
 * @return void
 */
$plugins->add_hook('admin_user_users_edit_commit_start', 'asb_admin_user_users_edit_commit_start');
function asb_admin_user_users_edit_commit_start()
{
	global $mybb, $extra_user_updates;

	$extra_user_updates['show_sidebox'] = $mybb->input['show_sidebox'];
}