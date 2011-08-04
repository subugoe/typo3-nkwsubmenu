/**
 * @preserve  jknav
 * @name      jquery.jknav.js
 * @author    Yu-Jie Lin http://j.mp/Google-livibetter
 * @version   0.5.0.1
 * @date      05-24-2011
 * @copyright (c) 2010, 2011 Yu-Jie Lin <livibetter@gmail.com>
 * @license   BSD License
 * @homepage  http://code.google.com/p/lilbtn/wiki/JsJqueryJknav
 * @example   http://lilbtn.googlecode.com/hg/src/static/js/jquery/jquery.jknav.demo.html
*/
(function (jQuery) {
	/**
	 * Print out debug infomation via console object
	 * @param {String} debug information
	 */
	function log (message) {
		var console = window.console;
		if (jQuery.jknav.DEBUG && console && console.log)
			console.log('jknav: ' + message);
		}

	/**
	 * Add jQuery objects to navgation list
	 *
	 * @param {Function} callback Callback function to be invoked after plugin scroll to item
	 * @param {String} name Navagation set name
	 * @return {jQuery} <code>this</code> for chaining
	 */
	jQuery.fn.jknav = function (callback, name) {
		if (name == null)
			name = 'default';
		if (jQuery.jknav.items[name] == null)
			jQuery.jknav.items[name] = [];
		return this.each(function () {
			jQuery.jknav.items[name].push([this, callback]);
			jQuery.jknav.items[name].sort(function (a, b) {
				var a_top = jQuery(a[0]).offset().top;
				var b_top = jQuery(b[0]).offset().top;
				if (a_top < b_top)
					return -1;
				if (a_top > b_top)
					return 1;
				if (a_top == b_top) {
					var a_left = jQuery(a[0]).offset().left;
					var b_left = jQuery(b[0]).offset().left;
					if (a_left < b_left)
						return -1;
					if (a_left > b_left)
						return 1;
					return 0;
					}
				});
			});
		};

	/**
	 * A helper to do callback
	 * @param {Number} index of the item navgation set
	 * @param {Object} opts Options
	 */
	function do_callback(index, opts) {
		var callback = jQuery.jknav.items[opts.name][index][1];
		if (callback)
			callback(jQuery.jknav.items[opts.name][index][0]);
		}

	/**
	 * Calculate the index of next item
	 * @param {Number} offset Indicates move forword or backward
	 * @param {Object} opts Options
	 */
	function calc_index(offset, opts) {
		var index = jQuery.jknav.index[opts.name];
		log('Calculating index for ' + opts.name + ', current index = ' + index);
		if (index == null) {
			// Initialize index
			var top = jQuery(jQuery.jknav.TARGET).scrollTop();
			log(jQuery.jknav.TARGET + ' top = ' + top);
			jQuery.each(jQuery.jknav.items[opts.name], function (idx, item) {
				// Got a strange case: top = 180, item_top = 180.35...
				var item_top = Math.floor(jQuery(item).offset().top);
				if (top >= item_top)
					index = idx;
				});
			if (index == null) {
				if (offset > 0)
					index = 0
				else
					index = jQuery.jknav.items[opts.name].length - 1;
				}
			else {
				if (offset > 0 && ++index >= jQuery.jknav.items[opts.name].length)
					index = 0
				else if (offset < 0 && top == Math.floor(jQuery(jQuery.jknav.items[opts.name][index]).offset().top) && --index < 0)
					index = jQuery.jknav.items[opts.name].length - 1;
				}
			}
		else {
			if (!opts.circular && ((index == 0 && offset == -1) || (index == jQuery.jknav.items[opts.name].length - 1 && offset == 1)))
				return index;
			index += offset;
			if (index >= jQuery.jknav.items[opts.name].length)
				index = 0;
			if (index < 0)
				index = jQuery.jknav.items[opts.name].length - 1;
			}
		log('new index = ' + index);
		jQuery.jknav.index[opts.name] = index;
		return index;
		}
		
	/**
	 * Keyup handler
	 * @param {Event} e jQuery event object
	 * @param {Object} opts Options
	 */
	function keyup(e, opts) {
		if (e.target.tagName.toLowerCase() == 'input' ||
		  e.target.tagName.toLowerCase() == 'button' ||
		  e.target.tagName.toLowerCase() == 'select' ||
		  e.target.tagName.toLowerCase() == 'textarea') {
			log('keyup: ' + e.target.tagName + ', target is INPUT ignored.');
			return
			}
		var ch = String.fromCharCode(e.keyCode).toLowerCase();
		log('keyup: ' + e.target.tagName + ', key: ' + ch);
		if (ch == opts.up.toLowerCase() || ch == opts.down.toLowerCase()) {
			if (opts.reevaluate)
				jQuery.jknav.index[opts.name] = null;
			var index = calc_index((ch == opts.down.toLowerCase()) ? 1 : -1, opts);
			var $item = jQuery(jQuery.jknav.items[opts.name][index][0]);
			jQuery(jQuery.jknav.TARGET).animate(
				{
					scrollLeft: Math.floor($item.offset().left),
					scrollTop: Math.floor($item.offset().top)
					},
				opts.speed,
				opts.easing,
				function () {
					do_callback(index, opts)
					}
				);
			}
		}

	jQuery.jknav = {
		index: {},
		items: {},
		opts: {},
		default_options: {
			up: 'k',
			down: 'j',
			name: 'default',
			easing: 'swing',
			speed: 'normal',
			circular: true,
			reevaluate: false
			},
		DEBUG: false,
		TARGET_KEYUP: 'html',
		// IE, Firefox, and Opera must use <html> to scroll
		// Webkit must use <bod> to scroll
		TARGET: (!jQuery.browser.webkit)?'html':'body',
		/**
		 * Initialization function
		 * @param {Object} options Options
		 */
		init: function (options) {
			var opts = jQuery.extend(jQuery.extend({}, jQuery.jknav.default_options), options);
			jQuery.jknav.index[opts.name] = null;
			jQuery.jknav.opts[opts.name] = opts;
			jQuery(jQuery.jknav.TARGET_KEYUP).keyup(function (e) {
				keyup(e, opts);
				});
			log('new set "' + opts.name + '" initialzed.');
			},
		/**
		 * Navigate up
		 * @param {String} name name of set
		 */
		up: function (name) {
			var opts = jQuery.jknav.opts[name || 'default'];
			keyup({target: {tagName: ''}, keyCode: opts.up.charCodeAt(0)}, opts);
			},
		/**
		 * Navigate down
		 * @param {String} name name of set
		 */
		down: function (name) {
			var opts = jQuery.jknav.opts[name || 'default'];
			keyup({target: {tagName: ''}, keyCode: opts.down.charCodeAt(0)}, opts);
			}
		};
	})(jQuery);
// vim: ts=2: sw=2