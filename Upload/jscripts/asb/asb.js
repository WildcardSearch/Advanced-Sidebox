/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains handlers for the side box toggle icon scripts
 */

var ASB = (function($, a) {
	"use strict";

	var $leftColumn,
		$middleColumn,
		$rightColumn,

		currentMiddleWidth,

	options = {
		leftWidth: 20,
		leftMargin: 0.5,
		middleWidth: 59,
		rightMargin: 0.5,
		rightWidth: 20,
	};

	/**
	 * observe the toggle icons links
	 *
	 * @return void
	 */
	function init() {
		$leftColumn = $("#asb_hide_column_left");
		$middleColumn = $("#asb_middle_column");
		$rightColumn = $("#asb_hide_column_right");

		currentMiddleWidth = options.middleWidth;

		if ($middleColumn.length < 1 ||
			($leftColumn.length < 1 &&
			$rightColumn.length < 1)) {
			return;
		}

		if ($leftColumn.length) {
			// left show/hide icon click
			$("#asb_hide_column_left").on("click", toggle);
		}

		if ($rightColumn.length) {
			// left show/hide icon click
			$("#asb_hide_column_right").on("click", toggle);
		}
	}

	/**
	 * toggle the side box column controlled by the clicked icon and
	 * use cookies to preserve settings
	 *
	 * @param  Event the click event object
	 * @return void
	 */
	function toggle(e) {
		var $column, $closeIcon, $openIcon,
			cookieName, marginKey, state,
			margin = options.leftMargin,
			width = options.leftWidth,
			position = "left";

		// the link does nothing if JS is deactivated and until the page has fully loaded
		e.preventDefault();

		if (this.id == "asb_hide_column_right") {
			position = "right";
			margin = options.rightMargin;
			width = options.rightWidth;
		}

		cookieName = "asb_hide_"+position;
		$column = $("#asb_"+position+"_column_id");
		$closeIcon = $("#asb_"+position+"_close");
		$openIcon = $("#asb_"+position+"_open");
		marginKey = "margin-"+position;

		// get the cookie
		state = Cookie.get(cookieName);

		// if it isn't set or its zero then we are hiding
		if (typeof state == "undefined" ||
			state < 1) {
			$column.hide();
			$closeIcon.hide();

			currentMiddleWidth += width;
			css = {
				width: currentMiddleWidth+"%",
			};

			css[marginKey] = "0px";

			$middleColumn.css(css);

			$openIcon.show();

			Cookie.set(cookieName, 1);
		} else {
			// otherwise we are showing
			currentMiddleWidth -= width;
			css = {
				width: currentMiddleWidth+"%",
			};

			css[marginKey] = margin+"%";

			$middleColumn.css(css);

			$column.show();
			$closeIcon.show();
			$openIcon.hide();
			Cookie.unset(cookieName);
		}
	}

	function setup(o) {
		$.each([ "leftWidth", "leftMargin", "middleWidth", "rightMargin", "rightWidth" ], function() {
			if (typeof o[this] !== "string") {
				return;
			}

			options[this] = parseFloat(o[this]);
		});
	}

	$(init);

	a.setup = setup;

	return a;
})(jQuery, ASB || {});
