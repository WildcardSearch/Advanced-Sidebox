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

// the first of two required routines for a successful box_type add.
function example_box_add_type(&$box_types)
{
	/*
	 * just add your template variable to the $box_types array
	 *
	 * $box_types[''] <-- 	enter your template variable. it must be the same as the name of your add-on module enclosed in curly brackets {} and with a $
	 * = ''; <-- enter the description/name of your add-on.
	 */
	$box_types['{$example_box}'] = 'Hello World';
}

// this function is called when it is time to display your box
function example_box_build_template(&$box_types)
{
	// don't forget to declare your variable! will not work without this
	global $example_box;
	
	/*
	 * check if the custom box type has been used by admin
	 *
	 * this is important because if the box hasn't been used it would be a waste to go any further
	*/
	if($box_types['{$example_box}'])
	{
		/*
		 * using a string requires that you flip the script and use single quotes, or just use double quotes and double-escape them.
		 *
		 * edit: I just realized 'flip the script' is funny :-/
		 */
		$template = '<table border=\"0\" cellspacing=\"{$theme[\'borderwidth\']}\" cellpadding=\"{$theme[\'tablespace\']}\" class=\"tborder\"><tr><td class=\"thead\"><strong>Hello World</strong></td></tr><tr><td class=\"trow1\">{$hello_world}</td></tr></table><br />';
		
		// set any variables in your template (or string as in this case) here just before the eval
		$hello_world = "Hello world";
		
		// then eval your variable and it will show up :)
		eval("\$example_box = \"" . $template . "\";");
	}
}

?>