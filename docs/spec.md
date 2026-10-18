# yuc-shop-features – spec

One file per feature in `docs/features/`. This file is the index plus the rules that apply to every feature.

Site facts (2026-10-18): Twenty Twenty-Five block theme, WooCommerce 11.2.1. Cart and checkout run as shortcodes (`[woocommerce_cart]`, `[woocommerce_checkout]`). Free shipping is a "Free shipping" method with a minimum order amount. Staging: `https://staging.youngandcold.de` (basic auth).

## Features

| # | Prio | Feature | Status |
|---|---|---|---|
| 1 | high | [Release date](features/01-release-date.md) | live |
| 2 | medium | [Phone required per shipping zone](features/02-checkout-phone.md) | live |
| 3 | medium | [Free shipping notice](features/03-free-shipping-notice.md) | live |
| 4 | medium | [Audio player](features/04-audio-player.md) | live |
| 5 | low | [Contact form when out of stock](features/05-out-of-stock-contact.md) | live |
| 6 | medium | [Details button in product listings](features/06-listing-details-button.md) | live |
| 7 | low | [Eager first listing image](features/07-listing-first-image.md) | live |
| 8 | very low | [Bundle blocks script only on cart/checkout](features/08-bundle-blocks-script.md) | live |
| 9 | medium | [Related products by artist and genre](features/09-related-products.md) | staging |

Status values: `planned` (spec written, no code) → `staging` (on a feature branch, deployed to staging) → `live` (merged to `main`, deployed to production).

## Writing a feature

Create `docs/features/NN-<slug>.md` with:

```
# <Feature name>

Status: planned · Prio: <high|medium|low> · Code: <files, once known>

**Value:** <one sentence: why this exists, from the customer's or shop's view>

<Origin, if ported or triggered by an analysis.>

<What the feature does, in behaviour terms.>

## Implementation
<Hooks, APIs, markup, decisions. Mark source-verified claims with [Code].>

## Test on staging
<Cases that prove the behaviour; the staging-check agent runs the ones visible in page source.>
```

Keep it short. The old theme code, where it exists, defines the behaviour to keep, not how to build it; "better" means simpler, not more.

## Structure

The repository root is the plugin directory (`wp-content/plugins/yuc-shop-features/`).

```
yuc-shop-features.php        # header, WooCommerce check, loads includes/*.php
includes/<feature>.php       # one file per feature
templates/                   # PHP templates rendered by the plugin
assets/                      # plain JavaScript and CSS, no jQuery
docs/spec.md, docs/features/
```

Procedural, prefix `yuc_`, no classes, no jQuery.

## Notes

- `woocommerce_single_product_summary` fires once in the block template, directly before the short description block (`core/post-excerpt`) [Code]. The block must stay in the Single Product template (feature 5 depends on it).
- Shortcode blocks for `[yuc_release_date]` (1) and `[yuc_player]` (4) must be placed in the Single Product template.
- OG data: nothing to rebuild. AIOSEO outputs `og:*`, WooCommerce outputs Schema.org Product/AggregateOffer (checked live).
- Logo: the Site Logo block still points to `wp-content/themes/youngandcold/assets/images/young_and_cold_web.png` (checked live). Upload to the media library and reassign before deleting the child theme.
- Germanized legal notices on the product page: check for duplicates or missing output after each deployment.
