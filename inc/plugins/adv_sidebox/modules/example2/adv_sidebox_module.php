<?php
/*
 * Advanced Sidebox Add-On Example #2
 *
 * This is an example of the an Advanced Sidebox add-on, but using a MyBB standard template to eval() content.
 */

// Include a check for Advanced Sidebox
if(!defined("IN_MYBB") || !defined("ADV_SIDEBOX"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

function example2_asb_info()
{
	return array
	(
		"name"				=>	'[Example 2] Using Templates',
		"description"		=>	'A simple box to illustrate using templates to produce content',
		"wrap_content"	=>	true,
		"version"			=>	"1.1",
		"templates"		=>	array
										(
											array
											(
												"title" 			=> "adv_sidebox_example",
												"template" 	=> "
	<tr>
		<td class=\"trow1\">Image sized to side box colum:</td>
	</tr>
	<tr>
		<td class=\"trow2\">
			<img src=\"images/logo.gif\" alt=\"logo\" title=\"example\" width=\"{\$inner_width}px\" style=\"margin: {\$margin}px;\" />
		</td>
	</tr>
												",
												"sid"				=>	-1
											)
										)
	);
}

function example2_asb_build_template($settings, $template_var, $width)
{
	global $$template_var, $templates;

	// You can use the side box width to size HTML elements:
	// store values here to use in the template above
	$inner_width = (int) ($width * .79);
	$margin = (int) (($width - $inner_width) / 2);

	// then eval() the template variable with the template above and you are done
	eval("\$" . $template_var . " = \"" . $templates->get("adv_sidebox_example") . "\";");
}

?>
