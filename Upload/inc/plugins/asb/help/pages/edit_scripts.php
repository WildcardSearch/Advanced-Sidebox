<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2013 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains a custom help document for ASB
 */

$page_title = 'Advanced Sidebox Help: Editing Scripts';
$see_also = asb_help_build_page_link('manage_scripts', 'See also:');
 
$help_content = <<<EOF
		<div class="help_content">
			<h1>{$page_title}</h1>
			<h2>customizing script definitions</h2>
			<p>Basically you must point the plugin to the correct script, the template to edit, the hook to call and what to search & replace in the template. If you want, you can replace the entire template with your own content.</p>
			<p class="info">To use a custom page (for example made with Page Manager), you'll need to select the 'Output To Variables' option, then globalize and use \$asb_left and \$asb_right in your script as columns.</p>
			<h2>Page Manager Example Code</h2>
			<div class="code_box">
<code><span style="color: #000000">
<span style="color: #0000BB">&lt;?php
<br />
<br /></span><span style="color: #007700">global&nbsp;</span><span style="color: #0000BB">\$headerinclude</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">\$header</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">\$theme</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">\$footer</span><span style="color: #007700">,&nbsp;</span><span style="background: yellow; color: #0000BB">\$asb_left</span><span style="color: #007700">,&nbsp;</span><span style="background: yellow; color: #0000BB">\$asb_right</span><span style="color: #007700">;
<br /></span><span style="color: #0000BB">\$template&nbsp;</span><span style="color: #007700">=&nbsp;</span><span style="color: #DD0000">'
<br />&lt;html&gt;
<br />&nbsp;&nbsp;&nbsp;&nbsp;&lt;head&gt;
<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;title&gt;'&nbsp;</span><span style="color: #007700">.&nbsp;</span><span style="color: #0000BB">\$pages</span><span style="color: #007700">[</span><span style="color: #DD0000">'name'</span><span style="color: #007700">]&nbsp;.&nbsp;</span><span style="color: #DD0000">'&lt;/title&gt;
<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{\$headerinclude}
<br />&nbsp;&nbsp;&nbsp;&nbsp;&lt;/head&gt;
<br />&nbsp;&nbsp;&nbsp;&nbsp;&lt;body&gt;
<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{\$header}
<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="background: yellow; color: #007700">'&nbsp;.&nbsp;</span><span style="background: yellow; color: #0000BB">\$asb_left&nbsp;</span><span style="background: yellow; color: #007700">.&nbsp;'</span><span style="color: #DD0000">
<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;table&nbsp;border="0"&nbsp;cellspacing="'&nbsp;</span><span style="color: #007700">.&nbsp;</span><span style="color: #0000BB">\$theme</span><span style="color: #007700">[</span><span style="color: #DD0000">'borderwidth'</span><span style="color: #007700">]&nbsp;.&nbsp;</span><span style="color: #DD0000">'"&nbsp;cellpadding="'&nbsp;</span><span style="color: #007700">.&nbsp;</span><span style="color: #0000BB">\$theme</span><span style="color: #007700">[</span><span style="color: #DD0000">'tablespace'</span><span style="color: #007700">]&nbsp;.&nbsp;</span><span style="color: #DD0000">'"&nbsp;class="tborder"&gt;
<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;thead&gt;
<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;tr&gt;
<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;td&nbsp;class="thead"&gt;
<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;strong&gt;'&nbsp;</span><span style="color: #007700">.&nbsp;</span><span style="color: #0000BB">\$pages</span><span style="color: #007700">[</span><span style="color: #DD0000">'name'</span><span style="color: #007700">]&nbsp;.&nbsp;</span><span style="color: #DD0000">'&lt;/strong&gt;
<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/td&gt;
<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/tr&gt;
<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/thead&gt;
<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;tbody&gt;
<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;tr&gt;
<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;td&nbsp;class="trow1"&gt;Your content here . . .&lt;/td&gt;
<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/tr&gt;
<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/tbody&gt;
<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/table&gt;&lt;br&nbsp;/&gt;
<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="background: yellow; color: #007700">'&nbsp;.&nbsp;</span><span style="background: yellow; color: #0000BB">\$asb_right&nbsp;</span><span style="background: yellow; color: #007700">.&nbsp;'</span><span style="color: #DD0000">
<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{\$footer}
<br />&nbsp;&nbsp;&nbsp;&nbsp;&lt;/body&gt;
<br />&lt;/html&gt;'</span><span style="color: #007700">;
<br />
<br /></span><span style="color: #0000BB">\$template</span><span style="color: #007700"> = </span><span style="color: #0000BB">str_replace</span><span style="color: #007700">(</span><span style="color: #DD0000">"\'"</span><span style="color: #007700">,&nbsp;</span><span style="color: #DD0000">"'"</span><span style="color: #007700">,&nbsp;</span><span style="color: #0000BB">addslashes</span><span style="color: #007700">(</span><span style="color: #0000BB">\$template</span><span style="color: #007700">));
<br /></span><span style="color: #0000BB">add_breadcrumb</span><span style="color: #007700">(</span><span style="color: #0000BB">\$pages</span><span style="color: #007700">[</span><span style="color: #DD0000">'name'</span><span style="color: #007700">]);
<br />eval(</span><span style="color: #DD0000">"\\\$page=\""</span><span style="color: #007700"> . </span><span style="color: #0000BB">\$template</span><span style="color: #007700"> . </span><span style="color: #DD0000">"\";"</span><span style="color: #007700">);
<br /></span><span style="color: #0000BB">output_page</span><span style="color: #007700">(</span><span style="color: #0000BB">\$page</span><span style="color: #007700">);
<br />
<br /></span><span style="color: #0000BB">?&gt;</span>
</span>
</code>
			</div>
			{$see_also}
		</div>
EOF;

?>
