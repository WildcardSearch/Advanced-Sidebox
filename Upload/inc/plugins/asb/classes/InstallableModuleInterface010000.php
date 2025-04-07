<?php
/**
 * Wildcard Helper Classes - External PHP Module Wrapper
 * interface
 */

interface InstallableModuleInterface010000
{
	public function install($cleanup = true);
	public function uninstall($cleanup = true);
}