# Spec: porting features from the Storefront child theme

Status 2026-10-15: the theme switch to Twenty Twenty-Five is done. Cart and checkout run as shortcodes (checkout still to be confirmed in the editor). Everything that is handled in the Site Editor is not listed here.

Paths like `functions.php:221` refer to the old child theme `youngandcold/` (outside this repo). **[Code]** = verified in the WooCommerce 11.2.1 source. No jQuery in plugin code (plain JavaScript).

---

## 1. Features

| Prio | Feature | What it does | Origin | Implementation |
|---|---|---|---|---|
| **High** | Release date | Notice box "Releasetermin / Release date: …" on the product page, only when the meta `release_date` is set | `functions.php:221-232` | Hook `woocommerce_single_product_summary` (25), `get_post_meta($id, 'release_date', true)`. Port as is. |
| **Medium** | Phone required per shipping zone | Phone becomes required when the chosen shipping method belongs to zone 0 ("Rest of the world") or 3 ("Amerika"): PHP validation, JS toggles the field live, 3 CSS rules (hide `.optional`, hide the WC asterisk, own `*` via `.is-phone-required::after`) | `functions.php:237-370`, Customizer CSS | `woocommerce_checkout_fields` (20), `woocommerce_after_checkout_validation`. JS without jQuery: delegated `change` listener on `input[name^="shipping_method"]` + `MutationObserver` on `#order_review` (WC's `updated_checkout` is a jQuery event, invisible to native JS). Requires the shortcode checkout [Code]. |
| **Medium** | Free shipping notice | In the cart: "X € more for free shipping" or "Free shipping!" | was CSS `content` attached to `flat_rate:2` (`style.css:518-528`) | Hook `woocommerce_before_cart_totals`. **Read the threshold from the shipping settings** (customer's zone → method `free_shipping` → `min_amount`), compare with `WC()->cart->get_displayed_subtotal()`, output via `wc_print_notice()`. No hard-coded 75 €. **Open:** is the threshold configured as a "Free shipping" method with a minimum order amount? |
| **Medium** | Audio player | Player on the product page: HTML5 playlist from the Pods release (`release_listen` → CPT `release`, field `files`); without a release, a Bandcamp/SoundCloud iframe (`bandcamp_album_id`, `soundcloud_id`, `single_track`) loaded on click | `inc/yuc-template-functions.php:3-103`, `content-release.php:16-67`, `assets/javascript/audio-player.js`, `style.css:248-310` | **Shortcode `[yuc_player]`**, placed in the product template in the Site Editor. This keeps the player visible for out-of-stock products (the old hook `woocommerce_after_add_to_cart_button` does not fire there because WC renders no button [Code]). Load the template file from the plugin. Rewrite the JS in plain JavaScript and fix: last track is skipped (`audio-player.js:42-51`), script grabs every `<audio>` on the page. |
| **Low** | Contact form when out of stock | CF7 form 4669 instead of the add-to-cart button | `functions.php:112-121` | Hook `woocommerce_single_product_summary` (30), `do_shortcode('[contact-form-7 id="4669"]')`. Port as is. |

---

## 2. Structure

The repository root is the plugin directory (`wp-content/plugins/yuc-shop-features/`).

```
yuc-shop-features.php      # header, WooCommerce check, loads includes/*.php
includes/
├── product-page.php       # release date, CF7 when out of stock
├── audio-player.php       # shortcode [yuc_player], enqueue only on is_product()
├── checkout-phone.php     # phone required per zone
└── free-shipping-notice.php
templates/
└── release-player.php     # from content-release.php (is_product() branch only)
assets/
├── audio-player.js / .css
└── checkout-phone.js / .css
```

Procedural, prefix `yuc_`, no classes, no jQuery.

---

## 3. Notes

- **Position of the summary hooks:** in the block template WC fires `woocommerce_single_product_summary` once, directly **before** the short description block (`core/post-excerpt`) [Code]. Release date and contact form appear there. The block must stay in the template. If the position is a problem, build both as shortcodes too.
- **OG data:** done, nothing to rebuild. AIOSEO outputs `og:*`, WooCommerce outputs Schema.org Product/AggregateOffer as JSON-LD (checked live). Optionally set `og:type` to "product" in AIOSEO.
- **Logo:** the Site Logo block still points to `wp-content/themes/youngandcold/assets/images/young_and_cold_web.png` (checked live). Upload to the media library and reassign before deleting the child theme.
- **Germanized legal notices** on the product page: check for duplicates or missing output; use the Germanized product blocks if needed.
