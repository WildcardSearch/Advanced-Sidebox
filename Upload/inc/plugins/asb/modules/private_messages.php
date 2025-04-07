<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * https://www.rantcentralforums.com
 *
 * ASB default module
 */

// Include a check for Advanced Sidebox
if (!defined('IN_MYBB') ||
	!defined('IN_ASB')) {
	die('Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.');
}

/**
 * provide info to ASB about the addon
 *
 * @return array module info
 */
function asb_private_messages_info()
{
	global $lang;

	if (!$lang->asb_addon) {
		$lang->load('asb_addon');
	}

	return array(
		'title' => $lang->asb_private_messages,
		'description' => $lang->asb_private_messages_desc,
		'wrap_content' => true,
		'xmlhttp' => true,
		'version' => '2.0.0',
		'compatibility' => '4.0',
		'noContentTemplate' => 'asb_private_messages_no_content',
		'installData' => array(
			'templates' => array(
				array(
					'title' => 'asb_pms',
					'template' => <<<EOF
				<div class="trow1 asb-private-messages-container">
					<div class="asb-private-messages-overview">
						<span>{\$lang->asb_pms_received_new}</span>
					</div>
					<div class="asb-private-messages-links tfoot smalltext">
						<a href="private.php">View All Messages</a> &mdash; <a href="private.php?action=send">Compose Message</a>
					</div>
				</div>
EOF
				),
				array(
					'title' => 'asb_private_messages_no_content',
					'template' => <<<EOF
<div class="asb-no-content-message">{\$lang->asb_private_messages_no_content}</div>

EOF
				),
			),
		),
	);
}

/**
 * handles display of children of this addon at page load
 *
 * @param  array info from child box
 * @return bool success/fail
 */
function asb_private_messages_get_content($settings, $script, $dateline)
{
	global $db, $mybb, $templates, $lang;

	// Load global and custom language phrases
	if (!$lang->asb_addon) {
		$lang->load('asb_addon');
	}

	if ($mybb->user['uid'] == 0) {
		// guest
		$lang->asb_private_messages_no_content = $lang->sprintf($lang->asb_pms_no_messages, "<a href=\"member.php?action=login\">{$lang->asb_pms_login}</a>", "<a href=\"member.php?action=register\">{$lang->asb_pms_register}</a>");
		return false;
	}

	// has the user disabled pms?
	if (!$mybb->user['receivepms']) {
		// user has disabled PMs
		return false;
	}

	// does admin allow pms?
	if (!$mybb->usergroup['canusepms'] ||
		!$mybb->settings['enablepms']) {
		// if not tell them
		$lang->asb_private_messages_no_content = $lang->sprintf($lang->asb_pms_disabled_by_admin, "<a href=\"usercp.php?action=options\">{$lang->welcome_usercp}</a>");
		return false;
	}

	// show the user their PM info
	$username = build_profile_link(format_name($mybb->user['username'], $mybb->user['usergroup'], $mybb->user['displaygroup']), $mybb->user['uid']);
	$lang->asb_pms_received_new = $lang->sprintf($lang->asb_pms_received_new, $mybb->user['pms_unread'], $mybb->user['pms_total']);

	eval("\$pmessages = \"{$templates->get('asb_pms')}\";");

	return $pmessages;
}