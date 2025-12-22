<?php
/**
 * Trait pour la gestion des menus admin
 *
 * @package Blazing_Feedback
 * @since 1.9.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait de gestion des menus admin
 *
 * @since 1.9.0
 */
trait WPVFH_Admin_Menu {

	/**
	 * Ajouter les pages de menu admin
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public static function add_menu_pages() {
		// Page principale - Dashboard
		add_menu_page(
			__( 'Blazing Feedback', 'blazing-feedback' ),
			__( 'Feedbacks', 'blazing-feedback' ),
			'edit_feedbacks',
			'wpvfh-dashboard',
			array( __CLASS__, 'render_dashboard_page' ),
			'dashicons-format-chat',
			3 // Juste après Dashboard (position 2)
		);

		// Sous-page - Dashboard (redirection pour remplacer le titre auto)
		add_submenu_page(
			'wpvfh-dashboard',
			__( 'Tableau de bord', 'blazing-feedback' ),
			__( 'Tableau de bord', 'blazing-feedback' ),
			'edit_feedbacks',
			'wpvfh-dashboard',
			array( __CLASS__, 'render_dashboard_page' )
		);

		// Note: "Tous les feedbacks" est ajouté automatiquement par le CPT
		// avec show_in_menu => 'wpvfh-dashboard'

		// Sous-page - Paramètres
		add_submenu_page(
			'wpvfh-dashboard',
			__( 'Paramètres', 'blazing-feedback' ),
			__( 'Paramètres', 'blazing-feedback' ),
			'manage_feedback',
			'wpvfh-settings',
			array( __CLASS__, 'render_settings_page' )
		);
	}

	/**
	 * Retirer les séparateurs du menu admin
	 *
	 * @since 2.1.0
	 * @return void
	 */
	public static function remove_menu_separators() {
		global $menu;

		if ( ! is_array( $menu ) ) {
			return;
		}

		// Parcourir le menu et retirer tous les séparateurs
		foreach ( $menu as $key => $item ) {
			// Les séparateurs ont 'wp-menu-separator' comme classe CSS (index 4)
			// ou ont un slug vide/separator (index 2)
			if ( isset( $item[4] ) && strpos( $item[4], 'wp-menu-separator' ) !== false ) {
				unset( $menu[ $key ] );
			}
		}
	}
}
