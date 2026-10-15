<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'woocommerce_product_image_loading_attr', 'yuc_listing_first_image_eager' );

function yuc_listing_first_image_eager( string $loading ): string {
	static $done = false;

	if ( $done || ( ! is_front_page() && ! is_shop() ) ) {
		return $loading;
	}

	$done = true;

	return 'eager';
}
