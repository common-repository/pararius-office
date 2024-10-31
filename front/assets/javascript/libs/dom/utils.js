
/* global mmx_define */

mmx_define('dom.utils', [], function() {
	'use strict';

	var api = {};

	api.each = function each(collection, iterator) {
		for (var i in collection) {
			if (collection.hasOwnProperty(i)) {
				iterator(i, collection[i]);
			}
		}
	};

	api.invoke = function invoke(collection, method) {
		api.each(collection, function(_, item) {
			item[method]();
		});
	};

	api.trimSpaces = function trimSpaces(input) {
		return input.replace(/^ */, '').replace(/ *$/, '');
	};

	return api;
});
