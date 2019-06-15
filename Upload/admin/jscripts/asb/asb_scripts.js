/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains JavaScript for the ACP script edit functions
 */

var ASB = (function($, a) {
	"use strict";

	var current = "",
		lang = {
			nothing_found: "nothing found",
			hooks: "hooks",
			actions: "actions",
			templates: "templates",
			error_file_name_empty: "no file name",
			error_file_does_not_exist: "file does not exist",
			error_file_empty: "file empty or corrupted",
		};

	/**
	 * hide/show the appropriate inputs based on current values,
	 * observe action/hook/template detection drop-downs and
	 * bind detection to changing of the script filename
	 *
	 * @return void
	 */
	function init() {
		// only show replace all options when selected
		new Peeker($(".replace_all"), $("#replace_content"), /1/, true);

		// hide search & replace inputs when replacing
		new Peeker($(".replace_all"), $("#header_search"), /0/, true);
		new Peeker($(".replace_all"), $("#footer_search"), /0/, true);

		// if we are eval()ing, hide all the inputs that are unnecessary
		new Peeker($(".eval"), $("#template_row"), /0/, true);
		new Peeker($(".eval"), $("#header_search"), /0/, true);
		new Peeker($(".eval"), $("#footer_search"), /0/, true);
		new Peeker($(".eval"), $("#replace_all"), /0/, true);
		new Peeker($(".eval"), $("#replace_content"), /0/, true);

		if ($("#replace_all_yes").prop("checked")) {
			$("#header_search").hide();
			$("#footer_search").hide();
		} else {
			$("#replace_content").hide();
		}

		if ($("#eval_yes").prop("checked")) {
			$("#header_search").hide();
			$("#footer_search").hide();
			$("#template_row").hide();
			$("#replace_all").hide();
			$("#replace_content").hide();
		}

		// watch the "detected" selectors and send the chosen
		// item to the appropriate text box
		observeInputs();

		/*
		 * watch the file name input and if it has changed on blur
		 * attempt to detect hooks, template and URL attributes
		 * (page, action)and display them as selectable lists
		 */
		$("#filename").on("blur", update);
	}

	/**
	 * show the spinners and launch the detection request
	 *
	 * @param  Event the blur event object
	 * @return void
	 */
	function update(event) {
		// if nothing has changed, get out
		if (this.value == current ||
			this.value == "") {
			return;
		}

		// otherwise, update the current script
		current = this.value;

		// hide the "detected" selectors
		$("#hook_list").hide();
		$("#template_list").hide();
		$("#action_list").hide();

		// show all the spinners
		$("div.ajax_spinners").show();

		$.ajax({
			type: "post",
			url: "index.php",
			data: {
				module: "config-asb",
				action: "xmlhttp",
				mode: "analyze_script",
				filename: this.value,
				selected: {
					hook: $("#hook").val(),
					template: $("#template_name").val(),
					action: $("#action").val(),
				},
			},
			success: showResults,
			error: function(jqXHR, textStatus, errorThrown) {
				alert("error" +
					"\n\n" +
					textStatus +
					"\n\n" +
					errorThrown);
			},
		});
	}

	/**
	 * hide the spinners and build the select elements
	 *
	 * @param  Response the XMLHTTP response object
	 * @return void
	 */
	function showResults(info) {
		var $fileInfo = $("#file_info"),
			errorMessage;

		// hide all the spinners
		$("div.ajax_spinners").hide();

		// check for errors
		if (typeof info.error !== "undefined") {
			switch (info.error) {
			case 1:
				errorMessage = lang.error_file_name_empty;
				break;
			case 3:
				errorMessage = lang.error_file_empty;
				break;
			default:
				errorMessage = lang.error_file_does_not_exist;
				break;
			}

			$fileInfo.html(errorMessage);
			return;
		} else {
			$fileInfo.html("")
		}

		/*
		 * use each of the key words in the array to
		 * show detected items or a message that there
		 * was nothing to show
		 */
		$.each(["hook", "template", "action"], function(i, k) {
			// make the plural version
			var ks = k + "s",
				// the no content message
				html = '<span style="font-style: italic;">' + lang.nothing_found.replace("{1}", lang[ks]) + "</span>";

			// if there is info...
			if (info[ks]) {
				// ...overwrite the no content message
				html = info[ks];
			}
			$("#" + k + "_list").html(html).show();
		});

		// re-do our observation of the selectors now that they have been rebuilt
		observeInputs();
	}

	/**
	 * insert the chosen item from the select elements into the appropriate input
	 *
	 * @param  Response the XMLHTTP response object
	 * @return void
	 */
	function observeInputs() {
		if ($("#hook_selector")) {
			$("#hook_selector").on("change", function(e) {
				var val = this.value;
				if (val == 0) {
					val = "";
				}

				$("#hook").val(val);
			});
		}

		if ($("#template_selector")) {
			$("#template_selector").on("change", function(e) {
				var val = this.value;
				if (val == 0) {
					val = "";
				}

				$("#template_name").val(val);
			});
		}

		if ($("#action_selector")) {
			$("#action_selector").on("change", function(e) {
				var val = this.value;
				if (val == 0) {
					val = "";
				}

				$("#action").val(val);
			});
		}
	}

	/**
	 * store the script currently being edited and
	 * get the local language
	 *
	 * @param  String the current file name
	 * @return void
	 */
	function setup(v, l) {
		current = v || "";
		$.extend(lang, l || {});
	}

	$(init);

	a.scripts = {
		setup: setup,
	};

	return a;
})(jQuery, ASB || {});
