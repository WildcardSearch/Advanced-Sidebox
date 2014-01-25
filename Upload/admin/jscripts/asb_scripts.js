/*
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2013 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains JavaScript for the ACP script edit functions
 */

Event.observe(window, 'load', function() {
	// only show replace all options when selected
	new Peeker($$(".replace_all"), $("replace_content"), /1/, true);

	// hide search & replace inputs when replacing
	new Peeker($$(".replace_all"), $("header_search"), /0/, true);
	new Peeker($$(".replace_all"), $("footer_search"), /0/, true);

	// if we are eval()ing, hide all the inputs that are unnecessary
	new Peeker($$(".eval"), $("template_row"), /0/, true);
	new Peeker($$(".eval"), $("header_search"), /0/, true);
	new Peeker($$(".eval"), $("footer_search"), /0/, true);
	new Peeker($$(".eval"), $("replace_all"), /0/, true);
	new Peeker($$(".eval"), $("replace_content"), /0/, true);

	if ($('replace_all_yes').checked) {
		$("header_search").hide();
		$("footer_search").hide();
	} else {
		$("replace_content").hide();
	}

	if ($('eval_yes').checked) {
		$("header_search").hide();
		$("footer_search").hide();
		$("template_row").hide();
		$("replace_all").hide();
		$("replace_content").hide();
	}

	// watch the 'detected' selectors and send the chosen
	// item to the appropriate text box
	asb_observe_inputs();

	// watch the file name input and if it has changed on blur
	// attempt to detect hooks, template and URL attributes (page, action)
	// and display them as selectable lists
	$('filename').observe('blur', function(event) {
		// if nothing has changed, get out
		if (this.value == edit_script || this.value == '') {
			return;
		}

		// otherwise, update the current script
		edit_script = this.value;

		// hide the 'detected' selectors
		$('hook_list').hide();
		$('template_list').hide();
		$('action_list').hide();

		// show all the spinners
		var spinners = $$('div.ajax_spinners');
		for (x = 0; x < spinners.length; ++x) {
			spinners[x].show();
		}

		// attempt to get the info
		new Ajax.Request('index.php', {
			parameters:
			{
				module: 'config-asb',
				action: 'xmlhttp',
				mode: 'analyze_script',
				filename: $('filename').value
			},
			onSuccess: function(response)
			{
				// hide all the spinners
				var spinners = $$('div.ajax_spinners');
				for (x = 0; x < spinners.length; ++x) {
					spinners[x].hide();
				}

				// any response at all means something to show
				if (!response.responseText) {
					return;
				}

				// send JSON so we have to decode it
				var info = response.responseText.evalJSON();

				// if there is info, show it
				if (info['hooks']) {
					$('hook_list').innerHTML = info['hooks'];
					$('hook_list').show();
				}

				if (info['templates']) {
					$('template_list').innerHTML = info['templates'];
					$('template_list').show();
				}

				if (info['actions']) {
					$('action_list').innerHTML = info['actions'];
					$('action_list').show();
				}

				// re-do our observation of the selectors now that
				// they have been rebuilt
				asb_observe_inputs();
			}
		});
	});
});

// watch the 'detected' selectors and send the input to the appropriate field
function asb_observe_inputs()
{
	if ($('hook_selector')) {
		$('hook_selector').observe('change', function(event) {
			$('hook').value = this.value;
		});
	}

	if ($('template_selector')) {
		$('template_selector').observe('change', function(event) {
			$('template_name').value = this.value;
		});
	}

	if ($('action_selector')) {
		$('action_selector').observe('change', function(event) {
			$('action').value = this.value;
		});
	}
}
