<?php
/**
 * Plugin Name:       YUC Shop Features
 * Plugin URI:        https://github.com/tiefencode/yuc-shop-features
 * Description:       WooCommerce shop features for Young & Cold Records.
 * Version:           0.1.0
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * Requires Plugins:  woocommerce
 * Author:            tiefencode
 * License:           GPL-2.0-or-later
 * Text Domain:       yuc-shop-features
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'YUC_SHOP_FEATURES_VERSION', '0.1.0' );
define( 'YUC_SHOP_FEATURES_DIR', plugin_dir_path( __FILE__ ) );
define( 'YUC_SHOP_FEATURES_URL', plugin_dir_url( __FILE__ ) );

add_action( 'plugins_loaded', 'yuc_shop_features_init' );

function yuc_shop_features_init(): void {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	foreach ( glob( YUC_SHOP_FEATURES_DIR . 'includes/*.php' ) as $file ) {
		require_once $file;
	}
}
