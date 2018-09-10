<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains the functions used in ACP
 */

/**
 * produces a link to a particular page in the plugin help system (with icon) specified by topic
 *
 * @param  string topic keyword
 * @return string html
 */
function asb_build_help_link($topic = '')
{
	global $mybb, $lang, $html, $cp_style;

	if (!$topic) {
		$topic = 'manage_sideboxes';
	}

	switch ($topic) {
	case 'manage_sideboxes':
		$url = 'https://github.com/WildcardSearch/Advanced-Sidebox/wiki/Help-Managing-Sideboxes';
		break;
	case 'edit_box':
		$url = 'https://github.com/WildcardSearch/Advanced-Sidebox/wiki/Help-Add-New-Side-Box';
		break;
	case 'edit_custom':
		$url = 'https://github.com/WildcardSearch/Advanced-Sidebox/wiki/Help-Add-New-Custom-Box';
		break;
	case 'custom':
		$url = 'https://github.com/WildcardSearch/Advanced-Sidebox/wiki/Help-Custom-Boxes';
		break;
	case 'edit_scripts':
		$url = 'https://github.com/WildcardSearch/Advanced-Sidebox/wiki/Help-Add-New-Script-Definition';
		break;
	case 'manage_scripts':
		$url = 'https://github.com/WildcardSearch/Advanced-Sidebox/wiki/Help-Managing-Scripts';
		break;
	case 'addons':
		$url = 'https://github.com/WildcardSearch/Advanced-Sidebox/wiki/Help-Managing-Modules';
		break;
	}

	return $html->link($url, $lang->asb_help, array("target" => '_blank', "style" => 'font-weight: bold;', "icon" => "styles/{$cp_style}/images/asb/help.png", "title" => $lang->asb_help), array("alt" => '?', "title" => $lang->asb_help, "style" => 'margin-bottom: -3px;'));
}

/**
 * produces a link to the plugin settings with icon
 *
 * @return string html
 */
function asb_build_settings_menu_link()
{
	global $mybb, $lang, $html, $cp_style;

	$settings_url = asb_build_settings_url(asb_get_settingsgroup());
	$settings_link = $html->link($settings_url, $lang->asb_plugin_settings, array("icon" => "styles/{$cp_style}/images/asb/settings.png", "style" => 'font-weight: bold;', "title" => $lang->asb_plugin_settings), array("alt" => 'S', "style" => 'margin-bottom: -3px;'));
	return $settings_link;
}

/**
 * Output ACP tabs for our pages
 *
 * @param  string current tab
 * @return void
 */
function asb_output_tabs($current)
{
	global $page, $lang, $mybb, $html;

	// set up tabs
	$sub_tabs['asb'] = array(
		'title' 				=> $lang->asb_manage_sideboxes,
		'link' 					=> $html->url(),
		'description' 		=> $lang->asb_manage_sideboxes_desc
	);

	$sub_tabs['asb_custom'] = array(
		'title'					=> $lang->asb_custom_boxes,
		'link'					=> $html->url(array("action" => 'custom_boxes')),
		'description'		=> $lang->asb_custom_boxes_desc
	);

	if (in_array($current, array('asb_add_custom', 'asb_custom'))) {
		$sub_tabs['asb_add_custom'] = array(
			'title'					=> $lang->asb_add_custom,
			'link'					=> $html->url(array("action" => 'custom_boxes', "mode" => 'edit')),
			'description'		=> $lang->asb_add_custom_desc
		);
	}
	$sub_tabs['asb_scripts'] = array(
		'title'					=> $lang->asb_manage_scripts,
		'link'					=> $html->url(array("action" => 'manage_scripts')),
		'description'		=> $lang->asb_manage_scripts_desc
	);
	if (in_array($current, array('asb_edit_script', 'asb_scripts'))) {
		$sub_tabs['asb_edit_script'] = array(
			'title'					=> $lang->asb_edit_script,
			'link'					=> $html->url(array("action" => 'manage_scripts', "mode" => 'edit')),
			'description'		=> $lang->asb_edit_script_desc
		);
	}
	$sub_tabs['asb_modules'] = array(
		'title'					=> $lang->asb_manage_modules,
		'link'					=> $html->url(array("action" => 'manage_modules')),
		'description'		=> $lang->asb_manage_modules_desc
	);
	$page->output_nav_tabs($sub_tabs, $current);
}

/**
 * output ACP footers for our pages
 *
 * @param  string current page
 * @return void
 */
function asb_output_footer($page_key)
{
    global $page;

	echo(asb_build_footer_menu($page_key));
	$page->output_footer();
}

/**
 * build a footer menu specific to each page
 *
 * @param  string topic key
 * @return string html
 */
function asb_build_footer_menu($page_key = '')
{
	global $mybb;

	if (!$page_key) {
		$page_key = 'manage_sideboxes';
	}

	$help_link = '&nbsp;' . asb_build_help_link($page_key);
	$settings_link = '&nbsp;' . asb_build_settings_menu_link();

	if ($page_key == 'manage_sideboxes') {
		$filter_links = asb_build_filter_selector($mybb->input['page']);
	}

	return <<<EOF

<div class="asb_label">
{$filter_links}
	{$settings_link}
	{$help_link}
</div>

EOF;
}

/**
 *  build a popup with a table of side box permission info
 *
 * @param  int id
 * @return string html
 */
function asb_build_permissions_table($id)
{
	if (!$id) {
		return false;
	}

	global $lang, $all_scripts;

	$sidebox = new SideboxObject($id);

	if (!$sidebox->isValid()) {
		return $lang->asb_invalid_sidebox;
	}

	if (!$all_scripts) {
		return $lang->asb_no_active_scripts;
	}

	$visibility_rows = asb_build_visibility_rows($sidebox, $group_count, $global);
	$theme_list = asb_build_theme_visibility_list($sidebox, $group_count + 1, $global);

	return <<<EOF
<table width="100%" class="box_info">{$visibility_rows}{$theme_list}
</table>
EOF;
}

/**
 * build HTML for the script/group table rows
 *
 * @param  SideboxObject
 * @param  int group count
 * @param  bool global visibility
 * @return string html
 */
function asb_build_visibility_rows($sidebox, &$group_count, &$global)
{
	global $db, $lang, $all_scripts;

	if (!is_array($all_scripts) ||
		empty($all_scripts)) {
		return $lang->asb_no_active_scripts;
	}

	// prepare options for which groups
	$options = array($lang->asb_guests);
	$groups = array();

	// look for all groups except Super Admins
	$query = $db->simple_select('usergroups', 'gid, title', "gid != '1'", array('order_by' => 'gid'));
	while ($usergroup = $db->fetch_array($query)) {
		// store the titles by group id
		$options[(int)$usergroup['gid']] = $usergroup['title'];
	}
	$group_count = $all_group_count = count($options);

	$groups = $sidebox->get('groups');
	$scripts = $sidebox->get('scripts');

	if (empty($scripts)) {
		if (empty($groups)) {
			$global = true;
			return "<tr><td>{$lang->asb_globally_visible}</td></tr>";
		} elseif (isset($groups[0]) && strlen($groups[0]) == 0) {
			return "<tr><td>{$lang->asb_all_scripts_deactivated}</td></tr>";
		} else {
			$scripts = $all_scripts;
		}
	} elseif (isset($scripts[0]) &&
		strlen($scripts[0]) == 0) {
		return "<tr><td>{$lang->asb_all_scripts_deactivated}</td></tr>";
	}

	$group_headers = '';
	foreach ($options as $gid => $title) {
		$group_headers .= <<<EOF

		<td title="{$title}" class="group_header">{$gid}</td>
EOF;
	}

	$script_rows = '';
	foreach ($all_scripts as $script => $script_title) {
		$script_title_full = '';
		if (strlen($script_title) > 15) {
			$script_title_full = $script_title;
			$script_title = substr($script_title, 0, 15) . '...';
		}

		$script_rows .= <<<EOF

	<tr>
		<td class="script_header" title="{$script_title_full}">{$script_title}</td>
EOF;

		if (empty($scripts) ||
			array_key_exists($script, $scripts) ||
			in_array($script, $scripts)) {
			if (empty($groups)) {
				$x = 1;
				while ($x <= $all_group_count) {
					$script_rows .= <<<EOF

		<td class="info_cell on"></td>
EOF;
					++$x;
				}
			} else {
				foreach ($options as $gid => $title) {
					$vis_class = 'off';
					if (in_array($gid, $groups)) {
						$vis_class = 'on';
					}
					$script_rows .= <<<EOF

		<td class="info_cell {$vis_class}"></td>
EOF;
				}
			}
		} else {
			$x = 1;
			while ($x <= $all_group_count) {
				$script_rows .= <<<EOF

		<td class="info_cell off"></td>
EOF;
				++$x;
			}
		}

		$script_rows .= <<<EOF

	</tr>
EOF;
	}

	return <<<EOF

	<tr>
		<td class="group_header"><strong>{$lang->asb_visibility}</strong></td>{$group_headers}
	</tr>{$script_rows}
EOF;
}

/**
 * build HTML for the script/group table rows
 *
 * @param  SideboxObject
 * @param  int group count
 * @param  bool global visibility
 * @return string html
 */
function asb_build_theme_visibility_list($sidebox, $colspan, $global)
{
	global $lang;

	$themes = asb_get_all_themes();
	$good_themes = $sidebox->get('themes');

	if (!$themes) {
		return false;
	}

	if (!$good_themes ||
		count($good_themes) == count($themes)) {
		$theme_list = $lang->asb_visibile_for_all_themes;
		if ($global) {
			return '';
		}
	} else {
		$theme_list = '';
		foreach ($themes as $tid => $name) {
			if ($good_themes &&
				!in_array($tid, $good_themes)) {
				$theme_list .= <<<EOF
{$sep}<s>{$name}</s>
EOF;
			} else {
				$theme_list .= <<<EOF
{$sep}{$name}
EOF;
			}
			$sep = ', ';
		}
	}

	return <<<EOF

	<tr>
		<td colspan="{$colspan}">{$theme_list}</td>
	</tr>
EOF;
}

/**
 * @param  SideboxObject
 * @param  bool wrap in div?
 * @param  bool produce delete link?
 * @return string html
 */
function asb_build_sidebox_info($sidebox, $wrap = true, $ajax = false)
{
	// must be a valid object
	if ($sidebox instanceof SideboxObject == false) {
		return false;
	}

	global $html, $scripts, $all_scripts, $lang, $cp_style;

	$title = $sidebox->get('title');
	$id = $sidebox->get('id');
	$pos = $sidebox->get('position');
	$module = $sidebox->get('box_type');

	// visibility table
	$visibility = '<span class="custom info">' . asb_build_permissions_table($id) . '</span>';

	// edit link
	$edit_link = $html->url(array("action" => 'edit_box', "id" => $id, "addon" => $module, "pos" => $pos));
	$edit_icon = <<<EOF
<a href="{$edit_link}" class="info_icon" id="edit_sidebox_{$id}" title="{$lang->asb_edit}"><img src="styles/{$cp_style}/images/asb/edit.png" height="18" width="18" alt="{$lang->asb_edit}"/></a>
EOF;

	// delete link (only used if JS is disabled)
	if (!$ajax) {
		$delete_link = $html->url(array("action" => 'delete_box', "id" => $id));
		$delete_icon = <<<EOF
<a href="{$delete_link}" class="del_icon" title="{$lang->asb_delete}"><img src="styles/{$cp_style}/images/asb/delete.png" height="18" width="18" alt="{$lang->asb_delete}"/></a>
EOF;
	}

	// the content
	$box = <<<EOF
<span class="tooltip"><img class="info_icon" src="styles/{$cp_style}/images/asb/visibility.png" alt="Information" height="18" width="18"/>{$visibility}</span>{$edit_icon}{$delete_icon}{$title}
EOF;

	// the <div> (if applicable)
	if ($wrap) {
		$box = <<<EOF
<div id="sidebox_{$id}" class="sidebox sortable">{$box}</div>

EOF;
	}

	// return the content (which will either be stored in a string and displayed by asb_main() or will be stored directly in the <div> when called from AJAX
	return $box;
}

/**
 * set the flag so the cache is rebuilt next run
 *
 * @return void
 */
function asb_cache_has_changed()
{
	AdvancedSideboxCache::getInstance()->update('has_changed', true);
}

/**
 * searches for hooks, templates and actions and returns a
 * keyed array of select box HTML for any that are found
 *
 * @param  string
 * @param  array
 * @return array script component information
 */
function asb_detect_script_info($filename, $selected = array())
{
	global $lang;

	// check all the info
	if (strlen(trim($filename)) == 0) {
		return false;
	}

	$full_path = '../' . trim($filename);
	if (!file_exists($full_path)) {
		return false;
	}

	$contents = @file_get_contents($full_path);
	if (!$contents) {
		return false;
	}

	// build the object info
	$info = array(
		"hook" => array(
			"pattern" => "#\\\$plugins->run_hooks\([\"|'|&quot;]([\w|_]*)[\"|'|&quot;](.*?)\)#i",
			"filter" => '_do_',
			"plural" => $lang->asb_hooks
		),
		"template" => array(
			"pattern" => "#\\\$templates->get\([\"|'|&quot;]([\w|_]*)[\"|'|&quot;](.*?)\)#i",
			"filter" => '',
			"plural" => $lang->asb_templates
		),
		"action" => array(
			"pattern" => "#\\\$mybb->input\[[\"|'|&quot;]action[\"|'|&quot;]\] == [\"|'|&quot;]([\w|_]*)[\"|'|&quot;]#i",
			"filter" => '',
			"plural" => $lang->asb_actions
		)
	);

	$form = new Form('', '', '', 0, '', true);
	foreach (array('hook', 'template', 'action') as $key) {
		$array_name = "{$key}s";
		$$array_name = array();

		// find any references to the current object
		preg_match_all($info[$key]['pattern'], $contents, $matches, PREG_SET_ORDER);
		foreach ($matches as $match) {
			// no duplicates and if there is a filter check it
			if (!in_array($match[1], $$array_name) &&
				(strlen(${$array_name}['filter'] == 0 ||
				strpos($match[1], ${$array_name}['filter']) === false))) {
				${$array_name}[$match[1]] = $match[1];
			}
		}

		// anything to show?
		if (!empty($$array_name)) {

			// sort the results, preserving keys
			ksort($$array_name);

			// make none = '' the first entry
			$$array_name = array_reverse($$array_name);
			${$array_name}[] = 'none';
			$$array_name = array_reverse($$array_name);

			// store the HTML select box
			$return_array[$array_name] = '<span style="font-weight: bold;">' . $lang->asb_detected . ' ' . $info[$key]['plural'] . ':</span><br />' . $form->generate_select_box("{$array_name}_options", $$array_name, $selected[$key], array("id" => "{$key}_selector")) . '<br /><br />';
		} else {
			$varName = "asb_ajax_{$array_name}";
			$noContent = $lang->sprintf($lang->asb_ajax_nothing_found, $lang->$varName);

			// store the no content message
			$return_array[$array_name] = <<<EOF
<span style="font-style: italic;">{$noContent}</span>
EOF;
		}
	}
	return $return_array;
}

/**
 * imports XML files created with ASB 1.x series
 *
 * @param  array
 * @return void
 */
function asb_legacy_custom_import($tree)
{
	global $lang;

	if (!is_array($tree['adv_sidebox']['custom_sidebox']) ||
		empty($tree['adv_sidebox']['custom_sidebox'])) {
		return $lang->asb_custom_import_file_corrupted;
	}

	foreach ($tree['adv_sidebox']['custom_sidebox'] as $property => $value) {
		if ($property == 'tag' ||
			$property == 'value') {
			continue;
		}
		$input_array[$property] = $value['value'];
	}

	if ($input_array['content'] &&
		$input_array['checksum'] &&
		my_strtolower(md5(base64_decode($input_array['content']))) == my_strtolower($input_array['checksum'])) {
		$input_array['content'] = trim(base64_decode($input_array['content']));
		$input_array['title'] = $input_array['name'];
		return $input_array;
	}

	$error = $lang->asb_custom_import_file_empty;
	if ($input_array['content']) {
		$error = $lang->asb_custom_import_file_corrupted;
	}
	return $error;
}

/**
 * build links for ACP Manage Side Boxes screen
 *
 * @param  string script to show or 'all_scripts' to avoid filtering altogether
 * @return string html
 */
function asb_build_filter_selector($filter)
{
	global $all_scripts;

	// if there are active scripts . . .
	if (!is_array($all_scripts) ||
		empty($all_scripts)) {
		return;
	}

	global $lang, $html;
	$options = array_merge(array("" => 'no filter'), $all_scripts);
	$form = new Form($html->url(), 'post', 'script_filter', 0, 'script_filter');
	echo($form->generate_select_box('page', $options, $filter));
	echo($form->generate_submit_button('Filter', array('name' => 'filter')));
	return $form->end();
}

/**
 * creates a single setting from an associative array
 *
 * @param  DefaultForm
 * @param  DefaultFormContainer
 * @param  array setting properties
 * @return void
 */
function asb_build_setting($this_form, $this_form_container, $setting)
{
	// create each element with unique id and name properties
	$options = '';
	$type = explode("\n", $setting['optionscode']);
    $type = array_map('trim', $type);
	$element_name = "{$setting['name']}";
	$element_id = "setting_{$setting['name']}";
	$row_id = "row_{$element_id}";

	// prepare labels
	$this_label = '<strong>' . htmlspecialchars_uni($setting['title']) . '</strong>';
	$this_desc = '<i>' . $setting['description'] . '</i>';

	// sort by type
	if ($type[0] == 'text' ||
		$type[0] == '') {
		$this_form_container->output_row($this_label, $this_desc, $this_form->generate_text_box($element_name, $setting['value'], array('id' => $element_id)), $element_name, array("id" => $row_id));
	} else if ($type[0] == 'textarea') {
		$this_form_container->output_row($this_label, $this_desc, $this_form->generate_text_area($element_name, $setting['value'], array('id' => $element_id)), $element_name, array('id' => $row_id));
	} else if ($type[0] == 'yesno') {
		$this_form_container->output_row($this_label, $this_desc, $this_form->generate_yes_no_radio($element_name, $setting['value'], true, array('id' => $element_id.'_yes', 'class' => $element_id), array('id' => $element_id.'_no', 'class' => $element_id)), $element_name, array('id' => $row_id));
	} else if ($type[0] == 'onoff') {
		$this_form_container->output_row($this_label, $this_desc, $this_form->generate_on_off_radio($element_name, $setting['value'], true, array('id' => $element_id.'_on', 'class' => $element_id), array('id' => $element_id.'_off', 'class' => $element_id)), $element_name, array('id' => $row_id));
	} else if ($type[0] == 'language') {
		$languages = $lang->get_languages();
		$this_form_container->output_row($this_label, $this_desc, $this_form->generate_select_box($element_name, $languages, $setting['value'], array('id' => $element_id)), $element_name, array('id' => $row_id));
	} else if ($type[0] == 'adminlanguage') {
		$languages = $lang->get_languages(1);
		$this_form_container->output_row($this_label, $this_desc, $this_form->generate_select_box($element_name, $languages, $setting['value'], array('id' => $element_id)), $element_name, array('id' => $row_id));
	} else if ($type[0] == 'passwordbox') {
		$this_form_container->output_row($this_label, $this_desc, $this_form->generate_password_box($element_name, $setting['value'], array('id' => $element_id)), $element_name, array('id' => $row_id));
	} else if ($type[0] == 'php') {
		$setting['optionscode'] = substr($setting['optionscode'], 3);
		eval("\$setting_code = \"" . $setting['optionscode'] . "\";");
	} else {
		for ($i=0; $i < count($type); $i++) {
			$optionsexp = explode('=', $type[$i]);
			if (!$optionsexp[1]) {
				continue;
			}

			if ($type[0] == 'select') {
				$option_list[$optionsexp[0]] = htmlspecialchars_uni($optionsexp[1]);
			} else if ($type[0] == 'radio') {
				if ($setting['value'] == $optionsexp[0]) {
					$option_list[$i] = $this_form->generate_radio_button($element_name, $optionsexp[0], htmlspecialchars_uni($optionsexp[1]), array('id' => $element_id.'_'.$i, "checked" => 1, 'class' => $element_id));
				} else {
					$option_list[$i] = $this_form->generate_radio_button($element_name, $optionsexp[0], htmlspecialchars_uni($optionsexp[1]), array('id' => $element_id.'_'.$i, 'class' => $element_id));
				}
			} else if($type[0] == 'checkbox') {
				if ($setting['value'] == $optionsexp[0]) {
					$option_list[$i] = $this_form->generate_check_box($element_name, $optionsexp[0], htmlspecialchars_uni($optionsexp[1]), array('id' => $element_id.'_'.$i, "checked" => 1, 'class' => $element_id));
				} else {
					$option_list[$i] = $this_form->generate_check_box($element_name, $optionsexp[0], htmlspecialchars_uni($optionsexp[1]), array('id' => $element_id.'_'.$i, 'class' => $element_id));
				}
			}
		}
		if ($type[0] == 'select') {
			$this_form_container->output_row($this_label, $this_desc, $this_form->generate_select_box($element_name, $option_list, $setting['value'], array('id' => $element_id)), $element_name, array('id' => $row_id));
		} else {
			$setting_code = implode('<br />', $option_list);
		}
	}

	if ($setting_code) {
		$this_form_container->output_row($this_label, $this_desc, $setting_code, '', array(), array('id' => $row_id));
	}
}

?>
