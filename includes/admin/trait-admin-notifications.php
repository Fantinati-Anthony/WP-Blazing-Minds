<?php
/**
 * Trait pour les notifications admin
 *
 * @package Blazing_Feedback
 * @since 1.9.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Trait de gestion des notifications admin
 *
 * @since 1.9.0
 */
trait WPVFH_Admin_Notifications {

    /**
     * Afficher les notices admin
     *
     * @since 1.0.0
     * @return void
     */
    public static function show_admin_notices() {
        // Notice de bienvenue (première activation) - ne s'affiche qu'une seule fois
        $welcome_notice = WPVFH_Database::get_setting( 'wpvfh_welcome_notice_dismissed' );
        if ( false === $welcome_notice && current_user_can( 'manage_feedback' ) ) {
            ?>
            <div class="notice notice-info is-dismissible wpvfh-welcome-notice" data-notice="wpvfh-welcome">
                <p>
                    <strong><?php esc_html_e( 'Blazing Feedback est activé !', 'blazing-feedback' ); ?></strong>
                    <?php esc_html_e( 'Le widget de feedback est maintenant disponible sur votre site.', 'blazing-feedback' ); ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpvfh-settings' ) ); ?>">
                        <?php esc_html_e( 'Configurer', 'blazing-feedback' ); ?>
                    </a>
                </p>
            </div>
            <script>
            jQuery(document).ready(function($) {
                $('.wpvfh-welcome-notice').on('click', '.notice-dismiss', function() {
                    $.post(ajaxurl, {
                        action: 'wpvfh_dismiss_notice',
                        notice_id: 'welcome',
                        nonce: '<?php echo esc_js( wp_create_nonce( 'wpvfh_nonce' ) ); ?>'
                    });
                });
            });
            </script>
            <?php
            // Marquer comme vu dès l'affichage
            WPVFH_Database::update_setting( 'wpvfh_welcome_notice_dismissed', '1' );
        }
    }

    /**
     * Ajouter des liens sur la page plugins
     *
     * @since 1.0.0
     * @param array $links Liens existants
     * @return array
     */
    public static function add_plugin_links( $links ) {
        $plugin_links = array(
            '<a href="' . admin_url( 'admin.php?page=wpvfh-settings' ) . '">' . __( 'Paramètres', 'blazing-feedback' ) . '</a>',
            '<a href="' . admin_url( 'admin.php?page=wpvfh-dashboard' ) . '">' . __( 'Dashboard', 'blazing-feedback' ) . '</a>',
        );

        return array_merge( $plugin_links, $links );
    }
}

/**
 * Envoyer une notification email pour un nouveau feedback
 *
 * @since 1.0.0
 * @param int   $feedback_id ID du feedback
 * @param array $data        Données du feedback
 * @return void
 */
function wpvfh_send_new_feedback_notification( $feedback_id, $data ) {
    // Vérifier si les notifications sont activées
    if ( ! WPVFH_Database::get_setting( 'wpvfh_email_notifications', true ) ) {
        return;
    }

    $to = WPVFH_Database::get_setting( 'wpvfh_notification_email', get_option( 'admin_email' ) );
    if ( ! is_email( $to ) ) {
        return;
    }

    $post = get_post( $feedback_id );
    $author = get_userdata( $post->post_author );
    $url = get_post_meta( $feedback_id, '_wpvfh_url', true );

    $subject = sprintf(
        /* translators: %s: site name */
        __( '[%s] Nouveau feedback reçu', 'blazing-feedback' ),
        get_bloginfo( 'name' )
    );

    $message = sprintf(
        /* translators: %1$s: author name, %2$s: page URL, %3$s: comment, %4$s: admin link */
        __( "Un nouveau feedback a été envoyé.\n\nAuteur : %1\$s\nPage : %2\$s\n\nCommentaire :\n%3\$s\n\nVoir le feedback : %4\$s", 'blazing-feedback' ),
        $author ? $author->display_name : __( 'Anonyme', 'blazing-feedback' ),
        $url,
        $post->post_content,
        admin_url( 'post.php?post=' . $feedback_id . '&action=edit' )
    );

    /**
     * Filtre le sujet de l'email de notification
     *
     * @since 1.0.0
     * @param string $subject     Sujet
     * @param int    $feedback_id ID du feedback
     */
    $subject = apply_filters( 'wpvfh_notification_subject', $subject, $feedback_id );

    /**
     * Filtre le contenu de l'email de notification
     *
     * @since 1.0.0
     * @param string $message     Message
     * @param int    $feedback_id ID du feedback
     */
    $message = apply_filters( 'wpvfh_notification_message', $message, $feedback_id );

    wp_mail( $to, $subject, $message );
}
add_action( 'wpvfh_feedback_created', 'wpvfh_send_new_feedback_notification', 10, 2 );
