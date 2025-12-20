<?php
/**
 * Modèle Apprenticeship
 *
 * AL = APPRENTISSAGE dans ICAVAL
 * Dernière étape du cycle - capitalisation des connaissances
 *
 * @package Blazing_Minds
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe BZMI_Apprenticeship
 *
 * @since 1.0.0
 */
class BZMI_Apprenticeship extends BZMI_Model_Base {

	/**
	 * Nom de la table
	 *
	 * @var string
	 */
	protected static $table = 'apprenticeships';

	/**
	 * Colonnes fillables
	 *
	 * @var array
	 */
	protected static $fillable = array(
		'source_type',
		'source_id',
		'lesson_type',
		'title',
		'description',
		'context',
		'recommendations',
		'applies_to',
		'reusable',
		'ai_generated',
		'confidence_score',
		'usage_count',
		'last_used_at',
		'tags',
		'metadata',
		'status',
		'created_by',
	);

	/**
	 * Types de source disponibles
	 *
	 * @return array
	 */
	public static function get_source_types() {
		return array(
			'information'   => __( 'Information', 'blazing-minds' ),
			'clarification' => __( 'Clarification', 'blazing-minds' ),
			'action'        => __( 'Action', 'blazing-minds' ),
			'value'         => __( 'Valeur', 'blazing-minds' ),
			'project'       => __( 'Projet', 'blazing-minds' ),
			'portfolio'     => __( 'Portefeuille', 'blazing-minds' ),
			'client'        => __( 'Client', 'blazing-minds' ),
		);
	}

	/**
	 * Types de leçon disponibles
	 *
	 * @return array
	 */
	public static function get_lesson_types() {
		return array(
			'insight'       => __( 'Insight', 'blazing-minds' ),
			'best_practice' => __( 'Bonne pratique', 'blazing-minds' ),
			'pattern'       => __( 'Pattern', 'blazing-minds' ),
			'anti_pattern'  => __( 'Anti-pattern', 'blazing-minds' ),
			'process'       => __( 'Processus', 'blazing-minds' ),
			'solution'      => __( 'Solution', 'blazing-minds' ),
			'mistake'       => __( 'Erreur à éviter', 'blazing-minds' ),
			'success'       => __( 'Facteur de succès', 'blazing-minds' ),
		);
	}

	/**
	 * Statuts disponibles
	 *
	 * @return array
	 */
	public static function get_statuses() {
		return array(
			'active'    => __( 'Actif', 'blazing-minds' ),
			'draft'     => __( 'Brouillon', 'blazing-minds' ),
			'review'    => __( 'En revue', 'blazing-minds' ),
			'archived'  => __( 'Archivé', 'blazing-minds' ),
			'deprecated' => __( 'Obsolète', 'blazing-minds' ),
		);
	}

	/**
	 * Obtenir l'objet source
	 *
	 * @return BZMI_Model_Base|null
	 */
	public function source() {
		$class_map = array(
			'information'   => 'BZMI_Information',
			'clarification' => 'BZMI_Clarification',
			'action'        => 'BZMI_Action',
			'value'         => 'BZMI_Value',
			'project'       => 'BZMI_Project',
			'portfolio'     => 'BZMI_Portfolio',
			'client'        => 'BZMI_Client',
		);

		if ( isset( $class_map[ $this->source_type ] ) && class_exists( $class_map[ $this->source_type ] ) ) {
			return call_user_func( array( $class_map[ $this->source_type ], 'find' ), $this->source_id );
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
	 * Obtenir les domaines d'application
	 *
	 * @return array
	 */
	public function get_applies_to() {
		$applies_to = $this->applies_to;
		if ( is_string( $applies_to ) ) {
			$applies_to = maybe_unserialize( $applies_to );
		}
		return is_array( $applies_to ) ? $applies_to : array();
	}

	/**
	 * Incrémenter le compteur d'utilisation
	 *
	 * @return bool
	 */
	public function increment_usage() {
		$this->usage_count++;
		$this->last_used_at = current_time( 'mysql' );
		return $this->save();
	}

	/**
	 * Obtenir les recommandations comme tableau
	 *
	 * @return array
	 */
	public function get_recommendations() {
		$recommendations = $this->recommendations;
		if ( is_string( $recommendations ) ) {
			$recommendations = maybe_unserialize( $recommendations );
		}
		return is_array( $recommendations ) ? $recommendations : array( $recommendations );
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
	 * Valider les données
	 *
	 * @return array Erreurs de validation.
	 */
	public function validate() {
		$errors = array();

		if ( empty( $this->title ) ) {
			$errors['title'] = __( 'Le titre est requis.', 'blazing-minds' );
		}

		if ( empty( $this->source_type ) ) {
			$errors['source_type'] = __( 'Le type de source est requis.', 'blazing-minds' );
		}

		if ( empty( $this->source_id ) ) {
			$errors['source_id'] = __( 'L\'ID source est requis.', 'blazing-minds' );
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
	 * Créer un apprentissage généré par l'IA
	 *
	 * @param string $source_type Type de source.
	 * @param int    $source_id   ID source.
	 * @param array  $data        Données.
	 * @return BZMI_Apprenticeship|false
	 */
	public static function create_ai_generated( $source_type, $source_id, $data = array() ) {
		$defaults = array(
			'source_type'  => $source_type,
			'source_id'    => $source_id,
			'lesson_type'  => 'insight',
			'ai_generated' => true,
			'reusable'     => true,
			'status'       => 'review',
		);

		return self::create( wp_parse_args( $data, $defaults ) );
	}

	/**
	 * Obtenir par source
	 *
	 * @param string $source_type Type de source.
	 * @param int    $source_id   ID source.
	 * @return array
	 */
	public static function by_source( $source_type, $source_id ) {
		return static::where( array(
			'source_type' => $source_type,
			'source_id'   => $source_id,
		) );
	}

	/**
	 * Obtenir les apprentissages réutilisables
	 *
	 * @return array
	 */
	public static function reusable() {
		return static::where( array(
			'reusable' => 1,
			'status'   => 'active',
		) );
	}

	/**
	 * Obtenir les plus utilisés
	 *
	 * @param int $limit Limite.
	 * @return array
	 */
	public static function most_used( $limit = 10 ) {
		return static::all( array(
			'where'   => array( 'status' => 'active' ),
			'orderby' => 'usage_count',
			'order'   => 'DESC',
			'limit'   => $limit,
		) );
	}

	/**
	 * Rechercher des apprentissages pertinents
	 *
	 * @param string $search Terme de recherche.
	 * @param array  $tags   Tags optionnels.
	 * @return array
	 */
	public static function search_relevant( $search, $tags = array() ) {
		global $wpdb;

		$table = BZMI_Database::get_table_name( static::$table );
		$like  = '%' . $wpdb->esc_like( $search ) . '%';

		$sql = $wpdb->prepare(
			"SELECT * FROM {$table}
			WHERE status = 'active'
			AND reusable = 1
			AND (title LIKE %s OR description LIKE %s OR context LIKE %s)
			ORDER BY usage_count DESC, confidence_score DESC
			LIMIT 20",
			$like,
			$like,
			$like
		);

		$rows   = $wpdb->get_results( $sql, ARRAY_A );
		$models = array();

		foreach ( $rows as $row ) {
			$model = new static( $row );

			// Filtrer par tags si spécifiés
			if ( ! empty( $tags ) ) {
				$model_tags = $model->get_tags();
				if ( ! empty( array_intersect( $tags, $model_tags ) ) ) {
					$models[] = $model;
				}
			} else {
				$models[] = $model;
			}
		}

		return $models;
	}

	/**
	 * Créer un apprentissage depuis une information terminée
	 *
	 * @param BZMI_Information $information Information source.
	 * @return BZMI_Apprenticeship|false
	 */
	public static function create_from_information( $information ) {
		if ( ! $information || 'completed' !== $information->icaval_stage ) {
			return false;
		}

		// Générer un titre basé sur l'information
		$title = sprintf(
			/* translators: %s: Information title */
			__( 'Leçon tirée de : %s', 'blazing-minds' ),
			$information->title
		);

		// Collecter le contexte
		$context = array(
			'information' => $information->to_array(),
			'clarifications' => array(),
			'actions' => array(),
			'values' => array(),
		);

		foreach ( $information->clarifications() as $clarif ) {
			$context['clarifications'][] = $clarif->to_array();
		}

		foreach ( $information->actions() as $action ) {
			$context['actions'][] = $action->to_array();
			foreach ( $action->values() as $value ) {
				$context['values'][] = $value->to_array();
			}
		}

		return self::create( array(
			'source_type'  => 'information',
			'source_id'    => $information->id,
			'lesson_type'  => 'insight',
			'title'        => $title,
			'description'  => $information->content,
			'context'      => $context,
			'tags'         => $information->get_tags(),
			'reusable'     => true,
			'status'       => 'draft',
		) );
	}
}
