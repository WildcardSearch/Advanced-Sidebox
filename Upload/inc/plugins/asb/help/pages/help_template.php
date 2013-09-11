<?php
/*
 * Plug-in Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2013 WildcardSearch
 * http://www.wildcardsworld.com
 *
 * this file contains a template for the ASB help system
 */

$head = <<<EOF
<!doctype html>
	<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta name="robots" content="noindex, nofollow" />
		<title>{$page_title}</title>
		{$header_include}
	</head>
EOF;

$body = <<<EOF
	<body>
		<table width="100%" valign="top">
			<tbody>
				<tr>
					<td width="20%" valign="top">{$links}</td>
					<td width="80%" valign="top"><img style="position: relative; float: right; margin: 5px;" src="asb_logo_160.png" />{$help_contents}</td>
				</tr>
			</tbody>
		</table>
	</body>
EOF;


$help_page = <<<EOF
{$head}
{$body}
</html>
EOF;

?>
