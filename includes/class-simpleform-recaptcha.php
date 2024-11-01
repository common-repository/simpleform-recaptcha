<?php
/**
 * File that takes care of implementing all the necessary functions to manage the plugin.
 *
 * @package    SimpleForm reCAPTCHA
 * @subpackage SimpleForm reCAPTCHA/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * The class used to define admin-area hooks and public-facing site hooks.
 */
class SimpleForm_ReCaptcha {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    SimpleForm_ReCaptcha_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The plugin's unique identifier.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The plugin's current version.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    string    $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * The error message.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    object    $error Array containing the list of errors.
	 */
	protected $error = null;

	/**
	 * Define the plugin's core functionality.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'simpleform-recaptcha';
		$this->version     = SIMPLEFORM_RECAPTCHA_VERSION;

		$this->requirements_matching();
		$this->load_dependencies();
		$this->plugin_management_hooks();
		$this->admin_hooks();
		$this->public_hooks();
	}

	/**
	 * Define the controls for the plugin compatibility.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 *
	 * @return void
	 */
	private function requirements_matching() {

		global $pagenow;

		$admin_pages = array(
			'plugins.php',
			'update-core.php',
		);

		if ( ! $this->is_core_active() ) {

			if ( in_array( $pagenow, $admin_pages, true ) ) {

				$addon = '<b>' . SIMPLEFORM_RECAPTCHA_NAME . '</b>';

				if ( ! file_exists( WP_PLUGIN_DIR . '/simpleform/simpleform.php' ) ) {

					$core_plugin = '<a href="' . esc_url( 'https://wordpress.org/plugins/simpleform/' ) . '" target="_blank" style="text-decoration: none;">' . __( 'SimpleForm', 'simpleform-recaptcha' ) . '</a>';
					$url         = '<a href="' . network_admin_url( 'plugin-install.php?tab=search&type=tag&s=simpleform-addon' ) . '" style="text-decoration: none;">' . __( 'WordPress Plugin Directory', 'simpleform-recaptcha' ) . '</a>';
					/* translators: %1$s: SimpleForm reCAPTCHA addon name, %2$s: WordPress.org core plugin link, %3$s: URL to admin page that let browsing the WordPress Plugin Directory */
					$message = sprintf( __( 'In order to use the %1$s plugin you need to install and activate %2$s. Search it in the %3$s.', 'simpleform-recaptcha' ), $addon, $core_plugin, $url );

				} else {

					$core_plugin = '<b>' . __( 'SimpleForm', 'simpleform-recaptcha' ) . '</b>';
					/* translators: %1$s: SimpleForm reCAPTCHA addon name, %2$s: SimpleForm core plugin name */
					$message = sprintf( __( 'In order to use the %1$s plugin you need to activate the %2$s plugin.', 'simpleform-recaptcha' ), $addon, $core_plugin );

				}

				$this->add_error( $message );

			}
		} elseif ( ! $this->is_version_compatible() ) {

			$plugin_pages = array(
				'sform-entries',
				'sform-entry',
				'sform-forms',
				'sform-form',
				'sform-new',
				'sform-editor',
				'sform-settings',
				'sform-support',
			);

			// phpcs:ignore
			if ( ( isset( $_GET['page'] ) && in_array( $_GET['page'], $plugin_pages, true ) ) || in_array( $pagenow, $admin_pages, true ) ) {

				$addon   = SIMPLEFORM_RECAPTCHA_NAME;
				$version = '<b>' . SIMPLEFORM_VERSION_REQUIRED . '</b>';

				/* translators: %1$s: SimpleForm reCAPTCHA addon name, %2$s: version number */
				$this->add_error( sprintf( __( '%1$s requires SimpleForm version %2$s or greater installed. Please update to make it work properly!', 'simpleform-recaptcha' ), $addon, $version ) );

			}
		}

		if ( is_a( $this->error, 'WP_Error' ) ) {

			add_action( 'admin_notices', array( $this, 'display_error' ), 10, 0 );

			set_transient( 'sform_version_alert', 'incompatible', 2 );

		}
	}

	/**
	 * Load the required dependencies for this plugin.
	 * Create an instance of the loader which will be used to register the hooks with WordPress.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 *
	 * @return void
	 */
	private function load_dependencies() {

		// The class responsible for orchestrating the actions and filters of the core plugin.
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-simpleform-recaptcha-loader.php';
		// The class responsible for manage the plugin.
		require_once plugin_dir_path( __DIR__ ) . 'admin/includes/class-simpleform-recaptcha-management.php';
		// The class responsible for defining all actions that occur in the admin area.
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-simpleform-recaptcha-admin.php';
		// The class responsible for defining utilities.
		require_once plugin_dir_path( __DIR__ ) . 'admin/includes/class-simpleform-recaptcha-util.php';
		// The class responsible for defining all actions that occur in the public area.
		require_once plugin_dir_path( __DIR__ ) . 'public/class-simpleform-recaptcha-public.php';

		$this->loader = new SimpleForm_ReCaptcha_Loader();
	}

	/**
	 * Register all hooks relating to the management of plugin.
	 *
	 * @since 1.2.0
	 *
	 * @access private
	 *
	 * @return void
	 */
	private function plugin_management_hooks() {

		$plugin_management = new SimpleForm_ReCaptcha_Management();

		// Check for core plugin listed in the active plugins.
		if ( $this->is_core_active() ) {

			// Check data stored in the database.
			$this->loader->add_action( 'plugins_loaded', $plugin_management, 'version_checking' ); // stored_data_checking
			// Add action links in the plugin meta row.
			$this->loader->add_filter( 'plugin_action_links', $plugin_management, 'plugin_links', 10, 2 );
			// Add support links in the plugin meta row.
			$this->loader->add_filter( 'plugin_row_meta', $plugin_management, 'support_link', 10, 2 );
			// Admin footer text.
			$this->loader->add_action( 'admin_footer_text', $plugin_management, 'admin_footer', 1, 2 );
			// Add an update notice for SimpleForm.
			$this->loader->add_action( 'in_plugin_update_message-simpleform-akismet/simpleform-akismet.php', $plugin_management, 'upgrade_notification', 10, 2 );

		} else {

			// Add message in the plugin meta row if SimpleForm plugin is missing.
			$this->loader->add_filter( 'plugin_row_meta', $plugin_management, 'plugin_meta', 10, 2 );
			// Add an update notice for SimpleForm.
			$this->loader->add_action( 'in_plugin_update_message-simpleform-recaptcha/simpleform-recaptcha.php', $plugin_management, 'upgrade_notification', 10, 2 );

		}
	}

	/**
	 * Register all hooks related to the admin area functionality of the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 *
	 * @return void
	 */
	private function admin_hooks() {

		$plugin_admin = new SimpleForm_ReCaptcha_Admin( $this->get_plugin_name(), $this->get_version() );

		// Check if parent plugin is active and updated.
		if ( $this->is_core_active() ) {

			// Register the scripts for the admin area.
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
			// Add the new fields in the settings page.
			$this->loader->add_filter( 'recaptcha_settings_fields', $plugin_admin, 'settings_fields', 10, 2 );
			// Add reCAPTCHA error message fields in the settings page.
			$this->loader->add_filter( 'recaptcha_validation_messages', $plugin_admin, 'validation_messages', 10, 2 );
			// Validate the new fields in the settings page.
			$this->loader->add_filter( 'recaptcha_settings_validation', $plugin_admin, 'settings_validation' );
			// Add the new settings values in the settings options array.
			$this->loader->add_filter( 'recaptcha_settings_storing', $plugin_admin, 'settings_storing' );
			// Add the new fields in the editor page.
			$this->loader->add_filter( 'recaptcha_editor_fields', $plugin_admin, 'editor_fields', 10, 2 );
			// Set the captcha label class value.
			$this->loader->add_filter( 'hide_captcha_label', $plugin_admin, 'captcha_label_class', 10, 2 );
			// Add the new editor values in the attributes options array.
			$this->loader->add_filter( 'recaptcha_attributes_storing', $plugin_admin, 'attributes_storing' );

		}
	}

	/**
	 * Register all hooks related to the admin area functionality of the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 *
	 * @return void
	 */
	private function public_hooks() {

		$plugin_public = new SimpleForm_ReCaptcha_Public();

		// Check if parent plugin is active and updated.
		if ( $this->is_core_active() ) {

			// Add reCAPTCHA field in the contact form.
			$this->loader->add_filter( 'recaptcha_usage', $plugin_public, 'recaptcha_field', 10, 3 );
			// Validate submitted data by invoking the reCAPTCHA challenge.
			$this->loader->add_filter( 'recaptcha_spam_detection', $plugin_public, 'invoke_challenge', 10, 4 );
			// Display an error when reCAPTCHA challenge failed and Ajax is not enabled.
			$this->loader->add_filter( 'recaptcha_error_detection', $plugin_public, 'error_detection', 10, 2 );

		}
	}

	/**
	 * Check if the core plugin is listed in the active plugins in the WordPress database.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True, if the core plugin is active. False otherwise.
	 */
	protected function is_core_active() {

		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		if ( is_plugin_active_for_network( 'simpleform/simpleform.php' ) ) {
			return true;
		} else {
			$activation_check = in_array( 'simpleform/simpleform.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ? true : false;
			return $activation_check;
		}
	}

	/**
	 * Check if the parent plugin is compatible with this addon.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True, if the core plugin is compatible. False otherwise.
	 */
	protected function is_version_compatible() {

		if ( defined( 'WP_PLUGIN_DIR' ) ) {

			$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/simpleform/simpleform.php' );

			if ( version_compare( $plugin_data['Version'], SIMPLEFORM_VERSION_REQUIRED, '<' ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Add a new error to the WP_Error object and create the object if it doesn't exist yet.
	 *
	 * @param string $message The error message.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function add_error( $message ) {

		if ( ! is_object( $this->error ) || ! is_a( $this->error, 'WP_Error' ) ) {
			$this->error = new WP_Error();
		}

		$this->error->add( 'addon_error', $message );
	}

	/**
	 * Display error. Get all the error messages and display them in the admin notices.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function display_error() {

		if ( ! is_a( $this->error, 'WP_Error' ) ) {
			return;
		}

		$message      = $this->error->get_error_messages();
		$important    = '<strong>' . __( 'Important:', 'simpleform-akismet' ) . '</strong>&nbsp;';
		$admin_notice = '<div class="notice notice-warning incompatible"><p>';

		if ( count( $message ) > 1 ) {
			$admin_notice .= '<ul>';
			foreach ( $message as $msg ) {
				$admin_notice .= '<li>' . $important . $msg . '</li>';
			}
			$admin_notice .= '</ul>';
		} else {
			$admin_notice .= $important . $message[0];
		}

		$admin_notice .= '</p></div>';

		echo wp_kses_post( $admin_notice );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function run() {

		$this->loader->run();
	}

	/**
	 * Retrieve the plugin's name
	 *
	 * @since 1.0.0
	 *
	 * @return string The name of the plugin.
	 */
	public function get_plugin_name() {

		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin
	 *
	 * @since 1.0.0
	 *
	 * @return SimpleForm_ReCaptcha_Loader Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {

		return $this->loader;
	}

	/**
	 * Retrieve the plugin's version number
	 *
	 * @since 1.0.0
	 *
	 * @return string The version number of the plugin.
	 */
	public function get_version() {

		return $this->version;
	}
}
