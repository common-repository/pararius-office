
/* global mmx_require, jQuery, ParariusOfficeL10n, google, ajaxurl */

mmx_require(['dom', 'magnify', 'popup'], function(dom, Magnify, Popup) {
	jQuery(function($, window) {
		function l10n(key) {
			return ParariusOfficeL10n[key] || key;
		}

		function isValidEmailAddress(emailAddress) {
			var pattern = new RegExp(/^([a-zA-Z0-9_.\-])+@([a-zA-Z0-9_.\-])+\.([a-zA-Z])+([a-zA-Z])+/);
			return pattern.test(emailAddress);
		}

		$('.parariusoffice-searchform.forsale-enabled, .parariusoffice-quick-search.forsale-enabled').each(function() {
			var that = $(this);

			that.find('input[name="for-sale"]').on('change', function() {
				that.find('.criterium.min-price, .criterium.max-price').toggle($(this).val() != '1');
				that.find('.criterium.forsale-min-price, .criterium.forsale-max-price').toggle($(this).val() == '1');

				if ($(this).val() == '1') {
					that.find('.criterium.min-price select, .criterium.max-price select').val('');
				} else if ($(this).val() == '0') {
					that.find('.criterium.forsale-min-price select, .criterium.forsale-max-price select').val('');
				}
			});

			that.find('input#for-sale-0').prop('checked', true).trigger('change');
		});

		$('.parariusoffice-searchform, .parariusoffice-quick-search').each(function() {
			var that = $(this);
			var optgroups = {};

			that.find('#district optgroup').each(function() {
				optgroups[$(this).attr('label').toLowerCase()] = $(this).clone();
			});

			that.find('#city').on('change', function() {
				var city = $(this).val();
				that.find('#district optgroup').remove();

				if (city) {
					var dis = optgroups[city.toLowerCase()];
					if (dis) {
						that.find('#district').append(dis.clone()).parent().show();
					} else {
						that.find('#district').parent().hide();
					}
				} else {
					$.each(optgroups, function(i, optgroup) {
						that.find('#district').append(optgroup.clone()).parent().show();
					});
				}
			}).trigger('change');
		});

		$('ul.parariusoffice-maps-overview').each(function() {
			var list = $(this).hide();
			var canvas = $('<div class="parariusoffice-maps-overview-canvas"/>').insertAfter(list);
			var bounds = new google.maps.LatLngBounds();
			var map = new google.maps.Map(canvas[0], {
				zoom: 13,
				center: new google.maps.LatLng(52.36464, 4.88453),
				mapTypeId: google.maps.MapTypeId.ROADMAP,
			});

			list.find('li').each(function() {
				var li = $(this);
				var coor = new google.maps.LatLng(li.data('lat'), li.data('lng'));
				var marker = new google.maps.Marker({
					position: coor,
					title: li.data('title'),
				});

				google.maps.event.addListener(marker, 'click', function() {
					window.location = li.find('a').attr('href');
				});

				marker.setMap(map);
				bounds.extend(coor);
			});

			map.fitBounds(bounds);
		});

		$('.parariusoffice-property .controls').each(function() {
			var controls = $(this);

			controls.find('a.contact-about-house-button, a.mail-a-friend').on('click', function() {
				var $this = $(this);
				var popup = $this.data('popup');
				var originalContent = $($this.attr('href')).html();

				if (!popup) {
					popup = new Popup();
					$this.data('popup', popup);
				}

				popup.clear().loading(true).open(originalContent);

				return false;
			});

			$(document).on('submit', '.contact-about-house-form-form', function() {
				var form = $(this);
				var errors = [];

				form.find('.response').html('').hide();

				if (form.find('input[name="last_name"]').val() === '') {
					errors.push(l10n('please_enter_your_name'));
				}

				if (!isValidEmailAddress(form.find('input[name="emailaddress"]').val())) {
					errors.push(l10n('please_enter_a_valid_emailadress'));
				}

				if (errors.length) {
					form.find('.response').html(errors.join('<br>')).show();
				} else {
					$.post(ajaxurl, {
						action: 'parariusoffice_contact',
						id: form.find('input[name="id"]').val(),
						last_name: form.find('input[name="last_name"]').val(),
						emailaddress: form.find('input[name="emailaddress"]').val(),
						telephone: form.find('input[name="telephone"]').val(),
						message: form.find('textarea[name="message"]').val(),
					}, function(data) {
						if (data.error) {
							form.find('.response').html(l10n('error_occurred_please_review')).show();

							if (data.messages && data.messages.length) {
								form.find('.response').html(data.messages.join('<br>')).show();
							}
						} else {
							form.find('.response').html(data.message).show();
							form.find('dl, span').hide();
						}
					}, 'json');
				}

				return false;
			});

			$(document).on('submit', '.mail-to-friend-form-form', function() {
				var form = $(this);
				var errors = [];

				form.find('.response').html('').hide();

				if (form.find('input[name="sender_name"]').val() === '') {
					errors.push(l10n('please_enter_a_senders_name'));
				}

				if (!isValidEmailAddress(form.find('input[name="sender_email"]').val())) {
					errors.push(l10n('please_enter_a_valid_emailaddress_for_the_sender'));
				}

				if (form.find('input[name="friend_name"]').val() === '') {
					errors.push(l10n('please_enter_a_recipients_name'));
				}

				if (!isValidEmailAddress(form.find('input[name="friend_email"]').val())) {
					errors.push(l10n('please_enter_a_valid_emailaddress_for_the_recipient'));
				}

				if (errors.length) {
					form.find('.response').html(errors.join('<br>')).show();
				} else {
					$.post(ajaxurl, {
						action: 'parariusoffice_mail_a_friend',
						id: form.find('input[name="id"]').val(),
						url: form.find('input[name="url"]').val(),
						sender_name: form.find('input[name="sender_name"]').val(),
						sender_email: form.find('input[name="sender_email"]').val(),
						friend_name: form.find('input[name="friend_name"]').val(),
						friend_email: form.find('input[name="friend_email"]').val(),
						message: form.find('textarea[name="message"]').val(),
					}, function(data) {
						if (data.error) {
							form.find('.response').html(l10n('error_occurred_please_review')).show();

							if (data.messages && data.messages.length) {
								form.find('.response').html(data.messages.join('<br>')).show();
							}
						} else {
							form.find('.response').html(data.message).show();
							form.find('dl, span').hide();
						}
					}, 'json');
				}

				return false;
			});
		}); // controls

		$('.parariusoffice-property .photos').each(function() {
			var photos = $(this).css({
				position: 'relative',
				height: $(this).height(),
			});
			var ul = photos.find('ul').css('position', 'absolute');
			var max = ul.find('li:last').position().left + ul.find('li:last').width() - photos.width();
			var offset = 0;
			var gap = ul.find('li:eq(1)').position().left;

			var prev;
			var next;

			var handler = function(m) {
				if (m) {
					offset = offset - m * gap;
					offset = Math.min(0, offset);
					offset = Math.max(-max, offset);

					ul.animate({
						left: offset,
					});
				}

				prev.toggle(offset < 0);
				next.toggle(offset > -max);
			};

			prev = $('<div class="prev"/>').appendTo(photos).on('click', function(){handler(-2);});
			next = $('<div class="next"/>').appendTo(photos).on('click', function(){handler(2);});

			handler();

			photos.find('a').on('click', function() {
				Magnify.magnify(dom(photos.find('a').clone().toArray()), photos.find('a').index(this));
				return false;
			});
		});


		var locationInit = {
			maps: function initializeMaps(el, latLng) {
				var myOptions = {
					zoom: 13,
					center: latLng,
					mapTypeId: google.maps.MapTypeId.ROADMAP,
				};

				var map = new google.maps.Map(el, myOptions);
				new google.maps.Marker({
					position: latLng,
					map: map,
				});
			},
			streetview: function initializeStreetview(el, latLng) {
				var panoramaOptions = {
					position: latLng,
					pov: {
						heading: 0,
						pitch: 0,
						zoom: 0,
					},
				};

				new google.maps.StreetViewPanorama(el, panoramaOptions);
			},
		};

		$('.parariusoffice-property .location').each(function() {
			var location = $(this);
			var done = {maps: false, streetview: false};
			var latLng = new google.maps.LatLng(location.data('lat'), location.data('lng'));

			location.on('click', '.toggles a', function() {
				var what = $(this).attr('href').substr(1);

				if (!done[what]) {
					locationInit[what](location.find('.'+what)[0], latLng);
					done[what] = true;
				}

				location.find('.holder > div').hide();
				location.find('.'+what).show();
				location.find('.toggles a').removeClass('active');
				$(this).addClass('active');
				return false;
			}).find('a:eq(0)').trigger('click');
		});

		$('.parariusoffice-print-property').on('click', function() {
			var iframe = $('<iframe src="'+this.href+'" width="0" height="0"/>');
			iframe.appendTo('body');

			return false;
		});
	}(jQuery, this));
});
