/**
 * JavaScript code delegated to the backend functionality of the plugin.
 *
 * @package SimpleForm reCAPTCHA
 * @subpackage SimpleForm reCAPTCHA/admin
 */

(function( $ ) {
	'use strict';

	$( window ).on(
		'load',
		function() {

			$( '#math-captcha' ).on(
				'click',
				function() {
					$( '.trcaptcha.clabel' ).removeClass( 'unseen' );
				}
			);

			$( '#google-captcha' ).on(
				'click',
				function() {
					$( '.trcaptcha.clabel' ).addClass( 'unseen' );
				}
			);

			$( '#captcha_field' ).on(
				'change',
				function() {
					var selectVal = $( '#captcha_field option:selected' ).val();
					if ( selectVal == 'hidden' ) {
						$( '.trcaptcha' ).addClass( 'unseen' );
					} else {
						$( '.trcaptcha' ).removeClass( 'unseen' );
						if ( $( '#math-captcha' ).prop( 'checked' ) == true ) {
							$( '.trcaptcha.clabel' ).removeClass( 'unseen' );
						} else {
							$( '.trcaptcha.clabel' ).addClass( 'unseen' );
						}
					}
				}
			);

			$( '#recaptcha' ).on(
				'click',
				function() {
					if ( $( this ).prop( 'checked' ) == true ) {
						$( '#tdrecaptcha' ).removeClass( 'last' );
						$( '.trrecaptcha' ).removeClass( 'unseen' );
						if ( $( '#v2-checkbox' ).prop( 'checked' ) == true ) {
							$( '.trrecaptcha.style' ).removeClass( 'unseen' );
							$( '.trrecaptcha.badge' ).addClass( 'unseen' );
							$( '.trrecaptcha.threshold' ).addClass( 'unseen' );
							$( '.trrecaptcha.usage' ).addClass( 'unseen' );
						} else {
							$( '.trrecaptcha.style' ).addClass( 'unseen' );
							$( '.trrecaptcha.badge' ).removeClass( 'unseen' );
							if ( $( '#v3' ).prop( 'checked' ) == true ) {
								$( '.trrecaptcha.threshold' ).removeClass( 'unseen' );
							} else {
								$( '.trrecaptcha.threshold' ).addClass( 'unseen' );
							}
							if ( $( '#recaptcha-badge' ).prop( 'checked' ) == true ) {
								$( '#thbadge, #tdbadge' ).removeClass( 'last' );
								$( '.trrecaptcha.usage' ).removeClass( 'unseen' );
							} else {
								$( '#thbadge, #tdbadge' ).addClass( 'last' );
								$( '.trrecaptcha.usage' ).addClass( 'unseen' );
							}
						}
					} else {
						$( '.trrecaptcha' ).addClass( 'unseen' );
						$( '#tdrecaptcha' ).addClass( 'last' );
					}
				}
			);

			$( '#v2-checkbox' ).on(
				'click',
				function() {
					$( 'span.rctype' ).html( sform_recaptcha_object.v2 );
					$( '.trrecaptcha.style, .trrecaptcha.v2' ).removeClass( 'unseen' );
					$( '.trrecaptcha.threshold, .trrecaptcha.badge, .trrecaptcha.usage' ).addClass( 'unseen' );
				}
			);

			$( '#v2-invisible' ).on(
				'click',
				function() {
					$( 'span.rctype' ).html( sform_recaptcha_object.v2i );
					$( '.trrecaptcha.badge' ).removeClass( 'unseen' );
					$( '.trrecaptcha.threshold, .trrecaptcha.style, .trrecaptcha.v2' ).addClass( 'unseen' );
					if ( $( '#recaptcha-badge' ).prop( 'checked' ) == true ) {
						$( '#thbadge, #tdbadge' ).removeClass( 'last' );
						$( '.trrecaptcha.usage' ).removeClass( 'unseen' );
					} else {
						$( '#thbadge, #tdbadge' ).addClass( 'last' );
						$( '.trrecaptcha.usage' ).addClass( 'unseen' );
					}
				}
			);

			$( '#v3' ).on(
				'click',
				function() {
					$( 'span.rctype' ).html( sform_recaptcha_object.v3 );
					$( '.trrecaptcha.threshold, .trrecaptcha.badge' ).removeClass( 'unseen' );
					$( '.trrecaptcha.style, .trrecaptcha.v2' ).addClass( 'unseen' );
					if ( $( '#recaptcha-badge' ).prop( 'checked' ) == true ) {
						$( '#thbadge, #tdbadge' ).removeClass( 'last' );
						$( '.trrecaptcha.usage' ).removeClass( 'unseen' );
					} else {
						$( '#thbadge, #tdbadge' ).addClass( 'last' );
						$( '.trrecaptcha.usage' ).addClass( 'unseen' );
					}
				}
			);

			$( '#recaptcha-badge' ).on(
				'click',
				function() {
					if ( $( this ).prop( 'checked' ) == true ) {
						$( '#thbadge, #tdbadge' ).removeClass( 'last' );
						$( '.trrecaptcha.usage' ).removeClass( 'unseen' );
					} else {
						$( '#thbadge, #tdbadge' ).addClass( 'last' );
						$( '.trrecaptcha.usage' ).addClass( 'unseen' );
					}
				}
			);

		}
	);

})( jQuery );
