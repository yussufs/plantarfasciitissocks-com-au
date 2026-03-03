<?php
/**
 * Product benefits list — parsed from short description.
 *
 * Expects: $product_benefits (array of strings) via set_query_var().
 *
 * @package BrandTheme
 */

defined( 'ABSPATH' ) || exit;

$product_benefits = get_query_var( 'product_benefits', array() );

if ( empty( $product_benefits ) ) {
    return;
}
?>
<ul class="product-benefits-list">
    <?php foreach ( $product_benefits as $benefit ) : ?>
        <li class="product-benefit-item">
            <?php brand_theme_icon( 'check', array( 'class' => 'product-benefit-icon' ) ); ?>
            <span><?php echo esc_html( $benefit ); ?></span>
        </li>
    <?php endforeach; ?>
</ul>
