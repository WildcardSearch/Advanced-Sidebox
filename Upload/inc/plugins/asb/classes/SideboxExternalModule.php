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
class SideboxExternalModule extends ExternalModule010000
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
	protected $settings = array();

	/**
	 * @var bool
	 */
	public $hasSettings = false;

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
	 * @var bool
	 */
	protected $isInstalled = false;

	/**
	 * @var bool
	 */
	protected $isUpgraded = false;

	/**
	 * @var string
	 */
	protected $version = '0';

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
			version_compare('2.1', $this->compatibility, '>')) {
			return false;
		}

		$this->hasSettings = !empty($this->settings);
		$this->hasScripts = !empty($this->scripts);
		$oldVersion = $this->getCacheVersion();

		// new module
		if ((!isset($oldVersion) ||
			$oldVersion === 0) &&
			!defined('IN_ASB_UNINSTALL')) {
			$this->install();
		// newly updated module
		} elseif ($oldVersion &&
			version_compare($oldVersion, $this->version, '<') &&
			!defined('IN_ASB_UNINSTALL')) {
			$this->upgrade();
		// pre-existing module
		} else {
			// otherwise mark upgrade status
			$this->isUpgraded = $this->isInstalled = true;
		}
		return true;
	}

	/**
	 * install templates if they exist to allow the add-on module to function correctly
	 *
	 * @param  bool remove install data before beginning?
	 * @return void
	 */
	public function install($cleanup = true)
	{
		global $db;

		// already installed? unless $cleanup is specifically denied...
		if ($this->isInstalled &&
			$cleanup) {
			// ...remove the leftovers before installing
			$this->uninstall();
		}

		$this->isUpgraded = $this->isInstalled = true;
		$this->setCacheVersion();

		// if there are no templates we're done
		if (!is_array($this->templates)) {
			return;
		}

		$insert_array = array();
		foreach ($this->templates as $template) {
			$template['sid'] = -2;
			$query = $db->simple_select('templates', '*', "title='{$template['title']}' AND sid IN('-2', '-1')");

			// if it exists, update
			if ($db->num_rows($query) > 0) {
				$db->update_query('templates', $template, "title='{$template['title']}' AND sid IN('-2', '-1')");
			} else {
				// if not, create a new template
				$insert_array[] = $template;
			}
		}

		if (!empty($insert_array)) {
			$db->insert_query_multiple('templates', $insert_array);
		}
	}

	/**
	 * remove any templates used by the module and clean up any boxes created
	 * using this add-on module
	 *
	 * @param  bool remove children
	 * @return void
	 */
	public function uninstall($cleanup = true)
	{
		$this->unsetCacheVersion();

		// unless specifically asked not to, delete any boxes that use this module
		if ($cleanup) {
			$this->removeChildren();
		}

		// if there are no templates we're done
		if (!is_array($this->templates)) {
			return;
		}

		// remove them all
		$delete_list = $sep = '';
		foreach ($this->templates as $template) {
			$delete_list .= "{$sep}'{$template['title']}'";
			$sep = ',';
		}

		if ($delete_list) {
			global $db;
			$db->delete_query('templates', "title IN({$delete_list})");
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
			$delete_list = $sep = '';
			foreach ($this->discarded_templates as $template) {
				$delete_list .= "{$sep}'{$template}'";
				$sep = ',';
			}

			if ($delete_list) {
				$db->delete_query('templates', "title IN({$delete_list})");
			}
		}

		/*
		 * install the updated module
		 *
		 * $cleanup = false directs the install method not to uninstall the module as normal
		 */
		$this->install(false);
		if ($this->hasSettings) {
			$this->updateChildren();
		}

		// update the version cache and the upgrade is complete
		$this->isUpgraded = $this->setCacheVersion();
		$this->isInstalled = true;
	}

	/**
	 * uninstalls (if necessary) and physically deletes the module from the server
	 *
	 * @return bool success/fail
	 */
	public function remove()
	{
		// make sure no trash is left behind
		$this->uninstall();

		// nuke it
		$filename = "{$this->path}/{$this->baseName}.php";
		@unlink($filename);

		return !file_exists($filename);
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
	 * version control
	 *
	 * @return string|int version or 0
	 */
	protected function getCacheVersion()
	{
		$addonVersions = AdvancedSideboxCache::getInstance()->read('addon_versions');

		if (is_array($addonVersions) &&
			isset($addonVersions[$this->baseName]) &&
			isset($addonVersions[$this->baseName]['version'])) {
			return $addonVersions[$this->baseName]['version'];
		}
		return 0;
	}

	/**
	 * version control
	 *
	 * @return bool true
	 */
	protected function setCacheVersion()
	{
		$myCache = AdvancedSideboxCache::getInstance();

		// update version cache to latest
		$addonVersions = $myCache->read('addon_versions');
		$addonVersions[$this->baseName]['version'] = $this->version;
		$myCache->update('addon_versions', $addonVersions);
		return true;
	}

	/**
	 * version control
	 *
	 * @return bool true
	 */
	protected function unsetCacheVersion()
	{
		$myCache = AdvancedSideboxCache::getInstance();

		$addonVersions = $myCache->read('addon_versions');
		if (isset($addonVersions[$this->baseName])) {
			unset($addonVersions[$this->baseName]);
		}
		$myCache->update('addon_versions', $addonVersions);
		return true;
	}

	/**
	 * runs template building code for the module
	 *
	 * @return mixed|bool return value of function or false
	 */
	public function buildTemplate($settings, $template_var, $width, $script)
	{
		foreach (array('settings', 'template_var', 'width', 'script') as $key) {
			$args[$key] = $$key;
		}
		return $this->run('build_template', $args);
	}

	/**
	 * run the modules XMLHTTP function
	 *
	 * @param  int UNIX timestamp representing last update
	 * @param  array settings
	 * @param  int column width
	 * @return mixed|bool return value of function or false
	 */
	public function doXmlhttp($dateline, $settings, $width)
	{
		foreach (array('dateline', 'settings', 'width') as $key) {
			$args[$key] = $$key;
		}
		return $this->run('xmlhttp', $args);
	}

	/**
	 * run the module's settings_load function
	 *
	 * @return mixed|bool return value of function or false
	 */
	public function doSettingsLoad()
	{
		return $this->run('settings_load', $settings);
	}

	/**
	 * run the module's settings_save function
	 *
	 * @param  array settings
	 * @return mixed|bool return value of function or false
	 */
	public function doSettingsSave($settings)
	{
		$retval = $this->run('settings_save', $settings);
		if ($retval) {
			return $retval;
		}
		return $settings;
	}
}

?>
