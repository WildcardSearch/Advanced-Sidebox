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

	$removed_files = array(
		'jscripts/asb.js',
		'jscripts/asb_xmlhttp.js',
		'admin/jscripts/asb.js',
		'admin/jscripts/asb_modal.js',
		'admin/jscripts/asb_scripts.js',
		'admin/jscripts/asb_sideboxes.js'
	);
	foreach ($removed_files as $file) {
		@unlink(MYBB_ROOT . $file);
	}
/* < 3.1 */
} elseif (version_compare($asbOldVersion, '3.1', '<')) {
	$removed_files = array(
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
	);
	foreach ($removed_files as $file) {
		@unlink(MYBB_ROOT . $file);
	}

	@my_rmdir_recursive(MYBB_ROOT . 'inc/plugins/asb/images');
	@rmdir(MYBB_ROOT . 'inc/plugins/asb/images');
/* < 3.1.1 */
} elseif (version_compare($asbOldVersion, '3.1.1', '<')) {
	@unlink(MYBB_ADMIN_DIR . 'styles/asb_acp.css');
}

?>
