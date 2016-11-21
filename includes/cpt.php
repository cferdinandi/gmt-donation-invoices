<?php


	/**
	 * Add custom post type for donation invoices
	 */
	function gmt_donation_invoices_add_custom_post_type() {

		$labels = array(
			'name'               => _x( 'Invoices', 'post type general name', 'gmt_donation_invoices' ),
			'singular_name'      => _x( 'Invoice', 'post type singular name', 'gmt_donation_invoices' ),
			'add_new'            => _x( 'Add New', 'keel-pets', 'gmt_donation_invoices' ),
			'add_new_item'       => __( 'Add New Invoice', 'gmt_donation_invoices' ),
			'edit_item'          => __( 'Edit Invoice', 'gmt_donation_invoices' ),
			'new_item'           => __( 'New Invoice', 'gmt_donation_invoices' ),
			'all_items'          => __( 'Invoices', 'gmt_donation_invoices' ),
			'view_item'          => __( 'View Invoice', 'gmt_donation_invoices' ),
			'search_items'       => __( 'Search Invoices', 'gmt_donation_invoices' ),
			'not_found'          => __( 'No invoices found', 'gmt_donation_invoices' ),
			'not_found_in_trash' => __( 'No invoices found in the Trash', 'gmt_donation_invoices' ),
			'parent_item_colon'  => '',
			'menu_name'          => __( 'Invoices', 'gmt_donation_invoices' ),
		);
		$args = array(
			'labels'        => $labels,
			'description'   => 'Holds our invoice data',
			'public'        => true,
			// 'menu_position' => 5,
			// 'menu_icon'     => 'dashicons-money',
			'hierarchical'  => false,
			'supports'      => array(
				'title',
				// 'editor',
				// 'thumbnail',
				// 'excerpt',
				// 'revisions',
				// 'page-attributes',
			),
			'has_archive'   => false,
			'show_in_menu'  => 'edit.php?post_type=gmt_donation_forms',
			'rewrite' => array(
				'slug' => 'invoices',
			),
			// 'map_meta_cap'  => true,
			// 'capabilities' => array(
			// 	'create_posts' => false,
			// 	'edit_published_posts' => false,
			// 	'delete_posts' => false,
			// 	'delete_published_posts' => false,
			// )
		);
		register_post_type( 'gmt_donate_invoices', $args );
	}
	add_action( 'init', 'gmt_donation_invoices_add_custom_post_type' );