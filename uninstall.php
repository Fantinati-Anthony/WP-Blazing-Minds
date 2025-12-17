<?php
/**
 * Blazing Feedback - Script de désinstallation
 *
 * Nettoie toutes les données du plugin lors de la désinstallation
 * Supprime : CPT, taxonomies, options, rôles, fichiers uploadés
 *
 * @package Blazing_Feedback
 * @since 1.0.0
 */

// Sécurité : vérifier que WordPress appelle ce fichier
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}

/**
 * Nettoyer les données du plugin
 *
 * @since 1.0.0
 * @return void
 */
function wpvfh_uninstall_cleanup() {
    global $wpdb;

    // =========================================================================
    // 1. Supprimer tous les feedbacks (posts)
    // =========================================================================
    $feedbacks = get_posts( array(
        'post_type'      => 'visual_feedback',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ) );

    foreach ( $feedbacks as $feedback_id ) {
        // Supprimer les screenshots attachés
        $screenshot_id = get_post_meta( $feedback_id, '_wpvfh_screenshot_id', true );
        if ( $screenshot_id ) {
            wp_delete_attachment( $screenshot_id, true );
        }

        // Supprimer le post et ses métadonnées
        wp_delete_post( $feedback_id, true );
    }

    // =========================================================================
    // 2. Supprimer les termes de taxonomie
    // =========================================================================
    $taxonomies = array( 'feedback_status', 'feedback_page' );

    foreach ( $taxonomies as $taxonomy ) {
        $terms = get_terms( array(
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'fields'     => 'ids',
        ) );

        if ( ! is_wp_error( $terms ) ) {
            foreach ( $terms as $term_id ) {
                wp_delete_term( $term_id, $taxonomy );
            }
        }
    }

    // =========================================================================
    // 3. Supprimer les options
    // =========================================================================
    $options = array(
        'wpvfh_version',
        'wpvfh_screenshot_enabled',
        'wpvfh_guest_feedback',
        'wpvfh_button_position',
        'wpvfh_button_color',
        'wpvfh_enabled_pages',
        'wpvfh_email_notifications',
        'wpvfh_notification_email',
        'wpvfh_show_welcome_notice',
    );

    foreach ( $options as $option ) {
        delete_option( $option );
    }

    // =========================================================================
    // 4. Supprimer les métadonnées utilisateur
    // =========================================================================
    $wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'wpvfh_%'" );

    // =========================================================================
    // 5. Supprimer les rôles personnalisés
    // =========================================================================
    $roles_to_remove = array( 'feedback_client', 'feedback_member', 'feedback_admin' );

    foreach ( $roles_to_remove as $role ) {
        remove_role( $role );
    }

    // Retirer les capacités des rôles WordPress existants
    $capabilities = array(
        'create_feedback',
        'read_feedback',
        'edit_feedback',
        'delete_feedback',
        'read_others_feedback',
        'edit_others_feedback',
        'delete_others_feedback',
        'moderate_feedback',
        'manage_feedback',
        'export_feedback',
    );

    $wp_roles = array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' );

    foreach ( $wp_roles as $role_name ) {
        $role = get_role( $role_name );
        if ( $role ) {
            foreach ( $capabilities as $cap ) {
                $role->remove_cap( $cap );
            }
        }
    }

    // =========================================================================
    // 6. Supprimer le dossier d'uploads
    // =========================================================================
    $upload_dir = wp_upload_dir();
    $feedback_dir = $upload_dir['basedir'] . '/visual-feedback';

    if ( file_exists( $feedback_dir ) ) {
        wpvfh_delete_directory( $feedback_dir );
    }

    // =========================================================================
    // 7. Nettoyer les transients
    // =========================================================================
    $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wpvfh_%'" );
    $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wpvfh_%'" );

    // =========================================================================
    // 8. Flush des règles de réécriture
    // =========================================================================
    flush_rewrite_rules();
}

/**
 * Supprimer récursivement un dossier et son contenu
 *
 * @since 1.0.0
 * @param string $dir Chemin du dossier
 * @return bool
 */
function wpvfh_delete_directory( $dir ) {
    if ( ! file_exists( $dir ) ) {
        return true;
    }

    if ( ! is_dir( $dir ) ) {
        return unlink( $dir );
    }

    foreach ( scandir( $dir ) as $item ) {
        if ( $item == '.' || $item == '..' ) {
            continue;
        }

        if ( ! wpvfh_delete_directory( $dir . DIRECTORY_SEPARATOR . $item ) ) {
            return false;
        }
    }

    return rmdir( $dir );
}

// Exécuter le nettoyage
wpvfh_uninstall_cleanup();
