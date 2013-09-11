<?php
/*
 * Plug-in Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2013 WildcardSearch
 * http://www.wildcardsworld.com
 *
 * this file contains a class list for forum-side XMLHTTP script
 */

if(!class_exists('MalleableObject'))
{
	require_once MYBB_ROOT . "inc/plugins/asb/classes/malleable.php";
}
if(!class_exists('StorableObject'))
{
	require_once MYBB_ROOT . "inc/plugins/asb/classes/storable.php";
}
if(!class_exists('PortableObject'))
{
	require_once MYBB_ROOT . "inc/plugins/asb/classes/portable.php";
}
if(!class_exists('Sidebox'))
{
	require_once MYBB_ROOT . "inc/plugins/asb/classes/sidebox.php";
}
if(!class_exists('Addon_type'))
{
	require_once MYBB_ROOT . "inc/plugins/asb/classes/module.php";
}

?>
