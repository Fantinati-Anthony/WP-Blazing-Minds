<?php
/**
 * Configuration IA centralisée
 *
 * Fournit la configuration IA à tous les plugins Blazing
 *
 * @package Blazing_Minds
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe BZMI_AI_Config
 *
 * @since 1.0.0
 */
class BZMI_AI_Config {

	/**
	 * Instance unique
	 *
	 * @var BZMI_AI_Config
	 */
	private static $instance = null;

	/**
	 * Configuration mise en cache
	 *
	 * @var array
	 */
	private $config = null;

	/**
	 * Obtenir l'instance unique
	 *
	 * @return BZMI_AI_Config
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructeur privé
	 */
	private function __construct() {
		$this->load_config();
	}

	/**
	 * Charger la configuration
	 *
	 * @return void
	 */
	private function load_config() {
		$this->config = array(
			'enabled'     => BZMI_Database::get_setting( 'ai_enabled', false ),
			'provider'    => BZMI_Database::get_setting( 'ai_provider', 'openai' ),
			'api_key'     => BZMI_Database::get_setting( 'ai_api_key', '' ),
			'model'       => BZMI_Database::get_setting( 'ai_model', 'gpt-4' ),
			'max_tokens'  => BZMI_Database::get_setting( 'ai_max_tokens', 2000 ),
			'temperature' => BZMI_Database::get_setting( 'ai_temperature', 0.7 ),
			'features'    => BZMI_Database::get_setting( 'ai_features', array() ),
		);
	}

	/**
	 * Vérifier si l'IA est activée
	 *
	 * @return bool
	 */
	public function is_enabled() {
		return $this->config['enabled'] && ! empty( $this->config['api_key'] );
	}

	/**
	 * Vérifier si une fonctionnalité est activée
	 *
	 * @param string $feature Nom de la fonctionnalité.
	 * @return bool
	 */
	public function is_feature_enabled( $feature ) {
		if ( ! $this->is_enabled() ) {
			return false;
		}

		$features = $this->config['features'];
		return ! empty( $features[ $feature ] );
	}

	/**
	 * Obtenir la configuration complète
	 *
	 * @return array
	 */
	public function get_config() {
		return $this->config;
	}

	/**
	 * Obtenir le fournisseur
	 *
	 * @return string
	 */
	public function get_provider() {
		return $this->config['provider'];
	}

	/**
	 * Obtenir la clé API
	 *
	 * @return string
	 */
	public function get_api_key() {
		return $this->config['api_key'];
	}

	/**
	 * Obtenir le modèle
	 *
	 * @return string
	 */
	public function get_model() {
		return $this->config['model'];
	}

	/**
	 * Obtenir les paramètres de requête
	 *
	 * @return array
	 */
	public function get_request_params() {
		return array(
			'model'       => $this->config['model'],
			'max_tokens'  => $this->config['max_tokens'],
			'temperature' => $this->config['temperature'],
		);
	}

	/**
	 * Obtenir l'URL de l'API selon le fournisseur
	 *
	 * @return string
	 */
	public function get_api_url() {
		$urls = array(
			'openai'    => 'https://api.openai.com/v1/chat/completions',
			'anthropic' => 'https://api.anthropic.com/v1/messages',
			'google'    => 'https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent',
			'azure'     => '', // Configurable
			'local'     => 'http://localhost:11434/api/generate',
		);

		$provider = $this->config['provider'];
		return isset( $urls[ $provider ] ) ? $urls[ $provider ] : '';
	}

	/**
	 * Construire les headers de la requête
	 *
	 * @return array
	 */
	public function get_headers() {
		$provider = $this->config['provider'];
		$api_key  = $this->config['api_key'];

		$headers = array(
			'Content-Type' => 'application/json',
		);

		switch ( $provider ) {
			case 'openai':
			case 'azure':
				$headers['Authorization'] = 'Bearer ' . $api_key;
				break;

			case 'anthropic':
				$headers['x-api-key'] = $api_key;
				$headers['anthropic-version'] = '2024-01-01';
				break;

			case 'google':
				// La clé est passée en paramètre URL
				break;
		}

		return $headers;
	}

	/**
	 * Envoyer une requête à l'IA
	 *
	 * @param string $prompt   Le prompt à envoyer.
	 * @param string $system   Message système optionnel.
	 * @param array  $options  Options supplémentaires.
	 * @return array|WP_Error
	 */
	public function send_request( $prompt, $system = '', $options = array() ) {
		if ( ! $this->is_enabled() ) {
			return new WP_Error( 'ai_disabled', __( 'L\'IA n\'est pas activée.', 'blazing-minds' ) );
		}

		$provider = $this->config['provider'];
		$url      = $this->get_api_url();
		$headers  = $this->get_headers();
		$params   = wp_parse_args( $options, $this->get_request_params() );

		// Construire le body selon le fournisseur
		switch ( $provider ) {
			case 'openai':
			case 'azure':
				$body = $this->build_openai_body( $prompt, $system, $params );
				break;

			case 'anthropic':
				$body = $this->build_anthropic_body( $prompt, $system, $params );
				break;

			case 'google':
				$url  = str_replace( '{model}', $params['model'], $url );
				$url  = add_query_arg( 'key', $this->config['api_key'], $url );
				$body = $this->build_google_body( $prompt, $system, $params );
				break;

			case 'local':
				$body = $this->build_ollama_body( $prompt, $system, $params );
				break;

			default:
				return new WP_Error( 'unknown_provider', __( 'Fournisseur IA inconnu.', 'blazing-minds' ) );
		}

		$response = wp_remote_post( $url, array(
			'headers' => $headers,
			'body'    => wp_json_encode( $body ),
			'timeout' => 60,
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		$body        = wp_remote_retrieve_body( $response );
		$data        = json_decode( $body, true );

		if ( $status_code !== 200 ) {
			$error_message = isset( $data['error']['message'] ) ? $data['error']['message'] : __( 'Erreur de l\'API IA.', 'blazing-minds' );
			return new WP_Error( 'api_error', $error_message, array( 'status' => $status_code ) );
		}

		// Extraire le contenu selon le fournisseur
		return $this->extract_response( $provider, $data );
	}

	/**
	 * Construire le body pour OpenAI
	 *
	 * @param string $prompt Prompt.
	 * @param string $system Message système.
	 * @param array  $params Paramètres.
	 * @return array
	 */
	private function build_openai_body( $prompt, $system, $params ) {
		$messages = array();

		if ( ! empty( $system ) ) {
			$messages[] = array(
				'role'    => 'system',
				'content' => $system,
			);
		}

		$messages[] = array(
			'role'    => 'user',
			'content' => $prompt,
		);

		return array(
			'model'       => $params['model'],
			'messages'    => $messages,
			'max_tokens'  => $params['max_tokens'],
			'temperature' => $params['temperature'],
		);
	}

	/**
	 * Construire le body pour Anthropic
	 *
	 * @param string $prompt Prompt.
	 * @param string $system Message système.
	 * @param array  $params Paramètres.
	 * @return array
	 */
	private function build_anthropic_body( $prompt, $system, $params ) {
		$body = array(
			'model'      => $params['model'],
			'max_tokens' => $params['max_tokens'],
			'messages'   => array(
				array(
					'role'    => 'user',
					'content' => $prompt,
				),
			),
		);

		if ( ! empty( $system ) ) {
			$body['system'] = $system;
		}

		return $body;
	}

	/**
	 * Construire le body pour Google
	 *
	 * @param string $prompt Prompt.
	 * @param string $system Message système.
	 * @param array  $params Paramètres.
	 * @return array
	 */
	private function build_google_body( $prompt, $system, $params ) {
		$content = ! empty( $system ) ? $system . "\n\n" . $prompt : $prompt;

		return array(
			'contents' => array(
				array(
					'parts' => array(
						array( 'text' => $content ),
					),
				),
			),
			'generationConfig' => array(
				'maxOutputTokens' => $params['max_tokens'],
				'temperature'     => $params['temperature'],
			),
		);
	}

	/**
	 * Construire le body pour Ollama
	 *
	 * @param string $prompt Prompt.
	 * @param string $system Message système.
	 * @param array  $params Paramètres.
	 * @return array
	 */
	private function build_ollama_body( $prompt, $system, $params ) {
		$full_prompt = ! empty( $system ) ? $system . "\n\n" . $prompt : $prompt;

		return array(
			'model'  => $params['model'],
			'prompt' => $full_prompt,
			'stream' => false,
			'options' => array(
				'temperature' => $params['temperature'],
			),
		);
	}

	/**
	 * Extraire la réponse selon le fournisseur
	 *
	 * @param string $provider Fournisseur.
	 * @param array  $data     Données de réponse.
	 * @return array
	 */
	private function extract_response( $provider, $data ) {
		$content = '';
		$usage   = array();

		switch ( $provider ) {
			case 'openai':
			case 'azure':
				$content = isset( $data['choices'][0]['message']['content'] )
					? $data['choices'][0]['message']['content']
					: '';
				$usage = isset( $data['usage'] ) ? $data['usage'] : array();
				break;

			case 'anthropic':
				$content = isset( $data['content'][0]['text'] )
					? $data['content'][0]['text']
					: '';
				$usage = isset( $data['usage'] ) ? $data['usage'] : array();
				break;

			case 'google':
				$content = isset( $data['candidates'][0]['content']['parts'][0]['text'] )
					? $data['candidates'][0]['content']['parts'][0]['text']
					: '';
				break;

			case 'local':
				$content = isset( $data['response'] ) ? $data['response'] : '';
				break;
		}

		return array(
			'content' => $content,
			'usage'   => $usage,
			'raw'     => $data,
		);
	}

	/**
	 * Générer des clarifications pour une information
	 *
	 * @param BZMI_Information $information L'information.
	 * @return array|WP_Error
	 */
	public function generate_clarifications( $information ) {
		if ( ! $this->is_feature_enabled( 'auto_clarify' ) ) {
			return new WP_Error( 'feature_disabled', __( 'La fonctionnalité de clarification automatique est désactivée.', 'blazing-minds' ) );
		}

		$system = "Tu es un assistant de gestion de projet. Analyse l'information suivante et génère 3 à 5 questions de clarification pertinentes pour mieux comprendre le besoin. Réponds en JSON avec un tableau 'questions'.";

		$prompt = sprintf(
			"Information:\nTitre: %s\nContenu: %s\nType: %s\nPriorité: %s",
			$information->title,
			$information->content,
			$information->type,
			$information->priority
		);

		$response = $this->send_request( $prompt, $system );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		// Parser le JSON
		$content = $response['content'];
		$json = json_decode( $content, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			// Essayer d'extraire le JSON du texte
			preg_match( '/\{.*\}/s', $content, $matches );
			if ( ! empty( $matches[0] ) ) {
				$json = json_decode( $matches[0], true );
			}
		}

		return isset( $json['questions'] ) ? $json['questions'] : array();
	}

	/**
	 * Suggérer des actions basées sur les clarifications
	 *
	 * @param BZMI_Information $information L'information avec ses clarifications.
	 * @return array|WP_Error
	 */
	public function suggest_actions( $information ) {
		if ( ! $this->is_feature_enabled( 'suggest_actions' ) ) {
			return new WP_Error( 'feature_disabled', __( 'La fonctionnalité de suggestion d\'actions est désactivée.', 'blazing-minds' ) );
		}

		$clarifications = $information->clarifications();
		$clarif_text = '';

		foreach ( $clarifications as $clarif ) {
			if ( $clarif->resolved ) {
				$clarif_text .= sprintf( "Q: %s\nR: %s\n\n", $clarif->question, $clarif->answer );
			}
		}

		$system = "Tu es un assistant de gestion de projet. Basé sur l'information et ses clarifications, suggère 2 à 4 actions concrètes à entreprendre. Réponds en JSON avec un tableau 'actions', chaque action ayant 'title', 'description', 'priority' (low/normal/high), et 'effort' (xs/s/m/l/xl).";

		$prompt = sprintf(
			"Information:\nTitre: %s\nContenu: %s\n\nClarifications:\n%s",
			$information->title,
			$information->content,
			$clarif_text
		);

		$response = $this->send_request( $prompt, $system );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$content = $response['content'];
		$json = json_decode( $content, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			preg_match( '/\{.*\}/s', $content, $matches );
			if ( ! empty( $matches[0] ) ) {
				$json = json_decode( $matches[0], true );
			}
		}

		return isset( $json['actions'] ) ? $json['actions'] : array();
	}

	/**
	 * Générer un apprentissage depuis une information complète
	 *
	 * @param BZMI_Information $information L'information terminée.
	 * @return array|WP_Error
	 */
	public function generate_apprenticeship( $information ) {
		$system = "Tu es un assistant d'apprentissage organisationnel. Analyse le cycle complet d'une information (de l'information initiale aux valeurs générées) et identifie les leçons à retenir. Réponds en JSON avec: 'title', 'description', 'lesson_type' (insight/best_practice/pattern/anti_pattern), 'recommendations' (tableau), 'tags' (tableau).";

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

		$prompt = "Contexte complet du cycle ICAVAL:\n" . wp_json_encode( $context, JSON_PRETTY_PRINT );

		$response = $this->send_request( $prompt, $system );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$content = $response['content'];
		$json = json_decode( $content, true );

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			preg_match( '/\{.*\}/s', $content, $matches );
			if ( ! empty( $matches[0] ) ) {
				$json = json_decode( $matches[0], true );
			}
		}

		return $json;
	}

	/**
	 * Rafraîchir la configuration (après modification des réglages)
	 *
	 * @return void
	 */
	public function refresh() {
		BZMI_Database::clear_cache();
		$this->load_config();
	}
}

/**
 * Fonction helper pour accéder à la configuration IA
 *
 * @return BZMI_AI_Config
 */
function bzmi_ai() {
	return BZMI_AI_Config::get_instance();
}
