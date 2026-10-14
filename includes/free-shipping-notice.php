<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'woocommerce_before_cart_table', 'yuc_free_shipping_notice' );
add_action( 'woocommerce_checkout_before_order_review', 'yuc_free_shipping_notice' );

function yuc_free_shipping_notice(): void {
	$min_amount = yuc_free_shipping_min_amount();
	if ( $min_amount <= 0 ) {
		return;
	}

	$subtotal  = WC()->cart->get_displayed_subtotal();
	$remaining = $min_amount - $subtotal;
	$text      = $remaining > 0
		? sprintf( 'Noch %s bis zum kostenlosen Versand', wc_price( $remaining ) )
		: 'Versandkostenfrei!';

	// Truck icon: Feather Icons, MIT.
	$icon = '<svg class="yuc-free-shipping__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>';

	printf(
		'<div class="yuc-free-shipping"><p class="yuc-free-shipping__text">%s</p><div class="yuc-free-shipping__bar" aria-hidden="true"><div class="yuc-free-shipping__fill" style="width: %s%%">%s</div></div></div>',
		wp_kses_post( $text ),
		esc_attr( min( 100, round( $subtotal / $min_amount * 100, 1 ) ) ),
		$icon
	);
}

function yuc_free_shipping_min_amount(): float {
	$packages = WC()->cart->get_shipping_packages();
	if ( ! $packages ) {
		return 0;
	}

	$zone = WC_Shipping_Zones::get_zone_matching_package( reset( $packages ) );

	foreach ( $zone->get_shipping_methods( true ) as $method ) {
		if ( 'free_shipping' === $method->id && in_array( $method->requires, [ 'min_amount', 'either' ], true ) ) {
			return (float) $method->min_amount;
		}
	}

	return 0;
}

add_action( 'wp_enqueue_scripts', 'yuc_free_shipping_enqueue_style' );

function yuc_free_shipping_enqueue_style(): void {
	if ( is_cart() || is_checkout() ) {
		wp_enqueue_style( 'yuc-free-shipping', YUC_SHOP_FEATURES_URL . 'assets/free-shipping-notice.css', [], YUC_SHOP_FEATURES_VERSION );
	}
}
