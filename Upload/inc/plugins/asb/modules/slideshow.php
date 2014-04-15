<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * ASB default module
 */

// include a check for Advanced Sidebox
if(!defined('IN_MYBB') || !defined('IN_ASB'))
{
	die('Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.');
}

/*
 * asb_slideshow_info()
 *
 * provide info to ASB about the addon
 *
 * @return: (array) the module info
 */
function asb_slideshow_info()
{
	global $lang;

	if(!$lang->asb_addon)
	{
		$lang->load('asb_addon');
	}

	return array(
		"title" => $lang->asb_slideshow,
		"description" => $lang->asb_slideshow_desc,
		"wrap_content" => true,
		"version" => '1',
		"scripts" => array(
			'Slideshow',
		),
		"settings" => array(
			"folder" => array(
				"sid" => 'NULL',
				"name" => 'folder',
				"title" => $lang->asb_slideshow_folder_title,
				"description" => $lang->asb_slideshow_folder_description,
				"optionscode" => 'text',
				"value" => 'images'
			),
			"rate" => array(
				"sid" => 'NULL',
				"name" => 'rate',
				"title" => $lang->asb_slideshow_rate_title,
				"description" => $lang->asb_slideshow_rate_description,
				"optionscode" => 'text',
				"value" => '10'
			),
			"shuffle" => array(
				"sid" => 'NULL',
				"name" => 'shuffle',
				"title" => $lang->asb_slideshow_shuffle_title,
				"description" => $lang->asb_slideshow_shuffle_description,
				"optionscode" => 'yesno',
				"value" => '1'
			),
			"fade_rate" => array(
				"sid" => 'NULL',
				"name" => 'fade_rate',
				"title" => $lang->asb_slideshow_fade_rate_title,
				"description" => $lang->asb_slideshow_fade_rate_description,
				"optionscode" => 'text',
				"value" => '1'
			),
			"footer_text" => array(
				"sid" => 'NULL',
				"name" => 'footer_text',
				"title" => $lang->asb_slideshow_footer_text_title,
				"description" => $lang->asb_slideshow_footer_text_description,
				"optionscode" => 'text',
				"value" => ''
			),
			"footer_url" => array(
				"sid" => 'NULL',
				"name" => 'footer_url',
				"title" => $lang->asb_slideshow_footer_url_title,
				"description" => $lang->asb_slideshow_footer_url_description,
				"optionscode" => 'text',
				"value" => ''
			),
		),
	);
}

/*
 * asb_slideshow_build_template()
 *
 * handles display of children of this addon at page load
 *
 * @param - $args - (array) the specific information from the child box
 * @return: (bool) true on success, false on fail/no content
 */
function asb_slideshow_build_template($args)
{
	extract($args);

	global $$template_var, $mybb;

	$shuffle = $settings['shuffle'] ? 'true' : 'false';
	$folder = $settings['folder'];
	$rate = (int) $settings['rate'] ? (int) $settings['rate'] : 10;
	$fade_rate = (float) $settings['fade_rate'] ? (float) $settings['fade_rate'] : 1;

	if(!is_dir(MYBB_ROOT . $folder))
	{
		return false;
	}

	$sep = '';
	foreach(new DirectoryIterator(MYBB_ROOT . $folder) as $file)
	{
		if($file->isDir() || $file->isDot())
		{
			continue;
		}

		$extension = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
		if(!in_array($extension, array('gif', 'png', 'jpg', 'jpeg')))
		{
			continue;
		}

		$filenames .= "{$sep}'{$file->getFilename()}'";
		$sep = ',';
	}

	if ($settings['footer_text'] && $settings['footer_url']) {
		$footer = <<<EOF

				<tr>
					<td class="tfoot">
						<div style="text-align: center;">
							<a style="font-weight: bold;" href="{$settings['footer_url']}">{$settings['footer_text']}</a>
						</div>
					</td>
				</tr>
EOF;
	}

	$width = $width * .9;
	$folder = $mybb->settings['bburl'] . '/' . $folder;
	$$template_var = <<<EOF
		<tr>
					<td class="trow1" style="text-align: center;">
						<div id="{$template_var}"></div>
						<script type="text/javascript">
						<!--
							new ASB.modules.Slideshow('{$template_var}', {
								shuffle: {$shuffle},
								folder: '{$folder}',
								images: [{$filenames}],
								size: {$width},
								rate: {$rate},
								fadeRate: {$fade_rate},
							});
						// -->
						</script>
					</td>
				</tr>{$footer}
EOF;

	return true;
}

?>
