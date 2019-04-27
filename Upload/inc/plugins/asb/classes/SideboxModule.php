<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains an object wrapper for external PHP modules
 */

/*
 * ExternalModule extended for add-on modules
 */
class SideboxModule extends InstallableModule010001
{
	/**
	 * @var string
	 */
	protected $author = 'Wildcard';

	/**
	 * @var string
	 */
	protected $author_site = '';

	/**
	 * @var string
	 */
	protected $module_site = 'https://github.com/WildcardSearch/Advanced-Sidebox';

	/**
	 * @var array
	 */
	protected $scripts = array();

	/**
	 * @var bool
	 */
	public $hasScripts = false;

	/**
	 * @var array
	 */
	protected $templates = array();

	/**
	 * @var bool
	 */
	public $xmlhttp = false;

	/**
	 * @var string
	 */
	protected $compatibility = '0';

	/**
	 * @var array
	 */
	protected $discarded_templates = array();

	/**
	 * @var bool
	 */
	protected $wrap_content = false;

	/**
	 * @var string
	 */
	protected $prefix = 'asb';

	/**
	 * @var string
	 */
	protected $path = ASB_MODULES_DIR;

	/**
	 * @var string
	 */
	protected $uninstallConstant = 'IN_ASB_UNINSTALL';

	/**
	 * @var string
	 */
	protected $cacheKey = 'asb';

	/**
	 * @var string
	 */
	protected $cacheSubKey = 'addons';

	/**
	 * attempts to load a module by name.
	 *
	 * @param  string base name
	 * @return bool success/fail
	 */
	public function load($module)
	{
		// input is necessary
		if (!parent::load($module)) {
			return false;
		}

		if (!$this->compatibility ||
			version_compare('4.0', $this->compatibility, '>')) {
			return false;
		}

		$this->hasScripts = !empty($this->scripts);

		return true;
	}

	/**
	 * remove any templates used by the module and clean up any boxes created
	 * using this add-on module
	 *
	 * @param  bool remove children
	 * @return void
	 */
	public function uninstall($cleanup=true)
	{
		parent::uninstall();

		if ($cleanup) {
			$this->removeChildren();
		}
	}

	/**
	 * called upon add-on version change to verify module's templates/settings
	 *
	 * @return void
	 */
	protected function upgrade()
	{
		global $db;

		// don't waste time if everything is in order
		if ($this->isUpgraded) {
			return;
		}

		// if any templates were dropped in this version
		if (is_array($this->discarded_templates)) {
			// delete them
			$deleteList = $sep = '';
			foreach ($this->discarded_templates as $template) {
				$deleteList .= "{$sep}'{$template}'";
				$sep = ',';
			}

			if ($deleteList) {
				$db->delete_query('templates', "title IN({$deleteList})");
			}
		}

		parent::upgrade();
	}

	/**
	 * delete all the side boxes of this type
	 *
	 * @return void
	 */
	protected function removeChildren()
	{
		global $db;

		// delete all boxes of this type in use
		$module = $db->escape_string(strtolower($this->baseName));
		$db->delete_query('asb_sideboxes', "LOWER(box_type)='{$module}'");
	}

	/**
	 * update settings for side boxes of this type
	 *
	 * @return void
	 */
	protected function updateChildren()
	{
		global $db;

		// get all boxes of this type in use
		$module = $db->escape_string(strtolower($this->baseName));
		$query = $db->simple_select('asb_sideboxes', '*', "LOWER(box_type)='{$module}'");
		if ($db->num_rows($query) == 0) {
			// this module has no children so we are done
			return;
		}

		// loop through all the children
		while ($data = $db->fetch_array($query)) {
			// create a new SideboxObject from the data
			$sidebox = new SideboxObject($data);

			if (!$sidebox->isValid()) {
				// something went wrong and this box has no ID
				// if we continue, we'll be creating a side box
				continue;
			}

			// retrieve the settings
			$sideboxSettings = $sidebox->get('settings');

			// unset any removed settings
			foreach ($sideboxSettings as $name => $setting) {
				if (!isset($this->settings[$name])) {
					unset($sideboxSettings[$name]);
				}
			}

			// update any settings which are missing
			foreach ($this->settings as $name => $setting) {
				if (!isset($sideboxSettings[$name])) {
					// new setting-- default value
					$sideboxSettings[$name] = $this->settings[$name]['value'];
				}
			}

			// save the side box
			$sidebox->set('settings', $sideboxSettings);
			$sidebox->save();
		}
	}

	/**
	 * runs template building code for the module
	 *
	 * @return mixed|bool return value of function or false
	 */
	public function buildTemplate($settings, $template_var, $width, $script)
	{
		return $this->run('build_template', $settings, $template_var, $width, $script);
	}

	/**
	 * run the modules XMLHTTP function
	 *
	 * @param  int UNIX timestamp representing last update
	 * @param  array settings
	 * @param  int column width
	 * @return mixed|bool return value of function or false
	 */
	public function doXmlhttp($dateline, $settings, $width, $script)
	{
		return $this->run('xmlhttp', $dateline, $settings, $width, $script);
	}

	/**
	 * run the module's settings_load function
	 *
	 * @return mixed|bool return value of function or false
	 */
	public function doSettingsLoad()
	{
		return $this->run('settings_load');
	}

	/**
	 * run the module's settings_save function
	 *
	 * @param  array settings
	 * @return mixed|bool return value of function or false
	 */
	public function doSettingsSave($settings)
	{
		$returnVal = $this->run('settings_save', $settings);
		if ($returnVal) {
			return $returnVal;
		}
		return $settings;
	}
}

?>
