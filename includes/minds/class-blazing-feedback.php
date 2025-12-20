<?php
/**
 * Intégration avec Blazing Feedback
 *
 * Convertit les feedbacks en Informations dans le cycle ICAVAL
 *
 * @package Blazing_Minds
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe BZMI_Integration_Blazing_Feedback
 *
 * @since 1.0.0
 */
class BZMI_Integration_Blazing_Feedback {

	/**
	 * Vérifier si Blazing Feedback est installé
	 *
	 * @return bool
	 */
	public static function is_available() {
		return class_exists( 'Blazing_Feedback' ) || defined( 'WPVFH_VERSION' );
	}

	/**
	 * Vérifier si la synchronisation est activée
	 *
	 * @return bool
	 */
	public static function is_sync_enabled() {
		return BZMI_Database::get_setting( 'blazing_feedback_sync', true );
	}

	/**
	 * Vérifier si l'import automatique est activé
	 *
	 * @return bool
	 */
	public static function is_auto_import_enabled() {
		return BZMI_Database::get_setting( 'blazing_feedback_auto_import', true );
	}

	/**
	 * Hook appelé quand un nouveau feedback est créé dans Blazing Feedback
	 *
	 * @param int   $feedback_id ID du feedback.
	 * @param array $feedback_data Données du feedback.
	 * @return void
	 */
	public static function on_feedback_created( $feedback_id, $feedback_data ) {
		// Vérifier si la synchronisation est activée
		if ( ! self::is_sync_enabled() || ! self::is_auto_import_enabled() ) {
			return;
		}

		// Obtenir le projet par défaut
		$project_id = BZMI_Database::get_setting( 'blazing_feedback_default_project', 0 );

		if ( ! $project_id ) {
			// Aucun projet configuré, ignorer
			return;
		}

		// Vérifier que le projet existe
		$project = BZMI_Project::find( $project_id );
		if ( ! $project ) {
			return;
		}

		// Créer l'information depuis le feedback
		$info = BZMI_Information::create_from_feedback( $feedback_data, $project_id );

		if ( $info ) {
			/**
			 * Action après l'import d'un feedback
			 *
			 * @since 1.0.0
			 * @param BZMI_Information $info L'information créée.
			 * @param array $feedback_data Les données du feedback original.
			 */
			do_action( 'bzmi_feedback_imported', $info, $feedback_data );

			// Déclencher l'IA si activée
			self::maybe_trigger_ai_clarifications( $info );
		}
	}

	/**
	 * Déclencher la génération de clarifications IA si activée
	 *
	 * @param BZMI_Information $info L'information.
	 * @return void
	 */
	private static function maybe_trigger_ai_clarifications( $info ) {
		$ai = bzmi_ai();

		if ( ! $ai->is_feature_enabled( 'auto_clarify' ) ) {
			return;
		}

		// Générer les clarifications de manière asynchrone si possible
		if ( function_exists( 'wp_schedule_single_event' ) ) {
			wp_schedule_single_event(
				time() + 5, // 5 secondes de délai
				'bzmi_generate_ai_clarifications',
				array( $info->id )
			);
		}
	}

	/**
	 * Importer manuellement des feedbacks existants
	 *
	 * @param int $project_id   Projet cible.
	 * @param int $limit        Nombre maximum à importer.
	 * @param int $offset       Offset pour la pagination.
	 * @return array Résultat de l'import.
	 */
	public static function import_existing_feedbacks( $project_id, $limit = 50, $offset = 0 ) {
		if ( ! self::is_available() ) {
			return array(
				'success' => false,
				'message' => __( 'Blazing Feedback n\'est pas installé.', 'blazing-minds' ),
			);
		}

		// Vérifier que le projet existe
		$project = BZMI_Project::find( $project_id );
		if ( ! $project ) {
			return array(
				'success' => false,
				'message' => __( 'Projet introuvable.', 'blazing-minds' ),
			);
		}

		global $wpdb;

		// Table des feedbacks de Blazing Feedback
		$feedbacks_table = $wpdb->prefix . 'blazingfeedback_feedbacks';

		// Vérifier si la table existe
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $feedbacks_table ) ) !== $feedbacks_table ) {
			return array(
				'success' => false,
				'message' => __( 'Table des feedbacks introuvable.', 'blazing-minds' ),
			);
		}

		// Récupérer les feedbacks
		$feedbacks = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$feedbacks_table} ORDER BY created_at DESC LIMIT %d OFFSET %d",
				$limit,
				$offset
			),
			ARRAY_A
		);

		$imported = 0;
		$skipped  = 0;
		$errors   = array();

		foreach ( $feedbacks as $feedback ) {
			// Vérifier si déjà importé
			$existing = BZMI_Information::first_where( array(
				'source'    => 'blazing_feedback',
				'source_id' => $feedback['id'],
			) );

			if ( $existing ) {
				$skipped++;
				continue;
			}

			// Préparer les données
			$feedback_data = array(
				'id'         => $feedback['id'],
				'message'    => $feedback['message'],
				'mood'       => isset( $feedback['mood'] ) ? $feedback['mood'] : 'neutral',
				'type'       => isset( $feedback['type'] ) ? $feedback['type'] : 'general',
				'page_url'   => isset( $feedback['page_url'] ) ? $feedback['page_url'] : '',
				'user_agent' => isset( $feedback['user_agent'] ) ? $feedback['user_agent'] : '',
				'metadata'   => isset( $feedback['metadata'] ) ? maybe_unserialize( $feedback['metadata'] ) : array(),
				'created_at' => $feedback['created_at'],
			);

			// Créer l'information
			$info = BZMI_Information::create_from_feedback( $feedback_data, $project_id );

			if ( $info ) {
				$imported++;
			} else {
				$errors[] = sprintf( __( 'Échec de l\'import du feedback #%d', 'blazing-minds' ), $feedback['id'] );
			}
		}

		// Compter le total
		$total = $wpdb->get_var( "SELECT COUNT(*) FROM {$feedbacks_table}" );

		return array(
			'success'  => true,
			'imported' => $imported,
			'skipped'  => $skipped,
			'errors'   => $errors,
			'total'    => $total,
			'message'  => sprintf(
				/* translators: 1: Number imported, 2: Number skipped */
				__( '%1$d feedbacks importés, %2$d ignorés (déjà importés).', 'blazing-minds' ),
				$imported,
				$skipped
			),
		);
	}

	/**
	 * Obtenir les statistiques de synchronisation
	 *
	 * @return array
	 */
	public static function get_sync_stats() {
		if ( ! self::is_available() ) {
			return array(
				'available'       => false,
				'sync_enabled'    => false,
				'auto_import'     => false,
				'total_feedbacks' => 0,
				'imported'        => 0,
				'pending'         => 0,
			);
		}

		global $wpdb;
		$feedbacks_table = $wpdb->prefix . 'blazingfeedback_feedbacks';

		$total_feedbacks = 0;
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $feedbacks_table ) ) === $feedbacks_table ) {
			$total_feedbacks = $wpdb->get_var( "SELECT COUNT(*) FROM {$feedbacks_table}" );
		}

		$imported = BZMI_Information::count( array( 'source' => 'blazing_feedback' ) );

		return array(
			'available'       => true,
			'sync_enabled'    => self::is_sync_enabled(),
			'auto_import'     => self::is_auto_import_enabled(),
			'total_feedbacks' => (int) $total_feedbacks,
			'imported'        => $imported,
			'pending'         => max( 0, $total_feedbacks - $imported ),
		);
	}

	/**
	 * Fournir la configuration IA à Blazing Feedback
	 *
	 * Cette méthode permet à Blazing Feedback d'utiliser la configuration IA centralisée
	 *
	 * @return array|null
	 */
	public static function provide_ai_config() {
		$ai = bzmi_ai();

		if ( ! $ai->is_enabled() ) {
			return null;
		}

		return array(
			'enabled'    => true,
			'provider'   => $ai->get_provider(),
			'api_key'    => $ai->get_api_key(),
			'model'      => $ai->get_model(),
			'params'     => $ai->get_request_params(),
			'api_url'    => $ai->get_api_url(),
			'headers'    => $ai->get_headers(),
		);
	}
}

/**
 * Hook pour la génération de clarifications IA asynchrone
 */
add_action( 'bzmi_generate_ai_clarifications', function( $information_id ) {
	$info = BZMI_Information::find( $information_id );
	if ( ! $info ) {
		return;
	}

	$ai = bzmi_ai();
	$questions = $ai->generate_clarifications( $info );

	if ( is_wp_error( $questions ) || empty( $questions ) ) {
		return;
	}

	// Créer les clarifications
	foreach ( $questions as $question ) {
		BZMI_Clarification::create_ai_suggestion(
			$info->id,
			$question,
			0.85 // Confiance par défaut
		);
	}

	// Avancer à l'étape clarification si des questions ont été générées
	if ( 'information' === $info->icaval_stage ) {
		$info->icaval_stage = 'clarification';
		$info->save();
	}
} );

/**
 * Filtre pour Blazing Feedback pour obtenir la config IA
 */
add_filter( 'wpvfh_ai_config', function( $config ) {
	$bzmi_config = BZMI_Integration_Blazing_Feedback::provide_ai_config();
	return $bzmi_config ?: $config;
} );
