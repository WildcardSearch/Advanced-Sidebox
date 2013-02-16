<?php
/*
 * This file contains a custom help document for ASB
 *
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2013 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * Check out this project on GitHub: http://wildcardsearch.github.com/Advanced-Sidebox
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses
 */

$page_title = 'Advanced Sidebox Help: Importing Custom Side Boxes';
$see_also = adv_sidebox_help_build_page_link(array('custom', 'edit_custom', 'export_custom'), 'See also:');

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
