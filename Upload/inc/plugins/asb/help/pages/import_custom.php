<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains a custom help document for ASB
 */

$page_title = 'Advanced Sidebox Help: Importing Custom Side Boxes';
$see_also = asb_help_build_page_link(array('custom', 'edit_custom', 'export_custom'), 'See also:');

$help_content = <<<EOF
		<div class="help_content">
			<h1>{$page_title}</h1>
			<h2>restoring saved static boxes</h2>
			<p>Since ASB 1.3.4 it has been possible to export static HTML boxes for backup or to share. Simply click the import tab and select the file from a local drive and ASB will attempt to import it as a new custom box type.</p>
			<p class="info">ASB exports custom side boxes as encoded XML files.</p>
			<p class="warning">These files are prepared with a checksum (a hashed value to prevent loading corrupted files) and altering the contents of the exported XML file will result in the file being rendered unloadable by the plugin.</p>
			{$see_also}
		</div>
EOF;

?>
