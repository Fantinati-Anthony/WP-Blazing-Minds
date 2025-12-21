<?php
/**
 * Template de l'onglet Détails d'un feedback
 *
 * @package Blazing_Feedback
 * @since 1.9.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Récupérer les options nécessaires
$feedback_types    = WPVFH_Options_Manager::get_types();
$priorities        = WPVFH_Options_Manager::get_priorities();
$custom_groups     = WPVFH_Options_Manager::get_custom_groups();
$types_settings    = WPVFH_Options_Manager::get_group_settings( 'types' );
$priority_settings = WPVFH_Options_Manager::get_group_settings( 'priorities' );
?>
<!-- Onglet: Détails d'un feedback -->
<div id="wpvfh-tab-details" class="wpvfh-tab-content">
	<div class="wpvfh-details-header-bar">
		<button type="button" class="wpvfh-back-btn" id="wpvfh-back-to-list">
			<span aria-hidden="true">←</span>
			<?php esc_html_e( 'Retour', 'blazing-feedback' ); ?>
		</button>
		<span class="wpvfh-feedback-id" id="wpvfh-detail-id"></span>
		<button type="button" class="wpvfh-edit-btn" id="wpvfh-edit-feedback-btn" title="<?php esc_attr_e( 'Modifier ce feedback', 'blazing-feedback' ); ?>">
			<span aria-hidden="true">✏️</span>
		</button>
	</div>

	<div class="wpvfh-detail-content" id="wpvfh-detail-content">
		<!-- Statut -->
		<div class="wpvfh-detail-status" id="wpvfh-detail-status"></div>

		<!-- Auteur et date -->
		<div class="wpvfh-detail-meta">
			<div class="wpvfh-detail-author" id="wpvfh-detail-author"></div>
			<div class="wpvfh-detail-date" id="wpvfh-detail-date"></div>
		</div>

		<!-- Étiquettes (Type, Priorité) -->
		<div class="wpvfh-detail-labels" id="wpvfh-detail-labels">
			<div class="wpvfh-label-item wpvfh-label-type" id="wpvfh-detail-type-label" hidden>
				<span class="wpvfh-label-icon"></span>
				<span class="wpvfh-label-text"></span>
			</div>
			<div class="wpvfh-label-item wpvfh-label-priority" id="wpvfh-detail-priority-label" hidden>
				<span class="wpvfh-label-icon"></span>
				<span class="wpvfh-label-text"></span>
			</div>
		</div>

		<!-- Information de ciblage/position -->
		<div class="wpvfh-detail-pin-info" id="wpvfh-detail-pin-info" hidden>
			<h4>
				<span class="wpvfh-dropdown-icon">📍</span>
				<?php esc_html_e( 'Ciblage', 'blazing-feedback' ); ?>
			</h4>
			<div class="wpvfh-pin-info-content">
				<div class="wpvfh-pin-info-item" id="wpvfh-pin-info-selector">
					<span class="wpvfh-pin-info-label"><?php esc_html_e( 'Élément:', 'blazing-feedback' ); ?></span>
					<code class="wpvfh-pin-info-value" id="wpvfh-pin-selector-value"></code>
				</div>
				<div class="wpvfh-pin-info-item" id="wpvfh-pin-info-position">
					<span class="wpvfh-pin-info-label"><?php esc_html_e( 'Position:', 'blazing-feedback' ); ?></span>
					<span class="wpvfh-pin-info-value" id="wpvfh-pin-position-value"></span>
				</div>
				<div class="wpvfh-pin-info-item" id="wpvfh-pin-info-page">
					<span class="wpvfh-pin-info-label"><?php esc_html_e( 'Page:', 'blazing-feedback' ); ?></span>
					<span class="wpvfh-pin-info-value" id="wpvfh-pin-page-value"></span>
				</div>
			</div>
		</div>

		<!-- Champs éditables (Statut, Type, Priorité, Groupes personnalisés) -->
		<div class="wpvfh-detail-dropdowns" id="wpvfh-detail-dropdowns">
			<!-- Statut -->
			<div class="wpvfh-dropdown-group">
				<label for="wpvfh-detail-status-select">
					<span class="wpvfh-dropdown-icon">📊</span>
					<?php esc_html_e( 'Statut', 'blazing-feedback' ); ?>
				</label>
				<select id="wpvfh-detail-status-select" class="wpvfh-dropdown">
					<?php foreach ( WPVFH_Options_Manager::get_statuses() as $status ) : ?>
						<option value="<?php echo esc_attr( $status['id'] ); ?>" data-color="<?php echo esc_attr( $status['color'] ); ?>">
							<?php echo esc_html( $status['emoji'] . ' ' . $status['label'] ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
			<?php if ( $types_settings['enabled'] && WPVFH_Options_Manager::user_can_access_group( 'types' ) ) : ?>
			<div class="wpvfh-dropdown-group">
				<label for="wpvfh-detail-type">
					<span class="wpvfh-dropdown-icon">🏷️</span>
					<?php esc_html_e( 'Type', 'blazing-feedback' ); ?>
				</label>
				<select id="wpvfh-detail-type" class="wpvfh-dropdown">
					<option value=""><?php esc_html_e( '-- Sélectionner --', 'blazing-feedback' ); ?></option>
					<?php foreach ( $feedback_types as $type ) : ?>
						<?php if ( ! empty( $type['enabled'] ) ) : ?>
						<option value="<?php echo esc_attr( $type['id'] ); ?>" data-color="<?php echo esc_attr( $type['color'] ); ?>">
							<?php echo esc_html( $type['emoji'] . ' ' . $type['label'] ); ?>
						</option>
						<?php endif; ?>
					<?php endforeach; ?>
				</select>
			</div>
			<?php endif; ?>
			<?php if ( $priority_settings['enabled'] && WPVFH_Options_Manager::user_can_access_group( 'priorities' ) ) : ?>
			<div class="wpvfh-dropdown-group">
				<label for="wpvfh-detail-priority-select">
					<span class="wpvfh-dropdown-icon">⚡</span>
					<?php esc_html_e( 'Priorité', 'blazing-feedback' ); ?>
				</label>
				<select id="wpvfh-detail-priority-select" class="wpvfh-dropdown">
					<?php foreach ( $priorities as $priority ) : ?>
						<?php if ( ! empty( $priority['enabled'] ) ) : ?>
						<option value="<?php echo esc_attr( $priority['id'] ); ?>" data-color="<?php echo esc_attr( $priority['color'] ); ?>">
							<?php echo esc_html( $priority['emoji'] . ' ' . $priority['label'] ); ?>
						</option>
						<?php endif; ?>
					<?php endforeach; ?>
				</select>
			</div>
			<?php endif; ?>
			<!-- Groupes personnalisés dans les détails -->
			<?php foreach ( $custom_groups as $slug => $group ) :
				$group_settings = WPVFH_Options_Manager::get_group_settings( $slug );
				if ( ! $group_settings['enabled'] || ! WPVFH_Options_Manager::user_can_access_group( $slug ) ) {
					continue;
				}
				$group_items = WPVFH_Options_Manager::get_custom_group_items( $slug );
				if ( empty( $group_items ) ) {
					continue;
				}
			?>
			<div class="wpvfh-dropdown-group wpvfh-custom-group" data-group="<?php echo esc_attr( $slug ); ?>">
				<label for="wpvfh-detail-custom-<?php echo esc_attr( $slug ); ?>">
					<span class="wpvfh-dropdown-icon">📋</span>
					<?php echo esc_html( $group['name'] ); ?>
				</label>
				<select id="wpvfh-detail-custom-<?php echo esc_attr( $slug ); ?>" class="wpvfh-dropdown wpvfh-detail-custom-dropdown" data-group="<?php echo esc_attr( $slug ); ?>">
					<option value=""><?php esc_html_e( '-- Sélectionner --', 'blazing-feedback' ); ?></option>
					<?php foreach ( $group_items as $item ) : ?>
						<?php if ( ! empty( $item['enabled'] ) ) : ?>
						<option value="<?php echo esc_attr( $item['id'] ); ?>" data-color="<?php echo esc_attr( $item['color'] ); ?>">
							<?php echo esc_html( $item['emoji'] . ' ' . $item['label'] ); ?>
						</option>
						<?php endif; ?>
					<?php endforeach; ?>
				</select>
			</div>
			<?php endforeach; ?>
		</div>

		<!-- Commentaire -->
		<div class="wpvfh-detail-comment" id="wpvfh-detail-comment"></div>

		<!-- Pièces jointes -->
		<div class="wpvfh-detail-attachments" id="wpvfh-detail-attachments" hidden>
			<h4><?php esc_html_e( 'Pièces jointes', 'blazing-feedback' ); ?></h4>
			<div class="wpvfh-attachments-list" id="wpvfh-attachments-list"></div>
		</div>

		<!-- Tags -->
		<div class="wpvfh-detail-tags-section" id="wpvfh-detail-tags-section">
			<h4>
				<span class="wpvfh-dropdown-icon">🔖</span>
				<?php esc_html_e( 'Tags', 'blazing-feedback' ); ?>
			</h4>
			<div class="wpvfh-tags-container" id="wpvfh-detail-tags-container">
				<input type="text" id="wpvfh-detail-tags-input" class="wpvfh-tags-input-inline" placeholder="<?php esc_attr_e( 'Ajouter un tag...', 'blazing-feedback' ); ?>">
			</div>
		</div>

		<!-- Screenshot -->
		<div class="wpvfh-detail-screenshot" id="wpvfh-detail-screenshot" hidden>
			<img src="" alt="<?php esc_attr_e( 'Screenshot', 'blazing-feedback' ); ?>">
		</div>

		<!-- Réponses -->
		<div class="wpvfh-detail-replies" id="wpvfh-detail-replies" hidden>
			<h4><?php esc_html_e( 'Réponses', 'blazing-feedback' ); ?></h4>
			<div class="wpvfh-replies-list" id="wpvfh-replies-list"></div>
		</div>

		<!-- Inviter des utilisateurs -->
		<div class="wpvfh-invite-section" id="wpvfh-invite-section">
			<h4><?php esc_html_e( 'Participants', 'blazing-feedback' ); ?></h4>
			<div class="wpvfh-participants-list" id="wpvfh-participants-list">
				<!-- Liste des participants -->
			</div>
			<div class="wpvfh-invite-input-wrapper">
				<input type="text" id="wpvfh-invite-input" class="wpvfh-invite-input" placeholder="<?php esc_attr_e( 'Rechercher un utilisateur...', 'blazing-feedback' ); ?>">
				<button type="button" id="wpvfh-invite-btn" class="wpvfh-btn wpvfh-btn-small">
					<span>➕</span>
					<?php esc_html_e( 'Inviter', 'blazing-feedback' ); ?>
				</button>
			</div>
			<div id="wpvfh-user-suggestions" class="wpvfh-user-suggestions" hidden></div>
		</div>

		<!-- Actions modérateur -->
		<div class="wpvfh-detail-actions" id="wpvfh-detail-actions">
			<div class="wpvfh-reply-section">
				<label for="wpvfh-reply-input"><?php esc_html_e( 'Ajouter une réponse:', 'blazing-feedback' ); ?></label>
				<textarea id="wpvfh-reply-input" class="wpvfh-textarea" rows="2" placeholder="<?php esc_attr_e( 'Votre réponse...', 'blazing-feedback' ); ?>"></textarea>
				<button type="button" class="wpvfh-btn wpvfh-btn-primary" id="wpvfh-send-reply">
					<span class="wpvfh-btn-emoji">📨</span>
					<?php esc_html_e( 'Envoyer', 'blazing-feedback' ); ?>
				</button>
			</div>

			<!-- Bouton ciblage/repositionnement -->
			<div class="wpvfh-target-section" id="wpvfh-target-section">
				<hr class="wpvfh-separator">
				<!-- Bouton ajouter un ciblage (si pas de position) -->
				<button type="button" class="wpvfh-btn wpvfh-btn-secondary" id="wpvfh-add-target-btn" hidden>
					<span class="wpvfh-btn-emoji">🎯</span>
					<?php esc_html_e( 'Ajouter un ciblage', 'blazing-feedback' ); ?>
				</button>
				<!-- Bouton repositionner (si position existante) -->
				<button type="button" class="wpvfh-btn wpvfh-btn-secondary" id="wpvfh-reposition-feedback-btn" hidden>
					<span class="wpvfh-btn-emoji">📍</span>
					<?php esc_html_e( 'Repositionner le marqueur', 'blazing-feedback' ); ?>
				</button>
			</div>

			<!-- Bouton supprimer (visible pour créateur/admin) -->
			<div class="wpvfh-delete-section" id="wpvfh-delete-section" hidden>
				<hr class="wpvfh-separator">
				<button type="button" class="wpvfh-btn wpvfh-btn-danger" id="wpvfh-delete-feedback-btn">
					<span class="wpvfh-btn-emoji">🗑️</span>
					<?php esc_html_e( 'Supprimer ce feedback', 'blazing-feedback' ); ?>
				</button>
			</div>
		</div>
	</div>
</div><!-- /wpvfh-tab-details -->
