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

	global $db, $mybb, $lang;

	require_once MYBB_ROOT . 'inc/plugins/adv_sidebox/adv_sidebox_classes.php';
	require_once MYBB_ROOT . 'inc/plugins/adv_sidebox/adv_sidebox_functions.php';

	// step up to 1.4 series
	if(version_compare($old_version, '1.4', '<') || $old_version == '' || $old_version == 0)
    {
		// Check the main table, if it exists then check each field that was added after 1.4 and create it if it isn't already there
		if($db->table_exists('sideboxes'))
		{
			if(!$db->field_exists('settings', 'sideboxes'))
			{
				$db->write_query("ALTER TABLE ".TABLE_PREFIX."sideboxes ADD settings TEXT");
			}

			if(!$db->field_exists('groups', 'sideboxes'))
			{
				$db->write_query("ALTER TABLE ".TABLE_PREFIX."sideboxes ADD groups TEXT");
			}

			if($db->field_exists('content', 'sideboxes'))
			{
				$db->write_query("ALTER TABLE ".TABLE_PREFIX."sideboxes DROP COLUMN content");
			}
		}
		else
		{
			// If the table is missing, create it.
			$collation = $db->build_create_table_collation();
			$db->write_query
			(
				"CREATE TABLE " . TABLE_PREFIX . "sideboxes
				(
					id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					display_order INT(10) NOT NULL,
					box_type VARCHAR(25) NOT NULL,
					display_name VARCHAR(32) NOT NULL,
					position INT(2),
					show_on_index INT(2),
					show_on_forumdisplay INT(2),
					show_on_showthread INT(2),
					show_on_portal INT(2),
					groups TEXT,
					stereo INT(2),
					wrap_content INT(2),
					settings TEXT
				) ENGINE=MyISAM{$collation};"
			);
		}

		$adv_sidebox14 = new Sidebox_handler;
	}

	// Version 1.3.4 is the first to have versioning. If the old version was earlier than that just check everything.
	if(version_compare($old_version, '1.3.4', '<') || $old_version == '' || $old_version == 0)
    {
		if(!$lang->adv_sidebox)
		{
			$lang->load('adv_sidebox');
		}

		// Check the main table, if it exists then check each field that was added after 1.0 and create it if it isn't already there
		if($db->table_exists('sideboxes'))
		{
			if(!$db->field_exists('display_name', 'sideboxes'))
			{
				$db->write_query("ALTER TABLE ".TABLE_PREFIX."sideboxes ADD display_name VARCHAR(32) NOT NULL");
			}

			if(!$db->field_exists('show_on_index', 'sideboxes'))
			{
				$db->write_query("ALTER TABLE ".TABLE_PREFIX."sideboxes ADD show_on_index INT(2) DEFAULT 1");
			}

			if(!$db->field_exists('show_on_forumdisplay', 'sideboxes'))
			{
				$db->write_query("ALTER TABLE ".TABLE_PREFIX."sideboxes ADD show_on_forumdisplay INT(2) DEFAULT 1");
			}

			if(!$db->field_exists('show_on_showthread', 'sideboxes'))
			{
				$db->write_query("ALTER TABLE ".TABLE_PREFIX."sideboxes ADD show_on_showthread INT(2) DEFAULT 1");
			}

			if(!$db->field_exists('show_on_portal', 'sideboxes'))
			{
				$db->write_query("ALTER TABLE ".TABLE_PREFIX."sideboxes ADD show_on_portal INT(2) DEFAULT 1");
			}

			if(!$db->field_exists('stereo', 'sideboxes'))
			{
				$db->write_query("ALTER TABLE ".TABLE_PREFIX."sideboxes ADD stereo INT(2)");
			}

			if(!$db->field_exists('wrap_content', 'sideboxes'))
			{
				$db->write_query("ALTER TABLE ".TABLE_PREFIX."sideboxes ADD wrap_content INT(2)");
			}
		}
		else
		{
			// If the table is missing, create it.
			$collation = $db->build_create_table_collation();
			$db->write_query
			(
				"CREATE TABLE " . TABLE_PREFIX . "sideboxes
				(
					id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					display_order INT(10) NOT NULL,
					box_type VARCHAR(25) NOT NULL,
					display_name VARCHAR(32) NOT NULL,
					position INT(2),
					show_on_index INT(2),
					show_on_forumdisplay INT(2),
					show_on_showthread INT(2),
					show_on_portal INT(2),
					stereo INT(2),
					wrap_content INT(2)
				) ENGINE=MyISAM{$collation};"
			);
		}

		// check the custom_sideboxes table . . .
		if($db->table_exists('custom_sideboxes'))
		{
			// . . . and make sure it has the only added field
			if(!$db->field_exists('wrap_content', 'custom_sideboxes'))
			{
				$db->write_query("ALTER TABLE ".TABLE_PREFIX."custom_sideboxes ADD wrap_content INT(2) DEFAULT 0");
			}
		}
		else
		{
			// if it is missing create it
			$collation = $db->build_create_table_collation();
			$db->write_query
			(
				"CREATE TABLE " . TABLE_PREFIX . "custom_sideboxes
				(
					id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					name VARCHAR(32) NOT NULL,
					description VARCHAR(128) NOT NULL,
					wrap_content INT(2),
					content TEXT
				) ENGINE=MyISAM{$collation};"
			);
		}

		// field added to users table?
		if(!$db->field_exists('show_sidebox', 'users'))
		{
			// no? add it
			$db->write_query("ALTER TABLE " . TABLE_PREFIX . "users ADD show_sidebox varchar(1) DEFAULT '1'");
		}

		// returns false if the group doesn't exist/the gid # otherwise
		$gid = adv_sidebox_get_settingsgroup();

		// no group?
		if(!$gid)
		{
			// create all the settings
			adv_sidebox_create_base_settings();
		}
		else
		{
			// otherwise update all the settings
			$all_settings = array();

			$settings_query = $db->simple_select('settings', '*', "gid='{$gid}'");

			while($this_setting = $db->fetch_array($settings_query))
			{
				if((int) $this_setting['disporder'] < 10)
				{
					// if this version is before 1.3 then the settings need to be spaced out
					$this_setting['disporder'] = (int) $this_setting['disporder'] * 10;
				}

				// this is for setting 7
				$this_setting['optionscode'] = $db->escape_string($this_setting['optionscode']);

				// store the settings
				$all_settings[$this_setting['name']] = $this_setting;
			}

			// update the theme EXCLUDE list
			$update_themes_link = "<ul><li><a href=\"" . ADV_SIDEBOX_URL . "&amp;action=update_theme_select\" title=\"\">{$lang->adv_sidebox_theme_exclude_select_update_link}</a><br />{$lang->adv_sidebox_theme_exclude_select_update_description}</li></ul>";

			$all_settings['adv_sidebox_exclude_theme']['description'] = $db->escape_string($lang->adv_sidebox_theme_exclude_list_description . $update_themes_link);

			$all_settings['adv_sidebox_avatar_per_row']['title'] = $db->escape_string($lang->adv_sidebox_wol_avatar_list);

			// update the settings
			foreach($all_settings as $key => $val)
			{
				unset($val['sid']);
				$db->update_query('settings', $val, "name='{$key}'");
			}
		}

		// these modules were renamed and if they exist will be removed.
		@my_rmdir_recursive(ADV_SIDEBOX_MODULES_DIR . "/" . 'pms');
		@rmdir(ADV_SIDEBOX_MODULES_DIR . "/" . 'pms');
		@my_rmdir_recursive(ADV_SIDEBOX_MODULES_DIR . "/" . 'welcome');
		@rmdir(ADV_SIDEBOX_MODULES_DIR . "/" . 'welcome');
		@my_rmdir_recursive(ADV_SIDEBOX_MODULES_DIR . "/" . 'search');
		@rmdir(ADV_SIDEBOX_MODULES_DIR . "/" . 'search');

		// get the handler now that we've updated the db and settings
		$adv_sidebox_134 = new Sidebox_handler('', true);

		// no boxes
		if(is_array($adv_sidebox_134->sideboxes))
		{
			foreach($adv_sidebox_134->sideboxes as $this_box)
			{
				// these modules have been renamed
				if($this_box->box_type == 'pms')
				{
					$this_box->box_type = 'private_messages';
				}
				if($this_box->box_type == 'welcome')
				{
					$this_box->box_type = 'welcome_box';
				}
				if($this_box->box_type == 'search')
				{
					$this_box->box_type = 'search_box';
				}

				// if this isn't a custom box (there is a module matching the base_name)
				if($adv_sidebox_134->addons[$this_box->box_type]->base_name == $this_box->box_type)
				{
					// then update the properties added since 1.0
					$this_box->stereo = true;
					$this_box->wrap_content = $adv_sidebox_134->addons[$this_box->box_type]->wrap_content;
					$this_box->display_name = $adv_sidebox_134->addons[$this_box->box_type]->name;
				}
				elseif($adv_sidebox_134->custom[$this_box->box_type])
				{
					// update the properties added since 1.0
					$this_box->wrap_content = $adv_sidebox_134->custom[$this_box->box_type]['wrap_content'];
					$this_box->display_name = $adv_sidebox_134->custom[$this_box->box_type]['name'];
				}

				// 'on-the-fly' custom boxes have been removed as of 1.3.4
				if($this_box->box_type == 'custom_box')
				{
					$input_array = array();

					// store the content as a user-defined box, but don't show it because it could break layout
					$input_array['name'] = $db->escape_string('Custom Backup #' . $this_box->id);
					$input_array['description'] = $db->escape_string('This is a custom box from a previous installation and may need to be edited before being used to remove table headers');
					$input_array['wrap_content'] = false;

					$query = $db->simple_select('sideboxes', '*', "id='{$this_box->id}'");

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
