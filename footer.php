<?php
/**
 * Theme footer.
 *
 * @package BrandTheme
 */
?>

<footer class="mt-auto border-t border-gray-200 bg-gray-50">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="md:flex md:items-center md:justify-between">
            <nav>
                <?php
                wp_nav_menu( array(
                    'theme_location' => 'footer',
                    'container'      => false,
                    'menu_class'     => 'flex flex-wrap gap-6 text-sm text-gray-600',
                    'fallback_cb'    => false,
                    'depth'          => 1,
                ) );
                ?>
            </nav>

            <p class="mt-4 text-sm text-gray-500 md:mt-0">
                &copy; <?php echo esc_html( gmdate( 'Y' ) ); ?>
                <?php bloginfo( 'name' ); ?>.
                <?php esc_html_e( 'All rights reserved.', 'brand-theme' ); ?>
            </p>
        </div>

        <?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
            <div class="mt-8 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                <?php dynamic_sidebar( 'footer-1' ); ?>
            </div>
        <?php endif; ?>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
