<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
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
		'version' => '2.0.1',
		'compatibility' => '4.0',
		'settings' => array(
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
					'title' => 'asb_pms',
					'template' => <<<EOF
				<div class="trow1 asb-private-messages-container">
					<span class="smalltext">{\$lang->asb_pms_received_new}<br /><br />
					<strong>&raquo; </strong> <strong>{\$mybb->user[\'pms_unread\']}</strong> {\$lang->asb_pms_unread}<br />
					<strong>&raquo; </strong> <strong>{\$mybb->user[\'pms_total\']}</strong> {\$lang->asb_pms_total}</span>
				</div>
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
function asb_private_messages_build_template($settings, $template_var, $script)
{
	global $$template_var, $lang; // <-- important!

	if (!$lang->asb_addon) {
		$lang->load('asb_addon');
	}

	$pmessages = asb_private_messages_get_messages();

	if ($pmessages) {
		$$template_var = $pmessages;
		return true;
	} else {
		$pm_message = $lang->sprintf($lang->asb_pms_user_disabled_pms, "<a href=\"{$mybb->settings['bburl']}/usercp.php?action=options\">{$lang->welcome_usercp}</a>");
		$$template_var = <<<EOF
	<tr>
		<td class='trow1'>{$pm_message}</td>
	</tr>
EOF;
		return false;
	}
}

/**
 * handles display of children of this addon via AJAX
 *
 * @param  array info from child box
 * @return void
 */
function asb_private_messages_xmlhttp($dateline, $settings, $script)
{
	global $db, $mybb;

	$query = $db->simple_select('privatemessages', '*', "dateline > {$dateline} AND toid='{$mybb->user['uid']}'");

	if ($db->num_rows($query) > 0) {
		$pmessages = asb_private_messages_get_messages();

		if ($pmessages) {
			return $pmessages;
		}
	}
	return 'nochange';
}

/**
 * get the user's private messages
 *
 * @return string|bool html or success/fail
 */
function asb_private_messages_get_messages()
{
	global $db, $mybb, $templates, $lang;

	// Load global and custom language phrases
	if (!$lang->asb_addon) {
		$lang->load('asb_addon');
	}

	if ($mybb->user['uid'] == 0) {
		// guest
		$pmessages = $lang->sprintf("<tr><td class='trow1'>{$lang->asb_pms_no_messages}</td></tr>", "<a href=\"{$mybb->settings['bburl']}/member.php?action=login\">{$lang->asb_pms_login}</a>", "<a href=\"{$mybb->settings['bburl']}/member.php?action=register\">{$lang->asb_pms_register}</a>");
		$ret_val = false;
	} else {
		// has the user disabled pms?
		if ($mybb->user['receivepms']) {
			// does admin allow pms?
			if (!$mybb->usergroup['canusepms'] ||
				!$mybb->settings['enablepms']) {
				// if not tell them
				$pmessages = $lang->sprintf("<tr><td class='trow1'>{$lang->asb_pms_disabled_by_admin}</td></tr>", "<a href=\"{$mybb->settings['bburl']}/usercp.php?action=options\">{$lang->welcome_usercp}</a>");
				$ret_val = false;
			} else {
				// if so show the user their PM info
				$username = build_profile_link(format_name($mybb->user['username'], $mybb->user['usergroup'], $mybb->user['displaygroup']), $mybb->user['uid']);
				$lang->asb_pms_received_new = $lang->sprintf($lang->asb_pms_received_new, $username, $mybb->user['pms_unread']);

				eval("\$pmessages = \"{$templates->get('asb_pms')}\";");
			}
		} else {
			// user has disabled PMs
			$pmessages = '';
		}
	}
	return $pmessages;
}

?>
