<?php
/*
 * Plug-in Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2013 WildcardSearch
 * http://www.wildcardsworld.com
 *
 * functions used by the default the modules and available to any third party add-ons as well
 */

/*
 * asb_strip_quotes()
 *
 * strips all quote tags (and their contents) from a post message
 *
 * @param - $message is a string containing the unparsed message
 */
function asb_strip_quotes($message)
{
	// Assign pattern and replace values.
	$pattern = array
	(
		"#\[quote=([\"']|&quot;|)(.*?)(?:\\1)(.*?)(?:[\"']|&quot;)?\](.*?)\[/quote\](\r\n?|\n?)#esi",
		"#\[quote\](.*?)\[\/quote\](\r\n?|\n?)#si",
		"#\[\/quote\](\r\n?|\n?)#si"
	);

	do
	{
		$message = preg_replace($pattern, '', $message, -1, $count);
	}
	while($count);

	$find = array
	(
		"#(\r\n*|\n*)<\/cite>(\r\n*|\n*)#",
		"#(\r\n*|\n*)<\/blockquote>#"
	);
	return preg_replace($find, '', $message);
}

/*
 * asb_strip_url()
 *
 * @param - $message
					the text to cleanse
 */
function asb_strip_url($message)
{
	$message = " " . $message;
	$message = preg_replace("#([\>\s\(\)])(http|https|ftp|news){1}://([^\/\"\s\<\[\.]+\.([^\/\"\s\<\[\.]+\.)*[\w]+(:[0-9]+)?(/[^\"\s<\[]*)?)#i", "", $message);
	$message = preg_replace("#([\>\s\(\)])(www|ftp)\.(([^\/\"\s\<\[\.]+\.)*[\w]+(:[0-9]+)?(/[^\"\s<\[]*)?)#i", "", $message);
	return my_substr($message, 1);
}

?>
