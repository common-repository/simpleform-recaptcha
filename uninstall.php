<?php
/**
 * File delegated to the uninstalling the plugin.
 *
 * @package SimpleForm reCAPTCHA
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// Detect the simpleform plugin installation.
$plugin_file = defined( 'WP_PLUGIN_DIR' ) ? WP_PLUGIN_DIR . '/simpleform/simpleform.php' : '';

if ( file_exists( $plugin_file ) ) {

	global $wpdb;

	if ( ! is_multisite() ) {

		$settings         = (array) get_option( 'sform_settings', array() );
		$attributes       = (array) get_option( 'sform_attributes', array() );
		$shortcodes_table = $wpdb->prefix . 'sform_shortcodes';

		if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $shortcodes_table ) ) === $shortcodes_table ) { // phpcs:ignore			
			$forms = $wpdb->get_col( "SELECT id FROM {$wpdb->prefix}sform_shortcodes WHERE id != '1'" ); // phpcs:ignore
		} else {
			$forms = array();
		}

		// Detect the parent plugin activation.
		if ( $settings ) {

			$addon_settings = array(
				'recaptcha'            => $settings['recaptcha'],
				'recaptcha_type'       => $settings['recaptcha_type'],
				'recaptcha_site_key'   => $settings['recaptcha_site_key'],
				'recaptcha_secret_key' => $settings['recaptcha_secret_key'],
				'recaptcha_threshold'  => $settings['recaptcha_threshold'],
				'recaptcha_style'      => $settings['recaptcha_style'],
				'recaptcha_badge'      => $settings['recaptcha_badge'],
				'unverified_recaptcha' => $settings['unverified_recaptcha'],
				'expired_recaptcha'    => $settings['expired_recaptcha'],
				'invalid_recaptcha'    => $settings['invalid_recaptcha'],
			);

			$new_settings = array_diff_key( $settings, $addon_settings );
			update_option( 'sform_settings', $new_settings );

			foreach ( $forms as $form ) {

				$form_settings = (array) get_option( 'sform_' . $form . '_settings', array() );

				if ( $form_settings ) {

					$addon_settings = array(
						'recaptcha'            => $form_settings['recaptcha'],
						'recaptcha_type'       => $form_settings['recaptcha_type'],
						'recaptcha_site_key'   => $form_settings['recaptcha_site_key'],
						'recaptcha_secret_key' => $form_settings['recaptcha_secret_key'],
						'recaptcha_threshold'  => $form_settings['recaptcha_threshold'],
						'recaptcha_style'      => $form_settings['recaptcha_style'],
						'recaptcha_badge'      => $form_settings['recaptcha_badge'],
						'unverified_recaptcha' => $form_settings['unverified_recaptcha'],
						'expired_recaptcha'    => $form_settings['expired_recaptcha'],
						'invalid_recaptcha'    => $form_settings['invalid_recaptcha'],
					);

					$new_form_settings = array_diff_key( $form_settings, $addon_settings );
					update_option( 'sform_' . $form . '_settings', $new_form_settings );

				}
			}
		}

		if ( $attributes ) {

			$addon_attributes = array(
				'captcha_type' => $attributes['captcha_type'],
			);

			$new_attributes = array_diff_key( $attributes, $addon_attributes );
			update_option( 'sform_attributes', $new_attributes );

			foreach ( $forms as $form ) {

				$form_attributes = (array) get_option( 'sform_' . $form . '_attributes', array() );

				if ( $form_attributes ) {

					$addon_attributes = array(
						'captcha_type' => $form_attributes['captcha_type'],
					);

					$new_form_attributes = array_diff_key( $form_attributes, $addon_attributes );
					update_option( 'sform_' . $form . '_attributes', $new_form_attributes );

				}
			}
		}
	} else {

		$blog_ids         = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" ); // phpcs:ignore
		$original_blog_id = get_current_blog_id();

		foreach ( $blog_ids as $blogid ) {

			switch_to_blog( $blogid );
			$settings         = (array) get_option( 'sform_settings', array() );
			$attributes       = (array) get_option( 'sform_attributes', array() );
			$shortcodes_table = $wpdb->prefix . 'sform_shortcodes';

			if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $shortcodes_table ) ) === $shortcodes_table ) { // phpcs:ignore	
				$forms      = $wpdb->get_col( "SELECT id FROM {$wpdb->prefix}sform_shortcodes WHERE id != '1'" ); // phpcs:ignore
			} else {
				$forms = array();
			}

			// Detect the parent plugin activation.
			if ( $settings ) {

				$addon_settings = array(
					'recaptcha'            => $settings['recaptcha'],
					'recaptcha_type'       => $settings['recaptcha_type'],
					'recaptcha_site_key'   => $settings['recaptcha_site_key'],
					'recaptcha_secret_key' => $settings['recaptcha_secret_key'],
					'recaptcha_threshold'  => $settings['recaptcha_threshold'],
					'recaptcha_style'      => $settings['recaptcha_style'],
					'recaptcha_badge'      => $settings['recaptcha_badge'],
					'unverified_recaptcha' => $settings['unverified_recaptcha'],
					'expired_recaptcha'    => $settings['expired_recaptcha'],
					'invalid_recaptcha'    => $settings['invalid_recaptcha'],
				);

				$new_settings = array_diff_key( $settings, $addon_settings );
				update_option( 'sform_settings', $new_settings );

				foreach ( $forms as $form ) {

					$form_settings = (array) get_option( 'sform_' . $form . '_settings', array() );

					if ( $form_settings ) {

						$addon_settings = array(
							'recaptcha'            => $form_settings['recaptcha'],
							'recaptcha_type'       => $form_settings['recaptcha_type'],
							'recaptcha_site_key'   => $form_settings['recaptcha_site_key'],
							'recaptcha_secret_key' => $form_settings['recaptcha_secret_key'],
							'recaptcha_threshold'  => $form_settings['recaptcha_threshold'],
							'recaptcha_style'      => $form_settings['recaptcha_style'],
							'recaptcha_badge'      => $form_settings['recaptcha_badge'],
							'unverified_recaptcha' => $form_settings['unverified_recaptcha'],
							'expired_recaptcha'    => $form_settings['expired_recaptcha'],
							'invalid_recaptcha'    => $form_settings['invalid_recaptcha'],
						);

						$new_form_settings = array_diff_key( $form_settings, $addon_settings );
						update_option( 'sform_' . $form . '_settings', $new_form_settings );

					}
				}
			}

			if ( $attributes ) {

				$addon_attributes = array(
					'captcha_type' => $attributes['captcha_type'],
				);

				$new_attributes = array_diff_key( $attributes, $addon_attributes );
				update_option( 'sform_attributes', $new_attributes );

				foreach ( $forms as $form ) {

					$form_attributes = (array) get_option( 'sform_' . $form . '_attributes', array() );

					if ( $form_attributes ) {

						$addon_attributes = array(
							'captcha_type' => $form_attributes['captcha_type'],
						);

						$new_form_attributes = array_diff_key( $form_attributes, $addon_attributes );
						update_option( 'sform_' . $form . '_attributes', $new_form_attributes );

					}
				}
			}
		}

		switch_to_blog( $original_blog_id );

	}
} else {

	global $wpdb;
	$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'sform_submissions' ); // phpcs:ignore
	$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'sform_shortcodes' ); // phpcs:ignore
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'sform\_%'" ); // phpcs:ignore
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'sform\-%'" ); // phpcs:ignore
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%\_sform\_%'" ); // phpcs:ignore

}
