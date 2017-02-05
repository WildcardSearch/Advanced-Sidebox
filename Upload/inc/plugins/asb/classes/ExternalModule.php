<?php
/*
 * Wildcard Helper Classes
 * MalleableObject Class Structure
 */

if(!class_exists('MalleableObject'))
{
	require_once MYBB_ROOT . 'inc/plugins/asb/classes/MalleableObject.php';
}

/*
 * standard interface for external PHP modules
 *
 * can be used to wrap any external PHP module for secure loading,
 * validation and execution of its functions
 */
interface ExternalModuleInterface
{
	public function load($name);
	public function run($function_name, $args = '');
}

/*
 * a standard wrapper for external PHP routines built upon the
 * MalleableObject abstract class and abiding by ExternalModuleInterface
 */
abstract class ExternalModule extends MalleableObject implements ExternalModuleInterface
{
	protected $title = '';
	protected $description = '';
	protected $version = 0;
	protected $public_version = 0;
	protected $path = '';
	protected $prefix = '';
	protected $baseName = '';

	/*
	 * attempt to load and validate the module
	 *
	 * @param string base name of the module to load
	 * @param string fully qualified path to the modules
	 * @return void
	 */
	public function __construct($module)
	{
		// if there is data
		if ($module) {
			// attempt to load it and return the results
			$this->valid = $this->load($module);
			return;
		}
		// new object
		$this->valid = false;
	}

	/*
	 * attempt to load the module's info
	 *
	 * @param string base name of the module to load
	 *
	 * @return bool true on success, false on fail
	 */
	public function load($module)
	{
		// good info?
		if ($module &&
			$this->path &&
			$this->prefix) {
			// store the unique name
			$this->baseName = trim($module);

			// store the info
			$info = $this->run('info');
			return $this->set($info);
		}
		return false;
	}

	/*
	 * safely access the module's function
	 *
	 * @param string
	 * @param array any data to pass to the function
	 * @return mixed|bool return value of function or false
	 */
	public function run($function_name, $args = '')
	{
		$function_name = trim($function_name);
		if ($function_name &&
			$this->baseName &&
			$this->path &&
			$this->prefix) {
			$fullpath = "{$this->path}/{$this->baseName}.php";
			if (file_exists($fullpath)) {
				require_once $fullpath;

				$this_function = "{$this->prefix}_{$this->baseName}_{$function_name}";
				if (function_exists($this_function)) {
					return $this_function($args);
				}
			}
		}
		return false;
	}
}

?>
