<?php
/**
 * Main file for invoking the Google reCAPTCHA challenge.
 *
 * @package    SimpleForm reCAPTCHA
 * @subpackage SimpleForm reCAPTCHA/public
 */

defined( 'ABSPATH' ) || exit;

/**
 * Core class used to implement the public-specific functionality of the plugin.
 */
class SimpleForm_ReCaptcha_Public {

	/**
	 * Class constructor.
	 *
	 * @since 1.2.0
	 */
	public function __construct() {

		// Load the Google reCAPTCHA API script.
		add_action( 'wp_enqueue_scripts', array( $this, 'recaptcha_api' ) );
		// Filter the script tag of enqueued script to add the async and defer attributes.
		add_filter( 'script_loader_tag', array( $this, 'recaptcha_script_attributes' ), 10, 2 );
	}

	/**
	 * Add the async and defer parameters in the script tag.
	 *
	 * @since 1.2.0
	 *
	 * @param string $tag    The <script> tag for the enqueued script.
	 * @param string $handle The script's registered handle.
	 *
	 * @return string The value to return.
	 */
	public function recaptcha_script_attributes( $tag, $handle ) {

		if ( 'google-recaptcha' === $handle ) {

			$util           = new SimpleForm_ReCaptcha_Util();
			$recaptcha_type = $util->get_sform_option( 1, 'settings', 'recaptcha_type', 'v2' );

			if ( false === stripos( $tag, 'async defer' ) && 'v3' !== $recaptcha_type ) {
				$tag = str_replace( '></script>', ' async defer></script>', $tag );
			}
		}

		return $tag;
	}

	/**
	 * Register the reCAPTCHA script to run the scripts that call grecaptcha.
	 *
	 * @since 1.2.0
	 *
	 * @return void.
	 */
	public function recaptcha_api() {

		$util           = new SimpleForm_ReCaptcha_Util();
		$recaptcha      = $util->get_sform_option( 1, 'settings', 'recaptcha', false );
		$recaptcha_type = $util->get_sform_option( 1, 'settings', 'recaptcha_type', 'v2' );
		$site_key       = $util->get_sform_option( 1, 'settings', 'recaptcha_site_key', '' );

		if ( $recaptcha ) {

			if ( 'v3' !== $recaptcha_type ) {

				// Register the script in the footer (this allows to include callbacks by wp_add_inline_script).
				wp_register_script( 'google-recaptcha', 'https://www.google.com/recaptcha/api.js', array(), null, true ); // phpcs:ignore

				/*
				Remove google cookies except for _GRECAPTCHA.
				$src = 'https://www.recaptcha.net/recaptcha/api.js';
				*/

			} else {

				// Register the script in the footer with the sitekey.
				$script = 'https://www.google.com/recaptcha/api.js?render=' . $site_key;
				wp_register_script( 'google-recaptcha', $script, array(), null, true ); // phpcs:ignore

			}
		} else {

			// Remove the registered reCAPTCHA script.
			wp_deregister_script( 'google-recaptcha' );

		}
	}

	/**
	 * Add reCAPTCHA field in the contact form.
	 *
	 * @since   1.0.0
	 * @version 1.2.0
	 *
	 * @param string   $captcha_input The value to filter.
	 * @param int      $form_id       The ID of the form.
	 * @param string[] $error_class   The list of errors found.
	 *
	 * @return string The HTML markup for the new field added by addon.
	 */
	public function recaptcha_field( $captcha_input, $form_id, $error_class ) {

		$util         = new SimpleForm_ReCaptcha_Util();
		$recaptcha    = $util->get_sform_option( 1, 'settings', 'recaptcha', false );
		$captcha_type = $util->get_sform_option( $form_id, 'attributes', 'captcha_type', 'math' );

		if ( ! empty( $captcha_input ) && $recaptcha && 'recaptcha' === $captcha_type ) {

			$recaptcha_type   = $util->get_sform_option( 1, 'settings', 'recaptcha_type', 'v2' );
			$site_key         = $util->get_sform_option( 1, 'settings', 'recaptcha_site_key', '' );
			$recaptcha_size   = $util->get_sform_option( 1, 'settings', 'recaptcha_size', 'normal' );
			$recaptcha_style  = $util->get_sform_option( 1, 'settings', 'recaptcha_style', 'light' );
			$recaptcha_badge  = $util->get_sform_option( 1, 'settings', 'recaptcha_badge', false );
			$recaptcha_notice = $util->get_sform_option( 1, 'settings', 'recaptcha_notice', '' );
			$label_position   = $util->get_sform_option( $form_id, 'attributes', 'label_position', 'top' );
			$inline_class     = 'inline' === $label_position ? 'col-sm-10' : '';

			if ( $recaptcha_badge ) {

				$badge_style  = 'div.grecaptcha-badge { width: 0px !important; }';
				$badge_style .= '.grecaptcha-notice { display: inline-block; font-size: 0.75em; margin-bottom: 22px;}';
				$class        = 'class="nolabel ' . $inline_class . ' grecaptcha-notice"';
				$badge        = '<span ' . $class . '>' . $recaptcha_notice . '</span>';

				wp_add_inline_style( 'simpleform-public', $badge_style );
				wp_add_inline_style( 'simpleform', $badge_style );

			} else {

				$badge = ''; // add padding-top 20px se invisible inline.

			}

			// Load the reCAPTCHA script only if reCAPTCHA is used.
			wp_enqueue_script( 'google-recaptcha' );

			// Render the reCAPTCHA widget in a div.
			if ( 'v3' === $recaptcha_type ) {

				// Render the reCAPTCHA widget in a div.
				$captcha_input  = '<div class="g-recaptcha">';
				$captcha_input .= '<input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response" value="" />';
				$captcha_input .= '<input type="hidden" name="action" value="submit_form">';
				$captcha_input .= $badge . '</div>';
				$script         = $this->v3_script( $form_id );

				// Load scripts that call grecaptcha after the reCAPTCHA API loads to avoid race conditions.
				wp_add_inline_script( 'google-recaptcha', $script, 'after' );

			} elseif ( 'invisible' === $recaptcha_type ) {

				// Render the reCAPTCHA widget in a div.
				// Add: data-badge="bottomright|bottomleft|inline".
				$captcha_input  = '<div class="g-recaptcha row" data-sitekey="' . $site_key . '" data-callback="onSubmit_' . $form_id . '" data-expired-callback="onExpire_' . $form_id . '" data-size="invisible"></div>';
				$captcha_input .= $badge;
				$script         = $this->invisible_script( $form_id );

				// Load scripts that call grecaptcha before the reCAPTCHA API loads to avoid race conditions.
				wp_add_inline_script( 'google-recaptcha', $script, 'before' );

			} else {

				// Render the reCAPTCHA widget in a div.
				$captcha_input = '<div class="gcaptcha-wrap nolabel ' . $inline_class . '"><div class="g-recaptcha" data-sitekey="' . $site_key . '" data-theme="' . $recaptcha_style . '" data-size="' . $recaptcha_size . '" data-callback="successCallback_' . $form_id . '" data-expired-callback="expireCallback_' . $form_id . '"	></div><input type="hidden" name="recaptcha-' . $form_id . '-response" id="recaptcha-' . $form_id . '-response" value="' . ( isset( $error_class['unverified_recaptcha'] ) ? 'failed' : '' ) . '"></div>';
				$script        = $this->checkbox_script( $form_id );

				// Load scripts that call grecaptcha before the reCAPTCHA API loads to avoid race conditions.
				wp_add_inline_script( 'google-recaptcha', $script, 'before' );

			}
		}

		return $captcha_input;
	}

	/**
	 * The script to be used when the reCAPTCHA V3 is selected.
	 *
	 * @since 1.2.0
	 *
	 * @param int $form_id The ID of the form.
	 *
	 * @return string The script that call grecaptcha.
	 */
	protected function v3_script( $form_id ) {

		$util     = new SimpleForm_ReCaptcha_Util();
		$site_key = $util->get_sform_option( 1, 'settings', 'recaptcha_site_key', '' );

		$script = '
		// Request a reCAPTCHA token, not on page load, but just before form submission.
		var recaptcha_token = null;
		// If you use more than one form, find all reCAPTCHA widgets in the document.
		var recaptcha_widgets = document.getElementsByName("g-recaptcha-response");
		var button_' . $form_id . ' = document.getElementById( "submission-' . $form_id . '" );

		// Check if the element is present in the DOM.
		if ( button_' . $form_id . ' != null ) {

			button_' . $form_id . '.addEventListener( "click", function( event ) {

				// Prevent the form submission without a valid reCAPTCHA token (validity time of two minutes).
				if ( ! recaptcha_token ) {

					event.preventDefault();

					// Call grecaptcha and request a token.
					grecaptcha.ready( function() {
						grecaptcha.execute( "' . $site_key . '", { action: "submit_form" } ).then( function( token ) {
							recaptcha_widgets.forEach( widget => {
								widget.value = token;
							})
							recaptcha_token = token;
						});
					});
					
					// Is it necessary to wait to receive the token. Once obtained, automatically submit the form.
					setTimeout( function(){
						button_' . $form_id . '.click();
					}, 1000);

				} else {

					// The reCAPTCHA token expire after two minutes. It is necessary refresh it.
					setTimeout(function(){ 
						recaptcha_widgets.forEach( widget => {
							widget.value = "";
						})
						recaptcha_token = null;
					}, 120000);

					return true;

				}

			});

		}';

		return $script;
	}

	/**
	 * The script to be used when the reCAPTCHA V2 invisible is selected.
	 *
	 * @since 1.2.0
	 *
	 * @param int $form_id The ID of the form.
	 *
	 * @return string The script that call grecaptcha.
	 */
	protected function invisible_script( $form_id ) {

		$script = '
		// Request a reCAPTCHA token, not on page load, but just before form submission.
		var recaptcha_token = null;
		var button_' . $form_id . ' = document.getElementById( "submission-' . $form_id . '" );

		// Check if the element is present in the DOM.
		if ( button_' . $form_id . ' != null ) {

			button_' . $form_id . '.addEventListener( "click", function( event ) {

				// Prevent the form submission without a valid reCAPTCHA token (validity time of two minutes).
				if ( ! recaptcha_token ) {
					event.preventDefault();
				}

				// Call grecaptcha and request a token.
				grecaptcha.execute();

			});

		}				

		// Is it necessary to wait to receive the token. Once obtained, automatically submit the form.
		var onSubmit_' . $form_id . ' = function() {
			recaptcha_token = grecaptcha.getResponse();
			setTimeout( function(){
				button_' . $form_id . '.click();
			}, 1000);
		};

		// Automatically prevent the form submission if the reCAPTCHA response expires and the user needs to re-verify.
		var onExpire_' . $form_id . ' = function() {
			recaptcha_token = null;
		};';

		return $script;
	}

	/**
	 * The script to be used when the reCAPTCHA V2 checkbox is selected.
	 *
	 * @since 1.2.0
	 *
	 * @param int $form_id The ID of the form.
	 *
	 * @return string The script that call grecaptcha.
	 */
	protected function checkbox_script( $form_id ) {

		$util             = new SimpleForm_ReCaptcha_Util();
		$unverified_error = $util->get_sform_option( 1, 'settings', 'unverified_recaptcha', __( 'Please prove you are not a robot', 'simpleform-recaptcha' ) );
		$expired_error    = $util->get_sform_option( 1, 'settings', 'expired_recaptcha', __( 'reCAPTCHA response expired, please answer again!', 'simpleform-recaptcha' ) );

		$script = '
		// Get the form error message.
		var error_' . $form_id . ' = document.getElementById( "error-message-' . $form_id . '" );
		var button_' . $form_id . ' = document.getElementById( "submission-' . $form_id . '" );

		// Automatically submit the form if the reCAPTCHA challenge is passed and no errors are detected.
		var successCallback_' . $form_id . ' = function() {
			if ( error_' . $form_id . '.classList.contains( "v-visible" ) && ( error_' . $form_id . '.innerText.trim() == "' . $expired_error . '" || error_' . $form_id . '.innerText.trim() == "' . $unverified_error . '" ) ) {
				error_' . $form_id . '.classList.remove( "v-visible" );
				if ( document.getElementById( "form-' . $form_id . '" ).classList.contains( "was-validated" ) ) {
					button_' . $form_id . '.click();
				}
			}
		};

		// Automatically display an error if the reCAPTCHA response expires and no other errors are detected.
		var expireCallback_' . $form_id . ' = function() {
			document.getElementById( "recaptcha-' . $form_id . '-response" ).value = "expired";
			if ( ! error_' . $form_id . '.classList.contains( "v-visible" ) ) {
				error_' . $form_id . '.classList.add( "v-visible" );
				error_' . $form_id . '.innerText = "' . $expired_error . '";
				setTimeout( function() {
					document.getElementById( "errors-' . $form_id . '" ).focus();
				}, 1000);
			}
		};';

		return $script;
	}

	/**
	 * Validate submitted data by invoking the reCAPTCHA challenge.
	 *
	 * @since   1.0.0
	 * @version 1.2.0
	 *
	 * @param string|string[] $errors      The value to filter.
	 * @param int             $form_id     The ID of the form.
	 * @param string          $captcha_question        The sanitized name value entered in the form.
	 * @param string          $captcha_answer       The sanitized email value entered in the form.
	 *
	 * @return string|mixed[] The error found after form submission.
	 */
	public function invoke_challenge( $errors, $form_id, $captcha_question, $captcha_answer ) {

		$util         = new SimpleForm_ReCaptcha_Util();
		$captcha_type = $util->get_sform_option( $form_id, 'attributes', 'captcha_type', 'math' );
		$recaptcha    = $util->get_sform_option( 1, 'settings', 'recaptcha', false );
		$more_errors  = strval( $util->get_sform_option( $form_id, 'settings', 'empty_fields', __( 'There were some errors that need to be fixed', 'simpleform' ) ) );

		if ( $recaptcha && 'recaptcha' === $captcha_type ) {

			$recaptcha_type       = $util->get_sform_option( 1, 'settings', 'recaptcha_type', 'v2' );
			$unverified_recaptcha = strval( $util->get_sform_option( 1, 'settings', 'unverified_recaptcha', __( 'Please prove you are not a robot', 'simpleform-recaptcha' ) ) );
			$invalid_recaptcha    = strval( $util->get_sform_option( 1, 'settings', 'invalid_recaptcha', __( 'Robot verification failed, please try again', 'simpleform-recaptcha' ) ) );
			$user_response        = $util->get_token();

			// Token can be used only once to get the response from google Api. Otherwise you get a timeout-or-duplicate error.
			if ( empty( $errors ) ) {

				if ( 'v2' === $recaptcha_type && empty( $user_response ) ) {

					// $errors is an array if ajax enabled.
					if ( is_array( $errors ) ) {
						$errors['notice']      = $this->display_multiple_error( $errors, $unverified_recaptcha, $more_errors );
						$errors['error']       = true;
						$errors['showerror']   = true;
						$errors['field_focus'] = false;
					} else {
						$errors = $form_id . ';unverified_recaptcha;';
					}
				} elseif ( $this->recaptcha_verification( $user_response ) ) {

					// $errors is an array if ajax enabled.
					if ( is_array( $errors ) ) {
						$errors['notice']      = $this->display_multiple_error( $errors, $invalid_recaptcha, $more_errors );
						$errors['error']       = true;
						$errors['showerror']   = true;
						$errors['field_focus'] = false;
					} else {
						$errors = $form_id . ';recaptcha;';
					}
				}
			}
		} else {

			// Validate the captcha value entered in the form.
			$errors = $this->captcha_field_validation( $errors, $form_id, $captcha_question, $captcha_answer );

		}

		return $errors;
	}

	/**
	 * Displaying of multiple errors message
	 *
	 * @since 1.2.0
	 *
	 * @param string[] $errors      The list of errors found during form validation.
	 * @param string   $error       The error message to show.
	 * @param string   $more_errors The multiple errors message.
	 *
	 * @return string The error message to show.
	 */
	protected function display_multiple_error( $errors, $error, $more_errors ) {

		if ( ! isset( $errors['error'] ) ) {
			$error_to_show = $error;
		} else {
			$error_to_show = $more_errors;
		}

		return $error_to_show;
	}

	/**
	 * Validate the captcha value entered in the form.
	 *
	 * @since 1.2.0
	 *
	 * @param string|string[] $errors           The value to filter.
	 * @param int             $form_id          The ID of the form.
	 * @param string          $captcha_question The sanitized name value entered in the form.
	 * @param string          $captcha_answer   The sanitized email value entered in the form.
	 *
	 * @return string|mixed[] The error found after form submission.
	 */
	protected function captcha_field_validation( $errors, $form_id, $captcha_question, $captcha_answer ) {

		$util          = new SimpleForm_ReCaptcha_Util();
		$captcha_error = $util->get_sform_option( $form_id, 'settings', 'invalid_captcha', __( 'Please enter a valid captcha value', 'simpleform' ) );
		$error         = strval( $util->get_sform_option( $form_id, 'settings', 'captcha_error', __( 'Error occurred validating the captcha', 'simpleform' ) ) );
		$out_error     = $util->get_sform_option( $form_id, 'settings', 'outside_error', 'bottom' );
		$showerror     = 'none' === $out_error ? true : false;
		$more_errors   = strval( $util->get_sform_option( $form_id, 'settings', 'empty_fields', __( 'There were some errors that need to be fixed', 'simpleform' ) ) );

		if ( $captcha_question !== $captcha_answer ) {

			// $errors is an array if ajax enabled.
			if ( is_array( $errors ) ) {
				$errors['notice']    = $this->display_multiple_error( $errors, $error, $more_errors );
				$errors['error']     = true;
				$errors['showerror'] = $showerror;
				$errors['captcha']   = $captcha_error;
			} else {
				$errors .= $form_id . ';captcha;';
			}
		}

		return $errors;
	}

	/**
	 * Verify the user's response to a reCAPTCHA challenge.
	 *
	 * @since   1.0.0
	 * @version 1.2.0
	 *
	 * @param string $user_response The user’s response token.
	 *
	 * @return bool True, if a bot is detected. False otherwise.
	 */
	protected function recaptcha_verification( $user_response ) {

		$util           = new SimpleForm_ReCaptcha_Util();
		$recaptcha_type = $util->get_sform_option( 1, 'settings', 'recaptcha_type', 'v2' );
		$secret_key     = $util->get_sform_option( 1, 'settings', 'recaptcha_secret_key', '' );
		$threshold      = $util->get_sform_option( 1, 'settings', 'recaptcha_threshold', '0.5' );
		$bot            = false;

		/*
		Remove google cookies except for _GRECAPTCHA.
		$google_url = 'https://www.recaptcha.net/recaptcha/api/siteverify';
		*/

		// Builds URL query.
		$google_url        = 'https://www.google.com/recaptcha/api/siteverify';
		$ip_address        = $util->get_ip();
		$query['secret']   = $secret_key;
		$query['response'] = $user_response;
		$query['remoteip'] = $ip_address;
		$query_string      = build_query( $query );
		$recaptcha_url     = strval( $google_url . '?' . $query_string );

		// Get a remote file.
		$recaptcha_file = wp_remote_get( $recaptcha_url );

		if ( is_wp_error( $recaptcha_file ) ) {

			$bot = false;

		} else {

			// Get only the body from the response.
			$response_body = wp_remote_retrieve_body( $recaptcha_file );
			// Read the content of body and decode JSON data to an associative array.
			$check_result = (array) json_decode( $response_body, true );

			if ( 'v3' !== $recaptcha_type ) {

				// Check if the user response token is not valid.
				if ( ! $check_result['success'] ) {
					$bot = true;
				}
			} else {

				// Check if the user response token is not valid or the score for this request is not acceptable.
				$bot = $check_result['success'] && 'submit_form' === $check_result['action'] && $check_result['score'] >= $threshold ? false : true;

			}
		}

		return $bot;
	}

	/**
	 * Display an error when reCAPTCHA challenge failed and Ajax is not enabled.
	 *
	 * @since   1.0.0
	 * @version 1.2.0
	 *
	 * @param string   $error       The value to filter.
	 * @param string[] $error_class The list of errors found.
	 *
	 * @return string The error found.
	 */
	public function error_detection( $error, $error_class ) {

		if ( ! isset( $error_class['duplicate_form'] ) && ! isset( $error_class['form_honeypot'] ) && ! isset( $error_class['spam'] ) ) {

			$util             = new SimpleForm_ReCaptcha_Util();
			$unverified_error = strval( $util->get_sform_option( 1, 'settings', 'unverified_recaptcha', __( 'Please prove you are not a robot', 'simpleform-recaptcha' ) ) );
			$expired_error    = strval( $util->get_sform_option( 1, 'settings', 'expired_recaptcha', __( 'reCAPTCHA response expired, please answer again!', 'simpleform-recaptcha' ) ) );
			$invalid_error    = strval( $util->get_sform_option( 1, 'settings', 'invalid_recaptcha', __( 'Robot verification failed, please try again', 'simpleform-recaptcha' ) ) );

			if ( isset( $error_class['unverified_recaptcha'] ) ) {
				$error = $unverified_error;
			}

			if ( isset( $error_class['expired_recaptcha'] ) ) {
				$error = $expired_error;
			}

			if ( isset( $error_class['recaptcha'] ) ) {
				$error = $invalid_error;
			}
		}

		return $error;
	}
}
