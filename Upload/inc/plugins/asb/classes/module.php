<?php
/*
 * Plug-in Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2013 WildcardSearch
 * http://www.wildcardsworld.com
 *
 * this file contains an object wrapper for external PHP modules
 */

if(!class_exists('MalleableObject'))
{
	require_once MYBB_ROOT . "inc/plugins/asb/classes/malleable.php";
}

/*
 * standard interface for external PHP modules
 *
 * can be used to wrap any external PHP module for secure loading, validation and execution of its functions
 */
interface ExternalModuleInterface
{
	public function load($name);
	public function run($function_name, $args = '');
}

/*
 * a standard wrapper for external PHP routines built upon the MalleableObject abstract class and abiding by ExternalModuleInterface
 */
abstract class ExternalModule extends MalleableObject implements ExternalModuleInterface
{
	protected $title = '';
	protected $description = '';
	protected $version = 0;
	protected $path = '';
	protected $prefix = '';
	protected $base_name = '';

	/*
	 * __construct()
	 *
	 * attempt to load and validate the module
	 *
	 * @param - $name - (string) base name of the module to load
	 * @param - $path - (string) fully qualified path to the modules
	 */
	function __construct($module)
	{
		// if there is data
		if($module)
		{
			// attempt to load it and return the results
			$this->valid = $this->load($module);
			return;
		}
		// new object
		$this->valid = false;
	}

	/*
	 * load($name)
	 *
	 * attempt to load the module's info
	 *
	 * @param - $name - (string) base name of the module to load
	 *
	 * returns: true if successfully loaded and validated/false if not
	 */
	public function load($module)
	{
		// good info?
		if($module && $this->path && $this->prefix)
		{
			// store the unique name
			$this->base_name = trim($module);

			// store the info
			$info = $this->run('info');
			return $this->set($info);
		}
		return false;
	}

	/*
	 * run()
	 *
	 * safely access the module's function
	 *
	 * @param - $function_name - (string)
	 * @param - $args - (array) any data to pass to the function
	 */
	public function run($function_name, $args = '')
	{
		$function_name = trim($function_name);
		if($function_name && $this->base_name && $this->path && $this->prefix)
		{
			$fullpath = "{$this->path}/{$this->base_name}.php";
			if(file_exists($fullpath))
			{
				require_once $fullpath;

				$this_function = "{$this->prefix}_{$this->base_name}_{$function_name}";
				if(function_exists($this_function))
				{
					return $this_function($args);
				}
			}
		}
		return false;
	}
}

/*
 * Sidebox_type extended for add-on modules
 */
class Addon_type extends ExternalModule
{
	protected $author = 'Wildcard';
	protected $author_site = 'http://wildcardsearch.github.com/Advanced-Sidebox';
	protected $settings = array();
	public $has_settings = false;
	protected $templates = array();
	public $xmlhttp = false;
	protected $is_installed = false;
	protected $is_upgraded = false;
	protected $old_version = 0;
	protected $version = 0;
	protected $discarded_settings = array();
	protected $discarded_templates = array();
	protected $wrap_content = false;

	/*
	 * __construct()
	 *
	 * called upon creation. loads module if possible and attempts to validate
	 */
	public function __construct($module)
	{
		$this->path = ASB_MODULES_DIR;
		$this->prefix = 'asb';
		parent::__construct($module);
	}

	/*
	 * load()
	 *
	 * attempts to load a module by name.
	 */
	public function load($module)
	{
		// input is necessary
		if($module && parent::load($module))
		{
			$this->old_version = $this->get_cache_version();

			// if this module needs to be upgraded . . .
			if(version_compare($this->old_version, $this->version, '<'))
			{
				// get-r-done
				$this->upgrade();
			}
			else
			{
				// otherwise mark upgrade status
				$this->is_installed = true;
				$this->is_upgraded = true;
			}

			$this->has_settings = !empty($this->settings);
			return true;
		}
		return false;
	}

	/*
	 * install()
	 *
	 * install templates if they exist to allow the add-on module to function correctly
	 */
	protected function install($cleanup = true)
	{
		global $db;

		// already installed? unless $cleanup is specifically denied . . .
		if($this->is_installed && $cleanup)
		{
			// . . . remove the leftovers before installing
			$status = $this->uninstall();
		}

		// if there are templates . . .
		if(is_array($this->templates))
		{
			// loop through them
			foreach($this->templates as $template)
			{
				$query = $db->simple_select('templates', '*', "title='{$template['title']}'");

				// if it exists, update
				if($db->num_rows($query) == 1)
				{
					$status = $db->update_query("templates", $template, "title='{$template['title']}'");
				}
				else
				{
					// if not, create a new template
					$status = $db->insert_query("templates", $template);
				}

				if(!$status)
				{
					$error = true;
				}
			}
		}
		return $error;
	}

	/*
	 * uninstall()
	 *
	 * remove any templates used by the module and clean up any boxes created using this add-on module
	 *
	 * @param - $cleanup, when false instructs the method to leave any side boxes that use this module behind when uninstalling. this is useful for when we want to upgrade an add-on without losing admin's work
	 */
	public function uninstall($cleanup = true)
	{
		global $db;

		// installed?
		if($this->is_installed)
		{
			$this->unset_cache_version();

			// if there are templates . . .
			if(is_array($this->templates))
			{
				// remove them all
				foreach($this->templates as $template)
				{
					$status = $db->delete_query('templates', "title='{$template['title']}'");
				}

				if(!$status)
				{
					$error = true;
				}

				// unless specifically asked not to, delete any boxes that use this module
				if($cleanup)
				{
					$this->remove_children();
				}
			}
		}
		return $error;
	}

	/*
	 * upgrade()
	 *
	 * called upon add-on version change to verify module's templates/settings
	 * discarded templates and ACP settings (from pre-1.4) are removed
	 */
	protected function upgrade()
	{
		global $db;

		// don't waste time if everything is in order
		if(!$this->is_upgraded)
		{
			$this->unset_cache_version();

			// if there are settings left over from a previous installation . . .
			if(is_array($this->discarded_settings))
			{
				// delete them all
				foreach($this->discarded_settings as $setting)
				{
					$status = $db->delete_query('settings', "name='{$setting}'");
				}

				if(!$status)
				{
					$error = true;
				}
			}

			// if any templates were dropped in this version
			if(is_array($this->discarded_templates))
			{
				// delete them
				foreach($this->discarded_templates as $template)
				{
					$status = $db->delete_query('templates', "title='{$template}'");

					if(!$status)
					{
						$error = true;
					}
				}
			}

			/*
			 * install the updated module
			 *
			 * $cleanup = false directs the install method not to uninstall the module as normal
			 */
			$this->install(false);

			// update the version cache and the upgrade is complete
			$this->is_upgraded = $this->set_cache_version();
			$this->is_installed = true;

			return $error;
		}
	}

	/*
	 * remove()
	 *
	 * uninstalls (if necessary) and physically deletes the module from the server
	 */
	public function remove()
	{
		// make sure no trash is left behind
		$this->uninstall();

		// nuke it
		$filename = "{$this->path}/{$this->base_name}.php";
		@unlink($filename);

		return !file_exists($filename);
	}

	/*
	 * remove_children()
	 *
	 * delete all the side boxes of this type
	 */
	protected function remove_children()
	{
		global $db;

		// delete all boxes of this type in use
		$module = $db->escape_string(strtolower($this->base_name));
		$db->delete_query('asb_sideboxes', "LOWER(box_type)='{$module}'");
	}

	/*
	 * get_cache_version()
	 *
	 * version control derived from the work of pavemen in MyBB Publisher
	 */
	protected function get_cache_version()
	{
		global $cache;

		// get currently installed version, if there is one
		$asb = $cache->read('asb');

		if(is_array($asb['addon_versions']))
		{
			return $asb['addon_versions'][$this->base_name]['version'];
		}
		return 0;
	}

	/*
	 * set_cache_version()
	 *
	 * version control derived from the work of pavemen in MyBB Publisher
	 */
	protected function set_cache_version()
	{
		global $cache;

		//update version cache to latest
		$asb = $cache->read('asb');
		$asb['addon_versions'][$this->base_name]['version'] = $this->version;
		$cache->update('asb', $asb);
		return true;
	}

	/*
	 * unset_cache_version()
	 *
	 * version control derived from the work of pavemen in MyBB Publisher
	 */
	protected function unset_cache_version()
	{
		global $cache;

		$asb = $cache->read('asb');
		if(isset($asb['addon_versions'][$this->base_name]))
		{
			unset($asb['addon_versions'][$this->base_name]);
		}
		$cache->update('asb', $asb);
		return true;
	}

	/*
	 * get_xmlhttp_script()
	 *
	 * @param - (int) $rate
						the AJAX refresh rate in seconds
	 * @param - (string) $elem_id
						the HTML ID for the side box's main table
	 */
	public function get_xmlhttp_script($rate, $elem_id)
	{
		// valid info?
		if($this->xmlhttp && $rate)
		{
			// build a periodical executer with the supplied refresh rate; on each execution launch an Ajax Request and if anything besides 'nochange' is returned then replace the contents of this side box with the returned HTML and update the HTML name property of the side box's main table to indicate the time of the last update
			return <<<EOF
	new PeriodicalExecuter
	(
		function(pe)
		{
			new Ajax.Request
				(
					'inc/plugins/asb/xmlhttp.php',
					{
						parameters:
						{
							action: 'do_module',
							box_type: '{$this->base_name}',
							dateline: $('{$elem_id}').readAttribute('name'),
							width_left: asb_width_left,
							width_right: asb_width_right
						},
						onSuccess: function(response)
						{
							if(response.responseText && response.responseText != 'nochange')
							{
								$('{$elem_id}').down('tbody').innerHTML = response.responseText;
								var table_elem_id = $('{$elem_id}').id;
								var table_info_array = table_elem_id.split("_");
								var table_id = table_info_array[table_info_array.length - 1];
								$('{$elem_id}').setAttribute('name', table_id +  '_{$this->base_name}_' + Math.floor((new Date).getTime()/1000));
							}
						}
					}
				);
		}, {$rate}
	);
EOF;
		}
	}

	/*
	 * build_template()
	 *
	 * runs template building code for the current module referenced by this object
	 */
	public function build_template($settings, $template_var, $width)
	{
		foreach(array('settings', 'template_var', 'width') as $key)
		{
			$args[$key] = $$key;
		}
		return $this->run('build_template', $args);
	}

	/*
	 * function do_xmlhttp()
	 *
	 * @param - (int) $dateline
						UNIX timestamp representing the last time the side box was updated
	 * @param - (array) $settings
						the individual side box settings
	 * @param - (int) $width
						the width of the column in which the produced side box will reside
	 */
	public function do_xmlhttp($dateline, $settings, $width)
	{
		foreach(array('dateline', 'settings', 'width') as $key)
		{
			$args[$key] = $$key;
		}
		return $this->run('xmlhttp', $args);
	}
}

?>
