<?php
/**
 * 404 template.
 *
 * @package BrandTheme
 */

get_header();
?>

<div class="py-20 text-center">
    <h1 class="text-6xl font-bold text-gray-300">404</h1>
    <p class="mt-4 text-xl text-gray-600">
        <?php esc_html_e( 'Page not found.', 'brand-theme' ); ?>
    </p>
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="mt-6 inline-block rounded-md bg-brand-600 px-6 py-3 text-sm font-medium text-white hover:bg-brand-700">
        <?php esc_html_e( 'Go home', 'brand-theme' ); ?>
    </a>
</div>

<?php
get_footer();
