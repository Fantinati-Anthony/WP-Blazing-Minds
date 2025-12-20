<?php
/**
 * Template: Information Form
 *
 * @package Blazing_Minds
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$base_url = admin_url( 'admin.php?page=blazing-minds-icaval&tab=informations' );
$stages = BZMI_Information::get_icaval_stages();
?>
<div class="bzmi-card" style="margin-top: 20px;">
	<div class="bzmi-card-header">
		<h3>
			<?php echo $is_new ? esc_html__( 'Nouvelle information', 'blazing-minds' ) : esc_html__( 'Modifier l\'information', 'blazing-minds' ); ?>
		</h3>
	</div>
	<div class="bzmi-card-body">
		<form method="post" action="" class="bzmi-form">
			<?php wp_nonce_field( 'bzmi_save_information' ); ?>
			<input type="hidden" name="action" value="save">
			<input type="hidden" name="item_id" value="<?php echo esc_attr( $item->id ); ?>">

			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="project_id"><?php esc_html_e( 'Projet', 'blazing-minds' ); ?> <span class="required">*</span></label>
					</th>
					<td>
						<select id="project_id" name="project_id" required>
							<option value=""><?php esc_html_e( '-- Sélectionner --', 'blazing-minds' ); ?></option>
							<?php foreach ( $projects as $project ) : ?>
								<option value="<?php echo esc_attr( $project->id ); ?>" <?php selected( $item->project_id, $project->id ); ?>>
									<?php echo esc_html( $project->name ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="title"><?php esc_html_e( 'Titre', 'blazing-minds' ); ?> <span class="required">*</span></label>
					</th>
					<td>
						<input type="text" id="title" name="title" value="<?php echo esc_attr( $item->title ); ?>" class="large-text" required>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="content"><?php esc_html_e( 'Contenu', 'blazing-minds' ); ?></label>
					</th>
					<td>
						<textarea id="content" name="content" rows="8" class="large-text"><?php echo esc_textarea( $item->content ); ?></textarea>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="type"><?php esc_html_e( 'Type', 'blazing-minds' ); ?></label>
					</th>
					<td>
						<select id="type" name="type">
							<?php foreach ( $types as $key => $label ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $item->type ?: 'manual', $key ); ?>>
									<?php echo esc_html( $label ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="priority"><?php esc_html_e( 'Priorité', 'blazing-minds' ); ?></label>
					</th>
					<td>
						<select id="priority" name="priority">
							<?php foreach ( $priorities as $key => $label ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $item->priority ?: 'normal', $key ); ?>>
									<?php echo esc_html( $label ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="icaval_stage"><?php esc_html_e( 'Étape ICAVAL', 'blazing-minds' ); ?></label>
					</th>
					<td>
						<select id="icaval_stage" name="icaval_stage">
							<?php foreach ( $stages as $key => $label ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $item->icaval_stage ?: 'information', $key ); ?>>
									<?php echo esc_html( $label ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="category"><?php esc_html_e( 'Catégorie', 'blazing-minds' ); ?></label>
					</th>
					<td>
						<input type="text" id="category" name="category" value="<?php echo esc_attr( $item->category ); ?>" class="regular-text">
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="tags"><?php esc_html_e( 'Tags', 'blazing-minds' ); ?></label>
					</th>
					<td>
						<input type="text" id="tags" name="tags" value="<?php echo esc_attr( is_array( $item->get_tags() ) ? implode( ', ', $item->get_tags() ) : '' ); ?>" class="large-text">
						<p class="description"><?php esc_html_e( 'Séparez les tags par des virgules.', 'blazing-minds' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="status"><?php esc_html_e( 'Statut', 'blazing-minds' ); ?></label>
					</th>
					<td>
						<select id="status" name="status">
							<?php foreach ( $statuses as $key => $label ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $item->status ?: 'new', $key ); ?>>
									<?php echo esc_html( $label ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
			</table>

			<p class="submit">
				<input type="submit" class="button button-primary" value="<?php echo $is_new ? esc_attr__( 'Créer', 'blazing-minds' ) : esc_attr__( 'Mettre à jour', 'blazing-minds' ); ?>">
				<a href="<?php echo esc_url( $base_url ); ?>" class="button"><?php esc_html_e( 'Annuler', 'blazing-minds' ); ?></a>
			</p>
		</form>
	</div>
</div>
