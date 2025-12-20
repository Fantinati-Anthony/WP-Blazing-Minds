<?php
/**
 * Template: Informations List
 *
 * @package Blazing_Minds
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$base_url = admin_url( 'admin.php?page=blazing-minds-icaval&tab=informations' );
$stages = BZMI_Information::get_icaval_stages();
$priorities = BZMI_Information::get_priorities();
?>
<div class="bzmi-card" style="margin-top: 20px;">
	<div class="bzmi-card-header">
		<h3><?php esc_html_e( 'Informations', 'blazing-minds' ); ?></h3>
		<a href="<?php echo esc_url( add_query_arg( 'action', 'new', $base_url ) ); ?>" class="button button-primary">
			<?php esc_html_e( 'Nouvelle information', 'blazing-minds' ); ?>
		</a>
	</div>
	<div class="bzmi-card-body">
		<!-- Filtres -->
		<form method="get" style="margin-bottom: 20px;">
			<input type="hidden" name="page" value="blazing-minds-icaval">
			<input type="hidden" name="tab" value="informations">
			<select name="project_id" onchange="this.form.submit()">
				<option value=""><?php esc_html_e( 'Tous les projets', 'blazing-minds' ); ?></option>
				<?php foreach ( $projects as $project ) : ?>
					<option value="<?php echo esc_attr( $project->id ); ?>" <?php selected( isset( $_GET['project_id'] ) ? intval( $_GET['project_id'] ) : 0, $project->id ); ?>>
						<?php echo esc_html( $project->name ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</form>

		<?php if ( empty( $items ) ) : ?>
			<div class="bzmi-empty-state">
				<span class="dashicons dashicons-info"></span>
				<h3><?php esc_html_e( 'Aucune information', 'blazing-minds' ); ?></h3>
				<p><?php esc_html_e( 'Les informations peuvent venir de Blazing Feedback ou être créées manuellement.', 'blazing-minds' ); ?></p>
			</div>
		<?php else : ?>
			<table class="bzmi-table wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Titre', 'blazing-minds' ); ?></th>
						<th><?php esc_html_e( 'Projet', 'blazing-minds' ); ?></th>
						<th><?php esc_html_e( 'Type', 'blazing-minds' ); ?></th>
						<th><?php esc_html_e( 'Étape ICAVAL', 'blazing-minds' ); ?></th>
						<th><?php esc_html_e( 'Priorité', 'blazing-minds' ); ?></th>
						<th><?php esc_html_e( 'Date', 'blazing-minds' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'blazing-minds' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $items as $item ) : ?>
						<tr>
							<td>
								<strong>
									<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'view', 'id' => $item->id ), $base_url ) ); ?>">
										<?php echo esc_html( $item->title ); ?>
									</a>
								</strong>
							</td>
							<td>
								<?php
								$project = $item->project();
								echo $project ? esc_html( $project->name ) : '-';
								?>
							</td>
							<td><?php echo esc_html( BZMI_Information::get_types()[ $item->type ] ?? $item->type ); ?></td>
							<td>
								<!-- Mini progress ICAVAL -->
								<div class="bzmi-icaval-progress" style="gap: 2px;">
									<?php
									$stage_keys = array_keys( $stages );
									$current_index = array_search( $item->icaval_stage, $stage_keys, true );
									foreach ( $stage_keys as $index => $stage ) :
										$class = 'bzmi-icaval-step';
										if ( $index < $current_index ) {
											$class .= ' completed';
										} elseif ( $index === $current_index ) {
											$class .= ' active';
										}
										?>
										<div class="<?php echo esc_attr( $class ); ?>" style="width: 20px; height: 20px; font-size: 8px;">
											<?php echo esc_html( strtoupper( substr( $stage, 0, 1 ) ) ); ?>
										</div>
									<?php endforeach; ?>
								</div>
							</td>
							<td>
								<span class="bzmi-priority <?php echo esc_attr( $item->priority ); ?>">
									<?php echo esc_html( $priorities[ $item->priority ] ?? $item->priority ); ?>
								</span>
							</td>
							<td><?php echo esc_html( human_time_diff( strtotime( $item->created_at ), current_time( 'timestamp' ) ) ); ?></td>
							<td class="actions">
								<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'edit', 'id' => $item->id ), $base_url ) ); ?>">
									<?php esc_html_e( 'Modifier', 'blazing-minds' ); ?>
								</a>
								<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'delete', 'id' => $item->id ), $base_url ), 'bzmi_delete_informations_' . $item->id ) ); ?>" class="bzmi-delete-link" style="color: #a00;">
									<?php esc_html_e( 'Supprimer', 'blazing-minds' ); ?>
								</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<?php echo BZMI_Admin::pagination( $total, 20, isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1, $base_url ); // phpcs:ignore ?>
		<?php endif; ?>
	</div>
</div>
