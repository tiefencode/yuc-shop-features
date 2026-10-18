# Eager first listing image

Status: live · Prio: low · Code: `includes/listing-first-image.php`

**Value:** The first product image on the front page and the shop page is the Largest Contentful Paint element; loading it eagerly with high priority cuts LCP.

Origin: Lighthouse analysis 2026-10-15 (`/Users/moswald/Projekte/youngandcold-wp/lighthouse-2026-10-15/README.md`, outside this repo). On the front page and the shop page the LCP element is the first image of the first Product Collection. WooCommerce renders every Product Image block with `loading="lazy"` and no `fetchpriority`, so the browser requests it late; with a warm page cache this was the largest remaining share of LCP (3.5 s at 80 ms TTFB).

The first product image on the front page and on the shop page is rendered with `loading="eager"` and `fetchpriority="high"`. All other listing images stay lazy.

## Implementation

- `ProductImage::render_image()` decides the `loading` attribute through `woocommerce_product_image_loading_attr` (default `'lazy'`, second argument the image ID) and passes it to `wp_get_attachment_image()` [Code].
- Filter returns `'eager'` for the first call on `is_front_page()` or `is_shop()` (static flag), the default otherwise.
- `fetchpriority="high"` is not set by the plugin. Core's `wp_get_loading_optimization_attributes()` treats a provided non-lazy `loading` value as "in viewport" and adds `fetchpriority="high"` to the first such image of at least 50 000 px² while `wp_high_priority_element_flag()` is still set [Code, WordPress `media.php`]. The site logo (100 × 100) is below that size and leaves the flag set.
- Only the first image gets priority; the rest of the first row stays lazy because browsers fetch in-viewport lazy images at layout time anyway and only one element should carry `fetchpriority="high"`.

## Test on staging

View-source of `/` and `/shop/`: the first `img` inside `li.wc-block-product` has `loading="eager" fetchpriority="high"`, the next ones `loading="lazy"`; a product page keeps `loading="lazy"` on its related-products images.
