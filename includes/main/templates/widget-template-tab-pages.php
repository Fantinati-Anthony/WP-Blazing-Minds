<?php
/**
 * Template de l'onglet Pages
 *
 * @package Blazing_Feedback
 * @since 1.9.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!-- Onglet: Pages -->
<div id="wpvfh-tab-pages" class="wpvfh-tab-content">
	<div class="wpvfh-pages-header">
		<h4><?php esc_html_e( 'Toutes les pages avec feedbacks', 'blazing-feedback' ); ?></h4>
	</div>
	<div id="wpvfh-pages-list" class="wpvfh-pages-list">
		<!-- Les pages seront chargées dynamiquement -->
	</div>
	<div id="wpvfh-pages-empty" class="wpvfh-empty-state" hidden>
		<div class="wpvfh-empty-icon" aria-hidden="true">📄</div>
		<p class="wpvfh-empty-text"><?php esc_html_e( 'Aucune page avec des feedbacks', 'blazing-feedback' ); ?></p>
	</div>
	<div id="wpvfh-pages-loading" class="wpvfh-loading-state">
		<span class="wpvfh-spinner"></span>
		<span><?php esc_html_e( 'Chargement des pages...', 'blazing-feedback' ); ?></span>
	</div>
</div><!-- /wpvfh-tab-pages -->
