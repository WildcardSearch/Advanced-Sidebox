/*
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains JavaScript for the ACP side box functions
 */

var ASB = (function(a) {
	var columns = ['left_column', 'right_column', 'trash_column'];

	/**
	 * init()
	 *
	 * build the columns, observe the edit links on all side box divs,
	 * remove the delete icons from side box divs and replace
	 * the module links with a plain text title
	 *
	 * @return: n/a
	 */
	function init() {
		buildColumns();

		// observe the edit links on side boxes
		$$("a[id^='edit_sidebox_']").invoke('observe', 'click', edit);

		// remove the delete icon as we have the trash column
		$$('.del_icon').each(function(e) {
			e.remove();
		});

		// replace all the links with their titles
		$$('.add_box_link').each(function(e) {
			e.replace(e.innerHTML);
		});
	}

	/* columns */

	/**
	 * buildColumns()
	 *
	 * build the columns as both sortable and droppable where applicable
	 *
	 * @return: n/a
	 */
	function buildColumns() {
		// set up our columns
		for (var i = 0; i < columns.length; i++) {
			var column = columns[i];
			buildSortable(column);
			if (column != 'trash_column') {
				buildDroppable(column);
			}
		}
	}

	/**
	 * buildSortable()
	 *
	 * instantiate a sortable div
	 *
	 * @param - id - (string) the sortable column id
	 * @return: n/a
	 */
	function buildSortable(id) {
		Sortable.create(id, {
			tag: 'div',
			dropOnEmpty: true,
			containment: columns,
			only: 'sidebox',
			onUpdate: function(dragged, dropped, event) {
				// when the order changes use AJAX to store the affected side boxes
				new Ajax.Request('index.php', {
					method: "post",
					parameters: {
						module: 'config-asb',
						action: 'xmlhttp',
						mode: 'order',
						pos: id,
						data: Sortable.serialize(id)
					},
					onSuccess: function(response) {
						removeDivs(response.responseText.split(','));
					}
				});
			}
		});
	}

	/**
	 * buildDroppable()
	 *
	 * instantiate a droppable div
	 *
	 * @param - id - (string) the droppable column id
	 * @return: n/a
	 */
	function buildDroppable(id) {
		Droppables.add(id, {
			accept: 'draggable',
			hoverclass: 'hover',
			onDrop: create
		});
	}

	/* side boxes */

	/**
	 * edit()
	 *
	 * open a modal dialog to edit the chosen side box
	 *
	 * @param - event - (Event) the click event object
	 * @return: n/a
	 */
	function edit(event) {
		// stop the link from redirecting the user-- set up this way so that if JS is disabled the user goes to a standard form rather than a modal edit form
		Event.stop(event);

		// create the modal edit box dialog
		new a.Modal({
			type: 'ajax',
			url: this.readAttribute('href') + '&ajax=1'
		});
	}

	/**
	 * create()
	 *
	 * open a modal dialog to create a new side box
	 *
	 * @param - dragged - (element) the module div
	 * @param - dropped - (element) the column div
	 * @return: n/a
	 */
	function create(dragged, dropped) {
		// sort by position
		var pos = 1;
		if (dropped.id == 'left_column') {
			pos = 0;
		}

		// create the dialog
		new a.Modal({
			type: 'ajax',
			url: 'index.php?module=config-asb&action=edit_box&ajax=1&box=0&addon=' + dragged.id + '&pos=' + pos
		});
	}

	/* side box divs */

	/**
	 * createDiv()
	 *
	 * create a simple side box div to be completed by updateDiv()
	 *
	 * @param - id - (int) the side box's database id
	 * @param - title - (string) the side box's title
	 * @param - columnId - (string) the id attribute of the column
	 * @return: n/a
	 */
	function createDiv(id, title, columnId) {
		if (!$(columnId)) {
			return;
		}

		$(columnId).insert(new Element('div', {
			id: 'sidebox_' + id,
			class: 'sidebox'
		}).update(title));

		buildColumns();
	}

	/**
	 * updateDiv()
	 *
	 * update a side box's div with new info
	 *
	 * @param - id - (int) the side box's database id
	 * @return: n/a
	 */
	function updateDiv(id) {
		var sideboxId = 'sidebox_' + id;
		if (!$(sideboxId)) {
			return;
		}

		new Ajax.Updater(sideboxId, 'index.php', {
			method: 'get',
			parameters: {
				module: 'config-asb',
				action: 'xmlhttp',
				mode: 'build_info',
				id: id
			},
			evalScripts: true
		});
	}

	/**
	 * removeDivs()
	 *
	 * delete one or more side box div elements by id
	 *
	 * @param - ids - (Array) an array of integer ids
	 * @return: n/a
	 */
	function removeDivs(ids) {
		if (ids.length == 0) {
			return;
		}

		var i, sideboxId, sidebox;
		for (i = 0; i < ids.length; i++) {
			sideboxId = 'sidebox_' + ids[i];
			if (!$(sideboxId)) {
				continue;
			}

			// change the text and fade the <div> out
			sidebox = $(sideboxId);
			sidebox.style.backgroundColor = '#f00';
			sidebox.innerHTML = lang.deleting_sidebox;
			sidebox.fade({ duration: .8 });
		}
	}

	Event.observe(window, 'load', init);

	a.sidebox = {
		createDiv: createDiv,
		updateDiv: updateDiv,
	};

	return a;
})(ASB || {});
