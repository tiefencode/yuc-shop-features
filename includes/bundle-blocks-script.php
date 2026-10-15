<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_print_footer_scripts', 'yuc_dequeue_bundle_blocks_script', 1 );

function yuc_dequeue_bundle_blocks_script(): void {
	if ( ! is_cart() && ! is_checkout() ) {
		wp_dequeue_script( 'woosb-blocks' );
	}
}
