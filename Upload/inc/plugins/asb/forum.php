<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * the forum-side routines start here
 */

// only add the necessary hooks and templates
asb_initialize();

/**
 * main implementation of many hooks depending upon THIS_SCRIPT constant and
 * $mybb->input/$_GET vars (see asb_initialize())
 *
 * @return void
 */
function asb_start()
{
	global $mybb, $theme;

	// don't waste execution if unnecessary
	if (!asbDoStartupChecks()) {
		return;
	}

	$asb = AdvancedSideboxCache::getInstance()->getCache();
	$script = asbGetCurrentScript($asb, true);

	// no boxes, get out
	if (!is_array($script['sideboxes']) ||
	   empty($script['sideboxes']) ||
	   (empty($script['sideboxes'][0]) &&
	   empty($script['sideboxes'][1])) ||
	   ((strlen($script['find_top']) == 0 ||
	   strlen($script['find_bottom']) == 0) &&
	   (!$script['replace_all'] &&
	   !$script['eval']))) {
		return;
	}

	$boxes = array(
		0 => '',
		1 => '',
	);

	if (empty($script['sideboxes'][0])) {
		$script["width_left"] = $script["left_margin"] = '0';
	}

	if (empty($script['sideboxes'][1])) {
		$script["width_right"] = $script["right_margin"] = '0';
	}

	$width = array(
		0 => $script["width_left"],
		1 => $script["width_right"],
		2 => $script["width_middle"],
		3 => $script["left_margin"],
		4 => $script["right_margin"],
	);

	$totalWidth = $width[0] + $width[1] + $width[2] + $width[3] + $width[4];
	if ($totalWidth < 100) {
		$width[2] += (100 - $totalWidth);
	} elseif ($totalWidth > 100) {
		$width[2] -= ($totalWidth - 100);
	}

	// does this column have boxes?
	if (!is_array($script['sideboxes']) ||
		empty($script['sideboxes'])) {
		return;
	}

	// functions for add-on modules
	require_once MYBB_ROOT.'inc/plugins/asb/functions_addon.php';

	// loop through all the boxes for the script
	foreach ($script['sideboxes'] as $pos => $sideboxes) {
		// does this column have boxes?
		if (!is_array($sideboxes) ||
			empty($sideboxes)) {
			continue;
		}

		// loop through them
		foreach ($sideboxes as $id => $module_name) {
			// verify that the box ID exists
			if (!isset($asb['sideboxes'][$id])) {
				continue;
			}

			// then load the object
			$sidebox = new SideboxObject($asb['sideboxes'][$id]);

			// can the user view this side box?
			if (!asbCheckUserPermissions($sidebox->get('groups'))) {
				continue;
			}

			// is this theme available for this side box?
			$good_themes = $sidebox->get('themes');
			if ($good_themes &&
				!in_array($theme['tid'], $good_themes)) {
				continue;
			}

			$result = false;

			// get the template variable
			$template_var = "{$module_name}_{$id}";

			// attempt to load the box as an add-on module
			$module = new SideboxModule($module_name);

			// if it is valid, then the side box was created using an
			// add-on module, so we can proceed
			if ($module->isValid()) {
				// build the template. pass settings, template variable
				// name and column width
				$result = $module->buildContent($sidebox->get('settings'), $template_var, get_current_location());
			// if it doesn't verify as an add-on, try it as a custom box
			} elseif (isset($asb['custom'][$module_name]) &&
				is_array($asb['custom'][$module_name])) {
				$custom = new CustomSidebox($asb['custom'][$module_name]);

				// if it validates, then build it, otherwise there was an error
				if ($custom->isValid()) {
					// build the custom box template
					$result = $custom->buildContent($template_var);
				}
			} else {
				continue;
			}

			if ($result) {
				$boxes[$pos] .= asbBuildSideBoxContent($sidebox->get('data'));
			}
		}
	}

	// make the edits
	asb_edit_template($boxes, $width, $script);
}

/**
 * edit the appropriate template
 *
 * @param  array side boxes
 * @param  array left and right width
 * @param  array current script definition
 * @return void
 */
function asb_edit_template($boxes, $width, $script)
{
	global $mybb, $lang, $templates, $headerinclude, $theme;

	if ($mybb->settings['asb_minify_js']) {
		$min = '.min';
	}

	$leftInsert = $boxes[0];
	$rightInsert = $boxes[1];

	$width_left = $width[0];
	$width_middle = $width[2];
	$width_right = $width[1];

	$left_margin = $width[3];
	$right_margin = $width[4];

	$toggles = $show = array();
	$filename = THIS_SCRIPT;

	// if admin wants to show the toggle icons...
	if ($mybb->settings['asb_show_toggle_icons']) {
		// we will need this js
		$headerinclude .= <<<EOF
<script type="text/javascript" src="jscripts/asb/asb{$min}.js"></script>
<script type="text/javascript">
<!--
ASB.setup({
	leftWidth: "{$width_left}",
	leftMargin: "{$left_margin}",
	middleWidth: "{$width_middle}",
	rightMargin: "{$right_margin}",
	rightWidth: "{$width_right}",
});
// -->
</script>
EOF;

		$toggle_info['left'] = array(
			'close' => array(
				'img' => "{$theme['imgdir']}/asb/left_arrow.png",
				'alt' => '&lt;',
			),
			'open' => array(
				'img' => "{$theme['imgdir']}/asb/right_arrow.png",
				'alt' => '&gt;',
			)
		);
		$toggle_info['right']['close'] = $toggle_info['left']['open'];
		$toggle_info['right']['open'] = $toggle_info['left']['close'];

		foreach (array('left', 'right') as $key) {
			// check the cookie
			if ($mybb->cookies["asb_hide_{$key}"] == 1) {
				// hide left
				$show[$key] = $close_style = 'display: none; ';
				$open_style = '';
			} else {
				// show left
				$close_style = '';
				$open_style = 'display: none; ';
			}

			// produce the link
			$open_image = $toggle_info[$key]['open']['img'];
			$close_image = $toggle_info[$key]['close']['img'];
			$open_alt = $toggle_info[$key]['open']['alt'];
			$close_alt = $toggle_info[$key]['close']['alt'];
			$column_id = "asb_hide_column_{$key}";
			$closed_id = "asb_{$key}_close";
			$open_id = "asb_{$key}_open";

			$positionClass = " asb-sidebox-toggle-column-{$key}";

			eval("\$toggles[\$key] = \"{$templates->get('asb_toggle_icon')}\";");
		}
	}

	foreach (array('left', 'right') as $key) {
		// if there is content
		$varName = "{$key}Insert";
		if ($$varName) {
			$propName = "{$key}_content";
			$widthName = "width_{$key}";
			$width = $$widthName;
			$show_column = $show[$key];
			$column_id = "asb_{$key}_column_id";
			$insertName = $varName;
			$sideboxes = $$insertName;
			$extraClass = " asb-sidebox-column-{$key}";

			$toggle_left = $toggle_right = '';
			$toggle_name = "toggle_{$key}";
			$$toggle_name = $toggles[$key];

			eval("\$content = \"{$templates->get('asb_sidebox_column')}\";");

			// finally set $POSITION_content for ::make_edits()
			$$propName = <<<EOF

		<!-- start: ASB {$key} column -->
		{$content}
		<!-- end: ASB {$key} column -->
EOF;
		}
	}

	eval("\$insertTop = \"{$templates->get('asb_begin')}\";");
	eval("\$insertBottom = \"{$templates->get('asb_end')}\";");

	if (is_array($script['extra_scripts']) &&
		!empty($script['extra_scripts'])) {
		$sep = '';
		$dateline = TIME_NOW;
		foreach ($script['extra_scripts'] as $id => $info) {
			// build the JS objects to pass to the custom object builder
			$extraScripts .= <<<EOF
{$sep}{ addon: '{$info['module']}', id: {$id}, position: {$info['position']}, rate: {$info['rate']}, dateline: {$dateline} }
EOF;
			$sep = ', ';
		}

		$location = get_current_location();

		$headerinclude .= <<<EOF

<script type="text/javascript" src="jscripts/asb/asb_xmlhttp{$min}.js"></script>
<script type="text/javascript">
<!--
$(function() {
	ASB.ajax.buildUpdaters([ {$extraScripts} ], { left: {$width_left}, right: {$width_right} }, '{$location}');
});
// -->
</script>
EOF;
	}

	if (is_array($script['js'])) {
		foreach ($script['js'] as $scriptName) {
			$scriptName .= $min;
			if (!file_exists(MYBB_ROOT."jscripts/asb/{$scriptName}.js")) {
				continue;
			}

			$headerinclude .= <<<EOF

<script type="text/javascript" src="jscripts/asb/{$scriptName}.js"></script>
EOF;
		}
	}

	// replace everything on the page?
	if ($script['replace_all'] == true) {
		// if there is content
		if ($script['replacement']) {
			// replace the existing page entirely
			$templates->cache[$script['template_name']] = str_replace(array('{$asb_left}', '{$asb_right}'), array($insertTop, $insertBottom), $script['replacement']);
		}
	// outputting to variables? (custom script/Page Manager)
	} elseif($script['eval']) {
		// globalize our columns
		global $asb_left, $asb_right;

		// globalize all the add-on template variables
		if (is_array($script['template_vars']) &&
			!empty($script['template_vars'])) {
			foreach ($script['template_vars'] as $var) {
				global $$var;
			}
		}

		// now eval() their content for the custom script
		eval("\$asb_left = \"".str_replace("\\'", "'", addslashes($insertTop))."\";");
		eval("\$asb_right = \"".str_replace("\\'", "'", addslashes($insertBottom))."\";");
	// otherwise we are editing the template in the cache
	} else {
		// if there are columns stored
		if ($insertTop ||
			$insertBottom) {
			// make the edits
			$script['find_top'] = str_replace("\r", '', $script['find_top']);
			$script['find_bottom'] = str_replace("\r", '', $script['find_bottom']);
			$topPosition = strpos($templates->cache[$script['template_name']], $script['find_top']);

			if ($topPosition !== false) {
				$bottomPosition = strpos($templates->cache[$script['template_name']], $script['find_bottom']);

				if ($bottomPosition !== false) {
					/*
					 * split the template in 3 parts and splice our columns in after 1 and before 3
					 * it is important that we function this way so we can work with the
					  * FIRST instance of the search text (find_top and find_bottom) rather
					  * than replacing multiple found instances
					 */
					$templates->cache[$script['template_name']] =
						substr($templates->cache[$script['template_name']], 0, $topPosition + strlen($script['find_top'])) .
						$insertTop .
						substr($templates->cache[$script['template_name']], $topPosition + strlen($script['find_top']), $bottomPosition - ($topPosition + strlen($script['find_top']))) .
						$insertBottom .
						substr($templates->cache[$script['template_name']], $bottomPosition);
				}
			}
		}
	}
}

/**
 * add the appropriate hooks and caches any templates that will be used
 *
 * @return void
 */
function asb_initialize()
{
	global $mybb, $plugins;

	if (!defined('THIS_SCRIPT')) {
		return;
	}

	// hooks for the User CP routine.
	switch (THIS_SCRIPT) {
	case 'usercp.php':
		if ($mybb->settings['asb_allow_user_disable']) {
			$plugins->add_hook('usercp_options_end', 'asbUserCpOptionsEnd');
			$plugins->add_hook('usercp_do_options_end', 'asbUserCpOptionsEnd');
		}
		break;
	case 'xmlhttp.php':
		$plugins->add_hook('xmlhttp', 'asb_xmlhttp');
		break;
	}

	// get the cache
	$asb = AdvancedSideboxCache::getInstance()->getCache();
	$script = asbGetCurrentScript($asb, true);

	// anything to show for this script?
	if (!is_array($script['sideboxes']) ||
		empty($script['sideboxes'])) {
		return;
	}

	// then add the hook...one priority lower than Page Manager ;-) we need to run first
	$plugins->add_hook($script['hook'], 'asb_start', 9);

	// cache any script-specific templates (read: templates used by add-ons used in the script)
	$addedTemplates = '';
	if (is_array($script['templates']) &&
		!empty($script['templates'])) {
		$addedTemplates = ','.implode(',', $script['templates']);
	}

	// add the extra templates (if any) to our base stack
	global $templatelist;
	$templatelist .= ',asb_begin,asb_end,asb_sidebox_column,asb_wrapped_sidebox,asb_toggle_icon,asb_content_pad,asb_expander,asb_sidebox_no_content'.$addedTemplates;
}

/**
 * add a check box to the User CP under Other Options to toggle the side boxes
 *
 * @return void
 */
function asbUserCpOptionsEnd()
{
	global $db, $mybb, $templates, $user, $lang, $asbShowSideboxes;

	// if the form is being submitted save the users choice.
	if ($mybb->request_method == 'post') {
		$db->update_query('users', array('show_sidebox' => (int) $mybb->input['showsidebox']), "uid='{$user['uid']}'");
		return;
    }

	if (!$lang->asb) {
		$lang->load('asb');
	}

    // don't be silly and waste a query :p (thanks Destroy666)
	if ($mybb->user['show_sidebox'] > 0) {
		$checked = 'checked="checked" ';
	}

	eval("\$asbShowSideboxes = \"{$templates->get('asb_ucp_show_sidebox_option')}\";");
}

/**
 * handle the AJAX refresh for side box modules (replacing asb/xmlhttp.php)
 *
 * @return void
 */
function asb_xmlhttp()
{
	global $mybb;

	if ($mybb->input['action'] != 'asb') {
		return;
	}

	// get the ASB core stuff
	require_once MYBB_ROOT.'inc/plugins/asb/functions_addon.php';

	// attempt to load the module and side box requested
	$module = new SideboxModule($mybb->input['addon']);
	$sidebox = new SideboxObject($mybb->input['id']);

	// we need both objects to continue
	if ($module->isValid() &&
		$sidebox->isValid()) {
		// then call the module's AJAX method and echo its return value
		echo($module->doXmlhttp($mybb->input['dateline'], $sidebox->get('settings'), $mybb->input['script']));
	}
	exit;
}

?>
