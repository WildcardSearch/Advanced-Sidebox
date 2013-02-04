<?php
/*
 * Advanced Sidebox Add-On Example #3
 *
 * This is an example of the an Advanced Sidebox add-on, but using a MyBB standard template to eval() content.
 */

// Include a check for Advanced Sidebox
if(!defined("IN_MYBB") || !defined("ADV_SIDEBOX"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

function example3_asb_info()
{
	return array
	(
		"name"				=>	'[Example 3] Using Settings',
		"description"		=>	'A simple box to illustrate using settings to control content',
		"wrap_content"	=>	true,
		"version"			=>	"1",
		"settings"			=>	array
										(
											"example3_setting"	=>	array
											(
												"sid"					=> "NULL",
												"name"				=> "example3_setting",
												"title"				=> "Announcement Text",
												"description"		=> "this text will be styled and displayed",
												"optionscode"	=> "text",
												"value"				=> ''
											)
										)
	);
}

function example3_asb_build_template($settings, $template_var)
{
	global $$template_var;

	if($settings['example3_setting']['value'] != '')
	{
		$template = '
	<tr>
		<td><span style=\"color: red; font-size: 1.4em; font-weight: bold;\">' . $settings['example3_setting']['value'] . '</span>
		</td>
	</tr>';
	}

	// then eval() the template variable with the template above and you are done
	eval("\$" . $template_var . " = \"" . $template . "\";");
}

?>
