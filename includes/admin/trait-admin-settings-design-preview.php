<?php
/**
 * Trait pour la section Preview et scripts (Design)
 *
 * @package Blazing_Feedback
 * @since 1.9.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait de gestion de la prévisualisation Design
 *
 * @since 1.9.0
 */
trait WPVFH_Admin_Settings_Design_Preview {

	/**
	 * Rendre la section de prévisualisation
	 *
	 * @since 1.9.0
	 * @param array $options Options actuelles.
	 * @return void
	 */
	public static function render_design_preview_section( $options ) {
		$light_icon_type = $options['light_icon_type'];
		$light_icon_emoji = $options['light_icon_emoji'];
		$light_icon_url = $options['light_icon_url'];
		$default_light_icon = $options['default_light_icon'];
		$button_color = $options['button_color'];
		$badge_bg_color = $options['badge_bg_color'];
		$badge_text_color = $options['badge_text_color'];
		?>
		<div class="wpvfh-preview-widget">
			<h3 style="margin-top: 0;"><?php esc_html_e( 'Prévisualisation', 'blazing-feedback' ); ?></h3>
			<div class="wpvfh-preview-box" id="wpvfh-preview-box">
				<!-- Bouton de feedback preview -->
				<div id="wpvfh-preview-button-wrapper">
					<div id="wpvfh-preview-button" class="wpvfh-preview-btn">
						<span id="wpvfh-preview-icon">
							<?php if ( 'emoji' === $light_icon_type ) : ?>
								<span id="wpvfh-preview-icon-emoji"><?php echo esc_html( $light_icon_emoji ); ?></span>
							<?php else : ?>
								<img src="<?php echo esc_url( $light_icon_url ? $light_icon_url : $default_light_icon ); ?>" alt="" id="wpvfh-preview-icon-img">
							<?php endif; ?>
						</span>
					</div>
					<!-- Compteur preview -->
					<div id="wpvfh-preview-badge">3</div>
				</div>
			</div>
			<p class="description" style="margin-top: 10px; text-align: center;">
				<?php esc_html_e( 'Cliquez sur le bouton pour voir l\'effet', 'blazing-feedback' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Rendre les styles de prévisualisation
	 *
	 * @since 1.9.0
	 * @param array $options Options actuelles.
	 * @return void
	 */
	public static function render_design_preview_styles( $options ) {
		$button_color = $options['button_color'];
		$badge_bg_color = $options['badge_bg_color'];
		$badge_text_color = $options['badge_text_color'];
		?>
		<style>
			.wpvfh-preview-box {
				background: #f0f0f1;
				border-radius: 8px;
				min-height: 350px;
				position: relative;
				overflow: hidden;
			}
			#wpvfh-preview-button-wrapper {
				position: absolute;
			}
			.wpvfh-preview-btn {
				display: flex;
				align-items: center;
				justify-content: center;
				cursor: pointer;
				transition: all 0.2s ease;
				background: <?php echo esc_attr( $button_color ); ?>;
			}
			.wpvfh-preview-btn:hover {
				filter: brightness(0.9);
			}
			.wpvfh-preview-btn.active {
				transform: rotate(45deg);
			}
			.wpvfh-preview-btn #wpvfh-preview-icon {
				font-size: 24px;
				color: #fff;
				transition: transform 0.2s ease;
				line-height: 1;
			}
			.wpvfh-preview-btn.active #wpvfh-preview-icon {
				transform: rotate(-45deg);
			}
			.wpvfh-preview-btn #wpvfh-preview-icon img {
				width: 28px;
				height: 28px;
				object-fit: contain;
				filter: brightness(0) invert(1);
			}
			#wpvfh-preview-badge {
				position: absolute;
				background: <?php echo esc_attr( $badge_bg_color ); ?>;
				color: <?php echo esc_attr( $badge_text_color ); ?>;
				font-size: 11px;
				font-weight: bold;
				padding: 2px 6px;
				border-radius: 10px;
				min-width: 18px;
				text-align: center;
				transition: all 0.2s ease;
			}
			.wpvfh-mode-option:hover {
				border-color: #FE5100 !important;
			}
		</style>
		<?php
	}

	/**
	 * Rendre les scripts de prévisualisation
	 *
	 * @since 1.9.0
	 * @param array $options Options actuelles.
	 * @return void
	 */
	public static function render_design_preview_scripts( $options ) {
		$default_light_icon = $options['default_light_icon'];
		$default_dark_icon = $options['default_dark_icon'];
		?>
		<script>
		jQuery(document).ready(function($) {
			var previewActive = false;
			var cornerPositions = ['bottom-right', 'bottom-left', 'top-right', 'top-left'];
			var defaultLightIcon = '<?php echo esc_js( $default_light_icon ); ?>';
			var defaultDarkIcon = '<?php echo esc_js( $default_dark_icon ); ?>';

			function getShapeFromPosition() {
				var position = $('#wpvfh_button_position').val();
				return cornerPositions.indexOf(position) !== -1 ? 'quarter' : 'half';
			}

			function updateAttachedStyleInfo() {
				var shape = getShapeFromPosition();
				var $icon = $('#wpvfh-attached-style-icon');
				var $desc = $('#wpvfh-attached-style-desc');

				if (shape === 'quarter') {
					$icon.css({'border-radius': '0 0 0 16px', 'width': '32px'});
					$desc.text('<?php echo esc_js( __( 'Quart de cercle (position d\'angle)', 'blazing-feedback' ) ); ?>');
				} else {
					$icon.css({'border-radius': '16px 0 0 16px', 'width': '16px'});
					$desc.text('<?php echo esc_js( __( 'Demi-cercle (position centrale)', 'blazing-feedback' ) ); ?>');
				}
			}

			function hexToRgba(hex, opacity) {
				var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
				if (result) {
					var r = parseInt(result[1], 16);
					var g = parseInt(result[2], 16);
					var b = parseInt(result[3], 16);
					return 'rgba(' + r + ',' + g + ',' + b + ',' + (opacity / 100) + ')';
				}
				return 'rgba(0,0,0,' + (opacity / 100) + ')';
			}

			function updateButtonPreview() {
				var style = $('input[name="wpvfh_button_style"]:checked').val();
				var position = $('#wpvfh_button_position').val() || 'bottom-right';
				var size = parseInt($('#wpvfh_button_size').val()) || 56;
				var color = $('#wpvfh_button_color').val() || '#FE5100';
				var borderWidth = parseInt($('#wpvfh_button_border_width').val()) || 0;
				var borderColor = $('#wpvfh_button_border_color').val() || '#ffffff';
				var shadowBlur = parseInt($('#wpvfh_button_shadow_blur').val()) || 12;
				var shadowOpacity = parseInt($('#wpvfh_button_shadow_opacity').val()) || 15;
				var shadowColor = $('#wpvfh_button_shadow_color').val() || '#000000';
				var badgeBgColor = $('#wpvfh_badge_bg_color').val() || '#263e4b';
				var badgeTextColor = $('#wpvfh_badge_text_color').val() || '#ffffff';

				var $btn = $('#wpvfh-preview-button');
				var $wrapper = $('#wpvfh-preview-button-wrapper');
				var $badge = $('#wpvfh-preview-badge');

				var boxShadow = '0 0 ' + shadowBlur + 'px ' + hexToRgba(shadowColor, shadowOpacity);
				var border = borderWidth > 0 ? borderWidth + 'px solid ' + borderColor : 'none';

				$wrapper.attr('style', 'position: absolute;');
				$btn.attr('style', '');

				$badge.css({
					'background': badgeBgColor,
					'color': badgeTextColor
				});

				if (style === 'attached') {
					var shape = getShapeFromPosition();
					var btnCss = {
						'background': color,
						'box-shadow': boxShadow,
						'border': border,
						'display': 'flex',
						'align-items': 'center',
						'justify-content': 'center',
						'cursor': 'pointer',
						'transition': 'all 0.2s ease'
					};
					var wrapperCss = {};
					var badgeCss = { 'bottom': 'auto', 'top': 'auto', 'left': 'auto', 'right': 'auto', 'transform': 'none' };

					if (shape === 'quarter') {
						btnCss.width = size + 'px';
						btnCss.height = size + 'px';

						switch(position) {
							case 'bottom-right':
								btnCss['border-radius'] = size + 'px 0 0 0';
								wrapperCss = { 'bottom': '0', 'right': '0' };
								badgeCss = { 'top': '-8px', 'bottom': 'auto', 'left': '50%', 'right': 'auto', 'transform': 'translateX(-50%)' };
								break;
							case 'bottom-left':
								btnCss['border-radius'] = '0 ' + size + 'px 0 0';
								wrapperCss = { 'bottom': '0', 'left': '0' };
								badgeCss = { 'top': '-8px', 'bottom': 'auto', 'left': '50%', 'right': 'auto', 'transform': 'translateX(-50%)' };
								break;
							case 'top-right':
								btnCss['border-radius'] = '0 0 0 ' + size + 'px';
								wrapperCss = { 'top': '0', 'right': '0' };
								badgeCss = { 'top': 'auto', 'bottom': '-8px', 'left': '50%', 'right': 'auto', 'transform': 'translateX(-50%)' };
								break;
							case 'top-left':
								btnCss['border-radius'] = '0 0 ' + size + 'px 0';
								wrapperCss = { 'top': '0', 'left': '0' };
								badgeCss = { 'top': 'auto', 'bottom': '-8px', 'left': '50%', 'right': 'auto', 'transform': 'translateX(-50%)' };
								break;
						}
					} else {
						var halfSize = size / 2;

						switch(position) {
							case 'bottom-center':
								btnCss.width = size + 'px';
								btnCss.height = halfSize + 'px';
								btnCss['border-radius'] = size + 'px ' + size + 'px 0 0';
								wrapperCss = { 'bottom': '0', 'left': '50%', 'transform': 'translateX(-50%)' };
								badgeCss = { 'top': '-8px', 'bottom': 'auto', 'left': '50%', 'right': 'auto', 'transform': 'translateX(-50%)' };
								break;
							case 'top-center':
								btnCss.width = size + 'px';
								btnCss.height = halfSize + 'px';
								btnCss['border-radius'] = '0 0 ' + size + 'px ' + size + 'px';
								wrapperCss = { 'top': '0', 'left': '50%', 'transform': 'translateX(-50%)' };
								badgeCss = { 'top': 'auto', 'bottom': '-8px', 'left': '50%', 'right': 'auto', 'transform': 'translateX(-50%)' };
								break;
							case 'middle-left':
								btnCss.width = halfSize + 'px';
								btnCss.height = size + 'px';
								btnCss['border-radius'] = '0 ' + size + 'px ' + size + 'px 0';
								wrapperCss = { 'top': '50%', 'left': '0', 'transform': 'translateY(-50%)' };
								badgeCss = { 'top': '-8px', 'bottom': 'auto', 'left': 'auto', 'right': '-8px', 'transform': 'none' };
								break;
							case 'middle-right':
								btnCss.width = halfSize + 'px';
								btnCss.height = size + 'px';
								btnCss['border-radius'] = size + 'px 0 0 ' + size + 'px';
								wrapperCss = { 'top': '50%', 'right': '0', 'transform': 'translateY(-50%)' };
								badgeCss = { 'top': '-8px', 'bottom': 'auto', 'left': '-8px', 'right': 'auto', 'transform': 'none' };
								break;
						}
					}

					$btn.css(btnCss);
					$wrapper.css(wrapperCss);
					$badge.attr('style', '').css(badgeCss);
				} else {
					var radius = $('#wpvfh_button_border_radius').val() || 50;
					var unit = $('#wpvfh_button_border_radius_unit').val() || 'percent';
					var margin = parseInt($('#wpvfh_button_margin').val()) || 20;
					var radiusValue = radius + (unit === 'percent' ? '%' : 'px');

					$btn.css({
						'width': size + 'px',
						'height': size + 'px',
						'border-radius': radiusValue,
						'box-shadow': boxShadow,
						'border': border,
						'background': color,
						'display': 'flex',
						'align-items': 'center',
						'justify-content': 'center',
						'cursor': 'pointer',
						'transition': 'all 0.2s ease'
					});

					var wrapperCss = {};
					var badgeCss = { 'top': '-5px', 'right': '-5px', 'left': 'auto', 'bottom': 'auto', 'transform': 'none' };

					switch(position) {
						case 'bottom-right':
							wrapperCss = { 'bottom': margin + 'px', 'right': margin + 'px' };
							break;
						case 'bottom-left':
							wrapperCss = { 'bottom': margin + 'px', 'left': margin + 'px' };
							break;
						case 'top-right':
							wrapperCss = { 'top': margin + 'px', 'right': margin + 'px' };
							break;
						case 'top-left':
							wrapperCss = { 'top': margin + 'px', 'left': margin + 'px' };
							break;
						case 'bottom-center':
							wrapperCss = { 'bottom': margin + 'px', 'left': '50%', 'transform': 'translateX(-50%)' };
							break;
						case 'top-center':
							wrapperCss = { 'top': margin + 'px', 'left': '50%', 'transform': 'translateX(-50%)' };
							break;
						case 'middle-left':
							wrapperCss = { 'top': '50%', 'left': margin + 'px', 'transform': 'translateY(-50%)' };
							break;
						case 'middle-right':
							wrapperCss = { 'top': '50%', 'right': margin + 'px', 'transform': 'translateY(-50%)' };
							break;
					}

					$wrapper.css(wrapperCss);
					$badge.attr('style', '').css(badgeCss);
				}
			}

			$('#wpvfh-preview-button').on('click', function() {
				previewActive = !previewActive;
				$(this).toggleClass('active', previewActive);
			});

			$('input[name="wpvfh_theme_mode"]').on('change', function() {
				$('.wpvfh-mode-option').css('border-color', '#ddd');
				$(this).closest('.wpvfh-mode-option').css('border-color', '#FE5100');
				updateMainIconPreview();
			});

			$('#wpvfh_button_position').on('change', function() {
				updateAttachedStyleInfo();
				updateButtonPreview();
			});

			$('input[name="wpvfh_button_style"]').on('change', function() {
				var style = $(this).val();
				if (style === 'attached') {
					$('#wpvfh-detached-options').slideUp();
				} else {
					$('#wpvfh-detached-options').slideDown();
				}
				updateButtonPreview();
			});

			$('#wpvfh_button_size').on('input', function() {
				$('#wpvfh_button_size_value').text($(this).val() + 'px');
				updateButtonPreview();
			});

			$('#wpvfh_button_border_width, #wpvfh_button_border_color, #wpvfh_button_shadow_blur, #wpvfh_button_shadow_color').on('input change', function() {
				updateButtonPreview();
			});

			$('#wpvfh_button_shadow_opacity').on('input', function() {
				$('#wpvfh_shadow_opacity_value').text($(this).val() + '%');
				updateButtonPreview();
			});

			$('#wpvfh_badge_bg_color, #wpvfh_badge_text_color').on('input change', function() {
				updateButtonPreview();
			});

			$('#wpvfh_button_border_radius, #wpvfh_border_radius_slider').on('input', function() {
				var val = $(this).val();
				$('#wpvfh_button_border_radius').val(val);
				$('#wpvfh_border_radius_slider').val(val);
				updateButtonPreview();
			});
			$('#wpvfh_button_border_radius_unit').on('change', function() {
				updateButtonPreview();
			});

			$('#wpvfh_button_margin, #wpvfh_margin_slider').on('input', function() {
				var val = $(this).val();
				$('#wpvfh_button_margin').val(val);
				$('#wpvfh_margin_slider').val(val);
				updateButtonPreview();
			});

			$('input[name="wpvfh_button_color"]').on('input change', function() {
				updateButtonPreview();
			});

			$('input[type="color"]').on('input change', function() {
				var optionName = $(this).attr('name');
				var hexInput = $('[data-color-input="' + optionName + '"]');
				hexInput.val($(this).val());
				updatePreview();
			});

			$('.wpvfh-color-hex-input').on('input change', function() {
				var optionName = $(this).data('color-input');
				var colorInput = $('#' + optionName);
				var value = $(this).val();
				if (/^#[0-9A-Fa-f]{6}$/.test(value)) {
					colorInput.val(value);
					updatePreview();
				}
			});

			$('.wpvfh-reset-color').on('click', function() {
				var optionName = $(this).data('option');
				var defaultValue = $(this).data('default');
				var colorInput = $('#' + optionName);
				var hexInput = $('[data-color-input="' + optionName + '"]');
				colorInput.val(defaultValue);
				hexInput.val(defaultValue);
				updatePreview();
				updateButtonPreview();
			});

			$('input[name="wpvfh_light_icon_type"]').on('change', function() {
				var type = $(this).val();
				if (type === 'emoji') {
					$('#wpvfh-light-emoji-input').show();
					$('#wpvfh-light-image-input').hide();
				} else {
					$('#wpvfh-light-emoji-input').hide();
					$('#wpvfh-light-image-input').show();
				}
				updateLightIconPreview();
				updateMainIconPreview();
			});

			$('input[name="wpvfh_dark_icon_type"]').on('change', function() {
				var type = $(this).val();
				if (type === 'emoji') {
					$('#wpvfh-dark-emoji-input').show();
					$('#wpvfh-dark-image-input').hide();
				} else {
					$('#wpvfh-dark-emoji-input').hide();
					$('#wpvfh-dark-image-input').show();
				}
				updateDarkIconPreview();
				updateMainIconPreview();
			});

			function updateLightIconPreview() {
				var type = $('input[name="wpvfh_light_icon_type"]:checked').val() || 'emoji';
				var $previewBox = $('#wpvfh-light-preview-box');

				if (type === 'emoji') {
					var emoji = $('#wpvfh_light_icon_emoji').val() || '💬';
					$previewBox.html('<span id="wpvfh-light-preview-content">' + emoji + '</span>');
				} else {
					var url = $('#wpvfh_light_icon_url').val() || defaultLightIcon;
					$previewBox.html('<img src="' + url + '" style="max-width: 26px; max-height: 26px; filter: brightness(0) invert(1);" id="wpvfh-light-preview-content">');
				}
			}

			function updateDarkIconPreview() {
				var type = $('input[name="wpvfh_dark_icon_type"]:checked').val() || 'emoji';
				var $previewBox = $('#wpvfh-dark-preview-box');

				if (type === 'emoji') {
					var emoji = $('#wpvfh_dark_icon_emoji').val() || '💬';
					$previewBox.html('<span id="wpvfh-dark-preview-content">' + emoji + '</span>');
				} else {
					var url = $('#wpvfh_dark_icon_url').val() || defaultDarkIcon;
					$previewBox.html('<img src="' + url + '" style="max-width: 26px; max-height: 26px; filter: brightness(0) invert(1);" id="wpvfh-dark-preview-content">');
				}
			}

			function updateMainIconPreview() {
				var themeMode = $('input[name="wpvfh_theme_mode"]:checked').val() || 'system';
				var $iconContainer = $('#wpvfh-preview-icon');
				var useLight = (themeMode === 'light' || themeMode === 'system');

				if (useLight) {
					var type = $('input[name="wpvfh_light_icon_type"]:checked').val() || 'emoji';
					if (type === 'emoji') {
						var emoji = $('#wpvfh_light_icon_emoji').val() || '💬';
						$iconContainer.html('<span id="wpvfh-preview-icon-emoji">' + emoji + '</span>');
					} else {
						var url = $('#wpvfh_light_icon_url').val() || defaultLightIcon;
						$iconContainer.html('<img src="' + url + '" id="wpvfh-preview-icon-img">');
					}
				} else {
					var type = $('input[name="wpvfh_dark_icon_type"]:checked').val() || 'emoji';
					if (type === 'emoji') {
						var emoji = $('#wpvfh_dark_icon_emoji').val() || '💬';
						$iconContainer.html('<span id="wpvfh-preview-icon-emoji">' + emoji + '</span>');
					} else {
						var url = $('#wpvfh_dark_icon_url').val() || defaultDarkIcon;
						$iconContainer.html('<img src="' + url + '" id="wpvfh-preview-icon-img">');
					}
				}
			}

			$('#wpvfh_light_icon_emoji').on('input', function() {
				updateLightIconPreview();
				updateMainIconPreview();
			});
			$('#wpvfh_dark_icon_emoji').on('input', function() {
				updateDarkIconPreview();
				updateMainIconPreview();
			});

			$('#wpvfh_light_icon_url').on('input change', function() {
				updateLightIconPreview();
				updateMainIconPreview();
			});
			$('#wpvfh_dark_icon_url').on('input change', function() {
				updateDarkIconPreview();
				updateMainIconPreview();
			});

			$('.wpvfh-select-icon').on('click', function(e) {
				e.preventDefault();
				var $button = $(this);
				var targetId = $button.data('target');
				var mode = $button.data('mode');

				var frame = wp.media({
					title: '<?php echo esc_js( __( 'Sélectionner une icône', 'blazing-feedback' ) ); ?>',
					button: { text: '<?php echo esc_js( __( 'Utiliser cette image', 'blazing-feedback' ) ); ?>' },
					multiple: false,
					library: { type: 'image' }
				});

				frame.on('select', function() {
					var attachment = frame.state().get('selection').first().toJSON();
					$('#' + targetId).val(attachment.url).trigger('change');
					if (mode === 'light') {
						updateLightIconPreview();
					} else {
						updateDarkIconPreview();
					}
					updateMainIconPreview();
				});

				frame.open();
			});

			$('.wpvfh-select-panel-logo').on('click', function(e) {
				e.preventDefault();
				var $button = $(this);
				var targetId = $button.data('target');
				var previewId = $button.data('preview');

				var frame = wp.media({
					title: '<?php echo esc_js( __( 'Sélectionner un logo pour le panneau', 'blazing-feedback' ) ); ?>',
					button: { text: '<?php echo esc_js( __( 'Utiliser ce logo', 'blazing-feedback' ) ); ?>' },
					multiple: false,
					library: { type: 'image' }
				});

				frame.on('select', function() {
					var attachment = frame.state().get('selection').first().toJSON();
					$('#' + targetId).val(attachment.url);
					$('#' + previewId).attr('src', attachment.url);
				});

				frame.open();
			});

			$('#wpvfh_panel_logo_light_url').on('input change', function() {
				var url = $(this).val() || defaultLightIcon;
				$('#wpvfh-panel-logo-light-preview').attr('src', url);
			});

			$('#wpvfh_panel_logo_dark_url').on('input change', function() {
				var url = $(this).val() || defaultDarkIcon;
				$('#wpvfh-panel-logo-dark-preview').attr('src', url);
			});

			function updatePreview() {
				var bgLight = $('#wpvfh_color_bg_light').val();
				$('#wpvfh-preview-box').css('background', bgLight);
				updateButtonPreview();
			}

			updateAttachedStyleInfo();
			updateButtonPreview();
			updateLightIconPreview();
			updateDarkIconPreview();
			updateMainIconPreview();
		});
		</script>
		<?php
	}
}
