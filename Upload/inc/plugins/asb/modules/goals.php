<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * ASB default module
 */

// disallow direct access
if (!defined('IN_MYBB') ||
	!defined('IN_ASB')) {
	die('Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.');
}

/**
 * provide info to ASB about the addon
 *
 * @return array
 */
function asb_goals_info()
{
	global $lang;

	if (!$lang->asb_addon) {
		$lang->load('asb_addon');
	}

	return array	(
		'title' => $lang->asb_goals_title,
		'description' => $lang->asb_goals_description,
		'wrap_content' => true,
		'version' => '2.0.0',
		'compatibility' => '4.0',
		'xmlhttp' => true,
		'settings' => array(
			'goal_type' => array(
				'name' => 'goal_type',
				'title' => $lang->asb_goals_goal_type_title,
				'description' => $lang->asb_goals_goal_type_description,
				'optionscode' => <<<EOF
select
1={$lang->asb_goals_goal_type_optionscode_posts}
2={$lang->asb_goals_goal_type_optionscode_threads}
3={$lang->asb_goals_goal_type_optionscode_users}
EOF
				,
				'value' => '1',
			),
			'goal' => array(
				'name' => 'goal',
				'title' => $lang->asb_goals_goal_title,
				'description' => $lang->asb_goals_goal_description,
				'optionscode' => 'text',
				'value' => '100000',
			),
			'success_image' => array(
				'name' => 'success_image',
				'title' => $lang->asb_goals_success_image_title,
				'description' => $lang->asb_goals_success_image_description,
				'optionscode' => 'text',
				'value' => '',
			),
			'xmlhttp_on' => array(
				'name' => 'xmlhttp_on',
				'title' => $lang->asb_xmlhttp_on_title,
				'description' => $lang->asb_xmlhttp_on_description,
				'optionscode' => 'text',
				'value' => '0',
			),
		),
		'installData' => array(
			'templates' => array(
				array(
					'title' => 'asb_goals',
					'template' => <<<EOF
				<tr>
					<td class="trow1" style="text-align: center;">{\$progress}</td>
				</tr>
				<tr>
					<td class="tfoot" style="text-align: center;"><span class="smalltext">{\$stats}</span></td>
				</tr>
EOF
				),
				array(
					'title' => 'asb_goals_goal_reached',
					'template' => <<<EOF
<span style="font-size: 1.6em; color: navy;">{\$goal_reached_message}</span>{\$successImage}
EOF
				),
				array(
					'title' => 'asb_goals_progress',
					'template' => <<<EOF
<span style="font-size: 1.4em; color: green;">{\$percentage}%</span> {\$progress_message}<br />
<div style="width: 95%; background: white; height: 20px; border: 2px outset grey;">
	<div style="width: {\$percentage}%; background: blue; height: 20px;" title="{\$progress_bar_title}">
	</div>
</div>
EOF
				),
				array(
					'title' => 'asb_goals_goal_reached_image',
					'template' => <<<EOF

<img src="{\$settings[\'success_image\']}" alt="celebrate!"/>
EOF
				),
			),
		),
	);
}

/**
 * handles display of children of this addon at page load
 *
 * @param  array
 * @return bool success/fail
 */
function asb_goals_build_template($settings, $template_var, $width, $script)
{
	global $$template_var, $lang;

	if (!$lang->asb_addon) {
		$lang->load('asb_addon');
	}

	$goalStatus = asb_goals_get_progress($settings, $template_var, $width, $script);
	if (!$goalStatus) {
		$$template_var = "<tr><td>{$lang->asb_goals_no_content}</td></tr>";
		return false;
	}

	$$template_var = $goalStatus;
	return true;
}

/**
 * AJAX
 *
 * @param  array
 * @return string
 */
function asb_goals_xmlhttp($dateline, $settings, $width, $script)
{
	$goalStatus = asb_goals_get_progress($settings);
	if ($goalStatus) {
		return $goalStatus;
	}
	return 'nochange';
}

/**
 * build the content based on settings
 *
 * @param  array
 * @return string
 */
function asb_goals_get_progress($settings)
{
	global $lang, $templates, $db;

	if (!$lang->asb_addon) {
		$lang->load('asb_addon');
	}

	$returnValue = '';
	$table = 'forums';
	$field = 'posts';
	$function = 'SUM';
	$goal_type_plural = $lang->asb_goals_posts;
	switch((int) $settings['goal_type']) {
	case 2:
		$field = 'threads';
		$goal_type_plural = $lang->asb_goals_threads;
		break;
	case 3:
		$table = 'users';
		$goal_type_plural = $lang->asb_goals_users;
		$field = 'uid';
		$function = 'COUNT';
		break;
	}

	$query = $db->simple_select($table, "{$function}({$field}) AS total");
	$total = $db->fetch_field($query, 'total');
	$formatted_total = my_number_format($total);
	$goal = (int) $settings['goal'];
	$formatted_goal = my_number_format($goal);

	if ($total >= $goal) {
		$percentage = 100;
		$stats = $lang->asb_goals_footer_goal_reached;
		$goal_reached_message = $lang->sprintf($lang->asb_goals_goal_reached, $formatted_goal, $goal_type_plural);

		$successImage = '';
		if ($settings['success_image']) {
			eval("\$successImage = \"{$templates->get('asb_goals_goal_reached_image')}\";");
		}

		eval("\$progress = \"{$templates->get('asb_goals_goal_reached')}\";");
	} else {
		$itemsLeft = my_number_format($goal - $total);
		$percentage = round(($total / $goal) * 100, 1);
		$stats = $lang->sprintf($lang->asb_goals_footer_progress, $itemsLeft, $goal_type_plural);
		$progress_message = $lang->sprintf($lang->asb_goals_progress_message, $formatted_goal, $goal_type_plural);
		$progress_bar_title = $lang->sprintf($lang->asb_goals_progress_bar_title, $formatted_total, $formatted_goal);
		eval("\$progress = \"{$templates->get('asb_goals_progress')}\";");
	}

	eval("\$returnValue = \"{$templates->get('asb_goals')}\";");
	return $returnValue;
}

?>
