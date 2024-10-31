
/* global mmx_define */

mmx_define('magnify', ['dom', 'overlay', 'slider'], function(dom, Overlay, Slider) {
	'use strict';

	var ESCAPE = 27;
	var LEFT = 37;
	var RIGHT = 39;

	var overlay = new Overlay();

	function keyHandler(code, fn) {
		return function (ev) {
			if (ev.keyCode == code) {
				fn();
				return false;
			}
		};
	}

	// function Single(api, image) {
	// 	var magnified = new Magnified(image);
	//
	// 	magnified.open();
	// 	magnified.image.on('click', api.close);
	//
	// 	this.close = function Single$close() {
	// 		magnified.close();
	// 	};
	// }

	function Multiple(api, images, index) {
		var sliderEl = dom.create('ul', { className: 'slider' });

		images.each(function() {
			var li = dom.create('li');
			dom.create('img', { src: this.attr('src') || this.attr('href') }).appendTo(li);

			sliderEl.append(li);
		});

		sliderEl.css('opacity', '0');
		sliderEl.appendTo(overlay.getEl());

		var slider = new Slider(sliderEl, {
			itemsPerPage: 1,
			startItem: index,
			buttonsHolder: overlay.getEl(),
		});

		overlay.open();
		sliderEl.animate('opacity', '1');

		api.prev = function() { slider.prev(); };
		api.next = function() { slider.next(); };

		var left = keyHandler(LEFT, api.prev);
		var right = keyHandler(RIGHT, api.next);
		dom.window.on('keydown', left);
		dom.window.on('keydown', right);

		this.close = function Multiple$close() {
			overlay.close();

			sliderEl.animate('opacity', 0, function() {
				slider.tearDown();
				sliderEl.remove();
			});

			dom.window.off('keydown', left);
			dom.window.off('keydown', right);
		};
	}

	return {
		magnify: function(image, index) {
			var instance;
			var escape;
			var api = {
				close: function() {
					overlay.getEl().off('click', api.close);
					dom.window.off('keydown', escape);
					instance.close();
				},
			};
			escape = keyHandler(ESCAPE, api.close);

			instance = new Multiple(api, image, index || 0);

			overlay.getEl().on('click', api.close);
			dom.window.on('keydown', escape);

			return api;
		},
	};
});
