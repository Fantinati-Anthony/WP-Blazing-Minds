/**
 * Blazing Minds - Admin JavaScript
 *
 * @package Blazing_Minds
 * @since 1.0.0
 */

(function($) {
	'use strict';

	/**
	 * Blazing Minds Admin Module
	 */
	const BlazingMinds = {
		/**
		 * Initialize
		 */
		init: function() {
			this.bindEvents();
			this.initColorPicker();
			this.initTemperatureSlider();
			this.initConfirmDelete();
			this.initAjaxForms();
		},

		/**
		 * Bind events
		 */
		bindEvents: function() {
			// Toggle dependent fields
			$('[data-toggle-target]').on('change', this.handleToggle);

			// AI provider change
			$('#ai_provider').on('change', this.handleProviderChange);

			// Filter form auto-submit
			$('.bzmi-filter-form select').on('change', function() {
				$(this).closest('form').submit();
			});

			// Bulk actions
			$('#doaction, #doaction2').on('click', this.handleBulkAction);
		},

		/**
		 * Initialize color picker
		 */
		initColorPicker: function() {
			$('.bzmi-color-options').each(function() {
				const $container = $(this);
				const $input = $container.find('input[type="hidden"]');

				$container.find('.bzmi-color-option').on('click', function() {
					const color = $(this).data('color');
					$container.find('.bzmi-color-option').removeClass('selected');
					$(this).addClass('selected');
					$input.val(color);
				});

				// Set initial selection
				const currentColor = $input.val();
				if (currentColor) {
					$container.find(`[data-color="${currentColor}"]`).addClass('selected');
				}
			});
		},

		/**
		 * Initialize temperature slider
		 */
		initTemperatureSlider: function() {
			const $slider = $('#ai_temperature');
			const $value = $('#ai_temperature_value');

			if ($slider.length && $value.length) {
				$slider.on('input', function() {
					$value.text($(this).val());
				});
			}
		},

		/**
		 * Initialize confirm delete
		 */
		initConfirmDelete: function() {
			$('.bzmi-delete-link, .delete-link').on('click', function(e) {
				if (!confirm(bzmiData.strings.confirm_delete)) {
					e.preventDefault();
					return false;
				}
			});
		},

		/**
		 * Initialize AJAX forms
		 */
		initAjaxForms: function() {
			$('.bzmi-ajax-form').on('submit', function(e) {
				e.preventDefault();

				const $form = $(this);
				const $submit = $form.find('[type="submit"]');
				const originalText = $submit.text();

				$submit.prop('disabled', true).text(bzmiData.strings.loading);
				$form.addClass('bzmi-loading');

				$.ajax({
					url: bzmiData.restUrl + $form.data('endpoint'),
					method: $form.attr('method') || 'POST',
					data: $form.serialize(),
					headers: {
						'X-WP-Nonce': bzmiData.restNonce
					},
					success: function(response) {
						BlazingMinds.showNotice(bzmiData.strings.saved, 'success');
						if ($form.data('redirect')) {
							window.location.href = $form.data('redirect');
						}
					},
					error: function(xhr) {
						const message = xhr.responseJSON?.message || bzmiData.strings.error;
						BlazingMinds.showNotice(message, 'error');
					},
					complete: function() {
						$submit.prop('disabled', false).text(originalText);
						$form.removeClass('bzmi-loading');
					}
				});
			});
		},

		/**
		 * Handle toggle
		 */
		handleToggle: function() {
			const $checkbox = $(this);
			const target = $checkbox.data('toggle-target');
			const $target = $(target);

			if ($checkbox.is(':checked')) {
				$target.show();
			} else {
				$target.hide();
			}
		},

		/**
		 * Handle provider change
		 */
		handleProviderChange: function() {
			const provider = $(this).val();
			const $modelSelect = $('#ai_model');

			// Show only relevant model options
			$modelSelect.find('optgroup').hide();
			$modelSelect.find(`optgroup[label*="${provider}"]`).show();

			// Select first visible option
			const $firstVisible = $modelSelect.find('optgroup:visible option:first');
			if ($firstVisible.length) {
				$modelSelect.val($firstVisible.val());
			}
		},

		/**
		 * Handle bulk action
		 */
		handleBulkAction: function(e) {
			const action = $(this).prev('select').val();

			if (action === '-1') {
				e.preventDefault();
				alert('Veuillez sélectionner une action.');
				return false;
			}

			if (action === 'delete') {
				if (!confirm(bzmiData.strings.confirm_delete)) {
					e.preventDefault();
					return false;
				}
			}
		},

		/**
		 * Show notice
		 */
		showNotice: function(message, type) {
			type = type || 'success';

			const $notice = $(`
				<div class="notice notice-${type} is-dismissible">
					<p>${message}</p>
					<button type="button" class="notice-dismiss">
						<span class="screen-reader-text">Fermer</span>
					</button>
				</div>
			`);

			$('.wrap h1').first().after($notice);

			// Auto dismiss after 5 seconds
			setTimeout(function() {
				$notice.fadeOut(function() {
					$(this).remove();
				});
			}, 5000);

			// Handle dismiss button
			$notice.find('.notice-dismiss').on('click', function() {
				$notice.fadeOut(function() {
					$(this).remove();
				});
			});
		},

		/**
		 * API request helper
		 */
		api: function(endpoint, method, data) {
			return $.ajax({
				url: bzmiData.restUrl + endpoint,
				method: method || 'GET',
				data: data || {},
				headers: {
					'X-WP-Nonce': bzmiData.restNonce
				}
			});
		},

		/**
		 * Generate AI clarifications
		 */
		generateClarifications: function(informationId) {
			const $btn = $(`#generate-clarifications-${informationId}`);
			const originalText = $btn.text();

			$btn.prop('disabled', true).text(bzmiData.strings.loading);

			this.api(`ai/clarifications/${informationId}`)
				.done(function(response) {
					if (response.questions && response.questions.length) {
						BlazingMinds.displayClarifications(informationId, response.questions);
						BlazingMinds.showNotice('Clarifications générées avec succès !', 'success');
					} else {
						BlazingMinds.showNotice('Aucune clarification générée.', 'warning');
					}
				})
				.fail(function(xhr) {
					const message = xhr.responseJSON?.message || bzmiData.strings.error;
					BlazingMinds.showNotice(message, 'error');
				})
				.always(function() {
					$btn.prop('disabled', false).text(originalText);
				});
		},

		/**
		 * Display generated clarifications
		 */
		displayClarifications: function(informationId, questions) {
			const $container = $(`#clarifications-${informationId}`);

			questions.forEach(function(question, index) {
				const $item = $(`
					<div class="bzmi-clarification-item">
						<p><strong>Q${index + 1}:</strong> ${question}</p>
						<button type="button" class="button button-small add-clarification"
								data-question="${encodeURIComponent(question)}"
								data-information="${informationId}">
							Ajouter
						</button>
					</div>
				`);
				$container.append($item);
			});

			// Bind add button
			$container.find('.add-clarification').on('click', function() {
				const question = decodeURIComponent($(this).data('question'));
				const infoId = $(this).data('information');

				BlazingMinds.addClarification(infoId, question);
				$(this).closest('.bzmi-clarification-item').remove();
			});
		},

		/**
		 * Add clarification
		 */
		addClarification: function(informationId, question) {
			this.api('clarifications', 'POST', {
				information_id: informationId,
				question: question,
				ai_suggested: true
			})
			.done(function() {
				BlazingMinds.showNotice('Clarification ajoutée !', 'success');
			})
			.fail(function() {
				BlazingMinds.showNotice('Erreur lors de l\'ajout.', 'error');
			});
		},

		/**
		 * Suggest actions via AI
		 */
		suggestActions: function(informationId) {
			const $btn = $(`#suggest-actions-${informationId}`);
			const originalText = $btn.text();

			$btn.prop('disabled', true).text(bzmiData.strings.loading);

			this.api(`ai/actions/${informationId}`)
				.done(function(response) {
					if (response.actions && response.actions.length) {
						BlazingMinds.displaySuggestedActions(informationId, response.actions);
						BlazingMinds.showNotice('Actions suggérées !', 'success');
					} else {
						BlazingMinds.showNotice('Aucune action suggérée.', 'warning');
					}
				})
				.fail(function(xhr) {
					const message = xhr.responseJSON?.message || bzmiData.strings.error;
					BlazingMinds.showNotice(message, 'error');
				})
				.always(function() {
					$btn.prop('disabled', false).text(originalText);
				});
		},

		/**
		 * Display suggested actions
		 */
		displaySuggestedActions: function(informationId, actions) {
			const $container = $(`#actions-${informationId}`);

			actions.forEach(function(action, index) {
				const $item = $(`
					<div class="bzmi-action-suggestion">
						<h4>${action.title}</h4>
						<p>${action.description || ''}</p>
						<span class="bzmi-priority ${action.priority}">${action.priority}</span>
						<span class="bzmi-effort">${action.effort || ''}</span>
						<button type="button" class="button button-small add-action"
								data-action='${JSON.stringify(action)}'
								data-information="${informationId}">
							Créer cette action
						</button>
					</div>
				`);
				$container.append($item);
			});

			// Bind add button
			$container.find('.add-action').on('click', function() {
				const action = JSON.parse($(this).data('action'));
				const infoId = $(this).data('information');

				BlazingMinds.addAction(infoId, action);
				$(this).closest('.bzmi-action-suggestion').remove();
			});
		},

		/**
		 * Add action
		 */
		addAction: function(informationId, actionData) {
			this.api('actions', 'POST', {
				information_id: informationId,
				title: actionData.title,
				description: actionData.description,
				priority: actionData.priority,
				effort_estimate: actionData.effort,
				ai_suggested: true
			})
			.done(function() {
				BlazingMinds.showNotice('Action créée !', 'success');
			})
			.fail(function() {
				BlazingMinds.showNotice('Erreur lors de la création.', 'error');
			});
		},

		/**
		 * Advance ICAVAL stage
		 */
		advanceStage: function(informationId) {
			if (!confirm('Avancer à l\'étape suivante ?')) {
				return;
			}

			this.api(`informations/${informationId}/advance`, 'POST')
				.done(function(response) {
					BlazingMinds.showNotice('Étape avancée !', 'success');
					setTimeout(function() {
						location.reload();
					}, 1000);
				})
				.fail(function(xhr) {
					const message = xhr.responseJSON?.message || bzmiData.strings.error;
					BlazingMinds.showNotice(message, 'error');
				});
		}
	};

	// Initialize on document ready
	$(document).ready(function() {
		BlazingMinds.init();
	});

	// Expose to global scope
	window.BlazingMinds = BlazingMinds;

})(jQuery);
