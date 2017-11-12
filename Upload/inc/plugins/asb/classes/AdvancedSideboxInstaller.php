<?php
/**
 * Wildcard Helper Classes - Plugin Installer
 * plugin specific extension
 */

class AdvancedSideboxInstaller extends WildcardPluginInstaller010202
{
	static public function getInstance()
	{
		static $instance;

		if (!isset($instance)) {
			$instance = new AdvancedSideboxInstaller();
		}
		return $instance;
	}

	/**
	 * link the installer to our data file
	 *
	 * @param  string path to the install data
	 * @return void
	 */
	public function __construct($path = '')
	{
		parent::__construct(MYBB_ROOT . 'inc/plugins/asb/install_data.php');
	}
}

?>
