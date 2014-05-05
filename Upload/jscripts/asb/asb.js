/*
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains handlers for the side box toggle icon scripts
 */

(function() {
	/**
	 * init()
	 *
	 * observe the toggle icons links
	 *
	 * @return: n/a
	 */
	function init() {
		if ($('asb_hide_column_left')) {
			// left show/hide icon click
			$('asb_hide_column_left').observe('click', toggle);
		}

		if ($('asb_hide_column_right')) {
			// left show/hide icon click
			$('asb_hide_column_right').observe('click', toggle);
		}
	}

	/**
	 * toggle()
	 *
	 * toggle the side box column controlled by the clicked icon and
	 * use cookies to preserve settings
	 *
	 * @param - event - (Event) the click event object
	 * @return: n/a
	 */
	function toggle(event) {
		// the link does nothing if JS is deactivated and until the page has fully loaded
		Event.stop(event);

		var position = 'left';
		if (this.id == 'asb_hide_column_right') {
			position = 'right';
		}

		var cookieName = 'asb_hide_' + position,
		column = $('asb_' + position + '_column_id'),
		closeIcon = $('asb_' + position + '_close'),
		openIcon = $('asb_' + position + '_open');
		
		// get the cookie
		var hide = Cookie.get(cookieName);

		// if it isn't set or its zero then we are hiding
		if (hide == 0 || hide == undefined) {
			column.style.display = 'none';
			closeIcon.style.display = 'none';
			openIcon.style.display = 'inline';
			Cookie.set(cookieName, 1);
		} else {
			// otherwise we are showing
			column.style.display = '';
			closeIcon.style.display = 'inline';
			openIcon.style.display = 'none';
			Cookie.unset(cookieName);
		}
	}
	Event.observe(window, 'load', init);
})();
