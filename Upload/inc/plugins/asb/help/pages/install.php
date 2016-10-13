<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains a custom help document for ASB
 */

$page_title = 'Advanced Sidebox Help: Installing and Upgrading';

$help_content = <<<EOF
		<div class="help_content">
			<h1>{$page_title}</h1>
			<h2>getting ASB installed</h2>
			<p>Installation is similar to most other MyBB plugins:</p>
			<p>
				<ul>
					<li>Copy the files in the <code>Upload</code> directory into your forum's root folder</li>
					<li>Install & Activate in ACP</li>
				</ul>
			</p>
			<p class="info">And that's all there is to it!</p>
			<h2>handling periodic upgrades</h2>
			<p>This plugin has made many changes since version 1.0 and many features have been added. Thankfully with some help from <a href="http://www.communityplugins.com/" title="CommunityPlugins.com"><span style="color: #FF7500; font-weight: bold;">pavemen</span></a> we have been able to make upgrading ASB a breeze. Any upgrade that is needed can be performed as follows:</p>
			<p>
				<ul>
					<li>Deactivate in ACP</li>
					<li>Overwrite previous files</li>
					<li>Activate and enjoy</li>
				</ul>
			</p>
			<p class="info">There is never a need to uninstall and lose your work!</p>
			<p class="warning">You should always be sure to back up your work and deactivate this plugin before attempting an upgrade.</p>
		</div>
EOF;

?>
