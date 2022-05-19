function getURLVar(key) {
	var value = [];

	var query = String(document.location).split('?');

	if (query[1]) {
		var part = query[1].split('&');

		for (i = 0; i < part.length; i++) {
			var data = part[i].split('=');

			if (data[0] && data[1]) {
				value[data[0]] = data[1];
			}
		}

		if (value[key]) {
			return value[key];
		} else {
			return '';
		}
	}
}

// Cart add remove functions
var cart = {
	'add': function(product_id, quantity) {
		$.ajax({
			url: 'index.php?route=checkout/cart/add',
			type: 'post',
			data: 'product_id=' + product_id + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1),
			dataType: 'json',
			beforeSend: function() {
				showPreloader();
			},
			complete: function() {
				$('#header-cart').load('index.php?route=common/cart/info', function () {
					hidePreloader();
				});
			},
			success: function(json) {
				if (json['redirect']) {
					location = json['redirect'];
				}

				if (json['success']) {
					console.log(json);
					toastr.success(json['success']);

					// Need to set timeout otherwise it wont update the total
					setTimeout(function () {
						$('#header-cart #cart-total').html(json['product_count']);
					}, 100);
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				toastr.error(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	},
	'update': function(key, quantity) {
		$.ajax({
			url: 'index.php?route=checkout/cart/edit',
			type: 'post',
			data: 'key=' + key + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1),
			dataType: 'json',
			beforeSend: function() {
				showPreloader();
			},
			complete: function() {
				if (getURLVar('route') != 'checkout/cart' || getURLVar('route') != 'checkout/checkout') {
					$('#header-cart').load('index.php?route=common/cart/info', function () {
						hidePreloader();
					});
				}
			},
			success: function(json) {
				// Need to set timeout otherwise it wont update the total
				setTimeout(function () {
					$('#cart #cart-total').html(json['product_count']);
				}, 100);

				if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
					window.location = 'index.php?route=checkout/cart';
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				toastr.error(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	},
	'remove': function(key) {
		$.ajax({
			url: 'index.php?route=checkout/cart/remove',
			type: 'post',
			data: 'key=' + key,
			dataType: 'json',
			beforeSend: function() {
				$('#cartModal').modal('hide');
				showPreloader();
			},
			complete: function() {
				if (getURLVar('route') != 'checkout/cart' || getURLVar('route') != 'checkout/checkout') {
					$('#header-cart').load('index.php?route=common/cart/info', function () {
						hidePreloader();
						$('#cartModal').modal('show');
					});
				}
			},
			success: function(json) {
				// Need to set timeout otherwise it wont update the total
				setTimeout(function () {
					$('#cart #cart-total').html(json['product_count']);
				}, 100);

				if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
					window.location = 'index.php?route=checkout/cart';
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				toastr.error(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	}
}

var voucher = {
	'add': function() {

	},
	'remove': function(key) {
		$.ajax({
			url: 'index.php?route=checkout/cart/remove',
			type: 'post',
			data: 'key=' + key,
			dataType: 'json',
			beforeSend: function() {
				showPreloader();
			},
			complete: function() {
				if (getURLVar('route') != 'checkout/cart' || getURLVar('route') != 'checkout/checkout') {
					$('#header-cart').load('index.php?route=common/cart/info', function () {
						hidePreloader();
					});
				}
			},
			success: function(json) {
				// Need to set timeout otherwise it wont update the total
				setTimeout(function () {
					$('#cart #cart-total').html(json['product_count']);
				}, 100);

				if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
					window.location = 'index.php?route=checkout/cart';
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				toastr.error(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	}
}

var wishlist = {
	'add': function(product_id) {
		$.ajax({
			url: 'index.php?route=account/wishlist/add',
			type: 'post',
			data: 'product_id=' + product_id,
			dataType: 'json',
			success: function(json) {
				if (json['redirect']) {
					window.location = json['redirect'];
				}

				if (json['success']) {
					toastr.success(json['success']);
				}

				$('#wishlist-total span').html(json['total']);
				$('#wishlist-total').attr('title', json['total']);
			},
			error: function(xhr, ajaxOptions, thrownError) {
				toastr.error(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	},
	'remove': function() {

	}
}

function initPhotoSwipeFromDOM(gallerySelector) {

	// parse slide data (url, title, size ...) from DOM elements
	// (children of gallerySelector)
	var parseThumbnailElements = function(el) {
		let rootEl = document.querySelector('.pswp-gallery'); //layout hook for gallery

		var thumbElements = rootEl.querySelectorAll('figure'),
			numNodes = thumbElements.length,
			items = [],
			figureEl,
			linkEl,
			size,
			item;

		console.log(thumbElements, numNodes);

		for(var i = 0; i < numNodes; i++) {

			figureEl = thumbElements[i]; // <figure> element

			// include only element nodes
			if(figureEl.nodeType !== 1) {
				continue;
			}

			linkEl = figureEl.children[0]; // <a> element
			if (linkEl.getAttribute('data-size') === null) {
				continue;
			}
			size = linkEl.getAttribute('data-size').split('x');

			// create slide object
			item = {
				src: linkEl.getAttribute('href'),
				w: parseInt(size[0], 10),
				h: parseInt(size[1], 10)
			};

			if(figureEl.children.length > 1) {
				// <figcaption> content
				item.title = figureEl.children[1].innerHTML;
			}

			if(linkEl.children.length > 0) {
				// <img> thumbnail element, retrieving thumbnail url
				item.msrc = linkEl.children[0].getAttribute('src');
			}

			item.el = figureEl; // save link to element for getThumbBoundsFn
			items.push(item);
		}

		return items;
	};

	// find nearest parent element
	var closest = function closest(el, fn) {
		return el && ( fn(el) ? el : closest(el.parentNode, fn) );
	};

	// triggers when user clicks on thumbnail
	var onThumbnailsClick = function(e) {
		e = e || window.event;
		e.preventDefault ? e.preventDefault() : e.returnValue = false;

		var eTarget = e.target || e.srcElement;

		// find root element of slide
		var clickedListItem = closest(eTarget, function(el) {
			return (el.tagName && el.tagName.toUpperCase() === 'FIGURE');
		});

		if(!clickedListItem) {
			return;
		}

		// find index of clicked item by looping through all child nodes
		// alternatively, you may define index via data- attribute
		var clickedGallery = clickedListItem.parentNode,
			childNodes = clickedListItem.parentNode.childNodes,
			numChildNodes = childNodes.length,
			nodeIndex = 0,
			index;

		for (var i = 0; i < numChildNodes; i++) {
			if(childNodes[i].nodeType !== 1) {
				continue;
			}

			if(childNodes[i] === clickedListItem) {
				index = nodeIndex;
				break;
			}
			nodeIndex++;
		}



		if(index >= 0) {
			// open PhotoSwipe if valid index found
			openPhotoSwipe( e.target, index, clickedGallery );
		}
	};

	// parse picture index and gallery index from URL (#&pid=1&gid=2)
	var photoswipeParseHash = function() {
		var hash = window.location.hash.substring(1),
			params = {};

		if(hash.length < 5) {
			return params;
		}

		var vars = hash.split('&');
		for (var i = 0; i < vars.length; i++) {
			if(!vars[i]) {
				continue;
			}
			var pair = vars[i].split('=');
			if(pair.length < 2) {
				continue;
			}
			params[pair[0]] = pair[1];
		}

		if(params.gid) {
			params.gid = parseInt(params.gid, 10);
		}

		return params;
	};

	 function openPhotoSwipe(targetEl, index, galleryElement, disableAnimation, fromURL) {
		var pswpElement = document.querySelectorAll('.pswp')[0],
			gallery,
			options,
			items;
		items = parseThumbnailElements(galleryElement);
		// define options (if needed)
		options = {
			index: targetEl.parentNode.parentNode.dataset.galleryIndex,
			shareEl: false,
			counterEl: false,
			arrowEl: true,
			zoomEl: true,
			// define gallery index (for URL)
			// galleryUID: galleryElement.getAttribute('data-pswp-uid'),

			getThumbBoundsFn: function(index) {
				// See Options -> getThumbBoundsFn section of documentation for more info
				var thumbnail = items[index].el.getElementsByTagName('img')[0], // find thumbnail
					pageYScroll = window.pageYOffset || document.documentElement.scrollTop,
					rect = thumbnail.getBoundingClientRect();

				return {x:rect.left, y:rect.top + pageYScroll, w:rect.width};
			}

		};

		// PhotoSwipe opened from URL
		// if(fromURL) {
		// 	if(options.galleryPIDs) {
		// 		// parse real index when custom PIDs are used
		// 		// http://photoswipe.com/documentation/faq.html#custom-pid-in-url
		// 		for(var j = 0; j < items.length; j++) {
		// 			if(items[j].pid == index) {
		// 				options.index = j;
		// 				break;
		// 			}
		// 		}
		// 	} else {
		// 		// in URL indexes start from 1
		// 		options.index = parseInt(index, 10) - 1;
		// 	}
		// } else {
		// 	options.index = parseInt(index, 10);
		// }

		// exit if index not found
		if( isNaN(options.index) ) {
			return;
		}

		if(disableAnimation) {
			options.showAnimationDuration = 0;
		}

		// Pass data to PhotoSwipe and initialize it
		gallery = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, items, options);
		gallery.init();
		return gallery;
	};

	// loop through all gallery elements and bind events
	var galleryElements = document.querySelectorAll( gallerySelector );

	for(var i = 0, l = galleryElements.length; i < l; i++) {
		galleryElements[i].setAttribute('data-pswp-uid', i+1);
		galleryElements[i].onclick = onThumbnailsClick;
	}

	// Parse URL and open gallery if it contains #&pid=3&gid=1
	var hashData = photoswipeParseHash();
	if(hashData.pid && hashData.gid) {
		openPhotoSwipe( hashData.pid ,  galleryElements[ hashData.gid - 1 ], true, true );
	}
};

function showPreloader() {
	$('body').append('<div class="preloader flex-center preloaderFadeIn"><i class="fa fa-circle-o-notch fa-spin"></i></div>');
}
function hidePreloader() {
	$('.preloader').fadeOut('slow');
	setTimeout(function () {
		$('.preloader').remove();
	}, 600);
}

document.addEventListener('DOMContentLoaded', () => {
// Autocomplete */
	(function($) {
		$.fn.autocomplete = function(option) {
			return this.each(function() {
				this.timer = null;
				this.items = new Array();

				$.extend(this, option);

				$(this).attr('autocomplete', 'off');

				// Focus
				$(this).on('focus', function() {
					this.request();
				});

				// Blur
				$(this).on('blur', function() {
					setTimeout(function(object) {
						object.hide();
					}, 200, this);
				});

				// Keydown
				$(this).on('keydown', function(event) {
					switch(event.keyCode) {
						case 27: // escape
							this.hide();
							break;
						default:
							this.request();
							break;
					}
				});

				// Click
				this.click = function(event) {
					event.preventDefault();

					value = $(event.target).parent().attr('data-value');

					if (value && this.items[value]) {
						this.select(this.items[value]);
					}
				}

				// Show
				this.show = function() {
					var pos = $(this).position();

					$(this).siblings('ul.dropdown-menu').css({
						top: pos.top + $(this).outerHeight(),
						left: pos.left
					});

					$(this).siblings('ul.dropdown-menu').show();
				}

				// Hide
				this.hide = function() {
					$(this).siblings('ul.dropdown-menu').hide();
				}

				// Request
				this.request = function() {
					clearTimeout(this.timer);

					this.timer = setTimeout(function(object) {
						object.source($(object).val(), $.proxy(object.response, object));
					}, 200, this);
				}

				// Response
				this.response = function(json) {
					html = '';

					if (json.length) {
						for (i = 0; i < json.length; i++) {
							this.items[json[i]['value']] = json[i];
						}

						for (i = 0; i < json.length; i++) {
							if (!json[i]['category']) {
								html += '<li data-value="' + json[i]['value'] + '"><a href="#">' + json[i]['label'] + '</a></li>';
							}
						}

						// Get all the ones with a categories
						var category = new Array();

						for (i = 0; i < json.length; i++) {
							if (json[i]['category']) {
								if (!category[json[i]['category']]) {
									category[json[i]['category']] = new Array();
									category[json[i]['category']]['name'] = json[i]['category'];
									category[json[i]['category']]['item'] = new Array();
								}

								category[json[i]['category']]['item'].push(json[i]);
							}
						}

						for (i in category) {
							html += '<li class="dropdown-header">' + category[i]['name'] + '</li>';

							for (j = 0; j < category[i]['item'].length; j++) {
								html += '<li data-value="' + category[i]['item'][j]['value'] + '"><a href="#">&nbsp;&nbsp;&nbsp;' + category[i]['item'][j]['label'] + '</a></li>';
							}
						}
					}

					if (html) {
						this.show();
					} else {
						this.hide();
					}

					$(this).siblings('ul.dropdown-menu').html(html);
				}

				$(this).after('<ul class="dropdown-menu"></ul>');
				$(this).siblings('ul.dropdown-menu').delegate('a', 'click', $.proxy(this.click, this));

			});
		}
	})(window.jQuery);

	/* Agree to Terms */
	$(document).delegate('.agree', 'click', function(e) {
		e.preventDefault();

		$('#modal-agree').remove();

		var element = this;

		$.ajax({
			url: $(element).attr('href'),
			type: 'get',
			dataType: 'html',
			beforeSend: function() {
				showPreloader();
			},
			complete: function() {
				hidePreloader();
			},
			success: function(data) {
				html  = '<div id="modal-agree" class="modal">';
				html += '  <div class="modal-dialog">';
				html += '    <div class="modal-content">';
				html += '      <div class="modal-header">';
				html += '        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>';
				html += '        <h4 class="modal-title">' + $(element).text() + '</h4>';
				html += '      </div>';
				html += '      <div class="modal-body">' + data + '</div>';
				html += '    </div>';
				html += '  </div>';
				html += '</div>';

				$('body').append(html);

				$('#modal-agree').modal('show');
			}
		});
	});

	$(window).on('scroll', function (e) {
		if (window.scrollY > 600) {
			$('.btn-to-top').addClass('active');
		} else {
			$('.btn-to-top').removeClass('active');
		}
	})

	$(document).ready(function() {
		// Highlight any found errors
		$('.text-danger').each(function() {
			var element = $(this).parent().parent();

			if (element.hasClass('form-group')) {
				element.addClass('has-error');
			}
		});

		// Currency
		$('#form-currency .currency-select').on('click', function(e) {
			e.preventDefault();

			$('#form-currency input[name=\'code\']').val($(this).attr('name'));

			$('#form-currency').submit();
		});

		// Language
		$('#form-language .language-select').on('click', function(e) {
			e.preventDefault();

			$('#form-language input[name=\'code\']').val($(this).attr('name'));

			$('#form-language').submit();
		});

		/* Search */
		$('#search input[name=\'search\']').parent().find('button').on('click', function() {
			var url = $('base').attr('href') + 'index.php?route=product/search';

			var value = $('header #search input[name=\'search\']').val();

			if (value) {
				url += '&search=' + encodeURIComponent(value);
			}

			location = url;
		});

		$('#search input[name=\'search\']').on('keydown', function(e) {
			if (e.keyCode == 13) {
				$('header #search input[name=\'search\']').parent().find('button').trigger('click');
			}
		});

		// Menu
		$('#menu .dropdown-menu').each(function() {
			var menu = $('#menu').offset();
			var dropdown = $(this).parent().offset();

			var i = (dropdown.left + $(this).outerWidth()) - (menu.left + $('#menu').outerWidth());

			if (i > 0) {
				$(this).css('margin-left', '-' + (i + 10) + 'px');
			}
		});

		// Checkout
		$(document).on('keydown', '#collapse-checkout-option input[name=\'email\'], #collapse-checkout-option input[name=\'password\']', function(e) {
			if (e.keyCode == 13) {
				$('#collapse-checkout-option #button-login').trigger('click');
			}
		});

		// tooltips on hover
		$('[data-toggle=\'tooltip\']').tooltip({container: 'body'});

		// Makes tooltips work on ajax generated content
		$(document).ajaxStop(function() {
			$('[data-toggle=\'tooltip\']').tooltip({container: 'body'});
		});

		let form = $('.to-validate');
		if (form.length > 0) {
			form.each(function () {
				if ($(this).prop('id') !== 'form-language' || $(this).prop('id') !== 'form-currency') {
					$(this).bootstrapValidator({
						live: 'enabled',
						feedbackIcons: {
							valid: 'fa fa-check',
							invalid: 'fa fa-times',
							validating: 'fa fa-refresh'
						}
					});
				}
			});
		}

		toastr.options = {
			"closeButton": true,
			"debug": false,
			"newestOnTop": false,
			"progressBar": true,
			"positionClass": "toast-bottom-left",
			"preventDuplicates": false,
			"onclick": null,
			"showDuration": "300",
			"hideDuration": "1000",
			"timeOut": "8000",
			"extendedTimeOut": "1000",
			"showEasing": "swing",
			"hideEasing": "linear"
		}

		$('.btn-main-menu').on('click', function () {
			const menu = $('.main-menu');
			menu.toggleClass('active');
		});

		$('.btn-to-top').on('click', function () {
			$("html, body").animate({ scrollTop: "0" }, 800);
		});

		if (screen.width <= 1024) {
			let interval = setInterval(function () {
				let addThis = $('#at-share-dock');
				if (typeof addThis != "undefined") {
					addThis.addClass('add-this-hidden-mobile');
					$('body').append('<div class="btn-share"><i class="fa fa-share-alt"></i></div>');
					$('.btn-share').on('click', function (e) {
						addThis.toggleClass('add-this-hidden-mobile');
						if (addThis.hasClass('add-this-hidden-mobile')) {
							$('.btn-share').removeClass('add-this-visible');
							$('.btn-to-top').removeClass('add-this-visible');
						} else {
							$('.btn-share').addClass('add-this-visible');
							$('.btn-to-top').addClass('add-this-visible');
						}
					});
					clearInterval(interval);
				}
			}, 1000);
		}
	});
});
