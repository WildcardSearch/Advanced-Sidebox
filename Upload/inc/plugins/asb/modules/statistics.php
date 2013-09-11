<?php
/*
 * Plug-in Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2013 WildcardSearch
 * http://www.wildcardsworld.com
 *
 * ASB default module
 */

// Include a check for Advanced Sidebox
if(!defined("IN_MYBB") || !defined("IN_ASB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

function asb_statistics_info()
{
	global $lang;

	if(!$lang->asb_addon)
	{
		$lang->load('asb_addon');
	}

 	return array
	(
		"title" => $lang->asb_stats,
		"description" => $lang->asb_stats_desc,
		"wrap_content" => true,
		"version" => "1.2",
		"settings" => array
		(
			"format_username" =>	array
			(
				"sid" => "NULL",
				"name" => "format_username",
				"title" => $lang->asb_stats_format_usernames_title,
				"description" => $lang->asb_stats_format_usernames_desc,
				"optionscode" => "yesno",
				"value" => '0'
			)
		),
		"templates" => array
		(
			array
			(
				"title" => "asb_statistics",
				"template" => <<<EOF
				<tr>
					<td class="trow1">
						<span class="smalltext">
						<strong>&raquo; </strong>{\$lang->asb_stats_num_members}: {\$statistics[\'numusers\']}<br />
						<strong>&raquo; </strong>{\$lang->asb_stats_latest_member}: {\$newestmember}<br />
						<strong>&raquo; </strong>{\$lang->asb_stats_num_threads}: {\$statistics[\'numthreads\']}<br />
						<strong>&raquo; </strong>{\$lang->asb_stats_num_posts}: {\$statistics[\'numposts\']}
						<br /><br /><a href="{\$mybb->settings[\'bburl\']}/stats.php">{\$lang->asb_stats_full_stats}</a>
						</span>
					</td>
				</tr>
EOF
				,
				"sid" => -1
			)
		)
	);
}

/*
 * This function is required. It is used by asb.php to display the custom content in your sidebox.
 */
function asb_statistics_build_template($args)
{
	foreach(array('settings', 'template_var') as $key)
	{
		$$key = $args[$key];
	}
	// don't forget to declare your variable! will not work without this
	global $$template_var; // <-- important!
	global $mybb, $cache, $templates, $lang;

	// Load global and custom language phrases
	if(!$lang->asb_addon)
	{
		$lang->load('asb_addon');
	}

	// get forum statistics
	$statistics = $cache->read("stats");
	$statistics['numthreads'] = my_number_format($statistics['numthreads']);
	$statistics['numposts'] = my_number_format($statistics['numposts']);
	$statistics['numusers'] = my_number_format($statistics['numusers']);

	if(!$statistics['lastusername'])
	{
		$newestmember = "<strong>" . $lang->asb_stats_no_one . "</strong>";
	}
	else
	{
		if($settings['format_username']['value'])
		{
			$last_user = get_user($statistics['lastuid']);
			$last_username = format_name($last_user['username'], $last_user['usergroup'], $last_user['displaygroup']);
		}
		else
		{
			$last_username = $statistics['lastusername'];
		}
	}
	$newestmember = build_profile_link($last_username, $statistics['lastuid']);

	eval("\$" . $template_var . " = \"" . $templates->get("asb_statistics") . "\";");
	return true;
}

?>
