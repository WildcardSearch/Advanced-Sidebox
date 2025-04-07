<?php
/**
 * Wildcard Helper Classes - External PHP Module Wrapper
 * class definition
 */

/**
 * a standard wrapper for external PHP routines built upon the
 * MalleableObject abstract class and abiding by ExternalModuleInterface
 */
abstract class ExternalModule020100 extends MalleableObject010000 implements ExternalModuleInterface020100
{
	/**
	 * @var string the module title
	 */
	protected $title = '';

	/**
	 * @var string the module description
	 */
	protected $description = '';

	/**
	 * @var string the module version
	 */
	protected $version = '0';

	/**
	 * @var the module path
	 */
	protected $path = '';

	/**
	 * @var the module prefix
	 */
	protected $prefix = '';

	/**
	 * @var the internal module name
	 */
	protected $baseName = '';

	/**
	 * attempt to load and validate the module
	 *
	 * @param  string base name of the module to load
	 * @param  string fully qualified path to the modules
	 * @return void
	 */
	public function __construct($module)
	{
		$this->valid = $this->load($module);
	}

	/**
	 * attempt to load the module's info
	 *
	 * @param  string base name of the module to load
	 * @return bool true on success, false on fail
	 */
	public function load($module)
	{
		if (!$module ||
			!$this->path ||
			!$this->prefix) {
			return false;
		}

		// store the unique name
		$this->baseName = trim($module);

		// store the info
		$info = $this->run('info');
		return $this->set($info);
	}

	/**
	 * safely access the module's functions
	 *
	 * @param  string function name
	 * @param  array
	 * @return mixed
	 */
	public function run($function_name)
	{
		$function_name = trim($function_name);
		if (!$function_name ||
			!$this->baseName ||
			!$this->path ||
			!$this->prefix) {
			return false;
		}

		$fullpath = "{$this->path}/{$this->baseName}.php";
		if (!file_exists($fullpath)) {
			return false;
		}
		require_once $fullpath;

		$this_function = "{$this->prefix}_{$this->baseName}_{$function_name}";
		if (!function_exists($this_function)) {
			return false;
		}

		$args = func_get_args();
		
		if (!is_array($args) ||
			empty($args) ||
			count($args) <= 1) {
			return $this_function();
		}

		array_shift($args);

		return call_user_func_array($this_function, $args);
	}

	/**
	 * physically delete the module from the server
	 *
	 * @return bool
	 */
	public function remove()
	{
		$filename = "{$this->path}/{$this->baseName}.php";
		@unlink($filename);

		return !file_exists($filename);
	}
}