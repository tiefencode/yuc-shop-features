# Release date

Status: live · Prio: high · Code: `includes/release-date.php`

**Value:** Customers see when a pre-order ships.

Origin: `functions.php:221-232` of the old child theme.

Shows a notice box "Releasetermin / Release date: {date}" on the product page when the product meta `release_date` is set.

## Implementation

- Shortcode `[yuc_release_date]`, placed in the Single Product template in the Site Editor. The old hook `woocommerce_single_product_summary` fires only once in the block template, directly before the short description [Code], which is too far down; the shortcode can be placed anywhere, e.g. below title or price.
- Read with `get_post_meta( get_the_ID(), 'release_date', true )`.
- Stored format is `Y-m-d`, empty fields `0000-00-00`. `get_post_meta()` returns the Pods display value instead: `d.m.Y` (e.g. `15.03.2021`), empty string for `0000-00-00` (checked live). Parse `Y-m-d` first, then `d.m.Y`.
- Hide the box automatically once the release date has passed. Parse the stored value with `DateTimeImmutable` in the site timezone (`wp_timezone()`); show only while the date is today or in the future. If the value cannot be parsed, show it as before so existing data keeps working.
- Output via `wc_print_notice( $text, 'notice', [], true )` (returned, not echoed), date escaped with `esc_html`.

## Test on staging

A product with a future `release_date` shows the box at the position of the shortcode block; one with a past date does not; one without the meta does not.
