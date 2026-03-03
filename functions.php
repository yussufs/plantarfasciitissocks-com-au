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
    // Gallery rendering handled by custom Svelte component — no default gallery supports.
}
add_action( 'after_setup_theme', 'brand_theme_woocommerce_setup' );

// Disable all default WooCommerce styles so theme styling is fully Tailwind-driven.
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

// Force classic shortcodes instead of Gutenberg blocks on cart/checkout pages.
// This keeps markup consistent with the theme's Tailwind-based WC styles.
add_filter( 'the_content', function ( $content ) {
	if ( is_cart() ) {
		return '[woocommerce_cart]';
	}
	if ( is_checkout() && ! is_order_received_page() ) {
		return '[woocommerce_checkout]';
	}
	return $content;
} );

// Replace default WC content wrappers with a properly styled container for the shop archive.
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

add_action( 'woocommerce_before_main_content', function () {
	echo '<main class="mx-auto max-w-6xl px-4 py-8">';
} );

add_action( 'woocommerce_after_main_content', function () {
	echo '</main>';
} );

// Remove default sidebar from shop/product archive pages.
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

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
        // CSS is imported by app.ts and injected by Vite client — no <link> tag needed.
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

// ──────────────────────────────────────────────
// Theme Image Helper
// ──────────────────────────────────────────────

/**
 * Get the URL for an optimized theme image from dist/images/.
 * In dev mode, serves the source file from Vite dev server.
 *
 * Usage: brand_theme_image_url( 'hero-banner.jpg' )
 *        brand_theme_image_url( 'icons/arrow.svg' )
 */
function brand_theme_image_url( $filename ) {
	if ( defined( 'VITE_DEV' ) && VITE_DEV ) {
		return 'http://localhost:5173/src/images/' . $filename;
	}

	return get_template_directory_uri() . '/dist/images/' . $filename;
}

/**
 * Output a responsive <picture> tag with WebP + srcset.
 *
 * For raster images (jpg/png), generates srcset at 400, 800, 1200, 1600w
 * with WebP variants. For SVGs, outputs a simple <img> tag.
 *
 * Usage:
 *   brand_theme_picture( 'hero-banner.jpg', 'Hero image', 'w-full h-auto', '100vw' );
 *   brand_theme_picture( 'about/team.png', 'Team photo', '', '(max-width: 768px) 100vw, 50vw' );
 *
 * @param string $filename  Relative path within src/images/ (e.g. 'hero.jpg').
 * @param string $alt       Alt text.
 * @param string $class     CSS classes for the <img> tag.
 * @param string $sizes     Sizes attribute (e.g. '100vw' or '(max-width: 768px) 100vw, 50vw').
 * @param array  $attr      Additional attributes (e.g. ['loading' => 'eager']).
 */
function brand_theme_picture( $filename, $alt = '', $class = '', $sizes = '100vw', $attr = array() ) {
	$ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );

	// SVGs and non-raster — simple <img> tag.
	if ( in_array( $ext, array( 'svg', 'gif', 'ico', 'webp', 'avif' ), true ) ) {
		$extra = brand_theme_build_attr_string( $attr );
		printf(
			'<img src="%s" alt="%s"%s%s>',
			esc_url( brand_theme_image_url( $filename ) ),
			esc_attr( $alt ),
			$class ? ' class="' . esc_attr( $class ) . '"' : '',
			$extra
		);
		return;
	}

	// Dev mode — serve original, no srcset.
	if ( defined( 'VITE_DEV' ) && VITE_DEV ) {
		$extra = brand_theme_build_attr_string( $attr );
		printf(
			'<img src="%s" alt="%s"%s%s>',
			esc_url( brand_theme_image_url( $filename ) ),
			esc_attr( $alt ),
			$class ? ' class="' . esc_attr( $class ) . '"' : '',
			$extra
		);
		return;
	}

	$widths      = array( 400, 800, 1200, 1600 );
	$name_no_ext = pathinfo( $filename, PATHINFO_FILENAME );
	$dir         = pathinfo( $filename, PATHINFO_DIRNAME );
	$prefix      = '.' === $dir ? $name_no_ext : $dir . '/' . $name_no_ext;
	$out_ext     = 'png' === $ext ? 'png' : 'jpg';
	$base_url    = get_template_directory_uri() . '/dist/images/';
	$base_path   = get_template_directory() . '/dist/images/';

	// Build srcset arrays for WebP and original format.
	$webp_srcset    = array();
	$original_srcset = array();

	foreach ( $widths as $w ) {
		$webp_file = $prefix . '-' . $w . 'w.webp';
		$orig_file = $prefix . '-' . $w . 'w.' . $out_ext;

		if ( file_exists( $base_path . $webp_file ) ) {
			$webp_srcset[] = esc_url( $base_url . $webp_file ) . ' ' . $w . 'w';
		}
		if ( file_exists( $base_path . $orig_file ) ) {
			$original_srcset[] = esc_url( $base_url . $orig_file ) . ' ' . $w . 'w';
		}
	}

	$loading = isset( $attr['loading'] ) ? $attr['loading'] : 'lazy';
	unset( $attr['loading'] );
	$extra = brand_theme_build_attr_string( $attr );

	$fallback_src = esc_url( $base_url . $filename );
	$class_attr   = $class ? ' class="' . esc_attr( $class ) . '"' : '';

	echo '<picture>';

	if ( ! empty( $webp_srcset ) ) {
		printf(
			'<source type="image/webp" srcset="%s" sizes="%s">',
			implode( ', ', $webp_srcset ),
			esc_attr( $sizes )
		);
	}

	if ( ! empty( $original_srcset ) ) {
		printf(
			'<source type="image/%s" srcset="%s" sizes="%s">',
			'png' === $out_ext ? 'png' : 'jpeg',
			implode( ', ', $original_srcset ),
			esc_attr( $sizes )
		);
	}

	printf(
		'<img src="%s" alt="%s"%s loading="%s"%s>',
		$fallback_src,
		esc_attr( $alt ),
		$class_attr,
		esc_attr( $loading ),
		$extra
	);

	echo '</picture>';
}

/**
 * Build an HTML attribute string from an associative array.
 */
function brand_theme_build_attr_string( $attr ) {
	$parts = '';
	foreach ( $attr as $key => $value ) {
		$parts .= ' ' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
	}
	return $parts;
}

// ──────────────────────────────────────────────
// Lucide Icon Helper
// ──────────────────────────────────────────────

/**
 * Output an inline Lucide SVG icon.
 *
 * Icons are read from dist/icons/ (copied from lucide-static at build time).
 * Add new icons to scripts/copy-icons.mjs → ICONS array.
 *
 * Usage:
 *   brand_theme_icon( 'truck' );
 *   brand_theme_icon( 'star', [ 'class' => 'w-5 h-5 text-yellow-400 fill-current' ] );
 *   brand_theme_icon( 'check', [ 'class' => 'w-4 h-4', 'aria-hidden' => 'true' ] );
 *
 * @param string $name  Lucide icon name (e.g. 'truck', 'chevron-down').
 * @param array  $attr  Override/add SVG attributes (class, aria-label, etc.).
 */
function brand_theme_icon( $name, $attr = array() ) {
	static $cache = array();

	if ( ! isset( $cache[ $name ] ) ) {
		$file = get_template_directory() . '/dist/icons/' . $name . '.svg';

		if ( ! file_exists( $file ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
			trigger_error( 'Lucide icon not found: ' . esc_html( $name ) . '. Add it to scripts/copy-icons.mjs.', E_USER_NOTICE );
			return;
		}

		$cache[ $name ] = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	}

	$svg = $cache[ $name ];

	// Apply custom attributes.
	foreach ( $attr as $key => $value ) {
		$pattern = '/' . preg_quote( $key, '/' ) . '="[^"]*"/';

		if ( preg_match( $pattern, $svg ) ) {
			// Replace existing attribute.
			$svg = preg_replace( $pattern, esc_attr( $key ) . '="' . esc_attr( $value ) . '"', $svg );
		} else {
			// Add new attribute to <svg> tag.
			$svg = preg_replace( '/<svg\b/', '<svg ' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"', $svg );
		}
	}

	// Default: aria-hidden if no aria-label provided.
	if ( ! isset( $attr['aria-label'] ) && strpos( $svg, 'aria-hidden' ) === false ) {
		$svg = preg_replace( '/<svg\b/', '<svg aria-hidden="true"', $svg );
	}

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG from trusted lucide-static package.
	echo $svg;
}

// ──────────────────────────────────────────────
// Product Svelte Data Helper
// ──────────────────────────────────────────────

function brand_theme_get_product_svelte_data( $product ) {
    if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
        return array();
    }

    $product_id = $product->get_id();

    // Gallery images.
    $image_ids = $product->get_gallery_image_ids();
    $thumb_id  = $product->get_image_id();
    if ( $thumb_id ) {
        array_unshift( $image_ids, $thumb_id );
    }

    $images = array();
    foreach ( $image_ids as $img_id ) {
        $full  = wp_get_attachment_image_url( $img_id, 'large' );
        $thumb = wp_get_attachment_image_url( $img_id, 'thumbnail' );
        $alt   = get_post_meta( $img_id, '_wp_attachment_image_alt', true );
        if ( $full ) {
            $images[] = array(
                'id'    => $img_id,
                'src'   => $full,
                'thumb' => $thumb ?: $full,
                'alt'   => $alt ?: $product->get_name(),
            );
        }
    }

    // Fallback if no images.
    if ( empty( $images ) ) {
        $images[] = array(
            'id'    => 0,
            'src'   => wc_placeholder_img_src( 'large' ),
            'thumb' => wc_placeholder_img_src( 'thumbnail' ),
            'alt'   => $product->get_name(),
        );
    }

    // Variations (for variable products).
    $variations = array();
    if ( $product->is_type( 'variable' ) ) {
        $available = $product->get_available_variations();
        foreach ( $available as $var ) {
            $var_image = null;
            if ( ! empty( $var['image']['url'] ) ) {
                $var_image = array(
                    'src'   => $var['image']['url'],
                    'thumb' => $var['image']['gallery_thumbnail_url'] ?? $var['image']['url'],
                    'alt'   => $var['image']['alt'] ?? $product->get_name(),
                );
            }
            $variations[] = array(
                'id'            => $var['variation_id'],
                'attributes'    => $var['attributes'],
                'price'         => strip_tags( $var['price_html'] ),
                'regular_price' => $var['display_regular_price'],
                'sale_price'    => $var['display_price'],
                'in_stock'      => $var['is_in_stock'],
                'image'         => $var_image,
            );
        }
    }

    // Color attributes for swatches.
    $color_attributes = array();
    if ( $product->is_type( 'variable' ) ) {
        $attributes = $product->get_variation_attributes();
        foreach ( $attributes as $attr_name => $options ) {
            $taxonomy = str_replace( 'attribute_', '', $attr_name );
            if ( stripos( $taxonomy, 'color' ) !== false || stripos( $taxonomy, 'colour' ) !== false ) {
                $swatches = array();
                foreach ( $options as $option ) {
                    $color_hex = get_term_meta(
                        get_term_by( 'slug', $option, $taxonomy ) ? get_term_by( 'slug', $option, $taxonomy )->term_id : 0,
                        '_brand_color_hex',
                        true
                    );
                    $swatches[] = array(
                        'slug'  => $option,
                        'label' => ucfirst( str_replace( '-', ' ', $option ) ),
                        'hex'   => $color_hex ?: null,
                    );
                }
                $color_attributes[ $attr_name ] = $swatches;
            }
        }
    }

    // Bundle tiers.
    $bundle_raw = get_post_meta( $product_id, '_brand_bundle_tiers', true );
    $bundle_tiers = array();
    if ( $bundle_raw ) {
        $bundle_tiers = json_decode( $bundle_raw, true );
    }
    if ( empty( $bundle_tiers ) ) {
        $bundle_tiers = array(
            array( 'qty' => 1, 'label' => 'Buy 1',  'discount' => 0,  'badge' => '' ),
            array( 'qty' => 2, 'label' => 'Buy 2',  'discount' => 10, 'badge' => 'Most Popular' ),
            array( 'qty' => 4, 'label' => 'Buy 4',  'discount' => 20, 'badge' => 'Best Value' ),
        );
    }

    // Prices.
    $regular_price = (float) $product->get_regular_price();
    $sale_price    = $product->get_sale_price() ? (float) $product->get_sale_price() : null;
    $active_price  = $sale_price ?: $regular_price;

    $checkout_url = function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/checkout/' );

    return array(
        'productId'       => $product_id,
        'productType'     => $product->get_type(),
        'name'            => $product->get_name(),
        'regularPrice'    => $regular_price,
        'salePrice'       => $sale_price,
        'activePrice'     => $active_price,
        'currencySymbol'  => get_woocommerce_currency_symbol(),
        'images'          => $images,
        'variations'      => $variations,
        'colorAttributes' => $color_attributes,
        'bundleTiers'     => $bundle_tiers,
        'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
        'wcAjaxUrl'       => WC_AJAX::get_endpoint( '%%endpoint%%' ),
        'cartUrl'         => wc_get_cart_url(),
        'checkoutUrl'     => $checkout_url,
        'nonce'           => wp_create_nonce( 'wc-product-' . $product_id ),
    );
}

// ──────────────────────────────────────────────
// Product Meta Boxes
// ──────────────────────────────────────────────

function brand_theme_add_product_meta_boxes() {
    add_meta_box(
        'brand_product_extras',
        __( 'Brand Theme — Product Extras', 'brand-theme' ),
        'brand_theme_product_meta_box_html',
        'product',
        'normal',
        'default'
    );
}
add_action( 'add_meta_boxes', 'brand_theme_add_product_meta_boxes' );

function brand_theme_product_meta_box_html( $post ) {
    wp_nonce_field( 'brand_product_extras_nonce', 'brand_product_extras_nonce_field' );

    $bundle_tiers    = get_post_meta( $post->ID, '_brand_bundle_tiers', true );
    $testimonial     = get_post_meta( $post->ID, '_brand_testimonial', true );
    $delivery_days   = get_post_meta( $post->ID, '_brand_delivery_days', true );
    $shipping_info   = get_post_meta( $post->ID, '_brand_shipping_info', true );
    $faqs            = get_post_meta( $post->ID, '_brand_faqs', true );

    if ( ! $testimonial ) {
        $testimonial = '';
    }
    if ( ! $delivery_days ) {
        $delivery_days = '3-7';
    }
    if ( ! $shipping_info ) {
        $shipping_info = '';
    }
    if ( ! $faqs ) {
        $faqs = '';
    }
    ?>
    <p>
        <label for="brand_delivery_days"><strong><?php esc_html_e( 'Delivery Days (e.g. 3-7)', 'brand-theme' ); ?></strong></label><br>
        <input type="text" id="brand_delivery_days" name="brand_delivery_days" value="<?php echo esc_attr( $delivery_days ); ?>" class="regular-text">
    </p>
    <p>
        <label for="brand_testimonial"><strong><?php esc_html_e( 'Testimonial (JSON: {"quote","author","verified"})', 'brand-theme' ); ?></strong></label><br>
        <textarea id="brand_testimonial" name="brand_testimonial" rows="3" class="large-text"><?php echo esc_textarea( $testimonial ); ?></textarea>
    </p>
    <p>
        <label for="brand_bundle_tiers"><strong><?php esc_html_e( 'Bundle Tiers (JSON array)', 'brand-theme' ); ?></strong></label><br>
        <textarea id="brand_bundle_tiers" name="brand_bundle_tiers" rows="4" class="large-text"><?php echo esc_textarea( $bundle_tiers ); ?></textarea>
        <br><span class="description"><?php esc_html_e( 'Leave empty for defaults (Buy 1 / Buy 2 / Buy 4).', 'brand-theme' ); ?></span>
    </p>
    <p>
        <label for="brand_shipping_info"><strong><?php esc_html_e( 'Shipping & Returns Info', 'brand-theme' ); ?></strong></label><br>
        <textarea id="brand_shipping_info" name="brand_shipping_info" rows="4" class="large-text"><?php echo esc_textarea( $shipping_info ); ?></textarea>
    </p>
    <p>
        <label for="brand_faqs"><strong><?php esc_html_e( 'FAQs (JSON array of {"q","a"} objects)', 'brand-theme' ); ?></strong></label><br>
        <textarea id="brand_faqs" name="brand_faqs" rows="4" class="large-text"><?php echo esc_textarea( $faqs ); ?></textarea>
    </p>
    <?php
}

function brand_theme_save_product_meta( $post_id ) {
    if ( ! isset( $_POST['brand_product_extras_nonce_field'] ) ) {
        return;
    }
    if ( ! wp_verify_nonce( $_POST['brand_product_extras_nonce_field'], 'brand_product_extras_nonce' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    $fields = array(
        'brand_delivery_days' => '_brand_delivery_days',
        'brand_testimonial'   => '_brand_testimonial',
        'brand_bundle_tiers'  => '_brand_bundle_tiers',
        'brand_shipping_info' => '_brand_shipping_info',
        'brand_faqs'          => '_brand_faqs',
    );

    foreach ( $fields as $input_name => $meta_key ) {
        if ( isset( $_POST[ $input_name ] ) ) {
            update_post_meta( $post_id, $meta_key, sanitize_textarea_field( wp_unslash( $_POST[ $input_name ] ) ) );
        }
    }
}
add_action( 'save_post_product', 'brand_theme_save_product_meta' );

// ──────────────────────────────────────────────
// Product Reviews (Static JSON)
// ──────────────────────────────────────────────

/**
 * Get reviews for a product by slug.
 *
 * Reads data/reviews.json once per request, filters by product slug.
 * A review with "*" in product_slugs matches all products.
 *
 * @param string $product_slug The product slug to filter by.
 * @return array Filtered reviews.
 */
function brand_theme_get_reviews( $product_slug ) {
	static $all_reviews = null;

	if ( null === $all_reviews ) {
		$file = get_template_directory() . '/data/reviews.json';
		if ( ! file_exists( $file ) ) {
			$all_reviews = array();
			return $all_reviews;
		}
		$json        = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$all_reviews = json_decode( $json, true );
		if ( ! is_array( $all_reviews ) ) {
			$all_reviews = array();
		}
	}

	return array_values( array_filter( $all_reviews, function ( $review ) use ( $product_slug ) {
		if ( ! isset( $review['product_slugs'] ) || ! is_array( $review['product_slugs'] ) ) {
			return false;
		}
		return in_array( '*', $review['product_slugs'], true )
			|| in_array( $product_slug, $review['product_slugs'], true );
	} ) );
}

/**
 * Build image data array for a review image filename.
 *
 * Returns URLs for thumbnail (400w) and full (800w) with WebP variants,
 * or dev-mode fallback URLs.
 *
 * @param string $filename Image filename (e.g. 'review-sarah.jpg').
 * @return array|null Image data array or null if no filename.
 */
function brand_theme_get_review_image_data( $filename ) {
	if ( ! $filename ) {
		return null;
	}

	$name_no_ext = pathinfo( $filename, PATHINFO_FILENAME );
	$ext         = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
	$out_ext     = 'png' === $ext ? 'png' : 'jpg';

	// Dev mode — serve original from Vite if the source file exists.
	if ( defined( 'VITE_DEV' ) && VITE_DEV ) {
		$source_path = get_template_directory() . '/src/images/reviews/' . $filename;
		if ( ! file_exists( $source_path ) ) {
			return null;
		}
		$src = 'http://localhost:5173/src/images/reviews/' . $filename;
		return array(
			'src'    => $src,
			'srcset' => '',
			'webp'   => '',
			'alt'    => '',
		);
	}

	$base_url  = get_template_directory_uri() . '/dist/images/reviews/';
	$base_path = get_template_directory() . '/dist/images/reviews/';

	$thumb_file = $name_no_ext . '-400w.' . $out_ext;
	$full_file  = $name_no_ext . '-800w.' . $out_ext;
	$thumb_webp = $name_no_ext . '-400w.webp';
	$full_webp  = $name_no_ext . '-800w.webp';

	// Build srcset for original format.
	$srcset_parts = array();
	if ( file_exists( $base_path . $thumb_file ) ) {
		$srcset_parts[] = esc_url( $base_url . $thumb_file ) . ' 400w';
	}
	if ( file_exists( $base_path . $full_file ) ) {
		$srcset_parts[] = esc_url( $base_url . $full_file ) . ' 800w';
	}

	// Build srcset for WebP.
	$webp_parts = array();
	if ( file_exists( $base_path . $thumb_webp ) ) {
		$webp_parts[] = esc_url( $base_url . $thumb_webp ) . ' 400w';
	}
	if ( file_exists( $base_path . $full_webp ) ) {
		$webp_parts[] = esc_url( $base_url . $full_webp ) . ' 800w';
	}

	// If no built files exist at all, the image hasn't been added yet — skip it.
	if ( empty( $srcset_parts ) && empty( $webp_parts ) && ! file_exists( $base_path . $filename ) ) {
		return null;
	}

	// Fallback src: prefer 800w, then 400w, then original filename.
	$src = $base_url . $filename;
	if ( file_exists( $base_path . $full_file ) ) {
		$src = $base_url . $full_file;
	} elseif ( file_exists( $base_path . $thumb_file ) ) {
		$src = $base_url . $thumb_file;
	}

	return array(
		'src'    => esc_url( $src ),
		'srcset' => implode( ', ', $srcset_parts ),
		'webp'   => implode( ', ', $webp_parts ),
		'alt'    => '',
	);
}
