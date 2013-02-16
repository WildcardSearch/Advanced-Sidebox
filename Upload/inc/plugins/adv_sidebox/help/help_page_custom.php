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

$page_title = 'Advanced Sidebox Help: Managing Custom Boxes';
$see_also = adv_sidebox_help_build_page_link(array('edit_custom', 'import_custom', 'export_custom'), 'See also:');

$help_content = <<<EOF
		<div class="help_content">
			<h1>{$page_title}</h1>
			<h2>adding static custom boxes and using them on your forum</h2>
			<p>ASB provides many default modules that deliver content from PHP scripts, but sometimes you just need to put a little HTML in a side box.</p>
			<p>To do that just create a new custom box type with your HTML. Then add a new side box and choose your new custom box from the list.</p>
			<p>You may create an unlimited amount of custom box types and then use them as for as many side boxes as you wish. Custom boxes may be imported/exported as backups or to share and custom boxes can be deleted as well.</p>
			{$see_also}
		</div>
EOF;

?>
