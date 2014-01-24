<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2013 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains a custom help document for ASB
 */

$page_title = 'Advanced Sidebox Help: Managing Scripts';
$see_also = asb_help_build_page_link('edit_scripts', 'See also:');

$help_content = <<<EOF
		<div class="help_content">
			<h1>{$page_title}</h1>
			<h2>adding, editing and removing script definitions</h2>
			<p>ASB 2.0 introduces a new way to customize side boxes for your forum. Now instead of being limited to certain forum pages as chosen by a developer (me :p ), admin can choose which scripts he will add side boxes to and how-- even allowing the use of custom pages!</p>
			<p class="info">You can edit a script definition by clicking on its title or using the option control menu.</p>
			<p>Script definitions can be deleted using the link in the options control menu and may also be imported/exported.</p>
			<p class="warning">Deleting script info is a permanent action and cannot be undone; back up your work!</p>
			{$see_also}
		</div>
EOF;

?>
