<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains a custom help document delivery system for ASB
 */

global $page_title, $links_array;

// CSS
$header_include = <<<EOF
		<link rel="stylesheet" type="text/css" href="asb_help.css" media="screen" />
EOF;

// our link bank
$links_array = array(
	"main" => 'Main Page',
	"install" => 'Install & Upgrade',
	"manage_sideboxes" => 'Sideboxes',
	"edit_box" => 'Add and Edit Sideboxes',
	"custom" => 'Custom Boxes',
	"edit_custom" => 'Edit Custom Boxes',
	"export_custom" => 'Export Custom Boxes',
	"import_custom" => 'Import Custom Boxes',
	"manage_scripts" => 'Manage Scripts',
	"edit_scripts" => 'Edit Script Definitions',
	"addons" => 'Add-on Modules'
);

// if we have a topic . . .
if(isset($_GET['topic']))
{
	// store it
	$current = $_GET['topic'];
}
else
{
	// otherwise blank means the main page
	$current = '';
}

// get the links and the document contents
$links = asb_get_links($current);
$help_contents = asb_help_get_contents($current);

// is the template intact?
if(file_exists('pages/help_template.php'))
{
	// if so require it
	require_once 'pages/help_template.php';
}
else
{
	// if not then compromise and just give them the help contents
	$help_page = $help_contents;
}

// final output
echo $help_page;
exit();

/*
 * gets the contents of a help document by topic
 *
 * @param - $current is the key name of the active page topic
 * @return void
 */
function asb_help_get_contents($current = '')
{
	global $page_title, $links_array;

	$filename = 'pages/' . $current . '.php';
	if(file_exists($filename))
	{
		// if so require ir
		require_once $filename;
		return $help_content;
	}

	// otherwise use the main page
	return <<<EOF
	<div class="help_content">
		<h1>Advanced Sidebox Help</h1>
		<h2>a work in progress</h2>
		<p>First thanks for using Advanced Sidebox. I am very excited about the amount of interest that has been shown in this plugin from the onset and feel very appreciative that so many are using it.Things got complicated rather quickly when feature request started piling up but we have done our best to simplify everything while adding those more powerful features as requested.</p>
		<p>This is the main help page. All you'll find here is links to the various topics that are covered in this documentation. To get help for specific actions, go to that page and find the help button. It will link you to the topic you are looking for.</p>
	</div>
EOF;
}

/*
 * build the menu links
 *
 * @param string key name for the active page topic
 * @return string a list of menu links or bool false on error
 */
function asb_get_links($current = '')
{
	global $links_array;

	// start fresh
	$links = '';

	// loop through each and create the link
	foreach($links_array as $topic => $link_title)
	{
		// if this is the current topic
		if($topic == $current)
		{
			// no link
			$links .= "<tr title=\"{$link_title}\" class='active_link'><td>{$link_title}</td></tr>";
		}
		else
		{
			// link
			$links .= "<tr class='inactive_link' onclick=\"document.location = '?topic={$topic}';\"><td><a href=\"?topic={$topic}\" title=\"{$link_title}\">{$link_title}</a></td></tr>";
		}
	}

	// close window link
	$links .= "<tr class='inactive_link' onclick=\"window.close();\"><td><a href=\"javascript:void()\" onclick=\"window.close()\" title=\"close window\">Close Window</a></td>";

	// if there are links (which there certainly will be)
	if($links)
	{
		// return them
		return '<div><table width="100%" class="menu" border="1">' . $links . '</table></div>';
	}
	return false;
}

/*
 * build help page links
 *
 * @param - $links can be a string containing a page topic key name or an array of page topic keys
 * @param - $caption is the label for the produced link(s)
 * @return string link(s) to specified help topic pages
 */
function asb_help_build_page_link($links, $caption = '')
{
	global $links_array;

	// start fresh
	$all_links = '';

	// if we have links . . .
	if(!$links)
	{
		return false;
	}

	if(is_array($links))
	{
		// loop through each topic key
		foreach($links as $topic)
		{
			// adding to the list?
			if($all_links)
			{
				// comma separator
				$separator = '<strong>,</strong>&nbsp;';
			}
			else
			{
				// first link
				$separator = '&nbsp;';
			}

			// produce the HTML
			$all_links .= "{$separator}<a href=\"?topic={$topic}\" title=\"" . $links_array[$topic] . "\">" . $links_array[$topic] . "</a>";
		}
	}
	else
	{
		// no array, just one link
		$all_links = "&nbsp;<a href=\"?topic={$links}\" title=\"" . $links_array[$links] . "\">" . $links_array[$links] . "</a>";
	}

	// links to show?
	if(!$all_links)
	{
		// no links
		return false;
	}

	// is there a caption?
	if($caption)
	{
		// if so use it
		return "<strong>{$caption}</strong>{$all_links}";
	}
	// if not just return the link(s)
	return $all_links;
}

?>
