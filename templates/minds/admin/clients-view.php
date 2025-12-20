<?php
/**
 * Template: Client View
 *
 * @package Blazing_Minds
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$base_url = admin_url( 'admin.php?page=blazing-minds-clients' );
?>
<div class="wrap">
	<h1>
		<?php echo esc_html( $client->name ); ?>
		<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'edit', 'id' => $client->id ), $base_url ) ); ?>" class="page-title-action">
			<?php esc_html_e( 'Modifier', 'blazing-minds' ); ?>
		</a>
	</h1>

	<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-top: 20px;">
		<!-- Informations du client -->
		<div class="bzmi-card">
			<div class="bzmi-card-header">
				<h3><?php esc_html_e( 'Informations', 'blazing-minds' ); ?></h3>
			</div>
			<div class="bzmi-card-body">
				<div class="bzmi-details-grid">
					<?php if ( $client->email ) : ?>
						<div class="bzmi-detail-item">
							<label><?php esc_html_e( 'Email', 'blazing-minds' ); ?></label>
							<div class="value"><a href="mailto:<?php echo esc_attr( $client->email ); ?>"><?php echo esc_html( $client->email ); ?></a></div>
						</div>
					<?php endif; ?>

					<?php if ( $client->phone ) : ?>
						<div class="bzmi-detail-item">
							<label><?php esc_html_e( 'Téléphone', 'blazing-minds' ); ?></label>
							<div class="value"><?php echo esc_html( $client->phone ); ?></div>
						</div>
					<?php endif; ?>

					<?php if ( $client->company ) : ?>
						<div class="bzmi-detail-item">
							<label><?php esc_html_e( 'Entreprise', 'blazing-minds' ); ?></label>
							<div class="value"><?php echo esc_html( $client->company ); ?></div>
						</div>
					<?php endif; ?>

					<?php if ( $client->website ) : ?>
						<div class="bzmi-detail-item">
							<label><?php esc_html_e( 'Site web', 'blazing-minds' ); ?></label>
							<div class="value"><a href="<?php echo esc_url( $client->website ); ?>" target="_blank"><?php echo esc_html( $client->website ); ?></a></div>
						</div>
					<?php endif; ?>

					<div class="bzmi-detail-item">
						<label><?php esc_html_e( 'Statut', 'blazing-minds' ); ?></label>
						<div class="value">
							<span class="bzmi-status <?php echo esc_attr( $client->status ); ?>">
								<?php echo esc_html( BZMI_Client::get_statuses()[ $client->status ] ?? $client->status ); ?>
							</span>
						</div>
					</div>
				</div>

				<?php if ( $client->address ) : ?>
					<div style="margin-top: 20px;">
						<label style="font-weight: bold; display: block; margin-bottom: 5px;"><?php esc_html_e( 'Adresse', 'blazing-minds' ); ?></label>
						<p><?php echo nl2br( esc_html( $client->address ) ); ?></p>
					</div>
				<?php endif; ?>

				<?php if ( $client->notes ) : ?>
					<div style="margin-top: 20px;">
						<label style="font-weight: bold; display: block; margin-bottom: 5px;"><?php esc_html_e( 'Notes', 'blazing-minds' ); ?></label>
						<p><?php echo nl2br( esc_html( $client->notes ) ); ?></p>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- Statistiques -->
		<div>
			<div class="bzmi-card">
				<div class="bzmi-card-header">
					<h3><?php esc_html_e( 'Statistiques', 'blazing-minds' ); ?></h3>
				</div>
				<div class="bzmi-card-body">
					<div class="bzmi-stat-card" style="box-shadow: none; border: none; padding: 10px 0;">
						<h3><?php esc_html_e( 'Portefeuilles', 'blazing-minds' ); ?></h3>
						<div class="value"><?php echo esc_html( $stats['portfolios_count'] ); ?></div>
					</div>
					<div class="bzmi-stat-card" style="box-shadow: none; border: none; padding: 10px 0;">
						<h3><?php esc_html_e( 'Projets', 'blazing-minds' ); ?></h3>
						<div class="value"><?php echo esc_html( $stats['projects_count'] ); ?></div>
					</div>
					<div class="bzmi-stat-card" style="box-shadow: none; border: none; padding: 10px 0;">
						<h3><?php esc_html_e( 'Projets actifs', 'blazing-minds' ); ?></h3>
						<div class="value primary"><?php echo esc_html( $stats['active_projects'] ); ?></div>
					</div>
					<div class="bzmi-stat-card" style="box-shadow: none; border: none; padding: 10px 0;">
						<h3><?php esc_html_e( 'Budget total', 'blazing-minds' ); ?></h3>
						<div class="value success"><?php echo esc_html( number_format( $stats['total_budget'], 2, ',', ' ' ) ); ?> &euro;</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Portefeuilles du client -->
	<div class="bzmi-card" style="margin-top: 20px;">
		<div class="bzmi-card-header">
			<h3><?php esc_html_e( 'Portefeuilles', 'blazing-minds' ); ?></h3>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=blazing-minds-portfolios&action=new&client_id=' . $client->id ) ); ?>" class="button button-small">
				<?php esc_html_e( 'Ajouter', 'blazing-minds' ); ?>
			</a>
		</div>
		<div class="bzmi-card-body">
			<?php if ( empty( $portfolios ) ) : ?>
				<p class="description"><?php esc_html_e( 'Aucun portefeuille pour ce client.', 'blazing-minds' ); ?></p>
			<?php else : ?>
				<table class="bzmi-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Nom', 'blazing-minds' ); ?></th>
							<th><?php esc_html_e( 'Projets', 'blazing-minds' ); ?></th>
							<th><?php esc_html_e( 'Progression', 'blazing-minds' ); ?></th>
							<th><?php esc_html_e( 'Statut', 'blazing-minds' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $portfolios as $portfolio ) : ?>
							<tr>
								<td>
									<span style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; background: <?php echo esc_attr( $portfolio->color ); ?>; margin-right: 8px;"></span>
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=blazing-minds-portfolios&action=view&id=' . $portfolio->id ) ); ?>">
										<?php echo esc_html( $portfolio->name ); ?>
									</a>
								</td>
								<td><?php echo esc_html( $portfolio->projects_count() ); ?></td>
								<td>
									<div style="background: #eee; border-radius: 10px; height: 10px; width: 100px; display: inline-block;">
										<div style="background: #2ecc71; border-radius: 10px; height: 10px; width: <?php echo esc_attr( $portfolio->calculate_progress() ); ?>%;"></div>
									</div>
									<?php echo esc_html( $portfolio->calculate_progress() ); ?>%
								</td>
								<td>
									<span class="bzmi-status <?php echo esc_attr( $portfolio->status ); ?>">
										<?php echo esc_html( $portfolio->status ); ?>
									</span>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
	</div>

	<p>
		<a href="<?php echo esc_url( $base_url ); ?>" class="button">&larr; <?php esc_html_e( 'Retour à la liste', 'blazing-minds' ); ?></a>
	</p>
</div>
