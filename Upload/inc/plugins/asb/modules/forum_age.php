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
function asb_forum_age_info()
{
	global $lang;

	if (!$lang->asb_addon) {
		$lang->load('asb_addon');
	}

	return array	(
		'title' => $lang->asb_forum_age_title,
		'description' => $lang->asb_forum_age_description,
		'wrap_content' => true,
		'version' => '2.0.0',
		'compatibility' => '4.0',
		'xmlhttp' => true,
		'settings' => array(
			'forum_age_date_format' => array(
				'name' => 'forum_age_date_format',
				'title' => $lang->asb_forum_age_forum_age_date_format_title,
				'description' => $lang->asb_forum_age_forum_age_date_format_description,
				'optionscode' => <<<EOF
select
1={$lang->asb_forum_age_optionscode_year}
2={$lang->asb_forum_age_optionscode_month}
3={$lang->asb_forum_age_optionscode_week}
4={$lang->asb_forum_age_optionscode_day}
5={$lang->asb_forum_age_optionscode_hour}
6={$lang->asb_forum_age_optionscode_minute}
7={$lang->asb_forum_age_optionscode_second}
EOF
				,
				'value' => 'year',
			),
			'show_creation_date' => array(
				'name' => 'show_creation_date',
				'title' => $lang->asb_forum_age_show_creation_date_title,
				'description' => $lang->asb_forum_age_show_creation_date_description,
				'optionscode' => 'yesno',
				'value' => '1',
			),
			'creation_date_format' => array(
				'name' => 'creation_date_format',
				'title' => $lang->asb_forum_age_creation_date_format_title,
				'description' => $lang->asb_forum_age_creation_date_format_description,
				'optionscode' => 'text',
				'value' => 'F jS, Y',
			),
		),
		'installData' => array(
			'templates' => array(
				array(
					'title' => 'asb_forum_age',
					'template' => <<<EOF
				<div class="trow1 asb-forum-age-header">{\$forumAge}</div>{\$creationDate}
EOF
				),
				array(
					'title' => 'asb_forum_age_creation_date',
					'template' => <<<EOF

				<div class="tfoot asb-forum-age-footer">{\$creationText}</div>
EOF
				),
				array(
					'title' => 'asb_forum_age_text',
					'template' => <<<EOF
<span class="asb-forum-age-text">{\$forumAgeText}</span>
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
function asb_forum_age_get_content($settings, $script, $dateline)
{
	global $mybb, $db, $lang, $templates;

	if (!$lang->asb_addon) {
		$lang->load('asb_addon');
	}

	// get the forum creation date
	$query = $db->simple_select('users', 'regdate', "uid='1'");
	if ($db->num_rows($query) == 0) {
		return false;
	}

	$creationDateStamp = $db->fetch_field($query, 'regdate');

	// if we are showing the creation date, include the footer
	if ($settings['show_creation_date']) {
		$format = $mybb->settings['dateformat'];
		if ($settings['creation_date_format']) {
			$format = $settings['creation_date_format'];
		}

		$creationDate = my_date($format, $creationDateStamp);
		$creationText = $lang->sprintf($lang->asb_forum_age_founded_message, $creationDate);
		eval("\$creationDate = \"{$templates->get('asb_forum_age_creation_date')}\";");
	}

	// information for all increments
	$allInfo = array(
		1 => array('name' => 'year', 'inSeconds' => 365 * 24 * 60 * 60),
		2 => array('name' => 'month', 'inSeconds' => 30 * 24 * 60 * 60),
		3 => array('name' => 'week', 'inSeconds' => 7 * 24 * 60 * 60),
		4 => array('name' => 'day', 'inSeconds' => 24 * 60 * 60),
		5 => array('name' => 'hour', 'inSeconds' => 60 * 60),
		6 => array('name' => 'minute', 'inSeconds' => 60),
		7 => array('name' => 'second', 'inSeconds' => 1),
	);

	/**
	 * loop through each increment and determine whether that
	 * increment should be shown, hidden, or replaced
	 */
	$start = $settings['forum_age_date_format'];
	$age = TIME_NOW - $creationDateStamp;
	foreach ($allInfo as $i => $info) {
		$varName = $info['name'].'s';
		$$varName = 0;

		if ($age > $info['inSeconds']) {
			$$varName = (int) ($age / $info['inSeconds']);
			$age = $age - ($$varName * $info['inSeconds']);
		}

		$key = "asb_forum_age_{$info['name']}";
		$data = $$varName;
		if ($$varName > 1) {
			$key .= 's';
		} elseif ($$varName == 0) {
			$data = $lang->asb_forum_age_less_than;
		}

		$forumAgeArray[] = $lang->sprintf($lang->$key, $data);
	}

	// remove increments before the selected value that are empty
	for ($x = 2; $x < 8; $x++) {
		$varName = $allInfo[$x - 1]['name'].'s';
		if ($settings['forum_age_date_format'] >= $x &&
		$$varName == 0) {
			array_shift($forumAgeArray);
			$start--;
		}
	}

	// remove increments after the selected value
	for ($x = $start; $x < 7; $x++) {
		unset($forumAgeArray[$x]);
	}

	// add "and" if there is more than one entry
	if (count($forumAgeArray) > 1) {
		$forumAgeArray[count($forumAgeArray) - 1] = $lang->asb_forum_age_and.$forumAgeArray[count($forumAgeArray) - 1];
	}

	// compile the time text
	$forumAgeText = implode(', ', $forumAgeArray);
	eval("\$forumAgeText = \"{$templates->get('asb_forum_age_text')}\";");
	$forumAge = $lang->sprintf($lang->asb_forum_age_text, $mybb->settings['bbname'], $forumAgeText);

	eval("\$returnValue = \"{$templates->get('asb_forum_age')}\";");
	return $returnValue;
}

/**
 * insert peeker for creation date
 *
 * @return void
 */
function asb_forum_age_settings_load()
{
	echo <<<EOF

	<script type="text/javascript">
	new Peeker($(".setting_show_creation_date"), $("#row_setting_creation_date_format"), /1/, true);
	</script>
EOF;
}