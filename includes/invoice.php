<?php


	/**
	 * Create invoice donate buttons
	 * @param  string $button Which button to create (`stripe` or `paypal`)
	 * @param  array  $form   The form data
	 * @return string         The button markup
	 */
	function gmt_donation_invoices_create_donation_form_buttons( $button, $form ) {

		// If the Stripe gateway is activated
		if ( $button === 'stripe' && $form['options']['gateways_stripe'] === 'on' ) {
			return
				'<button ' .
					'type="submit" ' .
					'class="gmt-donation-form-button gmt-donation-invoice-button gmt-donation-form-button-stripe gmt-donation-invoice-button-stripe" ' .
					'id="gmt-donation-invoice-button-stripe-' . esc_attr( $form['id'] ) . '" ' .
					'data-business-name="' . stripslashes( esc_attr( $form['options']['business_name'] ) ) . '" ' .
					'data-image="' . esc_attr( $form['options']['business_logo'] ) . '" ' .
					'data-description="' . get_the_title( $form['id'] ) . '" ' .
					'data-amount="' . ( $form['details']['amount'] * 100 ) . '"' .
					'data-panel-label="' . __( 'Donate', 'gmt_donations' ) . '"' .
					'data-loading="' . plugins_url( 'js/loading.gif' , __FILE__ ) . '"' .
					'disabled' .
				'>' .
					stripslashes( $form['options']['stripe_button_label'] ) .
				'</button> ';
		}

		// If the PayPal gateway is activated
		if ( $button === 'paypal' && $form['options']['gateways_paypal_express_checkout'] === 'on' ) {
			return
				'<button ' .
					'type="submit" ' .
					'class="gmt-donation-form-button gmt-donation-invoice-button gmt-donation-form-button-paypal gmt-donation-invoice-button-paypal" ' .
					'id="gmt-donation-invoice-button-paypal-' . esc_attr( $form['id'] ) . '" ' .
				'>' .
					stripslashes( $form['options']['paypal_button_label'] ) .
				'</button>';
		}

		return '';

	}



	/**
	 * Create the invoice
	 * @param  array $args  The form arguments and attributes
	 * @return string       The rendered form markup
	 */
	function gmt_donation_invoices_create_invoice( $form ) {

		// Make sure values are provided
		if ( empty( $form['details'] ) || !array_key_exists( 'amount', $form['details'] ) || empty( $form['details']['amount'] ) ) return;

		// Messages
		$status_messages = gmt_donation_form_get_status_message();
		$get_status = !empty( $form['status'] ) && array_key_exists( $form['status'], $status_messages ) ? $status_messages[$form['status']] : null;

		// Create buttons
		$button_stripe = gmt_donation_invoices_create_donation_form_buttons( 'stripe', $form );
		$button_paypal = gmt_donation_invoices_create_donation_form_buttons( 'paypal', $form );

		// Currencies
		$currencies = gmt_donations_settings_field_currency_choices();

		// Create invoice
		return
			( $form['options']['api_mode'] === 'test' ? '<div class="gmt-donation-form-test-mode gmt-donations-invoice-test-mode">' . __( 'You are in test/sandbox mode.', 'gmt_donations' ) . '</div>' : '' ) .

			'<div class="gmt-donation-invoice-message" id="gmt-donation-invoice-message-' . $form['id'] . '">' .
				wpautop( stripslashes( str_replace( '[amount]', $currencies[$form['options']['currency']]['symbol'] . number_format( $form['details']['amount'], 2 ), $form['details']['invoice_text'] ) ), true ) .
			'</div>' .

			'<div class="gmt-donation-invoice-amount" id="gmt-donation-invoice-amount-' . $form['id'] . '">' .
				'<span class="gmt-donation-invoice-amount-label" id="gmt-donation-invoice-amount-label-' . $form['id'] . '">' . __( 'Amount', 'gmt_donations' ) . ':</span> <span class="gmt-donation-invoice-amount-value" id="gmt-donation-invoice-amount-value-' . $form['id'] . '">' . $currencies[$form['options']['currency']]['symbol'] . esc_html( number_format( $form['details']['amount'], 2 ) ) . '</span>' .
			'</div>' .

			'<form class="gmt-donation-form gmt-donation-invoice" id="gmt-donation-invoice-' . esc_attr( $form['id'] ) . '" name="gmt-donation-invoice-' . esc_attr( $form['id'] ) . '" action="" method="post">' .

				'<div class="gmt-donation-form-tarpit">' .
					'<label for="gmt_donation_invoice_email">' . __( 'Email', 'gmt_donations' ) . '</label>' .
					'<input type="email" id="gmt_donation_invoice_email" name="gmt_donation_invoice[email]" value="">' .
				'</div>' .

				'<div class="gmt-donation-form-alert gmt-donation-invoice-alert" id="gmt-donation-invoice-alert-' . esc_attr( $form['id'] ) . '" tabindex="-1" style="outline: 0;' . ( empty( $get_status ) ? 'display: none; visibility: hidden;' : '' ) . '">' .
					( empty( $get_status ) ? '' : $get_status ) .
				'</div>' .

				'<div class="gmt-donation-form-actions gmt-donation-invoice-actions" id="gmt-donation-invoice-actions-' . esc_attr( $form['id'] ) . '">' .
					$button_stripe .
					$button_paypal .
				'</div>' .

				wp_nonce_field( 'gmt_donation_invoice_nonce', 'gmt_donation_invoice_process', true, false ) .
				'<input type="hidden" name="gmt_donation_invoice[id]" value="' . esc_attr( $form['id'] ) . '">' .
				'<input type="hidden" id="gmt_donations_tarpit_time" name="gmt_donation_invoice[tarpit_time]" value="' . esc_attr( current_time( 'timestamp' ) ) . '">' .
				'<input type="hidden" data-gmt-donations-stripe-key="' . $form['options']['stripe_' . $form['options']['api_mode'] . '_publishable'] . '">' .

			'</form>';

	}



	/**
	 * Create the payment confirmation message
	 * @param  array $details The form details
	 * @param  array $status  The donation details
	 * @return string         Confirmation message markup
	 */
	function gmt_donation_invoices_create_confirmation_message( $details, $status ) {

		// Get the currency
		$options = gmt_donations_get_theme_options();
		$currencies = gmt_donations_settings_field_currency_choices();

		// Create the message
		return str_replace( '[email]', $status['email'], str_replace( '[amount]', $currencies[$options['currency']]['symbol'] . $status['amount'], $details['confirmation'] ) );

	}



	/**
	 * Create invoice content
	 * @return string Invoice markup
	 */
	function gmt_donations_invoice( $content ) {

		if ( !is_singular( 'gmt_donate_invoices' ) ) return $content;

		// Prevent this content from caching
		define('DONOTCACHEPAGE', TRUE);

		// Variables
		global $post;
		$options = gmt_donations_get_theme_options();
		$details_saved = get_post_meta( $post->ID, 'gmt_invoice_details', true );
		$details_defaults = gmt_donations_metabox_details_defaults();
		$details = wp_parse_args( $details_saved, $details_defaults );
		$details['amount'] = get_post_meta( $post->ID, 'gmt_invoice_amount', true );
		$status = gmt_donations_get_session( 'gmt_invoice_status', true );

		// Display invoice confirmation message
		if ( !empty( get_post_meta( $post->ID, 'gmt_invoice_paid', true ) ) || $status['status'] === 'success' ) {
			return wpautop( stripslashes( gmt_donation_invoices_create_confirmation_message( $details, $status ) ), true );
		}

		// Generate form
		return gmt_donation_invoices_create_invoice(array(
			'id' => $post->ID,
			'options' => $options,
			'details' => $details,
			'status' => $status['status'],
		));

	}
	add_filter( 'the_content', 'gmt_donations_invoice' );



	/**
	 * Get the donor's email from the Stripe token
	 */
	function gmt_donation_invoices_get_email_from_stripe( $token, $status ) {

		// If checkout failed, return null
		if ( $status === 'failed' ) return null;

		// Variables
		$options = gmt_donations_get_theme_options();

		// Get email from token
		try {
			\Stripe\Stripe::setApiKey( $options['stripe_' . $options['api_mode'] . '_secret']);
			$stripeinfo = \Stripe\Token::retrieve( $token );
			$email = $stripeinfo->email;
		} catch (Exception $e) {
			return null;
		}
		return $email;

	}


	/**
	 * Process Stripe Donation
	 */
	function gmt_donation_invoices_process_stripe( $token, $amount ) {

		// Variables
		$options = gmt_donations_get_theme_options();

		// Charge card
		try {
			\Stripe\Stripe::setApiKey( $options['stripe_' . $options['api_mode'] . '_secret'] );
			$charge = \Stripe\Charge::create(array(
					'amount' => $amount * 100,
					'currency' => $options['currency'],
					'card' => $token,
				)
			);
		} catch (Exception $e) {
			return 'failed';
		}
		return 'success';

	}



	/**
	 * Process PayPal payment
	 * @return [type] [description]
	 * @todo Verify this all still works
	 */
	function gmt_donation_invoices_process_paypal() {

		// Get PayPal token
		$paypal = gmt_donations_get_session( 'gmt_donation_invoices_paypal_data', true );

		// Check to see if token exists and buyer has approved transaction
		if ( empty( $paypal ) || empty( $paypal['token'] ) || !isset( $_GET['PayerID'] ) ) return;

		// Variables
		$options = gmt_donations_get_theme_options();
		$credentials = gmt_donations_get_paypal_credentials( $options );
		$referer = gmt_donations_clean_url( 'PayerID', gmt_donations_clean_url( 'token', $paypal['referer'] ) );
		$error = gmt_donations_clean_url( 'PayerID', gmt_donations_clean_url( 'token', $paypal['error'] ) );

		// Get checkout data from PayPal
		$getCheckoutArgs = array(
			'METHOD' => 'GetExpressCheckoutDetails',
			'TOKEN' => $paypal['token'],
		);
		$checkoutResponse = gmt_paypal_call_api( $getCheckoutArgs, $credentials, $options['api_mode'] );

		// Make sure response didn't fail
		if ( is_wp_error( $checkoutResponse ) || $checkoutResponse['ACK'] !== 'Success' || !array_key_exists( 'PAYERID', $checkoutResponse ) ) {
			gmt_donations_set_session( 'gmt_invoice_status', array( 'status' => 'failed' ) );
			wp_safe_redirect( $error );
			exit;
		}

		// Complete checkout
		$doCheckoutArgs = array(
			'METHOD' => 'DoExpressCheckoutPayment',
			'TOKEN' => $paypal['token'],
			'PAYERID' => $checkoutResponse['PAYERID'],
			'PAYMENTREQUEST_0_PAYMENTACTION' => 'SALE',
			'PAYMENTREQUEST_0_AMT' => $checkoutResponse['PAYMENTREQUEST_0_AMT'],
			'PAYMENTREQUEST_0_CURRENCYCODE' => strtoupper( $options['currency'] ),
		);
		$doCheckoutResponse = gmt_paypal_call_api( $doCheckoutArgs, $credentials, $options['api_mode'] );

		// Verify that payment was completed
		if ( is_wp_error( $doCheckoutResponse ) || $doCheckoutResponse['ACK'] !== 'Success' ) {
			gmt_donations_set_session( 'gmt_invoice_status', array( 'status' => 'failed' ) );
			wp_safe_redirect( $error );
			exit;
		}

		// If payment was successful, display success message
		$status = array(
			'type' => 'paypal',
			'status' => 'success',
			'amount' => $checkoutResponse['AMT'],
			'email' => $checkoutResponse['EMAIL'],
		);
		gmt_donations_set_session( 'gmt_invoice_status', $status );

		// Emit action hook
		do_action( 'gmt_donations_invoice_success', $paypal['id'], $status );

		wp_safe_redirect( $referer );
		exit;

	}
	add_action( 'init', 'gmt_donation_invoices_process_paypal' );



	/**
	 * Process PayPal Donation
	 * @param array $paypal The PayPal and charge data
	 * @todo  Verify this all still works
	 */
	function gmt_donation_invoices_get_paypal_authorization( $paypal ) {

		// Variables
		$options = gmt_donations_get_theme_options();
		$currencies = gmt_donations_settings_field_currency_choices();
		$credentials = gmt_donations_get_paypal_credentials( $options );

		// Clean URLs
		$referer = gmt_donations_clean_url( 'PayerID', gmt_donations_clean_url( 'token', $paypal['referer'] ) );
		$error = gmt_donations_clean_url( 'PayerID', gmt_donations_clean_url( 'token', $paypal['error'] ) );

		// Request token
		$setCheckoutArgs = array(
			'METHOD' => 'SetExpressCheckout',
			'RETURNURL' => esc_url_raw( $referer ),
			'CANCELURL' => esc_url_raw( $referer ),
			'PAYMENTREQUEST_0_PAYMENTACTION' => 'Authorization',
			'PAYMENTREQUEST_0_AMT' => number_format( $paypal['amount'], 2 ),
			'PAYMENTREQUEST_0_DESC' => 'Invoice: ' . $paypal['title'],
			'PAYMENTREQUEST_0_CURRENCYCODE' => strtoupper( $options['currency'] ),
			'SOLUTIONTYPE' => 'Sole',
			'NOSHIPPING' => 1,
			'ALLOWNOTE' => 0,
			'PAGESTYLE' => $options['paypal_page_style'],
		);
		$setCheckoutResponse = gmt_paypal_call_api( $setCheckoutArgs, $credentials, $options['api_mode'] );

		// If response is not successful, display an error message
		if ( is_wp_error( $setCheckoutResponse ) || $setCheckoutResponse['ACK'] !== 'Success' ) {
			gmt_donations_set_session( 'gmt_invoice_status', array( 'status' => 'failed' ) );
			wp_safe_redirect( esc_url_raw( $error ), 302 );
			exit;
		}

		// Store token for 24 minutes
		$paypal['token'] = $setCheckoutResponse['TOKEN'];
		gmt_donations_set_session( 'gmt_donation_invoices_paypal_data', $paypal );

		// If response successful, send to PayPal for authorization.
		$getAuthorizationArgs = array(
			'token' => $setCheckoutResponse['TOKEN'],
			'useraction' => 'commit',
		);
		gmt_paypal_send_to_express_checkout( $getAuthorizationArgs, $options['api_mode'] );

	}



	/**
	 * Process donation form
	 */
	function gmt_donation_invoices_process_form() {

		// Check that form was submitted
		if ( !isset( $_POST['gmt_donation_invoice_process'] ) ) return;

		// Verify data came from proper screen
		if ( !wp_verify_nonce( $_POST['gmt_donation_invoice_process'], 'gmt_donation_invoice_nonce' ) ) {
			die( 'Security check' );
		}

		// Make sure donation form fields are provided
		if ( !isset( $_POST['gmt_donation_invoice'] ) ) return;

		// Variables
		$referer = gmt_donations_get_url();
		$error = $referer . '#gmt-donation-invoice-alert-' . $_POST['gmt_donation_invoice']['id'];

		// Verify honeypots
		if ( !empty( $_POST['gmt_donation_invoice']['email'] ) || !isset( $_POST['gmt_donation_invoice']['tarpit_time'] ) || current_time( 'timestamp' ) - $_POST['gmt_donation_invoice']['tarpit_time'] < 1 ) {
			gmt_donations_set_session( 'gmt_invoice_status', array( 'status' => 'failed' ) );
			wp_safe_redirect( $referer, 302 );
			exit;
		}

		// Verify that post is an invoice
		if ( !isset( $_POST['gmt_donation_invoice']['id'] ) || get_post_type( $_POST['gmt_donation_invoice']['id'] ) !== 'gmt_donate_invoices' ) {
			gmt_donations_set_session( 'gmt_invoice_status', array( 'status' => 'failed' ) );
			wp_safe_redirect( $referer, 302 );
			exit;
		}

		// Get the donation info
		$details_saved = get_post_meta( $_POST['gmt_donation_invoice']['id'], 'gmt_invoice_details', true );
		$details_defaults = gmt_donations_metabox_details_defaults();
		$details = wp_parse_args( $details_saved, $details_defaults );
		$details['amount'] = get_post_meta( $_POST['gmt_donation_invoice']['id'], 'gmt_invoice_amount', true );

		// Make sure an amount is provided
		if ( empty( $details['amount'] ) ) {
			gmt_donations_set_session( 'gmt_invoice_status', array( 'status' => 'failed' ) );
			wp_safe_redirect( $referer, 302 );
			exit;
		}

		// Charge card
		if ( isset( $_POST['stripe_token'] ) ) {
			$process = gmt_donation_invoices_process_stripe( $_POST['stripe_token'], $details['amount'] );
			$status = array(
				'type' => 'stripe',
				'status' => $process,
				'amount' => $details['amount'],
				'email' => gmt_donation_invoices_get_email_from_stripe( $_POST['stripe_token'], $process ),
			);
			gmt_donations_set_session( 'gmt_invoice_status', $status );
		} else {
			$paypal = array(
				'id' => $_POST['gmt_donation_invoice']['id'],
				'amount' => $details['amount'],
				'title' => get_the_title( $_POST['gmt_donation_invoice']['id'] ),
				'referer' => $referer,
				'error' => $error,
			);
			$process = gmt_donation_invoices_get_paypal_authorization( $paypal );
		}

		// If payment is successful, emit action hook
		if ( !empty( $status ) && array_key_exists( 'status', $status ) && $status['status'] === 'success' ) {
			do_action( 'gmt_donation_invoice_success', $_POST['gmt_donation_invoice']['id'], $status );
		}

		// Redirect user
		wp_safe_redirect( $referer, 302 );
		exit;

	}
	add_action( 'init', 'gmt_donation_invoices_process_form' );



	/**
	 * Load scripts conditionally
	 */
	function gmt_donation_invoices_load_front_end_scripts() {

		// Get the options
		$options = gmt_donations_get_theme_options();

		// Only run on invoices
		if ( !is_singular( 'gmt_donate_invoices' ) ) return;

		// If Stripe is active, load stripe scripts
		if ( $options['gateways_stripe'] === 'on' ) {
			wp_enqueue_script( 'stripe-checkout', 'https://checkout.stripe.com/checkout.js', null, false, true );
			wp_enqueue_script( 'gmt-donations-stripe', plugins_url( '../includes/js/gmt-donations-invoice-stripe.js' , __FILE__ ), array( 'stripe-checkout' ), false, true );
		}

	}
	add_action( 'wp_enqueue_scripts', 'gmt_donation_invoices_load_front_end_scripts' );



	/**
	 * Load styles conditionally
	 */
	function gmt_donation_invoices_load_front_end_styles() {

		// Only run on invoices
		if ( !is_singular( 'gmt_donate_invoices' ) ) return;
		?>
			<style type="text/css">
				.gmt-donation-form-tarpit{display:none;visibility:hidden;}
				.gmt-donations-loading{background-color:#000000;bottom:0;position:fixed;left:0;opacity:.6;right:0;top:0;z-index:9998;}
				.gmt-donations-loading-wrap{color:#ffffff;display:table;height:100%;text-align:center;width:100%;}
				.gmt-donations-loading-content{display:table-cell;vertical-align:middle;}
			</style>
		<?php
	}
	add_action( 'wp_head', 'gmt_donation_invoices_load_front_end_styles' );