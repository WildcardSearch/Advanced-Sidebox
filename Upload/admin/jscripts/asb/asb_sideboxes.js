/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains JavaScript for the ACP side box functions
 */

var ASB = (function(a, $) {
	/**
	 * build the columns, observe the edit links on all side box divs,
	 * remove the delete icons from side box divs and replace
	 * the module links with a plain text title
	 *
	 * @return void
	 */
	function init() {
		$(".draggable").draggable({
			revert: true,
		});

		$(".sortable").sortable({
			connectWith: ".column",
			update: function(e, ui) {
				var container = $(this);

				$.ajax({
					type: "post",
					url: "index.php",
					data: {
						module: "config-asb",
						action: "xmlhttp",
						mode: "order",
						pos: container.prop("id"),
						data: container.sortable("serialize"),
					},
					success: function(response) {
						if (!response) {
							return;
						}

						// if the admin session has expired
						if (typeof response == "string" &&
							response.search('value="login"') != -1) {
							// show them the login page
							location.reload(true);
						}

						var ids = response.split(",");

						// clean the input
						$(ids).each(function(i, id) {
							ids[i] = parseInt(id);
						});
						removeDivs(ids);
					},
					error: function(jqXHR, textStatus, errorThrown) {
						alert(textStatus +
							"\n\n" +
							errorThrown);
					},
				});
			},
		}).disableSelection();

		$(".droppable").droppable({
			accept: ".draggable",
			hoverClass: "hover",
			drop: create,
		});

		// observe the edit links on side boxes
		$("a[id^='edit_sidebox_']").click(edit);

		// remove the delete icon as we have the trash column
		$(".del_icon").remove();

		// replace all the links with their titles
		$(".add_box_link").each(function(i, e) {
			e.replaceWith($(e).html());
		});
	}

	/* side boxes */

	/**
	 * open a modal dialog to edit the chosen side box
	 *
	 * @param  Event the click event object
	 * @return void
	 */
	function edit(event) {
		/**
		 * stop the link from redirecting the user--
		 * set up this way so that if JS is disabled
		 * the user goes to a standard form rather than
		 * a modal edit form
		 */
		event.preventDefault();

		loadModal($(this).prop("href") + "&ajax=1");
	}

	/**
	 * open a modal dialog to create a new side box
	 *
	 * @param event
	 * @param object the UI information
	 * @return void
	 */
	function create(e, ui) {
		// sort by position
		var pos = ($(this).prop("id") == "left_column") ? 0 : 1,
			url = "index.php?module=config-asb&action=edit_box&ajax=1&box=0&addon=" + ui.draggable.prop("id") + "&pos=" + pos;

		loadModal(url);
	}

	/**
	 * open a modal
	 *
	 * @param  String
	 * @return void
	 */
	function loadModal(url) {
		$.get(url,
			function (html) {
				if (!html ||
					html.length == 0) {
					return;
				}

				// if the admin session has expired
				if (typeof html == "string" &&
					html.search("errors") != -1 &&
					html.search("login") != -1) {
					// redirect to the login page
					location.reload(true);
					return;
				}

				// show the modal
				$(html).appendTo("body").modal({
					fadeDuration: 250,
					zIndex: (typeof modal_zindex !== "undefined" ? modal_zindex : 9999),
				});
			});
	}

	/* side box divs */

	/**
	 * create a simple side box div to be completed by updateDiv
	 *
	 * @param  int id
	 * @param  string title
	 * @param  string column id
	 * @return void
	 */
	function createDiv(id, title, columnId) {
		if (!$("#" + columnId)) {
			return;
		}

		$("#" + columnId).append($("<div/>", {
			id: "sidebox_" + id,
			"class": "sidebox",
		}).html(title));
	}

	/**
	 * update a side box's div with new info
	 *
	 * @param  int id
	 * @return void
	 */
	function updateDiv(id) {
		var sideboxId = "#sidebox_" + id;
		if (!$(sideboxId)) {
			return;
		}

		$(sideboxId).load("index.php", {
			module: "config-asb",
			action: "xmlhttp",
			mode: "build_info",
			id: id,
			ajax: "1",
		});
	}

	/**
	 * delete one or more side box div elements by id
	 *
	 * @param  Array ids
	 * @return void
	 */
	function removeDivs(ids) {
		if (ids.length == 0) {
			return;
		}

		var i, sideboxId, sidebox;
		for (i = 0; i < ids.length; i++) {
			sideboxId = "#sidebox_" + ids[i];
			if (!$(sideboxId)) {
				continue;
			}

			// change the text and fade the <div> out
			sidebox = $(sideboxId);
			sidebox.css({
				backgroundColor: "#f00",
			});
			sidebox.html(lang.deleting_sidebox);
			sidebox.fadeOut(800, function() {
				$(this).remove();
			});
		}
	}

	$(init);

	a.sidebox = {
		createDiv: createDiv,
		updateDiv: updateDiv,
	};

	return a;
})(ASB || {}, jQuery);
