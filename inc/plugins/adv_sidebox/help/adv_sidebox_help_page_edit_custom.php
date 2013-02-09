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

$page_title = 'Advanced Sidebox Help: Editing Custom Side Box Contents';
$see_also = adv_sidebox_help_build_page_link(array('custom', 'export_custom', 'import_custom'), 'See also:');

$help_content = <<<EOF
		<div class="help_content">
			<h1>{$page_title}</h1>
			<h2>adding and editing new static custom side boxes</h2>
			<p>Custom side box types can contain any valid HTML so they are great for quick messages, information, stats, scores, ads . . . just about anything that can be done with static HTML can go here.</p>
			<p class="info">It is important to remember when entering HTML for a custom side box type that it must be valid HTML. If the 'Use Template' box is unchecked, you may enter any valid HTML, but if not, you are entering the contents of the <code>&lt;tbody></code> element.<br>
			This means your first entry must be to open a table row, then a table cell, then enter your contents, then close both table tags:</p>
			<div class="code_box">
				<code>
					&lt;tr><br />
						&emsp;&lt;td><br />
							&emsp;&emsp;your content here<br />
						&emsp;&lt;/td><br />
					&lt;/tr><br />
				</code>
			</div>
			<p class="warning">Leaving open <code>&lt;tr></code> or <code>&lt;td></code> tags in your custom box contents can result in a breakdown of the entire page's table structure-- in other words the columns may appear stacked on top of forum contents, merged into other content or disfigured in other ways.</p>
			<p>If you see something bad happening after you add a new custom box, disable it (by usergroup or completely) and check the HTML.</p>
			<p>Once you create a custom box type and save it, your new box will appear on the add/edit side boxes page under the name you chose. It will work just like any other type on the list now.</p>
			{$see_also}
		</div>
EOF;

?>
