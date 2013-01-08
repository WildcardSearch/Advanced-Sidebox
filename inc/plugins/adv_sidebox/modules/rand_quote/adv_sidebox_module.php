<?php
/*
 * Advanced Sidebox Add-On Example #1 - Meta File
 *
 * This is an example of the extended method adding a sidebox. Use this method when you need to add a setting, template, stylesheet, table, etc . . . if you don't need to modify anything to accomplish your sideboxes purpose then just omit this file and auto-activate will occur in the sidebox module.
 *
 * Two required functions handle the sidebox in ACP and in when being displayed.
*/
 
// Include a check for Advanced Sidebox
if(!defined("IN_MYBB") || !defined("ADV_SIDEBOX"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

/*
 * This function is required. It is used by acp_functions to add and describe your new sidebox.
 */
function rand_quote_asb_info()
{
	return array
	(
		"name"				=>	'Random Quotes',
		"description"		=>	'displays random quotes with a link and avatar',
		"stereo"			=>	true
	);
}

function rand_quote_asb_is_installed()
{
	global $db;
	
	// works just like a plugin
	$query = $db->simple_select('templates', 'title', "title='rand_quote_sidebox_left'");
	return $db->num_rows($query);
}

function rand_quote_asb_install()
{
	global $db;
	
	// a simple template
	$template_1 = array(
        "title" => "rand_quote_sidebox_left",
        "template" => "<table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\">
	<tr>
		<td class=\"thead\" colspan=\"3\"><strong>Random Quotes</strong></td>
	</tr>
	<tr>
		<td class=\"trow1\" colspan\"1\">
			<div style=\"position: relative; max_height: 100px; overflow: hidden;\">
				{\$rand_quote_avatar_l}{\$rand_quote_text}<br /><br />
				{\$read_more_l}{\$rand_quote_author}
			</div></td>
	</tr>
</table>
<br />",
        "sid" => -1
    );
	$db->insert_query("templates", $template_1);
	
	// a simple template
	$template_2 = array(
        "title" => "rand_quote_sidebox_right",
        "template" => "<table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\">
	<tr>
		<td class=\"thead\" colspan=\"3\"><strong>Random Quotes</strong></td>
	</tr>
	<tr>
		<td class=\"trow1\" colspan\"1\">
			<table>
				<tr>
					<td style=\"max-height: 130px;\">
						<div style=\"position: relative; max_height: 100px; overflow: hidden;\">
							{\$rand_quote_avatar_r}{\$rand_quote_text}
						</div>
					</td>
				</tr>
				<tr>
					<td>{\$read_more_r}{\$rand_quote_author}
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<br />",
        "sid" => -1
    );
	$db->insert_query("templates", $template_2);
}

/*
 * clean up after yourself.
 */
function rand_quote_asb_uninstall()
{
	global $db;
	
	// delete all the boxes of this custom type and the template as well
	$db->query("DELETE FROM " . TABLE_PREFIX . "sideboxes WHERE box_type='" . $db->escape_string('rand_quote') . "'");
	$db->query("DELETE FROM " . TABLE_PREFIX . "templates WHERE title='rand_quote_sidebox_left'");
	$db->query("DELETE FROM " . TABLE_PREFIX . "templates WHERE title='rand_quote_sidebox_right'");
}

/*
 * This function is required. It is used by adv_sidebox.php to display the custom content in your sidebox.
 */
function rand_quote_asb_build_template()
{
	// don't forget to declare your variable! will not work without this
	global $rand_quote_l, $rand_quote_r; // <-- important!
	
	global $db, $mybb, $templates, $theme;
	
	// get forums user cannot view
	$unviewable = get_unviewable_forums(true);
	if($unviewable)
	{
		$unviewwhere = "fid NOT IN ($unviewable)";
	}
	
	// get a random post
	$post_query = $db->simple_select('posts', 'pid, uid, message, fid, tid', $unviewwhere, array("order_by" => 'RAND()'));
	
	// if there are posts . . .
	if($db->num_rows($post_query))
	{
		// keep parsing until we have at least a one-liner . . .
		while(strlen($new_message) < 10)
		{
			$rand_post = $db->fetch_array($post_query);
			
			$rand_post['message'] = adv_sidebox_strip_quotes($rand_post['message']);
			
			// Build a post parser
			require_once MYBB_ROOT."inc/class_parser.php";
			$parser = new postParser;
			
			$new_message = $parser->text_parse_message($rand_post['message']);
			
			$parser_options = array(
				"allow_smilies" => 1
			);
			
			$new_message = $parser->parse_message($new_message, $parser_options);
			
			/* // concantate it if it is too long
			if(strlen($new_message) > 160)
			{
				$new_message = substr($new_message, 0, 80) . ' . . .<br />';
			}
			else
			{
				$new_message .= '<br />';
			} */
		}
		
		// get some data to work with
		$uid = $rand_post['uid'];
		$user = get_user($uid);
		
		$asb_width_l = (int) $mybb->settings['adv_sidebox_width_left'];
		$asb_inner_size_l = $asb_width_l * .83;
		$avatar_size_l = (int) ($asb_inner_size_l / 5);
		
		$asb_width_r = (int) $mybb->settings['adv_sidebox_width_right'];
		$asb_inner_size_r = $asb_width_r * .83;
		$avatar_size_r = (int) ($asb_inner_size_r / 5);
		
		// set up the username link so that it displays correctly for the display group of the user
		$username = htmlspecialchars_uni($user['username']);
		$usergroup = $user['usergroup'];
		$displaygroup = $user['displaygroup'];
		$username = format_name($username, $usergroup, $displaygroup);
		$author_link = get_profile_link($user['uid']);
		$post_link = get_post_link($rand_post['pid'], $rand_post['tid']) . '#pid' . $rand_post['pid'];
		
		// image sizes and text variables
		$read_more_height_l = $asb_inner_size_l / 12;
		$read_more_height_r = $asb_inner_size_r / 12;
		
		$read_more_width_l = (int) ($read_more_height_l * 4.5);
		$read_more_width_r = (int) ($read_more_height_r * 4.5);
		
		$rand_quote_text = $style . '<span class="quote_box">' . $new_message . '</span>';
		
		$rand_quote_avatar_l = '<img style="position: relative; float: right; padding: 4px; width: ' . $avatar_size_l . 'px; height: ' . $avatar_size_l . 'px;" src="' . ($user['avatar'] ? $user['avatar'] : 'images/default_avatar.gif') . '" alt="" title=""/>';
		$rand_quote_avatar_r = '<img style="position: relative; float: right; padding: 4px; width: ' . $avatar_size_r . 'px; height: ' . $avatar_size_r . 'px;" src="' . ($user['avatar'] ? $user['avatar'] : 'images/default_avatar.gif') . '" alt="" title=""/>';
		
		$rand_quote_author = "<a href=\"{$author_link}\">{$username}</a>";
		
		$read_more_l = '<a href="' . $post_link . '"><img style="width: ' . $read_more_width_l . 'px; height: ' . $read_more_height_l . 'px; position: relative; float: right;" src="http://www.rantcentralforums.com/images/readmore.gif" title="Click to see the entire post" alt="read more" /></a>';
		$read_more_r = '<a href="' . $post_link . '"><img style="width: ' . $read_more_width_r . 'px; height: ' . $read_more_height_r . 'px; position: relative; float: right;" src="http://www.rantcentralforums.com/images/readmore.gif" title="Click to see the entire post" alt="read more" /></a>';

		// eval the template and the sidebox will display
		eval("\$rand_quote_l = \"" . $templates->get("rand_quote_sidebox_left") . "\";");
		
		// eval the template and the sidebox will display
		eval("\$rand_quote_r = \"" . $templates->get("rand_quote_sidebox_right") . "\";");
	}
}

?>