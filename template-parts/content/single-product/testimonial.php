<?php
/**
 * Customer testimonial card with avatar and 5-star rating.
 *
 * Expects: $testimonial (array with 'quote', 'author', 'verified') via set_query_var().
 *
 * @package BrandTheme
 */

defined( 'ABSPATH' ) || exit;

$testimonial = get_query_var( 'testimonial', null );

if ( ! $testimonial || empty( $testimonial['quote'] ) ) {
    return;
}

$author   = $testimonial['author'] ?? __( 'Verified Customer', 'brand-theme' );
$verified = $testimonial['verified'] ?? true;
$initial  = strtoupper( mb_substr( $author, 0, 1 ) );
?>
<div class="testimonial-card">
    <div class="testimonial-stars">
        <?php for ( $i = 0; $i < 5; $i++ ) : ?>
            <?php brand_theme_icon( 'star', array( 'class' => 'h-4 w-4 text-amber-400 fill-current' ) ); ?>
        <?php endfor; ?>
    </div>
    <p class="testimonial-text">"<?php echo esc_html( $testimonial['quote'] ); ?>"</p>
    <div class="testimonial-author">
        <span class="testimonial-avatar"><?php echo esc_html( $initial ); ?></span>
        <div>
            <p class="testimonial-name"><?php echo esc_html( $author ); ?></p>
            <?php if ( $verified ) : ?>
                <p class="testimonial-verified">
                    <?php brand_theme_icon( 'check', array( 'class' => 'inline h-3.5 w-3.5 mr-0.5 -mt-px' ) ); ?>
                    <?php esc_html_e( 'Verified Purchase', 'brand-theme' ); ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>
