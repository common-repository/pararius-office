
/* global mmx_define */

mmx_define('overlay', ['settings', 'dom'], function(settings, dom) {
	'use strict';

	function Overlay() {
		var that = this;

		this.opening = false;
		this.closing = false;

		this.overlay = dom.create('div').css({
			opacity: 0,
			width: '100%',
			height: '100%',
			position: 'fixed',
			top: 0,
			left: 0,
			cursor: 'pointer',
			background: settings.ie8 ? '#000' : 'rgba(0, 0, 0, 0.6)',
		}).on('click', function(ev) {
			if (that.overlay.raw() === ev.target) {
				that.overlay.fire('close');
				that.close();
			}
		});
	}

	Overlay.prototype.open = function() {
		var that = this;

		if (!this.opening) {
			this.opening = true;

			dom.tag('body').append(this.overlay);
			this.overlay.css('z-index', 30).animate('opacity', 1, function() {
				that.opening = false;
			});
		}

		return this;
	};

	Overlay.prototype.close = function() {
		var that = this;

		if (!this.closing) {
			this.closing = true;

			this.overlay.animate('opacity', 0, function() {
				that.closing = false;
				that.overlay.remove();
			});
		}

		return this;
	};

	Overlay.prototype.getEl = function() {
		dom.tag('body').append(this.overlay); // make sure the element is in the DOM

		return this.overlay;
	};

	return Overlay;
});
