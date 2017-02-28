<?php
/*
 * Wildcard Helper Classes
 * ACP - HTML Generator
 *
 * produces standard or encoded URLs, HTML anchors and images
 */

class HTMLGenerator
{
	/**
	 * @var string default URL for links,
	 * can be set in __construct() by the plugin ACP
	 * page but can be changed in-line if needed
	 */
	public $base_url = 'index.php';

	/**
	 * @var array allowed $_GET/$mybb->input
	 * variable names, add custom keys in
	 * __construct() or in-line
	 */
	public $allowed_url_keys = array(
		'module',
		'action',
		'mode',
		'id',
		'uid',
		'tid',
		'page',
		'my_post_key',
	);

	/**
	 * @var array
	 */
	public $allowed_img_properties = array(
		'id',
		'name',
		'title',
		'alt',
		'style',
		'class',
		'onclick',
	);

	/**
	 * @var array
	 */
	public $allowed_link_properties = array(
		'id',
		'name',
		'title',
		'style',
		'class',
		'onclick',
		'target',
	);

	/**
	 * constructor
	 *
	 * @param  string the base URL for all links and URLs
	 * @param  string|array 1+ key names
	 * @return void
	 */
	public function __construct($url = '', $extra_keys = '')
	{
		// custom base URL?
		if (trim($url)) {
			$this->base_url = trim($url);
		}

		// custom keys?
		if ($extra_keys) {
			if (!is_array($extra_keys)) {
				$extra_keys = array($extra_keys);
			}
			foreach ($extra_keys as $key) {
				$key = trim($key);
				if ($key &&
					!in_array($key, $this->allowed_url_keys)) {
					$this->allowed_url_keys[] = $key;
				}
			}
		}
	}

	/**
	 * builds a URL from standard options array
	 *
	 * @param  array keyed to standard URL options
	 * @param  string overrides the default URL base if present
	 * @param  bool override URL encoded ampersand (for JS mostly)
	 * @return string URL
	 */
	public function url($options = array(), $base_url = '', $encoded = true)
	{
		if ($base_url &&
			trim($base_url)) {
			$url = $base_url;
		} else {
			$url = $this->base_url;
		}

		$amp = '&';
		if ($encoded) {
			$amp = '&amp;';
		}
		$sep = $amp;
		if (strpos($url, '?') === false) {
			$sep = '?';
		}

		// check for the allowed options
		foreach ((array) $this->allowed_url_keys as $item) {
			if (isset($options[$item]) &&
				$options[$item]) {
				// and add them if set
				$url .= "{$sep}{$item}={$options[$item]}";
				$sep = $amp;
			}
		}
		return $url;
	}

	/**
	 * builds an HTML anchor from the provided options
	 *
	 * @param  string the address
	 * @param  string the title of the link
	 * @param  array options to effect the HTML output
	 * @return string HTML anchor
	 */
	public function link($url = '', $caption = '', $options = '', $icon_options = array())
	{
		$properties = $this->build_property_list($options, $this->allowed_link_properties);

		if (isset($options['icon'])) {
			$icon_img = $this->img($options['icon'], $icon_options);
			$icon_link = <<<EOF
<a href="{$url}">{$icon_img}</a>&nbsp;
EOF;
		}

		if (!$url) {
			$url = $this->url();
		}
		if (!isset($caption) ||
			!$caption) {
			$caption = $url;
		}

		return <<<EOF
{$icon_link}<a href="{$url}"{$properties}>{$caption}</a>
EOF;
	}

	/**
	 * generate HTML <img> mark-up
	 *
	 * @param  string image source attribute
	 * @param  array a keyed array of options to be generated
	 * @return string HTML image
	 */
	public function img($url, $options = array())
	{
		$properties = $this->build_property_list($options, $this->allowed_img_properties);

		return <<<EOF
<img src="{$url}"{$properties}/>
EOF;
	}

	/**
	 * build HTML property list
	 *
	 * @param  array keyed array of properties
	 * @param  array unindexed array of allowable property names
	 * @return string a list of properties
	 */
	protected function build_property_list($options = array(), $allowed = array())
	{
		if (!is_array($options) ||
			!is_array($allowed)) {
			return false;
		}

		foreach ($allowed as $key) {
			if (isset($options[$key]) &&
				$options[$key]) {
				$property_list .= <<<EOF
 {$key}="{$options[$key]}"
EOF;
			}
		}
		return $property_list;
	}
}

?>
