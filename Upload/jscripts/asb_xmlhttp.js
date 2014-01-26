/*
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2013 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains a custom class for the AJAX refresh functionality for
 * side box modules and a function to create objects for all the add-on modules
 * represented on the forum
 */

// thanks to http://www.fluther.com/users/adrianscott/
Ajax.SideboxPeriodicalUpdater = Class.create(Ajax.Base,
{
	dateline: '',

	initialize: function($super, container, url, options)
	{
		// set up our parent object
		$super(options);

		// now get this instances overrides and options
		this.onComplete = this.options.onComplete;
		this.frequency = (this.options.frequency || 2);
		this.decay = (this.options.decay || 1);
		this.updater = { };
		this.container = $(container);
		this.url = url;
		this.dateline = this.options.parameters.dateline;

		// initiate the timer
		this.start();
	},

	start: function()
	{
		this.options.onComplete = this.updateComplete.bind(this);
		this.timer = this.onTimerEvent.bind(this).delay(this.decay * this.frequency);
	},

	stop: function()
	{
		this.updater.options.onComplete = undefined;
		clearTimeout(this.timer);
		(this.onComplete || Prototype.emptyFunction).apply(this, arguments);
	},

	updateComplete: function(response)
	{
		// good response?
		if (response.responseText && response.responseText != 'nochange') {
			// might add this option later
			this.decay = 1;

			// update the side box's <tbody>
			this.container.down('tbody').update(response.responseText);

			// last update time
			this.dateline = Math.floor(new Date().getTime() / 1000);
		} else {
			// currently does nothing, but left in to add this option
			this.decay = this.decay * this.options.decay;
		}
		// key up to do it again
		this.timer = this.onTimerEvent.bind(this).delay(this.decay * this.frequency);
	},

	onTimerEvent: function()
	{
		// and finally, this is what we are doing every {rate} seconds
		this.updater = new Ajax.Request(this.url, this.options);
	}
});

/*
 * asb_build_updaters()
 *
 * prepare the Updater objects
 *
 * @param - updaters - (array) an array filled with objects filled with side box details
 */
function asb_build_updaters(updaters, width_left, width_right)
{
	// no objects in the array
	if (updaters.length == 0) {
		// get out
		return;
	}

	var this_id = '';
	var name_array = [];
	var width = 0;
	for (var i = 0; i < updaters.length; i++) {
		// build the element ID
		this_id = updaters[i].addon + '_main_' + updaters[i].id;

		// get the correct width
		width = width_left;
		if (updaters[i].position) {
			width = width_right;
		}

		if ($(this_id)) {
			// this object will only update when a valid response is received
			new Ajax.SideboxPeriodicalUpdater(this_id, 'xmlhttp.php',
			{
				parameters:
				{
					action: 'asb',
					id: updaters[i].id,
					addon: updaters[i].addon,
					dateline: updaters[i].dateline,
					width: width
				},
				method: 'post',
				frequency: updaters[i].rate
			});
		}
	}
}
