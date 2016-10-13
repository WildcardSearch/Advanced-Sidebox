<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains a custom help document for ASB
 */

$page_title = 'Advanced Sidebox Help: Editing Side Boxes';
$see_also = asb_help_build_page_link('manage_sideboxes', 'See also:');
$ms_link = asb_help_build_page_link('manage_scripts', 'found out more here:');

$help_content = <<<EOF
		<div class="help_content">
			<h1>{$page_title}</h1>
			<h2>adding and editing side boxes</h2>
			<p>To add a new side box, you must set up certain properties to control how the box functions and where and to whom it will be displayed.</p>
			<p>
				<ul>
					<li><strong>Custom Title</strong> - each box type has a default title, but you don't have to use it. Type your desired title into the input box. If you've already set a custom title and are just editing, leave this field blank to keep your current custom title.<br /><br />If you haven't entered a custom title and this is a new box, even if the above option is on, the default title will be used.
					</li><br />
					<li>
						<strong>Position</strong> - left or right (<em>only available for the non-JS fall-back sctipts</em>)
					</li><br />
					<li>
						<strong>Display Order</strong> - controls the order side boxes display within their given column and across scripts. (<em>only available for the non-JS fall-back sctipts</em>)
						<p class="warning">This is a global value and isn't independent of script. Therefore it applies to any page the side box will be displayed upon and all viewers with permission.</p>
					</li>
					<li>
						<strong>Which Scripts</strong> - select from the installed scripts, you may add or remove scripts, find out more {$ms_link}
					</li><br />
					<li>
						<strong>Which Groups</strong> - select the user groups that can view this side box
						<p class="info">Selecting none will enable the side box for all user groups.</p>
					</li>
					<li><strong>Individual Box Settings</strong> - certain box types produced by add-on modules can accept values to change the way that they function. Each module can have different settings (but some have none) that affect the final output. They should all be described well in the interface, but if you have any questions you can refer to the individual documentation for each module.
					</li>
				</ul>
			</p>
			<p class="info">If you are adding a side box to a live forum the it is a good idea to enable it only for staff (or just admin) so that you can be sure it is exactly like you want it before allowing all of the members to view the side box.</p>
			{$see_also}
		</div>
EOF;

?>
