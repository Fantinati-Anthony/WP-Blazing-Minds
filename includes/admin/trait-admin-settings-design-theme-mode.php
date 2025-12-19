<?php
/**
 * Trait pour la section Mode du thème (Design)
 *
 * @package Blazing_Feedback
 * @since 1.9.0
 */

// Empêcher l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trait de gestion de la section Mode du thème
 *
 * @since 1.9.0
 */
trait WPVFH_Admin_Settings_Design_Theme_Mode {

	/**
	 * Rendre la section Mode du thème
	 *
	 * @since 1.9.0
	 * @param array $options Options actuelles.
	 * @return void
	 */
	public static function render_design_theme_mode_section( $options ) {
		$theme_mode = $options['theme_mode'];
		$light_icon_type = $options['light_icon_type'];
		$light_icon_emoji = $options['light_icon_emoji'];
		$light_icon_url = $options['light_icon_url'];
		$dark_icon_type = $options['dark_icon_type'];
		$dark_icon_emoji = $options['dark_icon_emoji'];
		$dark_icon_url = $options['dark_icon_url'];
		$default_light_icon = $options['default_light_icon'];
		$default_dark_icon = $options['default_dark_icon'];
		$panel_logo_light_url = $options['panel_logo_light_url'];
		$panel_logo_dark_url = $options['panel_logo_dark_url'];
		?>

		<!-- Mode du thème (pleine largeur) -->
		<div class="wpvfh-settings-section" style="margin-bottom: 30px;">
			<h2><?php esc_html_e( 'Mode d\'affichage', 'blazing-feedback' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Choisissez le mode d\'affichage du widget. Le mode Système s\'adapte automatiquement aux préférences de l\'utilisateur.', 'blazing-feedback' ); ?></p>

			<div class="wpvfh-theme-mode-selector" style="display: flex; gap: 15px; margin: 20px 0;">
				<label class="wpvfh-mode-option <?php echo 'system' === $theme_mode ? 'active' : ''; ?>" style="flex: 1; padding: 15px; border: 2px solid <?php echo 'system' === $theme_mode ? '#FE5100' : '#ddd'; ?>; border-radius: 8px; cursor: pointer; text-align: center; transition: all 0.2s;">
					<input type="radio" name="wpvfh_theme_mode" value="system" <?php checked( $theme_mode, 'system' ); ?> style="display: none;">
					<span style="font-size: 24px; display: block; margin-bottom: 5px;">🔄</span>
					<strong><?php esc_html_e( 'Système', 'blazing-feedback' ); ?></strong>
					<small style="display: block; color: #666; margin-top: 5px;"><?php esc_html_e( 'Auto dark/light', 'blazing-feedback' ); ?></small>
				</label>
				<label class="wpvfh-mode-option <?php echo 'light' === $theme_mode ? 'active' : ''; ?>" style="flex: 1; padding: 15px; border: 2px solid <?php echo 'light' === $theme_mode ? '#FE5100' : '#ddd'; ?>; border-radius: 8px; cursor: pointer; text-align: center; background: #fff; transition: all 0.2s;">
					<input type="radio" name="wpvfh_theme_mode" value="light" <?php checked( $theme_mode, 'light' ); ?> style="display: none;">
					<span style="font-size: 24px; display: block; margin-bottom: 5px;">☀️</span>
					<strong><?php esc_html_e( 'Clair', 'blazing-feedback' ); ?></strong>
					<small style="display: block; color: #666; margin-top: 5px;"><?php esc_html_e( 'Mode clair', 'blazing-feedback' ); ?></small>
				</label>
				<label class="wpvfh-mode-option <?php echo 'dark' === $theme_mode ? 'active' : ''; ?>" style="flex: 1; padding: 15px; border: 2px solid <?php echo 'dark' === $theme_mode ? '#FE5100' : '#ddd'; ?>; border-radius: 8px; cursor: pointer; text-align: center; background: #263e4b; color: #fff; transition: all 0.2s;">
					<input type="radio" name="wpvfh_theme_mode" value="dark" <?php checked( $theme_mode, 'dark' ); ?> style="display: none;">
					<span style="font-size: 24px; display: block; margin-bottom: 5px;">🌙</span>
					<strong><?php esc_html_e( 'Sombre', 'blazing-feedback' ); ?></strong>
					<small style="display: block; color: #b0bcc4; margin-top: 5px;"><?php esc_html_e( 'Mode sombre', 'blazing-feedback' ); ?></small>
				</label>
			</div>

			<!-- Icône ou logo selon le mode -->
			<div class="wpvfh-icon-settings" style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-radius: 8px;">
				<h4 style="margin-top: 0;"><?php esc_html_e( 'Icône ou logo selon le mode', 'blazing-feedback' ); ?></h4>

				<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
					<!-- Mode clair -->
					<div style="padding: 15px; background: #fff; border-radius: 6px; border: 1px solid #e0e4e8;">
						<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
							<span style="font-size: 18px;">☀️</span>
							<strong><?php esc_html_e( 'Mode clair', 'blazing-feedback' ); ?></strong>
							<small style="color: #666;"><?php esc_html_e( '(fond #ffffff)', 'blazing-feedback' ); ?></small>
						</div>

						<!-- Icône du bouton -->
						<div style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #e0e4e8;">
							<label style="display: block; font-weight: 500; margin-bottom: 8px;"><?php esc_html_e( 'Icône du bouton', 'blazing-feedback' ); ?></label>
							<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
								<div id="wpvfh-light-preview-box" style="width: 44px; height: 44px; background: #FE5100; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 22px; color: #fff;">
									<?php if ( 'emoji' === $light_icon_type ) : ?>
										<span id="wpvfh-light-preview-content"><?php echo esc_html( $light_icon_emoji ); ?></span>
									<?php else : ?>
										<img src="<?php echo esc_url( $light_icon_url ? $light_icon_url : $default_light_icon ); ?>" alt="" style="max-width: 26px; max-height: 26px; filter: brightness(0) invert(1);" id="wpvfh-light-preview-content">
									<?php endif; ?>
								</div>
								<div style="margin-bottom: 0;">
									<label style="display: inline-flex; align-items: center; gap: 5px; margin-right: 10px; cursor: pointer;">
										<input type="radio" name="wpvfh_light_icon_type" value="emoji" <?php checked( $light_icon_type, 'emoji' ); ?>>
										<?php esc_html_e( 'Emoji', 'blazing-feedback' ); ?>
									</label>
									<label style="display: inline-flex; align-items: center; gap: 5px; cursor: pointer;">
										<input type="radio" name="wpvfh_light_icon_type" value="image" <?php checked( $light_icon_type, 'image' ); ?>>
										<?php esc_html_e( 'Image', 'blazing-feedback' ); ?>
									</label>
								</div>
							</div>
							<div id="wpvfh-light-emoji-input" style="<?php echo 'image' === $light_icon_type ? 'display: none;' : ''; ?>">
								<input type="text" name="wpvfh_light_icon_emoji" id="wpvfh_light_icon_emoji" value="<?php echo esc_attr( $light_icon_emoji ); ?>" style="width: 60px; font-size: 20px; text-align: center;" maxlength="4">
							</div>
							<div id="wpvfh-light-image-input" style="<?php echo 'emoji' === $light_icon_type ? 'display: none;' : ''; ?> display: flex; gap: 8px;">
								<input type="text" name="wpvfh_light_icon_url" id="wpvfh_light_icon_url" value="<?php echo esc_attr( $light_icon_url ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'URL ou vide pour défaut', 'blazing-feedback' ); ?>" style="flex: 1;">
								<button type="button" class="button wpvfh-select-icon" data-target="wpvfh_light_icon_url" data-mode="light"><?php esc_html_e( 'Bibliothèque', 'blazing-feedback' ); ?></button>
							</div>
						</div>

						<!-- Logo du panneau -->
						<div>
							<label style="display: block; font-weight: 500; margin-bottom: 8px;"><?php esc_html_e( 'Logo du panneau', 'blazing-feedback' ); ?></label>
							<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
								<div style="height: 36px; padding: 5px 10px; background: #f0f0f1; border-radius: 4px; display: flex; align-items: center;">
									<img src="<?php echo esc_url( $panel_logo_light_url ? $panel_logo_light_url : $default_light_icon ); ?>" alt="" style="max-height: 26px;" id="wpvfh-panel-logo-light-preview">
								</div>
							</div>
							<div style="display: flex; gap: 8px;">
								<input type="text" name="wpvfh_panel_logo_light_url" id="wpvfh_panel_logo_light_url" value="<?php echo esc_attr( $panel_logo_light_url ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'URL ou vide pour light-mode-feedback.png', 'blazing-feedback' ); ?>" style="flex: 1;">
								<button type="button" class="button wpvfh-select-panel-logo" data-target="wpvfh_panel_logo_light_url" data-preview="wpvfh-panel-logo-light-preview"><?php esc_html_e( 'Bibliothèque', 'blazing-feedback' ); ?></button>
							</div>
						</div>
					</div>

					<!-- Mode sombre -->
					<div style="padding: 15px; background: #263e4b; border-radius: 6px; color: #fff;">
						<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
							<span style="font-size: 18px;">🌙</span>
							<strong><?php esc_html_e( 'Mode sombre', 'blazing-feedback' ); ?></strong>
							<small style="color: #b0bcc4;"><?php esc_html_e( '(fond #263E4B)', 'blazing-feedback' ); ?></small>
						</div>

						<!-- Icône du bouton -->
						<div style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #3d5564;">
							<label style="display: block; font-weight: 500; margin-bottom: 8px;"><?php esc_html_e( 'Icône du bouton', 'blazing-feedback' ); ?></label>
							<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
								<div id="wpvfh-dark-preview-box" style="width: 44px; height: 44px; background: #FE5100; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 22px; color: #fff;">
									<?php if ( 'emoji' === $dark_icon_type ) : ?>
										<span id="wpvfh-dark-preview-content"><?php echo esc_html( $dark_icon_emoji ); ?></span>
									<?php else : ?>
										<img src="<?php echo esc_url( $dark_icon_url ? $dark_icon_url : $default_dark_icon ); ?>" alt="" style="max-width: 26px; max-height: 26px; filter: brightness(0) invert(1);" id="wpvfh-dark-preview-content">
									<?php endif; ?>
								</div>
								<div style="margin-bottom: 0;">
									<label style="display: inline-flex; align-items: center; gap: 5px; margin-right: 10px; cursor: pointer; color: #fff;">
										<input type="radio" name="wpvfh_dark_icon_type" value="emoji" <?php checked( $dark_icon_type, 'emoji' ); ?>>
										<?php esc_html_e( 'Emoji', 'blazing-feedback' ); ?>
									</label>
									<label style="display: inline-flex; align-items: center; gap: 5px; cursor: pointer; color: #fff;">
										<input type="radio" name="wpvfh_dark_icon_type" value="image" <?php checked( $dark_icon_type, 'image' ); ?>>
										<?php esc_html_e( 'Image', 'blazing-feedback' ); ?>
									</label>
								</div>
							</div>
							<div id="wpvfh-dark-emoji-input" style="<?php echo 'image' === $dark_icon_type ? 'display: none;' : ''; ?>">
								<input type="text" name="wpvfh_dark_icon_emoji" id="wpvfh_dark_icon_emoji" value="<?php echo esc_attr( $dark_icon_emoji ); ?>" style="width: 60px; font-size: 20px; text-align: center;" maxlength="4">
							</div>
							<div id="wpvfh-dark-image-input" style="<?php echo 'emoji' === $dark_icon_type ? 'display: none;' : ''; ?> display: flex; gap: 8px;">
								<input type="text" name="wpvfh_dark_icon_url" id="wpvfh_dark_icon_url" value="<?php echo esc_attr( $dark_icon_url ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'URL ou vide pour défaut', 'blazing-feedback' ); ?>" style="flex: 1; background: #334a5a; border-color: #3d5564; color: #fff;">
								<button type="button" class="button wpvfh-select-icon" data-target="wpvfh_dark_icon_url" data-mode="dark"><?php esc_html_e( 'Bibliothèque', 'blazing-feedback' ); ?></button>
							</div>
						</div>

						<!-- Logo du panneau -->
						<div>
							<label style="display: block; font-weight: 500; margin-bottom: 8px;"><?php esc_html_e( 'Logo du panneau', 'blazing-feedback' ); ?></label>
							<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
								<div style="height: 36px; padding: 5px 10px; background: #334a5a; border-radius: 4px; display: flex; align-items: center;">
									<img src="<?php echo esc_url( $panel_logo_dark_url ? $panel_logo_dark_url : $default_dark_icon ); ?>" alt="" style="max-height: 26px;" id="wpvfh-panel-logo-dark-preview">
								</div>
							</div>
							<div style="display: flex; gap: 8px;">
								<input type="text" name="wpvfh_panel_logo_dark_url" id="wpvfh_panel_logo_dark_url" value="<?php echo esc_attr( $panel_logo_dark_url ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'URL ou vide pour dark-mode-feedback.png', 'blazing-feedback' ); ?>" style="flex: 1; background: #334a5a; border-color: #3d5564; color: #fff;">
								<button type="button" class="button wpvfh-select-panel-logo" data-target="wpvfh_panel_logo_dark_url" data-preview="wpvfh-panel-logo-dark-preview"><?php esc_html_e( 'Bibliothèque', 'blazing-feedback' ); ?></button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
