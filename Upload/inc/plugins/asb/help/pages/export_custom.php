<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains a custom help document for ASB
 */

$page_title = 'Advanced Sidebox Help: Exporting Custom Side Boxes';
$see_also = asb_help_build_page_link(array('custom', 'edit_custom', 'import_custom'), 'See also:');

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
