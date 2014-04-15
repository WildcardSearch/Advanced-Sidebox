<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this is an example of the simplest version of an ASB add-on
 */

// Include a check for Advanced Sidebox
if(!defined("IN_MYBB") || !defined("IN_ASB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

/*
 * asb_example_info()
 *
 * provide info to ASB about the addon
 *
 * @return: (array) the module info
 */
function asb_example_info()
{
	global $lang;

	if(!$lang->asb_addon)
	{
		$lang->load('asb_addon');
	}

	return array	(
		"title" => $lang->asb_example1_title,
		"description" => $lang->asb_example1_desc,
		"wrap_content" => true,
		"version" => '1',
		"compatibility" => '2.1',
	);
}

/*
 * asb_example_build_template()
 *
 * handles display of children of this addon at page load
 *
 * @param - $args - (array) the specific information from the child box
 * @return: (bool) true on success, false on fail/no content
 */
function asb_example_build_template($args)
{
	extract($args);

	/*
	 * using variable variables (thanks Euan T.) we declare the template variable as global here and eval() its contents.
	 */
	global $$template_var; //<-- this is necessary

	/*
	 * note the structure, this content should appropriate (and validate) as the contents of an HTML <tbody> element in structure and content.
	 */
	$$template_var = <<<EOF
		<tr>
					<td class="trow1">Same from either side.</td>
				</tr>
EOF;

	// return true if your box has something to show, or false if it doesn't.
	return true;
}

?>
