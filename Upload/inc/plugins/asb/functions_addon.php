<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * functions used by the default the modules and available to any third party add-ons as well
 */

/*
 * strips all quote tags (and their contents) from a post message
 *
 * @param string the unparsed message
 * @return string the message sans quotes
 */
function asb_strip_quotes($message)
{
	// Assign pattern and replace values.
	$pattern = array(
		"#\[quote=([\"']|&quot;|)(.*?)(?:\\1)(.*?)(?:[\"']|&quot;)?\](.*?)\[/quote\](\r\n?|\n?)#esi",
		"#\[quote\](.*?)\[\/quote\](\r\n?|\n?)#si",
		"#\[\/quote\](\r\n?|\n?)#si"
	);

	do {
		$message = preg_replace($pattern, '', $message, -1, $count);
	} while($count);

	$find = array(
		"#(\r\n*|\n*)<\/cite>(\r\n*|\n*)#",
		"#(\r\n*|\n*)<\/blockquote>#"
	);
	return preg_replace($find, '', $message);
}

/*
 * strip all URLs from the given string
 *
 * @param string the text to cleanse
 * @return string the message less URLs
 */
function asb_strip_url($message)
{
	$message = ' ' . $message;
	$message = preg_replace("#([\>\s\(\)])(http|https|ftp|news){1}://([^\/\"\s\<\[\.]+\.([^\/\"\s\<\[\.]+\.)*[\w]+(:[0-9]+)?(/[^\"\s<\[]*)?)#i", '', $message);
	$message = preg_replace("#([\>\s\(\)])(www|ftp)\.(([^\/\"\s\<\[\.]+\.)*[\w]+(:[0-9]+)?(/[^\"\s<\[]*)?)#i", '', $message);
	return my_substr($message, 1);
}

/*
 * build a cleaned list of numeric ids and optionally return it as an SQL IN() function
 *
 * @param mixed int id or a string comma-separated list of ids
 * @param string the field name of the DB table
 * @param bool whether to present the id list as an SQL IN() or not
 * @return string an SQL IN() function using the ids provided
 */
function asb_build_id_list($ids, $field = 'id', $wrap = true)
{
	if (strlen($ids) == 0) {
		return false;
	}

	if (strpos($ids, ',') !== false) {
		$id_array = explode(',', $ids);
		foreach ($id_array as $key => $id) {
			if((int) $id == 0)
			{
				unset($id_array[$key]);
			}
		}

		if (count($id_array) > 1) {
			$id_list = implode(',', $id_array);
		} elseif (count($id_array) == 1) {
			$id_list = (int) $id_array[key($id_array)];
		}
	} else {
		$id_list = (int) $ids;
	}

	if (!$id_list) {
		return false;
	}

	if ($wrap &&
		$field) {
		$id_list = <<<EOF
{$field} IN({$id_list})
EOF;
	}
	return $id_list;
}

/*
 * build an SQL WHERE clause from an array of conditions and other arguments
 *
 * @param mixed an array of string conditions or a single string condition
 * @param string the operand to use as a separator
 * @param string an operand or other prefix can be used here
 * @param bool whether to prefix and enclose the conditions in parentheses or not
 * @return string an SQL WHERE clause using the provided info
 */
function asb_build_SQL_where($conditions, $op = 'AND', $prefix = '', $wrap = true)
{
	if (is_array($conditions)) {
		$sep = '';
		foreach ($conditions as $condition) {
			if ($condition) {
				$where .= $sep . $condition;
				$sep = " {$op} ";
			}
		}
	} else {
		$where = $conditions;
	}

	if (!$where) {
		return false;
	}

	if ($wrap) {
		$where = "{$prefix}({$where})";
	}
	return $where;
}

/**
 * build a list of all the images stored in a particular folder on the server
 *
 * @param string the unqualified folder name from the root
 * @param string (for recursion) the current subfolder
 * @param bool determines where to recurse into subfolders
 * @return string a comma-separated, single-quoted list of file names
 */
function asb_get_folder_images($folder, $subfolder = '', $recursive = false)
{
	// bad folder, get out
	if (!$folder ||
	   !is_dir(MYBB_ROOT . $folder)) {
		return false;
	}

	// make sure the subfolder has a directory separator
	if ($subfolder &&
	   substr($subfolder, strlen($subfolder) - 1, 1) != '/') {
		$subfolder .= '/';
	}

	// cycle through all the files/folders and produce a list
	$sep = '';
	foreach (new DirectoryIterator(MYBB_ROOT . $folder) as $file) {
		// skip navigation folders
		if ($file->isDot()) {
			continue;
		}

		if ($file->isDir()) {
			// no recursion, just skip this
			if (!$recursive) {
				continue;
			}

			// get the files from this directory
			$sub_files = asb_get_folder_images($folder . '/' . $file->getFilename(), $subfolder . $file->getFilename(), $recursive);
			if ($sub_files) {
				$filenames .= "{$sep}{$sub_files}";
				$sep = ',';
			}
			continue;
		}

		// only certain extensions allowed
		$extension = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
		if (!in_array($extension, array('gif', 'png', 'jpg', 'jpeg'))) {
			continue;
		}

		$filenames .= "{$sep}'{$subfolder}{$file->getFilename()}'";
		$sep = ',';
	}
	return $filenames;
}

?>
