/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * inline controls
 */

/**
 * @var object the inline controls module
 */
var ASB = (function($, a) {
	"use strict";

	/**
	 * @var int the currently checked
	 */
	var checkCount = 0,

	/**
	 * @var object the default language
	 */
	lang = {
		go: 'Go',
		noSelection: 'You did not select anything.',
	};

	/**
	 * initiate the selected count and observe inputs
	 *
	 * @return void
	 */
	function init() {
		initialCount();
		$('#asb_select_all').on("click", selectAll);
		$('.asb_check').on("click", keepCount);
		$('#asb_inline_clear').on("click", clearAll);
		$('#asb_inline_submit').on("click", submitCheck);
	}

	/**
	 * allow custom language overrides
	 *
	 * @param  object the custom language
	 * @return void
	 */
	function setup(l) {
		$.extend(lang, l || {});
	}

	/**
	 * squeal if admin is submitting inline with nothing checked
	 *
	 * @param  object the event
	 * @return void
	 */
	function submitCheck(e) {
		if (checkCount) {
			return;
		}

		e.preventDefault();
		$.jGrowl(lang.noSelection);

	}

	/**
	 * sync all check boxes on this page with the master
	 *
	 * @param  object the event
	 * @return void
	 */
	function selectAll(e) {
		var onOff = false;

		if ($(this).prop("checked")) {
			onOff = true;
		}
		setAllChecks(onOff);
	}

	/**
	 * set all check boxes on this page on/off
	 *
	 * @param  bool true for checked, false for unchecked
	 * @return void
	 */
	function setAllChecks(onOff) {
		if (onOff !== true) {
			onOff = false;
		}

		checkCount = 0;
		$('#asb_select_all').prop("checked", onOff);
		$('.asb_check').each(function(k, check) {
			var $row = $(check).parent("label").parent("td").parent("tr");

			$(check).prop("checked", onOff);
			if (onOff) {
				$row.addClass("asb-script-checked");
				++checkCount;
			} else {
				$row.removeClass("asb-script-checked");
			}
		});

		updateCheckCount();
	}

	/**
	 * adjust checked count on-the-fly
	 *
	 * @param  object the event
	 * @return void
	 */
	function keepCount(e) {
		var $row = $(this).parent("label").parent("td").parent("tr");

		if (this.checked) {
			$row.addClass("asb-script-checked");
			++checkCount;
		} else {
			$row.removeClass("asb-script-checked");
			--checkCount;
		}

		updateCheckCount();
	}

	/**
	 * update the go button text to reflect the currently checked count
	 *
	 * @return void
	 */
	function updateCheckCount() {
		$('#asb_inline_submit').val(lang.go + ' (' + checkCount + ')');
	}

	/**
	 * clear all check boxes when the clear button is clicked
	 *
	 * @param  object the event
	 * @return void
	 */
	function clearAll(e) {
		setAllChecks();
	}

	/**
	 * count the initially checked boxes
	 *
	 * @return void
	 */
	function initialCount() {
		checkCount = 0;
		$('.asb_check').each(function(k, check) {
			var $row = $(check).parent("label").parent("td").parent("tr");

			if ($(check).prop("checked")) {
				$row.addClass("asb-script-checked");
				++checkCount;
			} else {
				$row.removeClass("asb-script-checked");
			}
		});

		updateCheckCount();
	}

	$(init);

	// the public method
	a.inline = {
		setup: setup,
	};

	return a;
})(jQuery, ASB || {});
