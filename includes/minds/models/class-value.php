<?php
/**
 * Modèle Value
 *
 * V = VALEUR dans ICAVAL
 *
 * @package Blazing_Minds
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe BZMI_Value
 *
 * @since 1.0.0
 */
class BZMI_Value extends BZMI_Model_Base {

	/**
	 * Nom de la table
	 *
	 * @var string
	 */
	protected static $table = 'values';

	/**
	 * Colonnes fillables
	 *
	 * @var array
	 */
	protected static $fillable = array(
		'action_id',
		'value_type',
		'title',
		'description',
		'impact_score',
		'monetary_value',
		'time_saved',
		'satisfaction_increase',
		'metrics',
		'ai_calculated',
		'validated',
		'validated_by',
		'validated_at',
		'metadata',
		'status',
		'created_by',
	);

	/**
	 * Types de valeur disponibles
	 *
	 * @return array
	 */
	public static function get_value_types() {
		return array(
			'business'     => __( 'Valeur business', 'blazing-minds' ),
			'technical'    => __( 'Valeur technique', 'blazing-minds' ),
			'user'         => __( 'Valeur utilisateur', 'blazing-minds' ),
			'operational'  => __( 'Valeur opérationnelle', 'blazing-minds' ),
			'strategic'    => __( 'Valeur stratégique', 'blazing-minds' ),
			'compliance'   => __( 'Conformité', 'blazing-minds' ),
		);
	}

	/**
	 * Statuts disponibles
	 *
	 * @return array
	 */
	public static function get_statuses() {
		return array(
			'estimated'  => __( 'Estimé', 'blazing-minds' ),
			'measured'   => __( 'Mesuré', 'blazing-minds' ),
			'validated'  => __( 'Validé', 'blazing-minds' ),
			'reported'   => __( 'Reporté', 'blazing-minds' ),
		);
	}

	/**
	 * Obtenir l'action parente
	 *
	 * @return BZMI_Action|null
	 */
	public function action() {
		return BZMI_Action::find( $this->action_id );
	}

	/**
	 * Obtenir l'information (via l'action)
	 *
	 * @return BZMI_Information|null
	 */
	public function information() {
		$action = $this->action();
		return $action ? $action->information() : null;
	}

	/**
	 * Obtenir l'utilisateur validateur
	 *
	 * @return WP_User|null
	 */
	public function validator() {
		if ( $this->validated_by ) {
			return get_user_by( 'id', $this->validated_by );
		}
		return null;
	}

	/**
	 * Valider la valeur
	 *
	 * @return bool
	 */
	public function validate_value() {
		$this->validated    = true;
		$this->validated_by = get_current_user_id();
		$this->validated_at = current_time( 'mysql' );
		$this->status       = 'validated';

		$result = $this->save();

		// Avancer l'information si toutes les valeurs sont validées
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
		$action = $this->action();
		if ( ! $action ) {
			return;
		}

		$information = $action->information();
		if ( ! $information || 'value' !== $information->icaval_stage ) {
			return;
		}

		$actions       = $information->actions();
		$all_validated = true;

		foreach ( $actions as $act ) {
			$values = $act->values();
			foreach ( $values as $value ) {
				if ( ! $value->validated ) {
					$all_validated = false;
					break 2;
				}
			}
		}

		if ( $all_validated ) {
			$information->advance_stage();
		}
	}

	/**
	 * Obtenir les métriques décodées
	 *
	 * @return array
	 */
	public function get_metrics() {
		$metrics = $this->metrics;
		if ( is_string( $metrics ) ) {
			$metrics = maybe_unserialize( $metrics );
		}
		return is_array( $metrics ) ? $metrics : array();
	}

	/**
	 * Définir une métrique
	 *
	 * @param string $key   Clé.
	 * @param mixed  $value Valeur.
	 * @return $this
	 */
	public function set_metric( $key, $value ) {
		$metrics         = $this->get_metrics();
		$metrics[ $key ] = $value;
		$this->metrics   = $metrics;
		return $this;
	}

	/**
	 * Calculer le score d'impact total
	 *
	 * @return int
	 */
	public function calculate_impact_score() {
		$score = 0;

		// Score basé sur la valeur monétaire (0-30 points)
		if ( $this->monetary_value > 0 ) {
			if ( $this->monetary_value >= 10000 ) {
				$score += 30;
			} elseif ( $this->monetary_value >= 5000 ) {
				$score += 20;
			} elseif ( $this->monetary_value >= 1000 ) {
				$score += 10;
			} else {
				$score += 5;
			}
		}

		// Score basé sur le temps économisé (0-30 points)
		if ( $this->time_saved > 0 ) {
			if ( $this->time_saved >= 480 ) { // 8h+
				$score += 30;
			} elseif ( $this->time_saved >= 240 ) { // 4h+
				$score += 20;
			} elseif ( $this->time_saved >= 60 ) { // 1h+
				$score += 10;
			} else {
				$score += 5;
			}
		}

		// Score basé sur l'augmentation de satisfaction (0-40 points)
		if ( $this->satisfaction_increase > 0 ) {
			$score += min( 40, $this->satisfaction_increase * 4 );
		}

		return min( 100, $score );
	}

	/**
	 * Mettre à jour le score d'impact
	 *
	 * @return bool
	 */
	public function update_impact_score() {
		$this->impact_score = $this->calculate_impact_score();
		return $this->save();
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

		if ( empty( $this->action_id ) ) {
			$errors['action_id'] = __( 'L\'action est requise.', 'blazing-minds' );
		} elseif ( ! BZMI_Action::find( $this->action_id ) ) {
			$errors['action_id'] = __( 'L\'action sélectionnée n\'existe pas.', 'blazing-minds' );
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

		// Calculer le score d'impact avant sauvegarde
		$this->impact_score = $this->calculate_impact_score();

		return $this->save();
	}

	/**
	 * Créer une valeur calculée par l'IA
	 *
	 * @param int   $action_id ID de l'action.
	 * @param array $data      Données de la valeur.
	 * @return BZMI_Value|false
	 */
	public static function create_ai_calculated( $action_id, $data = array() ) {
		$defaults = array(
			'action_id'     => $action_id,
			'value_type'    => 'business',
			'title'         => __( 'Valeur calculée par IA', 'blazing-minds' ),
			'ai_calculated' => true,
			'status'        => 'estimated',
		);

		return self::create( wp_parse_args( $data, $defaults ) );
	}

	/**
	 * Obtenir par action
	 *
	 * @param int $action_id ID de l'action.
	 * @return array
	 */
	public static function by_action( $action_id ) {
		return static::where( array( 'action_id' => $action_id ) );
	}

	/**
	 * Obtenir les valeurs non validées
	 *
	 * @return array
	 */
	public static function pending_validation() {
		return static::where( array( 'validated' => 0 ) );
	}

	/**
	 * Calculer la valeur totale d'un projet
	 *
	 * @param int $project_id ID du projet.
	 * @return array
	 */
	public static function calculate_project_value( $project_id ) {
		global $wpdb;

		$values_table = BZMI_Database::get_table_name( 'values' );
		$actions_table = BZMI_Database::get_table_name( 'actions' );
		$info_table = BZMI_Database::get_table_name( 'informations' );

		$sql = $wpdb->prepare(
			"SELECT
				SUM(v.monetary_value) as total_monetary,
				SUM(v.time_saved) as total_time_saved,
				AVG(v.satisfaction_increase) as avg_satisfaction,
				AVG(v.impact_score) as avg_impact
			FROM {$values_table} v
			JOIN {$actions_table} a ON v.action_id = a.id
			JOIN {$info_table} i ON a.information_id = i.id
			WHERE i.project_id = %d AND v.validated = 1",
			$project_id
		);

		return $wpdb->get_row( $sql, ARRAY_A );
	}
}
