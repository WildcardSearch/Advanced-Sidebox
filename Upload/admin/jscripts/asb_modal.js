/*
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * extend MyModal class to work around this Prototype error:
 * https://prototype.lighthouseapp.com/projects/8886/tickets/1384
 */

var ASB = (function(a) {
	/**
	 * submit()
	 *
	 * serialize and send the form data, replacing any multiple select
	 * elements with an array of hidden inputs to overcome a limitation
	 * of the Prototype JS library when serializing multiple
	 * select elements
	 *
	 * @param - e - (Event) the submit event object
	 * @return: n/a
	 */
	function submit(e) {
		var form, select, selects, option, options, newElement, s, o;

		Event.stop(e);
		this.showOverlayLoader();

		// get all the select elements on this form
		form = $(this.options.formId);
		selects = $$('#' + this.options.formId + ' select');

		for (s = 0; s < selects.length; s++) {
			select = selects[s];
			if (!select.multiple) {
				continue;
			}

			// get all the options in this select element
			options = select.childElements();
			for (o = 0; o < options.length; o++) {
				option = options[o];
				if (option.nodeName != 'OPTION' || !option.selected) {
					continue;
				}

				form.insert(new Element('input', {
					type: 'hidden',
					name: select.getAttribute('name'),
					value: option.value
				}));
			}

			// remove the select element once it is replaced
			select.remove();
		}

		// send the post data
		var postData = form.serialize();
		new Ajax.Request(this.options.url, {
            method: 'post',
            postBody: postData + '&ajax=1&time=' + new Date().getTime(),
            onComplete: this.onComplete.bind(this),
        });
	}

	a.Modal = Class.create(MyModal, {
		submit: submit,
	});

	return a;
})(ASB || {});
