/*
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains a custom class for the AJAX refresh functionality for
 * side box modules and a function to create objects for all the add-on modules
 * represented on the forum
 */

// thanks to http://www.fluther.com/users/adrianscott/
var ASB = (function(a) {
	/**
	 * initialize()
	 *
	 * constructor: sets up the object and starts the timer
	 *
	 * @param - $super - (callback) the parent initialize() function, Ajax.Base.initialize()
	 * @param - container - (string) the id of the container to be updated
	 * @param - url - (string) the URL of the AJAX server-side routine
	 * @param - options - (object) various options for the updater
	 * @return: n/a
	 */
	function initialize($super, container, url, options) {
		// set up our parent object
		$super(options);

		// now get this instance's overrides and options
		this.onComplete = this.options.onComplete;
		this.frequency = (this.options.frequency || 30);
		this.decay = this.options.decay = (this.options.decay || 1);
		this.updater = {};
		this.container = $(container).down('tbody');
		this.url = url;

		// if the server is on a different timezone, get the offset in seconds
		this.phpTimeDiff = Math.floor(this.options.parameters.dateline - (new Date().getTime() / 1000));

		// initiate the timer
		this.start();
	}

	/**
	 * start()
	 *
	 * initiate the timer
	 *
	 * @return: n/a
	 */
	function start() {
		this.options.onComplete = this.updateComplete.bind(this);
		this.timer = this.onTimerEvent.bind(this).delay(this.decay * this.frequency);
	}

	/**
	 * stop()
	 *
	 * halt the timer
	 *
	 * @return: n/a
	 */
	function stop() {
		this.updater.options.onComplete = undefined;
		clearTimeout(this.timer);
		(this.onComplete || Prototype.emptyFunction).apply(this, arguments);
	}

	/**
	 * updateComplete()
	 *
	 * check the XMLHTTP response and update the side box if there are changes
	 *
	 * @param - response - (Response) the XMLHTTP response object
	 * @return: n/a
	 */
	function updateComplete(response) {
		// good response?
		if (response.responseText && response.responseText != 'nochange') {
			// might add this option later
			this.decay = this.options.decay;

			// update the side box's <tbody>
			this.container.update(response.responseText);

		} else {
			// currently does nothing, but left in to add this option
			this.decay = this.decay * this.options.decay;
		}

		// last update time
		this.options.parameters.dateline = Math.floor(new Date().getTime() / 1000 + this.phpTimeDiff);

		// key up to do it again
		this.timer = this.onTimerEvent.bind(this).delay(this.decay * this.frequency);
	}

	/**
	 * onTimerEvent()
	 *
	 * send an AJAX request unless the side box is collapsed
	 *
	 * @return: n/a
	 */
	function onTimerEvent() {
		// don't update collapsed side boxes (thanks again, Destroy666)
		if (this.container.offsetWidth <= 0 && this.container.offsetHeight <= 0) {
			// just reset the timer and get out
			this.timer = this.onTimerEvent.bind(this).delay(this.decay * this.frequency);
			return;
		}

		// and finally, this is what we are doing every {rate} seconds
		this.updater = new Ajax.Request(this.url, this.options);
	}

	SideboxUpdater = Class.create(Ajax.Base, {
		initialize: initialize,
		start: start,
		stop: stop,
		updateComplete: updateComplete,
		onTimerEvent: onTimerEvent,
	});

	/*
	 * buildUpdaters()
	 *
	 * prepare the Updater objects
	 *
	 * @param - updaters - (array) an array filled with objects filled with side box details
	 * @param - widths - (object) widths for both positions
	 * @return: n/a
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

			if (!$(this_id)) {
				continue;
			}

			// get the correct width
			width = widths.left;
			if (updaters[i].position) {
				width = widths.right;
			}

			// this object will only update when a valid response is received
			new SideboxUpdater(this_id, 'xmlhttp.php', {
				parameters: {
					action: 'asb',
					id: updaters[i].id,
					addon: updaters[i].addon,
					dateline: updaters[i].dateline,
					width: width,
					script: script,
				},
				method: 'get',
				frequency: updaters[i].rate
			});
		}
	}

	a.ajax = {
		buildUpdaters: buildUpdaters,
	};

	return a;
})(ASB || {});
