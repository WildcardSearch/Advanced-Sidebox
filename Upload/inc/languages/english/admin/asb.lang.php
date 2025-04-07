<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * https://www.rantcentralforums.com
 *
 * this file contains language for the ACP pages
 */

/*
 * Core Language and General Use
 */

 // plugin info
$l['asb'] = 'Advanced Sidebox';
$l['asb_description1'] = 'Display sideboxes with custom dynamic content.';
$l['asb_description2'] = 'This is a versatile plugin that is easy to set up and use, but packed with powerful options for controlling where and to whom side boxes display';
$l['asb_logo'] = 'Advanced Sidebox Logo';

$l['asb_remove_old_files'] = 'Click here to remove them';
$l['asb_remove_old_files_desc'] = 'Components of an older version were found!';

// settings and descriptions
$l['asb_plugin_settings'] = 'Plugin Settings';
$l['asb_settingsgroup_description'] = 'control where and how the sideboxes display';

$l['asb_show_empty_boxes'] = 'Show Sideboxes With No Content?';
$l['asb_show_empty_boxes_desc'] = "select YES to show the box (with custom explanation text) or NO to hide any side boxes that doesn't have proper content";

$l['asb_show_toggle_icons'] = 'Show Column Visibility Toggle Icons?';

$l['asb_show_expanders'] = 'Show Expand/Collapse Icons?';

$l['asb_allow_user_disable'] = 'Allow Users To Disable Side Boxes?';

$l['asb_width_left'] = 'Left Width';
$l['asb_width_left_desc'] = 'width of left column';

$l['asb_left_margin'] = 'Left Margin';
$l['asb_left_margin_desc'] = 'width of margin between left column and page content';

$l['asb_width_middle'] = 'Middle Width';
$l['asb_width_middle_desc'] = 'width of middle column';

$l['asb_right_margin'] = 'Right Margin';
$l['asb_right_margin_desc'] = 'width of margin between page content and right column';

$l['asb_width_right'] = 'Right Width';
$l['asb_width_right_desc'] = 'width of right column';

$l['asb_minify_js_title'] = 'Minify JavaScript?';
$l['asb_minify_js_desc'] = 'YES (default) to serve client-side scripts minified to increase performance, NO to serve beautiful, commented code ;)';

$l['asb_theme_exclude_list'] = 'Theme EXCLUDE List';
$l['asb_theme_exclude_list_description'] = 'select themes to disable sidebox display for them.<br />(use CTRL to select/deselect multiple themes)';
$l['asb_theme_exclude_select_update_link'] = 'Update Theme Exclude Selector';
$l['asb_theme_exclude_select_update_description'] = 'when themes are installed/removed the theme exclude select box will need to be updated to reflect the current theme list.';
$l['asb_theme_exclude_no_themes'] = 'no themes';

$l['asb_disable_for_mobile_title'] = 'Disable For Mobile Users?';
$l['asb_disable_for_mobile_description'] = 'YES to disable for mobile users, NO (default) to enable for all browsers';

// messages
$l['asb_theme_exclude_select_update_fail'] = 'The setting could not be updated';
$l['asb_theme_exclude_select_update_success'] = 'The setting was successfully updated';

// permissions
$l['asb_admin_permissions_desc'] = 'Can manage sideboxes?';

/*
 * Side Box Management
 */

 // page
$l['asb_manage_sideboxes'] = 'Manage Sideboxes';
$l['asb_manage_sideboxes_desc'] = 'edit, remove and add sideboxes';
$l['asb_page_desc'] = 'This page allows customization of Advanced Sidebox on your forum.';

// general use
$l['asb_name'] = 'Name';
$l['asb_help'] = 'Help';
$l['asb_box_type'] = 'Type';
$l['asb_content'] = 'Content';
$l['asb_scripts'] = 'Scripts';
$l['asb_groups'] = 'Groups';
$l['asb_controls'] = 'Controls';
$l['asb_options'] = 'Options';
$l['asb_add'] = 'Add';
$l['asb_cancel'] = 'Cancel';
$l['asb_save'] = 'Save';
$l['asb_status'] = 'Status';
$l['asb_visibility'] = 'Visibility';
$l['asb_hooks'] = 'Hooks';
$l['asb_templates'] = 'Templates';
$l['asb_actions'] = 'Actions';
$l['asb_detected'] = 'Detected';
$l['asb_update'] = 'Update';
$l['asb_none'] = 'none';

$l['asb_inactive'] = 'Inactive';
$l['asb_inactive_desc'] = 'this script is currently deactivated, click to activate';
$l['asb_active'] = 'Active';
$l['asb_active_desc'] = 'this script is currently activated, click to deactivate';

$l['asb_filter_label'] = 'Showing only side boxes viewable on <strong>{1}</strong>';

$l['asb_title'] = 'Title';
$l['asb_title_desc'] = 'the display name';
$l['asb_filename'] = 'Filename';
$l['asb_filename_desc'] = 'the file name of the script';
$l['asb_action'] = 'Action';
$l['asb_page'] = 'Page';
$l['asb_scriptvar_generic_desc'] = 'the URL <span style="font-family: courier; font-weight: bold; font-size: 1.2em;">{1}</span> value';

$l['asb_template'] = 'Template';
$l['asb_template_desc'] = 'name of the template to edit';
$l['asb_hook'] = 'Hook';
$l['asb_hook_desc'] = 'plugin hook to use';

$l['asb_header_search_text'] = 'Header Search Text';
$l['asb_header_search_text_desc'] = 'ASB will place the side box tables just <em>after</em> the contents of this setting are found';
$l['asb_footer_search_text'] = 'Footer Search Text';
$l['asb_footer_search_text_desc'] = 'ASB will place the side box tables just <em>before</em> the contents of this setting are found';

$l['asb_replace_template'] = 'Replacing entire template?';
$l['asb_replace_template_desc'] = 'set to yes in order to replace the entire template with custom content';
$l['asb_replacement_content'] = 'Replacement Content';
$l['asb_replacement_content_desc'] = 'content to replace into this page';
$l['asb_replacement_template'] = 'Replacement Template Name';
$l['asb_replacement_template_desc'] = 'optionally enter a valid template name here and it will be used (leave blank to use custom content above)';
$l['asb_output_to_vars'] = 'Output Sideboxes To Variables Rather Than Editing Templates';
$l['asb_output_to_vars_desc'] = 'use this option if the script does not use templates to output the side box columns as {1}';

$l['asb_creating'] = 'Creating';
$l['asb_editing'] = 'Editing';
$l['asb_new_sidebox_action'] = '{1} A New {2} Sidebox';
$l['asb_detecting'] = 'detecting';

// positioning
$l['asb_position_left_boxes'] = 'Left Boxes';
$l['asb_position_right_boxes'] = 'Right Boxes';
$l['asb_position_desc'] = 'choose left or right';
$l['asb_position'] = 'Position';
$l['asb_position_left'] = 'Left';
$l['asb_position_right'] = 'Right';

// controls
$l['asb_edit'] = 'Edit';
$l['asb_delete'] = 'Delete';

// messages
$l['asb_no_boxes'] = 'no sideboxes added yet';
$l['asb_no_boxes_left'] = 'no left sideboxes';
$l['asb_no_boxes_right'] = 'no right sideboxes';
$l['asb_save_success'] = 'The box was saved successfully';
$l['asb_save_fail'] = 'The box could not be saved';
$l['asb_delete_box_success'] = 'The box was deleted successfully';
$l['asb_delete_box_failure'] = 'There was an error while attempting to remove the seleced sidebox';
$l['asb_no_description'] = 'no description';
$l['asb_edit_fail_bad_module'] = 'The chosen module was invalid and cannot be used.';

$l['asb_inline_title'] = 'Inline Edits';
$l['asb_inline_selection_error'] = 'You did not select anything.';
$l['asb_inline_success'] = '{1} {2} successfully {3}';
$l['asb_update_width'] = 'Update Width';
$l['asb_deleted'] = 'deleted';
$l['asb_activated'] = 'activated';
$l['asb_deactivated'] = 'deactivated';

// menus
$l['asb_add_new_box'] = 'Add A New Side Box';
$l['asb_add_new_box_desc'] = 'or edit an existing box';
$l['asb_add_a_sidebox'] = 'Add A Side Box';
$l['asb_edit_a_sidebox'] = 'Edit A Side Box';

// add/edit
$l['asb_edit_box'] = 'Edit Side Box';
$l['asb_type_desc'] = 'select one of several pre-sets';
$l['asb_add_new_sidebox'] = 'Add a new side box of this type';

$l['asb_modal_tab_general'] = 'General';
$l['asb_modal_tab_permissions'] = 'Permissions';
$l['asb_modal_tab_pages'] = 'Pages';
$l['asb_modal_tab_themes'] = 'Themes';
$l['asb_modal_tab_settings'] = 'Settings';
$l['asb_modal_tab_settings_desc'] = 'Custom Module Settings';

$l['asb_sample_content_tcat'] = '.tcat example';
$l['asb_sample_content_trow1'] = '.trow1 example';
$l['asb_sample_content_trow_sep'] = '.trow_sep example';
$l['asb_sample_content_trow2'] = '.trow2 example';
$l['asb_sample_content_tfoot'] = '.tfoot example';

// groups
$l['asb_which_groups'] = 'Which Groups?';
$l['asb_all_groups'] = 'All User Groups';
$l['asb_guests'] = 'Guests';

// scripts
$l['asb_all'] = 'All Scripts';
$l['asb_all_scripts_disabled'] = 'All Scripts Disabled!';
$l['asb_both_scripts'] = 'Both Scripts';
$l['asb_which_scripts'] = 'Which Scripts?';

$l['asb_no_script_filter'] = 'Do not filter by script';
$l['asb_script_filter_title'] = 'Show only the boxes for {1}';

// themes
$l['asb_which_themes'] = 'Which Themes?';

$l['asb_index'] = 'Index';
$l['asb_forumdisplay'] = 'Forum';
$l['asb_showthread'] = 'Thread';
$l['asb_portal'] = 'Portal';
$l['asb_member'] = 'Profile';
$l['asb_memberlist'] = 'Member List';
$l['asb_showteam'] = 'Forum Team';
$l['asb_stats'] = 'Statistics';

// script abbreviations (tool tips)
$l['asb_abbr_index'] = 'Index';
$l['asb_abbr_forumdisplay'] = 'Forum';
$l['asb_abbr_showthread'] = 'Thread';
$l['asb_abbr_portal'] = 'Portal';
$l['asb_abbr_member'] = 'Profile';
$l['asb_abbr_memberlist'] = 'Mem. List';
$l['asb_abbr_showteam'] = 'Team';
$l['asb_abbr_stats'] = 'Stats';

$l['asb_invalid_sidebox'] = 'invalid side box';
$l['asb_no_active_scripts'] = 'no active scripts';
$l['asb_visibile_for_all_themes'] = 'Visible For All Themes';

$l['asb_all_scripts_deactivated'] = 'This Side Box Is Deactivated';
$l['asb_globally_visible'] = 'Globally Visible';

// custom titles
$l['asb_current_title'] = 'current title: ';
$l['asb_current_title_info'] = '(leave blank to keep current custom title)';
$l['asb_use_custom_title'] = 'Use Custom Title?';
$l['asb_custom_title'] = 'Custom Title';
$l['asb_default_title'] = 'currently using default title';
$l['asb_default_title_info'] = '(leave blank to keep default title)';

// title link
$l['asb_title_link'] = 'Title Link';
$l['asb_title_link_desc'] = 'URL for title link (leave blank for no link)';

// misc. options
$l['asb_display_order'] = 'Display Order';

/*
 * Module Management
 */

 // general use and box types
$l['asb_box'] = 'Box';
$l['asb_custom'] = 'Custom';

 // page
$l['asb_manage_modules'] = 'Manage Modules';
$l['asb_manage_modules_desc'] = 'install, uninstall and delete add-ons';

// general use
$l['asb_addon_modules'] = 'Add-on Modules';
$l['asb_active_modules'] = 'Active Modules';
$l['asb_inactive_modules'] = 'Inactive Modules';
$l['asb_uninstall'] = 'Uninstall';
$l['asb_install'] = 'Install';
$l['asb_modules_version'] = 'Version';
$l['asb_modules_author'] = 'Author';
$l['asb_activate'] = 'Activate';
$l['asb_deactivate'] = 'Deactivate';

// module info
$l['asb_no_modules_detected'] = 'no modules detected';
$l['asb_module_info_good_count'] = 'There {1} {2} {3} detected';
$l['asb_are'] = 'are';
$l['asb_is'] = 'is';
$l['asb_module_plural'] = 'modules';
$l['asb_module_singular'] = 'module';
$l['asb_module_awaiting_install'] = ', {1} {2} awaiting installation.';
$l['asb_module_all_good'] = ' and properly installed.';
$l['asb_module_out_of_date'] = 'This addon is out-of-date and will require an update before it can be used.';

// user messages
$l['asb_install_addon_success'] = 'The module was installed successfully';
$l['asb_install_addon_failure'] = 'The module could not be installed';
$l['asb_uninstall_addon_success'] = 'The module was uninstalled successfully';
$l['asb_uninstall_addon_failure'] = 'The module could not be uninstalled';
$l['asb_delete_addon_success'] = 'The module was deleted successfully';
$l['asb_delete_addon_failure'] = 'The action couldn\'t be taken. Are you using the correct method to access this feature?';
$l['asb_modules_del_warning'] = 'Delete this add-on permanently?\n\nThis cannot be undone!';
$l['asb_script_del_warning'] = 'Delete this script info permanently, disabling any side boxes designated to appear on these pages?\n\nThis cannot be undone!';

/*
 * Custom Boxes
 */

// page
$l['asb_custom_boxes'] = 'Custom Boxes';
$l['asb_custom_boxes_desc'] = 'create, import and export custom box templates';

$l['asb_add_custom'] = 'Add New Custom Box';
$l['asb_add_custom_desc'] = 'edit existing custom boxes and create new boxes here';

// general use
$l['asb_description'] = $l['asb_custom_box_desc'] = 'Description';
$l['asb_custom_box_wrap_content'] = 'Use Template';
$l['asb_custom_box_wrap_content_desc'] = 'unchecking this box means that you want to build your own tables and expander';
$l['asb_no_custom_boxes'] = 'no custom box types saved';
$l['asb_custom_box_types'] = 'Custom Box Types';
$l['asb_add_custom_box_types'] = 'Add a new custom box type';
$l['asb_add_custom_box_name_desc'] = 'choose a descriptive name';
$l['asb_add_custom_box_description_desc'] = 'what does this box type display?';
$l['asb_add_custom_box_edit'] = 'Edit Custom Box';
$l['asb_add_custom_box_edit_desc'] = 'create new box templates and custom sideboxes';

// messages
$l['asb_custom_box_save_success'] = 'The new custom box type was saved successfully';
$l['asb_custom_box_save_failure'] = 'The new custom box type could not be saved successfully';
$l['asb_custom_box_save_failure_no_content'] = 'The new custom box type could not be saved successfully because one or more required inputs were blank';
$l['asb_add_custom_box_delete_success'] = 'The custom box type was deleted successfully';
$l['asb_add_custom_box_delete_failure'] = 'The new custom box type could not be deleted successfully';
$l['asb_custom_import_no_file'] = 'You must select a valid XML file.';
$l['asb_custom_import_file_error'] = 'Error: {1}';
$l['asb_custom_import_file_upload_error'] = 'There was a problem with the file upload.';
$l['asb_custom_import_file_empty'] = 'This file contains no usable information. It may be corrupted.';
$l['asb_custom_import_file_corrupted'] = 'This file may be corrupted.';
$l['asb_custom_import_save_success'] = 'The box was successfully imported';
$l['asb_custom_import_fail_generic'] = 'Content import unsuccessful';
$l['asb_custom_export_error'] = 'An error occurred when attempting to export this sidebox: side box does not exist';
$l['asb_custom_del_warning'] = 'Delete this custom box permanently?\n\nThis cannot be undone!';

// import/export
$l['asb_custom_export'] = 'Export';
$l['asb_custom_import'] = 'Import';
$l['asb_custom_import_box'] = 'Import A Custom Sidebox';
$l['asb_custom_import_description'] = 'backup and restore user-defined box types';
$l['asb_custom_import_select_file'] = 'Select a local file:';

/*
 * Script Management
 */

 // page
$l['asb_manage_scripts'] = 'Manage Scripts';
$l['asb_manage_scripts_desc'] = 'change how side boxes appear for each theme';

$l['asb_view_scripts'] = 'View Scripts';
$l['asb_view_scripts_desc'] = 'change how side boxes appear for each page';

$l['asb_edit_script'] = 'Add New Script';
$l['asb_edit_script_desc'] = 'add new script definitions or edit existing info';

$l['asb_overwrite_confirmation'] = 'Overwrite Confirmation';
$l['asb_overwrite_confirmation_desc'] = 'confirm that you want to overwrite an existing script definition';
$l['asb_import_overwrite_warning'] = 'Importing this script definition will overwrite the existing definition. Would you like to proceed?';
$l['asb_save_overwrite_warning'] = 'A script definition with these paramaters already exists. Saving it will overwrite the existing definition. Would you like to proceed?';

$l['asb_add_new_script'] = 'Add a new script definition';
$l['asb_no_scripts'] = 'no script info to show';
$l['asb_script_info'] = 'Script Info';
$l['asb_script_definition'] = 'script definition';
$l['asb_script_definitions'] = 'script definitions';
$l['asb_updated'] = 'updated';

$l['asb_revert'] = 'Revert';
$l['asb_confirm'] = 'Confirm';

// messages
$l['asb_script_save_success'] = 'The script definition was saved successfully';
$l['asb_script_save_fail'] = 'The script definition could not be saved successfully';
$l['asb_script_save_width_error'] = 'The script definition could not be saved successfully because the total width was greater than 100%';

$l['asb_script_import_success'] = 'The script definition was imported successfully';
$l['asb_script_import_fail'] = 'The script definition could not be imported successfully';

$l['asb_script_delete_success'] = 'The script definition was deleted successfully';
$l['asb_script_delete_fail'] = 'The script definition could not be deleted successfully';

$l['asb_script_revert_success'] = 'The script definition was reverted successfully';
$l['asb_script_revert_fail'] = 'The script definition could not be reverted successfully';

$l['asb_script_export_success'] = 'The script definition was exported successfully';
$l['asb_script_export_fail'] = 'The script definition could not be exported successfully';

$l['asb_script_activate_success'] = 'The script definition was activated successfully';
$l['asb_script_activate_fail'] = 'The script definition could not be activated successfully';

$l['asb_script_deactivate_success'] = 'The script definition was deactivated successfully';
$l['asb_script_deactivate_fail'] = 'The script definition could not be deactivated successfully';

// ajax
$l['asb_ajax_deleting_sidebox'] = 'Deleting . . .';
$l['asb_ajax_nothing_found'] = 'no {1} detected';
$l['asb_ajax_actions'] = 'actions';
$l['asb_ajax_hooks'] = 'hooks';
$l['asb_ajax_templates'] = 'templates';
$l['asb_ajax_file_name_empty'] = 'no file name';
$l['asb_ajax_file_does_not_exist'] = 'file does not exist';
$l['asb_ajax_file_empty'] = 'file empty or corrupted';