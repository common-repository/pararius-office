
/* global mmx_define */
// reading:
// http://rabblerule.blogspot.nl/2009/08/detecting-swipe-in-webkit.html

mmx_define('dom', ['dom.utils', 'dom.style', 'dom.animate'], function(utils, style, animate) {
	'use strict';

	var d = document;

	function _tags(context, tag) {
		return new List(context.querySelectorAll(tag));
	}

	function _find(context, selector) {
		return new List(context.querySelectorAll(selector));
	}

	function _regexSafe(text) {
		return text.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, '\\$&');
	}

	function Elem(element) {
		this.element = element;
		this.animations = [];
	}

	Elem.prototype.raw = function() {
		return this.element;
	};

	Elem.prototype.attr = function(name, value) {
		if (name === 'className') {
			name = 'class';
		}

		if (typeof value === 'undefined') {
			return this.element.getAttribute(name);
		} else {
			this.element.setAttribute(name, value);
			return this;
		}
	};

	Elem.prototype.id = function(id) {
		return this.findOne('#' + id);
	};

	Elem.prototype.tags = function(tag) {
		return _tags(this.element, tag);
	};

	Elem.prototype.tag = function(tag) {
		return this.tags(tag).get(0);
	};

	var _eventHandlers = [];
	Elem.prototype.on = function(eventName, cb) {
		var eventNames = eventName.split(' ');

		var callback = function(ev) {
			var rs = cb.call(this, ev);

			if (rs === false) {
				ev.stopPropagation();
				ev.preventDefault();
			}
		}.bind(this);

		// TODO prevent leaking
		_eventHandlers.push({
			original: cb,
			domVersion: callback,
		});

		for (var i = 0, l = eventNames.length; i < l; i++) {
			this.element.addEventListener(eventNames[i], callback, false);
		}

		return this;
	};

	Elem.prototype.off = function(eventName, cb) {
		var eventNames = eventName.split(' ');
		var index;

		utils.each(_eventHandlers, function(k, v) {
			if (v.original === cb) {
				index = k;
			}
		});

		if (index) {
			for (var i = 0, l = eventNames.length; i < l; i++) {
				this.element.removeEventListener(eventNames[i], _eventHandlers[index], false);
			}
		}

		return this;
	};

	Elem.prototype.parent = function() {
		return new Elem(this.element.parentNode);
	};

	// TODO custom events
	Elem.prototype.fire = function(eventName) {
		if (!this.element[eventName] || typeof this.element[eventName] !== 'function') {
			// throw new Error('Invalid event name: ' + eventName);
			return this;
		}

		this.element[eventName]();

		return this;
	};

	Elem.prototype.clone = function() {
		return new Elem(this.element.cloneNode(true));
	};

	Elem.prototype.append = function(element) {
		this.element.appendChild(element.raw());
		return this;
	};

	Elem.prototype.appendTo = function(element) {
		element.append(this);
		return this;
	};

	// internet explorer doesn't know `textContent`
	var textContentProperty = !(Object.defineProperty && Object.getOwnPropertyDescriptor &&
		Object.getOwnPropertyDescriptor(Element.prototype, 'textContent') &&
		!Object.getOwnPropertyDescriptor(Element.prototype, 'textContent').get) ?
			'textContent' :
			'innerText';
	Elem.prototype.text = function(text) {
		if (typeof text === 'undefined') {
			return this.element[textContentProperty];
		} else if (typeof text === 'function') {
			this.element[textContentProperty] = text.call(this, this.text());
			return this;
		} else {
			this.element[textContentProperty] = text;
			return this;
		}
	};

	Elem.prototype.html = function(html) {
		if (typeof html === 'undefined') {
			return this.element.innerHTML;
		} else if (typeof html === 'function') {
			this.element.innerHTML = html.call(this, this.html());
			return this;
		} else {
			this.element.innerHTML = html;
			return this;
		}
	};

	Elem.prototype.find = function(selector) {
		return _find(this.element, selector);
	};

	Elem.prototype.findOne = function(selector) {
		return this.find(selector).get(0);
	};

	// might need some better calculation
	Elem.prototype.width = function() {
		return this.element.offsetWidth || this.element.innerWidth;
	};

	Elem.prototype.height = function() {
		return this.element.offsetHeight || this.element.innerHeight;
	};

	Elem.prototype.hasClass = function(className) {
		return this.element.className.match(new RegExp('( |^)' + _regexSafe(className) + '( |$)', 'g')) !== null;
	};

	Elem.prototype.toggleClass = function(className, which) {
		if (typeof which === 'undefined') {
			which = !this.hasClass(className);
		}

		if (which) {
			if (!this.hasClass(className)) {
				this.element.className += ' ' + className;
			}
		} else {
			if (this.hasClass(className)) {
				this.element.className = utils.trimSpaces(this.element.className.replace(new RegExp('( |^)' + _regexSafe(className) + '( |$)', 'g'), ' '));
			}
		}

		return this;
	};

	Elem.prototype.removeClass = function(className) {
		return this.toggleClass(className, false);
	};

	Elem.prototype.addClass = function(className) {
		return this.toggleClass(className, true);
	};

	Elem.prototype.toggle = function(which) {
		if (which) {
			this.element.style.display = 'block'; // needs "some" tweaking...
		} else {
			this.element.style.display = 'none';
		}

		return this;
	};

	Elem.prototype.show = function() {
		return this.toggle(true);
	};

	Elem.prototype.hide = function() {
		return this.toggle(false);
	};

	Elem.prototype.data = function(name, value) {
		if (!this.element._data) {
			this.element._data = {};
		}

		if (value) {
			this.element._data[name] = value;
			return this;
		} else {
			return this.element._data[name] || null;
		}
	};

	Elem.prototype.css = function(property, value) {
		if (typeof property === 'string') {
			if (typeof value !== 'undefined') {
				style.set(this, property, value);
				return this;
			} else {
				return style.get(this, property);
			}
		} else {
			utils.each(property, function(prop, val) {
				this.css(prop, val);
			}.bind(this));

			return this;
		}
	};

	Elem.prototype.animate = function(property, value, callback) {
		if (typeof property === 'string') {
			this.animations.push(animate(this, property, value, callback));
		} else {
			utils.each(property, function(prop, val) {
				this.animate(prop, val, value);
			}.bind(this));
		}

		return this;
	};

	Elem.prototype.stop = function() {
		utils.invoke(this.animations, 'stop');

		this.animations = [];

		return this;
	};

	Elem.prototype.remove = function() {
		var parent = this.parent();

		if (parent.raw()) {
			parent.raw().removeChild(this.element);
		}
	};

	Elem.prototype.values = function() {
		var values = {};
		var element;
		var value;
		var undef;

		if (this.element.tagName !== 'FORM') {
			return values;
		}

		for (var i = 0; i < this.element.elements.length; i++) {
			element = this.element.elements[i];
			value = undef;

			if (element.name) {
				// TODO add support for more elemnt types
				switch (element.type) {
					case 'checkbox':
						if (element.checked) {
							value = element.value || '1';
						}
						break;

					default:
						value = element.value;
						break;
				}

				if (value !== undef) {
					values[element.name] = value;
				}
			}
		}

		return values;
	};

	///////////////////////////////////////////////////////////////////
	//////////////////////// LIST
	/////////////////////////////////////////////////////////////////

	function List(elements) {
		this.elements = [];
		this.length = elements.length;

		for (var i = 0; i < this.length; i++) {
			this.elements.push(new Elem(elements[i]));
		}
	}

	List.prototype.get = function(i) {
		if (typeof i === 'undefined') {
			return this.elements;
		} else {
			return this.elements[i] || null;
		}
	};

	List.prototype.raw = function() {
		return this.map(function(element) {
			return element.raw();
		});
	};

	List.prototype.each = function(cb) {
		for (var i = 0, l = this.elements.length; i < l; i++) {
			cb.call(this.elements[i], this.elements[i], i);
		}

		return this;
	};

	List.prototype.map = function(cb) {
		var result = [];

		this.each(function() {
			result.push(cb.call(this, this));
		});

		return result;
	};

	List.prototype.clone = function() {
		return new List(this.map(function(element) {
			return element.clone().raw();
		}));
	};

	List.prototype.slice = function() {
		return new List(this.map(function(element) {
			return element.raw();
		}));
	};

	List.prototype.pop = function() {
		var result = this.elements.pop();
		this.length = this.elements.length;
		return result;
	};

	List.prototype.shift = function() {
		var result = this.elements.shift();
		this.length = this.elements.length;
		return result;
	};

	List.prototype.index = function(item) {
		var result = -1;
		var i = 0;

		this.each(function(element) {
			if (item && item.element === element.element) {
				result = i;
			}

			i++;
		});

		return result;
	};

	function _delegate(functionName) {
		List.prototype[functionName] = function() {
			var args = arguments;

			return this.each(function() {
				return this[functionName].apply(this, args);
			});
		};
	}

	// TODO fold Elem and List together
	_delegate('text');
	_delegate('attr');
	_delegate('on');
	_delegate('fire');
	_delegate('toggleClass');
	_delegate('addClass');
	_delegate('removeClass');
	_delegate('toggle');
	_delegate('show');
	_delegate('hide');
	_delegate('animate');
	_delegate('stop');
	_delegate('pause');
	_delegate('uapause');

	var dom = function(htmlElements) {
		return new List(htmlElements);
	};

	dom.window = new Elem(window);
	dom.document = new Elem(d);

	dom.id = function(id) {
		var el = d.getElementById(id);

		if (el) {
			return new Elem(el);
		} else {
			return null;
		}
	};

	dom.tags = function(tag) {
		return _tags(d, tag);
	};

	dom.tag = function(tag) {
		return dom.tags(tag).get(0);
	};

	dom.find = function(selector) {
		return _find(d, selector);
	};

	dom.findOne = function(selector) {
		return _find(d, selector).get(0);
	};

	dom.create = function(name, attrs) {
		var element = new Elem(d.createElement(name));

		if (attrs) {
			utils.each(attrs, function() {
				element.attr.apply(element, arguments);
			});
		}

		return element;
	};

	dom.createElement = function(name) {
		return d.createElement(name);
	};

	var _readyHandlers = [];
	var _domReady = false;

	dom.ready = function(cb) {
		if (_domReady) {
			cb();
		} else {
			_readyHandlers.push(cb);
		}
	};

	dom.window.on('DOMContentLoaded', function() {
		_domReady = true;

		utils.each(_readyHandlers, function(_, cb) {
			cb();
		});
	});

	return dom;
});
