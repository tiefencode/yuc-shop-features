# Bundle blocks script only on cart/checkout

Status: live · Prio: very low · Code: `includes/bundle-blocks-script.php`

**Value:** WPC Product Bundles pulls the whole Cart/Checkout React bundle (~600 KB) onto every page; it is only useful on cart and checkout.

Origin: Lighthouse analysis 2026-10-15. `wp-components` (265 KB, 199 KB unused), `blocks-checkout`, `blocks-components`, `wc-cart-checkout-base/vendors`, React, `wp-data`, `moment`, `wp-rich-text` and more loaded on every page, roughly 600 KB of JavaScript.

The script `woosb-blocks` of WPC Product Bundles is dequeued on every page except cart and checkout, which takes its dependency chain with it.

## Implementation

- WPC registers an `IntegrationInterface` for the Mini-Cart, Cart and Checkout blocks (`includes/class-blocks.php`, WPC Product Bundles 8.7.5). `initialize()` registers `woosb-blocks` with the dependency `wc-blocks-checkout`, in the footer. The Mini-Cart block enqueues all integration script handles inside `render()` [Code, `MiniCart.php` 11.2.1], so the script is enqueued during body rendering on every page with the header Mini-Cart.
- The script only registers checkout filters (`cartItemClass`, `cartItemPrice`, `subtotalPriceFormat`, `itemName`, `showRemoveItemLink`) for the React Cart/Checkout components. Cart and checkout run as shortcodes, and the Mini-Cart drawer is the Interactivity API version, which does not use those filters. The script has no effect on this site; it is still left in place on cart and checkout in case either is switched to the block.
- `wp_print_footer_scripts`, priority 1 (before `_wp_footer_scripts` prints at 10 [Code]): `wp_dequeue_script( 'woosb-blocks' )` unless `is_cart()` or `is_checkout()`. Dependencies that nothing else needs are not printed. `gtm4wp-woocommerce-blocks` keeps `wp-data` and its chain on the page; that is GTM4WP's own dependency and out of scope.
- The 496-byte stylesheet `woosb-blocks` stays enqueued.

## Test on staging

View-source of `/`: no `components.min.js`, no `wc-cart-checkout-base-frontend.js`, no `blocks-checkout.js`; Mini-Cart drawer opens and adding a product works; cart and checkout pages still load `woosb-blocks`.
