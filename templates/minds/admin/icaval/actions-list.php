<?php
/**
 * Template: Actions List
 *
 * @package Blazing_Minds
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$base_url = admin_url( 'admin.php?page=blazing-minds-icaval&tab=actions' );
$priorities = BZMI_Action::get_priorities();
?>
<div class="bzmi-card" style="margin-top: 20px;">
	<div class="bzmi-card-header">
		<h3><?php esc_html_e( 'Actions', 'blazing-minds' ); ?></h3>
		<a href="<?php echo esc_url( add_query_arg( 'action', 'new', $base_url ) ); ?>" class="button button-primary">
			<?php esc_html_e( 'Nouvelle action', 'blazing-minds' ); ?>
		</a>
	</div>
	<div class="bzmi-card-body">
		<?php if ( empty( $items ) ) : ?>
			<div class="bzmi-empty-state">
				<span class="dashicons dashicons-hammer"></span>
				<h3><?php esc_html_e( 'Aucune action', 'blazing-minds' ); ?></h3>
				<p><?php esc_html_e( 'Les actions sont les tâches à réaliser suite aux clarifications.', 'blazing-minds' ); ?></p>
			</div>
		<?php else : ?>
			<table class="bzmi-table wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Titre', 'blazing-minds' ); ?></th>
						<th><?php esc_html_e( 'Type', 'blazing-minds' ); ?></th>
						<th><?php esc_html_e( 'Priorité', 'blazing-minds' ); ?></th>
						<th><?php esc_html_e( 'Échéance', 'blazing-minds' ); ?></th>
						<th><?php esc_html_e( 'Statut', 'blazing-minds' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'blazing-minds' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $items as $item ) : ?>
						<tr <?php echo $item->is_overdue() ? 'style="background: #fff5f5;"' : ''; ?>>
							<td>
								<strong><?php echo esc_html( $item->title ); ?></strong>
								<?php if ( $item->is_overdue() ) : ?>
									<span style="color: red; font-size: 11px;"><?php esc_html_e( '(En retard)', 'blazing-minds' ); ?></span>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html( BZMI_Action::get_action_types()[ $item->action_type ] ?? $item->action_type ); ?></td>
							<td>
								<span class="bzmi-priority <?php echo esc_attr( $item->priority ); ?>">
									<?php echo esc_html( $priorities[ $item->priority ] ?? $item->priority ); ?>
								</span>
							</td>
							<td>
								<?php echo $item->due_date ? esc_html( date_i18n( get_option( 'date_format' ), strtotime( $item->due_date ) ) ) : '-'; ?>
							</td>
							<td>
								<span class="bzmi-status <?php echo esc_attr( $item->status ); ?>">
									<?php echo esc_html( BZMI_Action::get_statuses()[ $item->status ] ?? $item->status ); ?>
								</span>
							</td>
							<td class="actions">
								<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'edit', 'id' => $item->id ), $base_url ) ); ?>">
									<?php esc_html_e( 'Modifier', 'blazing-minds' ); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
</div>
