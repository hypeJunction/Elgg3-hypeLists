define(function (require) {

	var elgg = require('elgg');
	var $ = require('jquery');
	var spinner = require('elgg/spinner');
	var xhr;
	var Ajax = require('elgg/Ajax');
	var lightbox = require('elgg/lightbox');

	$(document).on('submit', '.elgg-form-sortable-list', function (e) {
		var $form = $(this);
		var $container = $($form.attr('rel'));

		if (xhr && xhr.readystate !== 4) {
			xhr.abort();
		}

		var ajax = new Ajax();

		xhr = ajax.path($form.attr('action'), {
			data: $form.serialize()
		}).done(function (output) {
			var id = $container.attr('id');
			var $new;

			if ($(output).is('#' + id)) {
				$new = $(output);
			} else {
				$new = $(output).find('#' + id);
			}

			if ($new.length === 0) {
				elgg.register_error(elgg.echo('sort:search:empty'));
			} else {
				var $list = $new.find('.elgg-sortable-list-view');
				$container.find('.elgg-sortable-list-view').replaceWith($list);
			}

			lightbox.resize();
		});

		return false;
	});

	$(document).on('click', '.elgg-sortable-list-form-toggle', function (e) {
		e.preventDefault();
		$(this).siblings('.elgg-sortable-list-form-container').toggleClass('hidden');
		$(this).remove();
		lightbox.resize();
	});

});
