<?php
/*
 * Advanced Sidebox Add-On Example #1
 *
 * This is an example of the simplest version of an Advanced Sidebox add-on. When a module doesn't include the file sidebox_install.php, it indicates to ASB that this module is self-contained and need not alter the forum in any way to operate. Incidentally if this file is missing because of some other error or mishandling the sidebox may fail (but it could very well work correctly if its last state was installed.)
 *
 * This box is worthless, but it is a starting point.
 */

// Include a check for Advanced Sidebox
if(!defined("IN_MYBB") || !defined("ADV_SIDEBOX"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

function example_info()
{
	return array
	(
		"name"				=>	'Example Box',
		"description"		=>	'A simple box to illustrate extending Advanced Sideboxes',
		"stereo"			=>	false
	);
}

// this function is called when it is time to display your box
function example_build_template()
{
	// don't forget to declare your variable! will not work without this
	global $example;
	
	$template = '<table border=\"0\" cellspacing=\"{$theme[\'borderwidth\']}\" cellpadding=\"{$theme[\'tablespace\']}\" class=\"tborder\"><tr><td class=\"thead\"><strong>Hello World Mono</strong></td></tr><tr><td class=\"trow1\">{$hello_world}</td></tr></table><br />';
	
	// set any variables in your template (or string as in this case) here just before the eval
	$hello_world = "Same from either side.";
	
	eval("\$example = \"" . $template . "\";");
}

?>