<?php
/**
 * Trait pour le tableau de bord admin
 *
 * @package Blazing_Feedback
 * @since 1.9.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Trait de gestion du dashboard
 *
 * @since 1.9.0
 */
trait WPVFH_Admin_Dashboard {

    /**
     * Rendu de la page dashboard
     *
     * @since 1.0.0
     * @return void
     */
    public static function render_dashboard_page() {
        // Récupérer les statistiques
        $stats = self::get_dashboard_stats();
        ?>
        <div class="wrap wpvfh-dashboard-wrap">
            <h1><?php esc_html_e( 'Blazing Feedback - Tableau de bord', 'blazing-feedback' ); ?></h1>

            <!-- Statistiques -->
            <div class="wpvfh-stats-grid">
                <div class="wpvfh-stat-card">
                    <div class="wpvfh-stat-number"><?php echo esc_html( $stats['total'] ); ?></div>
                    <div class="wpvfh-stat-label"><?php esc_html_e( 'Total des feedbacks', 'blazing-feedback' ); ?></div>
                </div>
                <div class="wpvfh-stat-card">
                    <div class="wpvfh-stat-number wpvfh-status-new"><?php echo esc_html( $stats['new'] ); ?></div>
                    <div class="wpvfh-stat-label"><?php esc_html_e( 'Nouveaux', 'blazing-feedback' ); ?></div>
                </div>
                <div class="wpvfh-stat-card">
                    <div class="wpvfh-stat-number wpvfh-status-in_progress"><?php echo esc_html( $stats['in_progress'] ); ?></div>
                    <div class="wpvfh-stat-label"><?php esc_html_e( 'En cours', 'blazing-feedback' ); ?></div>
                </div>
                <div class="wpvfh-stat-card">
                    <div class="wpvfh-stat-number wpvfh-status-resolved"><?php echo esc_html( $stats['resolved'] ); ?></div>
                    <div class="wpvfh-stat-label"><?php esc_html_e( 'Résolus', 'blazing-feedback' ); ?></div>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="wpvfh-quick-actions">
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=visual_feedback' ) ); ?>" class="button button-primary">
                    <?php esc_html_e( 'Voir tous les feedbacks', 'blazing-feedback' ); ?>
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpvfh-settings' ) ); ?>" class="button">
                    <?php esc_html_e( 'Paramètres', 'blazing-feedback' ); ?>
                </a>
                <?php if ( current_user_can( 'export_feedback' ) ) : ?>
                <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=wpvfh-dashboard&action=export' ), 'wpvfh_export' ) ); ?>" class="button">
                    <?php esc_html_e( 'Exporter', 'blazing-feedback' ); ?>
                </a>
                <?php endif; ?>
            </div>

            <!-- Feedbacks récents -->
            <div class="wpvfh-recent-feedbacks">
                <h3><?php esc_html_e( 'Feedbacks récents', 'blazing-feedback' ); ?></h3>
                <?php
                $recent = self::get_recent_feedbacks( 10 );
                if ( $recent ) :
                ?>
                <ul class="wpvfh-feedback-list">
                    <?php foreach ( $recent as $feedback ) : ?>
                    <li class="wpvfh-feedback-item">
                        <div class="wpvfh-feedback-avatar">
                            <?php echo get_avatar( $feedback->post_author, 40 ); ?>
                        </div>
                        <div class="wpvfh-feedback-content">
                            <p class="wpvfh-feedback-title">
                                <a href="<?php echo esc_url( get_edit_post_link( $feedback->ID ) ); ?>">
                                    <?php echo esc_html( $feedback->post_title ); ?>
                                </a>
                            </p>
                            <p class="wpvfh-feedback-meta">
                                <?php
                                $author = get_userdata( $feedback->post_author );
                                $url = get_post_meta( $feedback->ID, '_wpvfh_url', true );
                                printf(
                                    /* translators: %1$s: author name, %2$s: date, %3$s: page path */
                                    esc_html__( 'Par %1$s le %2$s sur %3$s', 'blazing-feedback' ),
                                    '<strong>' . esc_html( $author ? $author->display_name : __( 'Anonyme', 'blazing-feedback' ) ) . '</strong>',
                                    esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $feedback->post_date ) ) ),
                                    '<code>' . esc_html( wp_parse_url( $url, PHP_URL_PATH ) ?: '/' ) . '</code>'
                                );
                                ?>
                            </p>
                        </div>
                        <div class="wpvfh-feedback-status">
                            <?php
                            $status = get_post_meta( $feedback->ID, '_wpvfh_status', true ) ?: 'new';
                            $status_data = WPVFH_Options_Manager::get_status_by_id( $status );
                            if ( ! $status_data ) {
                                $status_data = WPVFH_Options_Manager::get_status_by_id( 'new' );
                            }
                            ?>
                            <span class="wpvfh-status-badge wpvfh-badge-<?php echo esc_attr( $status ); ?>">
                                <?php echo esc_html( ( $status_data['emoji'] ?? '' ) . ' ' . ( $status_data['label'] ?? $status ) ); ?>
                            </span>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else : ?>
                <p style="padding: 20px; text-align: center; color: #50575e;">
                    <?php esc_html_e( 'Aucun feedback pour le moment.', 'blazing-feedback' ); ?>
                </p>
                <?php endif; ?>
            </div>

            <!-- Pages les plus commentées -->
            <div class="wpvfh-recent-feedbacks">
                <h3><?php esc_html_e( 'Pages les plus commentées', 'blazing-feedback' ); ?></h3>
                <?php
                $top_pages = self::get_top_pages( 5 );
                if ( $top_pages ) :
                ?>
                <ul class="wpvfh-feedback-list">
                    <?php foreach ( $top_pages as $page ) : ?>
                    <li class="wpvfh-feedback-item">
                        <div class="wpvfh-feedback-content">
                            <p class="wpvfh-feedback-title">
                                <a href="<?php echo esc_url( $page->url ); ?>" target="_blank">
                                    <?php echo esc_html( $page->path ); ?>
                                </a>
                            </p>
                        </div>
                        <div class="wpvfh-feedback-status">
                            <span class="wpvfh-status-badge">
                                <?php
                                printf(
                                    /* translators: %d: number of feedbacks */
                                    esc_html( _n( '%d feedback', '%d feedbacks', $page->count, 'blazing-feedback' ) ),
                                    $page->count
                                );
                                ?>
                            </span>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else : ?>
                <p style="padding: 20px; text-align: center; color: #50575e;">
                    <?php esc_html_e( 'Aucune donnée disponible.', 'blazing-feedback' ); ?>
                </p>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Obtenir les statistiques du dashboard
     *
     * @since 1.0.0
     * @return array
     */
    private static function get_dashboard_stats() {
        global $wpdb;

        $stats = array(
            'total'       => 0,
            'new'         => 0,
            'in_progress' => 0,
            'resolved'    => 0,
            'rejected'    => 0,
        );

        $stats['total'] = wp_count_posts( 'visual_feedback' )->publish;

        $statuses = array( 'new', 'in_progress', 'resolved', 'rejected' );
        foreach ( $statuses as $status ) {
            $count = $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->postmeta} pm
                INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                WHERE pm.meta_key = '_wpvfh_status'
                AND pm.meta_value = %s
                AND p.post_type = 'visual_feedback'
                AND p.post_status = 'publish'",
                $status
            ) );
            $stats[ $status ] = (int) $count;
        }

        return $stats;
    }

    /**
     * Obtenir les feedbacks récents
     *
     * @since 1.0.0
     * @param int $limit Nombre de feedbacks
     * @return array
     */
    private static function get_recent_feedbacks( $limit = 10 ) {
        return get_posts( array(
            'post_type'      => 'visual_feedback',
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ) );
    }

    /**
     * Obtenir les pages les plus commentées
     *
     * @since 1.0.0
     * @param int $limit Nombre de pages
     * @return array
     */
    private static function get_top_pages( $limit = 5 ) {
        global $wpdb;

        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT pm.meta_value as url, COUNT(*) as count
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE pm.meta_key = '_wpvfh_url'
            AND p.post_type = 'visual_feedback'
            AND p.post_status = 'publish'
            GROUP BY pm.meta_value
            ORDER BY count DESC
            LIMIT %d",
            $limit
        ) );

        foreach ( $results as &$result ) {
            $result->path = wp_parse_url( $result->url, PHP_URL_PATH ) ?: '/';
        }

        return $results;
    }
}
