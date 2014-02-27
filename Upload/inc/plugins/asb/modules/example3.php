<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this is an example of the an Advanced Sidebox add-on using a simple text setting to control content
 */

// Include a check for Advanced Sidebox
if(!defined("IN_MYBB") || !defined("IN_ASB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

/*
 * asb_example3_info()
 *
 * provide info to ASB about the addon
 *
 * @return: (array) the module info
 */
function asb_example3_info()
{
	global $lang;

	if(!$lang->asb_addon)
	{
		$lang->load('asb_addon');
	}

	return array(
		"title" => $lang->asb_example3_title,
		"description" => $lang->asb_example3_desc,
		"wrap_content" => true,
		"version" => "1",
		"settings" => array(
			"example3_setting" => array(
				"sid" => "NULL",
				"name" => "example3_setting",
				"title" => $lang->asb_example3_setting_anouncement_text,
				"description" => $lang->asb_example3_setting_anouncement_text_desc,
				"optionscode" => "text",
				"value" => ''
			)
		)
	);
}

/*
 * asb_example3_build_template()
 *
 * handles display of children of this addon at page load
 *
 * @param - $args - (array) the specific information from the child box
 * @return: (bool) true on success, false on fail/no content
 */
function asb_example3_build_template($args)
{
	extract($args);

	global $$template_var, $lang;

	if(!$lang->asb_addon)
	{
		$lang->load('asb_addon');
	}

	if(!$settings['example3_setting']['value'])
	{
		$settings['example3_setting']['value'] = $lang->asb_example3_info;
	}

	$$template_var = <<<EOF
		<tr>
					<td class="trow1"><span style="color: red; font-size: 1.4em; font-weight: bold;">{$settings['example3_setting']['value']}</span>
					</td>
				</tr>
EOF;

	// return true if your box has something to show, or false if it doesn't.
	return true;
}

?>
