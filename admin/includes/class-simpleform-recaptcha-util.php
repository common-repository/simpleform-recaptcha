<?php
/**
 * File delegated to list the most commonly used functions.
 *
 * @package    SimpleForm reCAPTCHA
 * @subpackage SimpleForm reCAPTCHA/admin/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Defines the the general utilities class.
 */
class SimpleForm_ReCaptcha_Util {

	/**
	 * Retrieve the option value.
	 *
	 * @since 1.2.0
	 *
	 * @param int                      $form_id The ID of the form.
	 * @param string                   $type    The type of the option.
	 * @param string                   $key     The key of the option.
	 * @param bool|string|int|string[] $preset  The default value to return if the option does not exist.
	 *
	 * @return mixed The value to return.
	 */
	public function get_sform_option( $form_id, $type, $key, $preset ) {

		if ( 1 === (int) $form_id ) {
			$option = (array) get_option( 'sform_' . $type );
		} else {
			$option = false !== get_option( 'sform_' . $form_id . '_' . $type ) ? (array) get_option( 'sform_' . $form_id . '_' . $type ) : (array) get_option( 'sform_' . $type );
		}

		if ( $key ) {
			if ( isset( $option[ $key ] ) ) {
				if ( is_bool( $option[ $key ] ) ) {
					$value = $option[ $key ] ? true : false;
				} else {
					$value = ! empty( $option[ $key ] ) ? $option[ $key ] : $preset;
				}
			} else {
				$value = $preset;
			}
		} else {
			$value = $option;
		}

		return $value;
	}

	/**
	 * Sanitize form data
	 *
	 * @since 1.2.0
	 *
	 * @param string $field The ID of input field.
	 * @param string $type  The type of input field.
	 *
	 * @return mixed The sanitized value.
	 */
	public function sanitized_input( $field, $type ) {

		if ( isset( $_POST[ $field ] ) && isset( $_POST['simpleform_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['simpleform_nonce'] ), 'simpleform_backend_update' ) ) {

			$sanitized_value = array(
				'captcha'   => sanitize_key( $_POST[ $field ] ),
				'form'      => absint( $_POST[ $field ] ),
				'tickbox'   => true,
				'text'      => sanitize_text_field( wp_unslash( $_POST[ $field ] ) ),
				'type'      => sanitize_key( $_POST[ $field ] ),
				'threshold' => sanitize_text_field( wp_unslash( $_POST[ $field ] ) ),
				'size'      => sanitize_key( $_POST[ $field ] ),
				'style'     => sanitize_key( $_POST[ $field ] ),
				'html'      => wp_kses_post( wp_unslash( $_POST[ $field ] ) ),
			);

			$value = $sanitized_value[ $type ];

		} else {

			$default_value = array(
				'captcha'   => 'math',
				'form'      => 1,
				'tickbox'   => false,
				'text'      => '',
				'type'      => 'v2',
				'threshold' => '0.5',
				'size'      => 'normal',
				'style'     => 'light',
				'html'      => '',
			);

			$value = $default_value[ $type ];

		}

		return $value;
	}

	/**
	 * Get the user’s response token.
	 *
	 * @since 1.2.0
	 *
	 * @return string The token value.
	 */
	public function get_token() {

		if ( isset( $_POST['g-recaptcha-response'] ) ) { // phpcs:ignore

			$value = $_POST['g-recaptcha-response']; // phpcs:ignore

		} else {

			$value = '';

		}

		return $value;
	}

	/**
	 * Get the user’s ip address.
	 *
	 * @since 1.2.0
	 *
	 * @return string The token value.
	 */
	public function get_ip() {

		if ( isset( $_SERVER['REMOTE_ADDR'] ) ) { // phpcs:ignore

			$value = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ); // phpcs:ignore

		} else {

			$value = '';

		}

		return $value;
	}
}
