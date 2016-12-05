<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * the main plugin file; splits forum and ACP scripts to decrease footprint
 */

// disallow direct access to this file for security reasons
if (!defined('IN_MYBB')) {
	die('Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.');
}

// for modules
define('IN_ASB', true);
define('ASB_MODULES_DIR', MYBB_ROOT . 'inc/plugins/asb/modules');

// some basic functions used everywhere
require_once MYBB_ROOT . 'inc/plugins/asb/functions.php';

// load the install/admin routines only if in ACP.
if (defined('IN_ADMINCP')) {
    require_once MYBB_ROOT . 'inc/plugins/asb/acp.php';
} else {
	require_once MYBB_ROOT . 'inc/plugins/asb/forum.php';
}

?>
