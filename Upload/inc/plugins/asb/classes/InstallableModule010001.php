<?php
/*
 * Wildcard Helper Classes
 * InstallableModule Class Structure
 */

abstract class InstallableModule010001 extends ConfigurableModule010101 implements InstallableModuleInterface010000
{
	/**
	 * @var array
	 */
	protected $installData = array();

	/**
	 * @var bool
	 */
	protected $isInstalled = false;

	/**
	 * @var bool
	 */
	protected $isUpgraded = false;

	/**
	 * @var object copy of MyBB cache
	 */
	protected $cache = null;

	/**
	 * @var array
	 */
	protected $cacheData = array();

	/**
	 * @var string
	 */
	protected $cacheKey = '';

	/**
	 * @var string
	 */
	protected $cacheSubKey = '';

	/**
	 * @var string
	 */
	protected $settingGroupName = '';

	/**
	 * @var string
	 */
	protected $uninstallConstant = '';

	/**
	 * @var string
	 */
	protected $debugMode = false;

	/**
	 * attempt to load and validate the module
	 *
	 * @param  string base name of the module to load
	 * @return void
	 */
	public function load($module)
	{
		if (!parent::load($module) ||
			!$this->loadCache()) {
			return false;
		}

		$currentVersion = $this->getCacheVersion();

		// new module
		if ((!isset($currentVersion) ||
			$currentVersion === 0)) {
			$this->install();
		// newly updated module
		} elseif ($this->debugMode === true ||
			($currentVersion &&
			version_compare($currentVersion, $this->version, '<') &&
			(!$this->uninstallConstant || !defined($this->uninstallConstant) ))) {
			$this->upgrade();
		// pre-existing module
		} else {
			// otherwise mark upgrade status
			$this->isUpgraded = $this->isInstalled = true;
		}

		return true;
	}

	/**
	 * get the addon's cache data
	 *
	 * @return bool
	 */
	protected function loadCache()
	{
		if (!$this->cacheKey) {
			return false;
		}

		global $cache;

		$this->cache = &$cache;
		$data = $cache->read($this->cacheKey);

		if ($this->cacheSubKey) {
			$this->cacheData = $data[$this->cacheSubKey][$this->baseName];
		} else {
			$this->cacheData = $data[$this->baseName];
		}

		return true;
	}

	/**
	 *
	 *
	 * @return bool
	 */
	public function install($cleanup=true)
	{
		if ($this->isInstalled &&
			$cleanup === true) {
			$this->uninstall(true);
		}

		$this->addTemplates();
		$this->addSettings();
		$this->setCacheVersion();

		return $this->isUpgraded = $this->isInstalled = true;
	}

	/**
	 *
	 *
	 * @param  bool
	 * @return void
	 */
	public function uninstall($cleanup=true)
	{
		$this->removeSettings();
		$this->removeTemplates();
		$this->unsetCacheVersion();
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

		return parent::remove();
	}

	/**
	 * remove/update templates/settings on version bump or debug mode
	 *
	 * @return string the return of the module routine
	 */
	protected function upgrade()
	{
		global $db;

		// don't waste time if everything is in order
		if ($this->isUpgraded) {
			return;
		}

		// if any templates were dropped in this version
		if (is_array($this->installData['removedTemplates'])) {
			// delete them
			$deleteList = $sep = '';
			foreach ($this->installData['removedTemplates'] as $template) {
				$deleteList .= "{$sep}'{$template}'";
				$sep = ',';
			}

			if ($deleteList) {
				$db->delete_query('templates', "title IN({$deleteList})");
			}
		}

		/*
		 * install the updated module
		 *
		 * $cleanup = false directs the install method not to uninstall the module as normal
		 */
		$this->install(false);

		// update the version cache and the upgrade is complete
		$this->setCacheVersion();
		$this->isUpgraded = $this->isInstalled = true;
	}

	/**
	 * install/update module templates
	 *
	 * @return string the return of the module routine
	 */
	protected function addTemplates()
	{
		global $db;

		if (empty($this->installData) ||
			empty($this->installData['templates'])) {
			return false;
		}

		$insertArray = array();
		foreach ($this->installData['templates'] as $template) {
			$template['sid'] = -2;
			$query = $db->simple_select('templates', '*', "title='{$template['title']}' AND sid IN('-2', '-1')");

			// if it exists, update
			if ($db->num_rows($query) > 0) {
				$db->update_query('templates', $template, "title='{$template['title']}' AND sid IN('-2', '-1')");
			} else {
				// if not, create a new template
				$insertArray[] = $template;
			}
		}

		if (!empty($insertArray)) {
			$db->insert_query_multiple('templates', $insertArray);
		}

		return true;
	}

	/**
	 * install/update module settings
	 *
	 * @return string the return of the module routine
	 */
	protected function addSettings()
	{
		global $db;

		if (empty($this->installData) ||
			empty($this->installData['settings']) ||
			empty($this->settingGroupName)) {
			return false;
		}

		$query = $db->simple_select('settinggroups', 'gid', "name='{$this->settingGroupName}'");
		$gid = (int) $db->fetch_field($query, 'gid');
		if (!$gid) {
			return false;
		}

		$insertArray = array();
		foreach ($this->installData['settings'] as $name => $setting) {
			$setting['gid'] = $gid;

			// does the setting already exist?
			$query = $db->simple_select('settings', 'sid', "name='{$name}'");
			if ($db->num_rows($query) > 0) {
				$setting['sid'] = (int) $db->fetch_field($query, 'sid');

				// update the info (but leave the value alone)
				unset($setting['value']);
				$db->update_query('settings', $setting, "sid='{$setting['sid']}'");
			} else {
				$insertArray[] = $setting;
			}
		}

		if (!empty($insertArray)) {
			$db->insert_query_multiple('settings', $insertArray);
		}

		rebuild_settings();

		return true;
	}

	/**
	 * remove module templates
	 *
	 * @return void
	 */
	protected function removeTemplates()
	{
		global $db;

		// remove them all
		$deleteList = $sep = '';
		foreach ((array) $this->installData['templates'] as $template) {
			$deleteList .= "{$sep}'{$template['title']}'";
			$sep = ',';
		}

		if ($deleteList) {
			$db->delete_query('templates', "title IN({$deleteList})");
		}
	}

	/**
	 * remove module settings
	 *
	 * @return void
	 */
	protected function removeSettings()
	{
		global $db;

		// remove them all
		$deleteList = $sep = '';
		foreach ((array) $this->installData['settings'] as $setting) {
			$deleteList .= "{$sep}'{$setting['name']}'";
			$sep = ',';
		}

		if ($deleteList) {
			$db->delete_query('settings', "name IN({$deleteList})");
		}
	}

	/**
	 * retrieve cache version
	 *
	 * @return string|int version or 0
	 */
	protected function getCacheVersion()
	{
		return $this->cacheData['version'] ? $this->cacheData['version'] : 0;
	}

	/**
	 * update cache version
	 *
	 * @return void
	 */
	protected function setCacheVersion()
	{
		$cache = $this->cache->read($this->cacheKey);

		$this->cacheData['version'] = $this->version;
		if ($this->cacheSubKey) {
			$cache[$this->cacheSubKey][$this->baseName] = $this->cacheData;
		} else {
			$cache = $this->cacheData[$this->baseName];
		}

		$this->cache->update($this->cacheKey, $cache);
	}

	/**
	 * clear cache version
	 *
	 * @return void
	 */
	protected function unsetCacheVersion()
	{
		$data = $this->cache->read($this->cacheKey);

		if (!is_array($data)) {
			$data = array();
		}

		if ($this->cacheSubKey) {
			unset($data[$this->cacheSubKey]);
		} else {
			unset($data);
		}

		$this->cache->update($this->cacheKey, $data);
	}
}