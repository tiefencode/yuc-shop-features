# Spec: porting features from the Storefront child theme

Status 2026-10-15: the theme switch to Twenty Twenty-Five is done. Cart and checkout run as shortcodes (`[woocommerce_cart]`, `[woocommerce_checkout]`). Free shipping is configured as a "Free shipping" method with a minimum order amount.

Paths like `functions.php:221` refer to the old child theme `youngandcold/` (outside this repo). **[Code]** = verified in the WooCommerce 11.2.1 source.

The old code defines the behaviour to keep, not how to build it. Replace its workarounds with the straightforward approach; "better" means simpler, not more. The implementation notes below are a starting point, not a constraint.

## Features

| Prio | Feature | Value |
|---|---|---|
| High | Release date | Customers see when a pre-order ships |
| Medium | Phone required per shipping zone | Carriers outside the EU need a phone number; orders without one get stuck |
| Medium | Free shipping notice | Nudges customers towards the free shipping threshold |
| Medium | Audio player | Customers can listen to the release on the product page |
| Low | Contact form when out of stock | Customers can ask about sold-out items instead of leaving |

---

## 1. Release date

Origin: `functions.php:221-232`.

Shows a notice box "Releasetermin / Release date: {date}" on the product page when the product meta `release_date` is set.

- Shortcode `[yuc_release_date]`, placed in the Single Product template in the Site Editor. The old hook `woocommerce_single_product_summary` fires only once in the block template, directly before the short description [Code], which is too far down; the shortcode can be placed anywhere, e.g. below title or price.
- Read with `get_post_meta( get_the_ID(), 'release_date', true )`.
- Stored format is `Y-m-d`, e.g. `2026-11-27`.
- **New:** hide the box automatically once the release date has passed. Parse the stored value with `DateTimeImmutable` in the site timezone (`wp_timezone()`); show only while the date is today or in the future. If the value cannot be parsed, show it as before so existing data keeps working.
- Output via `wc_print_notice( $text, 'notice', [], true )` (returned, not echoed), date escaped with `esc_html`.

Test on live: a product with a future `release_date` shows the box at the position of the shortcode block; one with a past date does not; one without the meta does not.

## 2. Phone required per shipping zone

Origin: `functions.php:237-370` plus three rules from the old Customizer CSS.

The billing phone becomes required when the chosen shipping method belongs to shipping zone 0 ("Rest of the world") or 3 ("Amerika").

- Resolve the rate IDs (`method_id:instance_id`) of all enabled methods in those zones via `WC_Shipping_Zones::get_zone()`; compare with `WC()->session->get( 'chosen_shipping_methods' )`.
- `woocommerce_checkout_fields` (priority 20): set `billing_phone['required']` accordingly, so the server renders the field as required when the method is already chosen.
- `woocommerce_after_checkout_validation`: add an error if the phone is empty while required. This is the actual enforcement. When the session already had the method, WooCommerce adds its own generic `billing_phone_required` error from the field flag; the handler removes it and adds the German message with the same code, so only one message appears.
- JS (`assets/checkout-phone.js`, plain JavaScript, loaded only on the checkout page): keep the field in sync when the customer changes the shipping method. Listen with a delegated `change` listener on `input[name^="shipping_method"]` and a `MutationObserver` on `#order_review`, because WooCommerce's `updated_checkout` is a jQuery-only event. Set `required`/`aria-required` on the input, toggle `validate-required` on the `.form-row` (WooCommerce's own inline validation reads it) and **swap the label marker WooCommerce rendered**: `<span class="optional">(optional)</span>` ↔ `<span class="required" aria-hidden="true">*</span>` [Code]. This reuses WooCommerce's own markup and styles, so no CSS file is needed. (The old code hid both markers and drew its own asterisk via CSS; that also hid "(optional)" on every checkout field.) The rate IDs reach the script via `wp_add_inline_script()`.
- Requires the shortcode checkout; the Checkout block does not apply these hooks [Code].

Test on live: with a German address the phone field stays optional and an order without phone goes through (use a test payment method or stop before paying). Switch the country to USA: the label changes to `*` without reload; submitting without phone shows the error; with phone the order validates. Switch back: label returns to "(optional)".

## 3. Free shipping notice

Origin: a CSS `content` rule attached to `flat_rate:2` (`style.css:518-528`). Replaced by a text line plus an animated progress bar.

Shows on the cart and checkout page: "Noch {amount} bis zum kostenlosen Versand" while below the threshold, "Versandkostenfrei!" once reached, with a bar whose green fill is the cart subtotal as a share of the threshold (capped at 100 %).

- Hooks `woocommerce_before_cart_table` (cart, inside the cart form directly above the product table) and `woocommerce_checkout_before_order_review` (checkout, between the "Your order" heading and the order table). The cart form is re-rendered on AJAX quantity updates, so the bar follows [Code]; `woocommerce_before_cart` would sit higher but outside the form and go stale. The checkout position is not re-rendered on address changes.
- Read the threshold from the shipping settings instead of hard-coding it: zone for the current customer via `WC_Shipping_Zones::get_zone_matching_package()`, then the enabled `free_shipping` method whose `requires` is `min_amount` or `either`, and its `min_amount`. Nothing is shown if the zone has no such method or the amount is 0 (coupon-based free shipping is out of scope).
- Compare with `WC()->cart->get_displayed_subtotal()`.
- Markup: `div.yuc-free-shipping` with `p.yuc-free-shipping__text` and `div.yuc-free-shipping__bar > div.yuc-free-shipping__fill` (inline `width` in percent, bar is `aria-hidden`); amount formatted with `wc_price()`. No WooCommerce notice box. Inside the fill an inline truck SVG (Feather Icons, MIT) rides on its right end.
- `assets/free-shipping-notice.css`, enqueued on cart and checkout: 1.5 rem bar, fill in `#0f834d` (the old free shipping green) with diagonal stripes moved by a `background-position` animation (barber pole, 2 s per period), disabled under `prefers-reduced-motion`. Track colour from `color-mix()` with `currentColor` so it works on light and dark backgrounds. The truck is a green circle centred on the fill's end, so it starts at the left edge and arrives at the right edge at 100 %.
- The Mini-Cart block renders its drawer client-side from the Store API; PHP hooks do not reach it, so it shows no bar.

Test on live: cart below the threshold shows the remaining amount and a partly filled bar; adding items past the threshold shows "Versandkostenfrei!" and a full bar; the checkout shows the same; a shipping address in a zone without a free shipping method shows nothing.

## 4. Audio player

Origin: `inc/yuc-template-functions.php:3-103`, `content-release.php:16-67`, `assets/javascript/audio-player.js`, `style.css:248-310`.

Plays the release on the product page. Two variants, decided by Pods fields on the product:

1. `release_listen` set → the linked `release` post provides `files` (MP3 attachments). Render an HTML5 `<audio>` element with a playlist, track title, previous/next controls.
2. No release, but `bandcamp_album_id` (numeric, e.g. `1474487848`) or `soundcloud_id` set → a "▶ Listen to {title}" trigger; on click, replace it with the Bandcamp or SoundCloud iframe (`single_track` decides between track and album/playlist embed). The iframe is not loaded before the click.

Implementation:

- Shortcode `[yuc_player]`, placed in the Single Product template in the Site Editor. Unlike the old hook `woocommerce_after_add_to_cart_button`, this also renders for out-of-stock products (WooCommerce renders no add-to-cart form there [Code]).
- Template `templates/release-player.php` for the playlist markup; iframe markup inline in the shortcode callback.
- `assets/audio-player.js` in plain JavaScript, scoped to the player container (not every `<audio>` on the page). Fix the old off-by-one: the last track was skipped on auto-advance (`audio-player.js:42-51`); after the last track the playlist starts over. Assets enqueued on `wp_enqueue_scripts` for `is_product()`: enqueuing from the shortcode callback printed the CSS in the footer and the hidden playlist flashed on load.
- `assets/audio-player.css` from `style.css:248-310`; trigger and previous/next are `<button>` elements with a minimal reset.
- The external embed sits in a `<template>` next to the trigger button and is moved into the DOM on click.
- Pods access via `pods( 'product', $id )->field( … )`; the release's `files` field returns attachment arrays (`ID`, `post_title`). File URLs via `wp_get_attachment_url( $file['ID'] )`, not the stored `guid`, so they follow the site URL. Without Pods the shortcode renders nothing.

Test on live: a product with a release plays tracks, previous/next and auto-advance reach the last track; a product with only a Bandcamp ID shows the trigger and loads the iframe on click, no iframe request before; a product with neither renders nothing; an out-of-stock product still shows the player.

## 5. Contact form when out of stock

Origin: `functions.php:112-121`.

Shows Contact Form 7 form 4669 on the product page when the product is not in stock.

- Hook `woocommerce_single_product_summary`, priority 30.
- `if ( ! $product->is_in_stock() ) echo do_shortcode( '[contact-form-7 id="4669"]' );`
- Port as is; no change in behaviour.

Test on live: an out-of-stock product shows the form; an in-stock product does not.

---

## Structure

The repository root is the plugin directory (`wp-content/plugins/yuc-shop-features/`).

```
yuc-shop-features.php        # header, WooCommerce check, loads includes/*.php
includes/
├── release-date.php
├── checkout-phone.php
├── free-shipping-notice.php
├── audio-player.php
└── out-of-stock-contact.php
templates/
└── release-player.php
assets/
├── audio-player.js / .css
├── checkout-phone.js
└── free-shipping-notice.css
```

Procedural, prefix `yuc_`, no classes, no jQuery.

## Notes

- `woocommerce_single_product_summary` fires once in the block template, directly before the short description block (`core/post-excerpt`) [Code]. The block must stay in the template for the out-of-stock contact form (5).
- Shortcode blocks for `[yuc_release_date]` (1) and `[yuc_player]` (4) must be placed in the Single Product template.
- OG data: nothing to rebuild. AIOSEO outputs `og:*`, WooCommerce outputs Schema.org Product/AggregateOffer (checked live).
- Logo: the Site Logo block still points to `wp-content/themes/youngandcold/assets/images/young_and_cold_web.png` (checked live). Upload to the media library and reassign before deleting the child theme.
- Germanized legal notices on the product page: check for duplicates or missing output after each deployment.
