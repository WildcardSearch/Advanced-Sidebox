/*
 * Plugin Name: Advanced Sidebox for MyBB 1.6.x
 * Copyright 2014 WildcardSearch
 * http://www.rantcentralforums.com
 *
 * this file contains a module for the Slideshow box addon as well as
 * some helpers (Timers/Effects)
 */

var ASB = (function(a) {

	/**
	 * Effect
	 *
	 * an object containing element effects processing functions
	 */
	var Effect = (function() {

		/**
		 * Timer
		 *
		 * an object containing timeframe handling for effects
		 */
		var Timer = (function() {

			/**
			 * Timer()
			 *
			 * the timer object's constructor
			 *
			 * @param - effect - (Object) the original effect
			 * @param - options - (Object) effects options
			 * @return void
			 */
			function Timer(effect, options) {
				this.options = options;

				// make sure we have work to do
				if (this.options.from == this.options.to) {
					return;
				}

				// calculate timing
				this.totalFrames = this.options.duration * this.options.fps;
				this.interval = (1 / this.options.fps) * 1000;
				this.increment = (this.options.to - this.options.from) / (this.totalFrames - 1);

				// the original effect object
				this.effect = effect;

				// a little more setup
				this.running = true;
				this.currentFrame = 0;
				this.value = this.options.from;

				// begin the timing
				this.run();
				Event.observe(window, 'unload', this.stop.bindAsEventListener(this));
			}

			/*
			 * run()
			 *
			 * sets timer to go another round
			 *
			 * @return void
			 */
			function run() {
				this.timeOutId = window.setTimeout(this.update.bind(this), this.interval);
			}

			/**
			 * stop()
			 *
			 * end the timer
			 *
			 * @return - void
			 */
			function stop() {
				window.clearTimeout(this.timeOutId);
			}

			/*
			 * update()
			 *
			 * increment the internal value, run the effect hook and either
			 * continue or end the cycle
			 *
			 * @return - void
			 */
			function update() {
				this.currentFrame++;
				this.value += this.increment;
				this.effect.runHook('update', [this.value]);

				if (this.currentFrame < this.totalFrames) {
					this.run();
				} else {
					this.running = false;
					this.value = this.options.to;
					this.effect.runHook('update', [this.value]);
					this.effect.runHook('finish');
				}
			}

			Timer.prototype = {
				run: run,
				stop: stop,
				update: update,
			};

			return Timer;
		})(),

		/**
		 * Base
		 *
		 * an object containing basic effect functionality and a bridge to the timer
		 */
		Base = (function() {
			var baseOptions = {
				to: 0,
				duration: 1,
				fps: 66,
			};

			/**
			 * initialize()
			 *
			 * format options, attach to element, run effect hooks and the timer
			 *
			 * @param - element - (String) the id of the element to affect
			 * @param - options - (Object) settings for the effect
			 * @return - void
			 */
			function initialize(element, options) {
				if (!element || !$(element)) {
					return;
				}

				this.element = $(element);
				this.options = Object.extend(Object.extend({}, baseOptions), options || {});

				this.runHook('setup');
				this.timer = new Timer(this, this.options);
			}

			/**
			 * runHook()
			 *
			 * @param - hook - (String) the effect function to call
			 * @param - args - (Array) the arguments to pass to the function
			 * @return - void
			 */
			function runHook(hook, args) {
				if (typeof this[hook] === 'function') {
					this[hook].apply(this, args || []);
				}
			}

			/**
			 * isRunning()
			 *
			 * a shortcut to determining if the Timer object is currently engaged
			 *
			 * @return - (Boolean) true if running, false if not
			 */
			function isRunning() {
				return this.timer.running;
			}

			/**
			 * forceRender()
			 *
			 * borrowed from the scriptaculous library, this helps to force the
			 * browser to render the element at each interval
			 *
			 * @return - void
			 */
			function forceRender() {
				try {
					var n = document.createTextNode(' ');
					this.element.appendChild(n);
					this.element.removeChild(n);
				} catch(e) {}
			}

			// this object will be used to create any effects
			return {
				baseOptions: baseOptions,
				init: initialize,
				runHook: runHook,
				isRunning: isRunning,
				forceRender: forceRender,
			};
		})(),

		/**
		 * Fade
		 *
		 * an effect that changes opacity from one value to another over
		 * a range of time
		 */
		Fade = (function(b) {
			/**
			 * Fade()
			 *
			 * the constructor-- simply calls Base.init()
			 *
			 * @param - element - (String) the id of the element to affect
			 * @param - options - (Object) settings for the effect
			 * @return - void
			 */
			function Fade(element, options) {
				this.init(element, options);
			}

			/**
			 * setup()
			 *
			 * called just after options have been checked and just before
			 * the timer is initiated
			 *
			 * @return - void
			 */
			function setup() {
				if (this.options.from == undefined) {
					this.options.from = this.element.getOpacity();
				}
				this.element.setOpacity(this.options.from).show();
			}

			/**
			 * update()
			 *
			 * called each time the timer interval runs out
			 *
			 * @param - value - (mixed) the current value at this frame
			 * @return - void
			 */
			function update(value) {
				this.element.setOpacity(value);
			}

			/**
			 * finish()
			 *
			 * called when the effect is complete
			 *
			 * @return - void
			 */
			function finish() {
				if (this.options.to == 0) {
					this.element.hide().setOpacity(this.options.from);
				}
				this.forceRender();
			}

			Fade.prototype = Object.extend({
				setup: setup,
				update: update,
				finish: finish,
			}, b);

			return Fade;
		})(Base);

		return {
			Timer: Timer,
			Base: Base,
			Fade: Fade,
		};
	})();

	/**
	 * Slideshow()
	 *
	 * constructor for slideshow objects-- commandeers an element and cycles
	 * through a defined set of images using configurable options
	 *
	 * @param - container - (String) the id of the containing <div>
	 * @param - options - (Object) settings for the object
	 * @return - void
	 */
	function Slideshow(container, options) {
		if (!$(container)) {
			return;
		}

		this.options = {
			rate: 10,
			shuffle: false,
			fadeRate: 1,
			size: 100,
		};
		Object.extend(this.options, options || {});

		// set up the container
		this.container = $(container);
		this.container.setStyle({
			width: this.options.size + 'px',
			height: this.options.size + 'px',
			marginLeft: 'auto',
			marginRight: 'auto',
			position: 'relative',
		});

		// no images, no have slideshow
		if (!this.options.images || this.options.images.length == 0) {
			return;
		}

		this.current = 0;
		if (this.options.shuffle) {
			this.options.images.sort(function() {
				return 0.5 - Math.random();
			});
		}

		// create the main image holder, set it up and insert it into the container
		this.mainImage = new Element('img', {
			src: this.getCurrentImage(),
		}).setStyle({
			display: 'none',
			position: 'absolute',
			width: this.options.size + 'px',
			left: '0px',
			top: '0px',
		});
		this.container.insert(this.mainImage);

		// clone the main image, store it as a buffer and insert it into the container
		this.bufferImage = this.mainImage.clone();
		this.container.insert(this.bufferImage);

		this.cloneWidth = this.cloneHeight = 0;

		/**
		 * get things going and begin cycling when the page loads
		 * and end when the user leaves
		 */
		this.showCurrent();
		Event.observe(window, 'load', this.run.bindAsEventListener(this));
		Event.observe(window, 'unload', this.stop.bindAsEventListener(this));
	}

	/**
	 * run()
	 *
	 * ready the slideshow to go another round
	 *
	 * @return - void
	 */
	function run() {
		this.timeOutId = window.setTimeout(this.showNext.bind(this), this.options.rate * 1000);
	}

	/**
	 * stop()
	 *
	 * end the slideshow
	 *
	 * @return - void
	 */
	function stop() {
		window.clearTimeout(this.timeOutId);
	}

	/**
	 * getCurrentImage()
	 *
	 * build the image file name
	 *
	 * @return - (String)
	 */
	function getCurrentImage() {
		return this.options.folder ?
				this.options.folder + '/' + this.options.images[this.current] :
				this.options.images[this.current];
	}

	/**
	 * nextImage()
	 *
	 * do the buffer swap and cycle to the next image
	 *
	 * @return - void
	 */
	function nextImage() {
		this.bufferImage.src = this.mainImage.src;
		this.resizeImage(this.bufferImage).show();
		this.mainImage.hide();

		this.current++;
		if (this.options.images.length <= this.current) {
			this.current = 0;
		}
	}

	/**
	 * showCurrent()
	 *
	 * load the current image and perform the transition
	 *
	 * @return - void
	 */
	function showCurrent() {
		this.mainImage.src = this.getCurrentImage();

		/*
		 * clone the main image and display it off-screen in order to
		 * get the correct size
		 */
		this.clone = new Element('img', {
			src: this.mainImage.src,
		}).setStyle({
			position: 'absolute',
			top: '-9999px',
			display: 'block',
		});
		$$('body')[0].insert(this.clone);
		this.clone.observe('load', this.resize.bindAsEventListener(this));

		// fade in the new image
		new Effect.Fade(this.mainImage, {
			from: 0,
			to: 1,
			duration: this.options.fadeRate,
		});

		// if we have already initialized, fade out the old image
		if (this.running) {
			new Effect.Fade(this.bufferImage, {
				from: 1,
				to: 0,
				duration: this.options.fadeRate,
			});
		}
		this.running = true;
	}

	/**
	 * resize()
	 *
	 * when the clone loads, get its dimensions and use them to resize the
	 * main image
	 *
	 * @return - void
	 */
	function resize(e) {
		var height = this.clone.getHeight(),
		width = this.clone.getWidth(),
		ratio;

		// maintain the ratio
		if (height > width) {
			ratio = height / width;
			this.cloneWidth = parseInt(this.options.size / ratio);
			this.cloneHeight = this.options.size;
		} else {
			ratio = width / height;
			this.cloneWidth = this.options.size;
			this.cloneHeight = parseInt(this.options.size / ratio);
		}

		this.clone.remove();
		this.resizeImage(this.mainImage);
	}

	/**
	 * resizeImage()
	 *
	 * apply the stored dimensions to a given element
	 *
	 * @param - el - (Object) the DOM Element Object
	 * @return - (Object) the DOM Element Object
	 */
	function resizeImage(el) {
		el.setStyle({
			height: this.cloneHeight + 'px',
			width: this.cloneWidth + 'px',
			left: parseInt((this.options.size / 2) - (this.cloneWidth / 2)) + 'px',
			top: parseInt((this.options.size / 2) - (this.cloneHeight / 2)) + 'px',
		});
		return el;
	}

	/**
	 * showNext()
	 *
	 * called cyclically to advance to the next image and restart the timer
	 *
	 * @param - el - (Object) the DOM Element Object
	 * @return - (Object) the DOM Element Object
	 */
	function showNext() {
		if (this.container.offsetWidth > 0)  {
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

	a.modules = Object.extend({
		Slideshow: Slideshow,
	}, a.modules || {});

	return a;
})(ASB || {});
