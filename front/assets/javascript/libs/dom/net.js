
/* global mmx_define */

mmx_define('dom.net', ['dom', 'dom.utils'], function(dom, utils) {
	'use strict';

	var net = {};

	net.script = function(url, id) {
		var head = dom.tag('head');
		var script = dom.create('script').raw();
		var callback;
		var api = {
			done: function(cb) {
				callback = cb;
			},
		};

		script.async = true;
		script.src = url;

		if (id) {
			script.id = id;
		}

		// from jQuery
		script.onload = script.onreadystatechange = function( _, isAbort) {
			if (isAbort || !script.readyState || /loaded|complete/.test( script.readyState)) {
				script.onload = script.onreadystatechange = null;

				if (script.parentNode) {
					script.parentNode.removeChild(script);
				}

				script = null;

				if (!isAbort && callback) {
					callback();
				}
			}
		};

		head.raw().insertBefore(script, head.raw().firstChild);

		return api;
	};

	function _dataToQueryString(data) {
		var query = [];

		utils.each(data, function(key, value) {
			query.push(encodeURIComponent(key) + '=' + encodeURIComponent(value));
		});

		return query.join('&');
	}

	function _request(url, options, callback) {
		var xhr = new XMLHttpRequest();

		xhr.onreadystatechange = function() {
			try {
				if (xhr.readyState === 4) {
					if (xhr.status === 200) {
						callback(null, xhr.responseText);
					} else {
						callback(xhr.status);
					}
				}
			} catch (e) {
				callback(e);
			}
		};

		xhr.open(options.method, url, true);

		if (options.method === 'POST') {
			xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		}

		xhr.setRequestHeader('Cache-Control', 'no-cache');
		xhr.send(options.data || null);

		return {};
	}

	net.get = function(url, callback) {
		return _request(url, { method: 'GET' }, callback);
	};

	net.post = function(url, data, callback) {
		return _request(url, { method: 'POST', data: _dataToQueryString(data) }, callback);
	};

	dom.net = net;
	return net;
});
