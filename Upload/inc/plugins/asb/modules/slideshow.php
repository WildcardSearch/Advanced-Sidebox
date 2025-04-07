<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * https://www.rantcentralforums.com
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
		'title' => $lang->asb_slideshow,
		'description' => $lang->asb_slideshow_desc,
		'wrap_content' => true,
		'version' => '2.0.0',
		'compatibility' => '4.0',
		'debugMode' => true,
		'noContentTemplate' => 'asb_slideshow_no_content',
		'scripts' => array(
			'Slideshow',
		),
		'settings' => array(
			'folder' => array(
				'name' => 'folder',
				'title' => $lang->asb_slideshow_folder_title,
				'description' => $lang->asb_slideshow_folder_description,
				'optionscode' => 'text',
				'value' => 'images',
			),
			'recursive' => array(
				'name' => 'recursive',
				'title' => $lang->asb_slideshow_recursive_title,
				'description' => $lang->asb_slideshow_recursive_description,
				'optionscode' => 'yesno',
				'value' => '0',
			),
			'rate' => array(
				'name' => 'rate',
				'title' => $lang->asb_slideshow_rate_title,
				'description' => $lang->asb_slideshow_rate_description,
				'optionscode' => 'text',
				'value' => '10',
			),
			'shuffle' => array(
				'name' => 'shuffle',
				'title' => $lang->asb_slideshow_shuffle_title,
				'description' => $lang->asb_slideshow_shuffle_description,
				'optionscode' => 'yesno',
				'value' => '1',
			),
			'fade_rate' => array(
				'name' => 'fade_rate',
				'title' => $lang->asb_slideshow_fade_rate_title,
				'description' => $lang->asb_slideshow_fade_rate_description,
				'optionscode' => 'text',
				'value' => '1',
			),
			'footer_text' => array(
				'name' => 'footer_text',
				'title' => $lang->asb_slideshow_footer_text_title,
				'description' => $lang->asb_slideshow_footer_text_description,
				'optionscode' => 'text',
				'value' => '',
			),
			'footer_url' => array(
				'name' => 'footer_url',
				'title' => $lang->asb_slideshow_footer_url_title,
				'description' => $lang->asb_slideshow_footer_url_description,
				'optionscode' => 'text',
				'value' => '',
			),
			'height' => array(
				'name' => 'height',
				'title' => $lang->asb_slideshow_height_title,
				'description' => $lang->asb_slideshow_height_description,
				'optionscode' => 'text',
				'value' => '200',
			),
		),
		'installData' => array(
			'templates' => array(
				array(
					'title' => 'asb_slideshow',
					'template' => <<<EOF
					<div class="trow1 asb-slideshow-container">
						<div id="{\$template_var}" class="asb-slideshow-image-container">
							<div class="asb-slideshow-image asb-slideshow-image-one"></div>
							<div class="asb-slideshow-image asb-slideshow-image-two"></div>
						</div>
					</div>{\$footer}
<script type="text/javascript">
<!--
	new ASB.modules.Slideshow(\'{\$template_var}\', {
		folder: \'{\$folder}\',
		images: {\$filenames},
		rate: {\$rate},
		fadeRate: {\$fade_rate},
		height: {\$height},
	});
// -->
</script>
EOF
				),
				array(
					'title' => 'asb_slideshow_footer',
					'template' => <<<EOF

				<div class="tfoot asb-slideshow-footer">
					<a style="font-weight: bold;" href="{\$settings[\'footer_url\']}">{\$settings[\'footer_text\']}</a>
				</div>
EOF
				),
				array(
					'title' => 'asb_slideshow_no_content',
					'template' => <<<EOF
<div class="asb-no-content-message">{\$lang->asb_slideshow_no_images}</div>

EOF
				),
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
function asb_slideshow_get_content($settings, $script, $dateline, $template_var)
{
	global $mybb, $templates;

	$shuffle = $settings['shuffle'] ? 'true' : 'false';
	$folder = $settings['folder'];
	$rate = (int) $settings['rate'] ? (int) $settings['rate'] : 10;
	$fade_rate = (float) $settings['fade_rate'] ? (int) ($settings['fade_rate'] * 1000) : 400;

	$filenames = asbGetImagesFromPath($folder, '', $settings['recursive']);

	if (!is_array($filenames) ||
		empty($filenames)) {
		return false;
	}

	if ($shuffle) {
		shuffle($filenames);
	}

	$filenames = json_encode($filenames);

	if ($settings['footer_text'] && $settings['footer_url']) {
		eval("\$footer = \"{$templates->get('asb_slideshow_footer')}\";");
	}

	$height = (int) $settings['height'];

	eval("\$content = \"{$templates->get('asb_slideshow')}\";");

	return $content;
}