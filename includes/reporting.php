<?php

	/**
	 * Create donation and donor records in the database
	 * @param  integer $id     The form ID
	 * @param  array   $status The donation data
	 */
	function gmt_donation_invoices_create_donation_records( $id, $status ) {

		// Check if donor already exists
		$donor = get_page_by_title( $status['email'], 'OBJECT', 'gmt_donors' );
		$donor_exists = empty( $donor ) ? false : true;
		$recurring_amount = $donor_exists ? get_post_meta( $donor->ID, 'gmt_donations_recurring', true ) : 0;
		$recurring_id = $donor_exists ? get_post_meta( $donor->ID, 'gmt_donations_recurring_id', true ) : 0;

		// If not, create donor record
		if ( empty( $donor ) ) {
			$donor = wp_insert_post(array(
				'post_content'   => '',
				'post_title'     => $status['email'],
				'post_status'    => 'publish',
				'post_type'      => 'gmt_donors',
			));
		} else {
			$donor = $donor->ID;
		}

		// Create donation record
		$donation = wp_insert_post(array(
			'post_content'   => '',
			'post_title'     => current_time( 'timestamp' ) . '_' . wp_generate_password( 24, false ),
			'post_status'    => 'publish',
			'post_type'      => 'gmt_donations',
		));

		// Add donation details
		if ( intval( $donation !== 0 ) ) {
			wp_update_post( array( 'ID' => $donation, 'post_title' => $donation ) );
			update_post_meta( $donation, 'gmt_donations_amount', wp_filter_nohtml_kses( $status['amount'] ) );
			update_post_meta( $donation, 'gmt_donations_form', -1 );
			update_post_meta( $donation, 'gmt_donations_donor', $donor );
			update_post_meta( $donation, 'gmt_donations_source', wp_filter_nohtml_kses( $status['type'] ) );
			update_post_meta( $donation, 'gmt_donations_recurring', 0 );
			update_post_meta( $donation, 'gmt_donations_in_honor', false );
			update_post_meta( $donation, 'gmt_donations_in_honor_email', null );
			update_post_meta( $donation, 'gmt_donations_in_honor_donor', null );
			update_post_meta( $donation, 'gmt_donation_invoice_id', $id );
		}

		// Add donor details
		if ( intval( $donor ) !== 0 ) {

			// Get all donations by donor
			$donations_by_donor = get_posts(array(
				'numberposts' => -1,
				'post_status' => 'publish',
				'post_type' => 'gmt_donations',
				'meta_key' => 'gmt_donations_donor',
				'meta_value' => $donor,
			));

			// Add donor metadata
			update_post_meta( $donor, 'gmt_donations_email', wp_filter_nohtml_kses( $status['email'] ) );
			update_post_meta( $donor, 'gmt_donations_count_donated', count( $donations_by_donor ) );
			update_post_meta( $donor, 'gmt_donations_total_donated', wp_filter_nohtml_kses( gmt_donations_get_total_donated( $donations_by_donor ) ) );
			update_post_meta( $donor, 'gmt_donations_recurring', $recurring_amount );
			update_post_meta( $donor, 'gmt_donations_recurring_id', $recurring_id );

		}

	}
	add_action( 'gmt_donation_invoice_success', 'gmt_donation_invoices_create_donation_records', 10, 2 );



	/**
	 * Add custom column to donation invoices table
	 * @param  array $columns Existing columns
	 * @return array          Updated array with new columns
	 */
	function gmt_donation_invoices_admin_table_columns( $columns ) {
		$new = array();
		foreach( $columns as $key => $title ) {
			if ( $key == 'date' ) {
				$new['amount'] = __( 'Amount', 'gmt_donations' );
				$new['payment_status'] = __( 'Status', 'gmt_donations' );
				$new['payment_method'] = __( 'Payment Method', 'gmt_donations' );
			}
			$new[$key] = $title;
		}
		return $new;
	}
	add_filter( 'manage_edit-gmt_donate_invoices_columns', 'gmt_donation_invoices_admin_table_columns' );



	/**
	 * Add custom column content to donation invoices table
	 * @param  string $name The column name
	 * @return string       The column content
	 */
	function gmt_donation_invoices_admin_table_columns_content( $column, $post_id ) {

		// Only run on donation forms
		if ( get_post_type( $post_id ) !== 'gmt_donate_invoices' ) return;

		// Currencies
		$options = gmt_donations_get_theme_options();
		$currencies = gmt_donations_settings_field_currency_choices();

		// Add custom column content
		if ( $column === 'amount' ) {
			echo esc_html( $currencies[$options['currency']]['symbol'] . number_format( get_post_meta( $post_id, 'gmt_invoice_amount', true ), 2 ) );
		}

		if ( $column === 'payment_status' ) {
			$status = get_post_meta( $post_id, 'gmt_invoice_paid', true );
			echo esc_html( empty( $status ) ? __( 'Not Paid', 'gmt_donations' ) : date( 'F j, Y', $status ) . ' - ' . date( 'g:ia', $status ) );
		}

		if ( $column === 'payment_method' ) {
			$method = get_post_meta( $post_id, 'gmt_invoice_payment_method', true );
			echo esc_html( empty( $method ) ? __( 'None', 'gmt_donations' ) : ucfirst( $method ) );
		}

	}
	add_action( 'manage_posts_custom_column', 'gmt_donation_invoices_admin_table_columns_content', 10, 2 );



	/**
	 * Register donation invoice fields as sortable
	 * @param  array $columns Existing columns
	 * @return array          Updated array with new columns
	 */
	function gmt_donation_invoices_admin_table_sortable_columns( $sortable ) {
		$sortable['amount'] = 'amount';
		$sortable['payment_status'] = 'payment_status';
		$sortable['payment_method'] = 'payment_method';
		return $sortable;
	}
	add_filter( 'manage_edit-gmt_donate_invoices_sortable_columns', 'gmt_donation_invoices_admin_table_sortable_columns' );



	/**
	 * Sort donor fields
	 * @param  object $query The query
	 */
	function gmt_donation_invoices_admin_table_sortable_columns_sorting( $query ) {

		if ( !is_admin() || !isset( $_GET['post_type'] ) || $_GET['post_type'] !== 'gmt_donate_invoices' ) return;

		$orderby = $query->get( 'orderby' );

		if ( empty( $orderby ) ) {
			$query->set( 'meta_key', 'gmt_invoice_paid' );
			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'order', 'ASC' );
		}

		if ( $orderby === 'amount' ) {
			$query->set( 'meta_key', 'gmt_invoice_amount' );
			$query->set( 'orderby', 'meta_value_num' );
		}

		if ( $orderby === 'payment_status' ) {
			$query->set( 'meta_key', 'gmt_invoice_paid' );
			$query->set( 'orderby', 'meta_value_num' );

		}

		if ( $orderby === 'payment_method' ) {
			$query->set( 'meta_key', 'gmt_invoice_payment_method' );
			$query->set( 'orderby', 'meta_value' );
		}

	}
	add_action( 'pre_get_posts', 'gmt_donation_invoices_admin_table_sortable_columns_sorting' );