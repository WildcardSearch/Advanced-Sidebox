<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * https://www.rantcentralforums.com
 *
 * wrapper to handle our plugin's installation
 */

class AdvancedSideboxInstaller extends WildcardPluginInstaller020001
{
	/**
	 * returns an installer object
	 *
	 * @return AdvancedSideboxInstaller
	 */
	static public function getInstance()
	{
		static $instance;

		if (!isset($instance)) {
			$instance = new AdvancedSideboxInstaller();
		}
		return $instance;
	}

	/**
	 * link the installer to our data file
	 *
	 * @param  string path to the install data
	 * @return void
	 */
	public function __construct($path='')
	{
		parent::__construct(MYBB_ROOT.'inc/plugins/asb/install_data.php');
	}
}