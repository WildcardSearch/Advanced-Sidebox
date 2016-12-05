<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * functions for the forum-side
 */

/*
 * avoid wasted execution by determining when and if code is necessary
 *
 * @return bool true on success, false on fail
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
	return true;
}

/*
 * get the tids of any excluded themes
 *
 * @return array the list of excluded themes (bool) false on fail
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

/*
 * retrieve the cache, rebuilding it if necessary
 *
 * @return array the cache data
 */
function asb_get_cache()
{
	global $cache;
	static $asb;

	// if we've already retrieved it (we will do it twice per script)
	// then just return the static copy, otherwise retrieve it
	if (!isset($asb) ||
		empty($asb)) {
		$asb = $cache->read('asb');
	}

	// if the cache has never been built or has been marked as changed
	// then rebuild and store it
	if ((int) $asb['last_run'] == 0 ||
		$asb['has_changed']) {
		asb_build_cache($asb);
		$cache->update('asb', $asb);
	}

	// returned the cached info
	return $asb;
}

/*
 * build all of the relevant info needed to manage side boxes
 *
 * @param array a reference to the asb cache data variable
 * @return void
 */
function asb_build_cache(&$asb)
{
	global $db;

	// fresh start
	$asb['custom'] = $asb['sideboxes'] = $asb['scripts'] = $asb['all_scripts'] = array();

	// update the run time and changed flag before we even start
	$asb['last_run'] = TIME_NOW;
	$asb['has_changed'] = false;

	// all the general side box related objects
	require_once MYBB_ROOT . 'inc/plugins/asb/classes/forum.php';

	// get all the active scripts' info
	$all_scripts = asb_get_all_scripts();

	// no scripts, no work to do
	if (!is_array($all_scripts) ||
		empty($all_scripts)) {
		return;
	}

	// store the script definitions and a master list
	foreach ($all_scripts as $filename => $script) {
		$asb['scripts'][$filename] = $script;
	}
	$asb['all_scripts'] = array_keys($all_scripts);

	// load all detected modules
	$addons = asb_get_all_modules();

	// get any custom boxes
	$custom = asb_get_all_custom();

	// get any sideboxes
	$sideboxes = asb_get_all_sideboxes();

	if (!is_array($sideboxes) ||
		empty($sideboxes)) {
		return;
	}

	foreach ($sideboxes as $sidebox) {
		// build basic data
		$scripts = $sidebox->get('scripts');
		$id = (int) $sidebox->get('id');
		$pos = $sidebox->get('position') ? 1 : 0;
		$asb['sideboxes'][$id] = $sidebox->get('data');
		$module = $sidebox->get('box_type');

		// no scripts == all scripts
		if (empty($scripts)) {
			// add this side box to the 'global' set (to be merged with the current script when applicable)
			$scripts = array('global');
		}

		// for each script in which the side box is used, add a pointer and if it is a custom box, cache its contents
		foreach ($scripts as $filename) {
			// side box from a module?
			if (isset($addons[$module]) &&
				$addons[$module] instanceof Addon_type) {
				// store the module name and all the template vars used
				$asb['scripts'][$filename]['sideboxes'][$pos][$id] = $module;
				$asb['scripts'][$filename]['template_vars'][$id] = "{$module}_{$id}";

				// if there are any templates get their names so we can cache them
				$templates = $addons[$module]->get('templates');
				if (is_array($templates) &&
					!empty($templates)) {
					foreach ($templates as $template) {
						$asb['scripts'][$filename]['templates'][] = $template['title'];
					}
				}

				// AJAX?
				if ($addons[$module]->xmlhttp &&
					$sidebox->has_settings) {
					$settings = $sidebox->get('settings');

					// again, default here is off if anything goes wrong
					if ($settings['xmlhttp_on']) {
						// if all is good add the script building info
						$asb['scripts'][$filename]['extra_scripts'][$id]['position'] = $pos;
						$asb['scripts'][$filename]['extra_scripts'][$id]['module'] = $module;
						$asb['scripts'][$filename]['extra_scripts'][$id]['rate'] = $settings['xmlhttp_on'];
					}
				}

				if ($addons[$module]->has_scripts) {
					foreach ($addons[$module]->get('scripts') as $script) {
						$asb['scripts'][$filename]['js'][$script] = $script;
					}
				}
			// side box from a custom box?
			} else if(isset($custom[$module]) &&
				$custom[$module] instanceof Custom_type) {
				// store the pointer
				$asb['scripts'][$filename]['sideboxes'][$pos][$id] = $module;
				$asb['scripts'][$filename]['template_vars'][$id] = "{$module}_{$id}";

				// and cache the contents
				$asb['custom'][$module] = $custom[$module]->get('data');
			}
		}
	}
}

/*
 * add all the parts of the script to build a unique name
 *
 * @param array an optional array of script environment info
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
			"filename" => THIS_SCRIPT,
			"action" => $mybb->input['action'],
			"page" => $mybb->input['page']
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

/*
 * get the correct cached script info using the script parameters
 *
 * @param array the asb cache data
 * @param bool true indicates that side boxes and templates
 * should be loaded along with the other info
 * @return array information used to present this script's side boxes
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

/*
 * merge global and script specific side box lists while maintaining display order
 *
 * @param array the asb cache data
 * @param array two or more arrays of side box ids => module names
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

/*
 * standard check of all user groups against an allowable list
 *
 * @param array groups allowed to perform the action we are protecting
 * @return bool true if the user is allowed, false if not
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

/*
 * use the sidebox info to produce its template
 *
 * @param Sidebox object or array of side box data
 * @return string HTML side box <div> markup or bool false on error
 */
function asb_build_sidebox_content($this_box)
{
	// need good info
	if ($this_box instanceof Sidebox) {
		$data = $this_box->get('data');
	} else if(is_array($this_box) && !empty($this_box)) {
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

/*
 * retrieve any detected modules
 *
 * @return array Addon_type objects
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

		// extract the base_name from the module file name
		$filename = $file->getFilename();
		$module = substr($filename, 0, strlen($filename) - 4);

		// attempt to load the module
		$return_array[$module] = new Addon_type($module);
	}
	return $return_array;
}

/*
 * retrieve all custom boxes
 *
 * @return array Custom_type objects
 */
function asb_get_all_custom()
{
	global $db;

	// get any custom boxes
	$return_array = array();

	$query = $db->simple_select('asb_custom_sideboxes');
	if ($db->num_rows($query) > 0) {
		while ($data = $db->fetch_array($query)) {
			$return_array['asb_custom_' . $data['id']] = new Custom_type($data);
		}
	}
	return $return_array;
}

/*
 * retrieve all side boxes
 *
 * @param string optional script filter
 * @return array an array of side box objects
 */
function asb_get_all_sideboxes($good_script = '')
{
	global $db;

	// get any side boxes
	$return_array = array();

	$query = $db->simple_select('asb_sideboxes', '*', '', array("order_by" => 'display_order', "order_dir" => 'ASC'));
	if ($db->num_rows($query) > 0) {
		while ($data = $db->fetch_array($query)) {
			$sidebox = new Sidebox($data);

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

/*
 * retrieve all script definitions
 *
 * @return array of script data arrays
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

/*
 * rebuilds the theme exclude list ACP setting
 *
 * @return string the <select> HTML or false on error
 */
function asb_get_all_themes($full = false)
{
	global $db;

	if ($full != true) {
		$excluded_themes = asb_get_excluded_themes(true);
	}

	// get all the themes that are not MasterStyles
	$query = $db->simple_select('themes', 'tid, name', "NOT pid='0'{$excluded_themes}");

	$return_array = array();
	if ($db->num_rows($query) == 0) {
		return $return_array;
	}

	while ($this_theme = $db->fetch_array($query)) {
		$return_array[$this_theme['tid']] = $this_theme['name'];
	}
	return $return_array;
}

?>
