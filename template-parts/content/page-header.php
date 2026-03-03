<?php
/**
 * Page header banner — brand primary background with white text.
 *
 * Usage: get_template_part( 'template-parts/content/page-header' );
 * Optional subtitle: set_query_var( 'page_subtitle', 'Your subtitle text' );
 * Optional title override: set_query_var( 'page_header_title', 'Custom Title' );
 *
 * @package BrandTheme
 */

defined( 'ABSPATH' ) || exit;

$title    = get_query_var( 'page_header_title', '' );
$subtitle = get_query_var( 'page_subtitle', '' );
?>
<div class="page-header">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-extrabold text-white sm:text-4xl"><?php echo $title ? esc_html( $title ) : get_the_title(); ?></h1>
        <?php if ( $subtitle ) : ?>
            <p class="mt-2 text-brand-100"><?php echo esc_html( $subtitle ); ?></p>
        <?php endif; ?>
    </div>
</div>
