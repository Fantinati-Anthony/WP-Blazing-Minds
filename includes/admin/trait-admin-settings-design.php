<?php
/**
 * Trait pour l'onglet de personnalisation (Design) - Orchestrateur
 *
 * @package Blazing_Feedback
 * @since 1.9.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Charger les sous-traits
require_once __DIR__ . '/trait-admin-settings-design-theme-mode.php';
require_once __DIR__ . '/trait-admin-settings-design-button.php';
require_once __DIR__ . '/trait-admin-settings-design-colors.php';
require_once __DIR__ . '/trait-admin-settings-design-border-radius.php';
require_once __DIR__ . '/trait-admin-settings-design-preview.php';

/**
 * Trait de gestion de l'onglet Design et des couleurs
 *
 * @since 1.9.0
 */
trait WPVFH_Admin_Settings_Design {

	use WPVFH_Admin_Settings_Design_Theme_Mode;
	use WPVFH_Admin_Settings_Design_Button;
	use WPVFH_Admin_Settings_Design_Colors;
	use WPVFH_Admin_Settings_Design_Border_Radius;
	use WPVFH_Admin_Settings_Design_Preview;

	/**
	 * Rendre l'onglet Design complet
	 *
	 * @since 1.9.0
	 * @return void
	 */
	public static function render_tab_design() {
		// Récupération des options
		$options = self::get_design_options();

		// Section Mode du thème
		self::render_design_theme_mode_section( $options );

		// Container avec prévisualisation à droite
		?>
		<div class="wpvfh-preview-container">
			<div class="wpvfh-preview-settings">
				<?php
				// Section Position
				self::render_design_position_section();

				// Section Bouton flottant
				self::render_design_floating_button_section( $options );

				// Section Couleurs du thème
				self::render_design_colors_section();

				// Section Border Radius (Arrondis)
				self::render_design_border_radius_section();
				?>
			</div>

			<?php
			// Section Prévisualisation
			self::render_design_preview_section( $options );
			?>
		</div>

		<?php
		// Styles de prévisualisation
		self::render_design_preview_styles( $options );

		// Scripts de prévisualisation
		self::render_design_preview_scripts( $options );
	}

	/**
	 * Récupérer toutes les options de design
	 *
	 * @since 1.9.0
	 * @return array
	 */
	private static function get_design_options() {
		$button_position = WPVFH_Database::get_setting( 'wpvfh_button_position', 'bottom-right' );
		$corner_positions = array( 'bottom-right', 'bottom-left', 'top-right', 'top-left' );

		return array(
			// Mode du thème
			'theme_mode'           => WPVFH_Database::get_setting( 'wpvfh_theme_mode', 'system' ),

			// Icônes mode clair
			'light_icon_type'      => WPVFH_Database::get_setting( 'wpvfh_light_icon_type', 'emoji' ),
			'light_icon_emoji'     => WPVFH_Database::get_setting( 'wpvfh_light_icon_emoji', '💬' ),
			'light_icon_url'       => WPVFH_Database::get_setting( 'wpvfh_light_icon_url', '' ),

			// Icônes mode sombre
			'dark_icon_type'       => WPVFH_Database::get_setting( 'wpvfh_dark_icon_type', 'emoji' ),
			'dark_icon_emoji'      => WPVFH_Database::get_setting( 'wpvfh_dark_icon_emoji', '💬' ),
			'dark_icon_url'        => WPVFH_Database::get_setting( 'wpvfh_dark_icon_url', '' ),

			// URLs par défaut
			'default_light_icon'   => WPVFH_PLUGIN_URL . 'assets/logo/light-mode-feedback.png',
			'default_dark_icon'    => WPVFH_PLUGIN_URL . 'assets/logo/dark-mode-feedback.png',

			// Logos du panneau
			'panel_logo_light_url' => WPVFH_Database::get_setting( 'wpvfh_panel_logo_light_url', '' ),
			'panel_logo_dark_url'  => WPVFH_Database::get_setting( 'wpvfh_panel_logo_dark_url', '' ),

			// Bouton
			'button_color'         => WPVFH_Database::get_setting( 'wpvfh_button_color', '#FE5100' ),
			'button_color_hover'   => WPVFH_Database::get_setting( 'wpvfh_button_color_hover', '#E04800' ),
			'button_size'          => WPVFH_Database::get_setting( 'wpvfh_button_size', 56 ),
			'button_style'         => WPVFH_Database::get_setting( 'wpvfh_button_style', 'detached' ),
			'button_position'      => $button_position,
			'border_radius'        => WPVFH_Database::get_setting( 'wpvfh_button_border_radius', 50 ),
			'border_radius_unit'   => WPVFH_Database::get_setting( 'wpvfh_button_border_radius_unit', 'percent' ),
			'button_margin'        => WPVFH_Database::get_setting( 'wpvfh_button_margin', 20 ),

			// Bordure et ombre
			'button_border_width'  => WPVFH_Database::get_setting( 'wpvfh_button_border_width', 0 ),
			'button_border_color'  => WPVFH_Database::get_setting( 'wpvfh_button_border_color', '#ffffff' ),
			'button_shadow_blur'   => WPVFH_Database::get_setting( 'wpvfh_button_shadow_blur', 12 ),
			'button_shadow_opacity'=> WPVFH_Database::get_setting( 'wpvfh_button_shadow_opacity', 15 ),
			'button_shadow_color'  => WPVFH_Database::get_setting( 'wpvfh_button_shadow_color', '#000000' ),

			// Badge
			'badge_bg_color'       => WPVFH_Database::get_setting( 'wpvfh_badge_bg_color', '#263e4b' ),
			'badge_text_color'     => WPVFH_Database::get_setting( 'wpvfh_badge_text_color', '#ffffff' ),

			// Helper
			'is_corner'            => in_array( $button_position, $corner_positions, true ),
		);
	}

	/**
	 * Obtenir les couleurs du thème personnalisées
	 *
	 * @since 1.8.0
	 * @return array
	 */
	public static function get_theme_colors() {
		return array(
			'primary'       => WPVFH_Database::get_setting( 'wpvfh_color_primary', '#e74c3c' ),
			'primary_hover' => WPVFH_Database::get_setting( 'wpvfh_color_primary_hover', '#c0392b' ),
			'secondary'     => WPVFH_Database::get_setting( 'wpvfh_color_secondary', '#3498db' ),
			'success'       => WPVFH_Database::get_setting( 'wpvfh_color_success', '#27ae60' ),
			'warning'       => WPVFH_Database::get_setting( 'wpvfh_color_warning', '#f39c12' ),
			'danger'        => WPVFH_Database::get_setting( 'wpvfh_color_danger', '#e74c3c' ),
			'text'          => WPVFH_Database::get_setting( 'wpvfh_color_text', '#333333' ),
			'text_light'    => WPVFH_Database::get_setting( 'wpvfh_color_text_light', '#666666' ),
			'bg'            => WPVFH_Database::get_setting( 'wpvfh_color_bg', '#ffffff' ),
			'bg_light'      => WPVFH_Database::get_setting( 'wpvfh_color_bg_light', '#f5f5f5' ),
			'border'        => WPVFH_Database::get_setting( 'wpvfh_color_border', '#dddddd' ),
		);
	}

	/**
	 * Générer le CSS inline pour les couleurs personnalisées
	 *
	 * @since 1.8.0
	 * @return string
	 */
	public static function get_custom_colors_css() {
		$colors = self::get_theme_colors();

		$defaults = array(
			'primary'       => '#e74c3c',
			'primary_hover' => '#c0392b',
			'secondary'     => '#3498db',
			'success'       => '#27ae60',
			'warning'       => '#f39c12',
			'danger'        => '#e74c3c',
			'text'          => '#333333',
			'text_light'    => '#666666',
			'bg'            => '#ffffff',
			'bg_light'      => '#f5f5f5',
			'border'        => '#dddddd',
		);

		$has_custom = false;
		foreach ( $colors as $key => $value ) {
			if ( strtolower( $value ) !== strtolower( $defaults[ $key ] ) ) {
				$has_custom = true;
				break;
			}
		}

		if ( ! $has_custom ) {
			return '';
		}

		$css = ':root {';
		$css .= '--wpvfh-primary: ' . esc_attr( $colors['primary'] ) . ';';
		$css .= '--wpvfh-primary-hover: ' . esc_attr( $colors['primary_hover'] ) . ';';
		$css .= '--wpvfh-secondary: ' . esc_attr( $colors['secondary'] ) . ';';
		$css .= '--wpvfh-success: ' . esc_attr( $colors['success'] ) . ';';
		$css .= '--wpvfh-warning: ' . esc_attr( $colors['warning'] ) . ';';
		$css .= '--wpvfh-danger: ' . esc_attr( $colors['danger'] ) . ';';
		$css .= '--wpvfh-text: ' . esc_attr( $colors['text'] ) . ';';
		$css .= '--wpvfh-text-light: ' . esc_attr( $colors['text_light'] ) . ';';
		$css .= '--wpvfh-bg: ' . esc_attr( $colors['bg'] ) . ';';
		$css .= '--wpvfh-bg-light: ' . esc_attr( $colors['bg_light'] ) . ';';
		$css .= '--wpvfh-border: ' . esc_attr( $colors['border'] ) . ';';
		$css .= '}';

		return $css;
	}
}
