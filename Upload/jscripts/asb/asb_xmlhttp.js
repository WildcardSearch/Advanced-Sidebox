/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains a custom class for the AJAX refresh functionality for
 * side box modules and a function to create objects for all the add-on modules
 * represented on the forum
 */

var ASB = (function(a, $) {
	/**
	 * constructor: sets up the object and starts the timer
	 *
	 * @param  string container id
	 * @param  string url
	 * @param  object options
	 * @return void
	 */
	function SideboxUpdater(container, url, options) {
		this.options = $.extend({}, options || {});

		// now get this instance's overrides and options
		this.rate = this.options.rate = (this.options.rate || 30);
		this.decay = this.options.decay = this.options.decay >= 1 ? this.options.decay : 1;
		this.container = $("#" + container);
		this.options.url = url;
		this.options.complete = $.proxy(this.updateComplete, this);

		// if the server is on a different timezone, get the offset in seconds
		this.phpTimeDiff = Math.floor(this.options.data.dateline - (new Date().getTime() / 1000));

		// initiate the timer
		this.start();
	}

	/**
	 * initiate the timer
	 *
	 * @return void
	 */
	function start() {
		this.timer = setTimeout($.proxy(this.onTimerEvent, this), (this.decay * this.rate) * 1000);
	}

	/**
	 * halt the timer
	 *
	 * @return void
	 */
	function stop() {
		clearTimeout(this.timer);
	}

	/**
	 * check the XMLHTTP response and update the side box if there are changes
	 *
	 * @param  Response
	 * @return void
	 */
	function updateComplete(response) {
		// good response?
		if (typeof response.responseText !== "undefined" &&
			response.responseText.length > 0) {
			this.rate = this.options.rate;

			// update the side box's content
			this.container.html(response.responseText);
		} else {
			this.rate = this.decay * this.rate;
		}

		// this.container.html(`Rate: ${this.rate},<br />Decay: ${this.decay}`);
		// last update time
		this.options.data.dateline = Math.floor(new Date().getTime() / 1000 + this.phpTimeDiff);

		// key up to do it again
		this.timer = setTimeout($.proxy(this.onTimerEvent, this), this.rate * 1000);
	}

	/**
	 * send an AJAX request unless the side box is collapsed
	 *
	 * @return void
	 */
	function onTimerEvent() {
		// don't update collapsed side boxes (thanks again, Destroy666)
		if (this.container.width() <= 0 &&
			this.container.height() <= 0) {
			// just reset the timer and get out
			this.timer = setTimeout($.proxy(this.onTimerEvent, this), (this.decay * this.rate) * 1000);
			return;
		}

		// and finally, this is what we are doing every {rate} seconds
		$.ajax(this.options);
	}

	SideboxUpdater.prototype = {
		start: start,
		stop: stop,
		updateComplete: updateComplete,
		onTimerEvent: onTimerEvent,
	};

	/**
	 * prepare the Updater objects
	 *
	 * @param  array options
	 * @param  object widths for both positions
	 * @return void
	 */
	function buildUpdaters(updaters, widths, script) {
		var i,
			this_id = "", width = 0;

		// no objects in the array
		if (updaters.length == 0) {
			// get out
			return;
		}

		for (i = 0; i < updaters.length; i++) {
			// build the element ID
			this_id = updaters[i].addon + "_" + updaters[i].id + "_e";

			if (!$("#" + this_id)) {
				continue;
			}

			// get the correct width
			width = widths.left;
			if (updaters[i].position) {
				width = widths.right;
			}

			// this object will only update when a valid response is received
			new SideboxUpdater(this_id, "xmlhttp.php", {
				type: "get",
				data: {
					action: "asb",
					id: updaters[i].id,
					addon: updaters[i].addon,
					dateline: updaters[i].dateline,
					width: width,
					script: script,
				},
				rate: updaters[i].rate,
				decay: updaters[i].decay,
			});
		}
	}

	a.ajax = {
		buildUpdaters: buildUpdaters,
	};

	return a;
})(ASB || {}, jQuery);
