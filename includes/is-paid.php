<?php


	/**
	 * Set `gmt_invoice_paid` post meta when invoice is first created
	 */
	function gmt_donation_invoices_set_is_paid( $post_id ) {
		if ( get_post_type( $post_id ) !== 'gmt_donate_invoices' ) return;
		update_post_meta( $post_id, 'gmt_invoice_paid', false );
		update_post_meta( $post_id, 'gmt_invoice_payment_method', false );
	}
	add_action( 'new_to_publish', 'gmt_donation_invoices_set_is_paid' );
	add_action( 'auto-draft_to_publish', 'gmt_donation_invoices_set_is_paid' );



	/**
	 * Mark invoice as paid
	 * @param  integer $id    The invoice ID
	 * @param  array $status  The invoice payment details
	 */
	function gmt_donation_invoices_mark_as_paid( $id, $status ) {
		update_post_meta( $id, 'gmt_invoice_paid', current_time( 'timestamp' ) );
		update_post_meta( $id, 'gmt_invoice_payment_method', $status['type'] );
	}
	add_action( 'gmt_donation_invoice_success', 'gmt_donation_invoices_mark_as_paid', 10, 2 );