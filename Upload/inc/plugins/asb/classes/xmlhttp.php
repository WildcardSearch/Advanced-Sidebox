<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains a class list for forum-side XMLHTTP script
 */

if (!class_exists('MalleableObject')) {
	require_once MYBB_ROOT . 'inc/plugins/asb/classes/MalleableObject.php';
}
if (!class_exists('StorableObject')) {
	require_once MYBB_ROOT . 'inc/plugins/asb/classes/StorableObject.php';
}
if (!class_exists('PortableObject')) {
	require_once MYBB_ROOT . 'inc/plugins/asb/classes/PortableObject.php';
}
if (!class_exists('SideboxObject')) {
	require_once MYBB_ROOT . 'inc/plugins/asb/classes/SideboxObject.php';
}
if (!class_exists('SideboxExternalModule')) {
	require_once MYBB_ROOT . 'inc/plugins/asb/classes/SideboxExternalModule.php';
}

?>
