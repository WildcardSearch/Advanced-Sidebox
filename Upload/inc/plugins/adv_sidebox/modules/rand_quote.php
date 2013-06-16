<?php
/*
 * Random Quotes
 *
 * This module is now part of the core but can still be removed through the interface like any other module.
*/

// Include a check for Advanced Sidebox
if(!defined("IN_MYBB") || !defined("ADV_SIDEBOX"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

/*
 * rand_quote_asb_info()
 *
 * gives the handler all the info it needs to handle the side box module and track its version and upgrades status
 */
function rand_quote_asb_info()
{
	global $lang;

	if(!$lang->adv_sidebox)
	{
		$lang->load('adv_sidebox');
	}

	return array
	(
		"name"							=>	'Random Quotes',
		"description"					=>	'Displays random quotes with a link and avatar',
		"wrap_content"				=>	true,
		"xmlhttp"						=>	true,
		"version"						=>	"1.4.1",
		"settings" => array
			(
				"forum_id"		=> array
				(
					"sid"					=> "NULL",
					"name"				=> "forum_id",
					"title"				=> $lang->adv_sidebox_quote_forums_title,
					"description"		=> $lang->adv_sidebox_quote_forums,
					"optionscode"	=> "text",
					"value"				=> ''
				),
				"min_length"		=> array
				(
					"sid"					=> "NULL",
					"name"				=> "min_length",
					"title"				=> $lang->adv_sidebox_min_quote_length_title,
					"description"		=> $lang->adv_sidebox_min_quote_length,
					"optionscode"	=> "text",
					"value"				=> '20'
				),
				"max_length"		=> array
				(
					"sid"					=> "NULL",
					"name"				=> "max_length",
					"title"				=> $lang->adv_sidebox_max_quote_length_title,
					"description"		=> $lang->adv_sidebox_max_quote_length,
					"optionscode"	=> "text",
					"value"				=> '160'
				),
				"fade_out"		=> array
				(
					"sid"					=> "NULL",
					"name"				=> "fade_out",
					"title"				=> $lang->adv_sidebox_fade_out_title,
					"description"		=> $lang->adv_sidebox_fade_out,
					"optionscode"	=> "yesno",
					"value"				=> '0'
				),
				"default_text"		=> array
				(
					"sid"					=> "NULL",
					"name"				=> "default_text",
					"title"				=> $lang->adv_sidebox_default_text,
					"description"		=> $lang->adv_sidebox_default_text_description,
					"optionscode"	=> "text",
					"value"				=> ''
				),
				"xmlhttp_on" => array
				(
					"sid"					=> "NULL",
					"name"				=> "xmlhttp_on",
					"title"				=> $lang->adv_sidebox_xmlhttp_on_title,
					"description"		=> $lang->adv_sidebox_xmlhttp_on_description,
					"optionscode"	=> "text",
					"value"				=> '0'
				)
			),
		"templates" => array
			(
				array
				(
					"title" => "rand_quote_sidebox",
					"template" => "

					<tr>
						<td class=\"tcat\">
							{\$thread_title_link}
						</td>
					</tr>
					<tr>
						<td class=\"trow1\">
							{\$rand_quote_avatar}&nbsp;{\$rand_quote_author}
						</td>
					</tr>
					<tr>
						<td class=\"trow2\">
							{\$rand_quote_text}
						</td>
					</tr>
					{\$read_more}
					",
					"sid" => -1
				)
			)
	);
}

/*
 * rand_quote_asb_build_template()
 *
 * @param - (array) $settings
					passed from core, side box settings
 * @param - (string) $template_var
					the encoded unique name of the side box requested for
 * @param - (int) $width
					the width of the column the calling side box is positioned in
 */
function rand_quote_asb_build_template($settings, $template_var, $width)
{
	// don't forget to declare your variable! will not work without this
	global $$template_var; // <-- important!

	$this_quote = rand_quote_asb_get_quote($settings, $width);

	if($this_quote)
	{
		$$template_var = $this_quote;
		return true;
	}
	else
	{
		// show the table only if there are posts
		$$template_var = '<tr><td class="trow1">' . $lang->adv_sidebox_no_posts . '</td></tr>';
		return false;
	}
}

/*
 * rand_quote_asb_xmlhttp()
 *
 * @param - (int) $dateline
					UNIX timestamp
 * @param - (array) $settings
					array of sidebox settings
 * @param - (int) $width
					width of column side box lives in
 */
function rand_quote_asb_xmlhttp($dateline, $settings, $width)
{
	// get a quote and return it
	$this_quote = rand_quote_asb_get_quote($settings, $width);
	if($this_quote)
	{
		return $this_quote;
	}
	return 'nochange';
}

/*
 * rand_quote_asb_get_quote()
 *
 * @param - (array) $settings
					passed from adv_sidebox_xmlhttp.php, the requesting side box's settings array
 * @param - (int) $width
					the width of the column
 */
function rand_quote_asb_get_quote($settings, $width)
{
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
			'allow_imgcode' => 1,
			'allow_smilies' => 0,
			'allow_videocode' => 1,
			'filter_badwords' => 1,
			'me_username' => $user['username']
		);
		$new_message = strip_tags($parser->parse_message(adv_sidebox_strip_quotes($rand_post['message']), $parser_options));

		$asb_width = (int) $width;
		$asb_inner_size = $asb_width * .83;
		$avatar_size = (int) ($asb_inner_size / 5);
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

		$new_message = $parser->text_parse_message(adv_sidebox_strip_url($new_message));

		if(strlen($new_message) < $settings['min_length']['value'])
		{
			if($settings['default_text']['value'])
			{
				$new_message = $settings['default_text']['value'];
			}
			else
			{
				// nothing to show
				return false;
			}
		}
		// concantate it if it is too long
		elseif(strlen($new_message) > $settings['max_length']['value'] && $settings['max_length']['value'])
		{
			$remainder_message = substr($new_message, $settings['max_length']['value']);
			$new_message = substr($new_message, 0, $settings['max_length']['value']);

			$the_rest = array();
			$this_font_size = $font_size;

			$bump = .9;

			while(strlen($remainder_message) > 0 && $this_font_size > 6)
			{
				$the_rest[] = '<span style="font-size: ' . (int) $this_font_size . 'px;">' . substr($remainder_message, 0, 10) . '</span>';
				$this_font_size = $this_font_size - $bump;
				$bump = $bump + .02;
				$remainder_message = substr($remainder_message, 10);
			}

			$clipped = true;
		}

		$parser_options_smilies = array(
			"allow_smilies" => 1,
			'allow_imgcode' => 1
		);

		$new_message = $parser->parse_message($new_message, $parser_options_smilies);

		// set up the username link so that it displays correctly for the display group of the user
		$plain_text_username = $username = htmlspecialchars_uni($user['username']);
		$usergroup = $user['usergroup'];
		$displaygroup = $user['displaygroup'];
		$username = format_name($username, $usergroup, $displaygroup);
		$author_link = get_profile_link($user['uid']);
		$post_link = get_post_link($rand_post['pid'], $rand_post['tid']) . '#pid' . $rand_post['pid'];

		$rand_quote_text = '<span style="font-size: ' . $message_font_size . 'px;">' . $new_message . '</span>';

		// If the user has an avatar then display it . . .
		if($user['avatar'] != "")
		{
			$avatar_filename = $user['avatar'];
		}
		else
		{
			// . . . otherwise force the default avatar.
			$avatar_filename = "{$theme['imgdir']}/default_avatar.gif";
		}

		$rand_quote_avatar = '<img style="padding: 4px; width: 15%; vertical-align: middle;" src="' . $avatar_filename . '" alt="' . $plain_text_username . '\s avatar" title="' . $plain_text_username . '\'s avatar"/>';

		$rand_quote_author = "<a  style=\"vertical-align: middle;\" href=\"{$author_link}\" title=\"{$plain_text_username}\"><span style=\"font-size: {$username_font_size}px;\">{$username}</span></a>";

		if($clipped)
		{
			if($settings['fade_out']['value'])
			{
				$rand_quote_text .= implode("", $the_rest);
			}
			else
			{
				$rand_quote_text .= ' . . .';
			}

			$read_more = '
					<tr class="tfoot">
						<td>
							<div style="text-align: center;"><a href="' . $post_link . '" title="Click to see the entire post"><strong>Read More</strong></a></div>
						</td>
					</tr>
			';
		}

		if(my_strlen($rand_post['subject']) > 40)
		{
			$rand_post['subject'] = my_substr($rand_post['subject'], 0, 40) . " . . .";
		}

		if(substr(strtolower($rand_post['subject']), 0, 3) == 're:')
		{
			$rand_post['subject'] = substr($rand_post['subject'], 3);
		}

		$rand_post['subject'] = htmlspecialchars_uni($parser->parse_badwords($rand_post['subject']));

		$thread_title_link = '<strong><a href="' . $post_link . '" title="' . $rand_post['subject'] . '"/><span style="font-size: ' . $title_font_size . 'px;">' . $rand_post['subject'] . '</span></a></strong>';

		// eval the template
		eval("\$this_quote = \"" . $templates->get("rand_quote_sidebox") . "\";");
		return $this_quote;
	}
	else
	{
		return false;
	}
}

?>
