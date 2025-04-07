<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * https://www.rantcentralforums.com
 *
 * this file contains the functions used in ACP
 */

/**
 * produces a link to a particular page in the plugin help system (with icon) specified by topic
 *
 * @param  string topic keyword
 * @return string html
 */
function asbBuildHelpLink($topic='')
{
	global $mybb, $lang, $html, $cp_style;

	if (!$topic) {
		$topic = 'manage_sideboxes';
	}

	$topics = array(
		'manage_sideboxes' => 'Managing-Sideboxes',
		'edit_box' => 'Add-New-Side-Box',
		'edit_custom' => 'Add-New-Custom-Box',
		'custom' => 'Custom-Boxes',
		'edit_scripts' => 'Add-New-Script-Definition',
		'manage_scripts' => 'Managing-Scripts',
		'addons' => 'Managing-Modules',
	);

	$url = 'https://github.com/WildcardSearch/Advanced-Sidebox/wiki/Help';
	if (strlen($topic) > 1) {
		$url .= '-'.$topics[$topic];
	}

	return $html->link($url, $lang->asb_help, array('target' => '_blank', 'style' => 'font-weight: bold;', 'icon' => "styles/{$cp_style}/images/asb/help.png", 'title' => $lang->asb_help), array('alt' => '?', 'title' => $lang->asb_help, 'style' => 'margin-bottom: -3px;'));
}

/**
 * produces a link to the plugin settings with icon
 *
 * @return string html
 */
function asbBuildSettingsMenuLink()
{
	global $mybb, $lang, $html, $cp_style;

	$settingsUrl = asbBuildSettingsUrl(asbGetSettingsGroup());
	$settingsLink = $html->link(
		$settingsUrl,
		$lang->asb_plugin_settings,
		array(
			'icon' => "styles/{$cp_style}/images/asb/settings.png",
			'style' => 'font-weight: bold;',
			'title' => $lang->asb_plugin_settings,
		),
		array(
			'alt' => 'S',
			'style' => 'margin-bottom: -3px;',
		)
	);

	return $settingsLink;
}

/**
 * Output ACP tabs for our pages
 *
 * @param  string current tab
 * @return void
 */
function asbOutputTabs($current)
{
	global $page, $lang, $mybb, $html;

	// set up tabs
	$tabs['asb'] = array(
		'title' => $lang->asb_manage_sideboxes,
		'link' => $html->url(),
		'description' => $lang->asb_manage_sideboxes_desc,
	);

	$tabs['asb_custom'] = array(
		'title' => $lang->asb_custom_boxes,
		'link' => $html->url(array('action' => 'custom_boxes')),
		'description' => $lang->asb_custom_boxes_desc,
	);

	if (in_array($current, array('asb_add_custom', 'asb_custom'))) {
		$tabs['asb_add_custom'] = array(
			'title' => $lang->asb_add_custom,
			'link' => $html->url(array('action' => 'custom_boxes', 'mode' => 'edit')),
			'description' => $lang->asb_add_custom_desc,
		);
	}

	$tabs['asb_scripts'] = array(
		'title' => $lang->asb_manage_scripts,
		'link' => $html->url(array('action' => 'manage_scripts')),
		'description' => $lang->asb_manage_scripts_desc,
	);

	if ($current == 'asb_view_scripts') {
		$tabs['asb_view_scripts'] = array(
			'title' => $lang->asb_view_scripts,
			'link' => $html->url(array('action' => 'view_scripts')),
			'description' => $lang->asb_view_scripts_desc,
		);
	}

	if (in_array($current, array('asb_edit_script', 'asb_scripts'))) {
		$tabs['asb_edit_script'] = array(
			'title' => $lang->asb_edit_script,
			'link' => $html->url(array('action' => 'manage_scripts', 'mode' => 'edit')),
			'description' => $lang->asb_edit_script_desc,
		);
	}

	$tabs['asb_modules'] = array(
		'title' => $lang->asb_manage_modules,
		'link' => $html->url(array('action' => 'manage_modules')),
		'description' => $lang->asb_manage_modules_desc,
	);

	$page->output_nav_tabs($tabs, $current);
}

/**
 * output ACP footers for our pages
 *
 * @param  string current page
 * @return void
 */
function asbOutputFooter($pageKey)
{
    global $page;

	echo(asbBuildFooterMenu($pageKey));
	$page->output_footer();
}

/**
 * build a footer menu specific to each page
 *
 * @param  string topic key
 * @return string html
 */
function asbBuildFooterMenu($pageKey='')
{
	global $mybb;

	if (!$pageKey) {
		$pageKey = 'manage_sideboxes';
	}

	$helpLink = '&nbsp;'.asbBuildHelpLink($pageKey);
	$settingsLink = '&nbsp;'.asbBuildSettingsMenuLink();

	if ($pageKey == 'manage_sideboxes') {
		$filterSelector = asbBuildFilterSelector($mybb->input['page']);
	}

	return <<<EOF

<div class="asb_label">
{$filterSelector}
	{$settingsLink}
	{$helpLink}
</div>

EOF;
}

/**
 *  build a popup with a table of side box permission info
 *
 * @param  int id
 * @return string html
 */
function asbBuildPermissionsTable($sidebox)
{
	global $lang, $allScripts;

	if ($sidebox instanceof SideboxObject == false ||
		!$sidebox->isValid()) {
		return $lang->asb_invalid_sidebox;
	}

	if (!$allScripts) {
		return $lang->asb_no_active_scripts;
	}

	$visibility_rows = asbBuildVisibilityRows($sidebox, $group_count, $global);
	$themeList = asbBuildThemeVisibilityList($sidebox, $group_count + 1, $global);

	return <<<EOF

								<table width="100%" class="box_info">{$visibility_rows}{$themeList}
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
function asbBuildVisibilityRows($sidebox, &$group_count, &$global)
{
	global $db, $lang, $allScripts;

	static $options;

	if (!is_array($allScripts) ||
		empty($allScripts)) {
		return $lang->asb_no_active_scripts;
	}

	if (!is_array($options)) {
		// prepare options for which groups
		$options = array($lang->asb_guests);

		// look for all groups except Super Admins
		$query = $db->simple_select('usergroups', 'gid, title', "gid != '1'", array('order_by' => 'gid'));
		while ($usergroup = $db->fetch_array($query)) {
			// store the titles by group id
			$options[(int)$usergroup['gid']] = $usergroup['title'];
		}
	}

	$group_count = $all_group_count = count($options);

	$groups = $sidebox->get('groups');
	$scripts = $sidebox->get('scripts');

	if (empty($scripts)) {
		if (empty($groups)) {
			$global = true;
			return <<<EOF

									<tr><td>{$lang->asb_globally_visible}</td></tr>
EOF;
		} elseif (isset($groups[0]) && strlen($groups[0]) == 0) {
			return <<<EOF

									<tr><td>{$lang->asb_all_scripts_deactivated}</td></tr>
EOF;
		} else {
			$scripts = $allScripts;
		}
	} elseif (isset($scripts[0]) &&
		strlen($scripts[0]) == 0) {
		return <<<EOF

									<tr><td>{$lang->asb_all_scripts_deactivated}</td></tr>
EOF;
	}

	$group_headers = '';
	foreach ($options as $gid => $title) {
		$group_headers .= <<<EOF

										<td title="{$title}" class="group_header">{$gid}</td>
EOF;
	}

	$script_rows = '';
	foreach ($allScripts as $script => $script_title) {
		$script_title_full = '';
		if (strlen($script_title) > 15) {
			$script_title_full = $script_title;
			$script_title = substr($script_title, 0, 15).'...';
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
function asbBuildThemeVisibilityList($sidebox, $colspan, $global)
{
	global $lang;

	$themes = asbGetAllThemes();
	$good_themes = $sidebox->get('themes');

	if (!$themes) {
		return false;
	}

	if (!$good_themes ||
		count($good_themes) == count($themes)) {
		$themeList = $lang->asb_visibile_for_all_themes;
		if ($global) {
			return '';
		}
	} else {
		$themeList = '';
		foreach ($themes as $tid => $name) {
			if ($good_themes &&
				!in_array($tid, $good_themes)) {
				$themeList .= <<<EOF
{$sep}<s>{$name}</s>
EOF;
			} else {
				$themeList .= <<<EOF
{$sep}{$name}
EOF;
			}
			$sep = ', ';
		}
	}

	return <<<EOF

									<tr>
										<td colspan="{$colspan}">{$themeList}</td>
									</tr>
EOF;
}

/**
 * @param  SideboxObject
 * @param  bool wrap in div?
 * @param  bool produce delete link?
 * @return string html
 */
function asbBuildSideBoxInfo($sidebox, $wrap=true, $ajax=false)
{
	// must be a valid object
	if ($sidebox instanceof SideboxObject == false ||
		!$sidebox->isValid()) {
		return false;
	}

	global $html, $scripts, $allScripts, $lang, $cp_style;

	$title = $sidebox->get('title');
	$id = $sidebox->get('id');
	$pos = $sidebox->get('position');
	$module = $sidebox->get('box_type');

	// visibility table
	$visibility = '<span class="custom info">'.asbBuildPermissionsTable($sidebox).'</span>';

	// edit link
	$editUrl = $html->url(array('action' => 'edit_box', 'id' => $id, 'addon' => $module, 'pos' => $pos));
	$editIcon = <<<EOF
<a href="{$editUrl}" class="info_icon" id="edit_sidebox_{$id}" title="{$lang->asb_edit}"><img src="styles/{$cp_style}/images/asb/edit.png" height="18" width="18" alt="{$lang->asb_edit}"/></a>
EOF;

	// delete link (only used if JS is disabled)
	if (!$ajax) {
		$deleteUrl = $html->url(array('action' => 'delete_box', 'id' => $id));
		$deleteIcon = <<<EOF
<a href="{$deleteUrl}" class="del_icon" title="{$lang->asb_delete}"><img src="styles/{$cp_style}/images/asb/delete.png" height="18" width="18" alt="{$lang->asb_delete}"/></a>
EOF;
	}

	// the content
	$box = <<<EOF

							<span class="tooltip"><img class="info_icon" src="styles/{$cp_style}/images/asb/visibility.png" alt="Information" height="18" width="18"/>{$visibility}</span>{$editIcon}{$deleteIcon}<span class="asb-sidebox-title">{$title}</span>
EOF;

	// the <div> (if applicable)
	if ($wrap) {
		$box = <<<EOF

						<div id="sidebox_{$id}" class="sidebox sortable">{$box}
						</div>
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
function asbCacheHasChanged()
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
function asbDetectScriptInfo($filename, $selected=array())
{
	global $lang;

	// check all the info
	if (strlen(trim($filename)) == 0) {
		return array(
			'error' => 1,
		);
	}

	$fullPath = '../'.trim($filename);
	if (!file_exists($fullPath)) {
		return array(
			'error' => 2,
		);
	}

	$contents = @file_get_contents($fullPath);
	if (!$contents) {
		return array(
			'error' => 3,
		);
	}

	// build the object info
	$info = array(
		'hook' => array(
			'pattern' => "#\\\$plugins->run_hooks\([\"|'|&quot;]([\w|_]*)[\"|'|&quot;](.*?)\)#i",
			'filter' => '_do_',
			'plural' => $lang->asb_hooks,
		),
		'template' => array(
			'pattern' => "#\\\$templates->get\([\"|'|&quot;]([\w|_]*)[\"|'|&quot;](.*?)\)#i",
			'filter' => '',
			'plural' => $lang->asb_templates,
		),
		'action' => array(
			'pattern' => "#\\\$mybb->input\[[\"|'|&quot;]action[\"|'|&quot;]\] == [\"|'|&quot;]([\w|_]*)[\"|'|&quot;]#i",
			'filter' => '',
			'plural' => $lang->asb_actions,
		),
	);

	$form = new Form('', '', '', 0, '', true);
	foreach (array('hook', 'template', 'action') as $key) {
		$element = "{$key}s";
		$$element = array();

		// find any references to the current object
		preg_match_all($info[$key]['pattern'], $contents, $matches, PREG_SET_ORDER);
		foreach ($matches as $match) {
			// no duplicates and if there is a filter check it
			if (!in_array($match[1], $$element) &&
				(strlen(${$element}['filter'] == 0 ||
				strpos($match[1], ${$element}['filter']) === false))) {
				${$element}[$match[1]] = $match[1];
			}
		}

		// anything to show?
		if (!empty($$element)) {

			// sort the results, preserving keys
			ksort($$element);

			// make none = '' the first entry
			$$element = array_reverse($$element);
			${$element}[] = 'none';
			$$element = array_reverse($$element);

			// store the HTML select box
			$returnArray[$element] = '<span style="font-weight: bold;">'.$lang->asb_detected.' '.$info[$key]['plural'].':</span><br />'.$form->generate_select_box("{$element}_options", $$element, $selected[$key], array('id' => "{$key}_selector")).'<br /><br />';
		} else {
			$varName = "asb_ajax_{$element}";
			$noContent = $lang->sprintf($lang->asb_ajax_nothing_found, $lang->$varName);

			// store the no content message
			$returnArray[$element] = <<<EOF
<span style="font-style: italic;">{$noContent}</span>
EOF;
		}
	}

	return $returnArray;
}

/**
 * build links for ACP Manage Side Boxes screen
 *
 * @param  string script to show or 'all_scripts' to avoid filtering altogether
 * @return string html
 */
function asbBuildFilterSelector($filter)
{
	global $allScripts;

	// if there are active scripts...
	if (!is_array($allScripts) ||
		empty($allScripts)) {
		return;
	}

	global $lang, $html;
	$options = array_merge(array('' => 'no filter'), $allScripts);
	$form = new Form($html->url(), 'post', 'script_filter', 0, 'script_filter');
	echo($form->generate_select_box('page', $options, $filter));
	echo($form->generate_submit_button('Filter', array('name' => 'filter')));
	return $form->end();
}

/**
 * checks if a script definition is already represented, relative to the filename and query parameters
 *
 * @param  array
 * @param  int theme id
 * @return string filename marked up for asb
 */
function asbFindDuplicateScriptByFilename($keys, $tid=0)
{
	global $db;

	if (!is_array($keys) ||
		empty($keys) ||
		!isset($keys['filename']) ||
		empty($keys['filename'])) {
		return false;
	}

	$tid = (int) $tid;

	$where = "tid='{$tid}'";
	foreach ($keys as $key => $val) {
		$val = $db->escape_string($val);

		if ($key && $val) {
			$where .= " AND {$key}='{$val}'";
		}
	}

	$query = $db->simple_select('asb_script_info', 'id', $where);
	if ($db->num_rows($query) == 0) {
		return false;
	}

	return (int) $db->fetch_field($query, 'id');
}