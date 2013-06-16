/*
 * This file contains JavaScript for the ACP functions
 *
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2013 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * Check out this project on GitHub: http://wildcardsearch.github.com/Advanced-Sidebox
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses
 */

// things to do at loading . . .
Event.observe
(
	window,
	'load',
	function()
	{
		// build our forum columns
		build_droppable('left_column');
		build_droppable('right_column');

		// observe the edit links on sideboxes
		$$("a[id^='edit_sidebox_']").invoke
		(
			'observe',
			'click',
			function(event)
			{
				// stop the link from redirecting the user-- set up this way so that if JS is disabled the user goes to a standard form rather than a modal edit form
				Event.stop(event);

				// create the modal edit box dialogue
				new MyModal
				(
					{
						type: 'ajax',
						url: this.readAttribute('href') + '&ajax=1'
					}
				);
			}
		);
	}
);

/*
 * function build_sortable(name)
 *
 * builds (or rebuilds as the case may be) the sidebox columns as sortables lists of <div>'s
 *
 * @param - name is the id property of the column class element
 */
function build_sortable(name)
{
	// create the object
	Sortable.create
	(
		name,
		{
			tag: 'div',
			dropOnEmpty:true,
			containment: columns,
			only: 'sidebox',
			onUpdate: function(dragged, dropped, event)
			{
				// when the order changes use AJAX to store the affected sideboxes
				new Ajax.Request
				(
					"index.php?module=config-adv_sidebox&action=xmlhttp&mode=order&pos=" + name,
					{
						method: "post",
						parameters:
						{
							// serialize the order of sideboxes in this column
							data: Sortable.serialize(name)
						},
						onSuccess: function(response)
						{
							// when we're done rebuild the sortable
							build_sortable(name);

							// any response means the admin dropped the side box into the trash can column
							if(response.responseText)
							{
								// the response is the id
								id = response.responseText;

								// change the text and fade the <div> out
								$('sidebox_' + id).style.backgroundColor = '#f00';
								$('sidebox_' + id).innerHTML = 'Deleting . . .';
								$('sidebox_' + id).fade
								(
									{
										duration: .8,
										afterFinish: function(event)
										{
											// when it has completely faded out remove it
											event.remove();
										}
									}
								);
							}
							else
							{
								// if this wasn't a deletion then this column needs to rebuilt as a droppable (trash column isn't droppable)
								build_droppable(name);
							}
						}
					}
				);
			}
		}
	);
}

/*
 * build_droppable()
 *
 * builds (or rebuilds) the droppable forum columns
 *
 * @param - name the id of the column class <div>
 */
function build_droppable(name)
{
	// rebuild the column as a droppable
	Droppables.add(name,
	{
		accept: 'draggable',
		hoverclass: 'hover',
		onDrop: function(dragged, dropped, event)
		{
			// and set up the creation handler
			do_drop(dragged, dropped, event, dropped.id);
		}
	});
}

/*
 * do_drop()
 *
 * handles the creation of a new side box from the browser side
 *
 * @param - dragged
 * @param - dropped
 * @param - event
 * @param - name
 */
function do_drop(dragged, dropped, event, name)
{
	// sort by position
	if(name == 'left_column')
	{
		var pos = 0;
	}
	else
	{
		var pos = 1;
	}

	// create the dialogue
	new MyModal
	(
		{
			type: 'ajax',
			url: 'index.php?module=config-adv_sidebox&action=edit_box&ajax=1&box=0&addon=' + dragged.id + '&pos=' + pos
		}
	);

	// and while they are selecting rebuild the column as both sortable and droppable
	build_sortable(name);
	build_droppable(name);
}
