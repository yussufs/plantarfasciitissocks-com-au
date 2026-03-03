<?php
/**
 * Delivery estimate with truck icon and date range.
 *
 * Expects: $delivery_days (string like "3-7") via set_query_var().
 *
 * @package BrandTheme
 */

defined( 'ABSPATH' ) || exit;

$delivery_days = get_query_var( 'delivery_days', '3-7' );

// Parse range to compute dates.
$parts    = explode( '-', $delivery_days );
$min_days = max( 1, (int) ( $parts[0] ?? 3 ) );
$max_days = max( $min_days, (int) ( $parts[1] ?? $min_days ) );

$date_format = 'M j';
$min_date    = wp_date( $date_format, strtotime( "+{$min_days} weekdays" ) );
$max_date    = wp_date( $date_format, strtotime( "+{$max_days} weekdays" ) );
?>
<div class="delivery-estimate">
    <?php brand_theme_icon( 'truck', array( 'class' => 'delivery-estimate-icon' ) ); ?>
    <span>
        <?php
        printf(
            /* translators: %1$s: earliest delivery date, %2$s: latest delivery date */
            esc_html__( 'Estimated delivery: %1$s – %2$s', 'brand-theme' ),
            esc_html( $min_date ),
            esc_html( $max_date )
        );
        ?>
    </span>
</div>
