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

$page_title = 'Advanced Sidebox Help: Exporting Custom Side Boxes';
$see_also = adv_sidebox_help_build_page_link(array('custom', 'edit_custom', 'import_custom'), 'See also:');

$help_content = <<<EOF
		<div class="help_content">
		<h1>{$page_title}</h1>
		<h2>creating backups that you can share</h2>
		<p>In the custom box tab you can easily export a custom box type using the option in the custom box's control menu. A dialogue will appear allowing you to choose a location on your local machine to store the XML file. Browse to a good place and then choose save.</p>
		<p class="info">Couldn't be easier.</p>
		{$see_also}
	</div>
EOF;

?>
