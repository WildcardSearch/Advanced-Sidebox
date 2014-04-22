<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains class definitions for the template handling and editing system
 */

class ASBTemplateHandler
{
	static public function edit($boxes, $width, $script)
	{
		global $mybb, $lang, $templates, $headerinclude;

		if($mybb->settings['asb_minify_js'])
		{
			$min = '.min';
		}

		$left_insert = $boxes[0];
		$right_insert = $boxes[1];
		$width_left = $width[0];
		$width_right = $width[1];
		$toggles = $show = array();
		$filename = THIS_SCRIPT;

		// if admin wants to show the toggle icons . . .
		if($mybb->settings['asb_show_toggle_icons'])
		{
			// we will need this js
			$headerinclude .= <<<EOF
<script type="text/javascript" src="jscripts/asb{$min}.js"></script>
EOF;

			$toggle_info['left'] = array(
				"close" => array(
					"img" => 'inc/plugins/asb/images/left_arrow.png',
					"alt" => '&lt;'
				),
				"open" => array(
					"img" => 'inc/plugins/asb/images/right_arrow.png',
					"alt" => '&gt;'
				)
			);
			$toggle_info['right']['close'] = $toggle_info['left']['open'];
			$toggle_info['right']['open'] = $toggle_info['left']['close'];

			foreach(array('left', 'right') as $key)
			{
				// check the cookie
				if($mybb->cookies["asb_hide_{$key}"] == 1)
				{
					// hide left
					$show[$key] = $close_style = 'display: none; ';
					$open_style = '';
				}
				else
				{
					// show left
					$close_style = '';
					$open_style = 'display: none; ';
				}

				// produce the link
				$open_image = $toggle_info[$key]['open']['img'];
				$close_image = $toggle_info[$key]['close']['img'];
				$open_alt = $toggle_info[$key]['open']['alt'];
				$close_alt = $toggle_info[$key]['close']['alt'];
				$column_id = "asb_hide_column_{$key}";
				$closed_id = "asb_{$key}_close";
				$open_id = "asb_{$key}_open";

				eval("\$toggles[\$key] = \"" . $templates->get('asb_toggle_icon') . "\";");
			}
		}

		foreach(array('left', 'right') as $key)
		{
			// if there is content
			$var_name = "{$key}_insert";
			if($$var_name)
			{
				$prop_name = "{$key}_content";
				$width_name = "width_{$key}";
				$width = $$width_name;
				$show_column = $show[$key];
				$column_id = "asb_{$key}_column_id";
				$insert_name = "{$key}_insert";
				$sideboxes = $$insert_name;

				eval("\$content_pad = \"" . $templates->get('asb_content_pad') . "\";");
				eval("\$content = \"" . $templates->get('asb_sidebox_column') . "\";");

				$toggle_left = $toggle_right = '';
				$toggle_name = "toggle_{$key}";
				$$toggle_name = $toggles[$key];

				// finally set $POSITION_content for ::make_edits()
				$$prop_name = <<<EOF

			<!-- start: ASB {$key} column -->{$toggle_left}
			{$content}
			<!-- end: ASB {$key} column -->{$toggle_right}
EOF;
			}
		}
		eval("\$insert_top = \"" . $templates->get('asb_begin') . "\";");
		eval("\$insert_bottom = \"" . $templates->get('asb_end') . "\";");

		if(is_array($script['extra_scripts']) && !empty($script['extra_scripts']))
		{
			$sep = '';
			$dateline = TIME_NOW;
			foreach($script['extra_scripts'] as $addon => $info)
			{
				// build the JS objects to pass to the custom object builder
				$extra_scripts .= <<<EOF
{$sep}{ addon: '{$addon}', id: {$info['id']}, position: {$info['position']}, rate: {$info['rate']}, dateline: {$dateline} }
EOF;
				$sep = ", ";
			}

			$location = get_current_location();
			$headerinclude .= <<<EOF

<script type="text/javascript" src="jscripts/asb_xmlhttp{$min}.js"></script>
<script type="text/javascript">
<!--
	Event.observe(window, 'load', function() {
		ASB.ajax.buildUpdaters([ {$extra_scripts} ], { left: {$width_left}, right: {$width_right} }, '{$location}');
	});
// -->
</script>
EOF;
		}

		if(is_array($script['js'])) {
			foreach($script['js'] as $script_name) {
				if(file_exists(MYBB_ROOT . "jscripts/asb/{$script_name}{$min}.js"))
				{
					$script_name .= $min;
				}
				$headerinclude .= <<<EOF

<script type="text/javascript" src="jscripts/asb/{$script_name}.js"></script>
EOF;
			}
		}

		// replace everything on the page?
		if($script['replace_all'] == true)
		{
			// if there is content
			if($script['replacement'])
			{
				// replace the existing page entirely
				$templates->cache[$script['template_name']] = str_replace(array('{$asb_left}', '{$asb_right}'), array($insert_top, $insert_bottom), $script['replacement']);
			}
		}
		// outputting to variables? (custom script/Page Manager)
		elseif($script['eval'])
		{
			// globalize our columns
			global $asb_left, $asb_right;

			// globalize all the add-on template variables
			if(is_array($script['template_vars']) && !empty($script['template_vars']))
			{
				foreach($script['template_vars'] as $var)
				{
					global $$var;
				}
			}

			// now eval() their content for the custom script
			eval("\$asb_left = \"" . str_replace("\\'", "'", addslashes($insert_top)) . "\";");
			eval("\$asb_right = \"" . str_replace("\\'", "'", addslashes($insert_bottom)) . "\";");
		}
		// otherwise we are editing the template in the cache
		else
		{
			// if there are columns stored
			if($insert_top || $insert_bottom)
			{
				// make the edits
				$find_top_pos = strpos($templates->cache[$script['template_name']], $script['find_top']);

				if($find_top_pos !== false)
				{
					$find_bottom_pos = strpos($templates->cache[$script['template_name']], $script['find_bottom']);

					if($find_bottom_pos !== false)
					{
						/*
						 * split the template in 3 parts and splice our columns in after 1 and before 3
						 * it is important that we function this way so we can work with the
						  * FIRST instance of the search text (find_top and find_bottom) rather
						  * than replacing multiple found instances
						 */
						$templates->cache[$script['template_name']] =
							substr($templates->cache[$script['template_name']], 0, $find_top_pos + strlen($script['find_top'])) .
							$insert_top .
							substr($templates->cache[$script['template_name']], $find_top_pos + strlen($script['find_top']), $find_bottom_pos - ($find_top_pos + strlen($script['find_top']))) .
							$insert_bottom .
							substr($templates->cache[$script['template_name']], $find_bottom_pos);
					}
				}
			}
		}
	}
}

?>
