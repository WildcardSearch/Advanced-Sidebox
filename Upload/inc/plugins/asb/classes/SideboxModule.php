<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * https://www.rantcentralforums.com
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
	 * @var string
	 */
	protected $noContentTemplate = 'asb_sidebox_no_content';

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
			version_compare(ASB_MIN_MODULE_VERSION, $this->compatibility, '>')) {
			return false;
		}

		$this->hasScripts = !empty($this->scripts);

		if ($this->xmlhttp) {
			$this->buildXmlhttpSettings();
		}

		return true;
	}

	/**
	 * build default settings added for modules with XMLHTTP capabilities
	 *
	 * @return void
	 */
	protected function buildXmlhttpSettings()
	{
		global $lang;

		if (!$lang->asb_addon) {
			$lang->load('asb_addon');
		}

		$this->settings['xmlhttp_refresh_rate'] = array(
			'name' => 'xmlhttp_refresh_rate',
			'title' => $lang->asb_xmlhttp_refresh_rate_title,
			'description' => $lang->asb_xmlhttp_refresh_rate_description,
			'optionscode' => 'text',
			'value' => '0',
		);

		$this->settings['xmlhttp_refresh_decay'] = array(
			'name' => 'xmlhttp_refresh_decay',
			'title' => $lang->asb_xmlhttp_refresh_decay_title,
			'description' => $lang->asb_xmlhttp_refresh_decay_description,
			'optionscode' => 'text',
			'value' => '1',
		);

		$this->hasSettings = true;
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
	public function buildContent($settings, $template_var, $script)
	{
		global $mybb, $templates, $theme, $lang, $$template_var;

		$content = $this->run('get_content', $settings, $script, TIME_NOW, $template_var);

		if (!empty($content)) {
			$$template_var = $content;
			return true;
		} elseif ($mybb->settings['asb_show_empty_boxes']) {
			eval("\${$template_var} = \"".$templates->get($this->noContentTemplate)."\";");
			return true;
		}

		return false;
	}

	/**
	 * run the modules XMLHTTP function
	 *
	 * @param  int UNIX timestamp representing last update
	 * @param  array settings
	 * @param  int column width
	 * @return mixed|bool return value of function or false
	 */
	public function doXmlhttp($settings, $script, $dateline)
	{
		return $this->run('get_content', $settings, $script, (int) $dateline);
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