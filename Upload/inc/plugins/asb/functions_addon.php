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

/*
 * asb_build_id_list()
 *
 * build a cleaned list of numeric ids and optionally return it as an SQL IN() function
 *
 * @param - $ids - (mixed) an (int) id or a (string) comma-separated list of ids
 * @param - $field - (string) the field name of the DB table
 * @param - $wrap - (bool) whether to present the id list as an SQL IN() or not
 */
function asb_build_id_list($ids, $field = 'id', $wrap = true)
{
	if(strlen($ids) == 0)
	{
		return false;
	}

	if(strpos($ids, ',') !== false)
	{
		$id_array = explode(',', $ids);
		foreach($id_array as $key => $id)
		{
			if((int) $id == 0)
			{
				unset($id_array[$key]);
			}
		}

		if(count($id_array) > 1)
		{
			$id_list = implode(',', $id_array);
		}
		elseif(count($id_array) == 1)
		{
			$id_list = (int) $id_array[key($id_array)];
		}
	}
	else
	{
		$id_list = (int) $ids;
	}

	if(!$id_list)
	{
		return false;
	}

	if($wrap && $field)
	{
		$id_list = <<<EOF
{$field} IN({$id_list})
EOF;
	}
	return $id_list;
}

/*
 * asb_build_SQL_where()
 *
 * build an SQL WHERE clause from an array of conditions and other arguments
 *
 * @param - $conditions - (mixed) an array of (string) conditions or a single (string) condition
 * @param - $op - (string) the operand to use as a separator
 * @param - $prefix - (string) an operand or other prefix can be used here
 * @param - $wrap - (bool) whether to prefix and enclose the conditions in parentheses or not
 */
function asb_build_SQL_where($conditions, $op = 'AND', $prefix = '', $wrap = true)
{
	if(is_array($conditions))
	{
		$sep = '';
		foreach($conditions as $condition)
		{
			if($condition)
			{
				$where .= $sep . $condition;
				$sep = " {$op} ";
			}
		}
	}
	else
	{
		$where = $conditions;
	}

	if(!$where)
	{
		return false;
	}

	if($wrap)
	{
		$where = "{$prefix}({$where})";
	}
	return $where;
}

?>
