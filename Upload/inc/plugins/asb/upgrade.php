<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains upgrade functionality
 */

global $lang, $asbOldVersion;
if (!$lang->asb) {
	$lang->load('asb');
}

if (!class_exists('WildcardPluginInstaller')) {
	require_once MYBB_ROOT . 'inc/plugins/asb/classes/WildcardPluginInstaller.php';
}
$installer = new WildcardPluginInstaller(MYBB_ROOT . 'inc/plugins/asb/install_data.php');
$installer->install();

$removedAdminFolders = $removedForumFolders = $removedAdminFiles = $removedForumFiles = array();

/* < 2.1 */
if (version_compare($asbOldVersion, '2.1', '<')) {
	require_once MYBB_ROOT . 'inc/plugins/asb/classes/forum.php';
	$sideboxes = asb_get_all_sideboxes();
	foreach ($sideboxes as $sidebox) {
		$settings = array();
		foreach ((array) $sidebox->get('settings') as $name => $setting) {
			$settings[$name] = $setting['value'];
		}
		$sidebox->set('settings', $settings);
		$sidebox->save();
	}

	for ($x = 1; $x < 4; $x++) {
		$module_name = 'example';
		if ($x != 1) {
			$module_name .= $x;
		}

		$module = new SideboxExternalModule($module_name);
		$module->remove();
	}

	asb_cache_has_changed();

	$removedForumFiles = array(
		'jscripts/asb.js',
		'jscripts/asb_xmlhttp.js',
	);

	$removedAdminFiles = array(
		'jscripts/asb.js',
		'jscripts/asb_modal.js',
		'jscripts/asb_scripts.js',
		'jscripts/asb_sideboxes.js',
	);
/* < 3.1 */
} elseif (version_compare($asbOldVersion, '3.1', '<')) {
	$removedForumFiles = array_merge($removedForumFiles, array(
		'inc/plugins/asb/classes/installer.php',
		'inc/plugins/asb/classes/malleable.php',
		'inc/plugins/asb/classes/portable.php',
		'inc/plugins/asb/classes/storable.php',
		'inc/plugins/asb/classes/sidebox.php',
		'inc/plugins/asb/classes/custom.php',
		'inc/plugins/asb/classes/module.php',
		'inc/plugins/asb/classes/html_generator.php',
		'inc/plugins/asb/classes/script_info.php',
		'inc/plugins/asb/classes/template_handler.php',
	));

	$removedForumFolders[] = 'inc/plugins/asb/images';
/* < 3.1.1 */
} elseif (version_compare($asbOldVersion, '3.1.1', '<')) {
	$removedAdminFiles[] = 'styles/asb_acp.css';
/* < 3.1.2 */
} elseif (version_compare($asbOldVersion, '3.1.2', '<')) {
	$removedForumFolders[] = 'inc/plugins/asb/help';
	$removedAdminFiles = array_merge($removedAdminFiles, array(
		'jscripts/asb/asb.js',
		'jscripts/asb/asb.min.js',
	));
}

if (!empty($removedForumFiles)) {
	foreach ($removedForumFiles as $file) {
		@unlink(MYBB_ROOT . $file);
	}
}

if (!empty($removedForumFolders)) {
	foreach ($removedForumFolders as $folder) {
		@my_rmdir_recursive(MYBB_ROOT . $folder);
		@rmdir(MYBB_ROOT . $folder);
	}
}

if (!empty($removedAdminFiles)) {
	foreach ($removedAdminFiles as $file) {
		@unlink(MYBB_ADMIN_DIR . $file);
	}
}

if (!empty($removedAdminFolders)) {
	foreach ($removedAdminFolders as $folder) {
		@my_rmdir_recursive(MYBB_ADMIN_DIR . $folder);
		@rmdir(MYBB_ADMIN_DIR . $folder);
	}
}

?>
