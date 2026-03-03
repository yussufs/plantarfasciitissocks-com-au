<?php
/**
 * Product badge — "HOT PRODUCT | LOW STOCK" pill.
 *
 * @package BrandTheme
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="product-badge">
    <?php brand_theme_icon( 'flame', array( 'class' => 'h-3.5 w-3.5' ) ); ?>
    <?php esc_html_e( 'HOT PRODUCT', 'brand-theme' ); ?>
    <span class="opacity-60">|</span>
    <?php esc_html_e( 'LOW STOCK', 'brand-theme' ); ?>
</div>
