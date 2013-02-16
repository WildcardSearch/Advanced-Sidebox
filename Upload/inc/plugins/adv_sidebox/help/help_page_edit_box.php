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

$page_title = 'Advanced Sidebox Help: Editing Side Boxes';
$see_also = adv_sidebox_help_build_page_link('manage_sideboxes', 'See also:');

$help_content = <<<EOF
		<div class="help_content">
			<h1>{$page_title}</h1>
			<h2>adding and editing side boxes</h2>
			<p>To add a new side box, you must set up certain properties to control how the box functions and where and to whom it will be displayed.</p>
			<p>
				<ul>
					<li><strong>Type</strong> - select from a list of all available side box types.<br /><br />Side boxes can be produced through add-on modules or custom static boxes that you create/import. Select a type for your box here.</li><br />
					<li><strong>Use Custom Title</strong> - each box type has a default title, but you don't have to use it. Turn this switch on to see the next field.<br /><br />
						<ul>
							<li><strong>Custom Title</strong> - <em>above must be on for this input field to appear</em> - type your desired title into the input box. If you've already set a custom title and are just editing, leave this field blank to keep your current custom title.<br /><br />If you haven't entered a custom title and this is a new box, even if the above option is on, the default title will be used.</li><br />
						</ul>
					</li>
					<li><strong>Position</strong> - left or right</li><br />
					<li><strong>Display Order</strong> - controls the order side boxes display within their given column and across scripts.
						<p class="warning">This is a global value and isn't independent of script. Therefore it applies to any page the side box will be displayed upon and all viewers with permission.</p></li>
					<li><strong>Which Scripts</strong> - select from the eight possible scripts: index.php, forumdisplay.php, showthread.php, member.php (profiles), memberlist.php, showteam.php, stats.php and portal.php</li><br />
					<li><strong>Which Groups</strong> - select the user groups that can view this side box
					<p class="info">Selecting none will enable the side box for all user groups.</p></li>
					<li><strong>Individual Box Settings</strong> - certain box types produced by add-on modules can accept values to change the way that they function. Each module can have different settings (but some have none) that affect the final output. They should all be described well in the interface, but if you have any questions you can refer to the individual documentation for each module.</li>
				</ul>
			</p>
			<p class="info">If you are adding a side box to a live forum the it is a good idea to enable it only for staff (or just admin) so that you can be sure it is exactly like you want it before allowing all of the members to view the side box.</p>
			{$see_also}
		</div>
EOF;

?>
