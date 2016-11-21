<?php


	/**
	 * Send email invoice
	 * @param  integer $amount  The invoice amount
	 * @param  array   $details The invoice details
	 */
	function gmt_donation_invoices_send_invoice_email( $id ) {

		// Invoice details
		$details_saved = get_post_meta( $id, 'gmt_invoice_details', true );
		$details_defaults = gmt_donation_invoices_metabox_details_defaults();
		$details = wp_parse_args( $details_saved, $details_defaults );
		$amount = get_post_meta( $id, 'gmt_invoice_amount', true );

		// Don't send if there's no amount or recipient specified
		if ( empty( $details['recipient'] ) || empty( $amount ) ) return;

		// Settings and variables
		$options = gmt_donations_get_theme_options();
		$currencies = gmt_donations_settings_field_currency_choices();
		$site_name = get_bloginfo('name');
		$domain = gmt_donations_get_site_domain();
		$headers = 'From: ' . $site_name . ' <donotreply@' . $domain . '>' . "\r\n";

		// Email content
		$subject = str_replace( '[amount]', $currencies[$options['currency']]['symbol'] . number_format( $amount, 2 ), $details['receipt_subject'] );
		$message = str_replace( '[amount]', $currencies[$options['currency']]['symbol'] . number_format( $amount, 2 ), $details['receipt_message'] );

		// Send email
		return wp_mail( sanitize_email( $details['recipient'] ), $subject, $message, $headers );

	}
	add_action( 'gmt_donation_success', 'gmt_donations_send_in_honor_honoree_email', 10, 2 );



	/**
	 * Receipt for invoice payment
	 * @param  integer $id     The form ID
	 * @param  array   $status The donation data
	 */
	function gmt_donation_invoice_receipt_email( $id, $status ) {

		// Variables
		$details_saved = get_post_meta( $id, 'gmt_invoice_details', true );
		$details_defaults = gmt_donations_metabox_details_defaults();
		$details = wp_parse_args( $details_saved, $details_defaults );

		// Send email
		if ( $details['send_receipt'] === 'off' || empty( $details['receipt_subject'] ) || empty( $details['receipt_message'] ) ) return;

		// Variables
		$options = gmt_donations_get_theme_options();
		$currencies = gmt_donations_settings_field_currency_choices();
		$site_name = get_bloginfo('name');
		$domain = gmt_donations_get_site_domain();
		$headers = 'From: ' . $site_name . ' <donotreply@' . $domain . '>' . "\r\n";

		// Email content
		$subject = str_replace( '[amount]', $currencies[$options['currency']]['symbol'] . number_format( $status['amount'], 2 ), $details['receipt_subject'] );
		$message = str_replace( '[amount]', $currencies[$options['currency']]['symbol'] . number_format( $status['amount'], 2 ), $details['receipt_message'] );

		// Send email
		@wp_mail( sanitize_email( $status['email'] ), $subject, $message, $headers );

	}
	add_action( 'gmt_donations_invoice_success', 'gmt_donation_invoice_receipt_email', 10, 2 );