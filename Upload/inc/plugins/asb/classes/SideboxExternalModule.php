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
class SideboxExternalModule extends ExternalModule
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
	public $has_settings = false;

	/**
	 * @var array
	 */
	protected $scripts = array();

	/**
	 * @var bool
	 */
	public $has_scripts = false;

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
	protected $is_installed = false;

	/**
	 * @var bool
	 */
	protected $is_upgraded = false;

	/**
	 * @var mixed
	 */
	protected $old_version = 0;

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

		$this->has_settings = !empty($this->settings);
		$this->has_scripts = !empty($this->scripts);
		$this->old_version = $this->get_cache_version();

		// if this module needs to be upgraded . . .
		if (isset($this->old_version) &&
			$this->old_version &&
			version_compare($this->old_version, $this->version, '<') &&
			!defined('IN_ASB_UNINSTALL')) {
			// get-r-done
			$this->upgrade();
		} else {
			// otherwise mark upgrade status
			$this->is_upgraded = $this->is_installed = true;
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

		// already installed? unless $cleanup is specifically denied . . .
		if ($this->is_installed &&
			$cleanup) {
			// . . . remove the leftovers before installing
			$this->uninstall();
		}

		// if there are templates . . .
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
	 * @param  bool leave side boxes that use this module?
	 * @return void
	 */
	public function uninstall($cleanup = true)
	{
		$this->unset_cache_version();

		// unless specifically asked not to, delete any boxes that use this module
		if ($cleanup) {
			$this->remove_children();
		}

		// if there are templates . . .
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
	 * discarded templates and ACP settings (from pre-1.4) are removed
	 *
	 * @return void
	 */
	protected function upgrade()
	{
		global $db;

		// don't waste time if everything is in order
		if ($this->is_upgraded) {
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
		if ($this->has_settings) {
			$this->update_children();
		}

		// update the version cache and the upgrade is complete
		$this->is_upgraded = $this->set_cache_version();
		$this->is_installed = true;
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
	protected function remove_children()
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
	protected function update_children()
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
				// if we continue, we'll be creating a side box when we save
				// so . . . don't ;)
				continue;
			}

			// retrieve the settings
			$sidebox_settings = $sidebox->get('settings');

			// unset any removed settings
			foreach ($sidebox_settings as $name => $setting) {
				if (!isset($this->settings[$name])) {
					unset($sidebox_settings[$name]);
				}
			}

			// update any settings which are missing
			foreach ($this->settings as $name => $setting) {
				if (!isset($sidebox_settings[$name])) {
					// new setting-- default value
					$sidebox_settings[$name] = $this->settings[$name]['value'];
				}
			}

			// save the side box
			$sidebox->set('settings', $sidebox_settings);
			$sidebox->save();
		}
	}

	/**
	 * version control derived from the work of pavemen in MyBB Publisher
	 *
	 * @return string|int version or 0
	 */
	protected function get_cache_version()
	{
		global $cache;

		// get currently installed version, if there is one
		$asb = $cache->read('asb');

		if (is_array($asb['addon_versions']) &&
			isset($asb['addon_versions'][$this->baseName]) &&
			isset($asb['addon_versions'][$this->baseName]['version'])) {
			return $asb['addon_versions'][$this->baseName]['version'];
		}
		return 0;
	}

	/**
	 * version control derived from the work of pavemen in MyBB Publisher
	 *
	 * @return bool true
	 */
	protected function set_cache_version()
	{
		global $cache;

		//update version cache to latest
		$asb = $cache->read('asb');
		$asb['addon_versions'][$this->baseName]['version'] = $this->version;
		$cache->update('asb', $asb);
		return true;
	}

	/**
	 * version control derived from the work of pavemen in MyBB Publisher
	 *
	 * @return bool true
	 */
	protected function unset_cache_version()
	{
		global $cache;

		$asb = $cache->read('asb');
		if (isset($asb['addon_versions'][$this->baseName])) {
			unset($asb['addon_versions'][$this->baseName]);
		}
		$cache->update('asb', $asb);
		return true;
	}

	/**
	 * runs template building code for the current module referenced by this object
	 *
	 * @return mixed|bool return value of function or false
	 */
	public function build_template($settings, $template_var, $width, $script)
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
	public function do_xmlhttp($dateline, $settings, $width)
	{
		foreach (array('dateline', 'settings', 'width') as $key) {
			$args[$key] = $$key;
		}
		return $this->run('xmlhttp', $args);
	}

	/**
	 * run the module's setting_load function
	 *
	 * @return mixed|bool return value of function or false
	 */
	public function do_settings_load()
	{
		return $this->run('settings_load', $settings);
	}

	/**
	 * run the module's do_settings_save function
	 *
	 * @param  array settings
	 * @return mixed|bool return value of function or false
	 */
	public function do_settings_save($settings)
	{
		$retval = $this->run('settings_save', $settings);
		if ($retval) {
			return $retval;
		}
		return $settings;
	}
}

?>
