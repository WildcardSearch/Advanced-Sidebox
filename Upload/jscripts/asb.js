/*
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2013 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains handlers for the side box toggle icon scripts
 */

// hide/show toggle icons
Event.observe(window, 'load', function() {
	if ($('asb_hide_column_left')) {
		// left show/hide icon click
		$('asb_hide_column_left').observe('click', function(event) {
			// the link does nothing if JS is deactivated and until the page has fully loaded
			Event.stop(event);

			// get the cookie
			var asb_hide_left = Cookie.get("asb_hide_left");

			// if it isn't set or its zero then we are hiding
			if (asb_hide_left == 0 || asb_hide_left == undefined) {
				$('asb_left_column_id').style.display = 'none';
				$('asb_left_close').style.display = 'none';
				$('asb_left_open').style.display = 'inline';
				Cookie.set("asb_hide_left", 1);
			} else {
				// otherwise we are showing
				$('asb_left_column_id').style.display = '';
				$('asb_left_close').style.display = 'inline';
				$('asb_left_open').style.display = 'none';
				Cookie.unset("asb_hide_left");
			}
		});
	}

	if ($('asb_hide_column_right')) {
		// right show/hide icon click
		$('asb_hide_column_right').observe('click', function(event) {
			Event.stop(event);

			var asb_hide_right = Cookie.get("asb_hide_right");

			if (asb_hide_right == 0 || asb_hide_right == undefined) {
				// hiding
				$('asb_right_column_id').style.display = 'none';
				$('asb_right_close').style.display = 'none';
				$('asb_right_open').style.display = 'inline';
				Cookie.set("asb_hide_right", 1);
			} else {
				// showing
				$('asb_right_column_id').style.display = '';
				$('asb_right_close').style.display = 'inline';
				$('asb_right_open').style.display = 'none';
				Cookie.unset("asb_hide_right");
			}
		});
	}
});
