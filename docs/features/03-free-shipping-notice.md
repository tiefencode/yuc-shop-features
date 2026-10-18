# Free shipping notice

Status: live · Prio: medium · Code: `includes/free-shipping-notice.php`, `assets/free-shipping-notice.css`

**Value:** Nudges customers towards the free shipping threshold.

Origin: a CSS `content` rule attached to `flat_rate:2` (`style.css:518-528` of the old child theme). Replaced by a text line plus an animated progress bar.

Shows on the cart and checkout page: "Noch {amount} bis zum kostenlosen Versand" while below the threshold, "Versandkostenfrei!" once reached, with a bar whose green fill is the cart subtotal as a share of the threshold (capped at 100 %).

## Implementation

- Hooks `woocommerce_before_cart_table` (cart, inside the cart form directly above the product table) and `woocommerce_checkout_before_order_review` (checkout, between the "Your order" heading and the order table). The cart form is re-rendered on AJAX quantity updates, so the bar follows [Code]; `woocommerce_before_cart` would sit higher but outside the form and go stale. The checkout position is not re-rendered on address changes.
- Read the threshold from the shipping settings instead of hard-coding it: zone for the current customer via `WC_Shipping_Zones::get_zone_matching_package()`, then the enabled `free_shipping` method whose `requires` is `min_amount` or `either`, and its `min_amount`. Nothing is shown if the zone has no such method or the amount is 0 (coupon-based free shipping is out of scope).
- Compare with `WC()->cart->get_displayed_subtotal()`.
- Markup: `div.yuc-free-shipping` with `p.yuc-free-shipping__text` and `div.yuc-free-shipping__bar > div.yuc-free-shipping__fill` (inline `width` in percent, bar is `aria-hidden`); amount formatted with `wc_price()`. No WooCommerce notice box. Inside the fill an inline truck SVG (Feather Icons, MIT) rides on its right end.
- `assets/free-shipping-notice.css`, enqueued on cart and checkout: 1.5 rem bar, fill in `#0f834d` (the old free shipping green) with diagonal stripes moved by a `background-position` animation (barber pole, 2 s per period), disabled under `prefers-reduced-motion`. Track colour from `color-mix()` with `currentColor` so it works on light and dark backgrounds. The truck is a green circle flush with the fill's end; the fill has a min-width of one circle so the truck is not cut off at small shares, and it reaches the right edge once the threshold is met.
- The Mini-Cart block renders its drawer client-side from the Store API; PHP hooks do not reach it, so it shows no bar.

## Test on staging

Cart below the threshold shows the remaining amount and a partly filled bar; adding items past the threshold shows "Versandkostenfrei!" and a full bar; the checkout shows the same; a shipping address in a zone without a free shipping method shows nothing.
