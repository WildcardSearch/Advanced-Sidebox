<?php
/*
 * Advanced Sidebox Add-On Example #2
 *
 * This is an example of the simple version of an Advanced Sidebox add-on, but using a 'stereo' template variable.
 *
 * 'Stereo' just means the add-on module creates two different template variables for left and right (channels). This allows the module to size elements based on sidebox width.
 */

// Include a check for Advanced Sidebox
if(!defined("IN_MYBB") || !defined("ADV_SIDEBOX"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

/*
 * example2_asb_info()
 *
 * This is a required function and must be named correctly and return an array with the appropriate information.
 */
function example2_asb_info()
{
	return array
	(
		"name"				=>	'[Example 2] Simple Stereo',
		"description"		=>	'A simple box to illustrate creating a stereo sidebox',
		"stereo"			=>	true,
		"wrap_content"	=>	true,
		"version"			=>	"1"
	);
}

// this function is called when it is time to display your box
function example2_asb_build_template($settings, $template_var)
{
	// create a template variable based on the passed $template_var to allow independent left and right 'channels'
	$left_var = $template_var . '_l';
	$right_var = $template_var . '_r';
	global $$left_var, $$right_var;
	
	// Add-on modules can create their own templates (stored and editable in ACP) but you can also use in-line templates like this:
	$template = '
		<tr>
			<td class=\"trow1\">{$hello_world}</td>
		</tr>';
	
	// set any variables in your template (or string as in this case) here just before the eval()
	// left
	$hello_world = "Different content on the left";
	eval("\$" . $left_var . " = \"" . $template . "\";");
	
	// and again for the right
	$hello_world = "Different content on the right";
	eval("\$" . $right_var . " = \"" . $template . "\";");
}

?>
