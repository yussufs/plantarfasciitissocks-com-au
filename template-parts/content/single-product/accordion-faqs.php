<?php
/**
 * FAQs accordion.
 *
 * Expects: $product_faqs (array of {'q','a'} objects) via set_query_var().
 *
 * @package BrandTheme
 */

defined( 'ABSPATH' ) || exit;

$product_faqs = get_query_var( 'product_faqs', array() );

if ( empty( $product_faqs ) || ! is_array( $product_faqs ) ) {
    return;
}
?>
<details class="product-accordion">
    <summary>
        <span class="flex items-center gap-2">
            <?php brand_theme_icon( 'help-circle', array( 'class' => 'h-4 w-4 text-zinc-500' ) ); ?>
            <?php esc_html_e( 'FAQs', 'brand-theme' ); ?>
        </span>
        <?php brand_theme_icon( 'chevron-down', array( 'class' => 'product-accordion-icon' ) ); ?>
    </summary>
    <div class="product-accordion-body space-y-4">
        <?php foreach ( $product_faqs as $faq ) :
            if ( empty( $faq['q'] ) ) {
                continue;
            }
            ?>
            <div>
                <p class="font-semibold text-zinc-900"><?php echo esc_html( $faq['q'] ); ?></p>
                <p class="mt-1"><?php echo esc_html( $faq['a'] ?? '' ); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</details>
