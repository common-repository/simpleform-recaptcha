<?php
/**
 * Main file for the admin functionality of the plugin.
 *
 * @package    SimpleForm reCAPTCHA
 * @subpackage SimpleForm reCAPTCHA/admin
 */

defined( 'ABSPATH' ) || exit;

/**
 * Core class used to implement the admin-specific functionality of the plugin.
 */
class SimpleForm_ReCaptcha_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var    string The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var    string The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version     The version of the plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the scripts for the admin area.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook The hook that was called.
	 *
	 * @return void
	 */
	public function enqueue_scripts( $hook ) {

		global $sform_editor;
		global $sform_new;
		global $sform_settings;

		if ( $hook !== $sform_editor && $hook !== $sform_new && $hook !== $sform_settings ) {
			return;
		}

		wp_enqueue_script( $this->plugin_name . '-admin', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery' ), $this->version, false );

		$util          = new SimpleForm_ReCaptcha_Util();
		$recaptcha     = $util->get_sform_option( 1, 'settings', 'recaptcha', false );
		$usage         = $recaptcha ? 'on' : 'off';
		$recaptcha_v2  = __( 'reCAPTCHA v2 checkbox', 'simpleform-recaptcha' );
		$recaptcha_v2i = __( 'reCAPTCHA v2 invisible', 'simpleform-recaptcha' );
		$recaptcha_v3  = __( 'reCAPTCHA v3', 'simpleform-recaptcha' );
		$array_vars    = array(
			'usage' => $usage,
			'v2'    => $recaptcha_v2,
			'v2i'   => $recaptcha_v2i,
			'v3'    => $recaptcha_v3,
		);

		wp_localize_script( $this->plugin_name . '-admin', 'sform_recaptcha_object', $array_vars );
	}

	/**
	 * Add the new fields in the settings page.
	 *
	 * @since   1.0.0
	 * @version 1.2.0
	 *
	 * @param string $extra_option The value to filter.
	 * @param int    $form         The ID of the form.
	 *
	 * @return string The HTML markup for the new fields added by addon.
	 */
	public function settings_fields( $extra_option, $form ) {

		$util            = new SimpleForm_ReCaptcha_Util();
		$color           = strval( $util->get_sform_option( 1, 'settings', 'admin_color', 'default' ) );
		$recaptcha       = $util->get_sform_option( 1, 'settings', 'recaptcha', false );
		$recaptcha_type  = $util->get_sform_option( 1, 'settings', 'recaptcha_type', 'v2' );
		$site_key        = $util->get_sform_option( 1, 'settings', 'recaptcha_site_key', '' );
		$secret_key      = $util->get_sform_option( 1, 'settings', 'recaptcha_secret_key', '' );
		$threshold       = $util->get_sform_option( 1, 'settings', 'recaptcha_threshold', '0.5' );
		$recaptcha_size  = $util->get_sform_option( 1, 'settings', 'recaptcha_size', 'normal' );
		$recaptcha_style = $util->get_sform_option( 1, 'settings', 'recaptcha_style', 'light' );
		$recaptcha_badge = $util->get_sform_option( 1, 'settings', 'recaptcha_badge', false );
		$privacy_url     = '<a href="https://policies.google.com/privacy" target="_blank">' . __( 'Privacy Policy', 'simpleform-recaptcha' ) . '</a>';
		$terms_url       = '<a href="https://policies.google.com/terms" target="_blank">' . __( 'Terms of Service', 'simpleform-recaptcha' ) . '</a>';
		/* translators: %1$s: Privacy Policy link, %2$s: Terms of Service link */
		$disclaimer       = sprintf( __( 'This site is protected by reCAPTCHA and the Google %1$s and %2$s apply', 'simpleform-recaptcha' ), $privacy_url, $terms_url );
		$recaptcha_notice = $util->get_sform_option( 1, 'settings', 'recaptcha_notice', $disclaimer );
		/* translators: %s: reCAPTCHA service link */
		$recaptcha_alert = ! $recaptcha ? sprintf( __( 'To use reCAPTCHA, you need to sign up for API keys for your site. %s', 'simpleform-recaptcha' ), '<strong><a href="https://www.google.com/recaptcha/admin/create" target="_blank" style="text-decoration: none;">' . __( 'Learn more', 'simpleform-recaptcha' ) . '</a></strong>' ) : '&nbsp;';
		$url             = '<strong><a href="https://www.google.com/recaptcha/" target="_blank" style="text-decoration: none;">' . __( 'your account', 'simpleform-recaptcha' ) . '</a></strong>';
		$url_admin       = '<strong><a href="https://www.google.com/recaptcha/admin" target="_blank" style="text-decoration: none;">' . __( 'admin console', 'simpleform-recaptcha' ) . '</a></strong>';
		$url_faq         = '<strong><a href="https://developers.google.com/recaptcha/docs/faq" target="_blank" style="text-decoration: none;">' . __( 'reCAPTCHA FAQ', 'simpleform-recaptcha' ) . '</a></strong>';
		/* translators: %s: reCAPTCHA FAQ link */
		$badge_notes = sprintf( __( 'You are allowed to hide the badge as long as you include the reCAPTCHA branding visibly in the user flow as defined in the %s', 'simpleform-recaptcha' ), $url_faq );
		if ( $recaptcha ) {
			$option_position    = '';
			$option_class       = '';
			$usage_notice_class = 'v2' === $recaptcha_type || ! $recaptcha_badge ? 'unseen' : '';
			$badge_position     = ! $recaptcha_badge ? 'last' : '';
			if ( 'v2' === $recaptcha_type ) {
				$type            = '<span class="rctype">' . __( 'reCAPTCHA v2 checkbox', 'simpleform-recaptcha' ) . '</span>';
				$badge_class     = 'unseen';
				$threshold_class = 'unseen';
				$style_class     = '';
			} elseif ( 'invisible' === $recaptcha_type ) {
				$type            = '<span class="rctype">' . __( 'reCAPTCHA v2 invisible', 'simpleform-recaptcha' ) . '</span>';
				$badge_class     = '';
				$threshold_class = 'unseen';
				$style_class     = 'unseen';
			} else {
				$type            = '<span class="rctype">' . __( 'reCAPTCHA v3', 'simpleform-recaptcha' ) . '</span>';
				$badge_class     = '';
				$threshold_class = '';
				$style_class     = 'unseen';
			}
		} else {
			$type               = '<span class="rctype">' . __( 'reCAPTCHA v2 checkbox', 'simpleform-recaptcha' ) . '</span>';
			$option_position    = 'last';
			$option_class       = 'unseen';
			$threshold_class    = 'unseen';
			$style_class        = 'unseen';
			$badge_class        = 'unseen';
			$usage_notice_class = 'unseen';
			$badge_position     = '';
		}
		/* translators: %1$s: reCAPTCHA type, %2$s: account link */
		$site_key_notes = sprintf( __( 'This your own personal Site Key for %1$s. Go to %2$s to find it', 'simpleform-recaptcha' ), $type, $url );
		/* translators: %1$s: reCAPTCHA type, %2$s: account link */
		$secret_key_notes = sprintf( __( 'This your own personal Secret Key for %1$s. Go to %2$s to find it', 'simpleform-recaptcha' ), $type, $url );
		/* translators: %s: reCAPTCHA admin console link */
		$threshold_notes = sprintf( __( 'Set the threshold below which the submissions will be rejected. You can help yourself by looking at your website traffic in the %s', 'simpleform-recaptcha' ), $url_admin );

		if ( 1 !== $form ) {
			$disabled_class  = 'disabled';
			$disabled_option = ' disabled="disabled"';
			$settings_button = '<a href="' . menu_page_url( 'sform-settings', false ) . '"><span class="dashicons dashicons-edit icon-button admin ' . esc_attr( $color ) . '"></span><span class="settings-page wp-core-ui button admin">' . __( 'Go to main settings for edit', 'simpleform-recaptcha' ) . '</span></a>';
		} else {
			$disabled_class  = '';
			$disabled_option = '';
			$settings_button = '';
		}

		// The HTML markup for the reCAPTCHA options.
		$extra_option = '<h2 id="h2-recaptcha" class="options-heading"><span class="heading" data-section="recaptcha">' . __( 'reCAPTCHA Spam Protection', 'simpleform-recaptcha' ) . '<span class="toggle dashicons dashicons-arrow-up-alt2 recaptcha"></span></span>' . $settings_button . '</h2><div class="section recaptcha"><table class="form-table recaptcha"><tbody>';

		$extra_option .= '<tr><th class="option"><span>' . __( 'reCAPTCHA Anti-Spam', 'simpleform-recaptcha' ) . '</span></th><td id="tdrecaptcha" class="checkbox-switch notes ' . $option_position . '"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="recaptcha" id="recaptcha" class="sform-switch" value="' . $recaptcha . '" ' . checked( $recaptcha, true, false ) . $disabled_option . '><span></span></label><label for="recaptcha" class="switch-label ' . $disabled_class . '">' . __( 'Enable reCAPTCHA Anti-Spam protection', 'simpleform-recaptcha' ) . '</label></div><p class="description">' . $recaptcha_alert . '</p></td></tr>';

		$extra_option .= '<tr class="trrecaptcha ' . $option_class . '"><th class="option"><span>' . __( 'reCAPTCHA Type', 'simpleform-recaptcha' ) . '</span></th><td class="radio"><fieldset><label for="v2-checkbox" class="radio ' . $disabled_class . '"><input type="radio" name="recaptcha-type" id="v2-checkbox" value="v2" ' . checked( $recaptcha_type, 'v2', false ) . $disabled_option . ' \>' . __( 'reCAPTCHA v2 checkbox', 'simpleform-recaptcha' ) . '</label><label for="v2-invisible" class="radio ' . $disabled_class . '"><input type="radio" name="recaptcha-type" id="v2-invisible"  value="invisible" ' . checked( $recaptcha_type, 'invisible', false ) . $disabled_option . ' \>' . __( 'reCAPTCHA v2 invisible', 'simpleform-recaptcha' ) . '</label><label for="v3" class="radio ' . $disabled_class . '"><input type="radio" name="recaptcha-type" id="v3" value="v3" ' . checked( $recaptcha_type, 'v3', false ) . $disabled_option . ' \>' . __( 'reCAPTCHA v3', 'simpleform-recaptcha' ) . '</label></fieldset></td></tr>';

		$extra_option .= '<tr class="trrecaptcha ' . $option_class . '" ><th class="option"><span>' . __( 'reCAPTCHA Site Key', 'simpleform-recaptcha' ) . '</span></th><td class="text notes"><input type="text" name="recaptcha-site-key" id="recaptcha-site-key" class="sform" value="' . $site_key . '" ' . $disabled_option . ' \><p id="gkey" class="description key">' . $site_key_notes . '</p></td></tr>';

		$extra_option .= '<tr class="trrecaptcha ' . $option_class . '" ><th class="option"><span>' . __( 'reCAPTCHA Secret Key', 'simpleform-recaptcha' ) . '</span></th><td class="text notes"><input type="text" name="recaptcha-secret-key" id="recaptcha-secret-key" class="sform" value="' . $secret_key . '" ' . $disabled_option . ' \><p class="description key">' . $secret_key_notes . '</p></td></tr>';

		$extra_option .= '<tr class="trrecaptcha threshold ' . $threshold_class . '" ><th class="option"><span>' . __( 'reCAPTCHA v3 Threshold', 'simpleform-recaptcha' ) . '</span></th><td class="select notes"><select name="recaptcha-threshold" id="recaptcha-threshold" class="sform" ' . $disabled_option . '><option value="1.0" ' . selected( $threshold, '1.0', false ) . '>' . __( '1.0 (good interaction)', 'simpleform-recaptcha' ) . '</option><option value="0.9" ' . selected( $threshold, '0.9', false ) . '>' . __( '0.9', 'simpleform-recaptcha' ) . '</option><option value="0.8" ' . selected( $threshold, '0.8', false ) . '>' . __( '0.8', 'simpleform-recaptcha' ) . '</option><option value="0.7" ' . selected( $threshold, '0.7', false ) . '> ' . __( '0.7', 'simpleform-recaptcha' ) . '</option><option value="0.6" ' . selected( $threshold, '0.6', false ) . '>' . __( '0.6', 'simpleform-recaptcha' ) . '</option><option value="0.5" ' . selected( $threshold, '0.5', false ) . '>' . __( '0.5 (default value)', 'simpleform-recaptcha' ) . '</option><option value="0.4" ' . selected( $threshold, '0.4', false ) . '>' . __( '0.4', 'simpleform-recaptcha' ) . '</option><option value="0.3" ' . selected( $threshold, '0.3', false ) . '>' . __( '0.3', 'simpleform-recaptcha' ) . '</option><option value="0.2"  ' . selected( $threshold, '0.2', false ) . '>' . __( '0.2', 'simpleform-recaptcha' ) . '</option><option value="0.1" ' . selected( $threshold, '0.1', false ) . '>' . __( '0.1', 'simpleform-recaptcha' ) . '</option><option value="0.0" ' . selected( $threshold, '0.0', false ) . '>' . __( '0.0 (likely a bot)', 'simpleform-recaptcha' ) . '</option></select><p class="description">' . $threshold_notes . '</p></td></tr>';

		$extra_option .= '<tr class="trrecaptcha style ' . $style_class . '"><th class="option"><span>' . __( 'reCAPTCHA Widget Size', 'simpleform-recaptcha' ) . '</span></th><td class="radio"><fieldset><label for="compact" class="radio ' . $disabled_class . '"><input type="radio" name="recaptcha-size" id="compact" value="compact" ' . checked( $recaptcha_size, 'compact', false ) . $disabled_option . ' \>' . __( 'Compact', 'simpleform-recaptcha' ) . '</label><label for="normal" class="radio ' . $disabled_class . '"><input type="radio" name="recaptcha-size" id="normal"  value="normal" ' . checked( $recaptcha_size, 'normal', false ) . $disabled_option . ' \>' . __( 'Normal', 'simpleform-recaptcha' ) . '</label></fieldset></td></tr>';

		$extra_option .= '<tr class="trrecaptcha style ' . $style_class . '"><th class="option"><span>' . __( 'reCAPTCHA Widget Theme', 'simpleform-recaptcha' ) . '</span></th><td class="radio last"><fieldset><label for="light-theme" class="radio ' . $disabled_class . '"><input type="radio" name="recaptcha-style" id="light-theme" value="light" ' . checked( $recaptcha_style, 'light', false ) . $disabled_option . ' \>' . __( 'Light color', 'simpleform-recaptcha' ) . '</label><label for="dark-theme" class="radio ' . $disabled_class . '"><input type="radio" name="recaptcha-style" id="dark-theme"  value="dark" ' . checked( $recaptcha_style, 'dark', false ) . $disabled_option . ' \>' . __( 'Dark color', 'simpleform-recaptcha' ) . '</label></fieldset></td></tr>';

		$extra_option .= '<tr class="trrecaptcha badge ' . $badge_class . '"><th class="option"><span>' . __( 'reCAPTCHA Badge', 'simpleform-recaptcha' ) . '</span></th><td id="tdbadge" class="checkbox-switch notes ' . $badge_position . '"><div class="switch-box"><label class="switch-input"><input type="checkbox" name="recaptcha-badge" id="recaptcha-badge" class="sform-switch" value="' . $recaptcha_badge . '" ' . checked( $recaptcha_badge, true, false ) . $disabled_option . '><span></span></label><label for="recaptcha-badge" class="switch-label ' . $disabled_class . '">' . __( 'Hide the reCAPTCHA badge and include a notice about its use', 'simpleform-recaptcha' ) . '</label></div><p class="description">' . $badge_notes . '</p></td></tr>';

		$extra_option .= '<tr class="trrecaptcha usage ' . $usage_notice_class . '"><th class="option"><span>' . __( 'Usage Notice', 'simpleform' ) . '</span></th><td class="textarea last"><textarea name="recaptcha-notice" id="recaptcha-notice" class="sform labels" ' . $disabled_class . '>' . $recaptcha_notice . '</textarea></td></tr>';

		$extra_option .= '</tbody></table></div>';

		return $extra_option;
	}

	/**
	 * Add error message fields for reCAPTCHA in the settings page.
	 *
	 * @since   1.0.0
	 * @version 1.2.0
	 *
	 * @param string $extra_option The value to filter.
	 * @param int    $form         The ID of the form.
	 *
	 * @return string The field added by addon.
	 */
	public function validation_messages( $extra_option, $form ) {

		$util                 = new SimpleForm_ReCaptcha_Util();
		$recaptcha            = $util->get_sform_option( 1, 'settings', 'recaptcha', false );
		$recaptcha_type       = $util->get_sform_option( 1, 'settings', 'recaptcha_type', 'v2' );
		$unverified_recaptcha = $util->get_sform_option( 1, 'settings', 'unverified_recaptcha', __( 'Please prove you are not a robot', 'simpleform-recaptcha' ) );
		$expired_recaptcha    = $util->get_sform_option( 1, 'settings', 'expired_recaptcha', __( 'reCAPTCHA response expired, please answer again!', 'simpleform-recaptcha' ) );
		$invalid_recaptcha    = $util->get_sform_option( 1, 'settings', 'invalid_recaptcha', __( 'Robot verification failed, please try again', 'simpleform-recaptcha' ) );
		$option_class         = ! $recaptcha || 'v2' !== $recaptcha_type ? 'unseen' : '';
		$invalid_option_class = ! $recaptcha ? 'unseen' : '';
		$disabled_option      = 1 !== $form ? ' disabled="disabled"' : '';

		$extra_option = '<tr class="trrecaptcha v2 ' . $option_class . '" ><th class="option"><span>' . __( 'Unverified reCAPTCHA Error', 'simpleform-recaptcha' ) . '</span></th><td class="text"><input type="text" name="unverified-recaptcha" id="unverified-recaptcha" class="sform" placeholder="' . esc_attr__( 'Please enter an error message to be displayed in case of reCAPTCHA is not being verified', 'simpleform-recaptcha' ) . '" value="' . $unverified_recaptcha . '"' . $disabled_option . ' \></td></tr>';

		$extra_option .= '<tr class="trrecaptcha v2 ' . $option_class . '" ><th class="option"><span>' . __( 'Expired reCAPTCHA Error', 'simpleform-recaptcha' ) . '</span></th><td class="text"><input type="text" name="expired-recaptcha" id="expired-recaptcha" class="sform" placeholder="' . esc_attr__( 'Please enter an error message to be displayed in case of response expired and the reCAPTCHA must be verified again', 'simpleform-recaptcha' ) . '" value="' . $expired_recaptcha . '"' . $disabled_option . ' \></td></tr>';

		$extra_option .= '<tr class="trrecaptcha ' . $invalid_option_class . '" ><th class="option"><span>' . __( 'Invalid reCAPTCHA Error', 'simpleform-recaptcha' ) . '</span></th><td class="text"><input type="text" name="invalid-recaptcha" id="invalid-recaptcha" class="sform" placeholder="' . esc_attr__( 'Please enter an error message to be displayed in case of reCAPTCHA verification failed', 'simpleform-recaptcha' ) . '" value="' . $invalid_recaptcha . '"' . $disabled_option . ' \></td></tr>';

		return $extra_option;
	}

	/**
	 * Validate the new fields in the settings page.
	 *
	 * @since   1.0.0
	 * @version 1.2.0
	 *
	 * @param string $error The value to filter.
	 *
	 * @return string The filtered value after the checks.
	 */
	public function settings_validation( $error ) {

		$util                 = new SimpleForm_ReCaptcha_Util();
		$recaptcha            = $util->sanitized_input( 'recaptcha', 'tickbox' );
		$site_key             = $util->sanitized_input( 'recaptcha-site-key', 'text' );
		$secret_key           = $util->sanitized_input( 'recaptcha-secret-key', 'text' );
		$recaptcha_type       = $util->sanitized_input( 'recaptcha_type', 'type' );
		$unverified_recaptcha = $util->sanitized_input( 'unverified-recaptcha', 'text' );
		$expired_recaptcha    = $util->sanitized_input( 'expired-recaptcha', 'text' );
		$invalid_recaptcha    = $util->sanitized_input( 'invalid-recaptcha', 'text' );

		if ( $recaptcha ) {

			// Check if reCAPTCHA API keys exist before saving settings.
			if ( empty( $site_key ) || empty( $secret_key ) ) {

				$error = __( 'Please enter the API keys for enabling reCAPTCHA Anti-Spam', 'simpleform-recaptcha' );

			}

			// Check if error messages are empty before saving settings.
			if ( 'v2' === $recaptcha_type ) {

				if ( empty( $unverified_recaptcha ) ) {

					$error = __( 'Please enter an error message when the reCAPTCHA is not answered', 'simpleform-recaptcha' );

				}

				if ( empty( $expired_recaptcha ) ) {

					$error = __( 'Please enter an error message if the reCAPTCHA response expires and is no longer valid', 'simpleform-recaptcha' );

				}
			} elseif ( empty( $invalid_recaptcha ) ) {

				$error = __( 'Please enter an error message if the reCAPTCHA verification failed', 'simpleform-recaptcha' );
			}
		}

		return $error;
	}

	/**
	 * Add the new settings values in the settings options array.
	 *
	 * @since   1.0.0
	 * @version 1.2.0
	 *
	 * @return mixed[] The fields values added by addon.
	 */
	public function settings_storing() {

		$util = new SimpleForm_ReCaptcha_Util();
		$form = $util->sanitized_input( 'form_id', 'form' );

		if ( 1 === $form ) {

			$recaptcha            = $util->sanitized_input( 'recaptcha', 'tickbox' );
			$recaptcha_type       = $util->sanitized_input( 'recaptcha-type', 'type' );
			$site_key             = $util->sanitized_input( 'recaptcha-site-key', 'text' );
			$secret_key           = $util->sanitized_input( 'recaptcha-secret-key', 'text' );
			$threshold            = $util->sanitized_input( 'recaptcha-threshold', 'threshold' );
			$recaptcha_size       = $util->sanitized_input( 'recaptcha-size', 'size' );
			$recaptcha_style      = $util->sanitized_input( 'recaptcha-style', 'style' );
			$recaptcha_badge      = $util->sanitized_input( 'recaptcha-badge', 'tickbox' );
			$recaptcha_notice     = $util->sanitized_input( 'recaptcha-notice', 'html' );
			$unverified_recaptcha = $util->sanitized_input( 'unverified-recaptcha', 'text' );
			$expired_recaptcha    = $util->sanitized_input( 'expired-recaptcha', 'text' );
			$invalid_recaptcha    = $util->sanitized_input( 'invalid-recaptcha', 'text' );

			$new_items = array(
				'recaptcha'            => $recaptcha,
				'recaptcha_type'       => $recaptcha_type,
				'recaptcha_site_key'   => $site_key,
				'recaptcha_secret_key' => $secret_key,
				'recaptcha_threshold'  => $threshold,
				'recaptcha_size'       => $recaptcha_size,
				'recaptcha_style'      => $recaptcha_style,
				'recaptcha_badge'      => $recaptcha_badge,
				'recaptcha_notice'     => $recaptcha_notice,
				'unverified_recaptcha' => $unverified_recaptcha,
				'expired_recaptcha'    => $expired_recaptcha,
				'invalid_recaptcha'    => $invalid_recaptcha,
			);

		} else {

			$new_items = array(
				'recaptcha'            => $util->get_sform_option( 1, 'settings', 'recaptcha', false ),
				'recaptcha_type'       => $util->get_sform_option( 1, 'settings', 'recaptcha_type', 'v2' ),
				'recaptcha_site_key'   => $util->get_sform_option( 1, 'settings', 'recaptcha_site_key', '' ),
				'recaptcha_secret_key' => $util->get_sform_option( 1, 'settings', 'recaptcha_secret_key', '' ),
				'recaptcha_threshold'  => $util->get_sform_option( 1, 'settings', 'recaptcha_threshold', '0.5' ),
				'recaptcha_size'       => $util->get_sform_option( 1, 'settings', 'recaptcha_size', 'normal' ),
				'recaptcha_style'      => $util->get_sform_option( 1, 'settings', 'recaptcha_style', 'light' ),
				'recaptcha_badge'      => $util->get_sform_option( 1, 'settings', 'recaptcha_badge', false ),
				'recaptcha_notice'     => $util->get_sform_option( 1, 'settings', 'recaptcha_notice', '' ),
				'unverified_recaptcha' => $util->get_sform_option( 1, 'settings', 'unverified_recaptcha', __( 'Please prove you are not a robot', 'simpleform-recaptcha' ) ),
				'expired_recaptcha'    => $util->get_sform_option( 1, 'settings', 'expired_recaptcha', __( 'reCAPTCHA response expired, please answer again!', 'simpleform-recaptcha' ) ),
				'invalid_recaptcha'    => $util->get_sform_option( 1, 'settings', 'invalid_recaptcha', __( 'Robot verification failed, please try again', 'simpleform-recaptcha' ) ),
			);

		}

		return $new_items;
	}

	/**
	 * Add the new fields in the editor page.
	 *
	 * @since   1.0.0
	 * @version 1.2.0
	 *
	 * @param string $extra_option The value to filter.
	 * @param int    $form         The ID of the form.
	 *
	 * @return string The HTML markup for the new fields added by addon.
	 */
	public function editor_fields( $extra_option, $form ) {

		$util          = new SimpleForm_ReCaptcha_Util();
		$recaptcha     = $util->get_sform_option( 1, 'settings', 'recaptcha', false );
		$captcha_field = $util->get_sform_option( $form, 'attributes', 'captcha_field', 'hidden' );
		$captcha_type  = $util->get_sform_option( $form, 'attributes', 'captcha_type', 'math' );
		$field_class   = 'hidden' === $captcha_field ? 'unseen' : '';

		if ( $recaptcha ) {

			$extra_option = '<tr class="trcaptcha ' . $field_class . '"><th class="option"><span>' . __( 'Captcha Field Type', 'simpleform-recaptcha' ) . '</span></th><td class="radio"><fieldset><label for="math-captcha"><input id="math-captcha" type="radio" name="captcha_type" value="math" ' . checked( $captcha_type, 'math', false ) . '>' . __( 'Math Captcha', 'simpleform-recaptcha' ) . '</label><label for="google-captcha"><input id="google-captcha" type="radio" name="captcha_type" value="recaptcha" ' . checked( $captcha_type, 'recaptcha', false ) . '> ' . __( 'reCAPTCHA', 'simpleform-recaptcha' ) . '</label></fieldset></td></tr>';

		}

		return $extra_option;
	}

	/**
	 * Set the captcha label class value.
	 *
	 * @since   1.0.0
	 * @version 1.2.0
	 *
	 * @param string $captcha_field_class The value to filter.
	 * @param int    $form                The ID of the form.
	 *
	 * @return string The class value.
	 */
	public function captcha_label_class( $captcha_field_class, $form ) {

		$util                = new SimpleForm_ReCaptcha_Util();
		$recaptcha           = $util->get_sform_option( $form, 'settings', 'recaptcha', false );
		$captcha_field       = $util->get_sform_option( $form, 'attributes', 'captcha_field', 'hidden' );
		$captcha_type        = $util->get_sform_option( $form, 'attributes', 'captcha_type', 'math' );
		$captcha_field_class = 'hidden' === $captcha_field || ( 'math' !== $captcha_type && $recaptcha ) ? 'unseen' : '';

		return $captcha_field_class;
	}

	/**
	 * Add the new editor values in the attributes options array.
	 *
	 * @since 1.0.0
	 *
	 * @return mixed[] The fields values added by addon.
	 */
	public function attributes_storing() {

		$util          = new SimpleForm_ReCaptcha_Util();
		$recaptcha     = $util->get_sform_option( 1, 'settings', 'recaptcha', false );
		$captcha_type  = $util->sanitized_input( 'captcha_type', 'captcha' );
		$captcha_field = $recaptcha ? $captcha_type : 'math';

		$new_items = array( 'captcha_type' => $captcha_field );

		return $new_items;
	}
}
