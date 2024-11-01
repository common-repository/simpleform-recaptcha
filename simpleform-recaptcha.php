<?php
/**
 *
 * Plugin Name:       SimpleForm reCAPTCHA
 * Description:       Stop Bot Spam! This SimpleForm addon allows you to require users to confirm that they are not a robot by completing the Google reCAPTCHA.
 * Version:           1.2.0
 * Requires at least: 5.9
 * Requires PHP:      7.4
 * Author:            SimpleForm Team
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       simpleform-recaptcha
 * Requires Plugins:  simpleform
 *
 * @package           SimpleForm reCAPTCHA
 */

defined( 'WPINC' ) || exit;

/**
 * Plugin constants.
 *
 * @since 1.0.0
 */

define( 'SIMPLEFORM_RECAPTCHA_NAME', 'SimpleForm reCAPTCHA' );
define( 'SIMPLEFORM_RECAPTCHA_VERSION', '1.2.0' );
define( 'SIMPLEFORM_RECAPTCHA_BASENAME', plugin_basename( __FILE__ ) );
define( 'SIMPLEFORM_RECAPTCHA_PATH', plugin_dir_path( __FILE__ ) );
if ( ! defined( 'SIMPLEFORM_VERSION_REQUIRED' ) ) {
	define( 'SIMPLEFORM_VERSION_REQUIRED', '2.2.0' );
}

/**
 * The code that runs during plugin activation.
 *
 * @since 1.0.0
 *
 * @param bool $network_wide Whether to enable the plugin for all sites in the network
 *                           or just the current site. Multisite only. Default false.
 *
 * @return void
 */
function activate_simpleform_recaptcha( $network_wide ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-simpleform-recaptcha-activator.php';
	SimpleForm_ReCaptcha_Activator::activate( $network_wide );
}

/**
 * Edit settings when a new site into a network is created.
 *
 * @since 1.0.0
 *
 * @param WP_Site $new_site New site object.
 *
 * @return void
 */
function simpleform_recaptcha_network( $new_site ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-simpleform-recaptcha-activator.php';
	SimpleForm_ReCaptcha_Activator::on_create_blog( $new_site );
}

add_action( 'wp_insert_site', 'simpleform_recaptcha_network' );

/**
 * The code that runs during plugin deactivation.
 *
 * @since 1.0.0
 *
 * @return void
 */
function deactivate_simpleform_recaptcha() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-simpleform-recaptcha-deactivator.php';
	SimpleForm_ReCaptcha_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_simpleform_recaptcha' );
register_deactivation_hook( __FILE__, 'deactivate_simpleform_recaptcha' );

/**
 * The core plugin class.
 *
 * @since 1.0.0
 */

require plugin_dir_path( __FILE__ ) . '/includes/class-simpleform-recaptcha.php';

/**
 * Begins execution of the plugin.
 *
 * @since 1.0.0
 *
 * @return void
 */
function run_simpleform_recaptcha() {

	$plugin = new SimpleForm_ReCaptcha();
	$plugin->run();
}

run_simpleform_recaptcha();
