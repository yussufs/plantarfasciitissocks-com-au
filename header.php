<?php
/**
 * Theme header.
 *
 * @package BrandTheme
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'bg-white text-gray-900 antialiased' ); ?>>
<?php wp_body_open(); ?>

<header class="border-b border-gray-200">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="text-xl font-bold text-gray-900">
                <?php bloginfo( 'name' ); ?>
            </a>

            <nav class="hidden md:block">
                <?php
                wp_nav_menu( array(
                    'theme_location' => 'primary',
                    'container'      => false,
                    'menu_class'     => 'flex items-center gap-8',
                    'fallback_cb'    => false,
                    'depth'          => 2,
                ) );
                ?>
            </nav>

            <div class="flex items-center gap-4">
                <?php if ( function_exists( 'wc_get_cart_url' ) ) : ?>
                    <a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="text-gray-600 hover:text-gray-900">
                        <?php esc_html_e( 'Cart', 'brand-theme' ); ?>
                        (<span class="cart-count"><?php echo esc_html( WC()->cart->get_cart_contents_count() ); ?></span>)
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
