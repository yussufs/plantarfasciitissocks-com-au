<?php
/**
 * Brand Theme functions and definitions.
 *
 * @package BrandTheme
 */

// ──────────────────────────────────────────────
// Theme Setup
// ──────────────────────────────────────────────

function brand_theme_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ) );

    register_nav_menus( array(
        'primary' => __( 'Primary Menu', 'brand-theme' ),
        'footer'  => __( 'Footer Menu', 'brand-theme' ),
    ) );
}
add_action( 'after_setup_theme', 'brand_theme_setup' );

// ──────────────────────────────────────────────
// WooCommerce Support
// ──────────────────────────────────────────────

function brand_theme_woocommerce_setup() {
    add_theme_support( 'woocommerce' );
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'brand_theme_woocommerce_setup' );

// Disable all default WooCommerce styles so theme styling is fully Tailwind-driven.
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

/**
 * Apply single-product page hook customizations for the custom PDP layout.
 */
function brand_theme_customize_single_product_page() {
    if ( ! function_exists( 'is_product' ) || ! is_product() ) {
        return;
    }

    remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
    remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );

    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );

    remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );

    add_action( 'woocommerce_single_product_summary', 'brand_theme_product_stock_badge', 4 );
    add_action( 'woocommerce_single_product_summary', 'brand_theme_product_sale_chip', 11 );
    add_action( 'woocommerce_single_product_summary', 'brand_theme_product_benefits', 21 );
    add_action( 'woocommerce_single_product_summary', 'brand_theme_product_color_swatches', 22 );
    add_action( 'woocommerce_single_product_summary', 'brand_theme_product_bundle_options', 29 );
    add_action( 'woocommerce_after_add_to_cart_button', 'brand_theme_product_buy_now_button' );
    add_action( 'woocommerce_single_product_summary', 'brand_theme_product_payment_badges', 31 );
    add_action( 'woocommerce_single_product_summary', 'brand_theme_product_delivery_window', 32 );
    add_action( 'woocommerce_single_product_summary', 'brand_theme_product_testimonial', 33 );
    add_action( 'woocommerce_single_product_summary', 'brand_theme_product_accordion_rows', 34 );
}
add_action( 'wp', 'brand_theme_customize_single_product_page' );

/**
 * Convert attribute slugs into visual swatch colors.
 */
function brand_theme_color_from_slug( $slug ) {
    $color_map = array(
        'black'  => '#111111',
        'white'  => '#f8f8f8',
        'red'    => '#ef2b2d',
        'blue'   => '#2454ff',
        'green'  => '#1ea672',
        'silver' => '#b5bcc7',
        'grey'   => '#5f6368',
        'gray'   => '#5f6368',
        'gold'   => '#e1b33d',
    );

    $normalized_slug = sanitize_title( (string) $slug );
    if ( isset( $color_map[ $normalized_slug ] ) ) {
        return $color_map[ $normalized_slug ];
    }

    $parts = preg_split( '/[-_]/', $normalized_slug );
    if ( empty( $parts ) || ! is_array( $parts ) ) {
        return '#222222';
    }

    $first = $color_map[ $parts[0] ] ?? '#111111';
    $second = $color_map[ $parts[1] ?? '' ] ?? '#444444';

    return "linear-gradient(135deg, {$first} 0 49%, {$second} 51% 100%)";
}

/**
 * Render the stock urgency badge above product title.
 */
function brand_theme_product_stock_badge() {
    global $product;

    if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
        return;
    }

    $stock_quantity = $product->get_stock_quantity();
    $is_low_stock   = null !== $stock_quantity && $stock_quantity <= 10;

    $data = array(
        'badgeText' => $is_low_stock
            ? __( 'HOT PRODUCT | LOW STOCK', 'brand-theme' )
            : __( 'HOT PRODUCT', 'brand-theme' ),
    );

    echo '<div id="brand-stock-badge" data-config=\'' . esc_attr( wp_json_encode( $data ) ) . '\'></div>';
}

/**
 * Render sale percentage chip next to the standard Woo price block.
 */
function brand_theme_product_sale_chip() {
    global $product;

    if ( ! $product || ! is_a( $product, 'WC_Product' ) || ! $product->is_on_sale() ) {
        return;
    }

    $regular_price = (float) $product->get_regular_price();
    $sale_price    = (float) $product->get_sale_price();

    if ( $regular_price <= 0 || $sale_price <= 0 || $regular_price <= $sale_price ) {
        return;
    }

    $percentage = (int) round( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 );
    if ( $percentage <= 0 ) {
        return;
    }

    $data = array( 'percentage' => $percentage );

    echo '<div id="brand-sale-chip" data-config=\'' . esc_attr( wp_json_encode( $data ) ) . '\'></div>';
}

/**
 * Render short, benefit-style bullets below the price.
 */
function brand_theme_product_benefits() {
    global $product;

    if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
        return;
    }

    $short_description = wp_strip_all_tags( (string) $product->get_short_description() );
    $lines             = preg_split( '/\r\n|\r|\n/', $short_description );
    $benefits          = array();

    if ( is_array( $lines ) ) {
        foreach ( $lines as $line ) {
            $line = trim( (string) $line );
            if ( '' === $line ) {
                continue;
            }
            $benefits[] = $line;
            if ( count( $benefits ) >= 3 ) {
                break;
            }
        }
    }

    if ( empty( $benefits ) ) {
        $benefits = array(
            __( 'Relieve painful knots and aches', 'brand-theme' ),
            __( 'Increase blood flow and joint mobility', 'brand-theme' ),
            __( 'Improve muscle recovery', 'brand-theme' ),
        );
    }

    $data = array(
        'benefits' => $benefits,
        'icons'    => array( '🧖', '🩸', '💪' ),
    );

    echo '<div id="brand-benefits" data-config=\'' . esc_attr( wp_json_encode( $data ) ) . '\'></div>';
}

/**
 * Render color swatches when the product has a pa_color attribute.
 */
function brand_theme_product_color_swatches() {
    global $product;

    if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
        return;
    }

    $terms = wc_get_product_terms( $product->get_id(), 'pa_color', array( 'fields' => 'all' ) );
    if ( empty( $terms ) || is_wp_error( $terms ) ) {
        return;
    }

    $colors = array();
    foreach ( $terms as $term ) {
        $colors[] = array(
            'name'       => $term->name,
            'background' => brand_theme_color_from_slug( $term->slug ),
        );
    }

    $data = array(
        'colors' => $colors,
        'label'  => __( 'Color', 'brand-theme' ),
    );

    echo '<div id="brand-color-swatches" data-config=\'' . esc_attr( wp_json_encode( $data ) ) . '\'></div>';
}

/**
 * Render bundle quantity cards that sync quantity input.
 */
function brand_theme_product_bundle_options() {
    global $product;

    if (
        ! $product ||
        ! is_a( $product, 'WC_Product' ) ||
        ! $product->is_purchasable() ||
        ! $product->is_type( 'simple' )
    ) {
        return;
    }

    $price = (float) $product->get_price();
    if ( $price <= 0 ) {
        return;
    }

    $regular_price = (float) $product->get_regular_price();
    $quantities    = array( 1, 2, 4 );
    $options       = array();

    foreach ( $quantities as $quantity ) {
        $total_price   = $price * $quantity;
        $total_regular = $regular_price > $price ? $regular_price * $quantity : 0;

        $options[] = array(
            'quantity'     => $quantity,
            'isPopular'    => 2 === $quantity,
            'pricePerUnit' => wc_price( $price ),
            'totalPrice'   => wc_price( $total_price ),
            'totalRegular' => $total_regular > 0 ? wc_price( $total_regular ) : '',
            'buyLabel'     => sprintf( __( 'Buy %d', 'brand-theme' ), $quantity ),
            'perUnitLabel' => __( 'per unit', 'brand-theme' ),
        );
    }

    $data = array(
        'options'          => $options,
        'title'            => __( 'BUNDLE & SAVE', 'brand-theme' ),
        'mostPopularLabel' => __( 'Most Popular', 'brand-theme' ),
    );

    echo '<div id="brand-bundle-options" data-config=\'' . esc_attr( wp_json_encode( $data ) ) . '\'></div>';
}

/**
 * Render Buy It Now button inside add-to-cart form.
 */
function brand_theme_product_buy_now_button() {
    global $product;

    if ( ! $product || ! is_a( $product, 'WC_Product' ) || ! $product->is_purchasable() ) {
        return;
    }

    $data = array( 'label' => __( 'BUY IT NOW', 'brand-theme' ) );

    echo '<div id="brand-buy-now" data-config=\'' . esc_attr( wp_json_encode( $data ) ) . '\'></div>';
}

/**
 * Redirect "Buy It Now" submissions directly to checkout.
 */
function brand_theme_buy_now_redirect( $url ) {
    if ( empty( $_REQUEST['brand_buy_now'] ) ) {
        return $url;
    }

    $buy_now = sanitize_text_field( wp_unslash( $_REQUEST['brand_buy_now'] ) );
    if ( '1' !== $buy_now ) {
        return $url;
    }

    return wc_get_checkout_url();
}
add_filter( 'woocommerce_add_to_cart_redirect', 'brand_theme_buy_now_redirect' );

/**
 * Render payment method tags row.
 */
function brand_theme_product_payment_badges() {
    $data = array(
        'methods'   => array( 'AMEX', 'Apple Pay', 'Google Pay', 'Klarna', 'Mastercard', 'PayPal', 'Shop', 'Visa' ),
        'ariaLabel' => __( 'Accepted payment methods', 'brand-theme' ),
    );

    echo '<div id="brand-payment-badges" data-config=\'' . esc_attr( wp_json_encode( $data ) ) . '\'></div>';
}

/**
 * Render shipping ETA line.
 */
function brand_theme_product_delivery_window() {
    $start = wp_date( 'l, F j', strtotime( '+4 days' ) );
    $end   = wp_date( 'l, F j', strtotime( '+9 days' ) );

    $data = array(
        'text' => sprintf(
            /* translators: 1: start date 2: end date */
            __( 'Get it between %1$s and %2$s.', 'brand-theme' ),
            $start,
            $end
        ),
    );

    echo '<div id="brand-delivery-window" data-config=\'' . esc_attr( wp_json_encode( $data ) ) . '\'></div>';
}

/**
 * Render testimonial snippet below CTA area.
 */
function brand_theme_product_testimonial() {
    $data = array(
        'quote'  => __( 'My knots are completely gone! I sit all day working and my back was absolutely killing me. This thing was exactly what I needed, everything is loose now and I can finally focus on what\'s in front of me.', 'brand-theme' ),
        'author' => __( 'Alex', 'brand-theme' ),
        'stars'  => '★★★★★',
    );

    echo '<div id="brand-testimonial" data-config=\'' . esc_attr( wp_json_encode( $data ) ) . '\'></div>';
}

/**
 * Render FAQ / shipping accordions.
 */
function brand_theme_product_accordion_rows() {
    $data = array(
        'rows' => array(
            array(
                'title'   => __( 'Shipping & Returns', 'brand-theme' ),
                'content' => __( 'Orders dispatch from our warehouse within 1-2 business days. Returns are accepted within 30 days for unopened items.', 'brand-theme' ),
            ),
            array(
                'title'   => __( 'Common FAQs', 'brand-theme' ),
                'content' => __( 'Use the massager for 10-15 minutes per area. For sensitive skin, start with low intensity and increase gradually.', 'brand-theme' ),
            ),
        ),
    );

    echo '<div id="brand-accordions" data-config=\'' . esc_attr( wp_json_encode( $data ) ) . '\'></div>';
}

// ──────────────────────────────────────────────
// Widget Areas
// ──────────────────────────────────────────────

function brand_theme_widgets_init() {
    register_sidebar( array(
        'name'          => __( 'Sidebar', 'brand-theme' ),
        'id'            => 'sidebar-1',
        'description'   => __( 'Main sidebar area.', 'brand-theme' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );

    register_sidebar( array(
        'name'          => __( 'Footer', 'brand-theme' ),
        'id'            => 'footer-1',
        'description'   => __( 'Footer widget area.', 'brand-theme' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );
}
add_action( 'widgets_init', 'brand_theme_widgets_init' );

// ──────────────────────────────────────────────
// Vite Asset Loader
// ──────────────────────────────────────────────

function brand_theme_vite_assets() {
    add_filter( 'script_loader_tag', function ( $tag, $handle ) {
        $module_handles = array(
            'brand-theme-app',
            'brand-theme-vite-client',
            'brand-theme-vite-app',
        );

        if ( in_array( $handle, $module_handles, true ) ) {
            $tag = str_replace( '<script ', '<script type="module" ', $tag );
        }

        return $tag;
    }, 10, 2 );

    // Development mode — load from Vite dev server.
    if ( defined( 'VITE_DEV' ) && VITE_DEV ) {
        // Load stylesheet early in <head> to reduce first-paint flicker in dev.
        // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
        wp_enqueue_style(
            'brand-theme-vite-style',
            'http://localhost:5173/src/css/app.css',
            array(),
            null
        );
        // Vite client for HMR.
        // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
        wp_enqueue_script(
            'brand-theme-vite-client',
            'http://localhost:5173/@vite/client',
            array(),
            null,
            false
        );
        // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
        wp_enqueue_script(
            'brand-theme-vite-app',
            'http://localhost:5173/src/js/app.ts',
            array( 'brand-theme-vite-client' ),
            null,
            false
        );
        return;
    }

    // Production mode — load from built manifest.
    $manifest_path = get_template_directory() . '/dist/.vite/manifest.json';

    if ( ! file_exists( $manifest_path ) ) {
        return;
    }

    $manifest_content = file_get_contents( $manifest_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
    $manifest         = json_decode( $manifest_content, true );

    if ( ! $manifest ) {
        return;
    }

    $entry = $manifest['src/js/app.ts'] ?? ( $manifest['src/js/app.js'] ?? null );
    if ( ! $entry ) {
        return;
    }

    $dist_uri = get_template_directory_uri() . '/dist/';

    // Enqueue the main JS bundle.
    wp_enqueue_script(
        'brand-theme-app',
        $dist_uri . $entry['file'],
        array(),
        null, // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
        true
    );

    // Enqueue CSS files generated by Vite.
    if ( ! empty( $entry['css'] ) ) {
        foreach ( $entry['css'] as $index => $css_file ) {
            wp_enqueue_style(
                'brand-theme-app-' . $index,
                $dist_uri . $css_file,
                array(),
                null // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
            );
        }
    }
}
add_action( 'wp_enqueue_scripts', 'brand_theme_vite_assets' );
