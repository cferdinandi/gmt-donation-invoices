<?php

/**
 * Plugin Name: GMT Donation Invoices
 * Plugin URI: https://github.com/cferdinandi/gmt-donation-invoices/
 * GitHub Plugin URI: https://github.com/cferdinandi/gmt-donation-invoices/
 * Description: Add invoice capability to the <a href="https://github.com/cferdinandi/gmt-donations">GMT Donations plugin</a>.
 * Version: 1.1.3
 * Author: Chris Ferdinandi
 * Author URI: http://gomakethings.com
 * License: GPLv3
 */

// @todo send emails

// Custom Post Type
require_once( plugin_dir_path( __FILE__ ) . 'includes/cpt.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/metabox.php' );

// Reporting
require_once( plugin_dir_path( __FILE__ ) . 'includes/reporting.php' );

// Invoices and processes
require_once( plugin_dir_path( __FILE__ ) . 'includes/invoice.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/is-paid.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/emails.php' );


/**
 * Display notice if GMT Donations plugin is not installed
 */
function gmt_donation_invoices_admin_notice() {

	if ( function_exists( 'gmt_donations_flush_rewrites' ) ) return;

	?>

		<div class="notice notice-error"><p><?php printf( __( 'GMT Donation Invoices will not work without the %sGMT Donations plugin%s. Please install it now.', 'gmt_donations' ), '<a href="https://github.com/cferdinandi/gmt-donations">', '</a>' ); ?></p></div>

	<?php
}
add_action( 'admin_notices', 'gmt_donation_invoices_admin_notice' );


/**
 * Flush rewrite rules on activation and deactivation
 */
function gmt_donation_invoices_flush_rewrites() {
	gmt_donation_invoices_add_custom_post_type();
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
register_activation_hook( __FILE__, 'gmt_donation_invoices_flush_rewrites' );