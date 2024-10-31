
/* global mmx_define */

mmx_define('dom.style', [], function() {
	'use strict';

	var numbers = {
		lumnCount: true,
		fillOpacity: true,
		flexGrow: true,
		flexShrink: true,
		fontWeight: true,
		lineHeight: true,
		opacity: true,
		order: true,
		orphans: true,
		widows: true,
		zIndex: true,
		zoom: true,
	};

	var _elements = {};
	function _getElement(tagName) {
		if (!_elements[tagName]) {
			_elements[tagName] = document.createElement(tagName);
		}

		return _elements[tagName];
	}

	var support = (function() {
		var el = _getElement('div');
		el.style.cssText = 'float:left;opacity:.5';

		return {
			opacity: el.style.opacity === '0.5',
			cssFloat: !!el.style.cssFloat,
		};
	}());

	var rDashAlpha = /-([\da-z])/gi;

	function _camelize(property) {
		return String(property).replace(rDashAlpha, function(all, character) {
			return character.toUpperCase();
		}).replace(/^float$/, support.cssFloat ? 'cssFloat' : 'styleFloat');
	}

	function _normalizeValue(px) {
		if (/^-?\d+(px)?$/.test(px)) {
			return parseInt(px, 10);
		} else if (/^-?\d+\.\d+$/.test(px)) {
			return parseFloat(px);
		} else {
			return px;
		}
	}

	function _getStyle(element, property) {
		var result = null;
		var style = element.style[_camelize(property)];
		var computedStyle;

		if (style) {
			result = style;
		} else {
			computedStyle = window.getComputedStyle(element)[property];

			if (computedStyle) {
				result = computedStyle;
			}
		}

		return _normalizeValue(result);
	}

	function _setOpacity(element, value) {
		var style = element.raw().style;
		style.zoom = 1;
		style.filter = 'alpha(opacity=' + (parseFloat(value) * 100) + ')';
	}

	return {
		set: function(element, property, value) {
			var camelizedProperty = _camelize(property);

			if (!numbers[camelizedProperty] && typeof value === 'number') {
				value += 'px'; // implicit string cast
			} else {
				value = String(value);
			}

			if (property === 'opacity' && !support.opacity) {
				_setOpacity(element, value);
			} else {
				element.raw().style[camelizedProperty] = value;
			}
		},
		get: function(element, property) {
			return _getStyle(element.raw(), property);
		},
		defaultStyle: function(tagName, property) {
			return _getStyle(_getElement(tagName), property);
		},
	};
});
