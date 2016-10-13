<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains a custom help document for ASB
 */

$page_title = 'Advanced Sidebox Help';
$see_also = asb_help_build_page_link(array('manage_sideboxes', 'addons', 'custom'), 'See also:');

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
