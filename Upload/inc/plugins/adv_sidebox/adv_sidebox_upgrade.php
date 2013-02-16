<?php
/* This is an upgrade module derived from the work of pavemen in MyBB Publisher
 *
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright © 2013 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * Check out this project on GitHub: http://wildcardsearch.github.com/Advanced-Sidebox
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses
 */

	global $db;
	
	require_once MYBB_ROOT . 'inc/plugins/adv_sidebox/adv_sidebox_classes.php';

	if(version_compare($old_version, '1.5', '<') || $old_version == '' || $old_version == 0)
    {
		adv_sidebox_do_db_changes();
		adv_sidebox_create_base_settings();
	}

	// a little trash removal for 1.4.3
	if(version_compare($old_version, '1.4.3', '<') || $old_version == '' || $old_version == 0)
    {
		foreach(new DirectoryIterator(ADV_SIDEBOX_MODULES_DIR) as $file)
		{
			if($file->isDir() && !($file->isDot()))
			{
				@my_rmdir_recursive(ADV_SIDEBOX_MODULES_DIR . "/" . $file);
				@rmdir(ADV_SIDEBOX_MODULES_DIR . "/" . $file);
			}
		}
	}
	
	if(version_compare($old_version, '1.4', '<') || $old_version == '' || $old_version == 0)
    {
		// dropped in 1.4
		if($db->field_exists('content', 'sideboxes'))
		{
			$db->write_query("ALTER TABLE ".TABLE_PREFIX."sideboxes DROP COLUMN content");
		}
		if($db->field_exists('stereo', 'sideboxes'))
		{
			$db->write_query("ALTER TABLE ".TABLE_PREFIX."sideboxes DROP COLUMN stereo");
		}
	}

	// Version 1.3.4 is the first to have versioning (1.3.6 was the last to use id-first custom box names) so just check that all the side boxes are up-to-date
	if(version_compare($old_version, '1.3.7', '<') || $old_version == '' || $old_version == 0)
    {
		// get the handler now that we've updated the db and settings
		$adv_sidebox_134 = new Sidebox_handler('', true);

		// boxes?
		if(is_array($adv_sidebox_134->sideboxes))
		{
			foreach($adv_sidebox_134->sideboxes as $this_box)
			{
				$box_type = $this_box->get_box_type();

				// these modules have been renamed
				if($box_type == 'pms')
				{
					$this_box->set_box_type('private_messages');
				}
				if($box_type == 'welcome')
				{
					$this_box->set_box_type('welcome_box');
				}
				if($box_type == 'search')
				{
					$this_box->set_box_type('search_box');
				}

				$box_type = $this_box->get_box_type();

				// if this isn't a custom box (there is a module matching the base_name)
				if($adv_sidebox_134->addons[$box_type]->valid)
				{
					$wrap_content = $adv_sidebox_134->addons[$box_type]->get_wrap_content();
					$display_name = $adv_sidebox_134->addons[$box_type]->get_name();
				}
				else
				{
					$new_box_type = 'asb_custom_' . substr($box_type, 0, strpos($box_type, '_'));

					if($adv_sidebox_134->custom[$new_box_type]->valid)
					{
						$wrap_content = $adv_sidebox_134->custom[$new_box_type]->get_wrap_content();
						$display_name = $adv_sidebox_134->custom[$new_box_type]->get_name();
						$this_box->set_box_type($new_box_type);
					}
					else
					{
						$wrap_content = 0;
						$display_name = '-name lost-';
					}
				}

				// update the properties added since 1.0
				$this_box->set_wrap_content($wrap_content);
				$this_box->set_display_name($display_name);

				// 'on-the-fly' custom boxes have been removed as of 1.3.4
				if($box_type == 'custom_box')
				{
					$id = $this_box->get_id();

					$input_array = array();

					// store the content as a user-defined box, but don't show it because it could break layout
					$input_array['name'] = $db->escape_string('Custom Backup #' . $id);
					$input_array['description'] = $db->escape_string('This is a custom box from a previous installation and may need to be edited before being used to remove table headers');
					$input_array['wrap_content'] = false;

					$query = $db->simple_select('sideboxes', '*', "id='{$id}'");

					if($db->num_rows($query) == 1)
					{
						$this_custom_box = $db->fetch_array($query);
						$input_array['content'] = $db->escape_string($this_custom_box['content']);
					}
					else
					{
						$input_array['content'] = $db->escape_string('There was an error recovering this sidebox.');
					}

					// backup the content
					$status = $db->insert_query('custom_sideboxes', $input_array);

					$this_box->remove();
				}
				else
				{
					// if it isn't a custom box just update it
					$this_box->save();
				}
			}
		}
	}

?>
