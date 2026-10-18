# Details button in product listings

Status: live · Prio: medium · Code: `includes/listing-details-button.php`

**Value:** Listings link to the product page instead of adding to the cart, with the native block styling intact.

Origin: the Germanized option "Link to product details page instead of add to cart within listings". Germanized implements it by replacing the whole Product Button block output on `woocommerce_loop_add_to_cart_link` with a bare `<a class="button">` (`wc-gzd-template-functions.php:991-1009`, Germanized 4.1.5). The block wrapper with width, alignment and inline styles from the Site Editor is lost. The option stays off.

Product buttons in product listings link to the product page and read "Details"; nothing is added to the cart from a listing. The block markup stays untouched.

## Implementation

- `ProductButton::render()` decides between an AJAX `<button>` and a plain `<a href>` from `$product->supports( 'ajax_add_to_cart' )`, and takes target and label from `add_to_cart_url()`, `add_to_cart_text()` and `add_to_cart_description()` [Code]. All four are filterable per product; wrapper, classes and styles are built from the block attributes independently.
- `woocommerce_product_supports`: `false` for `ajax_add_to_cart`. The block then renders the same `<a>` it already renders for variable products.
- `woocommerce_product_add_to_cart_url`: permalink. Target equals permalink, so the block sets no `rel="nofollow"`.
- `woocommerce_product_add_to_cart_text`: "Details". `woocommerce_product_add_to_cart_description`: "Details zu „{name}“" for the `aria-label`.
- The single product page is not affected: its button is a form submit and uses `single_add_to_cart_text()`; the four methods are only called in listing, grid and API contexts [Code]. Exception: the external-product button on the single page also uses `add_to_cart_url()`; the shop has no external products.
- Germanized "Show add to cart button on listings" must stay on. When it is off (and the link option is off), Germanized returns `false` on `woocommerce_loop_add_to_cart_link` and the block renders nothing (`wc-gzd-template-hooks.php:131-134`). Both Germanized options keep their effect on purpose; this feature only changes what the block renders when Germanized leaves it alone.

## Test on staging

In a Product Collection, every button reads "Details", links to the product page and keeps the width, alignment and style set in the Site Editor; clicking adds nothing to the cart. The product page still adds to the cart.
