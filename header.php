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
    <style id="brand-theme-nav-critical">
        .site-submenu {
            display: none;
            position: absolute;
        }
        .menu-item-has-children:hover > .site-submenu,
        .menu-item-has-children:focus-within > .site-submenu {
            display: block;
        }
    </style>
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'bg-white text-gray-900 antialiased' ); ?>>
<?php wp_body_open(); ?>

<?php
$home_url      = home_url( '/' );
$posts_page_id = (int) get_option( 'page_for_posts' );
$blog_url      = $posts_page_id ? get_permalink( $posts_page_id ) : home_url( '/blog/' );

$contact_page = get_page_by_path( 'contact-us' );
$contact_url  = $contact_page ? get_permalink( $contact_page ) : home_url( '/contact-us/' );

$tracking_url = 'https://auspost.com.au/mypost/track/search';

$privacy_url = get_privacy_policy_url();
if ( ! $privacy_url ) {
    $privacy_url = home_url( '/privacy-policy/' );
}

$refund_page = get_page_by_path( 'refund-policy' );
if ( ! $refund_page ) {
    $refund_page = get_page_by_path( 'refunds' );
}
$refund_url = $refund_page ? get_permalink( $refund_page ) : home_url( '/refund-policy/' );

$terms_page_id = 0;
if ( function_exists( 'wc_terms_and_conditions_page_id' ) ) {
    $terms_page_id = (int) wc_terms_and_conditions_page_id();
}
if ( ! $terms_page_id ) {
    $terms_page_id = (int) get_option( 'woocommerce_terms_page_id' );
}
$terms_url = $terms_page_id ? get_permalink( $terms_page_id ) : home_url( '/terms-and-conditions/' );

$shop_url      = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$search_url    = home_url( '/?s=' );
$cart_url      = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );
$product_links = array();
if ( post_type_exists( 'product' ) ) {
    $products = get_posts( array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => 6,
        'orderby'        => 'menu_order title',
        'order'          => 'ASC',
    ) );

    foreach ( $products as $product ) {
        $product_url = get_permalink( $product );
        if ( ! $product_url ) {
            continue;
        }

        $product_links[] = array(
            'title' => get_the_title( $product ),
            'url'   => $product_url,
        );
    }
}
?>
<header class="border-b border-gray-200">
    <div class="announcement-bar">
        <p><?php esc_html_e( 'SITEWIDE SALE ENDING IN 72 HOURS!', 'brand-theme' ); ?></p>
    </div>

    <div class="site-nav-shell mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="site-nav-row">
            <nav class="site-nav site-nav-left" aria-label="<?php esc_attr_e( 'Primary menu left', 'brand-theme' ); ?>">
                <ul class="site-menu">
                    <li><a class="menu-link" href="<?php echo esc_url( $home_url ); ?>"><?php esc_html_e( 'Home', 'brand-theme' ); ?></a></li>
                    <li class="menu-item-has-children">
                        <a class="menu-link" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Our Products', 'brand-theme' ); ?></a>
                        <ul class="site-submenu">
                            <li><a href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Shop All Products', 'brand-theme' ); ?></a></li>
                            <?php foreach ( $product_links as $product_link ) : ?>
                                <li><a href="<?php echo esc_url( $product_link['url'] ); ?>"><?php echo esc_html( $product_link['title'] ); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li><a class="menu-link" href="<?php echo esc_url( $blog_url ); ?>"><?php esc_html_e( 'Blog', 'brand-theme' ); ?></a></li>
                </ul>
            </nav>

            <a href="<?php echo esc_url( $home_url ); ?>" class="site-logo" aria-label="<?php esc_attr_e( 'Go to homepage', 'brand-theme' ); ?>">
                <?php bloginfo( 'name' ); ?>
            </a>

            <nav class="site-nav site-nav-right" aria-label="<?php esc_attr_e( 'Primary menu right', 'brand-theme' ); ?>">
                <ul class="site-menu">
                    <li><a class="menu-link" href="<?php echo esc_url( $contact_url ); ?>"><?php esc_html_e( 'Contact Us', 'brand-theme' ); ?></a></li>
                    <li><a class="menu-link" href="<?php echo esc_url( $tracking_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Tracking', 'brand-theme' ); ?></a></li>
                    <li class="menu-item-has-children">
                        <button type="button" class="menu-link menu-link-button"><?php esc_html_e( 'Legal', 'brand-theme' ); ?></button>
                        <ul class="site-submenu site-submenu-right">
                            <li><a href="<?php echo esc_url( $privacy_url ); ?>"><?php esc_html_e( 'Privacy Policy', 'brand-theme' ); ?></a></li>
                            <li><a href="<?php echo esc_url( $refund_url ); ?>"><?php esc_html_e( 'Refund Policy', 'brand-theme' ); ?></a></li>
                            <li><a href="<?php echo esc_url( $terms_url ); ?>"><?php esc_html_e( 'Terms and Conditions', 'brand-theme' ); ?></a></li>
                        </ul>
                    </li>
                </ul>
            </nav>

            <button
                type="button"
                class="mobile-nav-toggle lg:hidden"
                data-mobile-nav-toggle
                aria-expanded="false"
                aria-controls="mobile-site-nav"
            >
                <span class="sr-only"><?php esc_html_e( 'Toggle menu', 'brand-theme' ); ?></span>
                <span class="mobile-nav-toggle-line"></span>
                <span class="mobile-nav-toggle-line"></span>
                <span class="mobile-nav-toggle-line"></span>
            </button>
            <div class="mobile-actions lg:hidden">
                <a class="mobile-icon-link" href="<?php echo esc_url( $search_url ); ?>" aria-label="<?php esc_attr_e( 'Search', 'brand-theme' ); ?>">
                    <?php brand_theme_icon( 'search', array( 'class' => 'w-5 h-5' ) ); ?>
                </a>
                <a class="mobile-icon-link" href="<?php echo esc_url( $cart_url ); ?>" aria-label="<?php esc_attr_e( 'Cart', 'brand-theme' ); ?>">
                    <?php brand_theme_icon( 'shopping-cart', array( 'class' => 'w-5 h-5' ) ); ?>
                </a>
            </div>
        </div>

        <div class="mobile-drawer-backdrop lg:hidden" data-mobile-nav-overlay hidden aria-hidden="true"></div>
        <nav id="mobile-site-nav" class="mobile-site-nav" hidden aria-label="<?php esc_attr_e( 'Mobile menu', 'brand-theme' ); ?>">
            <div class="mobile-site-nav-header">
                <p><?php esc_html_e( 'Menu', 'brand-theme' ); ?></p>
                <button type="button" class="mobile-drawer-close" data-mobile-nav-close>
                    <span class="sr-only"><?php esc_html_e( 'Close menu', 'brand-theme' ); ?></span>
                    <?php brand_theme_icon( 'x', array( 'class' => 'w-5 h-5' ) ); ?>
                </button>
            </div>
            <ul class="mobile-menu-list">
                <li><a href="<?php echo esc_url( $home_url ); ?>"><?php esc_html_e( 'Home', 'brand-theme' ); ?></a></li>
                <li class="mobile-dropdown">
                    <details>
                        <summary><?php esc_html_e( 'Our Products', 'brand-theme' ); ?></summary>
                        <ul>
                            <li><a href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Shop All Products', 'brand-theme' ); ?></a></li>
                            <?php foreach ( $product_links as $product_link ) : ?>
                                <li><a href="<?php echo esc_url( $product_link['url'] ); ?>"><?php echo esc_html( $product_link['title'] ); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </details>
                </li>
                <li><a href="<?php echo esc_url( $blog_url ); ?>"><?php esc_html_e( 'Blog', 'brand-theme' ); ?></a></li>
                <li><a href="<?php echo esc_url( $contact_url ); ?>"><?php esc_html_e( 'Contact Us', 'brand-theme' ); ?></a></li>
                <li><a href="<?php echo esc_url( $tracking_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Tracking', 'brand-theme' ); ?></a></li>
                <li class="mobile-dropdown">
                    <details>
                        <summary><?php esc_html_e( 'Legal', 'brand-theme' ); ?></summary>
                        <ul>
                            <li><a href="<?php echo esc_url( $privacy_url ); ?>"><?php esc_html_e( 'Privacy Policy', 'brand-theme' ); ?></a></li>
                            <li><a href="<?php echo esc_url( $refund_url ); ?>"><?php esc_html_e( 'Refund Policy', 'brand-theme' ); ?></a></li>
                            <li><a href="<?php echo esc_url( $terms_url ); ?>"><?php esc_html_e( 'Terms and Conditions', 'brand-theme' ); ?></a></li>
                        </ul>
                    </details>
                </li>
            </ul>
        </nav>
    </div>
</header>
