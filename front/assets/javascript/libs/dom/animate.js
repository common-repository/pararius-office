
/* global mmx_define */

// percentages/pixels only, for now
mmx_define('dom.animate', ['dom.style', 'dom.easings'], function(style, easings) {
	'use strict';

	var tweens = {};
	var tweenId = 0;

	function _autoIsDefault(property) {
		return !!{ top: true, right: true, bottom: true, left: true }[property];
	}

	function Tween(element, property, value, callback) {
		this.stopped = false;
		this.callbackExecuted = false;

		this.element = element;
		this.property = property;
		this.value = parseInt(value, 10);
		this.percentages = /%$/.test(value);
		this.callback = callback;
		this.duration = 700;
		this.currentValue = this.element.css(property);

		if (this.currentValue === null || typeof this.currentValue === 'undefined') {
			this.currentValue = style.defaultStyle(property);
		} else if (/^-?\d+\.\d+(px|%)?$/.test(this.currentValue)) {
			this.currentValue = parseFloat(this.currentValue);
		} else if (/^-?\d+(px|%)?$/.test(this.currentValue)) {
			this.currentValue = parseInt(this.currentValue, 10);
		}

		if (_autoIsDefault(property) && this.currentValue === 'auto') {
			this.currentValue = 0;
		}

		this.diff = this.value - this.currentValue;

		this.startTime = new Date().getTime();
		this.endTime = new Date(+this.startTime + this.duration).getTime();
	}

	Tween.prototype.update = function(currentTime) {
		var remainingTime = Math.max(0, this.endTime - currentTime);
		var tmp = remainingTime / this.duration || 0;
		var percent = easings.easeInOutCubic(1 - tmp);
		var newValue = this.currentValue + percent * this.diff;

		if (this.percentages) {
			this.element.css(this.property, newValue + '%');
		} else {
			this.element.css(this.property, newValue);
		}

		return percent < 1;
	};

	Tween.prototype.done = function() {
		// `call` is causing weird issues, missing parentNode in some cases
		if (!this.callbackExecuted && this.callback) {
			this.callbackExecuted = true;
			this.callback();
		}
	};

	Tween.prototype.stop = function() {
		this.stopped = true;
		this.done();
	};

	var ticking = false;
	function tick() {
		var currentTime = new Date().getTime();
		var keepRunning = false;
		var tweenResult;

		var id = setTimeout(tick, 16);

		for (var i in tweens) {
			tweenResult = !tweens[i].stopped && tweens[i].update(currentTime);

			if (tweenResult) {
				keepRunning = true;
			} else {
				if (!tweens[i].stopped) {
					tweens[i].done();
				}

				delete tweens[i];
			}
		}

		if (!keepRunning) {
			// window.cancelAnimationFrame(id);
			clearTimeout(id);
			ticking = false;
			tweenId = 0;
		}
	}

	function startTicking() {
		if (!ticking) {
			ticking = true;
			// window.requestAnimationFrame(tick);
			setTimeout(tick, 16);
		}
	}

	return function animate(element, property, value, callback) {
		for (var i in tweens) {
			// stop current tweens for same properties
			if (tweens[i].element.raw() === element.raw() && tweens[i].property === property) {
				tweens[i].stop();
			}
		}

		var tween = tweens[tweenId] = new Tween(element, property, value, callback);

		startTicking();

		tweenId++;

		return tween;
	};
});
