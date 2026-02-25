<?php
/**
 * Product card template part.
 *
 * @package BrandTheme
 */

global $product;

if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
    return;
}
?>

<div class="group rounded-lg border border-gray-200 p-4 transition hover:shadow-md">
    <a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="block">
        <?php if ( $product->get_image_id() ) : ?>
            <div class="mb-4 overflow-hidden rounded-md">
                <?php echo wp_kses_post( $product->get_image( 'woocommerce_thumbnail', array( 'class' => 'h-48 w-full object-cover transition group-hover:scale-105' ) ) ); ?>
            </div>
        <?php endif; ?>

        <h3 class="text-sm font-medium text-gray-900 group-hover:text-brand-600">
            <?php echo esc_html( $product->get_name() ); ?>
        </h3>

        <p class="mt-1 text-sm font-semibold text-gray-900">
            <?php echo wp_kses_post( $product->get_price_html() ); ?>
        </p>
    </a>
</div>
