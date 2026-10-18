# Related products by artist and genre

Status: live · Prio: medium · Code: `includes/related-products.php`

**Value:** "Ähnliche Produkte" on the product page shows other releases of the same artist first, then the products that share the most genres, instead of a random pick from the catalogue.

Origin: the Single Product template uses the Product Collection block with the "Related Products" collection. It calls `wc_get_related_products()`, whose SQL returns every product that shares *any* category or tag, unranked, cached for a day and then shuffled [Code, WooCommerce 11.2.1 `wc-product-functions.php`]. The categories are media types (Vinyl, CD, Tape), so almost the whole catalogue qualifies. The old theme only switched off the category relation (`functions.php:78`); the result was still random.

Site facts: `artist` is a custom non-hierarchical taxonomy on `product` (term archives `/artist/<slug>/`, not in REST, not registered by the theme; presumably Pods). Genres are `product_tag` (rewrite base `genre`). Checked live 2026-10-18.

Behaviour, for the product being viewed:

1. Other products of the same artist come first.
2. Then the remaining products, ordered by the number of shared genres, most first.
3. Ties within a group: newest first.
4. Categories are ignored. The product itself and out-of-stock products never appear.
5. A product with neither artist nor genres gets no related products; the block stays empty. No fallback.
6. The block keeps its Site Editor settings (5 products, columns, card layout) and its heading.

## Implementation

The block stays as it is; the plugin replaces the product list it renders.

- `woocommerce_related_products` (`$related_ids, $product_id, $args`) runs after WooCommerce's own lookup and transient [Code]. The filter ignores the incoming list and returns the ranked IDs. The block then queries `post__in` with these IDs [Code, `ProductCollection/HandlerRegistry.php`]; an empty list makes the block render nothing.
- Ranking in one `$wpdb` query over `term_relationships`/`term_taxonomy`: published products that share at least one `artist` or `product_tag` term with the current product, scored `SUM(artist match × 1000 + tag match × 1)`, `ORDER BY score DESC, post_date DESC`, current product excluded, limit 100 (the block's own limit for this collection). Out-of-stock products are excluded by the `product_visibility` term `outofstock` (term ID from `wc_get_product_visibility_term_ids()`), so the result does not depend on the block's stock-status setting. Catalog-hidden products are already filtered by the block itself [Code, `QueryBuilder.php`].
- `woocommerce_product_related_posts_shuffle`: `false`, otherwise WooCommerce shuffles the list after the filter [Code].
- `query_loop_block_query_vars`, priority after WooCommerce's `build_frontend_query` (10): for the related collection set `orderby` to `post__in`, because the block otherwise sorts by its `orderBy` attribute (currently title). The block exposes the collection name as `$block->context['collection']` [Code, `ProductCollection/Controller.php`].
- No caching by the plugin: one indexed query per product page view.

## Test on staging

Product cards in the Product Collection carry `artist-<slug>`, `product_tag-<slug>` and `instock`/`outofstock` classes on `li.wc-block-product` [Code, checked live].

- Product page of a product whose artist has other in-stock products: the first cards under "Ähnliche Produkte" have the same `artist-<slug>` class as the product; cards after them share at least one `product_tag-<slug>` with the product, with the number of shared tags not increasing from card to card.
- Same page requested twice: identical card order.
- Product page of a product with no artist and no genre: no `li.wc-block-product` under the heading.
- No card with class `outofstock` on any product page checked.
- The heading and card layout (image, title, price, Details button) are unchanged against production.
