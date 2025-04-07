<?php
/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * https://www.rantcentralforums.com
 *
 * functions used by the default the modules and available to any third party add-ons as well
 */

/**
 * strips all quote tags (and their contents) from a post message
 *
 * @param  string message
 * @return string message
 */
function asbStripQuotes($message)
{
	// Assign pattern and replace values.
	$pattern = array(
		"#\[quote=([\"']|&quot;|)(.*?)(?:\\1)(.*?)(?:[\"']|&quot;)?\](.*?)\[/quote\](\r\n?|\n?)#si",
		"#\[quote\](.*?)\[\/quote\](\r\n?|\n?)#si",
		"#\[\/quote\](\r\n?|\n?)#si",
	);

	do {
		$message = preg_replace($pattern, '', $message, -1, $count);
	} while($count);

	return $message;
}

/**
 * strip all URLs from the given string
 *
 * @param string message
 * @return string message
 */
function asbStripUrls($message)
{
	$message = ' '.$message;
	$message = preg_replace("#([\>\s\(\)])(http|https|ftp|news){1}://([^\/\"\s\<\[\.]+\.([^\/\"\s\<\[\.]+\.)*[\w]+(:[0-9]+)?(/[^\"\s<\[]*)?)#i", '', $message);
	$message = preg_replace("#([\>\s\(\)])(www|ftp)\.(([^\/\"\s\<\[\.]+\.)*[\w]+(:[0-9]+)?(/[^\"\s<\[]*)?)#i", '', $message);
	return my_substr($message, 1);
}

/**
 * build a cleaned list of numeric ids and optionally return it as an SQL IN() function
 *
 * @param  int|string id or comma-separated list of ids
 * @param  string field name
 * @param  bool present the id list as SQL IN()?
 * @return string id list
 */
function asbBuildIdList($ids, $field='id', $wrap=true)
{
	if (strlen($ids) == 0) {
		return false;
	}

	if (strpos($ids, ',') !== false) {
		$idArray = explode(',', $ids);

		foreach ($idArray as $key => $id) {
			if((int) $id == 0)
			{
				unset($idArray[$key]);
			}
		}

		if (count($idArray) > 1) {
			$idList = implode(',', $idArray);
		} elseif (count($idArray) == 1) {
			$idList = (int) $idArray[key($idArray)];
		}
	} else {
		$idList = (int) $ids;
	}

	if (!$idList) {
		return false;
	}

	if ($wrap &&
		$field) {
		$idList = <<<EOF
{$field} IN({$idList})
EOF;
	}

	return $idList;
}

/**
 * build an SQL WHERE clause from an array of conditions and other arguments
 *
 * @param  array|string one or more string conditions
 * @param  string separator operand
 * @param  string prefix
 * @param  bool prefix and enclose the conditions in parentheses?
 * @return string SQL WHERE clause
 */
function asbBuildSqlWhere($conditions, $op='AND', $prefix='', $wrap=true)
{
	$where = '';

	if (is_array($conditions)) {
		$sep = '';
		foreach ($conditions as $condition) {
			if ($condition) {
				$where .= $sep.$condition;
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
 * @param  string folder name
 * @param  string (for recursion) the current subfolder
 * @param  bool recurse into subfolders?
 * @return string list of file names
 */
function asbGetImagesFromPath($folder, $subfolder='', $recursive=false)
{
	// bad folder, get out
	if (!$folder ||
	   !is_dir(MYBB_ROOT.$folder)) {
		return false;
	}

	// make sure the subfolder has a directory separator
	if ($subfolder &&
	   substr($subfolder, strlen($subfolder) - 1, 1) != '/') {
		$subfolder .= '/';
	}

	// cycle through all the files/folders and produce a list
	$sep = '';
	$filenames = array();
	foreach (new DirectoryIterator(MYBB_ROOT.$folder) as $file) {
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
			$subFiles = asbGetImagesFromPath($folder.'/'.$file->getFilename(), $subfolder.$file->getFilename(), $recursive);
			if ($subFiles) {
				$filenames = array_merge($filenames, $subFiles);
				$sep = ',';
			}
			continue;
		}

		// only certain extensions allowed
		$extension = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
		if (!in_array($extension, array('gif', 'png', 'jpg', 'jpeg'))) {
			continue;
		}

		$filenames[] = $subfolder.$file->getFilename();
		$sep = ',';
	}

	return $filenames;
}