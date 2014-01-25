<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2013 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains a custom help document for ASB
 */

$page_title = 'Advanced Sidebox Help: Managing Side Boxes';
$see_also = asb_help_build_page_link('edit_box', 'See also:');

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
