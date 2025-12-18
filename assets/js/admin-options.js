/**
 * Admin Options Manager JavaScript
 *
 * @package WP_Visual_Feedback_Hub
 * @since 1.1.0
 */

(function($) {
    'use strict';

    var WPVFHOptionsAdmin = {
        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.initSortable();
            this.initColorPickers();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            var self = this;

            // Add new item
            $(document).on('click', '.wpvfh-add-item-btn', function() {
                self.addNewItem($(this).data('type'));
            });

            // Save item
            $(document).on('click', '.wpvfh-save-item-btn', function() {
                self.saveItem($(this).closest('.wpvfh-option-item'));
            });

            // Delete item
            $(document).on('click', '.wpvfh-delete-item-btn', function() {
                if (confirm(wpvfhOptionsAdmin.i18n.confirmDelete)) {
                    self.deleteItem($(this).closest('.wpvfh-option-item'));
                }
            });

            // Update preview on input change
            $(document).on('input change', '.wpvfh-emoji-input, .wpvfh-label-input', function() {
                self.updatePreview($(this).closest('.wpvfh-option-item'));
            });

            // Save on Enter key
            $(document).on('keypress', '.wpvfh-label-input, .wpvfh-emoji-input', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    self.saveItem($(this).closest('.wpvfh-option-item'));
                }
            });
        },

        /**
         * Initialize sortable
         */
        initSortable: function() {
            var self = this;

            $('.wpvfh-sortable-items').sortable({
                handle: '.wpvfh-drag-handle',
                placeholder: 'ui-sortable-placeholder',
                axis: 'y',
                update: function(event, ui) {
                    self.saveOrder($(this).closest('.wpvfh-options-table'));
                }
            });
        },

        /**
         * Initialize color pickers
         */
        initColorPickers: function() {
            var self = this;

            $('.wpvfh-color-input').each(function() {
                if (!$(this).hasClass('wp-color-picker')) {
                    $(this).wpColorPicker({
                        change: function(event, ui) {
                            var $row = $(this).closest('.wpvfh-option-item');
                            setTimeout(function() {
                                self.updatePreview($row);
                            }, 10);
                        }
                    });
                }
            });
        },

        /**
         * Add new item
         */
        addNewItem: function(type) {
            var template = $('#wpvfh-item-template-' + type).html();
            var $table = $('.wpvfh-options-table[data-type="' + type + '"]');
            var $tbody = $table.find('.wpvfh-sortable-items');

            $tbody.append(template);

            var $newRow = $tbody.find('.wpvfh-new-item').last();

            // Initialize color picker for new row
            $newRow.find('.wpvfh-color-input').wpColorPicker({
                change: function(event, ui) {
                    var $row = $(this).closest('.wpvfh-option-item');
                    setTimeout(function() {
                        WPVFHOptionsAdmin.updatePreview($row);
                    }, 10);
                }
            });

            // Focus on label input
            $newRow.find('.wpvfh-label-input').focus();

            // Scroll to new row
            $('html, body').animate({
                scrollTop: $newRow.offset().top - 100
            }, 300);
        },

        /**
         * Save item
         */
        saveItem: function($row) {
            var self = this;
            var $table = $row.closest('.wpvfh-options-table');
            var type = $table.data('type');
            var isNew = $row.hasClass('wpvfh-new-item');

            var data = {
                action: 'wpvfh_save_option_item',
                nonce: wpvfhOptionsAdmin.nonce,
                option_type: type,
                item_id: $row.data('id') || '',
                label: $row.find('.wpvfh-label-input').val(),
                color: $row.find('.wpvfh-color-input').val(),
                is_new: isNew ? 'true' : 'false'
            };

            // Add emoji if applicable
            var $emoji = $row.find('.wpvfh-emoji-input');
            if ($emoji.length) {
                data.emoji = $emoji.val();
            }

            // Validate
            if (!data.label.trim()) {
                alert('Le label est requis.');
                $row.find('.wpvfh-label-input').focus();
                return;
            }

            $row.addClass('saving');

            $.post(wpvfhOptionsAdmin.ajaxUrl, data, function(response) {
                $row.removeClass('saving');

                if (response.success) {
                    // Update row data
                    if (response.data && response.data.item) {
                        $row.data('id', response.data.item.id);
                        $row.attr('data-id', response.data.item.id);
                    }

                    $row.removeClass('wpvfh-new-item');
                    $row.addClass('saved');

                    setTimeout(function() {
                        $row.removeClass('saved');
                    }, 500);
                } else {
                    alert(response.data || wpvfhOptionsAdmin.i18n.error);
                }
            }).fail(function() {
                $row.removeClass('saving');
                alert(wpvfhOptionsAdmin.i18n.error);
            });
        },

        /**
         * Delete item
         */
        deleteItem: function($row) {
            var $table = $row.closest('.wpvfh-options-table');
            var type = $table.data('type');
            var itemId = $row.data('id');

            // If new item (not saved yet), just remove
            if ($row.hasClass('wpvfh-new-item') || !itemId) {
                $row.fadeOut(200, function() {
                    $(this).remove();
                });
                return;
            }

            $row.addClass('saving');

            $.post(wpvfhOptionsAdmin.ajaxUrl, {
                action: 'wpvfh_delete_option_item',
                nonce: wpvfhOptionsAdmin.nonce,
                option_type: type,
                item_id: itemId
            }, function(response) {
                if (response.success) {
                    $row.fadeOut(200, function() {
                        $(this).remove();
                    });
                } else {
                    $row.removeClass('saving');
                    alert(response.data || wpvfhOptionsAdmin.i18n.error);
                }
            }).fail(function() {
                $row.removeClass('saving');
                alert(wpvfhOptionsAdmin.i18n.error);
            });
        },

        /**
         * Save order
         */
        saveOrder: function($table) {
            var type = $table.data('type');
            var order = [];

            $table.find('.wpvfh-option-item').each(function() {
                var id = $(this).data('id');
                if (id) {
                    order.push(id);
                }
            });

            $.post(wpvfhOptionsAdmin.ajaxUrl, {
                action: 'wpvfh_save_options_order',
                nonce: wpvfhOptionsAdmin.nonce,
                option_type: type,
                order: order
            });
        },

        /**
         * Update preview badge
         */
        updatePreview: function($row) {
            var label = $row.find('.wpvfh-label-input').val() || 'Aperçu';
            var color = $row.find('.wpvfh-color-input').val() || '#666666';
            var $emoji = $row.find('.wpvfh-emoji-input');
            var emoji = $emoji.length ? $emoji.val() : '';

            var $preview = $row.find('.wpvfh-preview-badge');
            $preview.css({
                'background-color': color + '20',
                'color': color,
                'border-color': color + '40'
            });

            $preview.find('.wpvfh-preview-emoji').text(emoji);
            $preview.find('.wpvfh-preview-label').text(label);
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        WPVFHOptionsAdmin.init();
    });

})(jQuery);
