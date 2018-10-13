<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * functions for the forum-side
 */

/**
 * avoid wasted execution by determining when and if code is necessary
 *
 * @return bool success/fail
 */
function asb_do_checks()
{
	global $mybb, $theme;

	// if the EXCLUDE list isn't empty and this theme is listed . . .
	$exclude_list = asb_get_excluded_themes();
	if ($exclude_list &&
		in_array($theme['tid'], $exclude_list)) {
		// no side boxes for you
		return false;
	}

	/*
	 * if the current user is not a guest, admin has allowed disabling side box
	 * display and the user has chosen to do so then do not display
	 */
	if ($mybb->settings['asb_allow_user_disable'] &&
		$mybb->user['uid'] != 0 &&
		$mybb->user['show_sidebox'] == 0) {
		return false;
	}

	/*
	 * if this is a mobile device, and admin has
	 * disabled side boxes for mobile...
	 *
	 * credit: http://stackoverflow.com/users/1304523/justin-docanto
	 */
	if ($mybb->settings['asb_disable_for_mobile'] &&
		preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER['HTTP_USER_AGENT'])) {
		return false;
	}

	return true;
}

/**
 * get the tids of any excluded themes
 *
 * @return array|bool excluded themes or false
 */
function asb_get_excluded_themes($sql = false)
{
	global $mybb;

	$retval = unserialize($mybb->settings['asb_exclude_theme']);
	if (!is_array($retval) ||
		empty($retval)) {
		$retval = false;
	}

	if ($sql) {
		if ($retval) {
			$retval = ' AND pid NOT IN(' . implode(',', $retval) . ')';
		} else {
			$retval = '';
		}
	}
	return $retval;
}

/**
 * add all the parts of the script to build a unique name
 *
 * @param  array script environment info
 * @return string filename marked up for asb
 */
function asb_build_script_filename($this_script = '')
{
	if ($this_script instanceof ScriptInfo) {
		$this_script = $this_script->get('data');
	}

	// no info means use the MyBB values
	if (!is_array($this_script) ||
		empty($this_script)) {
		global $mybb;
		$this_script = array(
			'filename' => THIS_SCRIPT,
			'action' => $mybb->input['action'],
			'page' => $mybb->input['page']
		);
	}

	$this_script = array_map('trim', $this_script);

	// if there is nothing to work with . . .
	if (!$this_script['filename']) {
		return;
	}

	// build each piece
	$filename = $this_script['filename'];
	foreach (array('action', 'page') as $key) {
		if (!$this_script[$key]) {
			continue;
		}
		$filename .= "&{$key}={$this_script[$key]}";
	}
	return $filename;
}

/**
 * get the correct cached script info using the script parameters
 *
 * @param  array asb cache data
 * @param  bool true indicates that side boxes and templates
 * 	should be loaded along with the other info
 * @return array script info
 */
function asb_get_this_script($asb, $get_all = false)
{
	global $mybb;

	if (is_array($asb['scripts'][THIS_SCRIPT]) &&
		!empty($asb['scripts'][THIS_SCRIPT])) {
		$return_array = $asb['scripts'][THIS_SCRIPT];
	}

	foreach (array('action', 'page') as $key) {
		$mybb->input[$key] = trim($mybb->input[$key]);
		if (!$mybb->input[$key]) {
			continue;
		}

		$filename = THIS_SCRIPT . "&{$key}={$mybb->input[$key]}";
		if (!is_array($asb['scripts'][$filename]) ||
			empty($asb['scripts'][$filename])) {
			continue;
		}
		$return_array = $asb['scripts'][$filename];
	}

	if (empty($return_array) ||
		!is_array($return_array)) {
		return;
	}

	// merge any globally visible (script-wise) side boxes with this script
	$return_array['template_vars'] = array_merge((array) $asb['scripts']['global']['template_vars'], (array) $return_array['template_vars']);
	$return_array['extra_scripts'] = (array) $asb['scripts']['global']['extra_scripts'] + (array) $return_array['extra_scripts'];
	$return_array['js'] = (array) $asb['scripts']['global']['js'] + (array) $return_array['js'];

	// the template handler does not need side boxes and templates
	if (!$get_all) {
		return $return_array;
	}

	// asb_start() and asb_initialize() do
	$return_array['sideboxes'][0] = asb_merge_sidebox_list($asb, (array) $asb['scripts']['global']['sideboxes'][0], (array) $return_array['sideboxes'][0]);
	$return_array['sideboxes'][1] = asb_merge_sidebox_list($asb, (array) $asb['scripts']['global']['sideboxes'][1], (array) $return_array['sideboxes'][1]);
	$return_array['templates'] = array_merge((array) $asb['scripts']['global']['templates'], (array) $return_array['templates']);
	return $return_array;
}

/**
 * merge global and script specific side box lists while maintaining display order
 *
 * @param  array asb cache data
 * @param  array two or more arrays of side box ids => module names
 * @return array an array with the merged and sorted arrays
 */
function asb_merge_sidebox_list($asb)
{
	// allow for variable amount of arguments
	$args = func_get_args();

	// if there aren't at least two arrays to merge . . .
	if (count($args) <= 2) {
		// return the single array if it exists
		if ($args[1]) {
			return $args[1];
		}
		// or an empty array if all else fails
		return array();
	}

	// remove the cache data from the arg list
	array_shift($args);

	// merge all the passed arrays
	$merged_array = array();
	foreach ($args as $sideboxes) {
		foreach ($sideboxes as $sidebox => $module) {
			$merged_array[$sidebox] = $module;
		}
	}

	// now sort them according to the original side box's display order
	$return_array = array();
	foreach ($asb['sideboxes'] as $sidebox => $module) {
		if (isset($merged_array[$sidebox])) {
			$return_array[$sidebox] = $module['box_type'];
		}
	}
	return $return_array;
}

/**
 * standard check of all user groups against an allowable list
 *
 * @param  array allowed groups
 * @return bool allowed/not
 */
function asb_check_user_permissions($good_groups)
{
	// no groups = all groups says wildy
	if (empty($good_groups)) {
		return true;
	}

	// array-ify the list if necessary
	if (!is_array($good_groups)) {
		$good_groups = explode(',', $good_groups);
	}

	global $mybb;
	if ($mybb->user['uid'] == 0) {
		// guests don't require as much work ;-)
		return in_array(0, $good_groups);
	}

	// get all the user's groups in one array
	$users_groups = array($mybb->user['usergroup']);
	if ($mybb->user['additionalgroups']) {
		$adtl_groups = explode(',', $mybb->user['additionalgroups']);
		$users_groups = array_merge($users_groups, $adtl_groups);
	}

	/*
	 * if any overlaps occur then they will be in $test_array,
	 * empty returns true/false so !empty = true for allow and false for disallow
	 */
	$test_array = array_intersect($users_groups, $good_groups);
	return !empty($test_array);
}

/**
 * use the sidebox info to produce its template
 *
 * @param  SideboxObject|array side box
 * @return string|bool html or false
 */
function asb_build_sidebox_content($this_box)
{
	// need good info
	if ($this_box instanceof SideboxObject) {
		$data = $this_box->get('data');
	} else if (is_array($this_box) &&
		!empty($this_box)) {
		$data = $this_box;
	} else {
		return false;
	}

	// build our info
	foreach (array('id', 'box_type', 'wrap_content', 'title', 'title_link') as $key) {
		if (isset($data[$key])) {
			$$key = $data[$key];
		}
	}

	// build the template variable
	$content = '{$' . "{$box_type}_{$id}" . '}';

	// if we are building header and expander . . .
	if ($wrap_content) {
		global $mybb, $templates, $theme, $collapsed;

		// element info
		$sidebox['expcolimage_id'] = "{$box_type}_{$id}_img";
		$sidebox['expdisplay_id'] = "{$box_type}_{$id}_e";
		$sidebox['name'] = "{$id}_{$box_type}_" . TIME_NOW;
		$sidebox['class'] = $sidebox['id'] = "{$box_type}_main_{$id}";
		$sidebox['content'] = $content;
		$sidebox['title'] = $title;
		if ($title_link) {
			$sidebox['title'] = <<<EOF
<a href="{$title_link}">{$title}</a>
EOF;
		}

		if ($mybb->settings['asb_show_expanders']) {
			// check if this side box is either expanded or collapsed and hide it as necessary.
			$expdisplay = '';
			$collapsed_name = "{$box_type}_{$id}_c";
			if (isset($collapsed[$collapsed_name]) &&
				$collapsed[$collapsed_name] == 'display: show;') {
				$expcolimage = 'collapse_collapsed.png';
				$expdisplay = 'display: none;';
				$expaltext = '[+]';
			} else {
				$expcolimage = 'collapse.png';
				$expaltext = '[-]';
			}
			eval("\$expander = \"" . $templates->get('asb_expander') . "\";");
		}
		eval("\$content = \"" . $templates->get('asb_wrapped_sidebox') . "\";");
	}

	// if there is anything to return
	if ($content) {
		// give it up
		return <<<EOF

		<!-- start side box #{$id} - box type {$box_type} -->
		{$content}
		<!-- end side box #{$id} - box type {$box_type} -->
EOF;
	}

	// otherwise return failure
	return false;
}

/**
 * retrieve any detected modules
 *
 * @return array SideboxExternalModule
 */
function asb_get_all_modules()
{
	$return_array = array();

	// load all detected modules
	foreach (new DirectoryIterator(ASB_MODULES_DIR) as $file) {
		if (!$file->isFile() ||
			$file->isDot() ||
			$file->isDir()) {
			continue;
		}

		$extension = pathinfo($file->getFilename(), PATHINFO_EXTENSION);

		// only PHP files
		if ($extension != 'php') {
			continue;
		}

		// extract the baseName from the module file name
		$filename = $file->getFilename();
		$module = substr($filename, 0, strlen($filename) - 4);

		// attempt to load the module
		$return_array[$module] = new SideboxExternalModule($module);
	}
	return $return_array;
}

/**
 * retrieve all custom boxes
 *
 * @return array CustomSidebox
 */
function asb_get_all_custom()
{
	global $db;

	// get any custom boxes
	$return_array = array();

	$query = $db->simple_select('asb_custom_sideboxes');
	if ($db->num_rows($query) > 0) {
		while ($data = $db->fetch_array($query)) {
			$return_array['asb_custom_' . $data['id']] = new CustomSidebox($data);
		}
	}
	return $return_array;
}

/**
 * retrieve all side boxes
 *
 * @param  string script filter
 * @return array SideboxObject
 */
function asb_get_all_sideboxes($good_script = '')
{
	global $db;

	// get any side boxes
	$return_array = array();

	$query = $db->simple_select('asb_sideboxes', '*', '', array('order_by' => 'display_order', 'order_dir' => 'ASC'));
	if ($db->num_rows($query) > 0) {
		while ($data = $db->fetch_array($query)) {
			$sidebox = new SideboxObject($data);

			if ($good_script) {
				$scripts = $sidebox->get('scripts');
				if (!empty($scripts) &&
					!in_array($good_script, $scripts)) {
					continue;
				}
			}

			// create the object and build basic data
			$return_array[$data['id']] = $sidebox;
		}
	}
	return $return_array;
}

/**
 * retrieve all script definitions
 *
 * @return array script data
 */
function asb_get_all_scripts()
{
	global $db;

	// get all the active scripts' info
	$return_array = array();

	$query = $db->simple_select('asb_script_info', '*', "active='1'");
	if ($db->num_rows($query) > 0) {
		while ($this_script = $db->fetch_array($query)) {
			$filename = asb_build_script_filename($this_script);
			$return_array[$filename] = $this_script;
		}
	}
	return $return_array;
}

/**
 * rebuilds the theme exclude list ACP setting
 *
 * @return string|bool html or false
 */
function asb_get_all_themes($full = false)
{
	global $db;

	static $themeList;

	if (!is_array($themeList)) {
		if ($full != true) {
			$excluded_themes = asb_get_excluded_themes(true);
		}

		// get all the themes that are not MasterStyles
		$query = $db->simple_select('themes', 'tid, name', "NOT pid='0'{$excluded_themes}");

		$themeList = array();
		while ($thisTheme = $db->fetch_array($query)) {
			$themeList[$thisTheme['tid']] = $thisTheme['name'];
		}
	}

	return $themeList;
}

?>
