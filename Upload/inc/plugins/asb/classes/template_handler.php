<?php
/*
 * Plug-in Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2013 WildcardSearch
 * http://www.wildcardsworld.com
 *
 * this file contains class definitions for the template handling and editing system
 */

class TemplateHandler
{
	// the template to edit (if any)
	protected $template_name = '';

	// any extra JS needed
	protected $extra_scripts = '';

	// true to eval() columns in lieu of editing templates
	protected $eval = false;

	// used when in eval() mode to produce content for custom scripts
	protected $template_vars;

	// used to complete replace the edited script
	protected $replace_all = false;

	// the contents to replace if replace_all is true
	protected $replacement = '';

	// the search keys
	protected $find_top = '';
	protected $find_bottom = '';

	// the columns
	protected $insert_top = '';
	protected $insert_bottom = '';

	// the side boxes
	protected $left_content;
	protected $right_content;

	// dimensions
	protected $width_left;
	protected $width_right;

	/*
	 * __construct()
	 *
	 * called upon creation to store content in an appropriate format IF it exists
	 *
	 * @param - $left_insert - (string) the left column of side box content for this page
	 * @param - $right_insert  - (string) ^ right tho :p
	 * @param - $width_left - (int) is the width (specified in ACP for each script) for left columns
	 * @param - $width_right - (int) is blah blah blah but right instead
	 * @param - $extra_scripts - (string) - any extra JS needed by modules used on this script
	 * @param - $template_vars - (array) - a non-indexed array of template variables
	 * 					to globalize (used when outputting to custom pages)
	 */
	public function __construct($left_insert, $right_insert, $width_left, $width_right, $extra_scripts = '', $template_vars = array())
	{
		global $mybb, $lang, $templates;

		if(!$lang->asb)
		{
			$lang->load('asb');
		}

		// store width upon construct (mostly here for custom implementations like portal)
		$this->width_left = $width_left;
		$this->width_right = $width_right;
		$this->extra_scripts = $extra_scripts;
		$this->template_vars = $template_vars;

		$toggles = $show = array();

		// if admin wants to show the toggle icons . . .
		if($mybb->settings['asb_show_toggle_icons'])
		{
			$toggle_info['left'] = array
			(
				"close" => array
				(
					"img" => 'inc/plugins/asb/images/left_arrow.png',
					"alt" => '&lt;'
				),
				"open" => array
				(
					"img" => 'inc/plugins/asb/images/right_arrow.png',
					"alt" => '&gt;'
				)
			);
			$toggle_info['right']['close'] = $toggle_info['left']['open'];
			$toggle_info['right']['open'] = $toggle_info['left']['close'];

			foreach(array('left', 'right') as $key)
			{
				// check the cookie
				if($mybb->cookies["asb_hide_{$key}"] == 1)
				{
					// hide left
					$show[$key] = $close_style = 'display: none; ';
					$open_style = '';
				}
				else
				{
					// show left
					$close_style = '';
					$open_style = 'display: none; ';
				}

				// produce the link
				$open_image = $toggle_info[$key]['open']['img'];
				$close_image = $toggle_info[$key]['close']['img'];
				$open_alt = $toggle_info[$key]['open']['alt'];
				$close_alt = $toggle_info[$key]['close']['alt'];
				$column_id = "asb_hide_column_{$key}";
				$closed_id = "asb_{$key}_close";
				$open_id = "asb_{$key}_open";

				eval("\$toggles[\$key] = \"" . $templates->get('asb_toggle_icon') . "\";");
			}
		}

		foreach(array('left', 'right') as $key)
		{
			// if there is content
			$var_name = "{$key}_insert";
			if($$var_name)
			{
				$prop_name = "{$key}_content";
				$width_name = "width_{$key}";
				$width = $$width_name;
				$show_column = $show[$key];
				$column_id = "asb_{$key}_column_id";
				$insert_name = "{$key}_insert";
				$sideboxes = $$insert_name;

				eval("\$content_pad = \"" . $templates->get('asb_content_pad') . "\";");
				eval("\$content = \"" . $templates->get('asb_sidebox_column') . "\";");

				$toggle_left = $toggle_right = '';
				$toggle_name = "toggle_{$key}";
				$$toggle_name = $toggles[$key];

				// finally set $this->POSITION_content for ::make_edits()
				$this->$prop_name = <<<EOF

			<!-- start: ASB {$key} column -->{$toggle_left}
			{$content}
			<!-- end: ASB {$key} column -->{$toggle_right}
EOF;
			}
		}
	}

	/*
	 * make_edits()
	 *
	 * handles the rudimentary tasks of editing a template for side boxes
	 */
	public function make_edits()
	{
		global $templates, $mybb, $headerinclude;

		// load the cache and attempt to store this script's info
		$asb = asb_get_cache();
		$this_script = asb_get_this_script($asb);

		// do we have a valid script?
		if(is_array($this_script) && !empty($this_script))
		{
			foreach($this_script as $key => $val)
			{
				if(property_exists($this, $key) && isset($val) && $val)
				{
					$this->$key = $val;
				}
			}
		}
		else
		{
			// if not get out
			return;
		}

		// if there is no info, go with the default values
		$this->find_top = trim($this->find_top);
		if(!$this->find_top)
		{
			$this->find_top = '{$header}';
		}
		$this->find_bottom = trim($this->find_bottom);
		if(!$this->find_bottom)
		{
			$this->find_bottom = '{$footer}';
		}

		$left_content = $this->left_content;
		eval("\$this->insert_top = \"" . $templates->get('asb_begin') . "\";");

		$right_content = $this->right_content;
		eval("\$this->insert_bottom = \"" . $templates->get('asb_end') . "\";");

		if($mybb->settings['asb_show_toggle_icons'])
		{
			// we will need this js
			$headerinclude .= '<script type="text/javascript" src="jscripts/asb.js"></script>';
		}

		if(is_array($this->extra_scripts) && !empty($this->extra_scripts))
		{
			$extra_scripts = "\n" . implode("\n", $this->extra_scripts);
			$headerinclude .= <<<EOF
<script type="text/javascript">
var asb_width_left = {$this->width_left};
var asb_width_right = {$this->width_right};
Event.observe
(
	window,
	'load',
	function()
	{
{$extra_scripts}
	}
);
</script>
EOF;
		}

		// replace everything on the page?
		if($this->replace_all == true)
		{
			// if there is content
			if($this->replacement)
			{
				// replace the existing page entirely
				$templates->cache[$this->template_name] = str_replace(array('{$asb_left}', '{$asb_right}'), array($this->insert_top, $this->insert_bottom), $this->replacement);
			}
		}
		// outputting to variables? (custom script/Page Manager)
		elseif($this->eval)
		{
			// globalize our columns
			global $asb_left, $asb_right;

			// globalize all the add-on template variables
			if(is_array($this->template_vars) && !empty($this->template_vars))
			{
				foreach($this->template_vars as $var)
				{
					global $$var;
				}
			}

			// now eval() their content for the custom script
			eval("\$asb_left = \"" . str_replace("\\'", "'", addslashes($this->insert_top)) . "\";");
			eval("\$asb_right = \"" . str_replace("\\'", "'", addslashes($this->insert_bottom)) . "\";");
		}
		// otherwise we are editing the template in the cache
		else
		{
			// if there are columns stored
			if($this->insert_top || $this->insert_bottom)
			{
				// make the edits
				$find_top_pos = strpos($templates->cache[$this->template_name], $this->find_top);

				if($find_top_pos !== false)
				{
					$find_bottom_pos = strpos($templates->cache[$this->template_name], $this->find_bottom);

					if($find_bottom_pos !== false)
					{
						$breakdown = array
							(
								"full_length" => strlen($templates->cache[$this->template_name]),
								"page_length" => strlen($templates->cache[$this->template_name]) - $find_bottom_pos,
								"find_top" => $this->find_top,
								"find_top_pos" => $find_top_pos,
								"find_bottom," => $this->find_bottom,
								"find_bottom_pos" => $find_bottom_pos,
								"FIRST" => substr($templates->cache[$this->template_name], 0, $find_top_pos + strlen($this->find_top)),
								"SECOND" => $this->insert_top,
								"THIRD" => substr($templates->cache[$this->template_name], $find_top_pos + strlen($this->find_top), $find_bottom_pos - ($find_top_pos + strlen($this->find_top))),
								"FOURTH" => $this->insert_bottom,
								"FIFTH" => substr($templates->cache[$this->template_name], $find_bottom_pos)
							);

						//$debug = true;
						if($debug)
						{
							die(var_dump($breakdown));
						}

						/*
						 * split the template in 3 parts and splice our columns in after 1 and before 3
						 * it is important that we function this way so we can work with the
						  * FIRST instance of the search text (find_top and find_bottom) rather
						  * than replacing multiple found instances
						 */
						$templates->cache[$this->template_name] =
							substr($templates->cache[$this->template_name], 0, $find_top_pos + strlen($this->find_top)) .
							$this->insert_top .
							substr($templates->cache[$this->template_name], $find_top_pos + strlen($this->find_top), $find_bottom_pos - ($find_top_pos + strlen($this->find_top))) .
							$this->insert_bottom .
							substr($templates->cache[$this->template_name], $find_bottom_pos);
					}
				}
			}
		}
	}
}

?>
