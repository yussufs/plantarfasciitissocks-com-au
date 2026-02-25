# WooCommerce Template Overrides

Place WooCommerce template overrides in this directory.

## How to override a template

1. Find the template you want to override in:
   `wp-content/plugins/woocommerce/templates/`

2. Copy it into this `woocommerce/` directory, maintaining the same subdirectory structure.

3. Modify your copy.

## Examples

| Original template | Override location |
|---|---|
| `woocommerce/templates/single-product.php` | `woocommerce/single-product.php` |
| `woocommerce/templates/cart/cart.php` | `woocommerce/cart/cart.php` |
| `woocommerce/templates/checkout/form-checkout.php` | `woocommerce/checkout/form-checkout.php` |
| `woocommerce/templates/loop/price.php` | `woocommerce/loop/price.php` |

## Notes

- Only override templates you need to customise.
- WooCommerce will warn you in the admin if your overrides are outdated after a plugin update.
- FunnelKit Pro may override checkout templates — check for conflicts.
- See the [WooCommerce template structure docs](https://woocommerce.com/document/template-structure/) for the full list.
