<?php
/**
 * Shipping & Returns accordion.
 *
 * Expects: $shipping_info (string, optional) via set_query_var().
 *
 * @package BrandTheme
 */

defined( 'ABSPATH' ) || exit;

$shipping_info = get_query_var( 'shipping_info', '' );

$default_info = sprintf(
    /* translators: %s: site name */
    __( 'We offer fast shipping Australia-wide. Orders are typically processed within 1-2 business days. If you\'re not satisfied with your purchase, you may return it within 30 days for a full refund. Please contact our support team to initiate a return.', 'brand-theme' ),
    get_bloginfo( 'name' )
);

$display_info = $shipping_info ?: $default_info;
?>
<details class="product-accordion" open>
    <summary>
        <span class="flex items-center gap-2">
            <?php brand_theme_icon( 'truck', array( 'class' => 'h-4 w-4 text-zinc-500' ) ); ?>
            <?php esc_html_e( 'Shipping & Returns', 'brand-theme' ); ?>
        </span>
        <?php brand_theme_icon( 'chevron-down', array( 'class' => 'product-accordion-icon' ) ); ?>
    </summary>
    <div class="product-accordion-body">
        <?php echo wp_kses_post( wpautop( $display_info ) ); ?>
    </div>
</details>
