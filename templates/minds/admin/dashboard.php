<?php
/**
 * Template: Dashboard
 *
 * @package Blazing_Minds
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap">
	<h1><?php esc_html_e( 'Blazing Minds - Tableau de bord', 'blazing-minds' ); ?></h1>

	<!-- Statistiques globales -->
	<div class="bzmi-dashboard">
		<div class="bzmi-stat-card">
			<h3><?php esc_html_e( 'Clients', 'blazing-minds' ); ?></h3>
			<div class="value"><?php echo esc_html( $stats['clients'] ); ?></div>
		</div>
		<div class="bzmi-stat-card">
			<h3><?php esc_html_e( 'Portefeuilles', 'blazing-minds' ); ?></h3>
			<div class="value"><?php echo esc_html( $stats['portfolios'] ); ?></div>
		</div>
		<div class="bzmi-stat-card">
			<h3><?php esc_html_e( 'Projets actifs', 'blazing-minds' ); ?></h3>
			<div class="value primary"><?php echo esc_html( $stats['active_projects'] ); ?></div>
		</div>
		<div class="bzmi-stat-card">
			<h3><?php esc_html_e( 'Nouvelles informations', 'blazing-minds' ); ?></h3>
			<div class="value warning"><?php echo esc_html( $stats['new_informations'] ); ?></div>
		</div>
		<div class="bzmi-stat-card">
			<h3><?php esc_html_e( 'Actions en attente', 'blazing-minds' ); ?></h3>
			<div class="value danger"><?php echo esc_html( $stats['pending_actions'] ); ?></div>
		</div>
		<div class="bzmi-stat-card">
			<h3><?php esc_html_e( 'Apprentissages', 'blazing-minds' ); ?></h3>
			<div class="value success"><?php echo esc_html( $stats['apprenticeships'] ); ?></div>
		</div>
	</div>

	<!-- Diagramme ICAVAL -->
	<div class="bzmi-card">
		<div class="bzmi-card-header">
			<h3><?php esc_html_e( 'Répartition ICAVAL', 'blazing-minds' ); ?></h3>
		</div>
		<div class="bzmi-card-body">
			<div class="bzmi-workflow-diagram">
				<?php
				$stages = array(
					'information'    => 'I',
					'clarification'  => 'C',
					'action'         => 'A',
					'value'          => 'V',
					'apprenticeship' => 'AL',
				);
				$stage_counts = $stats['icaval_stages'];
				$first = true;

				foreach ( $stages as $stage => $abbr ) :
					if ( ! $first ) :
						?>
						<div class="bzmi-arrow">&rarr;</div>
						<?php
					endif;
					$first = false;
					$count = isset( $stage_counts[ $stage ] ) ? $stage_counts[ $stage ] : 0;
					?>
					<div class="bzmi-stage">
						<?php echo esc_html( $abbr ); ?>
						<small><?php echo esc_html( $count ); ?></small>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
		<!-- Informations récentes -->
		<div class="bzmi-card">
			<div class="bzmi-card-header">
				<h3><?php esc_html_e( 'Dernières informations', 'blazing-minds' ); ?></h3>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=blazing-minds-icaval&tab=informations' ) ); ?>" class="button button-small">
					<?php esc_html_e( 'Voir tout', 'blazing-minds' ); ?>
				</a>
			</div>
			<div class="bzmi-card-body">
				<?php if ( empty( $pending_informations ) ) : ?>
					<p class="description"><?php esc_html_e( 'Aucune nouvelle information.', 'blazing-minds' ); ?></p>
				<?php else : ?>
					<ul style="margin: 0; padding: 0; list-style: none;">
						<?php foreach ( $pending_informations as $info ) : ?>
							<li style="padding: 10px 0; border-bottom: 1px solid #eee;">
								<strong><?php echo esc_html( $info->title ); ?></strong>
								<br>
								<small>
									<span class="bzmi-status <?php echo esc_attr( $info->icaval_stage ); ?>">
										<?php echo esc_html( $info->icaval_stage ); ?>
									</span>
									<?php echo esc_html( human_time_diff( strtotime( $info->created_at ), current_time( 'timestamp' ) ) ); ?>
								</small>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</div>

		<!-- Actions en retard -->
		<div class="bzmi-card">
			<div class="bzmi-card-header">
				<h3><?php esc_html_e( 'Actions en retard', 'blazing-minds' ); ?></h3>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=blazing-minds-icaval&tab=actions' ) ); ?>" class="button button-small">
					<?php esc_html_e( 'Voir tout', 'blazing-minds' ); ?>
				</a>
			</div>
			<div class="bzmi-card-body">
				<?php if ( empty( $overdue_actions ) ) : ?>
					<p class="description" style="color: green;"><?php esc_html_e( 'Aucune action en retard !', 'blazing-minds' ); ?></p>
				<?php else : ?>
					<ul style="margin: 0; padding: 0; list-style: none;">
						<?php foreach ( array_slice( $overdue_actions, 0, 5 ) as $action ) : ?>
							<li style="padding: 10px 0; border-bottom: 1px solid #eee;">
								<strong style="color: #e74c3c;"><?php echo esc_html( $action->title ); ?></strong>
								<br>
								<small>
									<?php esc_html_e( 'Échéance:', 'blazing-minds' ); ?>
									<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $action->due_date ) ) ); ?>
								</small>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<!-- Activités récentes -->
	<div class="bzmi-card" style="margin-top: 20px;">
		<div class="bzmi-card-header">
			<h3><?php esc_html_e( 'Activité récente', 'blazing-minds' ); ?></h3>
		</div>
		<div class="bzmi-card-body">
			<?php if ( empty( $recent_activities ) ) : ?>
				<p class="description"><?php esc_html_e( 'Aucune activité récente.', 'blazing-minds' ); ?></p>
			<?php else : ?>
				<table class="bzmi-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Action', 'blazing-minds' ); ?></th>
							<th><?php esc_html_e( 'Type', 'blazing-minds' ); ?></th>
							<th><?php esc_html_e( 'Utilisateur', 'blazing-minds' ); ?></th>
							<th><?php esc_html_e( 'Date', 'blazing-minds' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $recent_activities as $activity ) : ?>
							<tr>
								<td>
									<span class="bzmi-status <?php echo esc_attr( $activity['action'] ); ?>">
										<?php echo esc_html( ucfirst( $activity['action'] ) ); ?>
									</span>
								</td>
								<td><?php echo esc_html( $activity['object_type'] ); ?> #<?php echo esc_html( $activity['object_id'] ); ?></td>
								<td>
									<?php
									$user = get_user_by( 'id', $activity['user_id'] );
									echo $user ? esc_html( $user->display_name ) : '-';
									?>
								</td>
								<td><?php echo esc_html( human_time_diff( strtotime( $activity['created_at'] ), current_time( 'timestamp' ) ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
	</div>
</div>
