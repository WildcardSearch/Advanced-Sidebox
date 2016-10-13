/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains handlers for the side box toggle icon scripts
 */

(function() {
	/**
	 * observe the toggle icons links
	 *
	 * @return void
	 */
	function init() {
		if ($('#asb_hide_column_left')) {
			// left show/hide icon click
			$('#asb_hide_column_left').click(toggle);
		}

		if ($('#asb_hide_column_right')) {
			// left show/hide icon click
			$('#asb_hide_column_right').click(toggle);
		}
	}

	/**
	 * toggle the side box column controlled by the clicked icon and
	 * use cookies to preserve settings
	 *
	 * @param Event the click event object
	 * @return void
	 */
	function toggle(event) {
		// the link does nothing if JS is deactivated and until the page has fully loaded
		event.preventDefault();

		var position = 'left';
		if (this.id == 'asb_hide_column_right') {
			position = 'right';
		}

		var cookieName = 'asb_hide_' + position,
		column = $('#asb_' + position + '_column_id'),
		closeIcon = $('#asb_' + position + '_close'),
		openIcon = $('#asb_' + position + '_open');

		// get the cookie
		var hide = Cookie.get(cookieName);

		// if it isn't set or its zero then we are hiding
		if (hide == 0 ||
			typeof hide == 'undefined') {
			column.hide();
			closeIcon.hide();
			openIcon.show();
			Cookie.set(cookieName, 1);
		} else {
			// otherwise we are showing
			column.show();
			closeIcon.show();
			openIcon.hide();
			Cookie.unset(cookieName);
		}
	}
	$(init);
})();
