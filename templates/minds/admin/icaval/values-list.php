<?php
/**
 * Template: Values List
 *
 * @package Blazing_Minds
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$base_url = admin_url( 'admin.php?page=blazing-minds-icaval&tab=values' );
?>
<div class="bzmi-card" style="margin-top: 20px;">
	<div class="bzmi-card-header">
		<h3><?php esc_html_e( 'Valeurs', 'blazing-minds' ); ?></h3>
		<a href="<?php echo esc_url( add_query_arg( 'action', 'new', $base_url ) ); ?>" class="button button-primary">
			<?php esc_html_e( 'Nouvelle valeur', 'blazing-minds' ); ?>
		</a>
	</div>
	<div class="bzmi-card-body">
		<?php if ( empty( $items ) ) : ?>
			<div class="bzmi-empty-state">
				<span class="dashicons dashicons-chart-area"></span>
				<h3><?php esc_html_e( 'Aucune valeur', 'blazing-minds' ); ?></h3>
				<p><?php esc_html_e( 'Les valeurs mesurent l\'impact des actions réalisées.', 'blazing-minds' ); ?></p>
			</div>
		<?php else : ?>
			<table class="bzmi-table wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Titre', 'blazing-minds' ); ?></th>
						<th><?php esc_html_e( 'Type', 'blazing-minds' ); ?></th>
						<th><?php esc_html_e( 'Valeur monétaire', 'blazing-minds' ); ?></th>
						<th><?php esc_html_e( 'Temps économisé', 'blazing-minds' ); ?></th>
						<th><?php esc_html_e( 'Score d\'impact', 'blazing-minds' ); ?></th>
						<th><?php esc_html_e( 'Validée', 'blazing-minds' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'blazing-minds' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $items as $item ) : ?>
						<tr>
							<td><strong><?php echo esc_html( $item->title ); ?></strong></td>
							<td><?php echo esc_html( BZMI_Value::get_value_types()[ $item->value_type ] ?? $item->value_type ); ?></td>
							<td>
								<?php if ( $item->monetary_value > 0 ) : ?>
									<?php echo esc_html( number_format( $item->monetary_value, 2, ',', ' ' ) ); ?> &euro;
								<?php else : ?>
									-
								<?php endif; ?>
							</td>
							<td>
								<?php if ( $item->time_saved > 0 ) : ?>
									<?php echo esc_html( $item->time_saved ); ?> min
								<?php else : ?>
									-
								<?php endif; ?>
							</td>
							<td>
								<div style="background: #eee; border-radius: 10px; height: 10px; width: 80px; display: inline-block;">
									<div style="background: #2ecc71; border-radius: 10px; height: 10px; width: <?php echo esc_attr( $item->impact_score ); ?>%;"></div>
								</div>
								<?php echo esc_html( $item->impact_score ); ?>%
							</td>
							<td>
								<?php if ( $item->validated ) : ?>
									<span style="color: green;">&#10004;</span>
								<?php else : ?>
									<span style="color: orange;">&#9711;</span>
								<?php endif; ?>
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
