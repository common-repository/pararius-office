var mmx_require;
var mmx_define;

(function() {
	'use strict';

	var loadedDependencies = {};
	var queue = [];

	function each(ary, func) {
		var i;

		if (ary) {
			for (i = 0; i < ary.length; i += 1) {
				if (ary[i] && func(ary[i], i, ary)) {
					break;
				}
			}
		}
	}

	function execute() {
		(function loop(list, i) {
			var cur = list[i];
			var fulfilledDependencies = [];
			var go = true;

			if (cur) {
				// loop through all module dependencies,
				// looking to see if all of them are fulfilled
				each(cur.dependencies, function(dependency) {
					if (!Object.prototype.hasOwnProperty.call(loadedDependencies, dependency)) {
						go = false;
						return true;
					}
				});

				// all dependencies are fulfilled
				if (go) {
					each(cur.dependencies, function(dependency) {
						fulfilledDependencies.push(loadedDependencies[dependency]);
					});

					queue.splice(i, 1);
					cur.callback.apply(null, fulfilledDependencies);
				} else {
					loop(list, i + 1, go);
				}
			}
		}(queue, 0));
	}

	mmx_require = function(dependencies, callback) {
		queue.push({
			dependencies: dependencies,
			callback: callback,
		});

		execute();
	};

	mmx_define = function(/* name, [dependencies,] callback */) {
		var args = Array.prototype.slice.call(arguments);

		var callback = args.pop();
		var name = args.shift();
		var dependencies = args.shift();

		if (typeof name !== 'string') {
			dependencies = name;
			name = null;
		}

		if (Object.prototype.toString.call(dependencies) !== '[object Array]') {
			dependencies = [];
		}

		mmx_require(dependencies, function() {
			var resolved = callback.apply(null, arguments);

			if (name) {
				loadedDependencies[name] = resolved;
			}

			if (queue.length) {
				execute();
			}
		});
	};

	mmx_define.amd = {
		jQuery: true,
	};
}());
