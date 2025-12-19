<?php
/**
 * Trait pour la section Border Radius (Design)
 *
 * @package Blazing_Feedback
 * @since 1.9.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait de gestion de la section Border Radius
 *
 * @since 1.9.0
 */
trait WPVFH_Admin_Settings_Design_Border_Radius {

	/**
	 * Récupérer les valeurs par défaut des border-radius
	 *
	 * @since 1.9.0
	 * @return array
	 */
	public static function get_border_radius_defaults() {
		return array(
			'wpvfh_radius_panel'    => 12,
			'wpvfh_radius_general'  => 8,
			'wpvfh_radius_small'    => 4,
			'wpvfh_radius_pin_item' => 10,
			'wpvfh_radius_badge'    => 12,
			'wpvfh_radius_button'   => 8,
			'wpvfh_radius_input'    => 6,
			'wpvfh_radius_modal'    => 12,
		);
	}

	/**
	 * Rendre la section Border Radius
	 *
	 * @since 1.9.0
	 * @return void
	 */
	public static function render_design_border_radius_section() {
		$defaults = self::get_border_radius_defaults();

		$radius_settings = array(
			'wpvfh_radius_panel'    => array(
				'label'       => __( 'Panneau principal', 'blazing-feedback' ),
				'description' => __( 'Coins du panneau latéral', 'blazing-feedback' ),
				'icon'        => '📐',
				'max'         => 30,
			),
			'wpvfh_radius_general'  => array(
				'label'       => __( 'Éléments généraux', 'blazing-feedback' ),
				'description' => __( 'Cartes, formulaires, zones', 'blazing-feedback' ),
				'icon'        => '🔲',
				'max'         => 20,
			),
			'wpvfh_radius_small'    => array(
				'label'       => __( 'Petits éléments', 'blazing-feedback' ),
				'description' => __( 'Petits composants divers', 'blazing-feedback' ),
				'icon'        => '▪️',
				'max'         => 12,
			),
			'wpvfh_radius_pin_item' => array(
				'label'       => __( 'Cartes feedback', 'blazing-feedback' ),
				'description' => __( 'Éléments de liste des feedbacks', 'blazing-feedback' ),
				'icon'        => '📝',
				'max'         => 20,
			),
			'wpvfh_radius_badge'    => array(
				'label'       => __( 'Badges / Tags', 'blazing-feedback' ),
				'description' => __( 'Statuts, priorités, catégories', 'blazing-feedback' ),
				'icon'        => '🏷️',
				'max'         => 20,
			),
			'wpvfh_radius_button'   => array(
				'label'       => __( 'Boutons', 'blazing-feedback' ),
				'description' => __( 'Boutons d\'action', 'blazing-feedback' ),
				'icon'        => '🔘',
				'max'         => 20,
			),
			'wpvfh_radius_input'    => array(
				'label'       => __( 'Champs de saisie', 'blazing-feedback' ),
				'description' => __( 'Inputs, textareas, selects', 'blazing-feedback' ),
				'icon'        => '📝',
				'max'         => 16,
			),
			'wpvfh_radius_modal'    => array(
				'label'       => __( 'Modales / Tooltips', 'blazing-feedback' ),
				'description' => __( 'Fenêtres modales et infobulles', 'blazing-feedback' ),
				'icon'        => '💬',
				'max'         => 24,
			),
		);
		?>
		<div class="wpvfh-settings-section">
			<h2><?php esc_html_e( 'Arrondis des coins (Border Radius)', 'blazing-feedback' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Personnalisez les arrondis des différents éléments de l\'interface pour une cohérence graphique.', 'blazing-feedback' ); ?></p>

			<div class="wpvfh-border-radius-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; margin-top: 20px;">
				<?php foreach ( $radius_settings as $option_name => $setting ) :
					$value = get_option( $option_name, $defaults[ $option_name ] );
					$default = $defaults[ $option_name ];
				?>
				<div class="wpvfh-radius-item" style="background: #f9f9f9; border: 1px solid #e0e4e8; border-radius: 8px; padding: 16px;">
					<div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
						<span style="font-size: 20px;"><?php echo esc_html( $setting['icon'] ); ?></span>
						<div>
							<strong style="display: block; font-size: 14px;"><?php echo esc_html( $setting['label'] ); ?></strong>
							<small style="color: #666;"><?php echo esc_html( $setting['description'] ); ?></small>
						</div>
					</div>

					<div style="display: flex; align-items: center; gap: 12px;">
						<input
							type="range"
							name="<?php echo esc_attr( $option_name ); ?>"
							id="<?php echo esc_attr( $option_name ); ?>"
							value="<?php echo esc_attr( $value ); ?>"
							min="0"
							max="<?php echo esc_attr( $setting['max'] ); ?>"
							step="1"
							class="wpvfh-radius-slider"
							data-preview="<?php echo esc_attr( $option_name ); ?>"
							style="flex: 1; cursor: pointer;"
						>
						<div style="display: flex; align-items: center; gap: 4px;">
							<input
								type="number"
								value="<?php echo esc_attr( $value ); ?>"
								class="wpvfh-radius-number"
								data-slider="<?php echo esc_attr( $option_name ); ?>"
								min="0"
								max="<?php echo esc_attr( $setting['max'] ); ?>"
								style="width: 50px; text-align: center; padding: 4px; border: 1px solid #ddd; border-radius: 4px;"
							>
							<span style="color: #666; font-size: 12px;">px</span>
						</div>
						<button
							type="button"
							class="button button-small wpvfh-reset-radius"
							data-option="<?php echo esc_attr( $option_name ); ?>"
							data-default="<?php echo esc_attr( $default ); ?>"
							title="<?php esc_attr_e( 'Réinitialiser', 'blazing-feedback' ); ?>"
							style="padding: 0 6px; min-width: auto;"
						>
							<span class="dashicons dashicons-image-rotate" style="vertical-align: middle; margin-top: -2px; font-size: 14px; width: 14px; height: 14px;"></span>
						</button>
					</div>

					<!-- Preview box -->
					<div style="margin-top: 12px; display: flex; align-items: center; gap: 8px;">
						<span style="font-size: 11px; color: #888;"><?php esc_html_e( 'Aperçu:', 'blazing-feedback' ); ?></span>
						<div
							class="wpvfh-radius-preview"
							data-preview-for="<?php echo esc_attr( $option_name ); ?>"
							style="
								width: 40px;
								height: 40px;
								background: linear-gradient(135deg, var(--wpvfh-primary, #FE5100) 0%, #E04800 100%);
								border-radius: <?php echo esc_attr( $value ); ?>px;
								transition: border-radius 0.2s ease;
							"
						></div>
					</div>
				</div>
				<?php endforeach; ?>
			</div>

			<!-- Bouton réinitialiser tout -->
			<div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid #e0e4e8;">
				<button type="button" class="button wpvfh-reset-all-radius" id="wpvfh-reset-all-radius">
					<span class="dashicons dashicons-image-rotate" style="vertical-align: middle; margin-top: -3px;"></span>
					<?php esc_html_e( 'Réinitialiser tous les arrondis', 'blazing-feedback' ); ?>
				</button>
			</div>
		</div>

		<script>
		(function() {
			// Synchroniser sliders et inputs number
			document.querySelectorAll('.wpvfh-radius-slider').forEach(function(slider) {
				const optionName = slider.getAttribute('data-preview');
				const numberInput = document.querySelector('.wpvfh-radius-number[data-slider="' + optionName + '"]');
				const previewBox = document.querySelector('.wpvfh-radius-preview[data-preview-for="' + optionName + '"]');

				slider.addEventListener('input', function() {
					if (numberInput) numberInput.value = this.value;
					if (previewBox) previewBox.style.borderRadius = this.value + 'px';
				});

				if (numberInput) {
					numberInput.addEventListener('input', function() {
						const max = parseInt(slider.getAttribute('max'));
						let val = parseInt(this.value) || 0;
						if (val > max) val = max;
						if (val < 0) val = 0;
						this.value = val;
						slider.value = val;
						if (previewBox) previewBox.style.borderRadius = val + 'px';
					});
				}
			});

			// Boutons de réinitialisation individuels
			document.querySelectorAll('.wpvfh-reset-radius').forEach(function(btn) {
				btn.addEventListener('click', function() {
					const optionName = this.getAttribute('data-option');
					const defaultValue = this.getAttribute('data-default');
					const slider = document.getElementById(optionName);
					const numberInput = document.querySelector('.wpvfh-radius-number[data-slider="' + optionName + '"]');
					const previewBox = document.querySelector('.wpvfh-radius-preview[data-preview-for="' + optionName + '"]');

					if (slider) slider.value = defaultValue;
					if (numberInput) numberInput.value = defaultValue;
					if (previewBox) previewBox.style.borderRadius = defaultValue + 'px';
				});
			});

			// Bouton réinitialiser tout
			const resetAllBtn = document.getElementById('wpvfh-reset-all-radius');
			if (resetAllBtn) {
				resetAllBtn.addEventListener('click', function() {
					document.querySelectorAll('.wpvfh-reset-radius').forEach(function(btn) {
						btn.click();
					});
				});
			}
		})();
		</script>
		<?php
	}

	/**
	 * Générer le CSS inline pour les border-radius personnalisés
	 *
	 * @since 1.9.0
	 * @return string
	 */
	public static function get_custom_border_radius_css() {
		$defaults = self::get_border_radius_defaults();

		$values = array();
		$has_custom = false;

		foreach ( $defaults as $option_name => $default ) {
			$value = get_option( $option_name, $default );
			$values[ $option_name ] = intval( $value );
			if ( intval( $value ) !== $default ) {
				$has_custom = true;
			}
		}

		if ( ! $has_custom ) {
			return '';
		}

		$css = ':root {';
		$css .= '--wpvfh-radius-panel: ' . $values['wpvfh_radius_panel'] . 'px;';
		$css .= '--wpvfh-radius: ' . $values['wpvfh_radius_general'] . 'px;';
		$css .= '--wpvfh-radius-sm: ' . $values['wpvfh_radius_small'] . 'px;';
		$css .= '--wpvfh-radius-pin-item: ' . $values['wpvfh_radius_pin_item'] . 'px;';
		$css .= '--wpvfh-radius-badge: ' . $values['wpvfh_radius_badge'] . 'px;';
		$css .= '--wpvfh-radius-button: ' . $values['wpvfh_radius_button'] . 'px;';
		$css .= '--wpvfh-radius-input: ' . $values['wpvfh_radius_input'] . 'px;';
		$css .= '--wpvfh-radius-modal: ' . $values['wpvfh_radius_modal'] . 'px;';
		$css .= '}';

		return $css;
	}
}
