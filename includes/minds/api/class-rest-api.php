<?php
/**
 * API REST
 *
 * @package Blazing_Minds
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe BZMI_REST_API
 *
 * Endpoints REST pour le plugin
 *
 * @since 1.0.0
 */
class BZMI_REST_API {

	/**
	 * Namespace de l'API
	 *
	 * @var string
	 */
	const NAMESPACE = 'blazing-minds/v1';

	/**
	 * Enregistrer les routes
	 *
	 * @return void
	 */
	public static function register_routes() {
		// Clients
		register_rest_route( self::NAMESPACE, '/clients', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_clients' ),
				'permission_callback' => array( __CLASS__, 'check_read_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'create_client' ),
				'permission_callback' => array( __CLASS__, 'check_write_permission' ),
			),
		) );

		register_rest_route( self::NAMESPACE, '/clients/(?P<id>\d+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_client' ),
				'permission_callback' => array( __CLASS__, 'check_read_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( __CLASS__, 'update_client' ),
				'permission_callback' => array( __CLASS__, 'check_write_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( __CLASS__, 'delete_client' ),
				'permission_callback' => array( __CLASS__, 'check_delete_permission' ),
			),
		) );

		// Portfolios
		register_rest_route( self::NAMESPACE, '/portfolios', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_portfolios' ),
				'permission_callback' => array( __CLASS__, 'check_read_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'create_portfolio' ),
				'permission_callback' => array( __CLASS__, 'check_write_permission' ),
			),
		) );

		register_rest_route( self::NAMESPACE, '/portfolios/(?P<id>\d+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_portfolio' ),
				'permission_callback' => array( __CLASS__, 'check_read_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( __CLASS__, 'update_portfolio' ),
				'permission_callback' => array( __CLASS__, 'check_write_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( __CLASS__, 'delete_portfolio' ),
				'permission_callback' => array( __CLASS__, 'check_delete_permission' ),
			),
		) );

		// Projects
		register_rest_route( self::NAMESPACE, '/projects', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_projects' ),
				'permission_callback' => array( __CLASS__, 'check_read_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'create_project' ),
				'permission_callback' => array( __CLASS__, 'check_write_permission' ),
			),
		) );

		register_rest_route( self::NAMESPACE, '/projects/(?P<id>\d+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_project' ),
				'permission_callback' => array( __CLASS__, 'check_read_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( __CLASS__, 'update_project' ),
				'permission_callback' => array( __CLASS__, 'check_write_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( __CLASS__, 'delete_project' ),
				'permission_callback' => array( __CLASS__, 'check_delete_permission' ),
			),
		) );

		// Informations
		register_rest_route( self::NAMESPACE, '/informations', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_informations' ),
				'permission_callback' => array( __CLASS__, 'check_read_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'create_information' ),
				'permission_callback' => array( __CLASS__, 'check_write_permission' ),
			),
		) );

		register_rest_route( self::NAMESPACE, '/informations/(?P<id>\d+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( __CLASS__, 'get_information' ),
				'permission_callback' => array( __CLASS__, 'check_read_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( __CLASS__, 'update_information' ),
				'permission_callback' => array( __CLASS__, 'check_write_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( __CLASS__, 'delete_information' ),
				'permission_callback' => array( __CLASS__, 'check_delete_permission' ),
			),
		) );

		// Avancer l'étape ICAVAL
		register_rest_route( self::NAMESPACE, '/informations/(?P<id>\d+)/advance', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( __CLASS__, 'advance_stage' ),
			'permission_callback' => array( __CLASS__, 'check_write_permission' ),
		) );

		// IA endpoints
		register_rest_route( self::NAMESPACE, '/ai/clarifications/(?P<id>\d+)', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( __CLASS__, 'ai_generate_clarifications' ),
			'permission_callback' => array( __CLASS__, 'check_ai_permission' ),
		) );

		register_rest_route( self::NAMESPACE, '/ai/actions/(?P<id>\d+)', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( __CLASS__, 'ai_suggest_actions' ),
			'permission_callback' => array( __CLASS__, 'check_ai_permission' ),
		) );

		// Configuration IA (pour les autres plugins Blazing)
		register_rest_route( self::NAMESPACE, '/ai/config', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( __CLASS__, 'get_ai_config' ),
			'permission_callback' => array( __CLASS__, 'check_ai_permission' ),
		) );

		// Stats et Dashboard
		register_rest_route( self::NAMESPACE, '/stats', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( __CLASS__, 'get_stats' ),
			'permission_callback' => array( __CLASS__, 'check_read_permission' ),
		) );

		// Import depuis Blazing Feedback
		register_rest_route( self::NAMESPACE, '/import/feedback', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( __CLASS__, 'import_feedback' ),
			'permission_callback' => array( __CLASS__, 'check_write_permission' ),
		) );
	}

	/**
	 * Vérifier les permissions de lecture
	 *
	 * @param WP_REST_Request $request Requête.
	 * @return bool
	 */
	public static function check_read_permission( $request ) {
		return current_user_can( 'bzmi_view_reports' );
	}

	/**
	 * Vérifier les permissions d'écriture
	 *
	 * @param WP_REST_Request $request Requête.
	 * @return bool
	 */
	public static function check_write_permission( $request ) {
		return current_user_can( 'bzmi_manage_projects' );
	}

	/**
	 * Vérifier les permissions de suppression
	 *
	 * @param WP_REST_Request $request Requête.
	 * @return bool
	 */
	public static function check_delete_permission( $request ) {
		return current_user_can( 'bzmi_manage_projects' );
	}

	/**
	 * Vérifier les permissions IA
	 *
	 * @param WP_REST_Request $request Requête.
	 * @return bool
	 */
	public static function check_ai_permission( $request ) {
		return current_user_can( 'bzmi_use_ai' );
	}

	// =====================================================
	// CLIENTS
	// =====================================================

	/**
	 * Obtenir les clients
	 *
	 * @param WP_REST_Request $request Requête.
	 * @return WP_REST_Response
	 */
	public static function get_clients( $request ) {
		$page     = $request->get_param( 'page' ) ?: 1;
		$per_page = $request->get_param( 'per_page' ) ?: 20;
		$search   = $request->get_param( 'search' );
		$status   = $request->get_param( 'status' );

		$args = array();
		if ( $status ) {
			$args['where'] = array( 'status' => $status );
		}

		if ( $search ) {
			$clients = BZMI_Client::search( $search );
			$total = count( $clients );
			$clients = array_slice( $clients, ( $page - 1 ) * $per_page, $per_page );
		} else {
			$result = BZMI_Client::paginate( $page, $per_page, $args );
			$clients = $result['items'];
			$total = $result['total'];
		}

		$data = array_map( function( $client ) {
			return $client->to_array();
		}, $clients );

		return new WP_REST_Response( array(
			'data'  => $data,
			'total' => $total,
			'page'  => $page,
			'pages' => ceil( $total / $per_page ),
		), 200 );
	}

	/**
	 * Obtenir un client
	 *
	 * @param WP_REST_Request $request Requête.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function get_client( $request ) {
		$id = $request->get_param( 'id' );
		$client = BZMI_Client::find( $id );

		if ( ! $client ) {
			return new WP_Error( 'not_found', __( 'Client introuvable.', 'blazing-minds' ), array( 'status' => 404 ) );
		}

		return new WP_REST_Response( array(
			'data'  => $client->to_array(),
			'stats' => $client->get_stats(),
		), 200 );
	}

	/**
	 * Créer un client
	 *
	 * @param WP_REST_Request $request Requête.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function create_client( $request ) {
		$client = new BZMI_Client();
		$client->fill( $request->get_json_params() );

		$result = $client->save_validated();

		if ( is_array( $result ) ) {
			return new WP_Error( 'validation_error', implode( ' ', $result ), array( 'status' => 400 ) );
		}

		return new WP_REST_Response( $client->to_array(), 201 );
	}

	/**
	 * Mettre à jour un client
	 *
	 * @param WP_REST_Request $request Requête.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function update_client( $request ) {
		$id = $request->get_param( 'id' );
		$client = BZMI_Client::find( $id );

		if ( ! $client ) {
			return new WP_Error( 'not_found', __( 'Client introuvable.', 'blazing-minds' ), array( 'status' => 404 ) );
		}

		$client->fill( $request->get_json_params() );
		$result = $client->save_validated();

		if ( is_array( $result ) ) {
			return new WP_Error( 'validation_error', implode( ' ', $result ), array( 'status' => 400 ) );
		}

		return new WP_REST_Response( $client->to_array(), 200 );
	}

	/**
	 * Supprimer un client
	 *
	 * @param WP_REST_Request $request Requête.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function delete_client( $request ) {
		$id = $request->get_param( 'id' );
		$client = BZMI_Client::find( $id );

		if ( ! $client ) {
			return new WP_Error( 'not_found', __( 'Client introuvable.', 'blazing-minds' ), array( 'status' => 404 ) );
		}

		if ( $client->portfolios_count() > 0 ) {
			return new WP_Error( 'has_children', __( 'Ce client contient des portefeuilles.', 'blazing-minds' ), array( 'status' => 400 ) );
		}

		$client->delete();

		return new WP_REST_Response( null, 204 );
	}

	// =====================================================
	// PROJECTS (résumé - même pattern)
	// =====================================================

	public static function get_projects( $request ) {
		$page     = $request->get_param( 'page' ) ?: 1;
		$per_page = $request->get_param( 'per_page' ) ?: 20;
		$portfolio_id = $request->get_param( 'portfolio_id' );

		$args = array();
		if ( $portfolio_id ) {
			$args['where'] = array( 'portfolio_id' => $portfolio_id );
		}

		$result = BZMI_Project::paginate( $page, $per_page, $args );

		$data = array_map( function( $project ) {
			return $project->to_array();
		}, $result['items'] );

		return new WP_REST_Response( array(
			'data'  => $data,
			'total' => $result['total'],
		), 200 );
	}

	public static function get_project( $request ) {
		$id = $request->get_param( 'id' );
		$project = BZMI_Project::find( $id );

		if ( ! $project ) {
			return new WP_Error( 'not_found', __( 'Projet introuvable.', 'blazing-minds' ), array( 'status' => 404 ) );
		}

		return new WP_REST_Response( array(
			'data'        => $project->to_array(),
			'icaval_stats' => $project->get_icaval_stats(),
		), 200 );
	}

	public static function create_project( $request ) {
		$project = new BZMI_Project();
		$project->fill( $request->get_json_params() );

		$result = $project->save_validated();

		if ( is_array( $result ) ) {
			return new WP_Error( 'validation_error', implode( ' ', $result ), array( 'status' => 400 ) );
		}

		return new WP_REST_Response( $project->to_array(), 201 );
	}

	public static function update_project( $request ) {
		$id = $request->get_param( 'id' );
		$project = BZMI_Project::find( $id );

		if ( ! $project ) {
			return new WP_Error( 'not_found', __( 'Projet introuvable.', 'blazing-minds' ), array( 'status' => 404 ) );
		}

		$project->fill( $request->get_json_params() );
		$project->save_validated();

		return new WP_REST_Response( $project->to_array(), 200 );
	}

	public static function delete_project( $request ) {
		$id = $request->get_param( 'id' );
		$project = BZMI_Project::find( $id );

		if ( ! $project ) {
			return new WP_Error( 'not_found', __( 'Projet introuvable.', 'blazing-minds' ), array( 'status' => 404 ) );
		}

		$project->delete();
		return new WP_REST_Response( null, 204 );
	}

	// Portfolios (même pattern)
	public static function get_portfolios( $request ) {
		$result = BZMI_Portfolio::paginate( 1, 100 );
		$data = array_map( fn( $p ) => $p->to_array(), $result['items'] );
		return new WP_REST_Response( array( 'data' => $data, 'total' => $result['total'] ), 200 );
	}

	public static function get_portfolio( $request ) {
		$portfolio = BZMI_Portfolio::find( $request->get_param( 'id' ) );
		if ( ! $portfolio ) {
			return new WP_Error( 'not_found', __( 'Portefeuille introuvable.', 'blazing-minds' ), array( 'status' => 404 ) );
		}
		return new WP_REST_Response( $portfolio->to_array(), 200 );
	}

	public static function create_portfolio( $request ) {
		$portfolio = new BZMI_Portfolio();
		$portfolio->fill( $request->get_json_params() );
		$result = $portfolio->save_validated();
		if ( is_array( $result ) ) {
			return new WP_Error( 'validation_error', implode( ' ', $result ), array( 'status' => 400 ) );
		}
		return new WP_REST_Response( $portfolio->to_array(), 201 );
	}

	public static function update_portfolio( $request ) {
		$portfolio = BZMI_Portfolio::find( $request->get_param( 'id' ) );
		if ( ! $portfolio ) {
			return new WP_Error( 'not_found', __( 'Portefeuille introuvable.', 'blazing-minds' ), array( 'status' => 404 ) );
		}
		$portfolio->fill( $request->get_json_params() );
		$portfolio->save_validated();
		return new WP_REST_Response( $portfolio->to_array(), 200 );
	}

	public static function delete_portfolio( $request ) {
		$portfolio = BZMI_Portfolio::find( $request->get_param( 'id' ) );
		if ( ! $portfolio ) {
			return new WP_Error( 'not_found', __( 'Portefeuille introuvable.', 'blazing-minds' ), array( 'status' => 404 ) );
		}
		$portfolio->delete();
		return new WP_REST_Response( null, 204 );
	}

	// =====================================================
	// INFORMATIONS
	// =====================================================

	public static function get_informations( $request ) {
		$page       = $request->get_param( 'page' ) ?: 1;
		$per_page   = $request->get_param( 'per_page' ) ?: 20;
		$project_id = $request->get_param( 'project_id' );
		$stage      = $request->get_param( 'stage' );

		$args = array( 'where' => array() );
		if ( $project_id ) {
			$args['where']['project_id'] = $project_id;
		}
		if ( $stage ) {
			$args['where']['icaval_stage'] = $stage;
		}

		$result = BZMI_Information::paginate( $page, $per_page, $args );

		$data = array_map( function( $info ) {
			return $info->to_array();
		}, $result['items'] );

		return new WP_REST_Response( array(
			'data'  => $data,
			'total' => $result['total'],
		), 200 );
	}

	public static function get_information( $request ) {
		$id = $request->get_param( 'id' );
		$info = BZMI_Information::find( $id );

		if ( ! $info ) {
			return new WP_Error( 'not_found', __( 'Information introuvable.', 'blazing-minds' ), array( 'status' => 404 ) );
		}

		return new WP_REST_Response( array(
			'data'           => $info->to_array(),
			'clarifications' => array_map( fn( $c ) => $c->to_array(), $info->clarifications() ),
			'actions'        => array_map( fn( $a ) => $a->to_array(), $info->actions() ),
		), 200 );
	}

	public static function create_information( $request ) {
		$info = new BZMI_Information();
		$info->fill( $request->get_json_params() );

		$result = $info->save_validated();

		if ( is_array( $result ) ) {
			return new WP_Error( 'validation_error', implode( ' ', $result ), array( 'status' => 400 ) );
		}

		return new WP_REST_Response( $info->to_array(), 201 );
	}

	public static function update_information( $request ) {
		$id = $request->get_param( 'id' );
		$info = BZMI_Information::find( $id );

		if ( ! $info ) {
			return new WP_Error( 'not_found', __( 'Information introuvable.', 'blazing-minds' ), array( 'status' => 404 ) );
		}

		$info->fill( $request->get_json_params() );
		$info->save_validated();

		return new WP_REST_Response( $info->to_array(), 200 );
	}

	public static function delete_information( $request ) {
		$id = $request->get_param( 'id' );
		$info = BZMI_Information::find( $id );

		if ( ! $info ) {
			return new WP_Error( 'not_found', __( 'Information introuvable.', 'blazing-minds' ), array( 'status' => 404 ) );
		}

		$info->delete();
		return new WP_REST_Response( null, 204 );
	}

	/**
	 * Avancer l'étape ICAVAL
	 *
	 * @param WP_REST_Request $request Requête.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function advance_stage( $request ) {
		$id = $request->get_param( 'id' );
		$info = BZMI_Information::find( $id );

		if ( ! $info ) {
			return new WP_Error( 'not_found', __( 'Information introuvable.', 'blazing-minds' ), array( 'status' => 404 ) );
		}

		$result = $info->advance_stage();

		if ( ! $result ) {
			return new WP_Error( 'cannot_advance', __( 'Impossible d\'avancer à l\'étape suivante.', 'blazing-minds' ), array( 'status' => 400 ) );
		}

		return new WP_REST_Response( $info->to_array(), 200 );
	}

	// =====================================================
	// IA ENDPOINTS
	// =====================================================

	/**
	 * Générer des clarifications via IA
	 *
	 * @param WP_REST_Request $request Requête.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function ai_generate_clarifications( $request ) {
		$id = $request->get_param( 'id' );
		$info = BZMI_Information::find( $id );

		if ( ! $info ) {
			return new WP_Error( 'not_found', __( 'Information introuvable.', 'blazing-minds' ), array( 'status' => 404 ) );
		}

		$ai = bzmi_ai();
		$result = $ai->generate_clarifications( $info );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return new WP_REST_Response( array( 'questions' => $result ), 200 );
	}

	/**
	 * Suggérer des actions via IA
	 *
	 * @param WP_REST_Request $request Requête.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function ai_suggest_actions( $request ) {
		$id = $request->get_param( 'id' );
		$info = BZMI_Information::find( $id );

		if ( ! $info ) {
			return new WP_Error( 'not_found', __( 'Information introuvable.', 'blazing-minds' ), array( 'status' => 404 ) );
		}

		$ai = bzmi_ai();
		$result = $ai->suggest_actions( $info );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return new WP_REST_Response( array( 'actions' => $result ), 200 );
	}

	/**
	 * Obtenir la configuration IA (pour les autres plugins)
	 *
	 * @param WP_REST_Request $request Requête.
	 * @return WP_REST_Response
	 */
	public static function get_ai_config( $request ) {
		$ai = bzmi_ai();

		return new WP_REST_Response( array(
			'enabled'  => $ai->is_enabled(),
			'provider' => $ai->get_provider(),
			'model'    => $ai->get_model(),
			'params'   => $ai->get_request_params(),
		), 200 );
	}

	/**
	 * Obtenir les statistiques
	 *
	 * @param WP_REST_Request $request Requête.
	 * @return WP_REST_Response
	 */
	public static function get_stats( $request ) {
		return new WP_REST_Response( BZMI_Admin::get_dashboard_stats(), 200 );
	}

	/**
	 * Importer un feedback de Blazing Feedback
	 *
	 * @param WP_REST_Request $request Requête.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function import_feedback( $request ) {
		$feedback_data = $request->get_json_params();
		$project_id = $request->get_param( 'project_id' );

		if ( ! $project_id ) {
			$project_id = BZMI_Database::get_setting( 'blazing_feedback_default_project', 0 );
		}

		if ( ! $project_id ) {
			return new WP_Error( 'no_project', __( 'Aucun projet cible spécifié.', 'blazing-minds' ), array( 'status' => 400 ) );
		}

		$info = BZMI_Information::create_from_feedback( $feedback_data, $project_id );

		if ( ! $info ) {
			return new WP_Error( 'import_failed', __( 'Échec de l\'import du feedback.', 'blazing-minds' ), array( 'status' => 500 ) );
		}

		return new WP_REST_Response( $info->to_array(), 201 );
	}
}
