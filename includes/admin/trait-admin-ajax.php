<?php
/**
 * Trait pour les handlers AJAX admin
 *
 * @package Blazing_Feedback
 * @since 1.9.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Trait de gestion des requêtes AJAX admin
 *
 * @since 1.9.0
 */
trait WPVFH_Admin_Ajax {

    /**
     * Mise à jour rapide du statut (AJAX)
     *
     * @since 1.0.0
     * @return void
     */
    public static function ajax_quick_status_update() {
        check_ajax_referer( 'wpvfh_nonce', 'nonce' );

        if ( ! current_user_can( 'moderate_feedback' ) ) {
            wp_send_json_error( __( 'Permission refusée.', 'blazing-feedback' ) );
        }

        $feedback_id = isset( $_POST['feedback_id'] ) ? absint( $_POST['feedback_id'] ) : 0;
        $status = isset( $_POST['status'] ) ? sanitize_key( $_POST['status'] ) : '';

        if ( ! $feedback_id || ! $status ) {
            wp_send_json_error( __( 'Données invalides.', 'blazing-feedback' ) );
        }

        update_post_meta( $feedback_id, '_wpvfh_status', $status );
        wp_set_object_terms( $feedback_id, $status, 'feedback_status' );

        $status_data = WPVFH_Options_Manager::get_status_by_id( $status );
        wp_send_json_success( array(
            'status' => $status,
            'label'  => $status_data ? $status_data['label'] : $status,
        ) );
    }

    /**
     * Masquer une notice (AJAX)
     *
     * @since 1.0.0
     * @return void
     */
    public static function ajax_dismiss_notice() {
        check_ajax_referer( 'wpvfh_nonce', 'nonce' );

        $notice_id = isset( $_POST['notice_id'] ) ? sanitize_key( $_POST['notice_id'] ) : '';

        if ( $notice_id ) {
            update_user_meta( get_current_user_id(), 'wpvfh_dismissed_' . $notice_id, true );
        }

        wp_send_json_success();
    }
}
