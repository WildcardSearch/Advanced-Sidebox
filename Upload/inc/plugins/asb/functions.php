<?php
/*
 * Plug-in Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2013 WildcardSearch
 * http://www.wildcardsworld.com
 *
 * functions for the forum-side
 */

/*
 * asb_do_checks()
 *
 * avoid wasted execution by determining when and if code is necessary
 */
function asb_do_checks()
{
	global $mybb, $theme;

	// if the EXCLUDE list isn't empty and this theme is listed . . .
	$exclude_list = unserialize($mybb->settings['asb_exclude_theme']);
	if(is_array($exclude_list) && in_array($theme['tid'], $exclude_list))
	{
		// no side boxes for you
		return false;
	}

	/*
	 * if the current user is not a guest and has disabled the side
	 * box display in UCP then do not display the side boxes
	 */
	if($mybb->settings['asb_allow_user_disable'] && $mybb->user['uid'] != 0 && $mybb->user['show_sidebox'] == 0)
	{
		return false;
	}
	return true;
}

/*
 * asb_get_cache()
 *
 * retrieve the cache, rebuilding it if necessary
 */
function asb_get_cache()
{
	global $cache;
	static $asb;

	// if we've already retrieved it (we will do it thrice per script)
	// then just return the static copy, otherwise retrieve it
	if(!isset($asb) || empty($asb))
	{
		$asb = $cache->read('asb');
	}

	// if the cache has never been built or has been marked as changed
	// then rebuild and store it
	if((int) $asb['last_run'] == 0 || $asb['has_changed'])
	{
		asb_build_cache($asb);
		$cache->update('asb', $asb);
	}

	// returned the cached info
	return $asb;
}

/*
 * asb_build_cache(&$asb)
 *
 * build all of the relevant info needed to manage side boxes
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
	if(!is_array($all_scripts) || empty($all_scripts))
	{
		return;
	}

	// store the script definitions and a master list
	foreach($all_scripts as $filename => $script)
	{
		$asb['scripts'][$filename] = $script;
	}
	$asb['all_scripts'] = array_keys($all_scripts);

	// load all detected modules
	$addons = asb_get_all_modules();

	// get any custom boxes
	$custom = asb_get_all_custom();

	// get any sideboxes
	$sideboxes = asb_get_all_sideboxes();

	if(!is_array($sideboxes) || empty($sideboxes))
	{
		return;
	}

	foreach($sideboxes as $sidebox)
	{
		// build basic data
		$scripts = $sidebox->get('scripts');
		$id = (int) $sidebox->get('id');
		$pos = $sidebox->get('position') ? 1 : 0;
		$asb['sideboxes'][$id] = $sidebox->get('data');
		$module = $sidebox->get('box_type');

		// no scripts == all scripts
		if(empty($scripts))
		{
			// add this side box to the 'global' set (to be merged with the current script when applicable)
			$scripts = array('global');
		}

		// for each script in which the side box is used, add a pointer and if it is a custom box, cache its contents
		foreach($scripts as $filename)
		{
			// side box from a module?
			if(isset($addons[$module]) && $addons[$module] instanceof Addon_type)
			{
				// store the module name and all the template vars used
				$asb['scripts'][$filename]['sideboxes'][$pos][$id] = $module;
				$asb['scripts'][$filename]['template_vars'][$id] = "{$module}_{$id}";

				// if there are any templates get their names so we can cache them
				$templates = $addons[$module]->get('templates');
				if(is_array($templates) && !empty($templates))
				{
					foreach($templates as $template)
					{
						$asb['scripts'][$filename]['templates'][] = $template['title'];
					}
				}

				// AJAX?
				if($addons[$module]->xmlhttp && $sidebox->has_settings)
				{
					$settings = $sidebox->get('settings');

					// again, default here is off if anything goes wrong
					if($settings['xmlhttp_on']['value'])
					{
						// if all is good add the script building info
						$asb['scripts'][$filename]['extra_scripts'][$module]['position'] = $pos;
						$asb['scripts'][$filename]['extra_scripts'][$module]['id'] = $id;
						$asb['scripts'][$filename]['extra_scripts'][$module]['rate'] = $settings['xmlhttp_on']['value'];
					}
				}
			}
			// side box from a custom box?
			else if(isset($custom[$module]) && $custom[$module] instanceof Custom_type)
			{
				// store the pointer
				$asb['scripts'][$filename]['sideboxes'][$pos][$id] = $module;

				// and cache the contents
				$asb['custom'][$module] = $custom[$module]->get('data');
			}
		}
	}
}

/*
 * asb_build_script_filename($this_script = '')
 *
 * add all the parts of the script to build a unique name
 */
function asb_build_script_filename($this_script = '')
{
	if($this_script instanceof ScriptInfo)
	{
		$this_script = $this_script->get('data');
	}

	// no info means use the MyBB values
	if(!is_array($this_script) || empty($this_script))
	{
		global $mybb;
		$this_script = array
		(
			"filename" => THIS_SCRIPT,
			"action" => $mybb->input['action'],
			"page" => $mybb->input['page']
		);
	}

	// if there is something to work with . . .
	if(!trim($this_script['filename']))
	{
		return;
	}

	// build each piece
	$filename = trim($this_script['filename']);
	if(trim($this_script['action']))
	{
		$filename .= '&action=' . trim($this_script['action']);
	}
	if(trim($this_script['page']))
	{
		$filename .= '&page=' . trim($this_script['page']);
	}
	return $filename;
}

/*
 * asb_get_this_script(&$asb)
 *
 * get the correct cached script info using the script parameters
 */
function asb_get_this_script(&$asb, $get_all = false)
{
	global $mybb;

	if(is_array($asb['scripts'][THIS_SCRIPT]) && !empty($asb['scripts'][THIS_SCRIPT]))
	{
		$return_array = $asb['scripts'][THIS_SCRIPT];
	}
	elseif(isset($mybb->input['action']) && trim($mybb->input['action']))
	{
		if(isset($mybb->input['action']) && trim($mybb->input['action']) && is_array($asb['scripts'][THIS_SCRIPT . "&action={$mybb->input['action']}"]) && !empty($asb['scripts'][THIS_SCRIPT . "&action={$mybb->input['action']}"]))
		{
			$return_array = $asb['scripts'][THIS_SCRIPT . "&action={$mybb->input['action']}"];
		}
	}
	elseif(isset($mybb->input['page']) && trim($mybb->input['page']))
	{
		if(is_array($asb['scripts'][THIS_SCRIPT . "&page={$mybb->input['page']}"]) && !empty($asb['scripts'][THIS_SCRIPT . "&page={$mybb->input['page']}"]))
		{
			$return_array = $asb['scripts'][THIS_SCRIPT . "&page={$mybb->input['page']}"];
		}
	}
	else
	{
		return;
	}

	// merge any globally visible (script-wise) side boxes with this script
	$return_array['template_vars'] = array_merge((array) $asb['scripts']['global']['template_vars'], (array) $return_array['template_vars']);
	$return_array['extra_scripts'] = (array) $asb['scripts']['global']['extra_scripts'] + (array) $return_array['extra_scripts'];

	if($get_all)
	{
		$return_array['sideboxes'][0] = (array) $asb['scripts']['global']['sideboxes'][0] + (array) $return_array['sideboxes'][0];
		$return_array['sideboxes'][1] = (array) $asb['scripts']['global']['sideboxes'][1] + (array) $return_array['sideboxes'][1];
		$return_array['templates'] = array_merge((array) $asb['scripts']['global']['templates'], (array) $return_array['templates']);
	}
	return $return_array;
}

/*
 * asb_check_user_permissions($good_groups)
 *
 * standard check of all user groups against an allowable list
 */
function asb_check_user_permissions($good_groups)
{
	// no groups = all groups says wildy
	if(empty($good_groups))
	{
		return true;
	}

	// array-ify the list if necessary
	if(!is_array($good_groups))
	{
		$good_groups = explode(',', $good_groups);
	}

	global $mybb;
	if($mybb->user['uid'] == 0)
	{
		// guests don't require as much work ;-)
		return in_array(0, $good_groups);
	}

	// get all the user's groups in one array
	$users_groups = array($mybb->user['usergroup']);
	if($mybb->user['additionalgroups'])
	{
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
 * asb_build_sidebox_content($this_box)
 *
 * use the sidebox info to produce its template
 */
function asb_build_sidebox_content($this_box)
{
	// need good info
	if($this_box instanceof Sidebox)
	{
		$data = $this_box->get('data');
	}
	else if(is_array($this_box) && !empty($this_box))
	{
		$data = $this_box;
	}
	else
	{
		return false;
	}

	// build our info
	foreach(array('id', 'box_type', 'wrap_content', 'title') as $key)
	{
		if(isset($data[$key]))
		{
			$$key = $data[$key];
		}
	}

	// build the template variable
	$content = '{$' . "{$box_type}_{$id}" . '}';

	// if we are building header and expander . . .
	if($wrap_content)
	{
		global $mybb, $templates, $theme, $collapsed;

		// element info
		$sidebox['expcolimage_id'] = "{$box_type}_{$id}_img";
		$sidebox['expdisplay_id'] = "{$box_type}_{$id}_e";
		$sidebox['name'] = "{$id}_{$box_type}_" . TIME_NOW;
		$sidebox['class'] = $sidebox['id'] = "{$box_type}_main_{$id}";
		$sidebox['title'] = $title;
		$sidebox['content'] = $content;

		if($mybb->settings['asb_show_expanders'])
		{
			// check if this side box is either expanded or collapsed and hide it as necessary.
			$expdisplay = '';
			$collapsed_name = "{$box_type}_{$id}_c";
			if(isset($collapsed[$collapsed_name]) && $collapsed[$collapsed_name] == "display: show;")
			{
				$expcolimage = "collapse_collapsed.gif";
				$expdisplay = "display: none;";
				$expaltext = "[+]";
			}
			else
			{
				$expcolimage = "collapse.gif";
				$expaltext = "[-]";
			}
			$expander = <<<EOF

						<div class="expcolimage">
							<img src="{$theme['imgdir']}/{$expcolimage}" id="{$sidebox['expcolimage_id']}" class="expander" alt="{$expaltext}" title="{$expaltext}"/>
						</div>
EOF;
		}
		eval("\$content = \"" . $templates->get('asb_wrapped_sidebox') . "\";");
	}

	// if there is anything to return
	if($content)
	{
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
 * asb_get_all_modules()
 *
 * retrieve any detected modules
 */
function asb_get_all_modules()
{
	$return_array = array();

	// load all detected modules
	foreach(new DirectoryIterator(ASB_MODULES_DIR) as $file)
	{
		if($file->isFile())
		{
			// skip directories and '.' '..'
			if($file->isDot() || $file->isDir())
			{
				continue;
			}

			$extension = pathinfo($file->getFilename(), PATHINFO_EXTENSION);

			// only PHP files
			if($extension == 'php')
			{
				// extract the base_name from the module file name
				$filename = $file->getFilename();
				$module = substr($filename, 0, strlen($filename) - 4);

				// attempt to load the module
				$return_array[$module] = new Addon_type($module);
			}
		}
	}
	return $return_array;
}

/*
 * asb_get_all_custom()
 *
 * retrieve all custom boxes
 */
function asb_get_all_custom()
{
	global $db;

	// get any custom boxes
	$return_array = array();

	$query = $db->simple_select('asb_custom_sideboxes');
	if($db->num_rows($query) > 0)
	{
		while($data = $db->fetch_array($query))
		{
			$return_array['asb_custom_' . $data['id']] = new Custom_type($data);
		}
	}
	return $return_array;
}

/*
 * asb_get_all_sideboxes()
 *
 * retrieve all side boxes
 */
function asb_get_all_sideboxes($good_script = '')
{
	global $db;

	// get any side boxes
	$return_array = array();

	$query = $db->simple_select('asb_sideboxes', '*', '', array("order_by" => 'display_order', "order_dir" => 'ASC'));
	if($db->num_rows($query) > 0)
	{
		while($data = $db->fetch_array($query))
		{
			$sidebox = new Sidebox($data);

			if($good_script)
			{
				$scripts = $sidebox->get('scripts');
				if(!empty($scripts) && !in_array($good_script, $scripts))
				{
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
 * asb_get_all_scripts()
 *
 * retrieve all script definitions
 */
function asb_get_all_scripts()
{
	global $db;

	// get all the active scripts' info
	$return_array = array();

	$query = $db->simple_select('asb_script_info', '*', "active='1'");
	if($db->num_rows($query) > 0)
	{
		while($this_script = $db->fetch_array($query))
		{
			$filename = asb_build_script_filename($this_script);
			$return_array[$filename] = $this_script;
		}
	}
	return $return_array;
}

function asb_compile_box_types()
{
	// get all the box types and their titles
	$return_array = array();

	$addons = asb_get_all_modules();
	$custom = asb_get_all_custom();

	// get user-defined static types
	if(is_array($custom))
	{
		foreach($custom as $this_custom)
		{
			$return_array[$this_custom->get('base_name')] = $this_custom->get('title');
		}
	}

	// get add-on modules
	if(is_array($addons))
	{
		foreach($addons as $module)
		{
			$return_array[$module->get('base_name')] = $module->get('title');
		}
	}
	$box_types_lowercase = array_map('strtolower', $return_array);
	array_multisort($box_types_lowercase, SORT_ASC, SORT_STRING, $return_array);

	return $return_array;
}

?>
