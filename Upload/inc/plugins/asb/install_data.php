<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * https://www.rantcentralforums.com
 *
 * this file contains data used by classes/installer.php
 */

$tables = array(
	'pgsql' => array(
		'asb_sideboxes' => array(
			'id' => 'SERIAL',
			'display_order' => 'INT NOT NULL',
			'box_type' => 'VARCHAR(25) NOT NULL',
			'title' => 'TEXT',
			'title_link' => 'VARCHAR(128) NOT NULL',
			'position' => 'INT',
			'scripts' => 'TEXT',
			'groups' => 'TEXT',
			'themes' => 'TEXT',
			'settings' => 'TEXT',
			'wrap_content' => 'INT',
			'dateline' => 'INT NOT NULL, PRIMARY KEY(id)',
		),
		'asb_custom_sideboxes' => array(
			'id' => 'SERIAL',
			'title' => 'VARCHAR(32) NOT NULL',
			'description' => 'VARCHAR(128) NOT NULL',
			'wrap_content' => 'INT',
			'content' => 'TEXT',
			'dateline' => 'INT NOT NULL, PRIMARY KEY(id)',
		),
		'asb_script_info' => array(
			'id' => 'SERIAL',
			'tid' => 'INT NOT NULL DEFAULT 0',
			'title' => 'VARCHAR(32) NOT NULL',
			'filename' => 'VARCHAR(32) NOT NULL',
			'action' => 'VARCHAR(32) NOT NULL',
			'page' => 'VARCHAR(32) NOT NULL',
			'width_left' => 'VARCHAR(32) NOT NULL',
			'left_margin' => 'VARCHAR(32) NOT NULL',
			'width_middle' => 'VARCHAR(32) NOT NULL',
			'right_margin' => 'VARCHAR(32) NOT NULL',
			'width_right' => 'VARCHAR(32) NOT NULL',
			'template_name' => 'VARCHAR(128) NOT NULL',
			'hook' => 'VARCHAR(128) NOT NULL',
			'find_top' => 'TEXT',
			'find_bottom' => 'TEXT',
			'replace_all' => 'INT',
			'replacement' => 'TEXT',
			'replacement_template' => 'VARCHAR(128) NOT NULL',
			'eval' => 'INT',
			'active' => 'INT',
			'mobile_disabled' => 'INT',
			'dateline' => 'INT NOT NULL, PRIMARY KEY(id)',
		),
	),
	'asb_sideboxes' => array(
		'id' => 'INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY',
		'display_order' => 'INT(10) NOT NULL',
		'box_type' => 'VARCHAR(25) NOT NULL',
		'title' => 'TEXT',
		'title_link' => 'VARCHAR(128) NOT NULL',
		'position' => 'INT(2)',
		'scripts' => 'TEXT',
		'groups' => 'TEXT',
		'themes' => 'TEXT',
		'settings' => 'TEXT',
		'wrap_content' => 'INT(1)',
		'dateline' => 'INT(10)',
	),
	'asb_custom_sideboxes' => array(
		'id' => 'INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY',
		'title' => 'VARCHAR(32) NOT NULL',
		'description' => 'VARCHAR(128) NOT NULL',
		'wrap_content' => 'INT(1)',
		'content' => 'TEXT',
		'dateline' => 'INT(10)',
	),
	'asb_script_info' => array(
		'id' => 'INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY',
		'tid' => 'INT(10) NOT NULL DEFAULT 0',
		'title' => 'VARCHAR(32) NOT NULL',
		'filename' => 'VARCHAR(32) NOT NULL',
		'action' => 'VARCHAR(32) NOT NULL',
		'page' => 'VARCHAR(32) NOT NULL',
		'width_left' => 'VARCHAR(32) NOT NULL',
		'left_margin' => 'VARCHAR(32) NOT NULL',
		'width_middle' => 'VARCHAR(32) NOT NULL',
		'right_margin' => 'VARCHAR(32) NOT NULL',
		'width_right' => 'VARCHAR(32) NOT NULL',
		'template_name' => 'VARCHAR(128) NOT NULL',
		'hook' => 'VARCHAR(128) NOT NULL',
		'find_top' => 'TEXT',
		'find_bottom' => 'TEXT',
		'replace_all' => 'INT(1)',
		'replacement' => 'TEXT',
		'replacement_template' => 'VARCHAR(128) NOT NULL',
		'eval' => 'INT(1)',
		'active' => 'INT(1)',
		'mobile_disabled' => 'INT(1)',
		'dateline' => 'INT(10)',
	),
);

$columns = array(
	'pgsql' => array(
		'users' => array(
			'show_sidebox' => 'INT DEFAULT 1',
		),
	),
	'users' => array(
		'show_sidebox' => 'INT(1) DEFAULT 1',
	),
);

$updateThemeExcludeLink = "<ul><li><a href=\"".ASB_URL."&amp;action=update_theme_select\" title=\"\">{$lang->asb_theme_exclude_select_update_link}</a><br />{$lang->asb_theme_exclude_select_update_description}</li></ul>";

$settings = array(
	'asb_settings' => array(
		'group' => array(
			'name' => 'asb_settings',
			'title' => 'Advanced Sidebox',
			'description' => $lang->asb_settingsgroup_description,
			'disporder' => '101',
			'isdefault' => 0,
		),
		'settings' => array(
			'asb_show_empty_boxes' => array(
				'sid' => '0',
				'name' => 'asb_show_empty_boxes',
				'title' => $lang->asb_show_empty_boxes.':',
				'description' => $db->escape_string($lang->asb_show_empty_boxes_desc),
				'optionscode' => 'yesno',
				'value' => '1',
				'disporder' => '10',
			),
			'asb_show_toggle_icons' => array(
				'sid' => '0',
				'name' => 'asb_show_toggle_icons',
				'title' => $lang->asb_show_toggle_icons,
				'description' => '',
				'optionscode' => 'yesno',
				'value' => '0',
				'disporder' => '20',
			),
			'asb_show_expanders' => array(
				'sid' => '0',
				'name' => 'asb_show_expanders',
				'title' => $lang->asb_show_expanders,
				'description' => '',
				'optionscode' => 'yesno',
				'value' => '1',
				'disporder' => '30',
			),
			'asb_allow_user_disable' => array(
				'sid' => '0',
				'name' => 'asb_allow_user_disable',
				'title' => $lang->asb_allow_user_disable,
				'description' => '',
				'optionscode' => 'yesno',
				'value' => '1',
				'disporder' => '40',
			),
			'asb_minify_js' => array(
				'sid' => '0',
				'name' => 'asb_minify_js',
				'title' => $lang->asb_minify_js_title,
				'description' => $lang->asb_minify_js_desc,
				'optionscode' => 'yesno',
				'value' => '1',
				'disporder' => '50',
			),
			'asb_exclude_theme' => array(
				'sid' => '0',
				'name' => 'asb_exclude_theme',
				'title' => $lang->asb_theme_exclude_list.':',
				'description' => $db->escape_string($lang->asb_theme_exclude_list_description.$updateThemeExcludeLink),
				'optionscode' => $db->escape_string(asbBuildThemeExcludeSelect()),
				'value' => '',
				'disporder' => '60',
			),
			'asb_disable_for_mobile' => array(
				'sid' => '0',
				'name' => 'asb_disable_for_mobile',
				'title' => $lang->asb_disable_for_mobile_title.':',
				'description' => $lang->asb_disable_for_mobile_description,
				'optionscode' => 'yesno',
				'value' => '0',
				'disporder' => '70',
			),
		)
	)
);

$templates = array(
	'asb' => array(
		'group' => array(
			'prefix' => 'asb',
			'title' => $lang->asb,
		),
		'templates' => array(
			'asb_begin' => <<<EOF
<div class="asb-sidebox-container">{\$left_content}
	<!-- start: ASB middle column (page contents of {\$filename}) -->
	<div id="asb_middle_column" class="asb-sidebox-column asb-sidebox-column-middle" style="width: {\$width_middle}%; margin-left: {\$left_margin}%; margin-right: {\$right_margin}%;">
EOF
			,
			'asb_end' => <<<EOF
	</div>
	<!-- end: ASB middle column (page contents of {\$filename}) -->{\$right_content}
</div>
EOF
			,
			'asb_sidebox_column' => <<<EOF
	{\$toggle_left}
	<div class="asb-sidebox-column{\$extraClass}" style="width: {\$width}%;{\$show_column}" id="{\$column_id}">{\$sideboxes}
	</div>{\$toggle_right}
EOF
			,
			'asb_wrapped_sidebox' => <<<EOF
		<div id="{\$sidebox['id']}" class="asb-wrapped-sidebox tborder {\$sidebox['class']}">
			<div class="thead">
				{\$expander}
				<strong>{\$sidebox['title']}</strong>
			</div>
			<div style="{\$expdisplay}" id="{\$sidebox['expdisplay_id']}">
				{\$sidebox['content']}
			</div>
		</div>
EOF
			,
			'asb_sidebox_no_content' => <<<EOF
				<div class="asb-no-content-message">{\$lang->asb_sidebox_no_content}</div>
EOF
			,
			"asb_toggle_icon" => <<<EOF
	<span class="asb-sidebox-toggle-column{\$positionClass}">
		<a id="{\$column_id}" href="javascript:void()"><img id="{\$closed_id}" src="{\$close_image}" title="{\$lang->asb_toggle_hide}" alt="{\$close_alt}" style="{\$close_style}"/><img id="{\$open_id}" src="{\$open_image}" title="{\$lang->asb_toggle_show}" alt="{\$open_alt}" style="{\$open_style}"/></a>
	</span>
EOF
			,
			"asb_expander" => <<<EOF
				<div class="expcolimage">
					<img src="{\$theme['imgdir']}/{\$expcolimage}" id="{\$sidebox['expcolimage_id']}" class="expander" alt="{\$expaltext}" title="{\$expaltext}"/>
				</div>
EOF
			,
			'asb_ucp_show_sidebox_option' => <<<EOF

	<tr>
		<td valign="top" width="1">
			<input type="checkbox" class="checkbox" name="showsidebox" id="showsidebox" value="1" {\$checked}/>
		</td>
		<td>
			<span class="smalltext"><label for="showsidebox">{\$lang->asb_show_sidebox}</label></span>
		</td>
	</tr>
EOF
		),
	),
);

$styleSheets = array(
	'folder' => 'asb',
	'forum' => array(
		'asb' => array(
			'attachedto' => '',
			'stylesheet' => <<<EOF
/*
 * add/edit rules here to style the side boxes
 */

/** Main Wrapper **/
div.asb-sidebox-container {
	width: 100%;

	padding: 0px;
	margin: 0px;

	font-size: 0;
	text-align: left;
	clear: both;
}

/** Toggle Icons Start **/

#asb_left_open,
#asb_right_close {
	position: relative;
	top: 13px;
	left: 10px;
}

#asb_left_close,
#asb_right_open {
	position: relative;
	top: 13px;
	left: 3px;
}

span.asb-sidebox-toggle-column {
	position: absolute;
}

span.asb-sidebox-toggle-column-left {
	margin-left: -17px;
}

span.asb-sidebox-toggle-column-right {
	
}

/** Toggle Icons End **/

/** Columns Start **/

	/**
	Important - column width and margin is controlled in the Manage Scripts page.
	Adjusting column width or margin can break HTML output!
	**/

div.asb-sidebox-column {
	display: inline-block;

	vertical-align: top;
	text-align: left;
}

div.asb-sidebox-column-left {
	
}

div.asb-sidebox-column-middle {
	
}

div.asb-sidebox-column-middle > * {
	/** Important - restores font size, adjust if necessary **/
	font-size: 14px;
}

div.asb-sidebox-column-middle > table.tborder,
div.asb-sidebox-column-middle > div.tborder {
	/** Important - keeps middle content fluid **/
	width: 100%;

	/** Important - removes any space above middle content **/
	margin-top: 0px!important;
}

div.asb-sidebox-column-right {
	/** Fix for "mystery pixels" **/
	margin-left: -4px;
}

div.asb-sidebox-column-left,
div.asb-sidebox-column-right {
	
}

/** Columns End **/

/** Side Boxes Start **/

div.asb-wrapped-sidebox {
	font-size: 14px;
	word-wrap: break-word;

	margin-bottom: 10px;
}

/** Side Boxes End **/

/** Modules Start **/

	/** Birthdays Start **/

div.asb-birthdays-header {
	
}

div.asb-birthdays-today-header {
	
}

div.asb-birthdays-upcoming-header {
	
}

div.asb-birthdays-no-birthdays {
	
}

div.asb-birthdays-user-avatar {
	display: inline-block;
	vertical-align: middle;

	width: 20%;
	padding-bottom: 20%;

	background-size: contain;
	background-position: center;
	background-repeat: no-repeat;
	background-color: transparent;
}

div.asb-birthdays-user-row > span {
	display: inline-block;
	vertical-align: middle;

	font-weight: bold;
}

	/** Birthdays End **/

	/** Forum Age Start **/

div.asb-forum-age-header {
	
}

div.asb-forum-age-footer {
	
}

span.asb-forum-age-text {
	font-weight: bold;
	font-size: 1.2em;
	color: #444444;
}

	/** Forum Age End **/

	/** Goals Start **/

div.asb-goals-progress-container {
	text-align: center;
}

div.asb-goals-progress-footer {
	text-align: center;
}

div.asb-goals-goal-reached-message {
	font-size: 1.6em;
	color: navy;
}

span.asb-goals-progress-message {
	font-size: 1.4em;
	color: green;
}

div.asb-goals-progress-indicator {
	width: 95%;
	height: 20px;

	margin: 2px auto;
	border: 2px outset grey;

	background-color: white;
}

div.asb-goals-progress-indicator-completed {
	background: blue;
	height: 20px;
}

img.asb-goals-success-image {
	
}

	/** Goals End **/

	/** Latest Threads Start **/

div.asb-latest-threads-thread {
	
}

span.asb-latestest-threads-thread-title {
	font-weight: bold;
}

div.asb-latest-threads-container {
	font-size: 0;
}

div.asb-latest-threads-title-container {
	display: inline-block;
	vertical-align: top;

	width: 60%;

	font-size: 14px;

	margin-left: 2%;
	margin-right: 2%;
}

div.asb-latest-threads-last-post-container {
	display: inline-block;

	width: 20%;
}

div.asb-latest-threads-last-post-container a {
	font-size: 12px;
	font-weight: bold;
}

a.asb-latest-threads-thread-gotounread {
	
}

a.latest-threads-last-post-link {
	
}

a.asb-latest-threads-last-poster-avatar {
	display: inline-block;

	width: 15%;
	padding-bottom: 15%;

	background-size: contain;
	background-position: center;
	background-repeat: no-repeat;
	background-color: transparent;

	border-radius: 50%;
	border: 1px solid lightgrey;
}

	/** Latest Threads End **/

	/** Private Messages Start **/

div.asb-private-messages-container {
	
}

div.asb-private-messages-overview {
	text-align: center;

	padding: 2.5%;
}

div.asb-private-messages-links {
	
}

div.asb-private-messages-links a,
div.asb-private-messages-links a:hover {
	text-decoration: none;
	font-weight: bold;
}

	/** Privates Messages End **/

	/** Random Quotes Start **/

div.asb-random-quote-header {
	
}

div.asb-random-quote-user-info {
	
}

img.asb-random-quote-user-avatar {
	padding: 4px;
	width: 15%;
	vertical-align: middle;
	max-height: 30px;
}

a.asb-random-quote-user-link {
	vertical-align: middle;
}

div.asb-random-quote-message {
	
}

div.asb-random-quote-footer {
	text-align: center;
}

a.asb-random-quote-thread_title_link {
	
}

a.asb-random-quote-thread_title_link span {
	font-weight: bold;
}

	/** Random Quotes End **/

	/** Recent Posts Start **/

div.asb-recent-posts-title {
	clear: both;

	text-align: center; 
}

div.asb-recent-posts-excerpt {
	padding: 2.5%;
}

div.asb-recent-posts-author {
	text-align: right;
}

div.asb-recent-posts-author span {
	padding-right: 2.5%;
}

	/** Recent Posts End **/

	/** Search Start **/

div.asb-search-container {
	
}

input.asb-search-keywords {
	width: 80%;
}

input.asb-search-go-button {
	width: 13%;
}

div.asb-search-advanced {
	text-align: center;
	font-weight: bold;
}

div.asb-search-advanced > span > a,
div.asb-search-advanced > span > a:hover {
	text-decoration: none;
}

	/** Search End **/

	/** Slideshow Start **/

div.asb-slideshow-container {
	text-align: center;
}

div.tfoot asb-slideshow-footer {
	text-align: center;
}

div.asb-slideshow-image-container {
	position: relative;
}

div.asb-slideshow-image {
	position: absolute;

	top: 0px;
	left: 0px;

	width: 100%;
	height: 100%;

	background-color: transparent;
	background-position: center;
	background-size: cover;
	background-repeat: no-repeat;
}

div.asb-slideshow-image-one {
	
}

div.asb-slideshow-image-two {
	z-index: 99;
}

	/** Slideshow End **/

	/** Staff Online Start **/

div.asb-staff-online-row {
	width: 100%;

	font-size: 0;
	padding: 0px;
	margin: 0px;
}

a.asb-staff-online-avatar,
div.asb-staff-online-username,
div.asb-staff-online-badge {
	text-align: center;
}

a.asb-staff-online-avatar,
div.asb-staff-online-user-container {
	font-size: 14px;

	display: inline-block;
}

a.asb-staff-online-avatar {
	width: 19%;
	padding-bottom: 19%;
}

div.asb-staff-online-user-container {
	width: 79%;

	vertical-align: top;
}

div.asb-staff-online-username {
	width: 100%;

	margin-top: 2%;
}

div.asb-staff-online-badge {
	width: 100%;
}

div.asb-staff-online-badge > img {
	width: 60%;
}

	/** Staff Online End **/

	/** Statistics Start **/

div.asb-statistics-container {
	
}

ul.asb-statistics-list li {
	list-style: none;
}

div.asb-statistics-full-link {
	text-align: center;
}

div.asb-statistics-full-link a {
	font-weight: bold;
}

div.asb-statistics-full-link a,
div.asb-statistics-full-link a:hover {
	text-decoration: none;
}

	/** Statistics End **/

	/** Top Poster Start **/

div.asb-top-poster-description {
	font-size: .8em;
	text-align: center;
}

div.asb-top-poster-posters {
	padding: 0px;
}

div.asb-top-poster-posters-single {
	text-align: center;
}

div.asb-top-poster-poster-text,
div.asb-top-poster-poster-avatar {
	display: inline-block;
}

div.asb-top-poster-poster-avatar {
	width: 14%;

	padding-top: 0px;
	padding-bottom: 0px;
}

div.asb-top-poster-poster-text {
	width: 84%;
	font-size: 1em;

	padding-top: 0px;
	padding-bottom: 0px;
}

div.asb-top-poster-avatar {
	width: 90%;

	padding-bottom: 90%;
}

div.asb-top-poster-avatar-single {
	width: 80%;

	padding-bottom: 80%;
	margin: 2.5% auto;
}

div.asb-top-poster-avatar,
div.asb-top-poster-avatar-single {
	background-size: contain;
	background-position: center;
	background-repeat: no-repeat;
	background-color: transparent;
}

	/** Top Poster End **/

	/** Welcome Start **/

div.asb-welcome-info {
	
}

ul.asb-welcome-info-list li {
	list-style: none;
}

div.asb-welcome-links {
	text-align: center;

	clear: both;
}

div.asb-welcome-links > a {
	font-weight: bold;
}

div.asb-welcome-links > a,
div.asb-welcome-links > a:hover {
	text-decoration: none;
}

div.asb-welcome-user-avatar {
	width: 20%;
	padding-bottom: 20%;

	margin-top: 5%;
	margin-right: 5%;

	display: inline-block;
	float: right;

	background-size: contain;
	background-position: center;
	background-repeat: no-repeat;
	background-color: transparent;
}

div.asb-welcome-registration-form-container {
	
}

div.asb-welcome-registration-message {
	
}

	/** Welcome End **/

	/** Who's Online Start **/

div.asb-whosonline-container {
	
}

div.asb-whosonline-info {
	
}

div.asb-whosonline-users {
	
}

div.asb-whosonline-users.asb-whosonline-users-avatars-container {
	/** Important - will break user avatar layout if removed/changed **/
	font-size: 0;
}

div.asb-whosonline-users.asb-whosonline-users-links-container {
	
}

a.asb-whosonline-avatar-link {
	display: inline-block;
	overflow: hidden;

	padding: 0px;

	background-color: transparent;
	background-size: contain;
	background-position: center;
	background-repeat: no-repeat;

	/** uncomment for round avatars
	border-radius: 50%;
	**/
}

a.asb-whosonline-see-all-link {
	
}

	/** Who's Online End **/

/** Modules End **/

/** Responsive Adjustments **/

@media print,screen and (min-width:64em){
    
}

@media screen and (max-width:63.9375em) {

	div.asb-sidebox-container {
		text-align: center;
	}

	div.asb-sidebox-column-middle > table.tborder,
	div.asb-sidebox-column-middle > div.tborder,
	div.asb-sidebox-column {
		display: block;
		width: 90vw!important;
		margin: 20px;
	}

	div.asb-sidebox-column-middle {
		margin-left: 0px;
		margin-right: 0px;
	}

	span.asb-sidebox-toggle-column {
		display: none;
	}
}

@media screen and (max-width:39.9375em) {
   
}
EOF
		),
	),
	'acp' => array(
		'global' => array(
			'stylesheet' => <<<EOF
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains style information for the ACP pages
 */

/* sidebox management */

div.container {
	width: 100%;
	background: #ffffff;
}

table.content {
	text-align: center;
	width: 100%;
	table-layout: fixed;
	border-collapse: collapse;
}

/* components */

div.draggable {
	z-index: 50;
}

.custom_type, .box_type, .sidebox {
	cursor: move;
	font-size: 12px;
	padding: 3px;
	margin: 2px 1px;

	-webkit-user-select: none; /* Safari*/
	-khtml-user-select: none; /* Konqueror */
	-moz-user-select: none; /* Firefox */
	-ms-user-select: none; /* Internet Explorer/Edge */
	user-select: none; /* Chrome and Opera */

	border-radius: 3px;
	-moz-border-radius: 3px;
	-webkit-border-radius: 3px;
}

.box_type {
	background: #99e6ff;
	border: 1px solid #0086b3;
	/* text-align: center;
	line-height: 15px;
	text-shadow: 3px 3px 3px #070772; */
}

.custom_type {
	background: #ffffbf;
	border: 1px solid #b3b300;
	/* width: 93%;
	text-align: center;
	line-height: 15px;
	text-shadow: 3px 3px 3px #5a642f; */
}

.sidebox {
	background: #bfffbf;
	border: 1px solid #00b300;
	line-height: 25px;
	color: #000000;
	position: relative;
}

.column {
	border: 1px solid #aaaaaa;
}

td.hover {
	background: #eeeeee;
}

.trashcan {
	min-height: 80px;
}

.forum_column {
	width: 225px;
	min-height: 300px;
	padding: 5px 2px;
}

#addon_menu {
	border: 1px solid #aaaaaa;
}

#custom_menu {
	border: 1px solid #aaaaaa;
}

.column_head {
	background: #aaaaaa;
	color: #333;
	font-size: 1.5em;
}

/* visibility chart */

.tooltip {
	color: #000000;
	outline: none;
	cursor: help;
	text-decoration: none;
	position: relative;
}

.tooltip span {
	display: none;
	position: absolute;
	top: 0px;
	left: -300px;
	width: 300px;

	border-radius: 3px;
	-moz-border-radius: 3px;
	-webkit-border-radius: 3px;
}

.tooltip:hover span {
	display: inline;
}

.custom {
	z-index: 99;
}

* html a:hover {
	background: transparent;
}

.info {
	background: #99ccff;
	border: 1px solid #7396ff;
}

.info_icon {
	position: relative;
	float: left;
	padding: 4px;
}

.del_icon {
	position: relative;
	float: right;
	padding: 4px;
}

.box_info {
	background: #cccccc;
	color: #333;
	border: 1px solid #888888;
}

.box_info td {
	width: 10px;
	height: 10px;
}

.script_header {
	background: #ffffff;
	color: #000;

	height: 10px;
	text-align: right;
	padding-right: 10px;
}

.group_header {
	background: #ffffff;
	color: #000;
}

td.info_cell {
	height: 10px;
	width: 10px;
}

th.script_header {
	color: #000;
	height: 10px;
}

td.on {
	background: #cfffbf;
}

td.off {
	background: #ffffff;
}

/* footer menu */

.asb_label {
	color: #333;
	font-weight: bold;
	margin: auto auto;
	padding: 5px 0px;
	border: 1px solid #aaaaaa;
	text-align: center;

	border-radius: 3px;
	-moz-border-radius: 3px;
	-webkit-border-radius: 3px;
}

.asb_label a:hover,
.asb_label a:active {
	color: #333;
	text-decoration: none;
}

#file_info {
	color: red;
	font-weight: bold;
	margin-bottom: 5px;
}

/* Manage Scripts **/

	/** Inline Edit */

tr.asb-script-checked > td {
	background: #fffbd9!important;;
	color: #333;
	border-right-color: #f7e86a;
	border-bottom-color: #f7e86a;
}

input.asb-dimension-cell {
	width: 60%;
	margin-right: 2.5%;
}

EOF
		),
	),
);

$images = array(
	'folder' => 'asb',
	'forum' => array(
		'left_arrow.png' => array(
			'image' => <<<EOF
iVBORw0KGgoAAAANSUhEUgAAAAcAAAANCAYAAABlyXS1AAAAAXNSR0IArs4c6QAAAAZiS0dEAAAAAAAA+UO7fwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB90CDw82NZQk9AwAAABmSURBVBjTbdCxDcAgDETRP4i7dOyQkiWyCD0lG2QV5mAfp4gCtonbdyedDOaulDiPA6ACai2CutYHqrowwhjjxQillIURgNp7n+gAuC3OZs7ZN5vIFnCDYsAO+g1sT7CB7X1NxAUeTVxwi4e8uQsAAAAASUVORK5CYII=
EOF
		),
		'right_arrow.png' => array(
			'image' => <<<EOF
iVBORw0KGgoAAAANSUhEUgAAAAcAAAANCAYAAABlyXS1AAAAAXNSR0IArs4c6QAAAAZiS0dEAAAAAAAA+UO7fwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB90CDw83CNVXiVwAAACBSURBVBjTdZCxDYAgFESP6A7GhI7OGaRkCRehstEKR6JxCP8WDnE2SoDgJb96d/9ffgeAADprzDmPI677Ri6+s1ljUIskk2GZphKKyK+BIkLvfdPAGCMBbLWhz9YPIYQdwEoSSqm1SDrnimS6WYND63bbQ2ukQn+g+FANkIPvzqcHvP1sxH3VfFwAAAAASUVORK5CYII=
EOF
		),
		'see_all.png' => array(
			'image' => <<<EOF
iVBORw0KGgoAAAANSUhEUgAAAJYAAACWCAMAAAAL34HQAAABF1BMVEX4Enk/X0M/YENAYURAYkRAY0VBY0VBZEVBZEZBZUZCZUZCZkZCZkdDZ0dDaEdEakhEa0lFa0lFbEpFbUpGbktGb0tGcEtHcExHcUxIc01IdE5JdE5JdU5Jdk9Kdk9Kd09LeVBLeVFLelFMe1FMe1JMfFJMfVJNflNNf1NOgFROgVRPgVVPglVPg1VPg1ZQg1ZQhVZQhVdRhVdRhldRh1dRh1hSiFhSiVlSillTillTi1pTjFpUjFpUjVtUjltVjltVj1xWkFxWkV1Wkl1Xk15XlF5YlV9Yll9YlmBZl2BZmGBZmGFamWFammFammJam2Jbm2JbnGJbnGNbnWNcnWNcnmNcnmRcn2RdoGVdoWVeomZeo2ZfpGf06VQbAAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxMAAAsTAQCanBgAAAAHdElNRQfhAgUXBSLtrMGBAAADL0lEQVR42u2aaXfSQBSGmYSWSqtUS7WurVGLaK1LLbWulSIVAaGpDVnm//8OQwlkJiSXcIaQEe/zCfJych8ms52BTAZBEARBkGkhIyRUkkntSmTz+eeGbjrmRev7+92iQmSQWnmtUw67lr6VUjJpCClb5X5QKp9WtkGl03Ib6xONoQWO0NkPX0I2xj3GtMC5I4mJhZBK6DNjL/TLqVv7tVbPNtrVF0USlIoMBbRaET1pdMWtmy39YR5u/RlnFR2KaNkTOrhb+FY30OvOYoUz0aqE9wz36kMrajiAoZjXaHr/+eRGSK91x4RNo7WAUEzrhLnfxbfy1jJn5nYdnSn2ssmWBkNBtMBXdRpv7hBGq8yVWtY5LSAURR9/Cvre2tBK7fGPbddvETAUxwnpHuaBp3XXu7DhzZcFVgsIE/KixkDrnfdW9SqrrBYQzoLINZGQevSKCYbJiV1pdSCtDk1+8xFe2YS0TDqnPdFYZRvSspPSCllwqlxlYCoCQ+Ft4NilA7ZzNYDKYCioRY9XAhuoPKv1Mayy913AUFSLGpri38ptvlVWa5udxgJNDIbCWpRe7q35+16Fa4Nrw279lt0jq4MUDMW13AW6vv+gkCVKvlhq8cNp1NNeKSNxreOlYDgDLWCQD5dj2i1v5pSlwv0jw4/BMFGtjA3FdkLTaYwZ0YFyZ55asVbMGOFMzab8SHJLYox7gp+Q40AF+fdPoxM7QFowLW68Bd6iFmqhViLzQMgZXFpa4I9lqWpdv1f+cNo2LMcyflcPtVUihdZlcPdUeyyDVshm81xOLf9kGbXC9r+Dl80uhTzmrOW/Vk+l0eImsHWJtNjJ1JZFa3DAcXO7XDk561qStFbfKatVrdChmJpW32qnFzVDpKilfI2euNLSctvqiFVZX5JE6/YUHnPUOpZTqz3FIjhHLYuvK4uWzf3oK4lWhpx7hXYGk/2hJFpfvELW07yS037JsrF5BB08p7hU8//makqzseFW6UygMPw2+V3zsJo8WsNqeCiLIAiCIAiCIAiCIMj/wYS/PInFC6gFnlSJxaiFWqgVmwk3FosXTgtBEARBEARBEARBEI6/x4yTDS0pRUQAAAAASUVORK5CYII=
EOF
		),
		'transparent.png' => array(
			'image' => <<<EOF
iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEX///+nxBvIAAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxMAAAsTAQCanBgAAAAHdElNRQfhAgUXBhwH4I/pAAAACklEQVQI12NgAAAAAgAB4iG8MwAAAABJRU5ErkJggg==
EOF
		),
	),
	'acp' => array(
		'donate.png' => array(
			'image' => <<<EOF
iVBORw0KGgoAAAANSUhEUgAAAFwAAAAaCAMAAAANMMsbAAACSVBMVEXPCwL/8dv//fj+5rT/rzP/rC3+5K/+4KX+4ar+36H+0oT/sTj/sz1AUVb+tUK+uaAQPmt/g3W+qnxAXnWAb0f+t0hgdHy/jjsQPmoQOmLu26+en5AwVHOvhTsgSnHOv5auqpX/wmUgSW3vpzbOwJ0gQ2AwSlueoZQwUWxQWVR/ioi+tZX+uk1wgYb/x2ogSW/+tUOepJ5gdH7/vlqOlYz/qSaen43/3KiOlInfnzlgb3FwaE0gSW4gQl5/iYRAW27e0Kru3rr/58P/tkdAUViOjHfPmD2Ok4f/tEHPlDQgR2q/kECPdkPvoyxAYHsgQl//1JNgdYB/jIv/vVru1aH/rS//xGrfmzD/sjv/47P+znv+zXpwgov/tkP+zoD/uk//4rNQa37+0YT/8t2+tJL+3Jr/uUv+2ZTu1J3ey6FgdoEQO2L/v1eelnlwZ0vOxar/zX/+2ZeuqpF/jZD+x2z+sz2+tZlgYE//4K6PeUlwf4L/yW//3Kf/79R/jpP/z4POv5nezqb/6ML/9uhAXnZgYVFwf4D+wV/u2ar+zHn+wmH+wmJAX3f/89/+vFL/xm9AUlkQO2NQZW//qin+uk7/6sfu1qbPlTj+4KhweXOAcElQaXqurJiurpzeyp3+z32viEP/uEr/4bLOxKVAYHr/rjGfgEaffT//6sNQanuvhj/+w2RQa4CffkJwf4T+3Z+/jDj+zX2Olo/+57r+6cD/+e7/5b3/2Z7/9uX/zYD/8tv/79L/wWH/7Mj/tUL/qigAM2bMAAD/mTOpXq4DAAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxMAAAsTAQCanBgAAAAHdElNRQfhAgUWOgURujThAAACaklEQVRIx7WW9VMbQRTH0+YkSUsTIpCQlBjSBC9QKG6llBYrUHd3d6Pu7u7uLdTlvnN/WXfvEprkAj9dPjP33tvvm/3Ozs7c7mo0YUTV0MQievctHakKRx5fiLYXZ+z/oiLnNkXYi7u+q8zhQXdx0U/VuRZyF/t+JYDZsvuzl38SwUJp4WcGlBSBkH6gfmB46ouG7i2gS2ff9CuZh/T2dhPe9g/LxvStQzc/sWThs87f1Cp4CqNWW2fCEzowGtNCcpqxThuhLMY9STRq43Biu6gRC3O3cQqAkyTm4TPHZZvIDuVJ2nRS3g0p2RxH0xqOigWHFA5LcguJuc1mO6WLIQMFNAUR1Dlhd3pccBINLk8Xzuquw+4pgV23+j0QzCiBa0cXXsU6bL5qsxHzMYTTH/goyjGepj0o56uQw/NbcIefgKoeEqQGfxDg+Rwc5Xk77vM9dBTFXOpKzEdT5u9dqY8gBZNp8sHtho8Uy2AhWo3cWJXiI/th0etrcFzvJpXFAkTOXj7nkWRKzEeFWJH/wBBmHMwkTkKrwYxaqaqQNRpqcdmcjFsGQwXWk35rMmVwqiF/XdiRmPcySgCG8e8GpjITkeln/JdIRTUp0O8injNMJpppn2GaH8bxYHqJucAqSIXMO1I3Iet2B44RLYuVQwcCASCVZYHK12wATZVrcUNpwl4hf5EoKBibRLHOpHXbBgeqrVSbFgofqx1Wa9IUQbA68EJo2+kAHSihf6hYKiSEUunkEv8mBPlUFFt+J4CW8IFe/EN1iv9fRd5vKuONvEQby76qSFljzP3f3dkwQhUaOrvjvFzUfxT9AyTLYfdUmuB+AAAAAElFTkSuQmCC
EOF
		),
		'pixel.png' => array(
			'image' => <<<EOF
iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEXAwMBmS75kAAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxMAAAsTAQCanBgAAAAHdElNRQfhAgUXBQwxesxOAAAACklEQVQI12NgAAAAAgAB4iG8MwAAAABJRU5ErkJggg==
EOF
		),
		'settings.png' => array(
			'image' => <<<EOF
iVBORw0KGgoAAAANSUhEUgAAABAAAAAQBAMAAADt3eJSAAAAJ1BMVEUAAAAAAAADAwMGBgYKCgoNDQ0aGhodHR1JSUlYWFiHh4eWlpb///+1t4d2AAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxMAAAsTAQCanBgAAAAHdElNRQfhAgUXBgVjiycpAAAAfUlEQVQI12NgYGDg3sAABhyrqxpA9IQu6cIVnEAGZ6F0ofgEIKN9enRhZQVQxcLK6vLpUg0MXILps3eWCC5g2F5evuf07t27GYB4z+ny8mqglPjsncVAKaDi8u3TJYDa2aerb6ssABsovhFs4IQu8Y1gKxg4lu9qgFgPdgYAiBgpM0PxWJYAAAAASUVORK5CYII=
EOF
		),
		'logo.png' => array(
			'image' => <<<EOF
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
EOF
		),
		'add.png' => array(
			'image' => <<<EOF
iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAAXNSR0IArs4c6QAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB9wMGBAtOZp8lRIAAAEYSURBVDjLY1DaKM3EQCZQ2ijNxAhlMNzzf4oiuffx7v9Hnx5hYGBgYLCWtmFwlnVlRNOMqkdpozQjsubQHQH/lTZK/1faKP3fZYrD/4V75/9HUssNY8Odf8//6X+ljdJEOfue/9OvGAZADWFQ2ijNjEcz5z3/p/+QxViQnU3I9nqelm8MeyHseOdERrgBex/v/j/j6lS8mhdfX4gRyM6yrowsDAwMDEefHmE4+/M0Ts33ZG9jiMFiiOw0gBIG1tI2DIfOHMRps8gxCQw56xwbhAHOsq6MyPGMzc9BmiEofFjCgscCLFSVNkoz1PO0YI2RWYJTGNFTLBPe5InuJUg6wW4AIc1ohsCTPSWZif+e/9OPKKaRkZ1VAUVpe31b/eHeAAAAAElFTkSuQmCC
EOF
		),
		'delete.png' => array(
			'image' => <<<EOF
iVBORw0KGgoAAAANSUhEUgAAABIAAAASCAYAAABWzo5XAAAAAXNSR0IArs4c6QAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB90CFRQeFzPO4CgAAAD5SURBVDjLtdQxSgNBFMbxn1EbEYkIhkVYsrIXCXgEITewtxBsrDd4BI+RO1iJlQdI57Zio9jE5m1Y1rjZDfqameF985838z2Gf4gjnPXQH2JYLXZjHOEOY5R42wDJcI0LPOFjEIkDvOIelyFsg1zhFp/4Wie4wTLG7BdIEZqi7cAmbFrLTbtCfsDSNH2OTUWe5499IFVMArLEPEmSlxp40rcdigpQAaOatTFoI5VluajmSZJk2zTn6mpR1bx2tXFXyMrigBQoam/W2bG6xVvZ36XZNmpOezRbEzaCvUju4xgzPGDRAlqEBs7x3vxC4KSHs8MoYOdPP7Nvv1BJ+qn5oUoAAAAASUVORK5CYII=
EOF
		),
		'edit.png' => array(
			'image' => <<<EOF
iVBORw0KGgoAAAANSUhEUgAAABIAAAASCAYAAABWzo5XAAAAAXNSR0IArs4c6QAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB90CFQ8lKd5uXioAAAG+SURBVDjLnZJNaxNhFIWfkzag2C7ENkWhgmCYD2cWgn9ApAhB0broxpXL/gF/i1tBxS9sUbEoKN3rbl4yGRBUEBEVJfgBgnLcJHWQNKY9uxdeHu49z4UdJsoSAOI8XRm89wNop5AqlERZch1YBAKwKmlmeheQx8Av4IOkVeByr+h+n5oEEucpVSiJ8/SJpG/ANHBO0hXg9IHW3MfGJJBe0SXKkpu2BewDOsBd2zHwvArlmiZc5xpwTNK7AWQN2Av87BXd81GW/L/sOE/v216Q9Ano2L4t6aDtfhXKs8OJG+MUR1ny1HZTUn8AuQUktl/XISP119ZZB/YMij0FPJB0CCh6RffS8N8wGlVsnKdXbaeSvgBLwAbQtP21CuWFfyHA39VqkBu2j0t6DyzZfghMAdoOsjVRTfEGMCupb7sj6R5w2PbbKpTL46Q0gCHkDjAj6YftDvAIOGq7rEK5HOfpWLtblz3Xmn8FLEpaATYlNW2/qUJ5sW5nLCjO03YVyjC/0JoFfgMNSc0qlGe262TUwTWjLHGcp+sD/SeiLNkcdjdpFOfpSdvPJAG0e0X3ZV3ATkBHgLbtF1UoP7PL/AGC3NqVjoZmYAAAAABJRU5ErkJggg==
EOF
		),
		'help.png' => array(
			'image' => <<<EOF
iVBORw0KGgoAAAANSUhEUgAAABAAAAAQBAMAAADt3eJSAAAAD1BMVEUdHR0AAABOTk5YWFiamppmhYoCAAAAAXRSTlMAQObYZgAAAAFiS0dEAIgFHUgAAAAJcEhZcwAACxMAAAsTAQCanBgAAAAHdElNRQfhAgUXBAFW0IGyAAAAPElEQVQI12NgQAAmQUFBBRBDEcgQAjEEQQDMUGCCMMCCcOUQhiJEMQNMOxOUZmCESgAZAgyoAMM0BogSAAZKAzKqZgqEAAAAAElFTkSuQmCC
EOF
		),
		'manage.png' => array(
			'image' => <<<EOF
iVBORw0KGgoAAAANSUhEUgAAABAAAAAQBAMAAADt3eJSAAAAGFBMVEUAAAAlJSUmJiZERERGRkZNTU2Ojo7///9zy8a1AAAAAWJLR0QAiAUdSAAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB+ECBRcENABjRZEAAABLSURBVAjXY2AAAbPycga2tLS0ADCjvLy8AA9DFMwoAGuEMFTLAxhYQQy38gSIMIhhbCyg4gJiAM0NS0sDMoCagQagMYBSaWAGWCMAcOcmgAP/MAEAAAAASUVORK5CYII=
EOF
		),
		'trashcan_bg.png' => array(
			'image' => <<<EOF
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
EOF
		),
		'visibility.png' => array(
			'image' => <<<EOF
iVBORw0KGgoAAAANSUhEUgAAABIAAAASCAYAAABWzo5XAAAAAXNSR0IArs4c6QAAAAZiS0dEAP8A/wD/oL2nkwAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB90CFQ8gBZHBxowAAAB1SURBVDjL7dPBDYAgDIXhX4fx7D49NyZc3cAJGM2N9IJJQ2iQnnkJp5IPUgrMRLIDGXgaK5d6GPgF1sAJqAMosHngh4jBkwMls2c1GDQQyqnq3MZGSs29kcW0g2SvR9J5FBlteg0ekTG4Rudo6YB17vlf43kBjm9GC8Y0X2kAAAAASUVORK5CYII=
EOF
		),
	),
);