<?php
/**
 * Template: Clients List
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
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Clients', 'blazing-minds' ); ?></h1>
	<a href="<?php echo esc_url( add_query_arg( 'action', 'new', $base_url ) ); ?>" class="page-title-action">
		<?php esc_html_e( 'Ajouter', 'blazing-minds' ); ?>
	</a>
	<hr class="wp-header-end">

	<!-- Filtres -->
	<form method="get" class="bzmi-filter-form">
		<input type="hidden" name="page" value="blazing-minds-clients">
		<div style="display: flex; gap: 10px; margin-bottom: 20px;">
			<input type="search" name="s" value="<?php echo esc_attr( isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '' ); ?>" placeholder="<?php esc_attr_e( 'Rechercher...', 'blazing-minds' ); ?>">
			<select name="status">
				<option value=""><?php esc_html_e( 'Tous les statuts', 'blazing-minds' ); ?></option>
				<?php foreach ( $statuses as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '', $key ); ?>>
						<?php echo esc_html( $label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<button type="submit" class="button"><?php esc_html_e( 'Filtrer', 'blazing-minds' ); ?></button>
		</div>
	</form>

	<?php if ( empty( $clients ) ) : ?>
		<div class="bzmi-empty-state">
			<span class="dashicons dashicons-groups"></span>
			<h3><?php esc_html_e( 'Aucun client', 'blazing-minds' ); ?></h3>
			<p><?php esc_html_e( 'Commencez par créer votre premier client.', 'blazing-minds' ); ?></p>
			<a href="<?php echo esc_url( add_query_arg( 'action', 'new', $base_url ) ); ?>" class="button button-primary">
				<?php esc_html_e( 'Créer un client', 'blazing-minds' ); ?>
			</a>
		</div>
	<?php else : ?>
		<table class="bzmi-table wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Nom', 'blazing-minds' ); ?></th>
					<th><?php esc_html_e( 'Email', 'blazing-minds' ); ?></th>
					<th><?php esc_html_e( 'Entreprise', 'blazing-minds' ); ?></th>
					<th><?php esc_html_e( 'Portefeuilles', 'blazing-minds' ); ?></th>
					<th><?php esc_html_e( 'Statut', 'blazing-minds' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'blazing-minds' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $clients as $client ) : ?>
					<tr>
						<td>
							<strong>
								<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'view', 'id' => $client->id ), $base_url ) ); ?>">
									<?php echo esc_html( $client->name ); ?>
								</a>
							</strong>
						</td>
						<td><?php echo esc_html( $client->email ); ?></td>
						<td><?php echo esc_html( $client->company ); ?></td>
						<td><?php echo esc_html( $client->portfolios_count() ); ?></td>
						<td>
							<span class="bzmi-status <?php echo esc_attr( $client->status ); ?>">
								<?php echo esc_html( $statuses[ $client->status ] ?? $client->status ); ?>
							</span>
						</td>
						<td class="actions">
							<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'edit', 'id' => $client->id ), $base_url ) ); ?>">
								<?php esc_html_e( 'Modifier', 'blazing-minds' ); ?>
							</a>
							<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'delete', 'id' => $client->id ), $base_url ), 'bzmi_delete_client_' . $client->id ) ); ?>" class="bzmi-delete-link" style="color: #a00;">
								<?php esc_html_e( 'Supprimer', 'blazing-minds' ); ?>
							</a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php echo BZMI_Admin::pagination( $total, $per_page, $current_page, $base_url ); // phpcs:ignore ?>
	<?php endif; ?>
</div>
