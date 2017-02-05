/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains a module for the Slideshow box addon as well as
 * some helpers (Timers/Effects)
 */

var ASB = (function(a, $) {
	/**
	 * constructor for slideshow objects-- commandeers an element and cycles
	 * through a defined set of images using configurable options
	 *
	 * @param String the id of the containing <div>
	 * @param Object settings for the object
	 * @return void
	 */
	function Slideshow(container, options) {
		if (!$("#" + container).length) {
			return;
		}

		this.options = {
			rate: 10,
			shuffle: false,
			fadeRate: 400,
			size: 100,
			maxWidth: 0,
			maxHeight: 0,
			maintainHeight: 1,
		};
		$.extend(this.options, options || {});

		this.startHeight = this.options.size;
		if (this.options.maxHeight > 0 &&
			this.options.maintainHeight) {
			this.startHeight = this.options.maxHeight;
		}

		// set up the container
		this.container = $("#" + container);
		this.container.css({
			width: this.options.size + 'px',
			height: this.startHeight + 'px',
			marginLeft: 'auto',
			marginRight: 'auto',
			position: 'relative',
		});

		// no images, no have slide show
		if (typeof this.options.images === "undefined" ||
			this.options.images.length == 0) {
			return;
		}

		this.current = 0;
		if (this.options.shuffle) {
			this.options.images.sort(function() {
				return 0.5 - Math.random();
			});
		}

		// create the main image holder, set it up and insert it into the container
		this.mainImage = $('<img/>', {
			src: this.getCurrentImage(),
		}).css({
			display: 'none',
			position: 'absolute',
			left: '0px',
			top: '0px',
		});
		this.container.append(this.mainImage);

		// clone the main image, store it as a buffer and insert it into the container
		this.bufferImage = this.mainImage.clone();
		this.container.append(this.bufferImage);

		this.cloneWidth = this.cloneHeight = 0;

		/**
		 * get things going and begin cycling when the page loads
		 * and end when the user leaves
		 */
		this.showCurrent();
		$($.proxy(this.run, this));
		$(window).unload($.proxy(this.stop, this));
	}

	/**
	 * ready the slideshow to go another round
	 *
	 * @return void
	 */
	function run() {
		this.timeOutId = setTimeout($.proxy(this.showNext, this), this.options.rate * 1000);
	}

	/**
	 * end the slideshow
	 *
	 * @return void
	 */
	function stop() {
		clearTimeout(this.timeOutId);
	}

	/**
	 * build the image file name
	 *
	 * @return string
	 */
	function getCurrentImage() {
		return this.options.folder ?
				this.options.folder + '/' + this.options.images[this.current] :
				this.options.images[this.current];
	}

	/**
	 * do the buffer swap and cycle to the next image
	 *
	 * @return void
	 */
	function nextImage() {
		this.bufferImage.prop("title", this.mainImage.prop("title"));
		this.bufferImage.prop("alt", this.mainImage.prop("alt"));
		this.bufferImage.prop("src", this.mainImage.prop("src"));
		this.resizeImage(this.bufferImage).show();
		this.mainImage.hide();

		this.current++;
		if (this.options.images.length <= this.current) {
			this.current = 0;
		}
	}

	/**
	 * load the current image and perform the transition
	 *
	 * @return void
	 */
	function showCurrent() {
		this.mainImage.prop("title", this.options.images[this.current]);
		this.mainImage.prop("alt", this.options.images[this.current]);
		this.mainImage.prop("src", this.getCurrentImage());

		/*
		 * clone the main image and display it off-screen in order to
		 * get the correct size
		 */
		this.clone = $('<img/>', {
			src: this.mainImage.prop("src"),
		}).css({
			position: 'absolute',
			display: 'block',
		});
		$('body').append(this.clone);

		this.clone.load($.proxy(this.resize, this)).each(function() {
			if (this.complete) {
				$(this).load();
			}
		});

		// fade in the new image
		$(this.mainImage).fadeIn(this.options.fadeRate);

		// if we have already initialized, fade out the old image
		if (this.running) {
			$(this.bufferImage).fadeOut(this.options.fadeRate);
		}
		this.running = true;
	}

	/**
	 * when the clone loads, get its dimensions and use them to resize the
	 * main image
	 *
	 * @return - void
	 */
	function resize(e) {
		var height = this.clone.height(),
		width = this.clone.width(),
		ratio;

		// maintain the ratio and resize if necessary
		if (height > width) {
			ratio = height / width;

			this.cloneWidth = parseInt(this.options.size / ratio);
			this.cloneHeight = this.options.size;

			if (this.options.maxWidth > 0 &&
				this.cloneWidth > this.options.maxWidth) {
				this.cloneWidth = this.options.maxWidth;
				this.cloneHeight = parseInt(this.options.maxWidth * ratio);
			}

			if (this.options.maxHeight > 0 &&
				this.cloneHeight > this.options.maxHeight) {
				this.cloneWidth = parseInt(this.options.maxHeight / ratio);
				this.cloneHeight = this.options.maxHeight;
			}
		} else if (width > height) {
			ratio = width / height;

			this.cloneWidth = this.options.size;
			this.cloneHeight = parseInt(this.options.size / ratio);

			if (this.options.maxWidth > 0 &&
				this.cloneWidth > this.options.maxWidth) {
				this.cloneWidth = this.options.maxWidth;
				this.cloneHeight = parseInt(this.options.maxWidth / ratio);
			}

			if (this.options.maxHeight > 0 &&
				this.cloneHeight > this.options.maxHeight) {
				this.cloneWidth = parseInt(this.options.maxHeight * ratio);
				this.cloneHeight = this.options.maxHeight;
			}
		} else {
			this.cloneHeight = this.cloneWidth = this.options.size;

			if (this.options.maxWidth > 0 &&
				this.cloneWidth > this.options.maxWidth) {
				this.cloneHeight = this.cloneWidth = this.options.maxWidth;
			}

			if (this.options.maxHeight > 0 &&
				this.cloneHeight > this.options.maxHeight) {
				this.cloneWidth = this.cloneHeight = this.options.maxHeight;
			}
		}

		this.clone.remove();
		this.resizeImage(this.mainImage);
	}

	/**
	 * apply the stored dimensions to a given element
	 *
	 * @param Object the DOM Element Object
	 * @return Object the DOM Element Object
	 */
	function resizeImage(el) {
		style = {
			height: this.cloneHeight + 'px',
			width: this.cloneWidth + 'px',
			left: parseInt((this.options.size / 2) - (this.cloneWidth / 2)) + "px",
			top: parseInt((this.startHeight / 2) - (this.cloneHeight / 2)) + "px",
		};

		if (!this.options.maintainHeight) {
			this.container.css("height", this.cloneHeight + "px");
			style.top = "0px";
		}

		el.css(style);

		return el;
	}

	/**
	 * called cyclically to advance to the next image and restart the timer
	 *
	 * @param Object the DOM Element Object
	 * @return Object the DOM Element Object
	 */
	function showNext() {
		if (this.container.width() > 0)  {
			this.nextImage();
			this.showCurrent();
		}
		this.run();
	}

	Slideshow.prototype = {
		run: run,
		stop: stop,
		showCurrent: showCurrent,
		getCurrentImage: getCurrentImage,
		nextImage: nextImage,
		showNext: showNext,
		resize: resize,
		resizeImage: resizeImage,
	};

	a.modules = $.extend({
		Slideshow: Slideshow,
	}, a.modules || {});

	return a;
})(ASB || {}, jQuery);
