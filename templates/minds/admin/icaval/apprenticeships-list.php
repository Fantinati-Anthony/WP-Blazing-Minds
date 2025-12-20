<?php
/**
 * Template: Apprenticeships List
 *
 * @package Blazing_Minds
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$base_url = admin_url( 'admin.php?page=blazing-minds-icaval&tab=apprenticeships' );
$lesson_types = BZMI_Apprenticeship::get_lesson_types();
?>
<div class="bzmi-card" style="margin-top: 20px;">
	<div class="bzmi-card-header">
		<h3><?php esc_html_e( 'Apprentissages', 'blazing-minds' ); ?></h3>
		<a href="<?php echo esc_url( add_query_arg( 'action', 'new', $base_url ) ); ?>" class="button button-primary">
			<?php esc_html_e( 'Nouvel apprentissage', 'blazing-minds' ); ?>
		</a>
	</div>
	<div class="bzmi-card-body">
		<?php if ( empty( $items ) ) : ?>
			<div class="bzmi-empty-state">
				<span class="dashicons dashicons-welcome-learn-more"></span>
				<h3><?php esc_html_e( 'Aucun apprentissage', 'blazing-minds' ); ?></h3>
				<p><?php esc_html_e( 'Les apprentissages capitalisent les connaissances acquises au fil des projets.', 'blazing-minds' ); ?></p>
			</div>
		<?php else : ?>
			<table class="bzmi-table wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Titre', 'blazing-minds' ); ?></th>
						<th><?php esc_html_e( 'Type', 'blazing-minds' ); ?></th>
						<th><?php esc_html_e( 'Source', 'blazing-minds' ); ?></th>
						<th><?php esc_html_e( 'Réutilisable', 'blazing-minds' ); ?></th>
						<th><?php esc_html_e( 'Utilisations', 'blazing-minds' ); ?></th>
						<th><?php esc_html_e( 'IA', 'blazing-minds' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'blazing-minds' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $items as $item ) : ?>
						<tr>
							<td><strong><?php echo esc_html( $item->title ); ?></strong></td>
							<td>
								<span class="bzmi-status <?php echo esc_attr( $item->lesson_type ); ?>">
									<?php echo esc_html( $lesson_types[ $item->lesson_type ] ?? $item->lesson_type ); ?>
								</span>
							</td>
							<td><?php echo esc_html( ucfirst( $item->source_type ) ); ?> #<?php echo esc_html( $item->source_id ); ?></td>
							<td>
								<?php if ( $item->reusable ) : ?>
									<span style="color: green;">&#10004;</span>
								<?php else : ?>
									<span style="color: #999;">-</span>
								<?php endif; ?>
							</td>
							<td><?php echo esc_html( $item->usage_count ); ?></td>
							<td>
								<?php if ( $item->ai_generated ) : ?>
									<span class="dashicons dashicons-superhero" title="<?php esc_attr_e( 'Généré par IA', 'blazing-minds' ); ?>"></span>
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
