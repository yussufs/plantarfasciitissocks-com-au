<?php
/**
 * Trust badges — free shipping & money-back guarantee.
 *
 * @package BrandTheme
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="trust-badges">
	<div class="trust-badge">
		<?php brand_theme_icon( 'package', array( 'class' => 'trust-badge-icon' ) ); ?>
		<p><strong><?php esc_html_e( 'Free Shipping', 'brand-theme' ); ?></strong> <?php esc_html_e( 'from our warehouses in Brisbane and Sydney (1-2 days Express Post available)', 'brand-theme' ); ?></p>
	</div>
	<div class="trust-badge">
		<?php brand_theme_icon( 'shield-check', array( 'class' => 'trust-badge-icon' ) ); ?>
		<p><strong><?php esc_html_e( '30-Day Money Back Guarantee', 'brand-theme' ); ?></strong> — <?php esc_html_e( 'if you are not satisfied with your purchase, we will give you a full refund', 'brand-theme' ); ?></p>
	</div>
</div>
