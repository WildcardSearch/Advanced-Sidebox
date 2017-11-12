<?php
/**
 * Wildcard Helper Classes - External PHP Module Wrapper
 * interface
 */

/**
 * standard interface for external PHP modules
 *
 * can be used to wrap any external PHP module for secure loading,
 * validation and execution of its functions
 */
interface ExternalModuleInterface010000
{
	public function load($name);
	public function run($function_name, $args = '');
}

?>
