/*
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2013 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains JavaScript for the ACP functions
 */

ASB = {
	/**
	 * init()
	 *
	 * observe help links
	 *
	 * @return: n/a
	 */
	init: function()
	{
		if ($('help_link')) {
			$('help_link').observe('click', function(event) {
				Event.stop(event);
				MyBB.popupWindow(this.href, 'asbHelp', 840, 520);
			});
		}

		if ($('help_link_icon')) {
			$('help_link_icon').observe('click', function(event) {
				Event.stop(event);
				MyBB.popupWindow(this.up('a').href, 'asbHelp', 840, 520);
			});
		}
	}
};
Event.observe(window, 'load', ASB.init);
