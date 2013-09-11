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

function asb_slide_show_box_info()
{
	return array
	(
		"title" => 'Slide Show',
		"description" => 'Loops through all the pictures in a particular directory',
		"wrap_content" => true,
		"version" => "1",
		"settings" => array
		(
			"folder_name" =>	array
			(
				"sid" => "NULL",
				"name" => "folder_name",
				"title" => "Folder Name",
				"description" => "the folder containing the images to be looped",
				"optionscode" => "text",
				"value" => 'images'
			),
			"link_url" =>	array
			(
				"sid" => "NULL",
				"name" => "link_url",
				"title" => "Footer Link",
				"description" => "URL",
				"optionscode" => "text",
				"value" => ''
			),
			"link_title" =>	array
			(
				"sid" => "NULL",
				"name" => "link_title",
				"title" => "",
				"description" => "title of link",
				"optionscode" => "text",
				"value" => ''
			),
			"pause" =>	array
			(
				"sid" => "NULL",
				"name" => "pause",
				"title" => "Pause",
				"description" => "how long in seconds to show each photo (leave blank for 18 sec. default)",
				"optionscode" => "text",
				"value" => ''
			),
			"shuffle" =>	array
			(
				"sid" => "NULL",
				"name" => "shuffle",
				"title" => "Shuffle?",
				"description" => "YES for random, NO for linear",
				"optionscode" => "yesno",
				"value" => ''
			)
		)
	);
}

function asb_slide_show_box_build_template($args)
{
	foreach(array('settings', 'template_var', 'width') as $key)
	{
		$$key = $args[$key];
	}
	global $$template_var;

	$settings['shuffle']['value'] = (int) $settings['shuffle']['value'];

	if(!$settings['pause']['value'])
	{
		$settings['pause']['value'] = 18;
	}
	else
	{
		$settings['pause']['value'] = (int) $settings['pause']['value'];
	}

	if($settings['link_title']['value'] && $settings['link_url']['value'])
	{
		$ss_link = "<a href=\"{$settings['link_url']['value']}\"><span style=\"font-weight: bold;\">{$settings['link_title']['value']}</span></a>";
		$ss_footer = "
			<tr class=\"tfoot\">
				<td>{$ss_link}</td>
			</tr>
		";
	}

	if(!$settings['folder_name']['value'])
	{
		$settings['folder_name']['value'] = 'images';
	}

	$file_list .= '';

	foreach(new DirectoryIterator($settings['folder_name']['value']) as $file)
	{
		if($file->isDot() || $file->isDir() || !in_array($file->getExtension(), array('gif', 'png', 'jpg', 'jpeg'))) continue;

		$file_list .= $file->getFilename() . "\n";
	}

	$width = (int) $width * .95;

	$$template_var = "
	<tr>
		<td class=\"trow1\">
<script type=\"text/javascript\" language=\"JavaScript\">
var folder = '{$settings['folder_name']['value']}/';
var phwidth = {$width}; // pictureholder width
var phheight = {$width}; // pictureholder height

var photosize = {$width}; // width to show photos at
// Valid sizes are: 32, 48, 64, 72, 144, 160, 200, 288, 320, 400, 512, 576, 640, 720, 800

var seconds = {$settings['pause']['value']}; // switch photos n seconds
var randomize_photos = {$settings['shuffle']['value']}; // 0 = Do not randomize photos; 1 = Randomize photos
var display_caption = 0; // 0 = No caption; 1 = Display Description below photo
var caption_height = 50; // How many pixels high for caption box if on
var caption_border = 1; // Caption border pixels
// captionholder DIV has the class 'captionholder' so you can add CSS
var pan_zoom = 1.3;  // how many times to zoom before panning
var trans = \"fadeinout\"; // default transition between photos
var effect = \"zoomin(center)\"; // default effect on photos

var cur_pic = 0; // which photo to start with. 0 is first photo in album.
var trans_amount = 50; // amount of steps for transitions
var effect_amount = 6; // amount of steps for effects
var testing_mode = 0; // 0 = off; 1 = on; testing mode creates a DIV with ID testing to display testing info

var flicker_time = 300; // ms between photo operations
var opacity_time = 50; // ms between opacity
var trans_time = 45; // ms between transition steps
var effect_time = 300; // ms between effect steps

//end custom
</script>
			<SCRIPT TYPE=\"text/javascript\" LANGUAGE=\"JavaScript\" SRC=\"jscripts/coolslide.js\"></SCRIPT>
			<textarea id=\"piclist\" style=\"display:none;\">
			{$file_list}
			</textarea>
		</td>
	</tr>
	{$ss_footer}
	";

	// return true if your box has something to show, or false if it doesn't.
	return true;
}

?>
