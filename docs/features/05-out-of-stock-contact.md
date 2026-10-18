# Contact form when out of stock

Status: live · Prio: low · Code: `includes/out-of-stock-contact.php`

**Value:** Customers can ask about sold-out items instead of leaving.

Origin: `functions.php:112-121` of the old child theme.

Shows Contact Form 7 form 4669 on the product page when the product is not in stock.

## Implementation

- Hook `woocommerce_single_product_summary`, priority 30. In the block template this fires directly before the short description block, which must stay in the Single Product template [Code].
- `if ( ! $product->is_in_stock() ) echo do_shortcode( '[contact-form-7 id="4669"]' );`
- Ported as is; no change in behaviour.

## Test on staging

An out-of-stock product shows the form; an in-stock product does not.
