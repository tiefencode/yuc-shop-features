( function () {
	var rates = window.yucPhoneRequiredRates || [];

	function isRequired() {
		var inputs = document.querySelectorAll( 'input[name^="shipping_method"]' );

		return Array.prototype.some.call( inputs, function ( input ) {
			return ( input.type !== 'radio' || input.checked ) && rates.indexOf( input.value ) !== -1;
		} );
	}

	function sync() {
		var input = document.getElementById( 'billing_phone' );
		if ( ! input ) {
			return;
		}

		var required = isRequired();
		var row      = input.closest( '.form-row' );
		var marker   = document.querySelector( 'label[for="billing_phone"] .required, label[for="billing_phone"] .optional' );

		input.required = required;
		input.setAttribute( 'aria-required', required ? 'true' : 'false' );

		if ( row ) {
			row.classList.toggle( 'validate-required', required );
			if ( ! required ) {
				row.classList.remove( 'woocommerce-invalid', 'woocommerce-invalid-required-field' );
			}
		}

		if ( marker ) {
			marker.outerHTML = required
				? '<span class="required" aria-hidden="true">*</span>'
				: '<span class="optional">(optional)</span>';
		}
	}

	document.addEventListener( 'change', function ( event ) {
		if ( event.target.matches( 'input[name^="shipping_method"]' ) ) {
			sync();
		}
	} );

	var orderReview = document.getElementById( 'order_review' );
	if ( orderReview ) {
		new MutationObserver( sync ).observe( orderReview, { childList: true, subtree: true } );
	}

	sync();
} )();
