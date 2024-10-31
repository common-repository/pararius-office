
/* global mmx_define */

mmx_define('slider', ['dom'], function(dom) {
	'use strict';

	function Slider(wrapper, options) {
		this.options = options || {};

		this.wrapper = wrapper;
		this.items = this.wrapper.find(this.options.childSelector || 'li');
		this.totalItems = this.items.length;
		this.currentItem = 0;
		this.itemsPerPage = this.options.itemsPerPage || 3;

		this.initButtons();
		// this.initPagination();

		if (this.options.startItem) {
			this.go(this.options.startItem);
		} else {
			this.updateButtonVisibility();
		}

		this.resetContinuous();
	}

	Slider.prototype.getEl = function getEl() {
		return this.wrapper;
	};

	Slider.prototype.go = function go(where) {
		var that = this;
		var oldItem = this.currentItem;
		var items = this.items.slice();

		this.currentItem = Math.max(0, where);
		this.currentItem = Math.min(this.totalItems - this.itemsPerPage, this.currentItem);

		var to = oldItem >= that.currentItem ? 'left' : 'right';
		var oldStartEnd = to === 'right' ? oldItem : (oldItem + this.itemsPerPage);
		var newStartEnd = to === 'left' ? this.currentItem : (this.currentItem + this.itemsPerPage);

		var visibleRangeStart = Math.min(oldStartEnd, newStartEnd);
		var visibleRangeEnd = Math.max(oldStartEnd, newStartEnd);

		var currentTimeout = 0;
		var getFunc = to === 'right' ? 'shift' : 'pop';

		if (oldItem !== this.currentItem) {
			(function loop() {
				var item = items[getFunc]();
				var i = that.items.index(item);
				var left;

				if (i > -1) {
					left = (-that.currentItem * (100 / that.itemsPerPage)) + '%';

					// only delay visible images
					if (i >= visibleRangeStart && i <= visibleRangeEnd) {
						setTimeout(function() {
							item.animate('left', left);
						}, currentTimeout);

						currentTimeout += 50;
					} else {
						item.css('left', left);
					}

					loop();
				}
			}());
		}

		this.updateButtonVisibility();

		return oldItem !== this.currentItem;
	};

	Slider.prototype.get = function get() {
		return this.currentItem;
	};

	Slider.prototype.next = function next() {
		return this.go(this.currentItem + 1);
	};

	Slider.prototype.prev = function prev() {
		return this.go(this.currentItem - 1);
	};

	Slider.prototype.nextPage = function nextPage() {
		return this.go(this.currentItem + this.itemsPerPage);
	};

	Slider.prototype.prevPage = function prevPage() {
		return this.go(this.currentItem - this.itemsPerPage);
	};

	Slider.prototype.getPage = function getPage() {
		var page = 0;

		while (page * this.itemsPerPage < this.currentItem) {
			page++;
		}

		return page + 1;
	};

	Slider.prototype.initContinuous = function initContinuous(interval) {
		var that = this;

		this.interval  = setInterval(function() {
			var result = that.nextPage();

			if (!result) {
				that.go(0);
			}
		}, interval);
	};

	Slider.prototype.resetContinuous = function resetContinuous() {
		if (this.options.continuous) {
			clearInterval(this.interval);
			this.initContinuous(this.options.continuousInterval);
		}
	};

	Slider.prototype.initButtons = function initButtons() {
		var that = this;

		if (this.options.buttonsHolder) {
			this.prevButton = this.options.buttonsHolder.findOne('a.prev');

			if (!this.prevButton) {
				this.prevButton = dom.create('a', { href: '#prev', className: 'prev' }).css('opacity', 0).appendTo(this.options.buttonsHolder);
			}

			this.nextButton = this.options.buttonsHolder.findOne('a.next');

			if (!this.nextButton) {
				this.nextButton = dom.create('a', { href: '#next', className: 'next' }).css('opacity', 0).appendTo(this.options.buttonsHolder);
			}

			this.prevButton.on('click', function() {
				that.prevPage();
				that.resetContinuous();
				return false;
			});

			this.nextButton.on('click', function() {
				that.nextPage();
				that.resetContinuous();
				return false;
			});
		}
	};

	Slider.prototype.updateButtonVisibility = function updateButtonVisibility() {
		if (this.options.buttonsHolder) {
			if (this.currentItem > 0) {
				this.prevButton.animate('opacity', 1);
			} else {
				this.prevButton.animate('opacity', 0);
			}

			if (this.currentItem < this.totalItems - this.itemsPerPage) {
				this.nextButton.animate('opacity', 1);
			} else {
				this.nextButton.animate('opacity', 0);
			}
		}
	};

	// Slider.prototype.initPagination = function initPagination() {
	// 	var that = this;
	// 	var totalPages = Math.ceil(this.totalItems / this.itemsPerPage);
    //
	// 	if (this.options.paginationHolder && totalPages > 1) {
	// 		this.pagination = new Pagination(this.options.paginationHolder, totalPages);
	// 		this.pagination.onPageChange(function(page) {
	// 			that.go((page - 1) * that.itemsPerPage);
	// 		});
	// 	}
	// };

	Slider.prototype.showItem = function showItem(source) {
		var index = 0;
		var found = false;

		this.items.each(function iterator() {
			var href = this.tag('a').attr('href').substring(8);

			if (source === href) {
				found = true;
				return false;
			}

			index++;
		});

		if (found && (index < this.currentItem || index > (this.currentItem + this.itemsPerPage + 1))) {
			this.go(index - 1);
		}
	};

	Slider.prototype.tearDown = function tearDown() {
		if (this.options.buttonsHolder) {
			this.prevButton.remove();
			this.nextButton.remove();
		}
	};

	return Slider;
});
