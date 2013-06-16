<?php
/*
 * This file contains the language variable definitions for the entire project
 *
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2013 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * Check out this project on GitHub: http://wildcardsearch.github.com/Advanced-Sidebox
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses
 *
 * default modules are to include language definitions here, external add-on medules should use external lang files
 */

/*
 * Core Language and General Use
 */

 // plugin info
$l['adv_sidebox_name'] = 'Advanced Sidebox';
$l['adv_sidebox_description1'] = "Display sideboxes with custom dynamic content.";
$l['adv_sidebox_description2'] = "This is a versatile plugin that is easy to set up and use, but packed with powerful options for controlling where and to whom side boxes display";
$l['adv_sidebox_logo'] = "Advanced Sidebox Logo";

// settings and descriptions
$l['adv_sidebox_plugin_settings'] = "Plugin Settings";
$l['adv_sidebox_settingsgroup_description'] = "control where and how the sideboxes display";
$l['adv_sidebox_show_on_index'] = "Show On Index";
$l['adv_sidebox_show_on_forumdisplay'] = "Show On Forum Display";
$l['adv_sidebox_show_on_threaddisplay'] = "Show On Thread Display";
$l['adv_sidebox_show_on_member'] = "Show On Member Profiles";
$l['adv_sidebox_show_on_memberlist'] = "Show On The Member List";
$l['adv_sidebox_show_on_showteam'] = "Show On The Forum Team Page";
$l['adv_sidebox_show_on_stats'] = "Show On Statistics Page";
$l['adv_sidebox_replace_portal_boxes'] = "Replace Portal Boxes";
$l['adv_sidebox_show_empty_boxes'] = "Show Sideboxes With No Content?";
$l['adv_sidebox_show_toggle_icons'] = "Show Column Visibility Toggle Icons?";
$l['adv_sidebox_show_empty_boxes_desc'] = "select YES to show the box (with custom explanation text) or NO to hide any side boxes that doesn't have proper content";

$l['adv_sidebox_width'] = "Width of Sideboxes";
$l['adv_sidebox_theme_exclude_list'] = "Theme EXCLUDE List";
$l['adv_sidebox_theme_exclude_list_description'] = "select themes to disable sidebox display for them.<br />(use CTRL to select/deselect multiple themes)";
$l['adv_sidebox_theme_exclude_select_update_link'] = 'Update Theme Exclude Selector';
$l['adv_sidebox_theme_exclude_select_update_description'] = 'when themes are installed/removed the theme exclude select box will need to be updated to reflect the current theme list.';

// messages
$l['adv_sidebox_theme_exclude_select_update_fail'] = 'The setting could not be updated';
$l['adv_sidebox_theme_exclude_select_update_success'] = 'The setting was successfully updated';

// UCP
$l['adv_sidebox_show_sidebox'] = "Show sidebox.";

// permissions
$l['adv_sidebox_admin_permissions_desc'] = 'Can manage sideboxes?';

/*
 * Default Modules
 */

$l['adv_sidebox_welcome_box'] = "Welcome Box";
$l['adv_sidebox_pm_box'] = "PM Box";
$l['adv_sidebox_search_box'] = "Search Box";
$l['adv_sidebox_stats_box'] = "Board Statistics";

// default settings
$l['adv_sidebox_xmlhttp_on_title'] = "AJAX Update?";
$l['adv_sidebox_xmlhttp_on_description'] = "time (in seconds) between updates (0 to disable AJAX)";

// WOL
$l['adv_sidebox_num_avatars_per_row'] = "Number of Avatars Per Row";
$l['adv_sidebox_num_avatars_per_row_description'] = "(enter number of columns in which avatars will be shown within the module, or 0 to disable avatars)";
$l['adv_sidebox_avatar_max_rows'] = "Maximum Number of Avatar Rows";
$l['adv_sidebox_avatar_max_rows_description'] = "(enter the maximum amount of rows that avatars may occupy, or 0 to disable avatars)";
$l['adv_sidebox_noone_online'] = "There are currently no members online.";
$l['adv_sidebox_wol_avatar_list'] = "Avatar settings for Who's Online module";
$l['adv_sidebox_avatar'] = "Avatar";
$l['adv_sidebox_avatar_lc'] = "avatar";
$l['adv_sidebox_see_all_alt'] = 'See all...';
$l['adv_sidebox_see_all_title'] = 'Click to see all online members.';

// latest threads
$l['adv_sidebox_latest_threads_lastpost'] = 'Last Post:';
$l['adv_sidebox_latest_threads_replies'] = 'Replies:';
$l['adv_sidebox_latest_threads_views'] = 'Views:';
$l['adv_sidebox_latest_threads_no_threads'] = 'There are no threads to display.';
$l['adv_sidebox_latest_threads'] = "Latest Threads";
$l['adv_sidebox_gotounread'] = "Go to first unread post";
$l['adv_sidebox_latest_threads_max'] = "maximal number of threads to display";
$l['adv_sidebox_latest_threads_max_title'] = "Thread Limit";

//  recent posts
$l['adv_sidebox_recent_posts_max_title'] = "Post Limit";
$l['adv_sidebox_recent_posts_max_description'] = "maximal number of posts to display";
$l['adv_sidebox_recent_posts_max_length_title'] = 'Maxium Post Length';
$l['adv_sidebox_recent_posts_max_length_description'] = 'maximum length of excerpt to show in characters';

$l['adv_sidebox_recent_posts_no_posts'] = 'There are no posts to display.';

// pm's
$l['adv_sidebox_pms_no_messages'] = 'Please {1} or {2} to use this functionality.';
$l['adv_sidebox_pms_login'] = 'login';
$l['adv_sidebox_pms_register'] = 'register';
$l['adv_sidebox_pms_user_disabled_pms'] = 'You have disabled this functionality in {1}.';
$l['adv_sidebox_pms_usercp'] = 'control panel';
$l['adv_sidebox_pms_disabled_by_admin'] = 'You don\'t have privileges to access this functionality, or it has been disabled by administrator. You may contact administrator for assistance.';

// random quote
$l['adv_sidebox_no_posts'] = 'There are no posts to display.';
$l['adv_sidebox_read_more'] = 'Read More';
$l['adv_sidebox_quote_forums_title'] = 'Forum List';
$l['adv_sidebox_quote_forums'] = 'single fid or comma-separated fid list of forums to pull random posts from';
$l['adv_sidebox_max_quote_length_title'] = 'Maximal Post Length';
$l['adv_sidebox_max_quote_length'] = 'in characters';
$l['adv_sidebox_min_quote_length_title'] = 'Minimal Post Length';
$l['adv_sidebox_min_quote_length'] = 'in characters';
$l['adv_sidebox_fade_out_title'] = 'Gradually Fade Out Text Over Max?';
$l['adv_sidebox_fade_out'] = 'YES to fade NO to clip (. . .)';
$l['adv_sidebox_default_text'] = 'Default Text';
$l['adv_sidebox_default_text_description'] = 'display this if the message is too short';

// staff online
$l['adv_sidebox_no_staff'] = 'There are currently no staff members online.';
$l['adv_sidebox_max_staff_descr'] = 'maximal number of staff members to display';
$l['adv_sidebox_max_staff_title'] = 'Staff Limit';

/*
 * Sidebox Management
 */

 // page
$l['adv_sidebox_manage_sideboxes'] = 'Manage Sideboxes';
$l['adv_sidebox_manage_sideboxes_desc'] = 'edit, remove and add sideboxes';
$l['adv_sidebox_page_desc'] = 'This page allows customization of Advanced Sidebox on your forum.';

// general use
$l['adv_sidebox_box_type'] = 'Type';
$l['adv_sidebox_content'] = 'Content';
$l['adv_sidebox_scripts'] = 'Scripts';
$l['adv_sidebox_groups'] = 'Groups';
$l['adv_sidebox_controls'] = 'Controls';
$l['adv_sidebox_options'] = 'Options';

// positioning
$l['adv_sidebox_position_left_boxes'] = 'Left Boxes';
$l['adv_sidebox_position_right_boxes'] = 'Right Boxes';
$l['adv_sidebox_position_desc'] = 'choose left or right';
$l['adv_sidebox_position'] = "Position";
$l['adv_sidebox_position_left'] = "Left";
$l['adv_sidebox_position_right'] = "Right";

// controls
$l['adv_sidebox_edit'] = 'Edit';
$l['adv_sidebox_delete'] = 'Delete';

// messages
$l['adv_sidebox_no_boxes'] = 'no sideboxes added yet';
$l['adv_sidebox_no_boxes_left'] = 'no left sideboxes';
$l['adv_sidebox_no_boxes_right'] = 'no right sideboxes';
$l['adv_sidebox_save_success'] = 'The box was saved successfully';
$l['adv_sidebox_save_fail'] = 'The box could not be saved';
$l['adv_sidebox_delete_box_success'] = 'The box was deleted successfully';
$l['adv_sidebox_delete_box_failure'] = 'There was an error while attempting to remove the seleced sidebox';

// menus
$l['adv_sidebox_add_new_box'] = 'Add A New sidebox';
$l['adv_sidebox_add_new_box_desc'] = 'or edit an existing box';
$l['adv_sidebox_add_a_sidebox'] = 'Add A Sidebox';

// add/edit
$l['adv_sidebox_edit_box'] = 'Edit Sidebox';
$l['adv_sidebox_type_desc'] = 'select one of several presets';

// groups
$l['adv_sidebox_which_groups'] = 'Which Groups?';
$l['adv_sidebox_all_groups'] = 'All User Groups';
$l['adv_sidebox_guests'] = 'Guests';

// scripts
$l['adv_sidebox_all'] = 'All Scripts';
$l['adv_sidebox_all_scripts_disabled'] = 'All Scripts Disabled!';
$l['adv_sidebox_both_scripts'] = 'Both Scripts';
$l['adv_sidebox_which_scripts'] = 'Which Scripts?';
$l['adv_sidebox_index'] = 'Index';
$l['adv_sidebox_forum'] = 'Forum';
$l['adv_sidebox_thread'] = 'Thread';
$l['adv_sidebox_portal'] = 'Portal';
$l['adv_sidebox_member'] = 'Profile';
$l['adv_sidebox_memberlist'] = 'Member List';
$l['adv_sidebox_showteam'] = 'Forum Team';
$l['adv_sidebox_stats'] = 'Stats';

// script abbreviations (tool tips)
$l['adv_sidebox_abbr_index'] = 'Index';
$l['adv_sidebox_abbr_forumdisplay'] = 'Forum';
$l['adv_sidebox_abbr_showthread'] = 'Thread';
$l['adv_sidebox_abbr_portal'] = 'Portal';
$l['adv_sidebox_abbr_member'] = 'Profile';
$l['adv_sidebox_abbr_memberlist'] = 'Mem. List';
$l['adv_sidebox_abbr_showteam'] = 'Team';
$l['adv_sidebox_abbr_stats'] = 'Stats';

// custom titles
$l['adv_sidebox_current_title'] = 'current title: ';
$l['adv_sidebox_current_title_info'] = '(leave blank to keep current custom title)';
$l['adv_sidebox_use_custom_title'] = 'Use Custom Title?';
$l['adv_sidebox_custom_title'] = 'Custom Title';
$l['adv_sidebox_default_title'] = 'currently using default title';
$l['adv_sidebox_default_title_info'] = '(leave blank to keep default title)';

// misc. options
$l['adv_sidebox_display_order'] = 'Display Order';

/*
 * Module Management
 */

 // general use and box types
$l['adv_sidebox_box'] = "Box";
$l['adv_sidebox_custom'] = 'Custom';

 // page
$l['adv_sidebox_manage_modules'] = 'Manage Modules';
$l['adv_sidebox_manage_modules_desc'] = 'install, uninstall and delete add-ons';

// general use
$l['adv_sidebox_active_modules'] = 'Active Modules';
$l['adv_sidebox_inactive_modules'] = 'Inactive Modules';
$l['adv_sidebox_uninstall'] = 'Uninstall';
$l['adv_sidebox_install'] = 'Install';
$l['adv_sidebox_modules_version'] = 'Version';
$l['adv_sidebox_modules_author'] = 'Author';

// module info
$l['adv_sidebox_no_modules_detected'] = 'no modules detected';
$l['adv_sidebox_module_info_good_count'] = "There {1} {2} {3} detected";
$l['adv_sidebox_are'] = 'are';
$l['adv_sidebox_is'] = 'is';
$l['adv_sidebox_module_plural'] = 'modules';
$l['adv_sidebox_module_singular'] = 'module';
$l['adv_sidebox_module_awaiting_install'] = ", {1} {2} awaiting installation.";
$l['adv_sidebox_module_all_good'] = ' and properly installed.';

// user messages
$l['adv_sidebox_install_addon_success'] = 'The module was installed successfully';
$l['adv_sidebox_install_addon_failure'] = 'The module could not be installed';
$l['adv_sidebox_uninstall_addon_success'] = 'The module was uninstalled successfully';
$l['adv_sidebox_uninstall_addon_failure'] = 'The module could not be uninstalled';
$l['adv_sidebox_delete_addon_success'] = 'The module was deleted successfully';
$l['adv_sidebox_delete_addon_failure'] = 'The action couldn\'t be taken. Are you using the correct method to access this feature?';
$l['adv_sidebox_modules_del_warning'] = 'Delete this add-on permanently?\n\nThis cannot be undone!';

/*
 * Custom Boxes
 */

// page
$l['adv_sidebox_custom_boxes'] = 'Custom Boxes';
$l['adv_sidebox_custom_boxes_desc'] = 'create, import and export custom box templates';

// general use
$l['adv_sidebox_custom_box_name'] = 'Name';
$l['adv_sidebox_custom_box_desc'] = 'Description';
$l['adv_sidebox_custom_box_wrap_content'] = 'Use Template';
$l['adv_sidebox_custom_box_wrap_content_desc'] = 'unchecking this box means that you want to build your own tables and expander';
$l['adv_sidebox_no_custom_boxes'] = 'no custom box types saved';
$l['adv_sidebox_custom_box_types'] = 'Custom Box Types';
$l['adv_sidebox_add_custom_box_types'] = 'Add a new custom box type';
$l['adv_sidebox_add_custom_box_name_desc'] = 'choose a descriptive name';
$l['adv_sidebox_add_custom_box_description_desc'] = 'what does this box type display?';
$l['adv_sidebox_add_custom_box_edit'] = 'Edit Custom Box';
$l['adv_sidebox_add_custom_box_edit_desc'] = 'create new box templates and custom sideboxes';

// messages
$l['adv_sidebox_custom_box_save_success'] = 'The new custom box type was saved successfully';
$l['adv_sidebox_custom_box_save_failure'] = 'The new custom box type could not be saved successfully';
$l['adv_sidebox_custom_box_save_failure_no_content'] = 'The new custom box type could not be saved successfully because one or more required inputs were blank';
$l['adv_sidebox_add_custom_box_delete_success'] = 'The custom box type was deleted successfully';
$l['adv_sidebox_add_custom_box_delete_failure'] = 'The new custom box type could not be deleted successfully';
$l['adv_sidebox_custom_import_no_file'] = 'You must select a valid XML file.';
$l['adv_sidebox_custom_import_file_error'] = 'Error: {1}';
$l['adv_sidebox_custom_import_file_upload_error'] = 'There was a problem with the file upload.';
$l['adv_sidebox_custom_import_file_empty'] = 'This file contains no usable information. It may be corrupted.';
$l['adv_sidebox_custom_import_file_corrupted'] = 'This file may be corrupted.';
$l['adv_sidebox_custom_import_save_fail'] = 'Could not save the imported info in the database.';
$l['adv_sidebox_custom_import_save_success'] = 'The box was successfully imported';
$l['adv_sidebox_custom_export_error'] = 'An error occurred when attempting to export this sidebox: side box does not exist';
$l['adv_sidebox_custom_del_warning'] = 'Delete this custom box permanently?\n\nThis cannot be undone!';

// import/export
$l['adv_sidebox_custom_export'] = 'Export';
$l['adv_sidebox_custom_import'] = 'Import';
$l['adv_sidebox_custom_import_box'] = 'Import A Custom Sidebox';
$l['adv_sidebox_custom_import_description'] = 'backup and restore user-defined box types';
$l['adv_sidebox_custom_import_select_file'] = 'Select a local file:';

?>
