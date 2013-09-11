<?php
/*
 * Plug-in Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2013 WildcardSearch
 * http://www.wildcardsworld.com
 *
 * ASB default module
 */

// Include a check for Advanced Sidebox
if(!defined("IN_MYBB") || !defined("IN_ASB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

function asb_search_box_info()
{
	global $lang;

	if(!$lang->asb_addon)
	{
		$lang->load('asb_addon');
	}

	return array
	(
		"title" => $lang->asb_search,
		"description" => $lang->asb_search_desc,
		"wrap_content" => true,
		"version" => "1.2.1",
		"templates" =>	array
		(
			array
			(
				"title" => "asb_search",
				"template" => <<<EOF
				<tr>
					<td class="trow1">
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
							<input style="width: 95%;" type="text" class="textbox" name="keywords"/>
							{\$gobutton}
						</form><br />
						<span class="smalltext">
						(<a href="{\$mybb->settings[\'bburl\']}/search.php">{\$lang->asb_search_advanced_search}</a>)
						</span>
					</td>
				</tr>
EOF
				,
				"sid" => -1
			)
		)
	);
}

function asb_search_box_build_template($args)
{
	foreach(array('settings', 'template_var') as $key)
	{
		$$key = $args[$key];
	}
	// don't forget to declare your variable! will not work without this
	global $$template_var; // <-- important!

	global $mybb, $templates, $lang, $gobutton;

	// Load global and custom language phrases
	if(!$lang->asb_addon)
	{
		$lang->load('asb_addon');
	}

	eval("\$" . $template_var . " = \"" . $templates->get("asb_search") . "\";");
	return true;
}

?>
