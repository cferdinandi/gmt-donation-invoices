# GMT Donation Invoices
Add invoice capability to the <a href="https://github.com/cferdinandi/gmt-donations">GMT Donations plugin</a>.

[Download GMT Donation Invoices](https://github.com/cferdinandi/gmt-donation-invoices/archive/master.zip)



## Getting Started

Getting started with GMT Donation Invoices is as simple as installing a plugin:

1. Upload the `gmt-donation-invoices` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the Plugins menu in WordPress.
3. Add a new invoice under `Donations` > `Invoices` and enter all of your details.
4. Click `Send Email` to send an email invoice to the donor.

It's recommended that you also install the [GitHub Updater plugin](https://github.com/afragen/github-updater) to get automatic updates.


## Styling your invoice

A URL is automatically generated for your invoice. The invoice will inherit the default styles and layout of your theme.

### Customizing the layout

Modify the layout of your invoices using the [Custom Post Type Template](https://codex.wordpress.org/Post_Type_Templates) feature in WordPress.

### Adding your own styles

Almost every element in the donation form includes a general class you can hook into, as well as a unique ID for more specific styling. These may change over time, so I'd recommend using your browsers developer tools to view the source code as needed.

### The default styles

Here are the default styles you'll need to account for if you disable the GMT Donations CSS.

```css
.gmt-donation-invoice-amount {
	font-size: 1.2em;
}

.gmt-donation-invoice-amount-label {
	font-weight: bold;
}
```


## Extending functionality with plugins or your theme

GMT Donation Invoices emits an action hook when invoices are paid that you can use to extend functionality in your own plugins and themes.

```php
/**
 * Your function to extend plugin functionality
 * @param  integer $id     The form ID
 * @param  array   $status The donation data
 */
function gmt_donation_invoices_extend_functionality( $id, $status ) {

	// Variables
	$payment_type = $status['type']; // Payment type (`stripe` or `paypal`)
	$donation_amount = $status['amount']; // Donation amount
	$email = $status['email'];  // Email address of the donor

	// Get additional details
	$options = gmt_donations_get_theme_options(); // Your donation options from the Settings page
	$details_saved = get_post_meta( $id, 'gmt_invoice_details', true ); // This invoice details
	$details_defaults = gmt_donation_invoices_metabox_details_defaults(); // The invoice defaults
	$details = wp_parse_args( $details_saved, $details_defaults ); // A merge of the defaults and the details
	$currencies = gmt_donations_settings_field_currency_choices(); // A list of available currencies
	$currency_symbol = $currencies[$options['currency']]['symbol']; // The symbol for the selected currency (ex. $)

}
add_action( 'gmt_donation_invoice_success', 'gmt_donation_invoices_extend_functionality', 10, 2 );
```



## How to Contribute

Please read the [Contribution Guidelines](CONTRIBUTING.md).



## License

The code is available under the [GPLv3](LICENSE.md).