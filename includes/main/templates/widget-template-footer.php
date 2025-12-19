<?php
/**
 * Template du footer du panneau
 *
 * @package Blazing_Feedback
 * @since 1.9.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!-- Footer de la sidebar avec boutons d'action -->
<div class="wpvfh-panel-footer">
	<button
		type="button"
		id="wpvfh-add-btn"
		class="wpvfh-footer-btn wpvfh-footer-btn-add"
		title="<?php esc_attr_e( 'Ajouter un feedback', 'blazing-feedback' ); ?>"
	>
		<span class="wpvfh-footer-btn-icon" aria-hidden="true">➕</span>
		<span class="wpvfh-footer-btn-text"><?php esc_html_e( 'Nouveau', 'blazing-feedback' ); ?></span>
	</button>
	<button
		type="button"
		id="wpvfh-visibility-btn"
		class="wpvfh-footer-btn wpvfh-footer-btn-visibility"
		title="<?php esc_attr_e( 'Afficher/masquer les points', 'blazing-feedback' ); ?>"
		data-visible="true"
	>
		<span class="wpvfh-footer-btn-icon wpvfh-icon-visible" aria-hidden="true">👁️</span>
		<span class="wpvfh-footer-btn-icon wpvfh-icon-hidden" aria-hidden="true" hidden>🙈</span>
		<span class="wpvfh-footer-btn-text"><?php esc_html_e( 'Pins', 'blazing-feedback' ); ?></span>
	</button>
</div>
