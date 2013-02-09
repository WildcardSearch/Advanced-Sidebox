<?php
/*
 * This file contains a template for the ASB help system
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

$head = <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta name="robots" content="noindex, nofollow" />
		<title>{$page_title}</title>
		{$header_include}
	</head>
EOF;

$body = <<<EOF
	<body onload="resizeWindow();">
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
