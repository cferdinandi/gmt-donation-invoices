/**
 * GMT Donation Invoices Stripe Handler
 * @description  Handle Stripe button
 * @version  1.0.0
 * @author   Chris Ferdinandi
 * @license  MIT
 */
;(function (window, document, undefined) {

	'use strict';

	// Feature test
	var supports = 'querySelector' in document && 'addEventListener' in window;
	if ( !supports ) return;


	//
	// Variables
	//

	var key = document.querySelector( '[data-gmt-donations-stripe-key]' );
	if ( !key ) return;
	var handler = StripeCheckout.configure({
		key: key.getAttribute( 'data-gmt-donations-stripe-key' ),
		locale: 'auto',
	});
	var stripeBtn = document.querySelector( '.gmt-donation-invoice-button-stripe' );

	//
	// Methods
	//

	/**
	 * Get the closest matching element up the DOM tree.
	 * @param  {Element} elem     Starting element
	 * @param  {String}  selector Class to match against
	 * @return {Boolean|Element}  Returns null if not match found
	 */
	var getClosest = function ( elem, selector ) {
		var hasClassList = 'classList' in document.documentElement;
		for ( ; elem && elem !== document && elem.nodeType === 1; elem = elem.parentNode ) {
			if ( hasClassList ) {
				if ( elem.classList.contains( selector.substr(1) ) ) {
					return elem;
				}
			} else {
				if ( new RegExp('(^|\\s)' + selector.substr(1) + '(\\s|$)').test( elem.className ) ) {
					return elem;
				}
			}
		}
		return null;
	};

	/**
	 * Handle click events
	 */
	var clickHandler = function (event) {

		// Check if Stripe button was clicked
		var toggle = getClosest( event.target, '.gmt-donation-invoice-button-stripe' );
		if ( !toggle ) return;

		// Prevent form from submitting
		event.preventDefault();

		// Get form
		var form = getClosest( toggle, '.gmt-donation-invoice' );

		// Open Stripe payment modal
		handler.open({
			name: toggle.getAttribute( 'data-business-name' ),
			image: toggle.getAttribute( 'data-image' ),
			description: toggle.getAttribute( 'data-description' ),
			zipCode: true,
			panelLabel: toggle.getAttribute( 'data-panel-label' ),
			amount: toggle.getAttribute( 'data-amount' ),
			token: function(token, args) {
				var input = document.createElement('input');
				var loading = document.createElement('div');
				var spinner = toggle.getAttribute( 'data-loading' );
				input.type = 'hidden';
				input.value = token.id;
				input.name = 'stripe_token';
				loading.className = 'gmt-donations-loading';
				loading.innerHTML = '<div class="gmt-donations-loading-wrap"><div class="gmt-donations-loading-content"><img height="75" width="75" alt="processing" src="' + spinner + '"></div></div>';
				form.insertBefore( input, form.childNodes[0] );
				form.parentNode.insertBefore( loading, form );
				form.submit();
			}
		});
	};

	/**
	 * Handle pop events
	 */
	var popHandler = function () {
		handler.close();
	};


	//
	// Inits and event listeners
	//

	if ( !stripeBtn ) return;
	stripeBtn.removeAttribute( 'disabled' );
	document.addEventListener( 'click', clickHandler, false );
	document.addEventListener( 'popstate', popHandler, false );

})(window, document);