<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function yuc_phone_required_rate_ids(): array {
	$rate_ids = [];

	// Zone 0 "Rest of the world", zone 3 "Amerika".
	foreach ( [ 0, 3 ] as $zone_id ) {
		$zone = WC_Shipping_Zones::get_zone( $zone_id );
		if ( ! $zone ) {
			continue;
		}
		foreach ( $zone->get_shipping_methods( true ) as $method ) {
			$rate_ids[] = $method->get_rate_id();
		}
	}

	return $rate_ids;
}

function yuc_phone_required(): bool {
	$chosen = WC()->session ? (array) WC()->session->get( 'chosen_shipping_methods' ) : [];

	return (bool) array_intersect( $chosen, yuc_phone_required_rate_ids() );
}

add_filter( 'woocommerce_checkout_fields', 'yuc_phone_checkout_field', 20 );

function yuc_phone_checkout_field( array $fields ): array {
	if ( isset( $fields['billing']['billing_phone'] ) ) {
		$fields['billing']['billing_phone']['required'] = yuc_phone_required();
	}

	return $fields;
}

add_action( 'woocommerce_after_checkout_validation', 'yuc_phone_validate', 10, 2 );

function yuc_phone_validate( array $data, WP_Error $errors ): void {
	if ( ! yuc_phone_required() || '' !== trim( $data['billing_phone'] ?? '' ) ) {
		return;
	}

	$errors->remove( 'billing_phone_required' );
	$errors->add(
		'billing_phone_required',
		'Bitte gib eine Telefonnummer an (für diese Versandart erforderlich).',
		[ 'id' => 'billing_phone' ]
	);
}

add_action( 'wp_enqueue_scripts', 'yuc_phone_enqueue_script' );

function yuc_phone_enqueue_script(): void {
	if ( ! is_checkout() || is_order_received_page() ) {
		return;
	}

	wp_enqueue_script(
		'yuc-checkout-phone',
		YUC_SHOP_FEATURES_URL . 'assets/checkout-phone.js',
		[],
		YUC_SHOP_FEATURES_VERSION,
		true
	);
	wp_add_inline_script(
		'yuc-checkout-phone',
		'var yucPhoneRequiredRates = ' . wp_json_encode( yuc_phone_required_rate_ids() ) . ';',
		'before'
	);
}
