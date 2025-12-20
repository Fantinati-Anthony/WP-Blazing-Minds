<?php
/**
 * Classe de base pour les modèles
 *
 * @package Blazing_Minds
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe abstraite BZMI_Model_Base
 *
 * Fournit les méthodes CRUD de base pour tous les modèles
 *
 * @since 1.0.0
 */
abstract class BZMI_Model_Base {

	/**
	 * Nom de la table (sans préfixe)
	 *
	 * @var string
	 */
	protected static $table = '';

	/**
	 * Clé primaire
	 *
	 * @var string
	 */
	protected static $primary_key = 'id';

	/**
	 * Colonnes fillables (modifiables)
	 *
	 * @var array
	 */
	protected static $fillable = array();

	/**
	 * ID de l'enregistrement
	 *
	 * @var int
	 */
	public $id;

	/**
	 * Données de l'enregistrement
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * Données originales (pour détecter les changements)
	 *
	 * @var array
	 */
	protected $original = array();

	/**
	 * Constructeur
	 *
	 * @param array $data Données initiales.
	 */
	public function __construct( $data = array() ) {
		if ( ! empty( $data ) ) {
			$this->fill( $data );
			$this->original = $this->data;
			if ( isset( $data[ static::$primary_key ] ) ) {
				$this->id = (int) $data[ static::$primary_key ];
			}
		}
	}

	/**
	 * Remplir le modèle avec des données
	 *
	 * @param array $data Données.
	 * @return $this
	 */
	public function fill( $data ) {
		foreach ( $data as $key => $value ) {
			if ( in_array( $key, static::$fillable, true ) || $key === static::$primary_key ) {
				$this->data[ $key ] = $value;
			}
		}
		return $this;
	}

	/**
	 * Obtenir une valeur
	 *
	 * @param string $key Clé.
	 * @return mixed
	 */
	public function __get( $key ) {
		if ( $key === static::$primary_key ) {
			return $this->id;
		}
		return isset( $this->data[ $key ] ) ? $this->data[ $key ] : null;
	}

	/**
	 * Définir une valeur
	 *
	 * @param string $key   Clé.
	 * @param mixed  $value Valeur.
	 */
	public function __set( $key, $value ) {
		if ( $key === static::$primary_key ) {
			$this->id = (int) $value;
		} elseif ( in_array( $key, static::$fillable, true ) ) {
			$this->data[ $key ] = $value;
		}
	}

	/**
	 * Vérifier si une propriété existe
	 *
	 * @param string $key Clé.
	 * @return bool
	 */
	public function __isset( $key ) {
		return isset( $this->data[ $key ] );
	}

	/**
	 * Obtenir toutes les données
	 *
	 * @return array
	 */
	public function to_array() {
		$data = $this->data;
		if ( $this->id ) {
			$data[ static::$primary_key ] = $this->id;
		}
		return $data;
	}

	/**
	 * Convertir en JSON
	 *
	 * @return string
	 */
	public function to_json() {
		return wp_json_encode( $this->to_array() );
	}

	/**
	 * Sauvegarder le modèle
	 *
	 * @return bool|int
	 */
	public function save() {
		$data = $this->get_saveable_data();

		if ( $this->id ) {
			// Update
			$data['updated_at'] = current_time( 'mysql' );
			$result = BZMI_Database::update(
				static::$table,
				$data,
				array( static::$primary_key => $this->id )
			);

			if ( false !== $result ) {
				$this->log_activity( 'updated' );
				return true;
			}
			return false;
		} else {
			// Insert
			$data['created_at'] = current_time( 'mysql' );
			$data['updated_at'] = current_time( 'mysql' );

			if ( ! isset( $data['created_by'] ) && is_user_logged_in() ) {
				$data['created_by'] = get_current_user_id();
			}

			$id = BZMI_Database::insert( static::$table, $data );

			if ( $id ) {
				$this->id = $id;
				$this->log_activity( 'created' );
				return $id;
			}
			return false;
		}
	}

	/**
	 * Supprimer le modèle
	 *
	 * @return bool
	 */
	public function delete() {
		if ( ! $this->id ) {
			return false;
		}

		$this->log_activity( 'deleted' );

		$result = BZMI_Database::delete(
			static::$table,
			array( static::$primary_key => $this->id )
		);

		if ( false !== $result ) {
			$this->id = null;
			return true;
		}

		return false;
	}

	/**
	 * Obtenir les données sauvegardables
	 *
	 * @return array
	 */
	protected function get_saveable_data() {
		$data = array();
		foreach ( static::$fillable as $key ) {
			if ( isset( $this->data[ $key ] ) ) {
				$value = $this->data[ $key ];

				// Sérialiser les tableaux et objets
				if ( is_array( $value ) || is_object( $value ) ) {
					$value = maybe_serialize( $value );
				}

				$data[ $key ] = $value;
			}
		}
		return $data;
	}

	/**
	 * Journaliser l'activité
	 *
	 * @param string $action Action effectuée.
	 * @return void
	 */
	protected function log_activity( $action ) {
		if ( ! $this->id ) {
			return;
		}

		$table = BZMI_Database::get_table_name( 'activity_log' );

		global $wpdb;
		$wpdb->insert(
			$table,
			array(
				'object_type' => static::$table,
				'object_id'   => $this->id,
				'action'      => $action,
				'description' => sprintf(
					/* translators: 1: Action name, 2: Object type, 3: Object ID */
					__( '%1$s %2$s #%3$d', 'blazing-minds' ),
					ucfirst( $action ),
					static::$table,
					$this->id
				),
				'old_value'   => 'updated' === $action ? maybe_serialize( $this->original ) : null,
				'new_value'   => 'deleted' !== $action ? maybe_serialize( $this->data ) : null,
				'ip_address'  => $this->get_client_ip(),
				'user_agent'  => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
				'user_id'     => get_current_user_id(),
				'created_at'  => current_time( 'mysql' ),
			)
		);
	}

	/**
	 * Obtenir l'IP du client
	 *
	 * @return string
	 */
	protected function get_client_ip() {
		$ip_keys = array(
			'HTTP_CF_CONNECTING_IP',
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		);

		foreach ( $ip_keys as $key ) {
			if ( ! empty( $_SERVER[ $key ] ) ) {
				$ip = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
				// En cas de multiple IPs (X-Forwarded-For)
				if ( strpos( $ip, ',' ) !== false ) {
					$ip = trim( explode( ',', $ip )[0] );
				}
				if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					return $ip;
				}
			}
		}

		return '0.0.0.0';
	}

	/**
	 * Trouver par ID
	 *
	 * @param int $id ID de l'enregistrement.
	 * @return static|null
	 */
	public static function find( $id ) {
		$table = BZMI_Database::get_table_name( static::$table );

		$row = BZMI_Database::get_row(
			"SELECT * FROM {$table} WHERE " . static::$primary_key . " = %d",
			array( $id )
		);

		if ( $row ) {
			return new static( $row );
		}

		return null;
	}

	/**
	 * Trouver par ID ou échouer
	 *
	 * @param int $id ID de l'enregistrement.
	 * @return static
	 * @throws Exception Si non trouvé.
	 */
	public static function find_or_fail( $id ) {
		$model = static::find( $id );

		if ( ! $model ) {
			throw new Exception(
				sprintf(
					/* translators: 1: Model name, 2: ID */
					__( '%1$s avec l\'ID %2$d introuvable.', 'blazing-minds' ),
					static::class,
					$id
				)
			);
		}

		return $model;
	}

	/**
	 * Obtenir tous les enregistrements
	 *
	 * @param array $args Arguments de requête.
	 * @return array
	 */
	public static function all( $args = array() ) {
		$defaults = array(
			'orderby'  => static::$primary_key,
			'order'    => 'DESC',
			'limit'    => 0,
			'offset'   => 0,
			'where'    => array(),
		);

		$args  = wp_parse_args( $args, $defaults );
		$table = BZMI_Database::get_table_name( static::$table );

		$sql = "SELECT * FROM {$table}";

		// WHERE
		if ( ! empty( $args['where'] ) ) {
			$conditions = array();
			foreach ( $args['where'] as $key => $value ) {
				if ( is_array( $value ) ) {
					$placeholders = implode( ',', array_fill( 0, count( $value ), '%s' ) );
					$conditions[] = "{$key} IN ({$placeholders})";
				} else {
					$conditions[] = "{$key} = %s";
				}
			}
			$sql .= ' WHERE ' . implode( ' AND ', $conditions );
		}

		// ORDER BY
		$sql .= sprintf(
			' ORDER BY %s %s',
			esc_sql( $args['orderby'] ),
			'ASC' === strtoupper( $args['order'] ) ? 'ASC' : 'DESC'
		);

		// LIMIT
		if ( $args['limit'] > 0 ) {
			$sql .= sprintf( ' LIMIT %d', (int) $args['limit'] );
			if ( $args['offset'] > 0 ) {
				$sql .= sprintf( ' OFFSET %d', (int) $args['offset'] );
			}
		}

		// Préparer les valeurs
		$values = array();
		if ( ! empty( $args['where'] ) ) {
			foreach ( $args['where'] as $value ) {
				if ( is_array( $value ) ) {
					$values = array_merge( $values, $value );
				} else {
					$values[] = $value;
				}
			}
		}

		$rows = ! empty( $values )
			? BZMI_Database::get_results( $sql, $values )
			: BZMI_Database::get_results( $sql );

		$models = array();
		foreach ( $rows as $row ) {
			$models[] = new static( $row );
		}

		return $models;
	}

	/**
	 * Trouver par critères
	 *
	 * @param array $where Conditions WHERE.
	 * @return array
	 */
	public static function where( $where ) {
		return static::all( array( 'where' => $where ) );
	}

	/**
	 * Trouver le premier correspondant
	 *
	 * @param array $where Conditions WHERE.
	 * @return static|null
	 */
	public static function first_where( $where ) {
		$results = static::all( array(
			'where' => $where,
			'limit' => 1,
		) );

		return ! empty( $results ) ? $results[0] : null;
	}

	/**
	 * Compter les enregistrements
	 *
	 * @param array $where Conditions WHERE.
	 * @return int
	 */
	public static function count( $where = array() ) {
		$table = BZMI_Database::get_table_name( static::$table );

		$sql = "SELECT COUNT(*) FROM {$table}";

		if ( ! empty( $where ) ) {
			$conditions = array();
			$values     = array();
			foreach ( $where as $key => $value ) {
				$conditions[] = "{$key} = %s";
				$values[]     = $value;
			}
			$sql .= ' WHERE ' . implode( ' AND ', $conditions );
			return (int) BZMI_Database::get_var( $sql, $values );
		}

		return (int) BZMI_Database::get_var( $sql );
	}

	/**
	 * Créer un nouvel enregistrement
	 *
	 * @param array $data Données.
	 * @return static|false
	 */
	public static function create( $data ) {
		$model = new static( $data );
		$id    = $model->save();

		return $id ? $model : false;
	}

	/**
	 * Mettre à jour par ID
	 *
	 * @param int   $id   ID de l'enregistrement.
	 * @param array $data Données à mettre à jour.
	 * @return bool
	 */
	public static function update_by_id( $id, $data ) {
		$model = static::find( $id );

		if ( ! $model ) {
			return false;
		}

		$model->fill( $data );
		return $model->save();
	}

	/**
	 * Supprimer par ID
	 *
	 * @param int $id ID de l'enregistrement.
	 * @return bool
	 */
	public static function delete_by_id( $id ) {
		$model = static::find( $id );

		if ( ! $model ) {
			return false;
		}

		return $model->delete();
	}

	/**
	 * Paginer les résultats
	 *
	 * @param int   $page     Numéro de page.
	 * @param int   $per_page Éléments par page.
	 * @param array $args     Arguments supplémentaires.
	 * @return array
	 */
	public static function paginate( $page = 1, $per_page = 20, $args = array() ) {
		$page     = max( 1, (int) $page );
		$per_page = max( 1, (int) $per_page );
		$offset   = ( $page - 1 ) * $per_page;

		$args['limit']  = $per_page;
		$args['offset'] = $offset;

		$items = static::all( $args );
		$total = static::count( isset( $args['where'] ) ? $args['where'] : array() );

		return array(
			'items'        => $items,
			'total'        => $total,
			'per_page'     => $per_page,
			'current_page' => $page,
			'total_pages'  => ceil( $total / $per_page ),
		);
	}
}
