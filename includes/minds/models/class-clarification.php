<?php
/**
 * Modèle Clarification
 *
 * C = CLARIFICATION dans ICAVAL
 *
 * @package Blazing_Minds
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe BZMI_Clarification
 *
 * @since 1.0.0
 */
class BZMI_Clarification extends BZMI_Model_Base {

	/**
	 * Nom de la table
	 *
	 * @var string
	 */
	protected static $table = 'clarifications';

	/**
	 * Colonnes fillables
	 *
	 * @var array
	 */
	protected static $fillable = array(
		'information_id',
		'question',
		'answer',
		'ai_suggested',
		'ai_confidence',
		'resolved',
		'resolved_by',
		'resolved_at',
		'metadata',
		'status',
		'created_by',
	);

	/**
	 * Statuts disponibles
	 *
	 * @return array
	 */
	public static function get_statuses() {
		return array(
			'pending'   => __( 'En attente', 'blazing-minds' ),
			'answered'  => __( 'Répondu', 'blazing-minds' ),
			'validated' => __( 'Validé', 'blazing-minds' ),
			'rejected'  => __( 'Rejeté', 'blazing-minds' ),
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
	 * Obtenir l'utilisateur qui a résolu
	 *
	 * @return WP_User|null
	 */
	public function resolver() {
		if ( $this->resolved_by ) {
			return get_user_by( 'id', $this->resolved_by );
		}
		return null;
	}

	/**
	 * Marquer comme résolu
	 *
	 * @param string $answer Réponse.
	 * @return bool
	 */
	public function resolve( $answer = null ) {
		if ( null !== $answer ) {
			$this->answer = $answer;
		}

		$this->resolved    = true;
		$this->resolved_by = get_current_user_id();
		$this->resolved_at = current_time( 'mysql' );
		$this->status      = 'validated';

		$result = $this->save();

		// Avancer l'information si toutes les clarifications sont résolues
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
		if ( ! $information || 'clarification' !== $information->icaval_stage ) {
			return;
		}

		$clarifications = $information->clarifications();
		$all_resolved   = true;

		foreach ( $clarifications as $clarif ) {
			if ( ! $clarif->resolved ) {
				$all_resolved = false;
				break;
			}
		}

		if ( $all_resolved ) {
			$information->advance_stage();
		}
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

		if ( empty( $this->question ) ) {
			$errors['question'] = __( 'La question est requise.', 'blazing-minds' );
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
	 * Créer une clarification suggérée par l'IA
	 *
	 * @param int    $information_id ID de l'information.
	 * @param string $question       Question.
	 * @param float  $confidence     Confiance de l'IA.
	 * @return BZMI_Clarification|false
	 */
	public static function create_ai_suggestion( $information_id, $question, $confidence = 0.0 ) {
		return self::create( array(
			'information_id' => $information_id,
			'question'       => $question,
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
	 * Obtenir les non résolues
	 *
	 * @return array
	 */
	public static function pending() {
		return static::where( array( 'resolved' => 0 ) );
	}

	/**
	 * Obtenir les suggestions IA
	 *
	 * @return array
	 */
	public static function ai_suggestions() {
		return static::where( array( 'ai_suggested' => 1 ) );
	}
}
