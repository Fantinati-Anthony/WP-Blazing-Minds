<?php
/**
 * Modèle Information
 *
 * I = INFORMATION dans ICAVAL
 * Point d'entrée du cycle - reçoit les feedbacks de Blazing Feedback
 *
 * @package Blazing_Minds
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe BZMI_Information
 *
 * @since 1.0.0
 */
class BZMI_Information extends BZMI_Model_Base {

	/**
	 * Nom de la table
	 *
	 * @var string
	 */
	protected static $table = 'informations';

	/**
	 * Colonnes fillables
	 *
	 * @var array
	 */
	protected static $fillable = array(
		'project_id',
		'type',
		'source',
		'source_id',
		'title',
		'content',
		'priority',
		'category',
		'tags',
		'metadata',
		'status',
		'icaval_stage',
		'assigned_to',
		'created_by',
	);

	/**
	 * Types d'information disponibles
	 *
	 * @return array
	 */
	public static function get_types() {
		return array(
			'feedback'       => __( 'Feedback (Blazing Feedback)', 'blazing-minds' ),
			'chrome_addon'   => __( 'Extension Chrome', 'blazing-minds' ),
			'mobile_app'     => __( 'Application mobile', 'blazing-minds' ),
			'email'          => __( 'Email', 'blazing-minds' ),
			'meeting'        => __( 'Réunion', 'blazing-minds' ),
			'document'       => __( 'Document', 'blazing-minds' ),
			'manual'         => __( 'Saisie manuelle', 'blazing-minds' ),
			'api'            => __( 'API externe', 'blazing-minds' ),
		);
	}

	/**
	 * Sources disponibles
	 *
	 * @return array
	 */
	public static function get_sources() {
		return array(
			'blazing_feedback' => __( 'Blazing Feedback', 'blazing-minds' ),
			'chrome_extension' => __( 'Extension Chrome', 'blazing-minds' ),
			'mobile_ios'       => __( 'Application iOS', 'blazing-minds' ),
			'mobile_android'   => __( 'Application Android', 'blazing-minds' ),
			'email_import'     => __( 'Import email', 'blazing-minds' ),
			'api_webhook'      => __( 'Webhook API', 'blazing-minds' ),
			'manual'           => __( 'Saisie manuelle', 'blazing-minds' ),
		);
	}

	/**
	 * Statuts disponibles
	 *
	 * @return array
	 */
	public static function get_statuses() {
		return array(
			'new'        => __( 'Nouveau', 'blazing-minds' ),
			'processing' => __( 'En traitement', 'blazing-minds' ),
			'pending'    => __( 'En attente', 'blazing-minds' ),
			'resolved'   => __( 'Résolu', 'blazing-minds' ),
			'archived'   => __( 'Archivé', 'blazing-minds' ),
		);
	}

	/**
	 * Étapes ICAVAL
	 *
	 * @return array
	 */
	public static function get_icaval_stages() {
		return array(
			'information'    => __( 'Information', 'blazing-minds' ),
			'clarification'  => __( 'Clarification', 'blazing-minds' ),
			'action'         => __( 'Action', 'blazing-minds' ),
			'value'          => __( 'Valeur', 'blazing-minds' ),
			'apprenticeship' => __( 'Apprentissage', 'blazing-minds' ),
			'completed'      => __( 'Terminé', 'blazing-minds' ),
		);
	}

	/**
	 * Priorités disponibles
	 *
	 * @return array
	 */
	public static function get_priorities() {
		return array(
			'low'      => __( 'Basse', 'blazing-minds' ),
			'normal'   => __( 'Normale', 'blazing-minds' ),
			'high'     => __( 'Haute', 'blazing-minds' ),
			'critical' => __( 'Critique', 'blazing-minds' ),
		);
	}

	/**
	 * Obtenir le projet
	 *
	 * @return BZMI_Project|null
	 */
	public function project() {
		return BZMI_Project::find( $this->project_id );
	}

	/**
	 * Obtenir les clarifications
	 *
	 * @return array
	 */
	public function clarifications() {
		return BZMI_Clarification::where( array( 'information_id' => $this->id ) );
	}

	/**
	 * Obtenir les actions
	 *
	 * @return array
	 */
	public function actions() {
		return BZMI_Action::where( array( 'information_id' => $this->id ) );
	}

	/**
	 * Obtenir l'utilisateur assigné
	 *
	 * @return WP_User|null
	 */
	public function assignee() {
		if ( $this->assigned_to ) {
			return get_user_by( 'id', $this->assigned_to );
		}
		return null;
	}

	/**
	 * Obtenir les tags comme tableau
	 *
	 * @return array
	 */
	public function get_tags() {
		if ( empty( $this->tags ) ) {
			return array();
		}

		$tags = $this->tags;
		if ( is_string( $tags ) ) {
			$tags = maybe_unserialize( $tags );
			if ( is_string( $tags ) ) {
				$tags = array_map( 'trim', explode( ',', $tags ) );
			}
		}

		return is_array( $tags ) ? array_filter( $tags ) : array();
	}

	/**
	 * Définir les tags
	 *
	 * @param array|string $tags Tags.
	 * @return $this
	 */
	public function set_tags( $tags ) {
		if ( is_string( $tags ) ) {
			$tags = array_map( 'trim', explode( ',', $tags ) );
		}
		$this->tags = $tags;
		return $this;
	}

	/**
	 * Obtenir les métadonnées décodées
	 *
	 * @return array
	 */
	public function get_metadata() {
		$metadata = $this->metadata;
		if ( is_string( $metadata ) ) {
			$metadata = maybe_unserialize( $metadata );
		}
		return is_array( $metadata ) ? $metadata : array();
	}

	/**
	 * Définir une métadonnée
	 *
	 * @param string $key   Clé.
	 * @param mixed  $value Valeur.
	 * @return $this
	 */
	public function set_metadata( $key, $value ) {
		$metadata         = $this->get_metadata();
		$metadata[ $key ] = $value;
		$this->metadata   = $metadata;
		return $this;
	}

	/**
	 * Avancer à l'étape ICAVAL suivante
	 *
	 * @return bool
	 */
	public function advance_stage() {
		$stages = array_keys( self::get_icaval_stages() );
		$index  = array_search( $this->icaval_stage, $stages, true );

		if ( false !== $index && $index < count( $stages ) - 1 ) {
			$this->icaval_stage = $stages[ $index + 1 ];
			return $this->save();
		}

		return false;
	}

	/**
	 * Revenir à l'étape ICAVAL précédente
	 *
	 * @return bool
	 */
	public function revert_stage() {
		$stages = array_keys( self::get_icaval_stages() );
		$index  = array_search( $this->icaval_stage, $stages, true );

		if ( false !== $index && $index > 0 ) {
			$this->icaval_stage = $stages[ $index - 1 ];
			return $this->save();
		}

		return false;
	}

	/**
	 * Valider les données
	 *
	 * @return array Erreurs de validation.
	 */
	public function validate() {
		$errors = array();

		if ( empty( $this->title ) ) {
			$errors['title'] = __( 'Le titre est requis.', 'blazing-minds' );
		}

		if ( empty( $this->project_id ) ) {
			$errors['project_id'] = __( 'Le projet est requis.', 'blazing-minds' );
		} elseif ( ! BZMI_Project::find( $this->project_id ) ) {
			$errors['project_id'] = __( 'Le projet sélectionné n\'existe pas.', 'blazing-minds' );
		}

		$valid_types = array_keys( self::get_types() );
		if ( ! empty( $this->type ) && ! in_array( $this->type, $valid_types, true ) ) {
			$errors['type'] = __( 'Type d\'information invalide.', 'blazing-minds' );
		}

		return $errors;
	}

	/**
	 * Sauvegarder avec validation
	 *
	 * @return bool|int|array False, ID, ou array d'erreurs.
	 */
	public function save_validated() {
		$errors = $this->validate();

		if ( ! empty( $errors ) ) {
			return $errors;
		}

		return $this->save();
	}

	/**
	 * Créer depuis un feedback Blazing Feedback
	 *
	 * @param array $feedback_data Données du feedback.
	 * @param int   $project_id    ID du projet cible.
	 * @return BZMI_Information|false
	 */
	public static function create_from_feedback( $feedback_data, $project_id ) {
		$data = array(
			'project_id'  => $project_id,
			'type'        => 'feedback',
			'source'      => 'blazing_feedback',
			'source_id'   => isset( $feedback_data['id'] ) ? $feedback_data['id'] : null,
			'title'       => isset( $feedback_data['message'] ) ? wp_trim_words( $feedback_data['message'], 10, '...' ) : __( 'Nouveau feedback', 'blazing-minds' ),
			'content'     => isset( $feedback_data['message'] ) ? $feedback_data['message'] : '',
			'priority'    => isset( $feedback_data['mood'] ) ? self::map_mood_to_priority( $feedback_data['mood'] ) : 'normal',
			'category'    => isset( $feedback_data['type'] ) ? $feedback_data['type'] : 'general',
			'metadata'    => $feedback_data,
			'status'      => 'new',
			'icaval_stage' => 'information',
		);

		return self::create( $data );
	}

	/**
	 * Mapper le mood du feedback à une priorité
	 *
	 * @param string $mood Mood du feedback.
	 * @return string
	 */
	private static function map_mood_to_priority( $mood ) {
		$mapping = array(
			'angry'       => 'critical',
			'sad'         => 'high',
			'neutral'     => 'normal',
			'happy'       => 'low',
			'very_happy'  => 'low',
		);

		return isset( $mapping[ $mood ] ) ? $mapping[ $mood ] : 'normal';
	}

	/**
	 * Obtenir par projet
	 *
	 * @param int $project_id ID du projet.
	 * @return array
	 */
	public static function by_project( $project_id ) {
		return static::where( array( 'project_id' => $project_id ) );
	}

	/**
	 * Obtenir par étape ICAVAL
	 *
	 * @param string $stage Étape ICAVAL.
	 * @return array
	 */
	public static function by_stage( $stage ) {
		return static::where( array( 'icaval_stage' => $stage ) );
	}

	/**
	 * Obtenir par source
	 *
	 * @param string $source Source.
	 * @return array
	 */
	public static function by_source( $source ) {
		return static::where( array( 'source' => $source ) );
	}

	/**
	 * Rechercher
	 *
	 * @param string $search Terme de recherche.
	 * @return array
	 */
	public static function search( $search ) {
		global $wpdb;

		$table = BZMI_Database::get_table_name( static::$table );
		$like  = '%' . $wpdb->esc_like( $search ) . '%';

		$sql = $wpdb->prepare(
			"SELECT * FROM {$table} WHERE title LIKE %s OR content LIKE %s ORDER BY created_at DESC",
			$like,
			$like
		);

		$rows   = $wpdb->get_results( $sql, ARRAY_A );
		$models = array();

		foreach ( $rows as $row ) {
			$models[] = new static( $row );
		}

		return $models;
	}
}
