<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * https://www.rantcentralforums.com
 *
 * wrapper to handle our plugin's cache
 */

class AdvancedSideboxCache extends WildcardPluginCache010300
{
	/**
	 * @var  string cache key
	 */
	protected $cacheKey = 'asb';

	/**
	 * @var  string cache sub key
	 */
	protected $subKey = '';

	/**
	 * return an instance of the cache wrapper
	 *
	 * @return instance of the child class
	 */
	static public function getInstance()
	{
		static $instance;
		if (!isset($instance)) {
			$instance = new AdvancedSideboxCache;
		}

		return $instance;
	}

	/**
	 * retrieve the side box data, rebuilding it if necessary
	 *
	 * @return array
	 */
	public function getCache()
	{
		$data = $this->read();

		// if the cache has never been built or has been marked as changed
		// then rebuild and store it
		if ((int) $data['last_run'] == 0 ||
			$data['has_changed']) {
			$this->buildCache($data);
			$this->update(null, $data);
		}

		// returned the cached info
		return $data;
	}

	/**
	 * build all of the relevant info needed to manage side boxes
	 *
	 * @param  array cache data variable
	 * @return void
	 */
	public function buildCache(&$data)
	{
		global $db;

		// fresh start
		$data['custom'] = $data['sideboxes'] = $data['scripts'] = $data['all_scripts'] = array();

		// update the run time and changed flag before we even start
		$data['last_run'] = TIME_NOW;
		$data['has_changed'] = false;

		// get all the active scripts' info
		$allScripts = asbGetAllScripts();

		// no scripts, no work to do
		if (!is_array($allScripts) ||
			empty($allScripts)) {
			return;
		}

		// store the script definitions and a master list
		foreach ($allScripts as $tid => $scripts) {
			foreach ($scripts as $filename => $script) {
				$data['scripts'][$tid][$filename] = $script;
			}
		}

		$data['all_themes'] = array_keys($data['scripts']);
		$data['all_scripts'] = array_keys($allScripts);

		// load all detected modules
		$addons = asbGetAllModules();

		// get any custom boxes
		$custom = asbGetAllCustomBoxes();

		// get any sideboxes
		$sideboxes = asbGetAllSideBoxes();

		if (!is_array($sideboxes) ||
			empty($sideboxes)) {
			return;
		}

		foreach ($sideboxes as $sidebox) {
			// build basic data
			$scripts = $sidebox->get('scripts');
			$id = (int) $sidebox->get('id');
			$pos = $sidebox->get('position') ? 1 : 0;
			$data['sideboxes'][$id] = $sidebox->get('data');
			$module = $sidebox->get('box_type');

			// no scripts == all scripts
			if (empty($scripts)) {
				// add this side box to the 'global' set (to be merged with the current script when applicable)
				$scripts = array('global');
			}

			// for each script in which the side box is used, add a pointer and if it is a custom box, cache its contents
			foreach ($scripts as $filename) {
				foreach ($data['all_themes'] as $tid) {
					if ($tid > 0 &&
						(!isset($data['scripts'][$tid][$filename]) ||
						empty($data['scripts'][$tid][$filename])) &&
						(isset($data['scripts'][0][$filename]) &&
						!empty($data['scripts'][0][$filename]))) {
						$data['scripts'][$tid][$filename] = $data['scripts'][0][$filename];
						continue;
					}

					// side box from a module?
					if (isset($addons[$module]) &&
						$addons[$module] instanceof SideboxModule) {
						// store the module name and all the template vars used
						$data['scripts'][$tid][$filename]['sideboxes'][$pos][$id] = $module;
						$data['scripts'][$tid][$filename]['template_vars'][$id] = "{$module}_{$id}";

						// if there are any templates get their names so we can cache them
						$installData = $addons[$module]->get('installData');
						if (is_array($installData['templates']) &&
							!empty($installData['templates'])) {
							foreach ($installData['templates'] as $template) {
								$data['scripts'][$tid][$filename]['templates'][] = $template['title'];
							}
						}

						// AJAX?
						if ($addons[$module]->xmlhttp) {
							$settings = $sidebox->get('settings');

							// again, default here is off if anything goes wrong
							if ($settings['xmlhttp_refresh_rate']) {
								if ($settings['xmlhttp_refresh_decay'] < 1) {
									$settings['xmlhttp_refresh_decay'] = 1;
								}

								// add the info
								$data['scripts'][$tid][$filename]['extra_scripts'][$id]['position'] = $pos;
								$data['scripts'][$tid][$filename]['extra_scripts'][$id]['module'] = $module;
								$data['scripts'][$tid][$filename]['extra_scripts'][$id]['rate'] = $settings['xmlhttp_refresh_rate'];
								$data['scripts'][$tid][$filename]['extra_scripts'][$id]['decay'] = $settings['xmlhttp_refresh_decay'];
							}
						}

						if ($addons[$module]->hasScripts) {
							foreach ($addons[$module]->get('scripts') as $script) {
								$data['scripts'][$tid][$filename]['js'][$script] = $script;
							}
						}
					// side box from a custom box?
					} else if(isset($custom[$module]) &&
						$custom[$module] instanceof CustomSidebox) {
						// store the pointer
						$data['scripts'][$tid][$filename]['sideboxes'][$pos][$id] = $module;
						$data['scripts'][$tid][$filename]['template_vars'][$id] = "{$module}_{$id}";

						// and cache the contents
						$data['custom'][$module] = $custom[$module]->get('data');
					}
				}
			}
		}
	}
}