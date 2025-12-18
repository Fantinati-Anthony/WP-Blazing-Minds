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
    // 1. Supprimer les tables SQL personnalisées
    // =========================================================================
    $tables = array(
        'blazingfeedback_feedbacks',
        'blazingfeedback_replies',
        'blazingfeedback_metadata_types',
        'blazingfeedback_metadata_items',
        'blazingfeedback_custom_groups',
        'blazingfeedback_group_settings',
    );

    foreach ( $tables as $table ) {
        $table_name = $wpdb->base_prefix . $table;
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query( "DROP TABLE IF EXISTS $table_name" );
    }

    // =========================================================================
    // 2. Supprimer les screenshots (attachments dans media library)
    // =========================================================================
    $upload_dir = wp_upload_dir();
    $feedback_dir = $upload_dir['basedir'] . '/visual-feedback';

    // Supprimer les attachments qui sont dans ce dossier
    $attachments = $wpdb->get_results( "
        SELECT ID FROM {$wpdb->posts}
        WHERE post_type = 'attachment'
        AND guid LIKE '%/visual-feedback/%'
    " );

    foreach ( $attachments as $attachment ) {
        wp_delete_attachment( $attachment->ID, true );
    }

    // =========================================================================
    // 3. Supprimer les anciens posts CPT (rétrocompatibilité)
    // =========================================================================
    $feedbacks = get_posts( array(
        'post_type'      => 'visual_feedback',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ) );

    foreach ( $feedbacks as $feedback_id ) {
        wp_delete_post( $feedback_id, true );
    }

    // =========================================================================
    // 4. Supprimer les termes de taxonomie
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
    // 5. Supprimer les options
    // =========================================================================
    $options = array(
        'wpvfh_version',
        'wpvfh_db_version',
        'wpvfh_migration_complete',
        'wpvfh_screenshot_enabled',
        'wpvfh_guest_feedback',
        'wpvfh_button_position',
        'wpvfh_button_color',
        'wpvfh_enabled_pages',
        'wpvfh_email_notifications',
        'wpvfh_notification_email',
        'wpvfh_show_welcome_notice',
        'wpvfh_logo_mode',
        'wpvfh_logo_custom_url',
        // Anciennes options (rétrocompatibilité)
        'wpvfh_feedback_types',
        'wpvfh_feedback_priorities',
        'wpvfh_feedback_tags',
        'wpvfh_feedback_statuses',
        'wpvfh_custom_option_groups',
        'wpvfh_group_settings',
    );

    foreach ( $options as $option ) {
        delete_option( $option );
    }

    // Supprimer les anciennes options de groupes personnalisés (rétrocompatibilité)
    $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'wpvfh_custom_group_%'" );

    // =========================================================================
    // 6. Supprimer les métadonnées utilisateur
    // =========================================================================
    $wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'wpvfh_%'" );

    // =========================================================================
    // 7. Supprimer les rôles personnalisés
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
    // 8. Supprimer le dossier d'uploads
    // =========================================================================
    if ( file_exists( $feedback_dir ) ) {
        wpvfh_delete_directory( $feedback_dir );
    }

    // =========================================================================
    // 9. Nettoyer les transients
    // =========================================================================
    $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wpvfh_%'" );
    $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wpvfh_%'" );

    // =========================================================================
    // 10. Flush des règles de réécriture
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
