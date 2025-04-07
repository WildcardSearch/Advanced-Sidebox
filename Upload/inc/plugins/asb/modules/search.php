<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * https://www.rantcentralforums.com
 *
 * ASB default module
 */

// Include a check for Advanced Sidebox
if (!defined('IN_MYBB') ||
	!defined('IN_ASB')) {
	die('Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.');
}

/**
 * provide info to ASB about the addon
 *
 * @return array module info
 */
function asb_search_info()
{
	global $lang;

	if (!$lang->asb_addon) {
		$lang->load('asb_addon');
	}

	return array(
		'title' => $lang->asb_search,
		'description' => $lang->asb_search_desc,
		'wrap_content' => true,
		'version' => '2.0.0',
		'compatibility' => '4.0',
		'installData' => array(
			'templates' => array(
				array(
					'title' => 'asb_search',
					'template' => <<<EOF
				<div class="trow1 asb-search-container">
					<form method="post" action="{\$mybb->settings[\'bburl\']}/search.php">
						<input type="hidden" name="action" value="do_search"/>
						<input type="hidden" name="forums" value="all"/>
						<input type="hidden" name="sortby" value="lastpost"/>
						<input type="hidden" name="sortordr" value="desc"/>
						<label><strong>{\$lang->asb_search_in}:</strong></label><br />
						<input type="radio" class="radio" name="postthread" value="1" checked="checked"/>
						<label for="postthread">{\$lang->asb_search_messages}</label>
						<input type="radio" class="radio" name="postthread" value="2"/>
						<label for="postthread">Titles</label><br /><br />
						<label><strong>{\$lang->asb_search_results_as}:</strong></label><br />
						<input type="radio" class="radio" name="showresults" value="posts"/>
						<label for="showresults">{\$lang->asb_search_posts}</label>
						<input type="radio" class="radio" name="showresults" value="threads" checked="checked"/>
						<label for="showresults">{\$lang->asb_search_threads}</label><br /><br />
						<label for="keywords"><strong>{\$lang->asb_search_keywords}</strong></label><br />
						<input type="text" class="asb-search-keywords textbox" name="keywords"/>
						<input type="submit" class="asb-search-go-button button" value="Go">
					</form><br />
				</div>
				<div class="asb-search-advanced tfoot">
					<span class="smalltext"><a href="{\$mybb->settings[\'bburl\']}/search.php">{\$lang->asb_search_advanced_search}</a></span>
				</div>
EOF
				),
			),
		),
	);
}

/**
 * handles display of children of this addon at page load
 *
 * @param  array info from child box
 * @return bool sucess/fail
 */
function asb_search_get_content($settings, $script, $dateline)
{
	global $mybb, $templates, $lang;

	// Load global and custom language phrases
	if (!$lang->asb_addon) {
		$lang->load('asb_addon');
	}

	eval("\$content = \"{$templates->get('asb_search')}\";");

	return $content;
}