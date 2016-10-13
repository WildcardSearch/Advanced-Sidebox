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
	 * @param string the id of the container to be updated
	 * @param string the URL of the AJAX server-side routine
	 * @param object various options for the updater
	 * @return void
	 */
	function SideboxUpdater(container, url, options) {
		this.options = $.extend({}, options || {});

		// now get this instance's overrides and options
		this.frequency = (this.options.frequency || 30);
		this.decay = this.options.decay = (this.options.decay || 1);
		this.container = $("#" + container).children('tbody');
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
		this.timer = setTimeout($.proxy(this.onTimerEvent, this), (this.decay * this.frequency) * 1000);
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
	 * @param - response - (Response) the XMLHTTP response object
	 * @return void
	 */
	function updateComplete(response) {
		// good response?
		if (response.responseText &&
			response.responseText != 'nochange') {
			// might add this option later
			this.decay = this.options.decay;

			// update the side box's <tbody>
			this.container.html(response.responseText);

		} else {
			// currently does nothing, but left in to add this option
			this.decay = this.decay * this.options.decay;
		}

		// last update time
		this.options.data.dateline = Math.floor(new Date().getTime() / 1000 + this.phpTimeDiff);

		// key up to do it again
		this.timer = setTimeout($.proxy(this.onTimerEvent, this), (this.decay * this.frequency) * 1000);
	}

	/**
	 * onTimerEvent()
	 *
	 * send an AJAX request unless the side box is collapsed
	 *
	 * @return void
	 */
	function onTimerEvent() {
		// don't update collapsed side boxes (thanks again, Destroy666)
		if (this.container.width() <= 0 &&
			this.container.height() <= 0) {
			// just reset the timer and get out
			this.timer = setTimeout($.proxy(this.onTimerEvent, this), (this.decay * this.frequency) * 1000);
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

	/*
	 * buildUpdaters()
	 *
	 * prepare the Updater objects
	 *
	 * @param array objects filled with side box details
	 * @param object widths for both positions
	 * @return void
	 */
	function buildUpdaters(updaters, widths, script) {
		// no objects in the array
		if (updaters.length == 0) {
			// get out
			return;
		}

		var this_id = '', width = 0;
		for (var i = 0; i < updaters.length; i++) {
			// build the element ID
			this_id = updaters[i].addon + '_main_' + updaters[i].id;

			if (!$("#" + this_id)) {
				continue;
			}

			// get the correct width
			width = widths.left;
			if (updaters[i].position) {
				width = widths.right;
			}

			// this object will only update when a valid response is received
			new SideboxUpdater(this_id, 'xmlhttp.php', {
				type: 'get',
				data: {
					action: 'asb',
					id: updaters[i].id,
					addon: updaters[i].addon,
					dateline: updaters[i].dateline,
					width: width,
					script: script,
				},
				frequency: updaters[i].rate
			});
		}
	}

	a.ajax = {
		buildUpdaters: buildUpdaters,
	};

	return a;
})(ASB || {}, jQuery);
