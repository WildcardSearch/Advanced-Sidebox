<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains data used by classes/installer.php
 */

$tables = array(
	"asb_sideboxes" => array(
		"id" => 'INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY',
		"display_order" => 'INT(10) NOT NULL',
		"box_type" => 'VARCHAR(25) NOT NULL',
		"title" => 'VARCHAR(32) NOT NULL',
		"position" => 'INT(2)',
		"scripts" => 'TEXT',
		"groups" => 'TEXT',
		"settings" => 'TEXT',
		"wrap_content" => 'INT(1)',
		"dateline" => 'INT(10)'
	),
	"asb_custom_sideboxes" => array(
		"id" => 'INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY',
		"title" => 'VARCHAR(32) NOT NULL',
		"description" => 'VARCHAR(128) NOT NULL',
		"wrap_content" => 'INT(1)',
		"content" => 'TEXT',
		"dateline" => 'INT(10)'
	),
	"asb_script_info" => array(
		"id" => 'INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY',
		"title" => 'VARCHAR(32) NOT NULL',
		"filename" => 'VARCHAR(32) NOT NULL',
		"action" => 'VARCHAR(32) NOT NULL',
		"page" => 'VARCHAR(32) NOT NULL',
		"width_left" => 'INT(2)',
		"width_right" => 'INT(2)',
		"template_name" => 'VARCHAR(128) NOT NULL',
		"hook" => 'VARCHAR(128) NOT NULL',
		"find_top" => 'TEXT',
		"find_bottom" => 'TEXT',
		"replace_all" => 'INT(1)',
		"replacement" => 'TEXT',
		"replacement_template" => 'VARCHAR(128) NOT NULL',
		"eval" => 'INT(1)',
		"active" => 'INT(1)',
		"dateline" => 'INT(10)'
	)
);

$columns = array(
	"users" => array(
		"show_sidebox" => 'INT(1) DEFAULT 1'
	)
);

$update_themes_link = "<ul><li><a href=\"" . ASB_URL . "&amp;action=update_theme_select\" title=\"\">{$lang->asb_theme_exclude_select_update_link}</a><br />{$lang->asb_theme_exclude_select_update_description}</li></ul>";

$settings = array(
	"asb_settings" => array(
		"group" => array(
			"name" => "asb_settings",
			"title" => "Advanced Sidebox",
			"description" => $lang->asb_settingsgroup_description,
			"disporder" => "101",
			"isdefault" => 0
		),
		"settings" => array(
			"asb_show_empty_boxes" => array(
				"sid" => "NULL",
				"name" => "asb_show_empty_boxes",
				"title" => $lang->asb_show_empty_boxes . ":",
				"description" => $db->escape_string($lang->asb_show_empty_boxes_desc),
				"optionscode" => "yesno",
				"value" => '1',
				"disporder" => '10'
			),
			"asb_show_toggle_icons" => array(
				"sid" => "NULL",
				"name" => "asb_show_toggle_icons",
				"title" => $lang->asb_show_toggle_icons,
				"description" => '',
				"optionscode" => "yesno",
				"value" => '0',
				"disporder" => '20'
			),
			"asb_show_expanders" => array(
				"sid" => "NULL",
				"name" => "asb_show_expanders",
				"title" => $lang->asb_show_expanders,
				"description" => '',
				"optionscode" => "yesno",
				"value" => '1',
				"disporder" => '30'
			),
			"asb_allow_user_disable" => array(
				"sid" => "NULL",
				"name" => "asb_allow_user_disable",
				"title" => $lang->asb_allow_user_disable,
				"description" => '',
				"optionscode" => "yesno",
				"value" => '1',
				"disporder" => '40'
			),
			"asb_minify_js" => array(
				"sid" => "NULL",
				"name" => "asb_minify_js",
				"title" => $lang->asb_minify_js_title,
				"description" => $lang->asb_minify_js_desc,
				"optionscode" => "yesno",
				"value" => '1',
				"disporder" => '50'
			),
			"asb_exclude_theme" => array(
				"sid" => "NULL",
				"name" => "asb_exclude_theme",
				"title" => $lang->asb_theme_exclude_list . ":",
				"description" => $db->escape_string($lang->asb_theme_exclude_list_description . $update_themes_link),
				"optionscode" => $db->escape_string(asb_build_theme_exclude_select()),
				"value" => '',
				"disporder" => '60'
			)
		)
	)
);

$templates = array(
	"asb" => array(
		"group" => array(
			"prefix" => 'asb',
			"title" => $lang->asb,
		),
		"templates" => array(
			"asb_begin" => <<<EOF
<table width="100%" border="0" cellspacing="5">
	<tr>{\$left_content}
		<!-- start: ASB middle column (page contents of {\$filename}) -->
		<td width="auto" valign="top">
EOF
			,
			"asb_end" => <<<EOF
		</td>
		<!-- end: ASB middle column (page contents of {\$filename}) -->{\$right_content}
</tr>
</table>
EOF
			,
			"asb_sidebox_column" => <<<EOF
			<td style="width: {\$width}px;{\$show_column}" id="{\$column_id}" valign="top">
				{\$sideboxes}
				{\$content_pad}
			</td>
EOF
			,
			"asb_wrapped_sidebox" => <<<EOF
		<table id="{\$sidebox['id']}" style="table-layout: fixed; word-wrap: break-word;" border="0" cellspacing="{\$theme['borderwidth']}" cellpadding="{\$theme['tablespace']}" class="tborder {\$sidebox['class']}">
			<thead>
				<tr>
					<td class="thead">
{\$expander}
						<strong>{\$sidebox['title']}</strong>
					</td>
				</tr>
			</thead>
			<tbody style="{\$expdisplay}" id="{\$sidebox['expdisplay_id']}">
		{\$sidebox['content']}
			</tbody>
		</table><br />
EOF
			,
			"asb_toggle_icon" => <<<EOF
			<td valign="top">
				<a id="{\$column_id}" href="javascript:void()"><img id="{\$closed_id}" src="{\$close_image}" title="{\$lang->asb_toggle_hide}" alt="{\$close_alt}" style="{\$close_style}position: relative; top: 13px; left: 3px;"/><img id="{\$open_id}" src="{\$open_image}" title="{\$lang->asb_toggle_show}" alt="{\$open_alt}" style="{\$open_style}position: relative; top: 13px; left: 3px;"/></a>
			</td>
EOF
			,
			"asb_content_pad" => <<<EOF

		<img src="inc/plugins/asb/images/transparent.gif" style="width: {\$width}px;" height="1" alt=""/>
EOF
			,
			"asb_expander" => <<<EOF
						<div class="expcolimage">
							<img src="{\$theme['imgdir']}/{\$expcolimage}" id="{\$sidebox['expcolimage_id']}" class="expander" alt="{\$expaltext}" title="{\$expaltext}"/>
						</div>
EOF
		),
	),
);

?>
