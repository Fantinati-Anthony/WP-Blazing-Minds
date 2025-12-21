<?php
/**
 * Template du header du panneau et onglets
 *
 * @package Blazing_Feedback
 * @since 1.9.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wpvfh-panel-header">
	<?php
	$logo_mode = WPVFH_Database::get_setting( 'wpvfh_logo_mode', 'none' );
	$logo_url  = '';
	if ( $logo_mode === 'light' ) {
		$logo_url = WPVFH_PLUGIN_URL . 'assets/logo/light-mode-feedback.png';
	} elseif ( $logo_mode === 'dark' ) {
		$logo_url = WPVFH_PLUGIN_URL . 'assets/logo/dark-mode-feedback.png';
	} elseif ( $logo_mode === 'custom' ) {
		$logo_url = WPVFH_Database::get_setting( 'wpvfh_logo_custom_url', '' );
	}
	// Fallback to default light logo if no logo URL
	if ( empty( $logo_url ) ) {
		$logo_url = WPVFH_PLUGIN_URL . 'assets/logo/light-mode-feedback.png';
	}
	?>
	<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php esc_attr_e( 'Blazing Feedback', 'blazing-feedback' ); ?>" class="wpvfh-panel-logo">
	<div class="wpvfh-header-actions">
		<button type="button" id="wpvfh-visibility-btn" class="wpvfh-header-btn wpvfh-visibility-btn" title="<?php esc_attr_e( 'Afficher/masquer les points', 'blazing-feedback' ); ?>" data-visible="true">
			<span class="wpvfh-icon-visible" aria-hidden="true">👁️</span>
			<span class="wpvfh-icon-hidden" aria-hidden="true" hidden>🙈</span>
		</button>
		<div class="wpvfh-responsive-wrapper">
			<button type="button" id="wpvfh-responsive-btn" class="wpvfh-header-btn wpvfh-responsive-btn" title="<?php esc_attr_e( 'Mode responsive', 'blazing-feedback' ); ?>">
				<span aria-hidden="true">📱</span>
			</button>
			<div id="wpvfh-responsive-menu" class="wpvfh-responsive-menu" hidden>
				<button type="button" class="wpvfh-responsive-item" data-device="desktop" data-width="100%" data-height="100%">
					<span>🖥️</span> Desktop
				</button>
				<button type="button" class="wpvfh-responsive-item" data-device="laptop" data-width="1366" data-height="768">
					<span>💻</span> Laptop (1366×768)
				</button>
				<button type="button" class="wpvfh-responsive-item" data-device="tablet" data-width="768" data-height="1024">
					<span>📱</span> Tablet (768×1024)
				</button>
				<button type="button" class="wpvfh-responsive-item" data-device="mobile" data-width="375" data-height="667">
					<span>📱</span> iPhone (375×667)
				</button>
				<button type="button" class="wpvfh-responsive-item" data-device="mobile-large" data-width="414" data-height="896">
					<span>📱</span> iPhone Plus (414×896)
				</button>
				<hr class="wpvfh-responsive-divider">
				<button type="button" class="wpvfh-responsive-item wpvfh-responsive-reset" data-device="reset">
					<span>↩️</span> <?php esc_html_e( 'Réinitialiser', 'blazing-feedback' ); ?>
				</button>
			</div>
		</div>
		<button type="button" class="wpvfh-search-btn" id="wpvfh-search-btn" aria-label="<?php esc_attr_e( 'Rechercher', 'blazing-feedback' ); ?>" title="<?php esc_attr_e( 'Rechercher un feedback', 'blazing-feedback' ); ?>">
			<span aria-hidden="true">🔍</span>
		</button>
		<button type="button" class="wpvfh-close-btn" aria-label="<?php esc_attr_e( 'Fermer', 'blazing-feedback' ); ?>">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
</div>

<!-- Onglets -->
<div class="wpvfh-tabs">
	<button type="button" class="wpvfh-tab wpvfh-tab-add" data-tab="new" id="wpvfh-add-btn" title="<?php esc_attr_e( 'Nouveau feedback', 'blazing-feedback' ); ?>">
		<span class="wpvfh-tab-icon wpvfh-icon-plus" aria-hidden="true">+</span>
	</button>
	<button type="button" class="wpvfh-tab active" data-tab="list">
		<span class="wpvfh-tab-icon" aria-hidden="true">📋</span>
		<?php esc_html_e( 'Liste', 'blazing-feedback' ); ?>
		<span class="wpvfh-tab-count" id="wpvfh-pins-count"></span>
	</button>
	<button type="button" class="wpvfh-tab" data-tab="pages">
		<span class="wpvfh-tab-icon" aria-hidden="true">📄</span>
		<?php esc_html_e( 'Pages', 'blazing-feedback' ); ?>
	</button>
	<button type="button" class="wpvfh-tab" data-tab="metadata">
		<span class="wpvfh-tab-icon" aria-hidden="true">🏷️</span>
		<?php esc_html_e( 'Métadatas', 'blazing-feedback' ); ?>
	</button>
	<button type="button" class="wpvfh-tab" data-tab="details" id="wpvfh-tab-details-btn" hidden>
		<span class="wpvfh-tab-icon" aria-hidden="true">👁️</span>
		<?php esc_html_e( 'Détails', 'blazing-feedback' ); ?>
	</button>
</div>
