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
				window.open(this.href, 'asbHelp', 'width=840, height=520, scrollbars=yes');
			});
		}

		if ($('help_link_icon')) {
			$('help_link_icon').observe('click', function(event) {
				Event.stop(event);
				window.open(this.up('a').href, 'asbHelp', 'width=840, height=520, scrollbars=yes');
			});
		}
	}
};
Event.observe(window, 'load', ASB.init);
