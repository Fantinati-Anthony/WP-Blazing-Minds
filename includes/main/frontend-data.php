<?php
/**
 * Données passées au JavaScript frontend
 *
 * @package Blazing_Feedback
 * @since 1.7.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Obtenir les données pour le frontend JavaScript
 *
 * @since 1.0.0
 * @return array Données localisées
 */
function wpvfh_get_frontend_data() {
	$current_user = wp_get_current_user();

	// Préparer les groupes de métadonnées avec leurs paramètres
	$metadata_groups = wpvfh_get_metadata_groups_for_frontend();

	// Forme automatique selon la position (angle = quart de cercle, centre = demi-cercle)
	$button_position = WPVFH_Database::get_setting( 'wpvfh_button_position', 'bottom-right' );
	$corner_positions = array( 'bottom-right', 'bottom-left', 'top-right', 'top-left' );
	$auto_shape = in_array( $button_position, $corner_positions, true ) ? 'quarter' : 'half';

	// Obtenir le mode de thème
	$theme_mode = WPVFH_Database::get_setting( 'wpvfh_theme_mode', 'system' );

	/**
	 * Filtre les données passées au JavaScript frontend
	 *
	 * @since 1.0.0
	 * @param array $data Données localisées
	 */
	return apply_filters( 'wpvfh_frontend_data', array(
		'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
		'restUrl'        => rest_url( 'blazing-feedback/v1/' ),
		'restNonce'      => wp_create_nonce( 'wp_rest' ),
		'nonce'          => wp_create_nonce( 'wpvfh_nonce' ),
		'currentUrl'     => esc_url( home_url( add_query_arg( array() ) ) ),
		'userId'         => $current_user->ID,
		'userName'       => $current_user->display_name,
		'userEmail'      => $current_user->user_email,
		'isLoggedIn'     => is_user_logged_in(),
		'canCreate'      => current_user_can( 'publish_feedbacks' ),
		'canModerate'    => current_user_can( 'moderate_feedback' ),
		'canManage'      => current_user_can( 'manage_feedback' ),
		'pluginUrl'      => WPVFH_PLUGIN_URL,
		'screenshotEnabled' => wpvfh_is_screenshot_enabled(),
		'postFeedbackAction' => WPVFH_Database::get_setting( 'wpvfh_post_feedback_action', 'close' ),
		// Mode de thème
		'themeMode'      => $theme_mode,
		// Style du bouton
		'buttonStyle'    => array(
			'style'       => WPVFH_Database::get_setting( 'wpvfh_button_style', 'detached' ),
			'shape'       => $auto_shape,
			'size'        => absint( WPVFH_Database::get_setting( 'wpvfh_button_size', 56 ) ),
			'borderRadius'=> absint( WPVFH_Database::get_setting( 'wpvfh_button_border_radius', 50 ) ),
			'borderRadiusUnit' => WPVFH_Database::get_setting( 'wpvfh_button_border_radius_unit', 'percent' ),
			'margin'      => absint( WPVFH_Database::get_setting( 'wpvfh_button_margin', 20 ) ),
			'color'       => WPVFH_Database::get_setting( 'wpvfh_button_color', '#FE5100' ),
			'colorHover'  => WPVFH_Database::get_setting( 'wpvfh_button_color_hover', '#E04800' ),
		),
		// Couleurs mode clair
		'colorsLight'    => array(
			'primary'     => WPVFH_Database::get_setting( 'wpvfh_color_primary', '#FE5100' ),
			'primaryHover'=> WPVFH_Database::get_setting( 'wpvfh_color_primary_hover', '#E04800' ),
			'secondary'   => WPVFH_Database::get_setting( 'wpvfh_color_secondary', '#263e4b' ),
			'success'     => WPVFH_Database::get_setting( 'wpvfh_color_success', '#28a745' ),
			'warning'     => WPVFH_Database::get_setting( 'wpvfh_color_warning', '#ffc107' ),
			'danger'      => WPVFH_Database::get_setting( 'wpvfh_color_danger', '#dc3545' ),
			'text'        => WPVFH_Database::get_setting( 'wpvfh_color_text', '#263e4b' ),
			'textLight'   => WPVFH_Database::get_setting( 'wpvfh_color_text_light', '#5a7282' ),
			'bg'          => WPVFH_Database::get_setting( 'wpvfh_color_bg', '#ffffff' ),
			'bgLight'     => WPVFH_Database::get_setting( 'wpvfh_color_bg_light', '#f8f9fa' ),
			'border'      => WPVFH_Database::get_setting( 'wpvfh_color_border', '#e0e4e8' ),
			// Couleurs pin-item (cartes feedback)
			'pinItemBg'           => WPVFH_Database::get_setting( 'wpvfh_color_pin_item_bg', '#f8f9fa' ),
			'pinItemBgHover'      => WPVFH_Database::get_setting( 'wpvfh_color_pin_item_bg_hover', '#ffffff' ),
			'pinItemBgSelected'   => WPVFH_Database::get_setting( 'wpvfh_color_pin_item_bg_selected', '#fff5f3' ),
			'pinItemBorder'       => WPVFH_Database::get_setting( 'wpvfh_color_pin_item_border', '#e0e4e8' ),
			'pinItemBorderHover'  => WPVFH_Database::get_setting( 'wpvfh_color_pin_item_border_hover', '#FE5100' ),
			'pinItemText'         => WPVFH_Database::get_setting( 'wpvfh_color_pin_item_text', '#263e4b' ),
			'pinItemTextLight'    => WPVFH_Database::get_setting( 'wpvfh_color_pin_item_text_light', '#5a7282' ),
			// Couleurs footer
			'footerBg'                => WPVFH_Database::get_setting( 'wpvfh_color_footer_bg', '#f8f9fa' ),
			'footerBorder'            => WPVFH_Database::get_setting( 'wpvfh_color_footer_border', '#e9ecef' ),
			'footerBtnAddBg'          => WPVFH_Database::get_setting( 'wpvfh_color_footer_btn_add_bg', '#27ae60' ),
			'footerBtnAddText'        => WPVFH_Database::get_setting( 'wpvfh_color_footer_btn_add_text', '#ffffff' ),
			'footerBtnAddHover'       => WPVFH_Database::get_setting( 'wpvfh_color_footer_btn_add_hover', '#219a52' ),
			'footerBtnVisibilityBg'   => WPVFH_Database::get_setting( 'wpvfh_color_footer_btn_visibility_bg', '#3498db' ),
			'footerBtnVisibilityText' => WPVFH_Database::get_setting( 'wpvfh_color_footer_btn_visibility_text', '#ffffff' ),
			'footerBtnVisibilityHover'=> WPVFH_Database::get_setting( 'wpvfh_color_footer_btn_visibility_hover', '#2980b9' ),
		),
		// Couleurs mode sombre
		'colorsDark'     => array(
			'primary'     => WPVFH_Database::get_setting( 'wpvfh_color_primary', '#FE5100' ),
			'primaryHover'=> WPVFH_Database::get_setting( 'wpvfh_color_primary_hover', '#E04800' ),
			'secondary'   => WPVFH_Database::get_setting( 'wpvfh_color_secondary_dark', '#4a6572' ),
			'success'     => WPVFH_Database::get_setting( 'wpvfh_color_success', '#28a745' ),
			'warning'     => WPVFH_Database::get_setting( 'wpvfh_color_warning', '#ffc107' ),
			'danger'      => WPVFH_Database::get_setting( 'wpvfh_color_danger', '#dc3545' ),
			'text'        => WPVFH_Database::get_setting( 'wpvfh_color_text_dark', '#ffffff' ),
			'textLight'   => WPVFH_Database::get_setting( 'wpvfh_color_text_light_dark', '#b0bcc4' ),
			'bg'          => WPVFH_Database::get_setting( 'wpvfh_color_bg_dark', '#263e4b' ),
			'bgLight'     => WPVFH_Database::get_setting( 'wpvfh_color_bg_light_dark', '#334a5a' ),
			'border'      => WPVFH_Database::get_setting( 'wpvfh_color_border_dark', '#3d5564' ),
			// Couleurs pin-item (cartes feedback)
			'pinItemBg'           => WPVFH_Database::get_setting( 'wpvfh_color_pin_item_bg_dark', '#334a5a' ),
			'pinItemBgHover'      => WPVFH_Database::get_setting( 'wpvfh_color_pin_item_bg_hover_dark', '#3d5564' ),
			'pinItemBgSelected'   => WPVFH_Database::get_setting( 'wpvfh_color_pin_item_bg_selected_dark', '#4a3530' ),
			'pinItemBorder'       => WPVFH_Database::get_setting( 'wpvfh_color_pin_item_border_dark', '#3d5564' ),
			'pinItemBorderHover'  => WPVFH_Database::get_setting( 'wpvfh_color_pin_item_border_hover_dark', '#FE5100' ),
			'pinItemText'         => WPVFH_Database::get_setting( 'wpvfh_color_pin_item_text_dark', '#ffffff' ),
			'pinItemTextLight'    => WPVFH_Database::get_setting( 'wpvfh_color_pin_item_text_light_dark', '#b0bcc4' ),
			// Couleurs footer
			'footerBg'                => WPVFH_Database::get_setting( 'wpvfh_color_footer_bg_dark', '#1a2e38' ),
			'footerBorder'            => WPVFH_Database::get_setting( 'wpvfh_color_footer_border_dark', '#3d5564' ),
			'footerBtnAddBg'          => WPVFH_Database::get_setting( 'wpvfh_color_footer_btn_add_bg_dark', '#27ae60' ),
			'footerBtnAddText'        => WPVFH_Database::get_setting( 'wpvfh_color_footer_btn_add_text_dark', '#ffffff' ),
			'footerBtnAddHover'       => WPVFH_Database::get_setting( 'wpvfh_color_footer_btn_add_hover_dark', '#219a52' ),
			'footerBtnVisibilityBg'   => WPVFH_Database::get_setting( 'wpvfh_color_footer_btn_visibility_bg_dark', '#3498db' ),
			'footerBtnVisibilityText' => WPVFH_Database::get_setting( 'wpvfh_color_footer_btn_visibility_text_dark', '#ffffff' ),
			'footerBtnVisibilityHover'=> WPVFH_Database::get_setting( 'wpvfh_color_footer_btn_visibility_hover_dark', '#2980b9' ),
		),
		// Logos du panneau
		'panelLogos'     => array(
			'light'       => WPVFH_Database::get_setting( 'wpvfh_panel_logo_light_url', '' ) ?: WPVFH_PLUGIN_URL . 'assets/logo/light-mode-feedback.png',
			'dark'        => WPVFH_Database::get_setting( 'wpvfh_panel_logo_dark_url', '' ) ?: WPVFH_PLUGIN_URL . 'assets/logo/dark-mode-feedback.png',
		),
		// Métadonnées standards
		'statuses'       => WPVFH_CPT_Feedback::get_statuses(),
		'feedbackTypes'  => WPVFH_Options_Manager::get_types(),
		'priorities'     => WPVFH_Options_Manager::get_priorities(),
		'predefinedTags' => WPVFH_Options_Manager::get_predefined_tags(),
		// Groupes de métadonnées avec paramètres
		'metadataGroups' => $metadata_groups,
		'i18n'           => array(
			'feedbackButton'    => __( 'Donner un feedback', 'blazing-feedback' ),
			'closeButton'       => __( 'Fermer', 'blazing-feedback' ),
			'submitButton'      => __( 'Envoyer', 'blazing-feedback' ),
			'cancelButton'      => __( 'Annuler', 'blazing-feedback' ),
			'placeholder'       => __( 'Décrivez votre feedback...', 'blazing-feedback' ),
			'successMessage'    => __( 'Feedback envoyé avec succès !', 'blazing-feedback' ),
			'errorMessage'      => __( 'Erreur lors de l\'envoi du feedback.', 'blazing-feedback' ),
			'loadingMessage'    => __( 'Chargement...', 'blazing-feedback' ),
			'screenshotLabel'   => __( 'Capturer l\'écran', 'blazing-feedback' ),
			'clickToPin'        => __( 'Cliquez pour placer un marqueur', 'blazing-feedback' ),
			'modeEnabled'       => __( 'Mode feedback activé', 'blazing-feedback' ),
			'modeDisabled'      => __( 'Mode feedback désactivé', 'blazing-feedback' ),
			'replyPlaceholder'  => __( 'Votre réponse...', 'blazing-feedback' ),
			'statusNew'         => __( 'Nouveau', 'blazing-feedback' ),
			'statusInProgress'  => __( 'En cours', 'blazing-feedback' ),
			'statusResolved'    => __( 'Résolu', 'blazing-feedback' ),
			'statusRejected'    => __( 'Rejeté', 'blazing-feedback' ),
		),
	) );
}

/**
 * Obtenir tous les groupes de métadonnées pour le frontend
 *
 * Retourne les groupes standards et personnalisés avec leurs items et paramètres
 *
 * @since 1.7.0
 * @return array
 */
function wpvfh_get_metadata_groups_for_frontend() {
	$groups = array();
	$groups_with_order = array();

	// Récupérer l'ordre de tous les groupes
	$all_settings = WPVFH_Database::get_all_group_settings_ordered();

	// Groupes standards
	$standard_groups = array( 'statuses', 'types', 'priorities', 'tags' );

	foreach ( $standard_groups as $slug ) {
		$settings = WPVFH_Options_Manager::get_group_settings( $slug );

		// Vérifier l'accès de l'utilisateur
		if ( ! WPVFH_Options_Manager::user_can_access_group( $slug ) ) {
			continue;
		}

		$sort_order = isset( $all_settings[ $slug ]['sort_order'] ) ? $all_settings[ $slug ]['sort_order'] : 99;

		$groups_with_order[] = array(
			'slug'       => $slug,
			'name'       => wpvfh_get_group_label( $slug ),
			'type'       => 'standard',
			'sort_order' => $sort_order,
			'settings'   => array(
				'enabled'             => $settings['enabled'],
				'required'            => $settings['required'],
				'show_in_sidebar'     => $settings['show_in_sidebar'],
				'hide_empty_sections' => $settings['hide_empty_sections'],
			),
			'items'      => WPVFH_Options_Manager::get_items_by_type( $slug ),
		);
	}

	// Groupes personnalisés
	$custom_groups = WPVFH_Options_Manager::get_custom_groups();

	foreach ( $custom_groups as $slug => $group ) {
		$settings = WPVFH_Options_Manager::get_group_settings( $slug );

		// Vérifier l'accès de l'utilisateur
		if ( ! WPVFH_Options_Manager::user_can_access_group( $slug ) ) {
			continue;
		}

		$sort_order = isset( $all_settings[ $slug ]['sort_order'] ) ? $all_settings[ $slug ]['sort_order'] : 99;

		$groups_with_order[] = array(
			'slug'       => $slug,
			'name'       => $group['name'],
			'type'       => 'custom',
			'sort_order' => $sort_order,
			'settings'   => array(
				'enabled'             => $settings['enabled'],
				'required'            => $settings['required'],
				'show_in_sidebar'     => $settings['show_in_sidebar'],
				'hide_empty_sections' => $settings['hide_empty_sections'],
			),
			'items'      => WPVFH_Options_Manager::get_custom_group_items( $slug ),
		);
	}

	// Trier par sort_order
	usort( $groups_with_order, function( $a, $b ) {
		return $a['sort_order'] - $b['sort_order'];
	} );

	// Reconstruire le tableau associatif avec l'ordre correct
	foreach ( $groups_with_order as $group ) {
		$groups[ $group['slug'] ] = $group;
	}

	return $groups;
}

/**
 * Obtenir le label traduit d'un groupe standard
 *
 * @since 1.7.0
 * @param string $slug Slug du groupe
 * @return string
 */
function wpvfh_get_group_label( $slug ) {
	$labels = array(
		'statuses'   => __( 'Statuts', 'blazing-feedback' ),
		'types'      => __( 'Types', 'blazing-feedback' ),
		'priorities' => __( 'Priorités', 'blazing-feedback' ),
		'tags'       => __( 'Tags', 'blazing-feedback' ),
	);

	return isset( $labels[ $slug ] ) ? $labels[ $slug ] : $slug;
}
