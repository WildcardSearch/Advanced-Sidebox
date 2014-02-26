<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2013 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains the functions used in ACP and depends upon html_generator.php
 */

/*
 * asb_build_help_link()
 *
 * produces a link to a particular page in the plugin help system (with icon) specified by topic
 *
 * @param - $topic is the intended page's topic keyword
 * @return: (string) help link HTML
 */
function asb_build_help_link($topic = '')
{
	global $mybb, $lang, $html;

	if(!$topic)
	{
		$topic = 'manage_sideboxes';
	}

	$help_url = $html->url(array("topic" => $topic), "{$mybb->settings['bburl']}/inc/plugins/asb/help/index.php");
	return $html->link($help_url, $lang->asb_help, array("id" => 'help_link', "style" => 'font-weight: bold;', "icon" => "{$mybb->settings['bburl']}/inc/plugins/asb/images/help.gif", "title" => $lang->asb_help), array("id" => 'help_link_icon', "alt" => '?', "title" => $lang->asb_help, "style" => 'margin-bottom: -3px;'));
}

/*
 * asb_build_settings_menu_link()
 *
 * produces a link to the plugin settings with icon
 *
 * @return: (string) settings link HTML
 */
function asb_build_settings_menu_link()
{
	global $mybb, $lang, $html;

	$settings_url = asb_build_settings_url(asb_get_settingsgroup());
	$settings_link = $html->link($settings_url, $lang->asb_plugin_settings, array("icon" => "{$mybb->settings['bburl']}/inc/plugins/asb/images/settings.gif", "style" => 'font-weight: bold;', "title" => $lang->asb_plugin_settings), array("alt" => 'S', "style" => 'margin-bottom: -3px;'));
	return $settings_link;
}

/*
 * asb_output_tabs()
 *
 * Output ACP tabs for our pages
 *
 * @param - $current is the tab currently being viewed
 * @return: n/a
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

	if(in_array($current, array('asb_add_custom', 'asb_custom')))
	{
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
	if(in_array($current, array('asb_edit_script', 'asb_scripts')))
	{
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

/*
 * asb_output_footer()
 *
 * Output ACP footers for our pages
 *
 * @param - $page_key - (string) the current page key used by the help
 * system and the footer menu
 * @return: n/a
 */
function asb_output_footer($page_key)
{
    global $page;

	echo(asb_build_footer_menu($page_key));
	$page->output_footer();
}

/*
 * asb_build_footer_menu()
 *
 * build a footer menu specific to each page
 *
 * @param - $page_key is the topic key name for the current page
 * @return: (string) the footer menu HTML
 */
function asb_build_footer_menu($page_key = '')
{
	global $mybb;

	if(!$page_key)
	{
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

/*
 * asb_build_permissions_table()
 *
 * build a popup with a table of side box permission info
 *
 * @param - $id is the numeric id of the sidebox
 * @return: (string) the permission table HTML
 */
function asb_build_permissions_table($id)
{
	if(!$id)
	{
		return false;
	}

	global $db, $lang, $all_scripts;

	$sidebox = new Sidebox($id);

	if(!$sidebox->is_valid())
	{
		return false;
	}

	// prepare options for which groups
	$options = array('Guests');
	$groups = array();

	// look for all groups except Super Admins
	$query = $db->simple_select("usergroups", "gid, title", "gid != '1'", array('order_by' => 'gid'));
	while($usergroup = $db->fetch_array($query))
	{
		// store the titles by group id
		$options[(int)$usergroup['gid']] = $usergroup['title'];
	}

	$groups = $sidebox->get('groups');
	$scripts = $sidebox->get('scripts');

	if(empty($scripts))
	{
		if(empty($groups))
		{
			return $lang->asb_globally_visible;
		}
		elseif(isset($groups[0]) && strlen($groups[0]) == 0)
		{
			return $lang->asb_all_scripts_deactivated;
		}
		else
		{
			$scripts = $all_scripts;
		}
	}
	elseif(isset($scripts[0]) && strlen($scripts[0]) == 0)
	{
		return $lang->asb_all_scripts_deactivated;
	}

	if(!is_array($all_scripts) || empty($all_scripts))
	{
		return false;
	}

	$all_group_count = count($options);
	$info = <<<EOF

<table width="100%" class="box_info">
<tr>
<td class="group_header"><strong>{$lang->asb_visibility}</strong></td>

EOF;

	foreach($options as $gid => $title)
	{
		$info .= <<<EOF
<td title="{$title}" class="group_header">{$gid}</td>

EOF;
	}

	$info .= '	</tr>
';

	foreach($all_scripts as $script => $script_title)
	{
		$script_title_full = '';
		if(strlen($script_title) > 8)
		{
			$script_title_full = $script_title;
			$script_title = substr($script_title, 0, 8) . '. . .';
		}
		$info .= <<<EOF
<tr>
<td class="script_header" title="{$script_title_full}">{$script_title}</td>

EOF;
		if(empty($scripts) || array_key_exists($script, $scripts) || in_array($script, $scripts))
		{
			if(empty($groups))
			{
				$x = 1;
				while($x <= $all_group_count)
				{
					$info .= <<<EOF
<td class="info_cell on"></td>

EOF;
					++$x;
				}
			}
			else
			{
				foreach($options as $gid => $title)
				{
					if(in_array($gid, $groups))
					{
						$info .= <<<EOF
<td class="info_cell on"></td>

EOF;
					}
					else
					{
						$info .= <<<EOF
<td class="info_cell off"></td>

EOF;
					}
				}
			}
		}
		else
		{
			$x = 1;
			while($x <= $all_group_count)
			{
				$info .= <<<EOF
<td class="info_cell off"></td>

EOF;
				++$x;
			}
		}

		$info .= '	</tr>
';
	}

	$info .= '</table>';
	return $info;
}

/*
 * asb_build_sidebox_info()
 *
 * @param - $sidebox Sidebox type object xD
 * @param - $wrap specifies whether to produce the <div> or just the contents
 * @param - $ajax specifies whether to produce the delete link or not
 * @return: (string) the side box <div>
 */
function asb_build_sidebox_info($sidebox, $wrap = true, $ajax = false)
{
	// must be a valid object
	if($sidebox instanceof Sidebox == false)
	{
		return false;
	}

	global $html, $scripts, $all_scripts, $lang;

	$title = $sidebox->get('title');
	$id = $sidebox->get('id');
	$pos = $sidebox->get('position');
	$module = $sidebox->get('box_type');

	// visibility table
	$visibility = '<span class="custom info">' . asb_build_permissions_table($id) . '</span>';

	// edit link
	$edit_link = $html->url(array("action" => 'edit_box', "id" => $id, "addon" => $module, "pos" => $pos));
	$edit_icon = <<<EOF
<a href="{$edit_link}" class="info_icon" id="edit_sidebox_{$id}" title="{$lang->asb_edit}"><img src="../inc/plugins/asb/images/edit.png" height="18" width="18" alt="{$lang->asb_edit}"/></a>
EOF;

	// delete link (only used if JS is disabled)
	if(!$ajax)
	{
		$delete_link = $html->url(array("action" => 'delete_box', "id" => $id));
		$delete_icon = "<a href=\"{$delete_link}\" class=\"del_icon\" title=\"{$lang->asb_delete}\"><img src=\"../inc/plugins/asb/images/delete.png\" height=\"18\" width=\"18\" alt=\"{$lang->asb_delete}\"/></a>";
	}

	// the content
	$box = <<<EOF
<span class="tooltip"><img class="info_icon" src="../inc/plugins/asb/images/visibility.png" alt="Information" height="18" width="18"/>{$visibility}</span>{$edit_icon}{$delete_icon}{$title}
EOF;

	// the <div> (if applicable)
	if($wrap)
	{
		$box = <<<EOF
<div id="sidebox_{$id}" class="sidebox">{$box}</div>

EOF;
	}

	// return the content (which will either be stored in a string and displayed by asb_main() or will be stored directly in the <div> when called from AJAX
	return $box;
}

/*
 * asb_cache_has_changed()
 *
 * set the flag so the cache is rebuilt new run
 *
 * @return: n/a
 */
function asb_cache_has_changed()
{
	global $cache;

	$asb = $cache->read('asb');
	$asb['has_changed'] = true;
	$cache->update('asb', $asb);
}

/*
 * asb_detect_script_info()
 *
 * searches for hooks, templates and actions and returns a
 * keyed array of select box HTML for any that are found
 *
 * @param - $filename - (string) the file to check
 * @return: (array) script component information
 */
function asb_detect_script_info($filename)
{
	global $lang;

	// check all the info
	if(strlen(trim($filename)) == 0)
	{
		return false;
	}

	$full_path = '../' . trim($filename);
	if(!file_exists($full_path))
	{
		return false;
	}

	$contents = @file_get_contents($full_path);
	if(!$contents)
	{
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
	foreach(array('hook', 'template', 'action') as $key)
	{
		$array_name = "{$key}s";
		$$array_name = array();

		// find any references to the current object
		preg_match_all($info[$key]['pattern'], $contents, $matches, PREG_SET_ORDER);
		foreach($matches as $match)
		{
			// no duplicates and if there is a filter check it
			if(!in_array($match[1], $$array_name) && (strlen(${$array_name}['filter'] == 0 || strpos($match[1], ${$array_name}['filter']) === false)))
			{
				${$array_name}[$match[1]] = $match[1];
			}
		}

		// anything to show?
		if(!empty($$array_name))
		{
			// sort the results, preserving keys
			ksort($$array_name);

			// make none = '' the first entry
			$$array_name = array_reverse($$array_name);
			${$array_name}[] = 'none';
			$$array_name = array_reverse($$array_name);

			// store the HTML select box
			$return_array[$array_name] = '<span style="font-weight: bold;">' . $lang->asb_detected . ' ' . $info[$key]['plural'] . ':</span><br />' . $form->generate_select_box("{$array_name}_options", $$array_name, '', array("id" => "{$key}_selector")) . '<br /><br />';
		}
	}
	return $return_array;
}

/*
 * asb_legacy_custom_import()
 *
 * imports XML files created with ASB 1.x series
 *
 * @param - $tree - (array) as returned by XMLParser
 * @return: n/a
 */
function asb_legacy_custom_import($tree)
{
	global $lang;

	if(!is_array($tree['adv_sidebox']['custom_sidebox']) || empty($tree['adv_sidebox']['custom_sidebox']))
	{
		return $lang->asb_custom_import_file_corrupted;
	}

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
		$input_array['content'] = trim(base64_decode($input_array['content']));
		$input_array['title'] = $input_array['name'];
		return $input_array;
	}

	$error = $lang->asb_custom_import_file_empty;
	if($input_array['content'])
	{
		$error = $lang->asb_custom_import_file_corrupted;
	}
	return $error;
}

/*
 * asb_build_filter_selector()
 *
 * build links for ACP Manage Side Boxes screen
 *
 * @param - $filter is a string containing the script to show or 'all_scripts' to avoid filtering altogether
 * @return: (string) the form HTML
 */
function asb_build_filter_selector($filter)
{
	global $all_scripts;

	// if there are active scripts . . .
	if(!is_array($all_scripts) || empty($all_scripts))
	{
		return;
	}

	global $lang, $html;
	$options = array_merge(array("" => 'no filter'), $all_scripts);
	$form = new Form($html->url(), 'post', 'script_filter', 0, 'script_filter');
	echo($form->generate_select_box('page', $options, $filter));
	echo($form->generate_submit_button('Filter', array('name' => 'filter')));
	return $form->end();
}

/*
 * asb_build_setting()
 *
 * creates a single setting from an associative array
 *
 * @param - $this_form is a valid object of class DefaultForm
 * @param - $this_form_container is a valid object of class DefaultFormContainer
 * @param - $setting is an associative array for the settings properties
 * @return: n/a
 */
function asb_build_setting($this_form, $this_form_container, $setting)
{
	// create each element with unique id and name properties
	$options = "";
	$type = explode("\n", $setting['optionscode']);
    $type = array_map('trim', $type);
	$element_name = "{$setting['name']}";
	$element_id = "setting_{$setting['name']}";

	// prepare labels
	$this_label = '<strong>' . htmlspecialchars_uni($setting['title']) . '</strong>';
	$this_desc = '<i>' . $setting['description'] . '</i>';

	// sort by type
	if($type[0] == "text" || $type[0] == "")
	{
		$this_form_container->output_row($this_label, $this_desc, $this_form->generate_text_box($element_name, $setting['value'], array('id' => $element_id)), $element_name, array("id" => $element_id));
	}
	else if($type[0] == "textarea")
	{
		$this_form_container->output_row($this_label, $this_desc, $this_form->generate_text_area($element_name, $setting['value'], array('id' => $element_id)), $element_name, array('id' => $element_id));
	}
	else if($type[0] == "yesno")
	{
		$this_form_container->output_row($this_label, $this_desc, $this_form->generate_yes_no_radio($element_name, $setting['value'], true, array('id' => $element_id.'_yes', 'class' => $element_id), array('id' => $element_id.'_no', 'class' => $element_id)), $element_name, array('id' => $element_id));
	}
	else if($type[0] == "onoff")
	{
		$this_form_container->output_row($this_label, $this_desc, $this_form->generate_on_off_radio($element_name, $setting['value'], true, array('id' => $element_id.'_on', 'class' => $element_id), array('id' => $element_id.'_off', 'class' => $element_id)), $element_name, array('id' => $element_id));
	}
	else if($type[0] == "language")
	{
		$languages = $lang->get_languages();
		$this_form_container->output_row($this_label, $this_desc, $this_form->generate_select_box($element_name, $languages, $setting['value'], array('id' => $element_id)), $element_name, array('id' => $element_id));
	}
	else if($type[0] == "adminlanguage")
	{
		$languages = $lang->get_languages(1);
		$this_form_container->output_row($this_label, $this_desc, $this_form->generate_select_box($element_name, $languages, $setting['value'], array('id' => $element_id)), $element_name, array('id' => $element_id));
	}
	else if($type[0] == "passwordbox")
	{
		$this_form_container->output_row($this_label, $this_desc, $this_form->generate_password_box($element_name, $setting['value'], array('id' => $element_id)), $element_name, array('id' => $element_id));
	}
	else if($type[0] == "php")
	{
		$setting['optionscode'] = substr($setting['optionscode'], 3);
		eval("\$setting_code = \"" . $setting['optionscode'] . "\";");
	}
	else
	{
		for($i=0; $i < count($type); $i++)
		{
			$optionsexp = explode("=", $type[$i]);
			if(!$optionsexp[1])
			{
				continue;
			}

			if($type[0] == "select")
			{
				$option_list[$optionsexp[0]] = htmlspecialchars_uni($optionsexp[1]);
			}
			else if($type[0] == "radio")
			{
				if($setting['value'] == $optionsexp[0])
				{
					$option_list[$i] = $this_form->generate_radio_button($element_name, $optionsexp[0], htmlspecialchars_uni($optionsexp[1]), array('id' => $element_id.'_'.$i, "checked" => 1, 'class' => $element_id));
				}
				else
				{
					$option_list[$i] = $this_form->generate_radio_button($element_name, $optionsexp[0], htmlspecialchars_uni($optionsexp[1]), array('id' => $element_id.'_'.$i, 'class' => $element_id));
				}
			}
			else if($type[0] == "checkbox")
			{
				if($setting['value'] == $optionsexp[0])
				{
					$option_list[$i] = $this_form->generate_check_box($element_name, $optionsexp[0], htmlspecialchars_uni($optionsexp[1]), array('id' => $element_id.'_'.$i, "checked" => 1, 'class' => $element_id));
				}
				else
				{
					$option_list[$i] = $this_form->generate_check_box($element_name, $optionsexp[0], htmlspecialchars_uni($optionsexp[1]), array('id' => $element_id.'_'.$i, 'class' => $element_id));
				}
			}
		}
		if($type[0] == "select")
		{
			$this_form_container->output_row($this_label, $this_desc, $this_form->generate_select_box($element_name, $option_list, $setting['value'], array('id' => $element_id)), $element_name, array('id' => $element_id));
		}
		else
		{
			$setting_code = implode("<br />", $option_list);
		}
	}

	if($setting_code)
	{
		$this_form_container->output_row($this_label, $this_desc, $setting_code, '', array(), array('id' => 'row_' . $element_id));
	}
}

?>
