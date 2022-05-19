function scrollTo(element, offsetTop = 0) {
    $([document.documentElement, document.body]).animate({
        scrollTop: $(element).offset().top - offsetTop
    }, 2000);
}
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
function makeSeoUrl(text) {
	var symbols = ['!', '"', '@', '#', '$', '%', '&', '=', '-', '–'];
	for (const symbol of symbols) {
		text = text.replace(new RegExp( symbol, 'g' ), '');
	}
	var symbols = ['?', '(', ')', '/', '\\', '+', '^', '\''];
	for (const symbol of symbols) {
		while (text.indexOf(symbol) !== -1) {
			text = text.replace(symbol, '');
		}
	}
    return text.toLowerCase().trim().replace(/ /g, '-');
}
function showPreloader() {
    let html = '<div class="preloader show"><img src="view/image/preloader.svg"></div>';
    $('body').append(html);
}
function hidePreloader() {
    $('.preloader').removeClass('show').addClass('hide');
    setTimeout(function () {
        $('.preloader').remove();
    }, 550);
}
function isEmptyString(str) {
    return str.length > 0 ? false : true;
}
function toolsPanelScroll() {
	let toolsSelector = '.page-header .pull-right';
    let isMobile = screen.width <= 768;
    $(toolsSelector).addClass('tools-panel');
    $(toolsSelector + ' > .btn').addClass('btn-floating circle btn-sm');
    let pos = $(toolsSelector).position();
    if (typeof pos !== "undefined" && !isMobile) {
        //Stick Tool Panel
        $(toolsSelector).removeClass('mobile');
        if (window.pageYOffset > pos.top) {
            $(toolsSelector).addClass('fixed');
        } else {
            $(toolsSelector).removeClass('fixed');
        }
    } else if(isMobile) {
        //Mobile Tool Panel
        let toolPanel = $(toolsSelector).addClass('mobile');
        let btn = $('#btn_tools_panel');
        if (toolPanel.find('a, button').length > 0) {
            if (btn.length <= 0) {
                let html = '<a id="btn_tools_panel" class="btn btn-floating circle btn-md btn-primary" onclick="switchToolMenu()"><i class="fa fa-eercast"></i></a>';
                $('body').append(html);
            }
        }
    }
}
function switchToolMenu() {
    $('.tools-panel').toggleClass('visible');
}
function switchButtonChange(e) {
    let toggle = $(e.target);
    if (toggle.prop('checked')) {
        toggle.val(1);
    } else {
        toggle.val(0);
    }
}
function triggerCheckboxValue(chkSelector) {
    let chk = $(chkSelector);
    chk.prop('checked') ? chk.val(1) : chk.val(0);
}

function OpenMonsterImageManager(callback, value, meta) {
	let width = window.innerWidth - 20;
	let height = window.innerHeight - 20;

	//value stores prev selected file + path
	// let fileUrl = tinyMCE.activeEditor.settings.pthManager + 'images.php?path=' + value;
	let fileUrl = tinymce.activeEditor.settings.pthManager;

	tinymce.activeEditor.windowManager.openUrl({
		title: "Monster Image Manager",
		url: fileUrl,
		width: width,
		height: height,
		inline: 1,
		resizable: true,
		maximizable: true,
		onMessage: function (api, data) {
			if (data.mceAction === 'customAction') {
				callback(data.url);
				api.close();
			}
		}

	});
}

var tinymceOptions = {
	selector: '[id^="input-description"]',
	height: 500,
	plugins: [
		'advlist autolink link image lists charmap print preview hr anchor pagebreak spellchecker',
		'searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking',
		'save table directionality emoticons template paste codemirror'
	],
	toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print preview media fullpage | forecolor backcolor emoticons | code',
	extended_valid_elements : "script[charset|async|defer|language|src|type]",
	forced_root_block: false,
	file_picker_callback: function (callback, value, meta) {
		OpenMonsterImageManager(callback, value, meta);
	},
	codemirror: {
		indentOnInit: true, // Whether or not to indent code on init.
		fullscreen: true,   // Default setting is false
		path: 'codemirror-4.8', // Path to CodeMirror distribution
		config: {           // CodeMirror config object
			mode: 'text/html',
			lineNumbers: true
		},
		width: 1000,         // Default value is 800
		height: 600,        // Default value is 550
		saveCursorPosition: false,    // Insert caret marker
		jsFiles: [          // Additional JS files to load
			'mode/javascript/javascript.js',
			'mode/php/php.js',
			'mode/htmlmixed/htmlmixed.js'
		]
	}
};

$(document).ready(function() {
    $('.selectpicker').selectpicker({
        liveSearch: true
    });

	$('select.form-control').selectpicker();

    toolsPanelScroll();

    $(window).on('resize', function () {
        $('.selectpicker').selectpicker('refresh');
    });

    //Stick tools panel
    $(window).on('scroll', function () {
        toolsPanelScroll();
    });

    //material design routine
    $('.col-sm-10').removeClass('col-sm-10').addClass('col-sm-12');
    $('.form-group').addClass('bmd-form-group');
    $('.btn-link').addClass('btn-primary').removeClass('btn-link');
	$('.form-group .control-label').removeClass('col-sm-2').addClass('label-small');
    $('.table').addClass('table-striped');
    $('.table td .btn').addClass('btn-floating circle btn-sm');
    $('.table tbody td:last-child .btn').wrap('<div class="d-flex justify-content-center flex-wrap"></div>');

    let tab = $('.nav.nav-tabs').first();
    tab.addClass('bg-dark');
	tab.find('li').addClass('nav-item');
    tab.find('a').addClass('nav-link');

    let langTabs = $('#language');
    langTabs.addClass('bg-primary');
    langTabs.find('li').addClass('nav-item');
    langTabs.find('a').addClass('nav-link');

    //Init material design for input type="radio"
    $('input[type="radio"]').after('<span class="bmd-radio"></span>');

    //Add checkbox wrapper to table checkboxes
    $('.table input[type="checkbox"]').wrap('<div class="checkbox"><label></label></div>');

    //Init material design for input type="checkbox"
    $('input[type="checkbox"]').after('<span class="checkbox-decorator"><span class="check"></span></span>');

    //Init Ripple Click Effect for next elements
    $('.bootstrap-select, input[type=submit], input[type=reset], input[type=button], button, a').rippleEffect();

	//Highlight active breadcrumb
	$('.breadcrumb li').last().addClass('active');

	//Form Submit for IE Browser
	$('button[type=\'submit\']').on('click', function() {
		$("form[id*='form-']").submit();
	});

	// Highlight any found errors
	$('.text-danger').each(function() {
		var element = $(this).parent().parent();

		if (element.hasClass('form-group')) {
			element.addClass('has-error');
		}
	});

	// tooltips on hover
	$('[data-toggle=\'tooltip\']').tooltip({container: 'body', html: true});

	// Makes tooltips work on ajax generated content
	$(document).ajaxStop(function() {
		$('[data-toggle=\'tooltip\']').tooltip({container: 'body'});
	});

	// https://github.com/opencart/opencart/issues/2595
	$.event.special.remove = {
		remove: function(o) {
			if (o.handler) {
				o.handler.apply(this, arguments);
			}
		}
	}

	// Tooltip remove fixed
	$(document).on('click', '[data-toggle=\'tooltip\']', function(e) {
		$('body > .tooltip').remove();
	});
	
	$('#button-menu').on('click', function(e) {
		e.preventDefault();

		var menu = $('#column-left');
		var btn = $(this);
		menu.toggleClass('active');
		if (menu.hasClass('active')) {
			btn.find('i').removeClass('fa-bars').addClass('fa-times');
			sessionStorage.setItem('column-left-visibility', true);
		} else {
			btn.find('i').removeClass('fa-times').addClass('fa-bars');
			sessionStorage.setItem('column-left-visibility', false);
		}
	});

	//Set left menu visibility
    if (sessionStorage.getItem('column-left-visibility') === 'true') {
        $('#column-left').addClass('active');
	    $('.bmd-btn-fab i.fa').removeClass('fa-bars').addClass('fa-times');
    }
	// Set last page opened on the menu
	$('#menu a[href]').on('click', function() {
		sessionStorage.setItem('menu', $(this).attr('href'));
	});

	if (!sessionStorage.getItem('menu')) {
		$('#menu #dashboard').addClass('active');
	} else {
		// Sets active and open to selected page in the left column menu.
		$('#menu a[href=\'' + sessionStorage.getItem('menu') + '\']').parent().addClass('active');
	}

	$('#menu a[href=\'' + sessionStorage.getItem('menu') + '\']').parents('li > a').removeClass('collapsed');

	$('#menu a[href=\'' + sessionStorage.getItem('menu') + '\']').parents('ul.collapse').addClass('in show');
	// $(sessionStorage.getItem('menu')).addClass('in show');

	$('#menu a[href=\'' + sessionStorage.getItem('menu') + '\']').parents('li').addClass('active');

    // Image Manager
    $(document).on('click', 'a[data-toggle=\'image\']', function(e) {
        e.preventDefault();
        let $element = $(this);
        let image = $element.find('img').prop('src');

        $('#modal-upload-image').remove();

        let htmlModal = '<div class="modal fade" id="modal-upload-image" tabindex="-1" role="dialog">\n' +
            '    <div class="modal-dialog modal-side modal-top-left modal-notify modal-info" role="document">\n' +
            '      <div class="modal-content">\n' +
            '        <div class="modal-header">\n' +
            '            <h5><i class="fa fa-image"></i> Image Manager</h5>\n' +
            '            <button type="button" class="close" data-dismiss="modal" aria-label="Close" class="m-0">\n' +
            '              <span aria-hidden="true">×</span>\n' +
            '            </button>\n' +
            '        </div>\n' +
            '        <div class="modal-body">\n' +
            '          <div class="d-flex justify-content-center align-items-center fw">\n' +
            '            <img src="' + image + '" class="img-fluid">\n' +
            '          </div>\n' +
            '        </div>\n' +
            '        <div class="modal-footer justify-content-center">\n' +
            '          <button type="button" class="btn btn-floating circle btn-primary" id="button-image">\n' +
            '            <i class="fa fa-image fa-2x"></i>\n' +
            '          </button>\n' +
            '          <button type="button" class="btn btn-floating circle btn-danger" id="button-clear">\n' +
            '            <i class="fa fa-trash-o fa-2x"></i>\n' +
            '          </button>\n' +
            '        </div>\n' +
            '      </div>\n' +
            '    </div>\n' +
            '  </div>';

        $('body').append(htmlModal);
        $('#modal-upload-image').modal();

        $('#button-image').on('click', function() {
            let $button = $(this);
            let $icon   = $button.find('> i');

            $('#modal-image').remove();

            $.ajax({
                url: 'index.php?route=common/filemanager&user_token=' + getURLVar('user_token') + '&target=' + $element.parent().find('input').attr('id') + '&thumb=' + $element.attr('id'),
                dataType: 'html',
                beforeSend: function() {
                    $button.prop('disabled', true);
                    if ($icon.length) {
                        $icon.attr('class', 'fa fa-cog fa-spin text-white');
                    }
                },
                complete: function() {
                    $button.prop('disabled', false);

                    if ($icon.length) {
                        $icon.attr('class', 'fa fa-pen text-white');
                    }
                },
                success: function(html) {
                    $('body').append('<div id="modal-image" class="modal fade" tabindex="-1" role="dialog">' + html + '</div>');

                    $('#modal-image').modal('show');
                }
            });

            $('#modal-upload-image').modal('hide');
        });

        $('#button-clear').on('click', function() {
            let img = $element.find('img');
            img.prop('src', img.data('placeholder'));
            $element.parent().find('input').val('');

            $('#modal-upload-image').modal('hide');
        });
    });

    //Dropdowns overflow fix in tables
	$('.table').on('show.bs.dropdown', function () {
		$('.table-responsive').css( "overflow", "inherit" );
	});

	$('.table').on('hide.bs.dropdown', function () {
		$('.table-responsive').css( "overflow", "auto" );
	});

	$('.table .btn.dropdown-toggle.btn-floating.circle').removeClass('btn-floating circle btn-sm');
});

// Autocomplete */
(function($) {
	$.fn.autocomplete = function(option) {
		return this.each(function() {
			var $this = $(this);
			var $dropdown = $('<div class="dropdown-menu autocomplete-dropdown"><div class="dropdown-item"><i class="fa fa-spin fa-circle-o-notch"></i></div></div>');

			this.timer = null;
			this.items = [];

			$.extend(this, option);

            $this.parent().addClass('dropdown');
			$this.attr('autocomplete', 'off');
            $this.attr('data-toggle', 'dropdown');

			// Focus
			$this.on('focus', function() {
				this.request();
			});

			// Keydown
			$this.on('keydown', function(event) {
				switch(event.keyCode) {
					case 27: // escape
						// this.hide();
						break;
					default:
						this.request();
						break;
				}
			});

			// Click
			this.click = function(event) {
				event.preventDefault();
				var value = $(event.target).data('value');

				if (value && this.items[value]) {
					this.select(this.items[value]);
				}

                $dropdown.find('a').remove();
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
				var html = '';
				var category = {};
				var name;
				var i = 0, j = 0;

				if (json.length) {
					for (i = 0; i < json.length; i++) {
						// update element items
						this.items[json[i]['value']] = json[i];

						if (!json[i]['category']) {
							// ungrouped items
							html += '<a href="#" class="dropdown-item" data-value="' + json[i]['value'] + '">' + json[i]['label'] + '</a>';
						} else {
							// grouped items
							name = json[i]['category'];
							if (!category[name]) {
								category[name] = [];
							}

							category[name].push(json[i]);
						}
					}

					for (name in category) {
						html += '<div class="dropdown-header">' + name + '</div>';

						for (j = 0; j < category[name].length; j++) {
							html += '<a href="#" class="dropdown-item" data-value="' + category[name][j]['value'] + '">&nbsp;&nbsp;&nbsp;' + category[name][j]['label'] + '</a>';
						}
					}
				}

				$dropdown.html(html);

                $dropdown.on('click', '> a', $.proxy(this.click, this));

            }

			$this.after($dropdown);
		});
	}
})(window.jQuery);