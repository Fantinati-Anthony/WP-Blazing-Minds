<?php
/**
 * Trait pour la section Bouton flottant (Design)
 *
 * @package Blazing_Feedback
 * @since 1.9.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait de gestion de la section Bouton flottant
 *
 * @since 1.9.0
 */
trait WPVFH_Admin_Settings_Design_Button {

	/**
	 * Rendre la section Position
	 *
	 * @since 1.9.0
	 * @return void
	 */
	public static function render_design_position_section() {
		?>
		<div class="wpvfh-settings-section">
			<h2><?php esc_html_e( 'Position', 'blazing-feedback' ); ?></h2>

			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Position du bouton', 'blazing-feedback' ); ?></th>
					<td>
						<?php self::render_position_field(); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Position du volet', 'blazing-feedback' ); ?></th>
					<td>
						<?php self::render_panel_position_field(); ?>
					</td>
				</tr>
			</table>
		</div>
		<?php
	}

	/**
	 * Rendre la section Bouton flottant
	 *
	 * @since 1.9.0
	 * @param array $options Options actuelles.
	 * @return void
	 */
	public static function render_design_floating_button_section( $options ) {
		$button_color = $options['button_color'];
		$button_color_hover = $options['button_color_hover'];
		$button_size = $options['button_size'];
		$button_style = $options['button_style'];
		$border_radius = $options['border_radius'];
		$border_radius_unit = $options['border_radius_unit'];
		$button_margin = $options['button_margin'];
		$button_border_width = $options['button_border_width'];
		$button_border_color = $options['button_border_color'];
		$button_shadow_blur = $options['button_shadow_blur'];
		$button_shadow_opacity = $options['button_shadow_opacity'];
		$button_shadow_color = $options['button_shadow_color'];
		$badge_bg_color = $options['badge_bg_color'];
		$badge_text_color = $options['badge_text_color'];
		$is_corner = $options['is_corner'];
		?>
		<div class="wpvfh-settings-section">
			<h2><?php esc_html_e( 'Bouton flottant', 'blazing-feedback' ); ?></h2>

			<table class="form-table">
				<tr>
					<th scope="row"><?php esc_html_e( 'Couleur du bouton', 'blazing-feedback' ); ?></th>
					<td>
						<div style="display: flex; align-items: center; gap: 10px;">
							<input type="color" name="wpvfh_button_color" id="wpvfh_button_color" value="<?php echo esc_attr( $button_color ); ?>">
							<input type="text" value="<?php echo esc_attr( $button_color ); ?>" class="wpvfh-color-hex-input" data-color-input="wpvfh_button_color" style="width: 80px; font-family: monospace;" maxlength="7">
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Couleur du bouton au survol', 'blazing-feedback' ); ?></th>
					<td>
						<div style="display: flex; align-items: center; gap: 10px;">
							<input type="color" name="wpvfh_button_color_hover" id="wpvfh_button_color_hover" value="<?php echo esc_attr( $button_color_hover ); ?>">
							<input type="text" value="<?php echo esc_attr( $button_color_hover ); ?>" class="wpvfh-color-hex-input" data-color-input="wpvfh_button_color_hover" style="width: 80px; font-family: monospace;" maxlength="7">
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Taille du bouton', 'blazing-feedback' ); ?></th>
					<td>
						<input type="range" name="wpvfh_button_size" id="wpvfh_button_size" min="40" max="80" value="<?php echo esc_attr( $button_size ); ?>" style="width: 150px; vertical-align: middle;">
						<span id="wpvfh_button_size_value" style="margin-left: 10px; font-weight: 500;"><?php echo esc_html( $button_size ); ?>px</span>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Style du bouton', 'blazing-feedback' ); ?></th>
					<td>
						<fieldset>
							<label style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px; cursor: pointer;">
								<input type="radio" name="wpvfh_button_style" value="detached" <?php checked( $button_style, 'detached' ); ?>>
								<span style="display: flex; align-items: center; gap: 8px;">
									<span style="width: 32px; height: 32px; background: #FE5100; border-radius: 50%; display: inline-block;"></span>
									<span>
										<strong><?php esc_html_e( 'Séparé', 'blazing-feedback' ); ?></strong><br>
										<small style="color: #666;"><?php esc_html_e( 'Bouton flottant avec marge', 'blazing-feedback' ); ?></small>
									</span>
								</span>
							</label>
							<label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
								<input type="radio" name="wpvfh_button_style" value="attached" <?php checked( $button_style, 'attached' ); ?>>
								<span style="display: flex; align-items: center; gap: 8px;">
									<span id="wpvfh-attached-style-icon" style="width: 32px; height: 32px; background: #FE5100; border-radius: <?php echo $is_corner ? '0 0 0 16px' : '16px 0 0 16px'; ?>; display: inline-block;"></span>
									<span>
										<strong><?php esc_html_e( 'Collé', 'blazing-feedback' ); ?></strong><br>
										<small style="color: #666;" id="wpvfh-attached-style-desc">
											<?php echo $is_corner ? esc_html__( 'Quart de cercle (position d\'angle)', 'blazing-feedback' ) : esc_html__( 'Demi-cercle (position centrale)', 'blazing-feedback' ); ?>
										</small>
									</span>
								</span>
							</label>
						</fieldset>
					</td>
				</tr>
			</table>

			<!-- Options pour bouton séparé -->
			<div id="wpvfh-detached-options" style="<?php echo 'attached' === $button_style ? 'display: none;' : ''; ?> margin-left: 20px; padding: 15px; background: #f9f9f9; border-radius: 4px; margin-top: 10px;">
				<h4 style="margin-top: 0;"><?php esc_html_e( 'Options du bouton séparé', 'blazing-feedback' ); ?></h4>
				<table class="form-table" style="margin: 0;">
					<tr>
						<th scope="row" style="padding: 10px 0;"><?php esc_html_e( 'Border radius', 'blazing-feedback' ); ?></th>
						<td style="padding: 10px 0;">
							<div style="display: flex; align-items: center; gap: 10px;">
								<input type="number" name="wpvfh_button_border_radius" id="wpvfh_button_border_radius" value="<?php echo esc_attr( $border_radius ); ?>" min="0" max="100" style="width: 70px;">
								<select name="wpvfh_button_border_radius_unit" id="wpvfh_button_border_radius_unit">
									<option value="percent" <?php selected( $border_radius_unit, 'percent' ); ?>>%</option>
									<option value="px" <?php selected( $border_radius_unit, 'px' ); ?>>px</option>
								</select>
								<input type="range" id="wpvfh_border_radius_slider" min="0" max="50" value="<?php echo esc_attr( $border_radius ); ?>" style="width: 100px;">
							</div>
						</td>
					</tr>
					<tr>
						<th scope="row" style="padding: 10px 0;"><?php esc_html_e( 'Marge', 'blazing-feedback' ); ?></th>
						<td style="padding: 10px 0;">
							<div style="display: flex; align-items: center; gap: 10px;">
								<input type="number" name="wpvfh_button_margin" id="wpvfh_button_margin" value="<?php echo esc_attr( $button_margin ); ?>" min="0" max="50" style="width: 70px;">
								<span>px</span>
								<input type="range" id="wpvfh_margin_slider" min="0" max="50" value="<?php echo esc_attr( $button_margin ); ?>" style="width: 100px;">
							</div>
						</td>
					</tr>
				</table>
			</div>

			<!-- Bordure et ombre -->
			<div style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-radius: 4px;">
				<h4 style="margin-top: 0;"><?php esc_html_e( 'Bordure et ombre', 'blazing-feedback' ); ?></h4>
				<table class="form-table" style="margin: 0;">
					<tr>
						<th scope="row" style="padding: 10px 0;"><?php esc_html_e( 'Bordure', 'blazing-feedback' ); ?></th>
						<td style="padding: 10px 0;">
							<div style="display: flex; align-items: center; gap: 10px;">
								<input type="number" name="wpvfh_button_border_width" id="wpvfh_button_border_width" value="<?php echo esc_attr( $button_border_width ); ?>" min="0" max="10" style="width: 60px;">
								<span>px</span>
								<input type="color" name="wpvfh_button_border_color" id="wpvfh_button_border_color" value="<?php echo esc_attr( $button_border_color ); ?>">
								<input type="text" value="<?php echo esc_attr( $button_border_color ); ?>" class="wpvfh-color-hex-input" data-color-input="wpvfh_button_border_color" style="width: 70px; font-family: monospace; font-size: 12px;" maxlength="7">
							</div>
						</td>
					</tr>
					<tr>
						<th scope="row" style="padding: 10px 0;"><?php esc_html_e( 'Ombre', 'blazing-feedback' ); ?></th>
						<td style="padding: 10px 0;">
							<div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
								<label style="display: flex; align-items: center; gap: 5px;">
									<?php esc_html_e( 'Flou:', 'blazing-feedback' ); ?>
									<input type="number" name="wpvfh_button_shadow_blur" id="wpvfh_button_shadow_blur" value="<?php echo esc_attr( $button_shadow_blur ); ?>" min="0" max="50" style="width: 60px;">
									<span>px</span>
								</label>
								<label style="display: flex; align-items: center; gap: 5px;">
									<?php esc_html_e( 'Opacité:', 'blazing-feedback' ); ?>
									<input type="range" name="wpvfh_button_shadow_opacity" id="wpvfh_button_shadow_opacity" value="<?php echo esc_attr( $button_shadow_opacity ); ?>" min="0" max="100" style="width: 80px;">
									<span id="wpvfh_shadow_opacity_value"><?php echo esc_html( $button_shadow_opacity ); ?>%</span>
								</label>
								<input type="color" name="wpvfh_button_shadow_color" id="wpvfh_button_shadow_color" value="<?php echo esc_attr( $button_shadow_color ); ?>">
							</div>
						</td>
					</tr>
				</table>
			</div>

			<!-- Couleurs du badge -->
			<div style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-radius: 4px;">
				<h4 style="margin-top: 0;"><?php esc_html_e( 'Badge compteur', 'blazing-feedback' ); ?></h4>
				<table class="form-table" style="margin: 0;">
					<tr>
						<th scope="row" style="padding: 10px 0;"><?php esc_html_e( 'Couleur de fond', 'blazing-feedback' ); ?></th>
						<td style="padding: 10px 0;">
							<div style="display: flex; align-items: center; gap: 10px;">
								<input type="color" name="wpvfh_badge_bg_color" id="wpvfh_badge_bg_color" value="<?php echo esc_attr( $badge_bg_color ); ?>">
								<input type="text" value="<?php echo esc_attr( $badge_bg_color ); ?>" class="wpvfh-color-hex-input" data-color-input="wpvfh_badge_bg_color" style="width: 70px; font-family: monospace; font-size: 12px;" maxlength="7">
							</div>
						</td>
					</tr>
					<tr>
						<th scope="row" style="padding: 10px 0;"><?php esc_html_e( 'Couleur du nombre', 'blazing-feedback' ); ?></th>
						<td style="padding: 10px 0;">
							<div style="display: flex; align-items: center; gap: 10px;">
								<input type="color" name="wpvfh_badge_text_color" id="wpvfh_badge_text_color" value="<?php echo esc_attr( $badge_text_color ); ?>">
								<input type="text" value="<?php echo esc_attr( $badge_text_color ); ?>" class="wpvfh-color-hex-input" data-color-input="wpvfh_badge_text_color" style="width: 70px; font-family: monospace; font-size: 12px;" maxlength="7">
							</div>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<?php
	}
}
