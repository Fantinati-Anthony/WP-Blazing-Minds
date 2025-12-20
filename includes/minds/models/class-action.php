<?php
/**
 * Modèle Action
 *
 * A = ACTION dans ICAVAL
 *
 * @package Blazing_Minds
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe BZMI_Action
 *
 * @since 1.0.0
 */
class BZMI_Action extends BZMI_Model_Base {

	/**
	 * Nom de la table
	 *
	 * @var string
	 */
	protected static $table = 'actions';

	/**
	 * Colonnes fillables
	 *
	 * @var array
	 */
	protected static $fillable = array(
		'information_id',
		'title',
		'description',
		'action_type',
		'priority',
		'effort_estimate',
		'due_date',
		'completed_at',
		'ai_suggested',
		'ai_confidence',
		'metadata',
		'status',
		'assigned_to',
		'created_by',
	);

	/**
	 * Types d'action disponibles
	 *
	 * @return array
	 */
	public static function get_action_types() {
		return array(
			'task'        => __( 'Tâche', 'blazing-minds' ),
			'bug_fix'     => __( 'Correction de bug', 'blazing-minds' ),
			'feature'     => __( 'Nouvelle fonctionnalité', 'blazing-minds' ),
			'improvement' => __( 'Amélioration', 'blazing-minds' ),
			'research'    => __( 'Recherche', 'blazing-minds' ),
			'meeting'     => __( 'Réunion', 'blazing-minds' ),
			'review'      => __( 'Revue', 'blazing-minds' ),
			'other'       => __( 'Autre', 'blazing-minds' ),
		);
	}

	/**
	 * Statuts disponibles
	 *
	 * @return array
	 */
	public static function get_statuses() {
		return array(
			'pending'     => __( 'En attente', 'blazing-minds' ),
			'in_progress' => __( 'En cours', 'blazing-minds' ),
			'review'      => __( 'En revue', 'blazing-minds' ),
			'completed'   => __( 'Terminé', 'blazing-minds' ),
			'cancelled'   => __( 'Annulé', 'blazing-minds' ),
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
	 * Estimations d'effort
	 *
	 * @return array
	 */
	public static function get_effort_estimates() {
		return array(
			'xs'  => __( 'XS (< 1h)', 'blazing-minds' ),
			's'   => __( 'S (1-4h)', 'blazing-minds' ),
			'm'   => __( 'M (4-8h)', 'blazing-minds' ),
			'l'   => __( 'L (1-3 jours)', 'blazing-minds' ),
			'xl'  => __( 'XL (3-5 jours)', 'blazing-minds' ),
			'xxl' => __( 'XXL (> 5 jours)', 'blazing-minds' ),
		);
	}

	/**
	 * Obtenir l'information parente
	 *
	 * @return BZMI_Information|null
	 */
	public function information() {
		return BZMI_Information::find( $this->information_id );
	}

	/**
	 * Obtenir les valeurs générées
	 *
	 * @return array
	 */
	public function values() {
		return BZMI_Value::where( array( 'action_id' => $this->id ) );
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
	 * Marquer comme terminé
	 *
	 * @return bool
	 */
	public function complete() {
		$this->status       = 'completed';
		$this->completed_at = current_time( 'mysql' );

		$result = $this->save();

		// Avancer l'information si toutes les actions sont terminées
		if ( $result ) {
			$this->check_and_advance_information();
		}

		return $result;
	}

	/**
	 * Vérifier et avancer l'information si possible
	 *
	 * @return void
	 */
	private function check_and_advance_information() {
		$information = $this->information();
		if ( ! $information || 'action' !== $information->icaval_stage ) {
			return;
		}

		$actions       = $information->actions();
		$all_completed = true;

		foreach ( $actions as $action ) {
			if ( 'completed' !== $action->status && 'cancelled' !== $action->status ) {
				$all_completed = false;
				break;
			}
		}

		if ( $all_completed ) {
			$information->advance_stage();
		}
	}

	/**
	 * Vérifier si en retard
	 *
	 * @return bool
	 */
	public function is_overdue() {
		if ( empty( $this->due_date ) ) {
			return false;
		}

		if ( 'completed' === $this->status || 'cancelled' === $this->status ) {
			return false;
		}

		return strtotime( $this->due_date ) < current_time( 'timestamp' );
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

		if ( empty( $this->information_id ) ) {
			$errors['information_id'] = __( 'L\'information est requise.', 'blazing-minds' );
		} elseif ( ! BZMI_Information::find( $this->information_id ) ) {
			$errors['information_id'] = __( 'L\'information sélectionnée n\'existe pas.', 'blazing-minds' );
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
	 * Créer une action suggérée par l'IA
	 *
	 * @param int    $information_id ID de l'information.
	 * @param string $title          Titre.
	 * @param string $description    Description.
	 * @param float  $confidence     Confiance de l'IA.
	 * @return BZMI_Action|false
	 */
	public static function create_ai_suggestion( $information_id, $title, $description = '', $confidence = 0.0 ) {
		return self::create( array(
			'information_id' => $information_id,
			'title'          => $title,
			'description'    => $description,
			'ai_suggested'   => true,
			'ai_confidence'  => $confidence,
			'status'         => 'pending',
		) );
	}

	/**
	 * Obtenir par information
	 *
	 * @param int $information_id ID de l'information.
	 * @return array
	 */
	public static function by_information( $information_id ) {
		return static::where( array( 'information_id' => $information_id ) );
	}

	/**
	 * Obtenir les actions en retard
	 *
	 * @return array
	 */
	public static function overdue() {
		global $wpdb;

		$table = BZMI_Database::get_table_name( static::$table );
		$now   = current_time( 'mysql' );

		$sql = $wpdb->prepare(
			"SELECT * FROM {$table} WHERE due_date < %s AND status NOT IN ('completed', 'cancelled') ORDER BY due_date ASC",
			$now
		);

		$rows   = $wpdb->get_results( $sql, ARRAY_A );
		$models = array();

		foreach ( $rows as $row ) {
			$models[] = new static( $row );
		}

		return $models;
	}

	/**
	 * Obtenir les actions assignées à un utilisateur
	 *
	 * @param int $user_id ID de l'utilisateur.
	 * @return array
	 */
	public static function assigned_to( $user_id ) {
		return static::where( array( 'assigned_to' => $user_id ) );
	}
}
