<?php
/**
 * File delegated to the plugin activation.
 *
 * @package    SimpleForm reCAPTCHA
 * @subpackage SimpleForm reCAPTCHA/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * The class instantiated during the plugin activation.
 */
class SimpleForm_ReCaptcha_Activator {

	/**
	 * Run default functionality during plugin activation.
	 *
	 * @since 1.0.0
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @param bool $network_wide Whether to enable the plugin for all sites in the network
	 *                           or just the current site. Multisite only. Default false.
	 *
	 * @return void
	 */
	public static function activate( $network_wide ) {

		if ( class_exists( 'SimpleForm' ) ) {

			if ( function_exists( 'is_multisite' ) && is_multisite() ) {

				if ( $network_wide ) {

					global $wpdb;
					$blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" ); // phpcs:ignore

					foreach ( $blog_ids as $blog_id ) {
						switch_to_blog( $blog_id );
						self::change_db();
						self::sform_settings();
						restore_current_blog();
					}
				} else {
					self::change_db();
					self::sform_settings();
				}
			} else {
				self::change_db();
				self::sform_settings();
			}
		}
	}

	/**
	 * Modifies the database tables.
	 *
	 * @since 1.2.0
	 *
	 * @return void
	 */
	public static function change_db() {

		$current_version   = SIMPLEFORM_RECAPTCHA_VERSION;
		$installed_version = strval( get_option( 'sform_recaptcha_version' ) );

		if ( $installed_version !== $current_version ) {
			update_option( 'sform_recaptcha_version', $current_version );
		}
	}

	/**
	 * Save initial settings.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function sform_settings() {

		// Detect the parent plugin activation.
		$main_settings   = (array) get_option( 'sform_settings', array() );
		$main_attributes = (array) get_option( 'sform_attributes', array() );

		$privacy_url = '<a href="https://policies.google.com/privacy" target="_blank">' . __( 'Privacy Policy', 'simpleform-recaptcha' ) . '</a>';
		$terms_url   = '<a href="https://policies.google.com/terms" target="_blank">' . __( 'Terms of Service', 'simpleform-recaptcha' ) . '</a>';
		/* translators: %1$s: Privacy Policy link, %2$s: Terms of Service link */
		$recaptcha_notice = sprintf( __( 'This site is protected by reCAPTCHA and the Google %1$s and %2$s apply', 'simpleform-recaptcha' ), $privacy_url, $terms_url );

		$new_settings = array(
			'recaptcha'            => false,
			'recaptcha_type'       => 'v2',
			'recaptcha_site_key'   => '',
			'recaptcha_secret_key' => '',
			'recaptcha_threshold'  => '0.5',
			'recaptcha_style'      => 'light',
			'recaptcha_badge'      => false,
			'recaptcha_notice'     => $recaptcha_notice,
			'unverified_recaptcha' => __( 'Please prove you are not a robot', 'simpleform-recaptcha' ),
			'expired_recaptcha'    => __( 'reCAPTCHA response expired, please answer again!', 'simpleform-recaptcha' ),
			'invalid_recaptcha'    => __( 'Robot verification failed, please try again', 'simpleform-recaptcha' ),
		);

		$new_attributes = array( 'captcha_type' => 'math' );

		if ( $main_settings ) {

			$settings = array_merge( $main_settings, $new_settings );
			update_option( 'sform_settings', $settings );

		}

		if ( $main_attributes ) {

			$attributes = array_merge( $main_attributes, $new_attributes );
			update_option( 'sform_attributes', $attributes );

		}

		// Check if other forms have been activated.
		global $wpdb;
		$shortcodes_table = $wpdb->prefix . 'sform_shortcodes';

		if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $shortcodes_table ) ) === $shortcodes_table ) { // phpcs:ignore			

			$forms = $wpdb->get_col( "SELECT id FROM {$wpdb->prefix}sform_shortcodes WHERE id != '1'" ); // phpcs:ignore

			foreach ( $forms as $form ) {

				$form_settings   = (array) get_option( 'sform_' . $form . '_settings', array() );
				$form_attributes = (array) get_option( 'sform_' . $form . '_attributes', array() );

				if ( $form_settings ) {

					$settings = array_merge( $form_settings, $new_settings );
					update_option( 'sform_' . $form . '_settings', $settings );

				}

				if ( $form_attributes ) {

					$attributes = array_merge( $form_attributes, $new_attributes );
					update_option( 'sform_' . $form . '_attributes', $attributes );

				}
			}
		}
	}

	/**
	 * Create a table whenever a new blog is created in a WordPress Multisite installation.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Site $new_site New site object.
	 *
	 * @return void
	 */
	public static function on_create_blog( $new_site ) {

		if ( is_plugin_active_for_network( 'simpleform-recaptcha/simpleform-recaptcha.php' ) ) {

			switch_to_blog( (int) $new_site->blog_id );
			self::change_db();
			self::sform_settings();
			restore_current_blog();

		}
	}
}
