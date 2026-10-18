<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'woocommerce_related_products', 'yuc_related_products_ranked', 10, 2 );
add_filter( 'woocommerce_product_related_posts_shuffle', '__return_false' );
add_filter( 'query_loop_block_query_vars', 'yuc_related_products_keep_order', 20, 2 );

function yuc_related_products_ranked( array $related_ids, int $product_id ): array {
	global $wpdb;

	$out_of_stock = (int) ( wc_get_product_visibility_term_ids()['outofstock'] ?? 0 );

	return array_map(
		'intval',
		$wpdb->get_col(
			$wpdb->prepare(
				"SELECT p.ID
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->term_relationships} r ON r.object_id = p.ID
				INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = r.term_taxonomy_id
				WHERE tt.term_taxonomy_id IN (
					SELECT r2.term_taxonomy_id
					FROM {$wpdb->term_relationships} r2
					INNER JOIN {$wpdb->term_taxonomy} tt2 ON tt2.term_taxonomy_id = r2.term_taxonomy_id
					WHERE r2.object_id = %d AND tt2.taxonomy IN ( 'artist', 'product_tag' )
				)
				AND p.post_type = 'product'
				AND p.post_status = 'publish'
				AND p.ID <> %d
				AND p.ID NOT IN (
					SELECT r3.object_id
					FROM {$wpdb->term_relationships} r3
					INNER JOIN {$wpdb->term_taxonomy} tt3 ON tt3.term_taxonomy_id = r3.term_taxonomy_id
					WHERE tt3.term_id = %d
				)
				GROUP BY p.ID
				ORDER BY SUM( CASE WHEN tt.taxonomy = 'artist' THEN 1000 ELSE 1 END ) DESC, p.post_date DESC
				LIMIT 100",
				$product_id,
				$product_id,
				$out_of_stock
			)
		)
	);
}

function yuc_related_products_keep_order( array $query, WP_Block $block ): array {
	if ( 'woocommerce/product-collection/related' === ( $block->context['collection'] ?? '' ) ) {
		$query['orderby'] = 'post__in';
	}

	return $query;
}
