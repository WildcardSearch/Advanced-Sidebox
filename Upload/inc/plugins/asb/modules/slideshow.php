<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * ASB default module
 */

// include a check for Advanced Sidebox
if (!defined('IN_MYBB') ||
	!defined('IN_ASB')) {
	die('Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.');
}

/**
 * provide info to ASB about the addon
 *
 * @return array module info
 */
function asb_slideshow_info()
{
	global $lang;

	if (!$lang->asb_addon) {
		$lang->load('asb_addon');
	}

	return array(
		"title" => $lang->asb_slideshow,
		"description" => $lang->asb_slideshow_desc,
		"wrap_content" => true,
		"version" => '1.1',
		"compatibility" => '2.1',
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
				"value" => 'images',
			),
			"recursive" => array(
				"sid" => 'NULL',
				"name" => 'recursive',
				"title" => $lang->asb_slideshow_recursive_title,
				"description" => $lang->asb_slideshow_recursive_description,
				"optionscode" => 'yesno',
				"value" => '0',
			),
			"rate" => array(
				"sid" => 'NULL',
				"name" => 'rate',
				"title" => $lang->asb_slideshow_rate_title,
				"description" => $lang->asb_slideshow_rate_description,
				"optionscode" => 'text',
				"value" => '10',
			),
			"shuffle" => array(
				"sid" => 'NULL',
				"name" => 'shuffle',
				"title" => $lang->asb_slideshow_shuffle_title,
				"description" => $lang->asb_slideshow_shuffle_description,
				"optionscode" => 'yesno',
				"value" => '1',
			),
			"fade_rate" => array(
				"sid" => 'NULL',
				"name" => 'fade_rate',
				"title" => $lang->asb_slideshow_fade_rate_title,
				"description" => $lang->asb_slideshow_fade_rate_description,
				"optionscode" => 'text',
				"value" => '1',
			),
			"footer_text" => array(
				"sid" => 'NULL',
				"name" => 'footer_text',
				"title" => $lang->asb_slideshow_footer_text_title,
				"description" => $lang->asb_slideshow_footer_text_description,
				"optionscode" => 'text',
				"value" => '',
			),
			"footer_url" => array(
				"sid" => 'NULL',
				"name" => 'footer_url',
				"title" => $lang->asb_slideshow_footer_url_title,
				"description" => $lang->asb_slideshow_footer_url_description,
				"optionscode" => 'text',
				"value" => '',
			),
			"max_width" => array(
				"sid" => 'NULL',
				"name" => 'max_width',
				"title" => $lang->asb_slideshow_max_width_title,
				"description" => $lang->asb_slideshow_max_width_description,
				"optionscode" => 'text',
				"value" => '',
			),
			"max_height" => array(
				"sid" => 'NULL',
				"name" => 'max_height',
				"title" => $lang->asb_slideshow_max_height_title,
				"description" => $lang->asb_slideshow_max_height_description,
				"optionscode" => 'text',
				"value" => '',
			),
			"maintain_height" => array(
				"sid" => 'NULL',
				"name" => 'maintain_height',
				"title" => $lang->asb_slideshow_maintain_height_title,
				"description" => $lang->asb_slideshow_maintain_height_description,
				"optionscode" => 'yesno',
				"value" => '1',
			),
		),
		"templates" => array(
			array(
				"title" => 'asb_slideshow',
				"template" => <<<EOF
				<tr>
					<td class="trow1" style="text-align: center;">
						<div id="{\$template_var}"></div>
						<script type="text/javascript">
						<!--
							new ASB.modules.Slideshow(\'{\$template_var}\', {
								shuffle: {\$shuffle},
								folder: \'{\$folder}\',
								images: [{\$filenames}],
								size: {\$width},
								rate: {\$rate},
								fadeRate: {\$fade_rate},
								maxWidth: {\$max_width},
								maxHeight: {\$max_height},
								maintainHeight: {\$maintain_height},
							});
						// -->
						</script>
					</td>
				</tr>{\$footer}
EOF
			),
			array(
				"title" => "asb_slideshow_footer",
				"template" => <<<EOF

				<tr>
					<td class="tfoot">
						<div style="text-align: center;">
							<a style="font-weight: bold;" href="{\$settings[\'footer_url\']}">{\$settings[\'footer_text\']}</a>
						</div>
					</td>
				</tr>
EOF
			),
		),
	);
}

/**
 * handles display of children of this addon at page load
 *
 * @param  array info from child box
 * @return bool success/fail
 */
function asb_slideshow_build_template($args)
{
	extract($args);

	global $$template_var, $mybb, $templates;

	$shuffle = $settings['shuffle'] ? 'true' : 'false';
	$folder = $settings['folder'];
	$rate = (int) $settings['rate'] ? (int) $settings['rate'] : 10;
	$fade_rate = (float) $settings['fade_rate'] ? (int) ($settings['fade_rate'] * 1000) : 400;

	$filenames = asb_get_folder_images($folder, '', $settings['recursive']);
	if (!$filenames) {
		$$template_var = <<<EOF
		<tr>
			<td class="trow1">{$lang->asb_slideshow_no_images}</td>
		</tr>
EOF;
		return false;
	}

	if ($settings['footer_text'] && $settings['footer_url']) {
		eval ("\$footer = \"" . $templates->get('asb_slideshow_footer') . "\";");
	}

	$max_width = (int) $settings['max_width'];
	$max_height = (int) $settings['max_height'];
	$maintain_height = (int) $settings['maintain_height'];

	$width = $width * .9;
	eval("\$\$template_var = \"" . $templates->get('asb_slideshow') . "\";");

	return true;
}

?>
