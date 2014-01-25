<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2013 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this is an example of the an Advanced Sidebox add-on using a MyBB standard template to eval() content
 */

// Include a check for Advanced Sidebox
if(!defined("IN_MYBB") || !defined("IN_ASB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

/*
 * asb_example2_info()
 *
 * provide info to ASB about the addon
 */
function asb_example2_info()
{
	global $lang;

	if(!$lang->asb_addon)
	{
		$lang->load('asb_addon');
	}

	return array(
		"title" => $lang->asb_example2_title,
		"description" => $lang->asb_example2_desc,
		"wrap_content"	=> true,
		"version" =>	"1.1",
		"templates" => array(
			array(
				"title" 			=> "asb_example",
				"template" 	=> <<<EOF
				<tr>
					<td class="trow1">Image sized to side box column:</td>
				</tr>
				<tr>
					<td class="trow2">
						<img src="images/logo.gif" alt="logo" title="example" width="{\$inner_width}px" style="margin: {\$margin}px;"/>
					</td>
				</tr>
EOF
			)
		)
	);
}

/*
 * asb_example2_build_template()
 *
 * handles display of children of this addon at page load
 *
 * @param - $args - (array) the specific information from the child box
 */
function asb_example2_build_template($args)
{
	extract($args);

	global $$template_var, $templates;

	// you can use the side box width to size HTML elements:
	$inner_width = (int) ($width * .79);
	$margin = (int) (($width - $inner_width) / 2);

	// then eval() the template variable with the template above and you are done
	eval("\$" . $template_var . " = \"" . $templates->get("asb_example") . "\";");

	// return true if your box has something to show, or false if it doesn't.
	return true;
}

?>
