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
		"name"					=>	'Random Quotes',
		"description"			=>	'Displays random quotes with a link and avatar',
		"wrap_content"		=>	true,
		"version"					=>	"1",
		"discarded_templates"	=>	array
													(
														"rand_quote_sidebox_left",
														"rand_quote_sidebox_right"
													),
		"settings"		=>	array
										(
											"forum_id"		=> array
											(
												"sid"					=> "NULL",
												"name"				=> "forum_id",
												"title"				=> "Forum List",
												"description"		=> "single fid or comma-separated fid list of forums to pull random posts from",
												"optionscode"	=> "text",
												"value"				=> ''
											)
										),
		"templates"		=>	array
										(
											array
											(
												"title" => "rand_quote_sidebox",
												"template" => "
					<tr class=\"tcat\">
						<td>
							{\$thread_title_link}
						</td>
					</tr>
					<tr class=\"trow1\">
						<td>
							{\$rand_quote_text}
						</td>
					</tr>
					<tr class=\"trow2\">
						<td>
							{\$read_more}
							{\$rand_quote_avatar}&nbsp;{\$rand_quote_author}
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
	global $db, $mybb, $templates, $lang, $theme;

	if(!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}

	if($settings['forum_id']['value'] != '' && (int) $settings['forum_id']['value'] != 0)
	{
		$view_only = "fid IN ({$settings['forum_id']['value']})";
	}

	// get forums user cannot view
	$unviewable = get_unviewable_forums(true);
	if($unviewable)
	{
		$unviewwhere = "fid NOT IN ($unviewable)";
	}

	if($unviewwhere && $view_only)
	{
		$operator = ' AND ';
	}

	$where = $unviewwhere . $operator . $view_only;

	// get a random post
	$post_query = $db->simple_select('posts', 'pid, uid, message, fid, tid, subject', $where, array("order_by" => 'RAND()'));

	// if there are posts . . .
	if($db->num_rows($post_query))
	{
		$rand_post = $db->fetch_array($post_query);

		// get some data to work with
		$uid = $rand_post['uid'];
		$user = get_user($uid);

		// Build a post parser
		require_once MYBB_ROOT."inc/class_parser.php";
		$parser = new postParser;

		$parser_options = array(
			'allow_html' => 1,
			'allow_mycode' => 1,
			'allow_smilies' => 0,
			'allow_imgcode' => 1,
			'filter_badwords' => 1,
			'me_username' => $user['username']
		);
		$new_message = strip_tags($parser->parse_message(adv_sidebox_strip_quotes($rand_post['message']), $parser_options));

		if(strlen($parser->text_parse_message($new_message)) < 20)
		{
			$new_message = 'I love ' . $mybb->settings['bbname'] . '!!!';
		}
		// concantate it if it is too long
		elseif(strlen($new_message) > 80)
		{
			$new_message = substr($new_message, 0, 80) . ' . . .';
		}

		$parser_options_smilies = array(
			"allow_smilies" => 1,
			'allow_imgcode' => 1
		);

		$new_message = $parser->parse_message($new_message, $parser_options_smilies);

		$asb_width = (int) $width;
		$asb_inner_size = $asb_width * .83;
		$avatar_size = (int) ($asb_inner_size / 7.5);
		$font_size = $asb_width / 4.5;

		if($font_size > 16)
		{
			$font_size = 16;
		}
		if($font_size < 10)
		{
			$font_size = 10;
		}
		$username_font_size = (int) $font_size * .9;
		$title_font_size = (int) $font_size * .65;
		$message_font_size = (int) $font_size;

		// set up the username link so that it displays correctly for the display group of the user
		$plain_text_username = $username = htmlspecialchars_uni($user['username']);
		$usergroup = $user['usergroup'];
		$displaygroup = $user['displaygroup'];
		$username = format_name($username, $usergroup, $displaygroup);
		$author_link = get_profile_link($user['uid']);
		$post_link = get_post_link($rand_post['pid'], $rand_post['tid']) . '#pid' . $rand_post['pid'];

		// image sizes and text variables
		$read_more_width = (int) ($asb_inner_size / 2.5);

		if($read_more_width < 40)
		{
			$read_more_width = 40;
		}

		if($read_more_width > 85)
		{
			$read_more_width = 85;
		}

		$rand_quote_text = '<span style="font-size: ' . $message_font_size . 'px;">' . $new_message . '</span>';

		$rand_quote_avatar = '<img style="padding: 4px; width: ' . $avatar_size . 'px; position: relative; float: left;" src="' . ($user['avatar'] ? $user['avatar'] : 'images/default_avatar.gif') . '" alt="' . $plain_text_username . '\s avatar" title="' . $plain_text_username . '\'s avatar"/>';

		$rand_quote_author = "<a  style=\"padding-top: 10px\" href=\"{$author_link}\" title=\"{$plain_text_username}\"><span style=\"font-size: {$username_font_size}px;\">{$username}</span></a>";

		$read_more = '<a href="' . $post_link . '"><img style="width: ' . $read_more_width . 'px; position: relative; float: right; padding: 8px;" src="http://www.rantcentralforums.com/images/readmore.gif" title="Click to see the entire post" alt="read more . . ." /></a>';

		if(my_strlen($rand_post['subject']) > 40)
		{
			$rand_post['subject'] = my_substr($rand_post['subject'], 0, 60) . " . . .";
		}

		$rand_post['subject'] = htmlspecialchars_uni($parser->parse_badwords($rand_post['subject']));

		$thread_title_link = '<strong><a href="' . $post_link . '" title="' . $rand_post['subject'] . '"/><span style="font-size: ' . $title_font_size . 'px;">' . $rand_post['subject'] . '</span></a></strong>';

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
