<?php
/**
 * File delegated to deactivate the plugin.
 *
 * @package    SimpleForm reCAPTCHA
 * @subpackage SimpleForm reCAPTCHA/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Defines the class instantiated during the plugin's deactivation.
 */
class SimpleForm_ReCaptcha_Deactivator {

	/**
	 * Run during plugin deactivation.
	 *
	 * @since 1.0.0
	 * @since 1.2.0 Refactoring of code.
	 *
	 * @return void
	 */
	public static function deactivate() {

		// Detect the parent plugin activation.
		$settings   = (array) get_option( 'sform_settings', array() );
		$attributes = (array) get_option( 'sform_attributes', array() );

		if ( $settings ) {

			$settings['recaptcha'] = false;
			update_option( 'sform_settings', $settings );

		}

		if ( $attributes ) {

			$attributes['captcha_type'] = 'math';
			update_option( 'sform_attributes', $attributes );

		}

		global $wpdb;
		$shortcodes_table = $wpdb->prefix . 'sform_shortcodes';

		if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $shortcodes_table ) ) === $shortcodes_table ) { // phpcs:ignore			

			// Check if other forms have been activated.
			$forms = $wpdb->get_col( "SELECT id FROM {$wpdb->prefix}sform_shortcodes WHERE id != '1'" ); // phpcs:ignore

			foreach ( $forms as $form ) {

				$form_settings   = (array) get_option( 'sform_' . $form . '_settings', array() );
				$form_attributes = (array) get_option( 'sform_' . $form . '_attributes', array() );

				if ( $form_settings ) {

					$form_settings['recaptcha'] = false;
					update_option( 'sform_' . $form . '_settings', $form_settings );

				}

				if ( $form_attributes ) {

					$form_attributes['captcha_type'] = 'math';
					update_option( 'sform_' . $form . '_attributes', $form_attributes );

				}
			}
		}
	}
}
