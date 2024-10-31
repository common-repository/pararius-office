
/* global mmx_define */

mmx_define('popup', ['dom', 'overlay'], function(dom, Overlay) {
	'use strict';

	var undef;

	function Popup() {
		this.overlay = new Overlay();
		this.wrapper = dom.create('div', { className: 'popup' }).appendTo(this.overlay.getEl());
		this.content = dom.create('div', { className: 'content' }).appendTo(this.wrapper);
		this.isLoading = false;
		this.isLoadingTimeout = undef;

		dom.create('div', { className: 'close' }).html('&times;').appendTo(this.wrapper).on('click', this.close.bind(this));
	}

	Popup.prototype.clear = function() {
		return this.html('');
	};

	Popup.prototype.loading = function(which) {
		this.isLoading = !!which;

		clearTimeout(this.isLoadingTimeout);
		this.isLoadingTimeout = setTimeout(function() {
			this.wrapper.toggleClass('is-loading', this.isLoading);
		}.bind(this), 50);

		return this;
	};

	Popup.prototype.open = function(html) {
		if (html) {
			this.html(html);
		}

		this.overlay.open();
		return this;
	};

	Popup.prototype.html = function(html) {
		this.content.html(html);
		return this;
	};

	Popup.prototype.getContent = function() {
		return this.content;
	};

	Popup.prototype.close = function() {
		this.overlay.close();
		return this;
	};

	return Popup;
});
