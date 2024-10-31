
/* global mmx_define */

mmx_define('settings', ['dom'], function(dom) {
	'use strict';

	function Settings() {
		this._handlers = {};
	}

	Settings.prototype.on = function(eventName, cb) {
		if (!this._handlers[eventName]) {
			this._handlers[eventName] = [];
		}

		this._handlers[eventName].push(cb);

		return this;
	};

	Settings.prototype.trigger = function(eventName, args) {
		if (this._handlers[eventName]) {
			if (!args) {
				args = [];
			}

			for (var i = 0, l = this._handlers[eventName].length; i < l; i++) {
				this._handlers[eventName][i].apply(null, args);
			}
		}

		return this;
	};

	var settings = new Settings();
	var breakpoints = {
		mobile: 700,
	};

	function mobile() {
		return dom.window.width() <= breakpoints.mobile;
	}

	settings.mobile = mobile();
	settings.ie8 = dom.findOne('html').hasClass('ie8');

	function handler() {
		var oldValue = settings.mobile;
		var newValue = mobile();

		if (oldValue !== newValue) {
			settings.mobile = mobile();
			settings.trigger('mobile-changed', [newValue]);
		}
	}

	var timeout;
	dom.window.on('resize orientationchange', function() {
		clearTimeout(timeout);
		timeout = setTimeout(handler, 50);
	});

	return settings;
});
