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
 
$page_title = 'Advanced Sidebox Help: Managing Side Boxes';
$see_also = adv_sidebox_help_build_page_link('edit_box', 'See also:');

$help_content = <<<EOF
		<div class="help_content">
			<h1>{$page_title}</h1>
			<h2>adding, editing and removing side boxes</h2>
			<p>Managing side boxes is much easier with this plugin, but there are a lot of options involved so it can get complicated. We are working to simplify the interface while maintaining (and even increasing) the current level of performance.</p>
			<p>From this screen you can view each side box on your forum at once or filter the results by a particular script. When a side box is set to display for all scripts it will appear on every page regardless of filter.</p>
			<p class="info">You can edit a side box by clicking on its title or using the option control menu.</p>
			<p>Side boxes can be deleted using the link in the options control menu.</p>
			<p class="warning">Deleting side boxes is a permanent action and cannot be undone.</p>
			{$see_also}
		</div>
EOF;

?>
