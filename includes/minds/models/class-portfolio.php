<?php
/**
 * Modèle Portfolio
 *
 * P = PORTEFEUILLE dans CPPICAVAL
 *
 * @package Blazing_Minds
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe BZMI_Portfolio
 *
 * @since 1.0.0
 */
class BZMI_Portfolio extends BZMI_Model_Base {

	/**
	 * Nom de la table
	 *
	 * @var string
	 */
	protected static $table = 'portfolios';

	/**
	 * Colonnes fillables
	 *
	 * @var array
	 */
	protected static $fillable = array(
		'client_id',
		'name',
		'description',
		'color',
		'icon',
		'priority',
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
			'active'   => __( 'Actif', 'blazing-minds' ),
			'inactive' => __( 'Inactif', 'blazing-minds' ),
			'archived' => __( 'Archivé', 'blazing-minds' ),
		);
	}

	/**
	 * Couleurs prédéfinies
	 *
	 * @return array
	 */
	public static function get_colors() {
		return array(
			'#3498db' => __( 'Bleu', 'blazing-minds' ),
			'#2ecc71' => __( 'Vert', 'blazing-minds' ),
			'#e74c3c' => __( 'Rouge', 'blazing-minds' ),
			'#f39c12' => __( 'Orange', 'blazing-minds' ),
			'#9b59b6' => __( 'Violet', 'blazing-minds' ),
			'#1abc9c' => __( 'Turquoise', 'blazing-minds' ),
			'#34495e' => __( 'Gris foncé', 'blazing-minds' ),
			'#e91e63' => __( 'Rose', 'blazing-minds' ),
		);
	}

	/**
	 * Obtenir le client
	 *
	 * @return BZMI_Client|null
	 */
	public function client() {
		return BZMI_Client::find( $this->client_id );
	}

	/**
	 * Obtenir les projets du portefeuille
	 *
	 * @return array
	 */
	public function projects() {
		return BZMI_Project::where( array( 'portfolio_id' => $this->id ) );
	}

	/**
	 * Obtenir le nombre de projets
	 *
	 * @return int
	 */
	public function projects_count() {
		return BZMI_Project::count( array( 'portfolio_id' => $this->id ) );
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
	 * Obtenir le créateur
	 *
	 * @return WP_User|null
	 */
	public function creator() {
		if ( $this->created_by ) {
			return get_user_by( 'id', $this->created_by );
		}
		return null;
	}

	/**
	 * Valider les données
	 *
	 * @return array Erreurs de validation.
	 */
	public function validate() {
		$errors = array();

		if ( empty( $this->name ) ) {
			$errors['name'] = __( 'Le nom est requis.', 'blazing-minds' );
		}

		if ( empty( $this->client_id ) ) {
			$errors['client_id'] = __( 'Le client est requis.', 'blazing-minds' );
		} elseif ( ! BZMI_Client::find( $this->client_id ) ) {
			$errors['client_id'] = __( 'Le client sélectionné n\'existe pas.', 'blazing-minds' );
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
	 * Calculer la progression globale
	 *
	 * @return int Pourcentage de progression.
	 */
	public function calculate_progress() {
		$projects = $this->projects();

		if ( empty( $projects ) ) {
			return 0;
		}

		$total_progress = 0;
		foreach ( $projects as $project ) {
			$total_progress += (int) $project->progress;
		}

		return (int) round( $total_progress / count( $projects ) );
	}

	/**
	 * Obtenir les statistiques du portefeuille
	 *
	 * @return array
	 */
	public function get_stats() {
		$projects = $this->projects();

		$active    = 0;
		$completed = 0;
		$pending   = 0;
		$budget    = 0;

		foreach ( $projects as $project ) {
			switch ( $project->status ) {
				case 'active':
				case 'in_progress':
					$active++;
					break;
				case 'completed':
					$completed++;
					break;
				case 'pending':
					$pending++;
					break;
			}
			$budget += (float) $project->budget;
		}

		return array(
			'total_projects' => count( $projects ),
			'active'         => $active,
			'completed'      => $completed,
			'pending'        => $pending,
			'total_budget'   => $budget,
			'progress'       => $this->calculate_progress(),
		);
	}

	/**
	 * Obtenir les portefeuilles par client
	 *
	 * @param int $client_id ID du client.
	 * @return array
	 */
	public static function by_client( $client_id ) {
		return static::where( array( 'client_id' => $client_id ) );
	}
}
