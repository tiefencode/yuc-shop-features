# Phone required per shipping zone

Status: live · Prio: medium · Code: `includes/checkout-phone.php`, `assets/checkout-phone.js`

**Value:** Carriers outside the EU need a phone number; orders without one get stuck.

Origin: `functions.php:237-370` of the old child theme plus three rules from the old Customizer CSS.

The billing phone becomes required when the chosen shipping method belongs to shipping zone 0 ("Rest of the world") or 3 ("Amerika").

## Implementation

- Resolve the rate IDs (`method_id:instance_id`) of all enabled methods in those zones via `WC_Shipping_Zones::get_zone()`; compare with `WC()->session->get( 'chosen_shipping_methods' )`.
- `woocommerce_checkout_fields` (priority 20): set `billing_phone['required']` accordingly, so the server renders the field as required when the method is already chosen.
- `woocommerce_after_checkout_validation`: add an error if the phone is empty while required. This is the actual enforcement. When the session already had the method, WooCommerce adds its own generic `billing_phone_required` error from the field flag; the handler removes it and adds the German message with the same code, so only one message appears.
- JS (`assets/checkout-phone.js`, plain JavaScript, loaded only on the checkout page): keep the field in sync when the customer changes the shipping method. Listen with a delegated `change` listener on `input[name^="shipping_method"]` and a `MutationObserver` on `#order_review`, because WooCommerce's `updated_checkout` is a jQuery-only event. Set `required`/`aria-required` on the input, toggle `validate-required` on the `.form-row` (WooCommerce's own inline validation reads it) and swap the label marker WooCommerce rendered: `<span class="optional">(optional)</span>` ↔ `<span class="required" aria-hidden="true">*</span>` [Code]. This reuses WooCommerce's own markup and styles, so no CSS file is needed. The rate IDs reach the script via `wp_add_inline_script()`.
- Requires the shortcode checkout; the Checkout block does not apply these hooks [Code].

## Test on staging

With a German address the phone field stays optional and an order without phone validates (stop before paying). Switch the country to USA: the label changes to `*` without reload; submitting without phone shows the error; with phone the order validates. Switch back: label returns to "(optional)".
