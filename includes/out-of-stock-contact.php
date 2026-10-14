<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'woocommerce_single_product_summary', 'yuc_out_of_stock_contact_form', 30 );

function yuc_out_of_stock_contact_form(): void {
	global $product;

	if ( ! $product->is_in_stock() ) {
		echo do_shortcode( '[contact-form-7 id="4669"]' );
	}
}
