<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * https://www.rantcentralforums.com
 *
 * this file contains upgrade functionality
 */

global $lang, $asbOldVersion, $db;
if (!$lang->asb) {
	$lang->load('asb');
}

AdvancedSideboxInstaller::getInstance()->install();

$removedAdminFolders = $removedForumFolders = $removedAdminFiles = $removedForumFiles = array();

/** placeholder **/

if (!empty($removedForumFiles)) {
	foreach ($removedForumFiles as $file) {
		@unlink(MYBB_ROOT.$file);
	}
}

if (!empty($removedForumFolders)) {
	foreach ($removedForumFolders as $folder) {
		@my_rmdir_recursive(MYBB_ROOT.$folder);
		@rmdir(MYBB_ROOT.$folder);
	}
}

if (!empty($removedAdminFiles)) {
	foreach ($removedAdminFiles as $file) {
		@unlink(MYBB_ADMIN_DIR.$file);
	}
}

if (!empty($removedAdminFolders)) {
	foreach ($removedAdminFolders as $folder) {
		@my_rmdir_recursive(MYBB_ADMIN_DIR.$folder);
		@rmdir(MYBB_ADMIN_DIR.$folder);
	}
}