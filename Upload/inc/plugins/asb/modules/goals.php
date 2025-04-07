<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * https://www.rantcentralforums.com
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
		),
		'installData' => array(
			'templates' => array(
				array(
					'title' => 'asb_goals',
					'template' => <<<EOF
				<div class="trow1 asb-goals-progress-container">{\$progress}</div>
				<div class="tfoot asb-goals-progress-footer">
					<span class="smalltext">{\$stats}</span>
				</div>
EOF
				),
				array(
					'title' => 'asb_goals_goal_reached',
					'template' => <<<EOF
<div class="asb-goals-goal-reached-message">{\$goal_reached_message}</div>{\$successImage}
EOF
				),
				array(
					'title' => 'asb_goals_progress',
					'template' => <<<EOF
<span class="asb-goals-progress-message">{\$percentage}%</span> {\$progress_message}<br />
<div class="asb-goals-progress-indicator">
	<div class="asb-goals-progress-indicator-completed" style="width: {\$percentage}%;" title="{\$progress_bar_title}">
	</div>
</div>
EOF
				),
				array(
					'title' => 'asb_goals_goal_reached_image',
					'template' => <<<EOF

<img class="asb-goals-success-image" src="{\$settings[\'success_image\']}" alt="celebrate!"/>
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
function asb_goals_get_content($settings, $script, $dateline)
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