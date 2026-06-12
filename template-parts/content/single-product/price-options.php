<?php
/**
 * Static price + Svelte options mount point.
 *
 * The static price sits directly above the options mount so the Svelte price
 * row (rendered first inside the mount) takes its place when ProductOptions
 * hides it on hydrate — no layout shift for content above (trust badges).
 *
 * The mount carries a per-product min-height estimate (applied via
 * #product-options:empty in app.css) so content below it doesn't shift down
 * when the component hydrates (CLS).
 *
 * Expects query var:
 * - svelte_data: array from brand_theme_get_product_svelte_data()
 *
 * @package BrandTheme
 */

defined( 'ABSPATH' ) || exit;

global $product;

$svelte_data = get_query_var( 'svelte_data' );
if ( ! is_array( $svelte_data ) || ! $product ) {
	return;
}

// Row heights mirror ProductOptions.svelte at mobile widths: price ~40px,
// attribute row ~70px, bundle tier card ~74px (+28px label), quantity
// stepper ~74px, CTA ~52px, space-y-3 gaps 12px. The static price above
// collapses when the component renders its own (~44px with gap), hence the
// subtraction.
$attr_rows     = count( $svelte_data['colorAttributes'] ) + count( $svelte_data['selectAttributes'] );
$tier_count    = count( $svelte_data['bundleTiers'] );
$options_est   = 40
	+ $attr_rows * 70
	+ ( $tier_count > 1 ? 28 + $tier_count * 74 : 74 )
	+ 52
	+ 12 * ( 2 + $attr_rows );
$options_min_h = max( 0, $options_est - 44 );
?>
<!-- Server-rendered price (SEO + first paint) — hidden when Svelte mounts -->
<div id="product-price-static" class="flex items-center gap-3">
	<?php if ( $product->is_on_sale() ) : ?>
		<span class="product-price"><?php echo wp_kses_post( wc_price( $product->get_sale_price() ) ); ?></span>
		<span class="product-price-compare"><?php echo wp_kses_post( wc_price( $product->get_regular_price() ) ); ?></span>
	<?php else : ?>
		<span class="product-price"><?php echo wp_kses_post( wc_price( $product->get_regular_price() ) ); ?></span>
	<?php endif; ?>
</div>

<!-- Svelte interactive options (swatches, bundles, CTA buttons) -->
<div id="product-options" style="--options-min-h: <?php echo esc_attr( $options_min_h ); ?>px" data-config='<?php echo esc_attr( wp_json_encode( $svelte_data ) ); ?>'></div>
