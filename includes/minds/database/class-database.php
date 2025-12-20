<?php
/**
 * Classe de gestion de la base de données
 *
 * @package Blazing_Minds
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe BZMI_Database
 *
 * Gère les opérations de base de données et les paramètres
 *
 * @since 1.0.0
 */
class BZMI_Database {

	/**
	 * Préfixe des tables
	 *
	 * @var string
	 */
	const TABLE_PREFIX = 'blazingminds_';

	/**
	 * Cache des paramètres
	 *
	 * @var array
	 */
	private static $settings_cache = array();

	/**
	 * Obtenir le nom complet d'une table
	 *
	 * @param string $table Nom de la table sans préfixe.
	 * @return string
	 */
	public static function get_table_name( $table ) {
		global $wpdb;
		return $wpdb->prefix . self::TABLE_PREFIX . $table;
	}

	/**
	 * Obtenir un paramètre
	 *
	 * @param string $key     Clé du paramètre.
	 * @param mixed  $default Valeur par défaut.
	 * @return mixed
	 */
	public static function get_setting( $key, $default = null ) {
		// Vérifier le cache
		if ( isset( self::$settings_cache[ $key ] ) ) {
			return self::$settings_cache[ $key ];
		}

		global $wpdb;
		$table = self::get_table_name( 'settings' );

		// Vérifier si la table existe
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			return $default;
		}

		$result = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT setting_value FROM {$table} WHERE setting_key = %s",
				$key
			)
		);

		if ( null === $result ) {
			return $default;
		}

		$value = maybe_unserialize( $result );
		self::$settings_cache[ $key ] = $value;

		return $value;
	}

	/**
	 * Définir un paramètre
	 *
	 * @param string $key   Clé du paramètre.
	 * @param mixed  $value Valeur du paramètre.
	 * @return bool
	 */
	public static function update_setting( $key, $value ) {
		global $wpdb;
		$table = self::get_table_name( 'settings' );

		// Vérifier si la table existe
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			return false;
		}

		$serialized_value = maybe_serialize( $value );

		// Vérifier si le paramètre existe
		$exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE setting_key = %s",
				$key
			)
		);

		if ( $exists ) {
			$result = $wpdb->update(
				$table,
				array(
					'setting_value' => $serialized_value,
					'updated_at'    => current_time( 'mysql' ),
				),
				array( 'setting_key' => $key ),
				array( '%s', '%s' ),
				array( '%s' )
			);
		} else {
			$result = $wpdb->insert(
				$table,
				array(
					'setting_key'   => $key,
					'setting_value' => $serialized_value,
					'created_at'    => current_time( 'mysql' ),
					'updated_at'    => current_time( 'mysql' ),
				),
				array( '%s', '%s', '%s', '%s' )
			);
		}

		// Mettre à jour le cache
		if ( false !== $result ) {
			self::$settings_cache[ $key ] = $value;
			return true;
		}

		return false;
	}

	/**
	 * Supprimer un paramètre
	 *
	 * @param string $key Clé du paramètre.
	 * @return bool
	 */
	public static function delete_setting( $key ) {
		global $wpdb;
		$table = self::get_table_name( 'settings' );

		$result = $wpdb->delete(
			$table,
			array( 'setting_key' => $key ),
			array( '%s' )
		);

		// Supprimer du cache
		unset( self::$settings_cache[ $key ] );

		return false !== $result;
	}

	/**
	 * Obtenir tous les paramètres
	 *
	 * @return array
	 */
	public static function get_all_settings() {
		global $wpdb;
		$table = self::get_table_name( 'settings' );

		// Vérifier si la table existe
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			return array();
		}

		$results = $wpdb->get_results(
			"SELECT setting_key, setting_value FROM {$table}",
			ARRAY_A
		);

		$settings = array();
		if ( $results ) {
			foreach ( $results as $row ) {
				$settings[ $row['setting_key'] ] = maybe_unserialize( $row['setting_value'] );
			}
		}

		return $settings;
	}

	/**
	 * Définir les paramètres par défaut
	 *
	 * @return void
	 */
	public static function set_default_settings() {
		$defaults = array(
			// Version
			'db_version'                  => BZMI_DB_VERSION,

			// Configuration IA
			'ai_enabled'                  => false,
			'ai_provider'                 => 'openai',
			'ai_api_key'                  => '',
			'ai_model'                    => 'gpt-4',
			'ai_max_tokens'               => 2000,
			'ai_temperature'              => 0.7,
			'ai_features'                 => array(
				'auto_clarify'     => true,
				'suggest_actions'  => true,
				'detect_patterns'  => true,
				'generate_reports' => true,
			),

			// Configuration générale
			'default_status'              => 'pending',
			'enable_notifications'        => true,
			'notification_email'          => get_option( 'admin_email' ),
			'date_format'                 => 'Y-m-d H:i',
			'items_per_page'              => 20,

			// Workflow ICAVAL
			'icaval_auto_advance'         => false,
			'icaval_require_validation'   => true,
			'icaval_notify_stakeholders'  => true,

			// Intégration Blazing Feedback
			'blazing_feedback_sync'       => true,
			'blazing_feedback_auto_import' => true,
			'blazing_feedback_default_project' => 0,

			// Export
			'export_format'               => 'json',
			'export_include_attachments'  => true,
		);

		foreach ( $defaults as $key => $value ) {
			$existing = self::get_setting( $key, null );
			if ( null === $existing ) {
				self::update_setting( $key, $value );
			}
		}
	}

	/**
	 * Vider le cache des paramètres
	 *
	 * @return void
	 */
	public static function clear_cache() {
		self::$settings_cache = array();
	}

	/**
	 * Exécuter une requête SQL brute
	 *
	 * @param string $sql    Requête SQL.
	 * @param array  $params Paramètres pour prepare().
	 * @return mixed
	 */
	public static function query( $sql, $params = array() ) {
		global $wpdb;

		if ( ! empty( $params ) ) {
			$sql = $wpdb->prepare( $sql, $params );
		}

		return $wpdb->query( $sql );
	}

	/**
	 * Obtenir des résultats
	 *
	 * @param string $sql    Requête SQL.
	 * @param array  $params Paramètres pour prepare().
	 * @return array
	 */
	public static function get_results( $sql, $params = array() ) {
		global $wpdb;

		if ( ! empty( $params ) ) {
			$sql = $wpdb->prepare( $sql, $params );
		}

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * Obtenir une ligne
	 *
	 * @param string $sql    Requête SQL.
	 * @param array  $params Paramètres pour prepare().
	 * @return array|null
	 */
	public static function get_row( $sql, $params = array() ) {
		global $wpdb;

		if ( ! empty( $params ) ) {
			$sql = $wpdb->prepare( $sql, $params );
		}

		return $wpdb->get_row( $sql, ARRAY_A );
	}

	/**
	 * Obtenir une valeur
	 *
	 * @param string $sql    Requête SQL.
	 * @param array  $params Paramètres pour prepare().
	 * @return mixed
	 */
	public static function get_var( $sql, $params = array() ) {
		global $wpdb;

		if ( ! empty( $params ) ) {
			$sql = $wpdb->prepare( $sql, $params );
		}

		return $wpdb->get_var( $sql );
	}

	/**
	 * Insérer une ligne
	 *
	 * @param string $table Table sans préfixe.
	 * @param array  $data  Données à insérer.
	 * @return int|false ID inséré ou false.
	 */
	public static function insert( $table, $data ) {
		global $wpdb;

		$table_name = self::get_table_name( $table );
		$result     = $wpdb->insert( $table_name, $data );

		if ( false !== $result ) {
			return $wpdb->insert_id;
		}

		return false;
	}

	/**
	 * Mettre à jour des lignes
	 *
	 * @param string $table Table sans préfixe.
	 * @param array  $data  Données à mettre à jour.
	 * @param array  $where Conditions WHERE.
	 * @return int|false Nombre de lignes affectées ou false.
	 */
	public static function update( $table, $data, $where ) {
		global $wpdb;

		$table_name = self::get_table_name( $table );
		return $wpdb->update( $table_name, $data, $where );
	}

	/**
	 * Supprimer des lignes
	 *
	 * @param string $table Table sans préfixe.
	 * @param array  $where Conditions WHERE.
	 * @return int|false Nombre de lignes affectées ou false.
	 */
	public static function delete( $table, $where ) {
		global $wpdb;

		$table_name = self::get_table_name( $table );
		return $wpdb->delete( $table_name, $where );
	}
}
