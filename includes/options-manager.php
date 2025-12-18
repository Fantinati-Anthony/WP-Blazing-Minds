<?php
/**
 * Gestionnaire des options personnalisables (Types, Priorités, Tags)
 *
 * @package WP_Visual_Feedback_Hub
 * @since 1.1.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Classe de gestion des options
 *
 * @since 1.1.0
 */
class WPVFH_Options_Manager {

    /**
     * Clés des options
     */
    const OPTION_TYPES      = 'wpvfh_feedback_types';
    const OPTION_PRIORITIES = 'wpvfh_feedback_priorities';
    const OPTION_TAGS       = 'wpvfh_feedback_tags';

    /**
     * Initialiser le gestionnaire
     *
     * @since 1.1.0
     * @return void
     */
    public static function init() {
        add_action( 'admin_menu', array( __CLASS__, 'add_admin_menu' ) );
        add_action( 'admin_init', array( __CLASS__, 'handle_actions' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_scripts' ) );
        add_action( 'wp_ajax_wpvfh_save_options_order', array( __CLASS__, 'ajax_save_order' ) );
        add_action( 'wp_ajax_wpvfh_save_option_item', array( __CLASS__, 'ajax_save_item' ) );
        add_action( 'wp_ajax_wpvfh_delete_option_item', array( __CLASS__, 'ajax_delete_item' ) );
    }

    /**
     * Ajouter le menu d'administration
     *
     * @since 1.1.0
     * @return void
     */
    public static function add_admin_menu() {
        add_submenu_page(
            'wpvfh-dashboard',
            __( 'Options de feedback', 'blazing-feedback' ),
            __( 'Options', 'blazing-feedback' ),
            'manage_feedback',
            'wpvfh-options',
            array( __CLASS__, 'render_options_page' )
        );
    }

    /**
     * Charger les scripts admin
     *
     * @since 1.1.0
     * @param string $hook Page hook
     * @return void
     */
    public static function enqueue_admin_scripts( $hook ) {
        if ( 'feedbacks_page_wpvfh-options' !== $hook ) {
            return;
        }

        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_script( 'jquery-ui-sortable' );

        wp_enqueue_style(
            'wpvfh-options-admin',
            WPVFH_PLUGIN_URL . 'assets/css/admin-options.css',
            array(),
            WPVFH_VERSION
        );

        wp_enqueue_script(
            'wpvfh-options-admin',
            WPVFH_PLUGIN_URL . 'assets/js/admin-options.js',
            array( 'jquery', 'jquery-ui-sortable', 'wp-color-picker' ),
            WPVFH_VERSION,
            true
        );

        wp_localize_script( 'wpvfh-options-admin', 'wpvfhOptionsAdmin', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'wpvfh_options_nonce' ),
            'i18n'    => array(
                'confirmDelete' => __( 'Êtes-vous sûr de vouloir supprimer cet élément ?', 'blazing-feedback' ),
                'saving'        => __( 'Enregistrement...', 'blazing-feedback' ),
                'saved'         => __( 'Enregistré !', 'blazing-feedback' ),
                'error'         => __( 'Erreur lors de l\'enregistrement', 'blazing-feedback' ),
            ),
        ) );
    }

    /**
     * Obtenir les types de feedback par défaut
     *
     * @since 1.1.0
     * @return array
     */
    public static function get_default_types() {
        return array(
            array(
                'id'    => 'bug',
                'label' => __( 'Bug', 'blazing-feedback' ),
                'emoji' => '🐛',
                'color' => '#e74c3c',
            ),
            array(
                'id'    => 'improvement',
                'label' => __( 'Amélioration', 'blazing-feedback' ),
                'emoji' => '💡',
                'color' => '#f39c12',
            ),
            array(
                'id'    => 'question',
                'label' => __( 'Question', 'blazing-feedback' ),
                'emoji' => '❓',
                'color' => '#3498db',
            ),
            array(
                'id'    => 'design',
                'label' => __( 'Design', 'blazing-feedback' ),
                'emoji' => '🎨',
                'color' => '#9b59b6',
            ),
            array(
                'id'    => 'content',
                'label' => __( 'Contenu', 'blazing-feedback' ),
                'emoji' => '📝',
                'color' => '#1abc9c',
            ),
            array(
                'id'    => 'other',
                'label' => __( 'Autre', 'blazing-feedback' ),
                'emoji' => '📌',
                'color' => '#95a5a6',
            ),
        );
    }

    /**
     * Obtenir les priorités par défaut
     *
     * @since 1.1.0
     * @return array
     */
    public static function get_default_priorities() {
        return array(
            array(
                'id'    => 'none',
                'label' => __( 'Aucune', 'blazing-feedback' ),
                'emoji' => '⚪',
                'color' => '#bdc3c7',
            ),
            array(
                'id'    => 'low',
                'label' => __( 'Basse', 'blazing-feedback' ),
                'emoji' => '🟢',
                'color' => '#27ae60',
            ),
            array(
                'id'    => 'medium',
                'label' => __( 'Moyenne', 'blazing-feedback' ),
                'emoji' => '🟠',
                'color' => '#f39c12',
            ),
            array(
                'id'    => 'high',
                'label' => __( 'Haute', 'blazing-feedback' ),
                'emoji' => '🔴',
                'color' => '#e74c3c',
            ),
        );
    }

    /**
     * Obtenir les tags par défaut
     *
     * @since 1.1.0
     * @return array
     */
    public static function get_default_tags() {
        return array(
            array(
                'id'    => 'urgent',
                'label' => __( 'Urgent', 'blazing-feedback' ),
                'color' => '#e74c3c',
            ),
            array(
                'id'    => 'frontend',
                'label' => __( 'Frontend', 'blazing-feedback' ),
                'color' => '#3498db',
            ),
            array(
                'id'    => 'backend',
                'label' => __( 'Backend', 'blazing-feedback' ),
                'color' => '#9b59b6',
            ),
            array(
                'id'    => 'mobile',
                'label' => __( 'Mobile', 'blazing-feedback' ),
                'color' => '#1abc9c',
            ),
        );
    }

    /**
     * Obtenir les types de feedback
     *
     * @since 1.1.0
     * @return array
     */
    public static function get_types() {
        $types = get_option( self::OPTION_TYPES );
        if ( false === $types || empty( $types ) ) {
            $types = self::get_default_types();
            update_option( self::OPTION_TYPES, $types );
        }
        return $types;
    }

    /**
     * Obtenir les priorités
     *
     * @since 1.1.0
     * @return array
     */
    public static function get_priorities() {
        $priorities = get_option( self::OPTION_PRIORITIES );
        if ( false === $priorities || empty( $priorities ) ) {
            $priorities = self::get_default_priorities();
            update_option( self::OPTION_PRIORITIES, $priorities );
        }
        return $priorities;
    }

    /**
     * Obtenir les tags prédéfinis
     *
     * @since 1.1.0
     * @return array
     */
    public static function get_predefined_tags() {
        $tags = get_option( self::OPTION_TAGS );
        if ( false === $tags ) {
            $tags = self::get_default_tags();
            update_option( self::OPTION_TAGS, $tags );
        }
        return $tags;
    }

    /**
     * Sauvegarder les types
     *
     * @since 1.1.0
     * @param array $types Types à sauvegarder
     * @return bool
     */
    public static function save_types( $types ) {
        return update_option( self::OPTION_TYPES, $types );
    }

    /**
     * Sauvegarder les priorités
     *
     * @since 1.1.0
     * @param array $priorities Priorités à sauvegarder
     * @return bool
     */
    public static function save_priorities( $priorities ) {
        return update_option( self::OPTION_PRIORITIES, $priorities );
    }

    /**
     * Sauvegarder les tags
     *
     * @since 1.1.0
     * @param array $tags Tags à sauvegarder
     * @return bool
     */
    public static function save_tags( $tags ) {
        return update_option( self::OPTION_TAGS, $tags );
    }

    /**
     * Obtenir un type par ID
     *
     * @since 1.1.0
     * @param string $id ID du type
     * @return array|null
     */
    public static function get_type_by_id( $id ) {
        $types = self::get_types();
        foreach ( $types as $type ) {
            if ( $type['id'] === $id ) {
                return $type;
            }
        }
        return null;
    }

    /**
     * Obtenir une priorité par ID
     *
     * @since 1.1.0
     * @param string $id ID de la priorité
     * @return array|null
     */
    public static function get_priority_by_id( $id ) {
        $priorities = self::get_priorities();
        foreach ( $priorities as $priority ) {
            if ( $priority['id'] === $id ) {
                return $priority;
            }
        }
        return null;
    }

    /**
     * Gérer les actions admin
     *
     * @since 1.1.0
     * @return void
     */
    public static function handle_actions() {
        if ( ! isset( $_GET['page'] ) || 'wpvfh-options' !== $_GET['page'] ) {
            return;
        }

        // Reset aux valeurs par défaut
        if ( isset( $_GET['action'] ) && 'reset' === $_GET['action'] ) {
            check_admin_referer( 'wpvfh_reset_options' );

            if ( ! current_user_can( 'manage_feedback' ) ) {
                wp_die( esc_html__( 'Permission refusée.', 'blazing-feedback' ) );
            }

            $tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'types';

            switch ( $tab ) {
                case 'types':
                    delete_option( self::OPTION_TYPES );
                    break;
                case 'priorities':
                    delete_option( self::OPTION_PRIORITIES );
                    break;
                case 'tags':
                    delete_option( self::OPTION_TAGS );
                    break;
            }

            wp_safe_redirect( admin_url( 'admin.php?page=wpvfh-options&tab=' . $tab . '&reset=1' ) );
            exit;
        }
    }

    /**
     * AJAX: Sauvegarder l'ordre
     *
     * @since 1.1.0
     * @return void
     */
    public static function ajax_save_order() {
        check_ajax_referer( 'wpvfh_options_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_feedback' ) ) {
            wp_send_json_error( __( 'Permission refusée.', 'blazing-feedback' ) );
        }

        $type  = isset( $_POST['option_type'] ) ? sanitize_key( $_POST['option_type'] ) : '';
        $order = isset( $_POST['order'] ) ? array_map( 'sanitize_key', $_POST['order'] ) : array();

        if ( empty( $type ) || empty( $order ) ) {
            wp_send_json_error( __( 'Données invalides.', 'blazing-feedback' ) );
        }

        $items = array();
        switch ( $type ) {
            case 'types':
                $items = self::get_types();
                break;
            case 'priorities':
                $items = self::get_priorities();
                break;
            case 'tags':
                $items = self::get_predefined_tags();
                break;
        }

        // Réorganiser selon l'ordre
        $sorted = array();
        foreach ( $order as $id ) {
            foreach ( $items as $item ) {
                if ( $item['id'] === $id ) {
                    $sorted[] = $item;
                    break;
                }
            }
        }

        // Sauvegarder
        switch ( $type ) {
            case 'types':
                self::save_types( $sorted );
                break;
            case 'priorities':
                self::save_priorities( $sorted );
                break;
            case 'tags':
                self::save_tags( $sorted );
                break;
        }

        wp_send_json_success();
    }

    /**
     * AJAX: Sauvegarder un élément
     *
     * @since 1.1.0
     * @return void
     */
    public static function ajax_save_item() {
        check_ajax_referer( 'wpvfh_options_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_feedback' ) ) {
            wp_send_json_error( __( 'Permission refusée.', 'blazing-feedback' ) );
        }

        $option_type = isset( $_POST['option_type'] ) ? sanitize_key( $_POST['option_type'] ) : '';
        $item_id     = isset( $_POST['item_id'] ) ? sanitize_key( $_POST['item_id'] ) : '';
        $label       = isset( $_POST['label'] ) ? sanitize_text_field( $_POST['label'] ) : '';
        $emoji       = isset( $_POST['emoji'] ) ? wp_kses( $_POST['emoji'], array() ) : '';
        $color       = isset( $_POST['color'] ) ? sanitize_hex_color( $_POST['color'] ) : '#666666';
        $is_new      = isset( $_POST['is_new'] ) && $_POST['is_new'] === 'true';

        if ( empty( $option_type ) || empty( $label ) ) {
            wp_send_json_error( __( 'Données invalides.', 'blazing-feedback' ) );
        }

        // Générer un ID si nouveau
        if ( $is_new || empty( $item_id ) ) {
            $item_id = sanitize_title( $label ) . '_' . time();
        }

        $new_item = array(
            'id'    => $item_id,
            'label' => $label,
            'color' => $color,
        );

        // Ajouter l'emoji seulement pour types et priorities
        if ( in_array( $option_type, array( 'types', 'priorities' ), true ) ) {
            $new_item['emoji'] = $emoji;
        }

        // Obtenir les items existants
        $items = array();
        switch ( $option_type ) {
            case 'types':
                $items = self::get_types();
                break;
            case 'priorities':
                $items = self::get_priorities();
                break;
            case 'tags':
                $items = self::get_predefined_tags();
                break;
        }

        // Mettre à jour ou ajouter
        $found = false;
        foreach ( $items as $key => $item ) {
            if ( $item['id'] === $item_id ) {
                $items[ $key ] = $new_item;
                $found = true;
                break;
            }
        }

        if ( ! $found ) {
            $items[] = $new_item;
        }

        // Sauvegarder
        switch ( $option_type ) {
            case 'types':
                self::save_types( $items );
                break;
            case 'priorities':
                self::save_priorities( $items );
                break;
            case 'tags':
                self::save_tags( $items );
                break;
        }

        wp_send_json_success( array( 'item' => $new_item ) );
    }

    /**
     * AJAX: Supprimer un élément
     *
     * @since 1.1.0
     * @return void
     */
    public static function ajax_delete_item() {
        check_ajax_referer( 'wpvfh_options_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_feedback' ) ) {
            wp_send_json_error( __( 'Permission refusée.', 'blazing-feedback' ) );
        }

        $option_type = isset( $_POST['option_type'] ) ? sanitize_key( $_POST['option_type'] ) : '';
        $item_id     = isset( $_POST['item_id'] ) ? sanitize_key( $_POST['item_id'] ) : '';

        if ( empty( $option_type ) || empty( $item_id ) ) {
            wp_send_json_error( __( 'Données invalides.', 'blazing-feedback' ) );
        }

        // Obtenir les items existants
        $items = array();
        switch ( $option_type ) {
            case 'types':
                $items = self::get_types();
                break;
            case 'priorities':
                $items = self::get_priorities();
                break;
            case 'tags':
                $items = self::get_predefined_tags();
                break;
        }

        // Supprimer l'élément
        $items = array_filter( $items, function( $item ) use ( $item_id ) {
            return $item['id'] !== $item_id;
        } );
        $items = array_values( $items ); // Réindexer

        // Sauvegarder
        switch ( $option_type ) {
            case 'types':
                self::save_types( $items );
                break;
            case 'priorities':
                self::save_priorities( $items );
                break;
            case 'tags':
                self::save_tags( $items );
                break;
        }

        wp_send_json_success();
    }

    /**
     * Rendu de la page d'options
     *
     * @since 1.1.0
     * @return void
     */
    public static function render_options_page() {
        if ( ! current_user_can( 'manage_feedback' ) ) {
            wp_die( esc_html__( 'Permission refusée.', 'blazing-feedback' ) );
        }

        $current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'types';
        $tabs = array(
            'types'      => __( 'Types de feedback', 'blazing-feedback' ),
            'priorities' => __( 'Niveaux de priorité', 'blazing-feedback' ),
            'tags'       => __( 'Tags prédéfinis', 'blazing-feedback' ),
        );

        // Message de confirmation
        $message = '';
        if ( isset( $_GET['reset'] ) ) {
            $message = '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Options réinitialisées avec succès.', 'blazing-feedback' ) . '</p></div>';
        }
        ?>
        <div class="wrap wpvfh-options-page">
            <h1><?php esc_html_e( 'Options de feedback', 'blazing-feedback' ); ?></h1>

            <?php echo $message; ?>

            <nav class="nav-tab-wrapper">
                <?php foreach ( $tabs as $tab_id => $tab_label ) : ?>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpvfh-options&tab=' . $tab_id ) ); ?>"
                       class="nav-tab <?php echo $current_tab === $tab_id ? 'nav-tab-active' : ''; ?>">
                        <?php echo esc_html( $tab_label ); ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="wpvfh-options-content">
                <?php
                switch ( $current_tab ) {
                    case 'types':
                        self::render_types_tab();
                        break;
                    case 'priorities':
                        self::render_priorities_tab();
                        break;
                    case 'tags':
                        self::render_tags_tab();
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Rendu de l'onglet Types
     *
     * @since 1.1.0
     * @return void
     */
    private static function render_types_tab() {
        $types = self::get_types();
        self::render_items_table( 'types', $types, true );
    }

    /**
     * Rendu de l'onglet Priorités
     *
     * @since 1.1.0
     * @return void
     */
    private static function render_priorities_tab() {
        $priorities = self::get_priorities();
        self::render_items_table( 'priorities', $priorities, true );
    }

    /**
     * Rendu de l'onglet Tags
     *
     * @since 1.1.0
     * @return void
     */
    private static function render_tags_tab() {
        $tags = self::get_predefined_tags();
        self::render_items_table( 'tags', $tags, false );
    }

    /**
     * Rendu du tableau d'éléments
     *
     * @since 1.1.0
     * @param string $type       Type d'option (types, priorities, tags)
     * @param array  $items      Éléments à afficher
     * @param bool   $show_emoji Afficher la colonne emoji
     * @return void
     */
    private static function render_items_table( $type, $items, $show_emoji = true ) {
        $reset_url = wp_nonce_url(
            admin_url( 'admin.php?page=wpvfh-options&tab=' . $type . '&action=reset' ),
            'wpvfh_reset_options'
        );
        ?>
        <div class="wpvfh-options-header">
            <p class="description">
                <?php
                switch ( $type ) {
                    case 'types':
                        esc_html_e( 'Définissez les types de feedback disponibles. Glissez-déposez pour réorganiser.', 'blazing-feedback' );
                        break;
                    case 'priorities':
                        esc_html_e( 'Définissez les niveaux de priorité disponibles. Glissez-déposez pour réorganiser.', 'blazing-feedback' );
                        break;
                    case 'tags':
                        esc_html_e( 'Définissez les tags prédéfinis. Les utilisateurs peuvent aussi créer leurs propres tags.', 'blazing-feedback' );
                        break;
                }
                ?>
            </p>
            <div class="wpvfh-options-actions">
                <button type="button" class="button button-primary wpvfh-add-item-btn" data-type="<?php echo esc_attr( $type ); ?>">
                    <span class="dashicons dashicons-plus-alt2"></span>
                    <?php esc_html_e( 'Ajouter', 'blazing-feedback' ); ?>
                </button>
                <a href="<?php echo esc_url( $reset_url ); ?>" class="button"
                   onclick="return confirm('<?php esc_attr_e( 'Réinitialiser aux valeurs par défaut ?', 'blazing-feedback' ); ?>');">
                    <span class="dashicons dashicons-image-rotate"></span>
                    <?php esc_html_e( 'Réinitialiser', 'blazing-feedback' ); ?>
                </a>
            </div>
        </div>

        <table class="wp-list-table widefat fixed striped wpvfh-options-table" data-type="<?php echo esc_attr( $type ); ?>">
            <thead>
                <tr>
                    <th class="wpvfh-col-drag" style="width: 30px;"></th>
                    <?php if ( $show_emoji ) : ?>
                    <th class="wpvfh-col-emoji" style="width: 60px;"><?php esc_html_e( 'Emoji', 'blazing-feedback' ); ?></th>
                    <?php endif; ?>
                    <th class="wpvfh-col-label"><?php esc_html_e( 'Label', 'blazing-feedback' ); ?></th>
                    <th class="wpvfh-col-color" style="width: 120px;"><?php esc_html_e( 'Couleur', 'blazing-feedback' ); ?></th>
                    <th class="wpvfh-col-preview" style="width: 150px;"><?php esc_html_e( 'Aperçu', 'blazing-feedback' ); ?></th>
                    <th class="wpvfh-col-actions" style="width: 100px;"><?php esc_html_e( 'Actions', 'blazing-feedback' ); ?></th>
                </tr>
            </thead>
            <tbody class="wpvfh-sortable-items">
                <?php foreach ( $items as $item ) : ?>
                    <?php self::render_item_row( $type, $item, $show_emoji ); ?>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Template pour nouvelle ligne -->
        <script type="text/template" id="wpvfh-item-template-<?php echo esc_attr( $type ); ?>">
            <?php self::render_item_row( $type, array( 'id' => '', 'label' => '', 'emoji' => '📌', 'color' => '#666666' ), $show_emoji, true ); ?>
        </script>
        <?php
    }

    /**
     * Rendu d'une ligne d'élément
     *
     * @since 1.1.0
     * @param string $type       Type d'option
     * @param array  $item       Données de l'élément
     * @param bool   $show_emoji Afficher l'emoji
     * @param bool   $is_new     Est un nouvel élément
     * @return void
     */
    private static function render_item_row( $type, $item, $show_emoji = true, $is_new = false ) {
        $id    = isset( $item['id'] ) ? $item['id'] : '';
        $label = isset( $item['label'] ) ? $item['label'] : '';
        $emoji = isset( $item['emoji'] ) ? $item['emoji'] : '📌';
        $color = isset( $item['color'] ) ? $item['color'] : '#666666';
        ?>
        <tr class="wpvfh-option-item <?php echo $is_new ? 'wpvfh-new-item' : ''; ?>" data-id="<?php echo esc_attr( $id ); ?>">
            <td class="wpvfh-col-drag">
                <span class="wpvfh-drag-handle dashicons dashicons-menu"></span>
            </td>
            <?php if ( $show_emoji ) : ?>
            <td class="wpvfh-col-emoji">
                <input type="text" class="wpvfh-emoji-input" value="<?php echo esc_attr( $emoji ); ?>" maxlength="4" style="width: 40px; text-align: center; font-size: 18px;">
            </td>
            <?php endif; ?>
            <td class="wpvfh-col-label">
                <input type="text" class="wpvfh-label-input regular-text" value="<?php echo esc_attr( $label ); ?>" placeholder="<?php esc_attr_e( 'Label...', 'blazing-feedback' ); ?>">
            </td>
            <td class="wpvfh-col-color">
                <input type="text" class="wpvfh-color-input" value="<?php echo esc_attr( $color ); ?>" data-default-color="<?php echo esc_attr( $color ); ?>">
            </td>
            <td class="wpvfh-col-preview">
                <span class="wpvfh-preview-badge" style="background-color: <?php echo esc_attr( $color ); ?>20; color: <?php echo esc_attr( $color ); ?>; border: 1px solid <?php echo esc_attr( $color ); ?>40;">
                    <?php if ( $show_emoji ) : ?><span class="wpvfh-preview-emoji"><?php echo esc_html( $emoji ); ?></span><?php endif; ?>
                    <span class="wpvfh-preview-label"><?php echo esc_html( $label ?: __( 'Aperçu', 'blazing-feedback' ) ); ?></span>
                </span>
            </td>
            <td class="wpvfh-col-actions">
                <button type="button" class="button wpvfh-save-item-btn" title="<?php esc_attr_e( 'Enregistrer', 'blazing-feedback' ); ?>">
                    <span class="dashicons dashicons-saved"></span>
                </button>
                <button type="button" class="button wpvfh-delete-item-btn" title="<?php esc_attr_e( 'Supprimer', 'blazing-feedback' ); ?>">
                    <span class="dashicons dashicons-trash"></span>
                </button>
            </td>
        </tr>
        <?php
    }

    /**
     * Obtenir toutes les options pour le frontend
     *
     * @since 1.1.0
     * @return array
     */
    public static function get_all_options_for_frontend() {
        return array(
            'types'      => self::get_types(),
            'priorities' => self::get_priorities(),
            'tags'       => self::get_predefined_tags(),
        );
    }
}
