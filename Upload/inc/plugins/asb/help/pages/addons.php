<?php
/*
 * Plug-in Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2013 WildcardSearch
 * http://www.wildcardsworld.com
 *
 * this file contains a custom help document for ASB
 */

$page_title = 'Advanced Sidebox Help: Managing Addons';

$help_content = <<<EOF
		<div class="help_content">
			<h1>{$page_title}</h1>
			<h2>understanding add-on modules</h2>
			<p>Because of progress made toward simplifying the ACP add-on module interface, the admin's role has become more separated and therefore much easier.</p>
			<p>The core now handles add-on module install and upgrade (if necessary) seamlessly. If you no longer wish to use a particular module, simple delete it. Don't worry, if you want it back all you have to do is replace the files and ASB will automatically reinstall it for you.</p>
			<p class="warning">When removing modules, always use the available functionality within ASB to delete add-on modules rather than manually deleting the files from the server. Additional checks are made by the core to ensure no trash is left behind and that no side boxes become 'stranded' (box_type no longer exists)</p>
		</div>
EOF;

?>
