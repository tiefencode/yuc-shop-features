<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'woocommerce_before_cart_totals', 'yuc_free_shipping_notice' );

function yuc_free_shipping_notice(): void {
	$packages = WC()->cart->get_shipping_packages();
	if ( ! $packages ) {
		return;
	}

	$zone = WC_Shipping_Zones::get_zone_matching_package( reset( $packages ) );

	foreach ( $zone->get_shipping_methods( true ) as $method ) {
		if ( 'free_shipping' !== $method->id || ! in_array( $method->requires, [ 'min_amount', 'either' ], true ) ) {
			continue;
		}

		$remaining = (float) $method->min_amount - WC()->cart->get_displayed_subtotal();
		$text      = $remaining > 0
			? sprintf( 'Noch %s bis zum kostenlosen Versand', wc_price( $remaining ) )
			: 'Versandkostenfrei!';

		wc_print_notice( $text, 'notice' );
		return;
	}
}
