/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains JavaScript for the ACP functions
 */

$(function() {
	if ($("#help_link")) {
		$("#help_link").click(function(event) {
			event.preventDefault();
			window.open(this.href, "asbHelp", "toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes.width=840,height=520")
		});
	}

	if ($("#help_link_icon")) {
		$("#help_link_icon").click(function(event) {
			event.preventDefault();
			window.open(this.href, "asbHelp", "toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes.width=840,height=520")
		});
	}
});
