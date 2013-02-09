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

$page_title = 'Advanced Sidebox Help';
$see_also = adv_sidebox_help_build_page_link(array('sideboxes', 'addons', 'custom'), 'See also:');

$help_content = <<<EOF
		<div class="help_content">
		<h1>{$page_title}</h1>
		<h2>a work in progress</h2>
		<p>First thanks for using Advanced Sidebox. I am very excited about the amount of interest that has been shown in this plugin from the onset and feel very appreciative that so many are using it.Things got complicated rather quickly when feature request started piling up but we have done our best to simplify everything while adding those more powerful features as requested.</p>
		<p>This is the main help page. All you'll find here is links to the various topics that are covered in this documentation. To get help for specific actions, go to that page and find the help button. It will link you to the topic you are looking for.</p>
		{$see_also}
	</div>
EOF;

?>
