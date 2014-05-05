<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * the forum-side routines start here
 */

// only add the necessary hooks and templates
asb_initialize();

/*
 * asb_start()
 *
 * main implementation of many hooks depending upon THIS_SCRIPT constant and
 * $mybb->input/$_GET vars (see asb_initialize())
 *
 * @return: n/a
 */
function asb_start()
{
	global $mybb, $theme;

	// side box, add-on and custom box classes
	require_once MYBB_ROOT . 'inc/plugins/asb/classes/forum.php';

	// don't waste execution if unnecessary
	if(!asb_do_checks())
	{
		return;
	}

	$asb = asb_get_cache();
	$this_script = asb_get_this_script($asb, true);

	// no boxes, get out
	if(empty($this_script['sideboxes'][0]) && empty($this_script['sideboxes'][1]))
	{
		return;
	}

	$width = $boxes = array(
		0 => '',
		1 => ''
	);

	// make sure this script's width is within range 120-800 (120 because the templates
	// aren't made to work any smaller and tbh 800 is kind of arbitrary :s
	foreach(array("left" => 0, "right" => 1) as $key => $pos)
	{
		$width[$pos] = (int) max(120, min(800, $this_script["width_{$key}"]));
	}

	// does this column have boxes?
	if(!is_array($this_script['sideboxes']) || empty($this_script['sideboxes']))
	{
		return;
	}

	// functions for add-on modules
	require_once MYBB_ROOT . 'inc/plugins/asb/functions_addon.php';

	// loop through all the boxes for the script
	foreach($this_script['sideboxes'] as $pos => $sideboxes)
	{
		// does this column have boxes?
		if(!is_array($sideboxes) || empty($sideboxes))
		{
			continue;
		}

		// loop through them
		foreach($sideboxes as $id => $module_name)
		{
			// verify that the box ID exists
			if(!isset($asb['sideboxes'][$id]))
			{
				continue;
			}

			// then load the object
			$sidebox = new Sidebox($asb['sideboxes'][$id]);

			// can the user view this side box?
			if(!asb_check_user_permissions($sidebox->get('groups')))
			{
				continue;
			}

			// is this theme available for this side box?
			$good_themes = $sidebox->get('themes');
			if($good_themes && !in_array($theme['tid'], $good_themes))
			{
				continue;
			}

			$result = false;

			// get the template variable
			$template_var = "{$module_name}_{$id}";

			// attempt to load the box as an add-on module
			$module = new Addon_type($module_name);

			// if it is valid, then the side box was created using an
			// add-on module, so we can proceed
			if($module->is_valid())
			{
				// build the template. pass settings, template variable
				// name and column width
				$result = $module->build_template($sidebox->get('settings'), $template_var, $width[$pos], get_current_location());
			}
			// if it doesn't verify as an add-on, try it as a custom box
			elseif(isset($asb['custom'][$module_name]) && is_array($asb['custom'][$module_name]))
			{
				$custom = new Custom_type($asb['custom'][$module_name]);

				// if it validates, then build it, otherwise there was an error
				if($custom->is_valid())
				{
					// build the custom box template
					$result = $custom->build_template($template_var);
				}
			}
			else
			{
				continue;
			}

			/*
			 * all box types return true or false based upon whether they have
			 * content to show. in the case of custom boxes, false is returned
			 * when the custom content is empty; in reference to add-on modules
			 * many factors are involved, but basically, if the side box depends on
			 * an element (threads for example) and there are none, it will return
			 * false-- IF asb_show_empty_boxes is true then it will return a side
			 * box with a 'no content' message, if not, it will be skipped
			 */
			if($result || $mybb->settings['asb_show_empty_boxes'])
			{
				$boxes[$pos] .= asb_build_sidebox_content($sidebox->get('data'));
			}
		}
	}

	// load the template handler class definition and make the edits
	require_once MYBB_ROOT . 'inc/plugins/asb/classes/template_handler.php';
	ASBTemplateHandler::edit($boxes, $width, $this_script);
}

/*
 * asb_initialize()
 *
 * add the appropriate hooks and caches any templates that will be used
 *
 * @return: n/a
 */
function asb_initialize()
{
	global $mybb, $plugins;

	// hooks for the User CP routine.
	switch (THIS_SCRIPT) {
	case 'usercp.php':
		if($mybb->settings['asb_allow_user_disable'])
		{
			$plugins->add_hook('usercp_options_end', 'asb_usercp_options_end');
			$plugins->add_hook('usercp_do_options_end', 'asb_usercp_options_end');
		}
		break;
	case 'xmlhttp.php':
		$plugins->add_hook('xmlhttp', 'asb_xmlhttp');
		break;
	}

	// get the cache
	$asb = asb_get_cache();
	$this_script = asb_get_this_script($asb, true);

	// anything to show for this script?
	if(!is_array($this_script['sideboxes']) || empty($this_script['sideboxes']))
	{
		return;
	}

	// then add the hook . . . one priority lower than Page Manager ;-) we need to run first
	$plugins->add_hook($this_script['hook'], 'asb_start', 9);

	// cache any script-specific templates (read: templates used by add-ons used in the script)
	$template_list = '';
	if(is_array($this_script['templates']) && !empty($this_script['templates']))
	{
		$template_list = ',' . implode(',', $this_script['templates']);
	}

	// add the extra templates (if any) to our base stack
	global $templatelist;
	$templatelist .= ',asb_begin,asb_end,asb_sidebox_column,asb_wrapped_sidebox,asb_toggle_icon,asb_content_pad,asb_expander' . $template_list;
}

/*
 * asb_usercp_options_end()
 *
 * add a check box to the User CP under Other Options to toggle the side boxes
 *
 * @return: n/a
 */
function asb_usercp_options_end()
{
	global $db, $mybb, $templates, $user, $lang;

	if(!$lang->asb)
	{
		$lang->load('asb');
	}

    // if the form is being submitted save the users choice.
	if($mybb->request_method == 'post')
    {
		$db->update_query('users', array("show_sidebox" => (int) $mybb->input['showsidebox']), "uid='{$user['uid']}'");
    }

	// don't be silly and waste a query :p (thanks Destroy666)
	if($mybb->user['show_sidebox'] > 0)
	{
		// checked
		$checked = 'checked="checked" ';
	}

	$usercp_option = <<<EOF
	<td valign="top" width="1">
		<input type="checkbox" class="checkbox" name="showsidebox" id="showsidebox" value="1" {$checked}/>
	</td>
	<td>
		<span class="smalltext"><label for="showsidebox">{$lang->asb_show_sidebox}</label></span>
	</td>
</tr>
<tr>
<td valign="top" width="1">
	<input type="checkbox" class="checkbox" name="showredirect"
EOF;

    // update the template cache
	$find = <<<EOF
<td valign="top" width="1"><input type="checkbox" class="checkbox" name="showredirect"
EOF;
    $templates->cache['usercp_options'] = str_replace($find, $usercp_option, $templates->cache['usercp_options']);
}

/*
 * asb_xmlhttp()
 *
 * handle the AJAX refresh for side box modules (replacing asb/xmlhttp.php)
 *
 * @return: n/a
 */
function asb_xmlhttp()
{
	global $mybb;

	if($mybb->input['action'] != 'asb')
	{
		return;
	}

	// get the ASB core stuff
	require_once MYBB_ROOT . 'inc/plugins/asb/functions_addon.php';
	require_once MYBB_ROOT . 'inc/plugins/asb/classes/xmlhttp.php';

	// attempt to load the module and side box requested
	$module = new Addon_type($mybb->input['addon']);
	$sidebox = new Sidebox($mybb->input['id']);

	// we need both objects to continue
	if($module->is_valid() && $sidebox->is_valid())
	{
		// then call the module's AJAX method and echo its return value
		echo($module->do_xmlhttp($mybb->input['dateline'], $sidebox->get('settings'), $mybb->input['width'], $mybb->input['script']));
	}
	exit;
}

?>
