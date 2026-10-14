<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'woocommerce_single_product_summary', 'yuc_release_date_notice', 25 );

function yuc_release_date_notice(): void {
	global $product;

	$release_date = get_post_meta( $product->get_id(), 'release_date', true );
	if ( '' === $release_date ) {
		return;
	}

	$timezone = wp_timezone();
	$date     = DateTimeImmutable::createFromFormat( '!Y-m-d', $release_date, $timezone );
	if ( $date && $date < new DateTimeImmutable( 'today', $timezone ) ) {
		return;
	}

	wc_print_notice( 'Releasetermin / Release date: ' . esc_html( $release_date ), 'notice' );
}
