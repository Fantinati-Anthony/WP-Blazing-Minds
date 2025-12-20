<?php
/**
 * Template: Clarifications List
 *
 * @package Blazing_Minds
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$base_url = admin_url( 'admin.php?page=blazing-minds-icaval&tab=clarifications' );
?>
<div class="bzmi-card" style="margin-top: 20px;">
	<div class="bzmi-card-header">
		<h3><?php esc_html_e( 'Clarifications', 'blazing-minds' ); ?></h3>
		<a href="<?php echo esc_url( add_query_arg( 'action', 'new', $base_url ) ); ?>" class="button button-primary">
			<?php esc_html_e( 'Nouvelle clarification', 'blazing-minds' ); ?>
		</a>
	</div>
	<div class="bzmi-card-body">
		<?php if ( empty( $items ) ) : ?>
			<div class="bzmi-empty-state">
				<span class="dashicons dashicons-search"></span>
				<h3><?php esc_html_e( 'Aucune clarification', 'blazing-minds' ); ?></h3>
				<p><?php esc_html_e( 'Les clarifications permettent d\'approfondir les informations reçues.', 'blazing-minds' ); ?></p>
			</div>
		<?php else : ?>
			<table class="bzmi-table wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Question', 'blazing-minds' ); ?></th>
						<th><?php esc_html_e( 'Information', 'blazing-minds' ); ?></th>
						<th><?php esc_html_e( 'Résolue', 'blazing-minds' ); ?></th>
						<th><?php esc_html_e( 'IA', 'blazing-minds' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'blazing-minds' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $items as $item ) : ?>
						<tr>
							<td><?php echo esc_html( wp_trim_words( $item->question, 15 ) ); ?></td>
							<td>
								<?php
								$info = $item->information();
								echo $info ? esc_html( $info->title ) : '-';
								?>
							</td>
							<td>
								<?php if ( $item->resolved ) : ?>
									<span style="color: green;">&#10004;</span>
								<?php else : ?>
									<span style="color: orange;">&#9711;</span>
								<?php endif; ?>
							</td>
							<td>
								<?php if ( $item->ai_suggested ) : ?>
									<span class="dashicons dashicons-superhero" title="<?php esc_attr_e( 'Suggéré par IA', 'blazing-minds' ); ?>"></span>
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
