<?php
/*
 * Plug-in Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2013 WildcardSearch
 * http://www.wildcardsworld.com
 *
 * this file contains an object wrapper for script definitons
 */

// check dependencies
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

class ScriptInfo extends PortableObject
{
	protected $title = '';

	// script identity
	protected $filename = '';
	protected $action = '';
	protected $page = '';

	// dimensions
	protected $width_left = 160;
	protected $width_right = 160;

	// the template and hook to use
	protected $template_name = '';
	protected $hook = '';

	// the search text
	protected $find_top = '';
	protected $find_bottom = '';

	// replacement options
	protected $replace_all = false;
	protected $replacement = '';
	protected $replacement_template = '';

	// extra properties
	protected $eval = false;
	protected $active = false;

	/*
	 * function __construct()
	 *
	 * @param - $data - (mixed) an integer ID or an associative array of script data
	 */
	public function __construct($data = '')
	{
		$this->table_name = 'asb_script_info';
		parent::__construct($data);
	}
}

?>
