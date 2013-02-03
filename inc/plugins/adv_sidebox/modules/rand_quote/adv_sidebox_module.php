<?php
/*
 * Random Quotes
 *
 * This module is experimental and hasn't been officially released yet.
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
		"description"		=>	'Displays random quotes with a link and avatar',
		"wrap_content"	=>	true,
		"version"			=>	"1.1",
		"discarded_templates"	=>	array
													(
														"rand_quote_sidebox_left",
														"rand_quote_sidebox_right"
													),
		"templates"		=>	array
										(
											array
											(
												"title" => "rand_quote_sidebox",
												"template" => "
	<tr>
		<td class=\"trow1\" colspan=\"1\">
			<table>
				<tr>
					<td style=\"max-height: 130px;\">
						<div style=\"position: relative; max_height: 100px; overflow: hidden;\">
							{\$rand_quote_avatar}{\$rand_quote_text}
						</div>
					</td>
				</tr>
				<tr>
					<td>{\$read_more}{\$rand_quote_author}
					</td>
				</tr>
			</table>
		</td>
	</tr>
												",
												"sid" => -1
											)
										)
	);
}

function rand_quote_asb_build_template($settings, $template_var, $width)
{
	// don't forget to declare your variable! will not work without this
	global $$template_var; // <-- important!
	global $db, $mybb, $templates, $lang;

	if(!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}

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

		$asb_width = (int) $width;
		$asb_inner_size = $asb_width * .83;
		$avatar_size = (int) ($asb_inner_size / 5);

		// set up the username link so that it displays correctly for the display group of the user
		$username = htmlspecialchars_uni($user['username']);
		$usergroup = $user['usergroup'];
		$displaygroup = $user['displaygroup'];
		$username = format_name($username, $usergroup, $displaygroup);
		$author_link = get_profile_link($user['uid']);
		$post_link = get_post_link($rand_post['pid'], $rand_post['tid']) . '#pid' . $rand_post['pid'];

		// image sizes and text variables
		$read_more_height = $asb_inner_size / 10;

		$read_more_width = (int) ($read_more_height * 4.5);

		$rand_quote_text = $style . '<span class="quote_box">' . $new_message . '</span>';

		$rand_quote_avatar = '<img style="position: relative; float: right; padding: 4px; width: ' . $avatar_size . 'px; height: ' . $avatar_size . 'px;" src="' . ($user['avatar'] ? $user['avatar'] : 'images/default_avatar.gif') . '" alt="" title=""/>';

		$rand_quote_author = "<a href=\"{$author_link}\">{$username}</a>";

		$read_more = '<a href="' . $post_link . '"><img style="width: ' . $read_more_width . 'px; height: ' . $read_more_height . 'px; position: relative; float: right;" src="http://www.rantcentralforums.com/images/readmore.gif" title="Click to see the entire post" alt="read more" /></a>';

		// eval the template and the sidebox will display
		eval("\$" . $template_var . " = \"" . $templates->get("rand_quote_sidebox") . "\";");
	}
	else
	{
		// eval the template and the sidebox will display
		eval("\$" . $template_var . " = \"<tr><td class=\\\"trow1\\\">" . $lang->adv_sidebox_no_posts . "</td></tr>\";");
	}
}

?>
