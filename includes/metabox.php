<?php

	/**
	 * Create the metabox
	 */
	function gmt_donation_invoices_create_metaboxes() {
		add_meta_box( 'gmt_donation_invoices_details_metabox', 'Invoice Details', 'gmt_donation_invoices_render_details_metabox', 'gmt_donate_invoices', 'normal', 'default');
		add_meta_box( 'gmt_donation_invoices_payment_status_metabox', 'Payment Status', 'gmt_donation_invoices_render_payment_status_metabox', 'gmt_donate_invoices', 'side', 'high');
		add_meta_box( 'gmt_donation_invoices_emails_metabox', 'Emails', 'gmt_donation_invoices_render_emails_metabox', 'gmt_donate_invoices', 'side', 'default');
	}
	add_action( 'add_meta_boxes', 'gmt_donation_invoices_create_metaboxes' );



	/**
	 * Create the metabox detail default values
	 * @todo  update defaults
	 */
	function gmt_donation_invoices_metabox_details_defaults() {
		return array(

			// The Basics
			'recipient' => '',

			// Messages and emails
			'invoice_text' => sprintf( __( 'Please donate %s to %s.', 'gmt_donation_invoices' ), '[amount]', get_bloginfo( 'name' ) ),
			'email_subject' => __( 'Donation Invoice from', 'gmt_donations' ) . ' ' . get_bloginfo( 'name' ),
			'email_message' => sprintf( __( 'Please donate %s to %s. %s', 'gmt_donation_invoices' ), '[amount]', get_bloginfo( 'name' ), '[invoice]' ),
			'confirmation' => sprintf( __( 'Thank you for your donation of %s to %s.', 'gmt_donation_invoices' ), '[amount]', get_bloginfo( 'name' ) ),
			'send_receipt' => 'off',
			'receipt_subject' => __( 'Invoice Paid', 'gmt_donations' ) . ': ' . get_bloginfo( 'name' ),
			'receipt_message' => '',
		);
	}



	function gmt_donation_invoices_render_payment_status_metabox() {

		// Variables
		global $post;
		$is_paid = get_post_meta( $post->ID, 'gmt_invoice_paid', true );

		?>

			<fieldset>
				<?php if ( empty( $is_paid ) ) : ?>
					<?php _e( 'Not Paid', 'gmt_donations' ); ?>
				<?php else : ?>
					<?php echo date( 'F j, Y', $is_paid ); ?> <?php _e( 'at', 'gmt_donations' ); ?> <?php echo date( 'g:ia', $is_paid ); ?><br><?php echo esc_html( ucfirst( get_post_meta( $post->ID, 'gmt_invoice_payment_method', true ) ) ); ?>
				<?php endif; ?>
			</fieldset>

		<?php

	}




	function gmt_donation_invoices_render_emails_metabox() {

		// Variables
		global $post;
		$emails = get_post_meta( $post->ID, 'gmt_invoice_emails_sent', true );

		?>

			<fieldset>

				<label>
					<input type="checkbox" name="gmt_donations_invoice_send_email" value="on">
					<?php _e( 'Send an email to the invoice recipient', 'gmt_donations' ); ?>
				</label>

				<p><strong><?php _e( 'Emails Sent', 'gmt_donations' ); ?></strong></p>

				<?php if ( !is_array( $emails ) ) : ?>
					<?php _e( 'None', 'gmt_donations' ); ?>
				<?php else : ?>
					<ul>
					<?php
						$emails = array_reverse( $emails );
						foreach( $emails as $email ) :
					?>
						<li><?php if ( $email['status'] === 'failed' ) { echo __( 'Failed', 'gmt_donations' ) . ': '; } ?><?php echo date( 'F j, Y', $email['timestamp'] ) . ' - ' . date( 'g:ia', $email['timestamp'] ) ?></li>
					<?php endforeach; ?>
					</ul>
				<?php endif; ?>

			</fieldset>

		<?php

		// Security field
		wp_nonce_field( 'gmt_donation_invoice_emails_metabox_nonce', 'gmt_donation_invoice_emails_metabox_process' );

	}



	/**
	 * Render the forms metabox
	 */
	function gmt_donation_invoices_render_details_metabox() {

		// Variables
		global $post;
		$options = gmt_donations_get_theme_options();
		$currencies = gmt_donations_settings_field_currency_choices();
		$saved = get_post_meta( $post->ID, 'gmt_invoice_details', true );
		$defaults = gmt_donation_invoices_metabox_details_defaults();
		$details = wp_parse_args( $saved, $defaults );
		$amount = get_post_meta( $post->ID, 'gmt_invoice_amount', true );

		?>

			<fieldset>

				<h3><?php _e( 'The Basics', 'gmt_donation_invoices' ); ?></h3>

				<div>
					<label class="description" for="gmt_donation_invoice_amount"><?php _e( 'Invoice Amount', 'gmt_donation_invoices' ); ?></label><br>
					<?php echo $currencies[$options['currency']]['symbol']; ?> <input type="number" min="1" step="any" name="gmt_donation_invoice_amount" class="regular-text" id="gmt_donation_invoice_amount" value="<?php echo esc_attr( $amount ); ?>">
				</div>
				<br>

				<div>
					<label class="description" for="gmt_donation_invoice_recipient"><?php _e( 'Recipient Email', 'gmt_donation_invoices' ); ?></label><br>
					<input type="email" name="gmt_donation_invoice[recipient]" class="regular-text" id="gmt_donation_invoice_recipient" value="<?php echo esc_attr( $details['recipient'] ); ?>">
				</div>
				<br>

			</fieldset>

			<fieldset>

				<h3><?php _e( 'Messages and Emails', 'gmt_donation_invoices' ); ?></h3>

				<h4><?php _e( 'The Invoice', 'gmt_donation_invoices' ); ?></h4>

				<div>
					<label class="description" for="gmt_donation_invoice_invoice_text"><?php _e( 'Invoice text', 'gmt_donation_invoices' ); ?></label><br>
					<textarea name="gmt_donation_invoice[invoice_text]" class="large-text" id="gmt_donation_invoice_invoice_text" cols="50" rows="10"><?php echo stripslashes( esc_textarea( $details['invoice_text'] ) ); ?></textarea>
				</div>
				<br>

				<div>
					<label class="description" for="gmt_donation_invoice_email_subject"><?php printf( __( 'Email Subject. Use %s to dynamically add the invoice amount.', 'gmt_donation_invoices' ), '<code>[amount]</code>' ); ?></label><br>
					<input type="text" name="gmt_donation_invoice[email_subject]" class="large-text" id="gmt_donation_invoice_email_subject" value="<?php echo stripslashes( esc_attr( $details['email_subject'] ) ); ?>">
				</div>
				<br>

				<div>
					<label class="description" for="gmt_donation_invoice_email_message"><?php printf( __( 'Email Message. Use %s to dynamically add the invoice amount. Use %s to add a link to the invoice (required).', 'gmt_donation_invoices' ), '<code>[amount]</code>', '<code>[invoice]</code>' ); ?></label><br>
					<textarea name="gmt_donation_invoice[email_message]" class="large-text" id="gmt_donation_invoice_email_message" cols="50" rows="10"><?php echo stripslashes( esc_textarea( $details['email_message'] ) ); ?></textarea>
				</div>
				<br>

				<h4><?php _e( 'Confirmation and Receipt', 'gmt_donation_invoices' ); ?></h4>

				<div>
					<label class="description" for="gmt_donation_invoice_confirmation"><?php printf( __( 'Confirmation Message. Use %s to dynamically add the invoice amount, and %s to add the donor\'s email address.', 'gmt_donation_invoices' ), '<code>[amount]</code>', '<code>[email]</code>' ); ?></label><br>
					<textarea name="gmt_donation_invoice[confirmation]" class="large-text" id="gmt_donation_invoice_confirmation" cols="50" rows="10"><?php echo stripslashes( esc_textarea( $details['confirmation'] ) ); ?></textarea>
				</div>
				<br>

				<div>
					<label>
						<input type="checkbox" name="gmt_donation_invoice[send_receipt]" value="on" <?php checked( $details['send_receipt'], 'on' ); ?>>
						<?php _e( 'Send an invoice payment receipt', 'gmt_donation_invoices' ); ?>
					</label>
				</div>
				<br>

				<div>
					<label class="description" for="gmt_donation_invoice_receipt_subject"><?php printf( __( 'Receipt Subject. Use %s to dynamically add the invoice amount.', 'gmt_donation_invoices' ), '<code>[amount]</code>' ); ?></label><br>
					<input type="text" name="gmt_donation_invoice[receipt_subject]" class="large-text" id="gmt_donation_invoice_receipt_subject" value="<?php echo stripslashes( esc_attr( $details['receipt_subject'] ) ); ?>">
				</div>
				<br>

				<div>
					<label class="description" for="gmt_donation_invoice_receipt_message"><?php printf( __( 'Receipt Message. Use %s to dynamically add the invoice amount.', 'gmt_donation_invoices' ), '<code>[amount]</code>' ); ?></label><br>
					<textarea name="gmt_donation_invoice[receipt_message]" class="large-text" id="gmt_donation_invoice_receipt_message" cols="50" rows="10"><?php echo stripslashes( esc_textarea( $details['receipt_message'] ) ); ?></textarea>
				</div>
				<br>

			</fieldset>

		<?php

		// Security field
		wp_nonce_field( 'gmt_donation_invoices_form_metabox_nonce', 'gmt_donation_invoices_form_metabox_process' );

	}



	/**
	 * Save the metabox
	 * @param  Number $post_id The post ID
	 * @param  Array  $post    The post data
	 */
	function gmt_donation_invoices_save_metabox( $post_id, $post ) {

		if ( !isset( $_POST['gmt_donation_invoices_form_metabox_process'] ) ) return;

		// Verify data came from edit screen
		if ( !wp_verify_nonce( $_POST['gmt_donation_invoices_form_metabox_process'], 'gmt_donation_invoices_form_metabox_nonce' ) ) {
			return $post->ID;
		}

		// Verify user has permission to edit post
		if ( !current_user_can( 'edit_post', $post->ID )) {
			return $post->ID;
		}

		// Sanitize and save details
		if ( isset( $_POST['gmt_donation_invoice'] ) ) {
			$details = array();
			foreach ( $_POST['gmt_donation_invoice'] as $key => $detail ) {
				$details[$key] = wp_filter_post_kses( $detail );
			}
			if ( !array_key_exists( 'send_receipt', $details ) ) {
				$details['send_receipt'] = 'off';
			}
			update_post_meta( $post->ID, 'gmt_invoice_details', $details );
		}
		if ( isset( $_POST['gmt_donation_invoice_amount'] ) ) {
			update_post_meta( $post->ID, 'gmt_invoice_amount', wp_filter_nohtml_kses( $_POST['gmt_donation_invoice_amount'] ) );
		}

		if (empty(get_post_meta( $post->ID, 'gmt_invoice_paid', true ))) {
			update_post_meta( $post->ID, 'gmt_invoice_paid', false );
		}

	}
	add_action('save_post', 'gmt_donation_invoices_save_metabox', 1, 2);



	/**
	 * Send invoice email
	 * @param  Number $post_id The post ID
	 * @param  Array  $post    The post data
	 */
	function gmt_donation_invoices_send_email_metabox( $post_id, $post ) {

		// Check if "send email" checkbox is selected
		if ( !isset( $_POST['gmt_donations_invoice_send_email'] ) ) return;

		// Verify data came from edit screen
		if ( !wp_verify_nonce( $_POST['gmt_donation_invoice_emails_metabox_process'], 'gmt_donation_invoice_emails_metabox_nonce' ) ) {
			return $post_id;
		}

		// Verify user has permission to edit post
		if ( !current_user_can( 'edit_post', $post_id )) {
			return $post_id;
		}

		// Only send email if invoice isn't paid yet
		if ( !empty( get_post_meta( $post_id, 'gmt_invoice_paid', true ) ) ) return;

		// Variables
		$sent_emails = get_post_meta( $post_id, 'gmt_invoice_emails_sent', true );
		$sent_emails = is_array( $sent_emails ) ? $sent_emails : array();
		$email = gmt_donation_invoices_send_invoice_email( $post_id );

		// Timestamp email attempt
		if ( $email ) {
			$sent_emails[] = array(
				'status' => 'success',
				'timestamp' => current_time( 'timestamp' ),
			);
		} else {
			// If email mails, timestamp and note failure
			$sent_emails[] = array(
				'status' => 'failed',
				'timestamp' => current_time( 'timestamp' ),
			);
		}

		update_post_meta( $post_id, 'gmt_invoice_emails_sent', $sent_emails );

	}
	add_action( 'save_post', 'gmt_donation_invoices_send_email_metabox', 2, 2 );