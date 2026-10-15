<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'woocommerce_product_supports', 'yuc_listing_button_disable_ajax', 10, 2 );
add_filter( 'woocommerce_product_add_to_cart_url', 'yuc_listing_button_url', 10, 2 );
add_filter( 'woocommerce_product_add_to_cart_text', 'yuc_listing_button_text' );
add_filter( 'woocommerce_product_add_to_cart_description', 'yuc_listing_button_description', 10, 2 );
add_action( 'init', 'yuc_listing_button_remove_germanized_hooks', 20 );

function yuc_listing_button_disable_ajax( bool $supports, string $feature ): bool {
	return 'ajax_add_to_cart' === $feature ? false : $supports;
}

function yuc_listing_button_url( string $url, WC_Product $product ): string {
	return $product->get_permalink();
}

function yuc_listing_button_text(): string {
	return 'Details';
}

function yuc_listing_button_description( string $description, WC_Product $product ): string {
	return sprintf( 'Details zu „%s“', $product->get_name() );
}

function yuc_listing_button_remove_germanized_hooks(): void {
	remove_filter( 'woocommerce_loop_add_to_cart_link', '__return_false', 99 );
	remove_filter( 'woocommerce_loop_add_to_cart_link', 'woocommerce_gzd_template_loop_add_to_cart', 99 );
}
