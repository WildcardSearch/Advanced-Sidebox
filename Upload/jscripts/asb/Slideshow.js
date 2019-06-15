/*
 * Plugin Name: Advanced Sidebox for MyBB 1.8.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains a module for the Slideshow box addon as well as
 * some helpers (Timers/Effects)
 */

var ASB = (function($, a) {
	"use strict";

	/**
	 * constructor
	 *
	 * cycles through a defined set of images
	 *
	 * @param String id
	 * @param Object options
	 * @return Void
	 */
	function Slideshow(el, o)
	{
		if (typeof el !== "string" ||
			el.length === 0) {
			return;
		}

		this.options = {
			rate: 10,
			height: 200,
			fadeRate: 400,
		};

		this.options = $.extend(this.options, o);

		this.$container = $("#"+el);

		if (this.$container.length < 1 ||
			typeof this.options.images !== "object" ||
			this.options.images.length < 1) {
			return false;
		}

		this.running = false;
		this.isSetUp = false;
		this.imageIndex = 0;

		this.$container.css({
			height: this.options.height+"px",
		});

		this.$image1 = this.$container.children("div.asb-slideshow-image-one");
		this.$image2 = this.$container.children("div.asb-slideshow-image-two").hide();

		this.start();
	}

	/**
	 * kicks everything off
	 *
	 * @return Void
	 */
	function start()
	{
		if (this.running) {
			return;
		}

		this.running = true;

		this.showNextImage();
	}

	/**
	 * shuts everything down
	 *
	 * @return Void
	 */
	function stop()
	{
		if (!this.running ||
			!this.timer) {
			return;
		}

		window.clearTimeout(this.timer);

		this.running = false;
	}

	/**
	 * initialize the slideshow
	 *
	 * @return Void
	 */
	function setup()
	{
		var url = this.getNextImageUrl();

		// set both image containers to the same URL
		setImageUrl(this.$image1, url);
		setImageUrl(this.$image2, url);

		this.isSetUp = true;

		window.setTimeout($.proxy(this.showNextImage, this), this.options.rate*1000);
	}

	/**
	 * swap the images
	 *
	 * @return Void
	 */
	function showNextImage()
	{
		if (this.isSetUp !== true) {
			this.setup();
			return;
		}

		var url = this.getNextImageUrl();

		// show the backup image (same as current image)
		this.$image2.show();

		// swap out the first image
		setImageUrl(this.$image1, url);

		// fade out the backup image (revealing the new image)
		this.$image2.fadeOut(this.options.fadeRate, $.proxy(function() {
			// and when that's done, set the backup image to the same image as the first image
			setImageUrl(this.$image2, url);

			// and do it again
			this.timer = window.setTimeout($.proxy(this.showNextImage, this), this.options.rate*1000);
		}, this));
	}

	/**
	 * get the next image in the set
	 *
	 * @return Void
	 */
	function getNextImageUrl()
	{
		var url;

		// get the next index or start over
		this.imageIndex++;
		if (this.imageIndex > this.options.images.length) {
			this.imageIndex = 0;
		}

		// build the url
		url = this.options.images[this.imageIndex];
		if (this.options.folder) {
			url = this.options.folder+"/"+url;
		}

		return url;
	}

	/**
	 * local static function to change element bg image
	 *
	 * @param jQuery el
	 * @param String
	 * @return Void
	 */
	function setImageUrl($i, url)
	{
		$i.css({
			"background-image": "url("+url+")",
		});
	}

	Slideshow.prototype = {
		start: start,
		stop: stop,
		setup: setup,

		showNextImage: showNextImage,
		getNextImageUrl: getNextImageUrl,
	};

	a.modules = $.extend({
		Slideshow: Slideshow,
	}, a.modules || {});

	return a;
})(jQuery, ASB || {});
