<?php
/**
 * Brand Theme functions and definitions.
 *
 * @package BrandTheme
 */

// ──────────────────────────────────────────────
// Contact Form Handler
// ──────────────────────────────────────────────

function brand_theme_handle_contact_form() {
    // Verify nonce.
    if ( ! isset( $_POST['brand_contact_nonce'] ) || ! wp_verify_nonce( $_POST['brand_contact_nonce'], 'brand_contact_form' ) ) {
        wp_safe_redirect( wp_get_referer() ? wp_get_referer() : home_url( '/contact/' ) );
        exit;
    }

    // Honeypot check — bots fill this hidden field.
    if ( ! empty( $_POST['website'] ) ) {
        // Silently reject but redirect as if successful to not tip off bots.
        wp_safe_redirect( add_query_arg( 'contact', 'success', wp_get_referer() ? wp_get_referer() : home_url( '/contact/' ) ) );
        exit;
    }

    $name    = isset( $_POST['contact_name'] ) ? sanitize_text_field( wp_unslash( $_POST['contact_name'] ) ) : '';
    $email   = isset( $_POST['contact_email'] ) ? sanitize_email( wp_unslash( $_POST['contact_email'] ) ) : '';
    $message = isset( $_POST['contact_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['contact_message'] ) ) : '';

    // Validate required fields.
    if ( ! $name || ! is_email( $email ) || ! $message ) {
        wp_safe_redirect( add_query_arg( 'contact', 'error', wp_get_referer() ? wp_get_referer() : home_url( '/contact/' ) ) );
        exit;
    }

    $to      = get_option( 'admin_email' );
    $subject = sprintf( '[%s] Contact Form: %s', get_bloginfo( 'name' ), $name );
    $body    = sprintf(
        "Name: %s\nEmail: %s\n\nMessage:\n%s",
        $name,
        $email,
        $message
    );
    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        sprintf( 'Reply-To: %s <%s>', $name, $email ),
    );

    $sent = wp_mail( $to, $subject, $body, $headers );

    $status = $sent ? 'success' : 'error';
    wp_safe_redirect( add_query_arg( 'contact', $status, wp_get_referer() ? wp_get_referer() : home_url( '/contact/' ) ) );
    exit;
}
add_action( 'admin_post_nopriv_brand_contact_form', 'brand_theme_handle_contact_form' );
add_action( 'admin_post_brand_contact_form', 'brand_theme_handle_contact_form' );

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

// Disable default WooCommerce styles except on checkout pages.
add_filter( 'woocommerce_enqueue_styles', function ( $styles ) {
	if ( is_checkout() ) {
		return $styles;
	}
	return array();
} );

// Force classic shortcode on cart page only.
// Checkout uses WooCommerce Blocks (block editor page).
add_filter( 'the_content', function ( $content ) {
	if ( is_cart() ) {
		return '[woocommerce_cart]';
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

// Append an inline remove link inside the quantity cell (visible on mobile only).
add_filter( 'woocommerce_cart_item_quantity', function ( $product_quantity, $cart_item_key, $cart_item ) {
    $remove_url = esc_url( wc_get_cart_remove_url( $cart_item_key ) );
    $remove_link = sprintf(
        '<a href="%s" class="cart-inline-remove" aria-label="%s">&times;</a>',
        $remove_url,
        esc_attr__( 'Remove this item', 'brand-theme' )
    );
    return '<div class="cart-qty-row">' . $product_quantity . $remove_link . '</div>';
}, 10, 3 );

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
 * Render an image from the WordPress media library (uploads) as an <img>,
 * resolved to its attachment so Smush CDN, srcset and WebP all apply.
 *
 * Use this instead of a raw <img src="…/wp-content/uploads/…"> — raw tags
 * bypass WooCommerce/Smush image filters and never hit the CDN. Falls back to a
 * plain <img> if the file isn't a media-library attachment.
 *
 * @param string $path  Path within uploads, e.g. '2026/05/foo.jpg'.
 * @param string $alt   Alt text.
 * @param string $class CSS classes for the <img>.
 * @param string $size  Registered image size (default 'large').
 * @param array  $attr  Extra/override <img> attributes (e.g. loading, fetchpriority).
 * @return string HTML <img> markup.
 */
function brand_theme_uploads_image( $path, $alt = '', $class = '', $size = 'large', $attr = array() ) {
	$url = trailingslashit( wp_get_upload_dir()['baseurl'] ) . ltrim( $path, '/' );
	$id  = attachment_url_to_postid( $url );

	if ( $id ) {
		return wp_get_attachment_image( $id, $size, false, array_merge( array(
			'class'   => $class,
			'alt'     => $alt,
			'loading' => 'lazy',
		), $attr ) );
	}

	// Fallback: file isn't a media-library attachment — output a plain tag.
	$extra = '';
	foreach ( $attr as $key => $value ) {
		$extra .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $value ) );
	}
	if ( ! isset( $attr['loading'] ) ) {
		$extra .= ' loading="lazy"';
	}

	return sprintf(
		'<img class="%s" src="%s" alt="%s"%s>',
		esc_attr( $class ),
		esc_url( $url ),
		esc_attr( $alt ),
		$extra // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- attributes escaped above.
	);
}

/**
 * Convert a local media URL to its Smush Pro CDN URL.
 *
 * Smush's CDN rewrites <img> tags in the server-rendered page HTML, so it can't
 * reach images that Svelte renders client-side from a data-config JSON blob
 * (product gallery, reviews, size guide). For those we ask Smush to build the
 * CDN URL directly. No-op (returns the origin URL) when Smush/CDN is inactive.
 *
 * @param string $url
 * @return string
 */
function brand_theme_cdn_url( $url ) {
	if ( empty( $url ) || ! is_string( $url ) ) {
		return $url;
	}

	// Smush Pro 3.12+ — CDN_Helper singleton.
	if ( class_exists( '\Smush\Core\CDN\CDN_Helper' ) ) {
		$helper = \Smush\Core\CDN\CDN_Helper::get_instance();
		if ( $helper && method_exists( $helper, 'is_cdn_active' ) && $helper->is_cdn_active() ) {
			return $helper->generate_cdn_url( $url );
		}
	}

	// Older Smush Pro — CDN module on the core object.
	if ( class_exists( 'WP_Smush' ) ) {
		$smush = WP_Smush::get_instance();
		if ( $smush && method_exists( $smush, 'core' ) ) {
			$core = $smush->core();
			if ( ! empty( $core->mod->cdn ) && method_exists( $core->mod->cdn, 'generate_cdn_url' ) ) {
				return $core->mod->cdn->generate_cdn_url( $url );
			}
		}
	}

	return $url;
}

/**
 * Run every URL in a srcset string through brand_theme_cdn_url().
 *
 * @param string $srcset
 * @return string
 */
function brand_theme_cdn_srcset( $srcset ) {
	if ( empty( $srcset ) ) {
		return $srcset;
	}
	$sources = array_map( 'trim', explode( ',', $srcset ) );
	foreach ( $sources as &$source ) {
		$parts = preg_split( '/\s+/', $source, 2 );
		if ( ! empty( $parts[0] ) ) {
			$parts[0] = brand_theme_cdn_url( $parts[0] );
			$source   = implode( ' ', $parts );
		}
	}
	unset( $source );
	return implode( ', ', $sources );
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
                'src'   => brand_theme_cdn_url( $full ),
                'thumb' => brand_theme_cdn_url( $thumb ?: $full ),
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
                    'src'   => brand_theme_cdn_url( $var['image']['url'] ),
                    'thumb' => brand_theme_cdn_url( $var['image']['gallery_thumbnail_url'] ?? $var['image']['url'] ),
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

    // Variation attributes split by type: color swatches vs generic selectors.
    $color_attributes  = array();
    $select_attributes = array();
    if ( $product->is_type( 'variable' ) ) {
        $attributes = $product->get_variation_attributes();
        foreach ( $attributes as $attr_name => $options ) {
            $taxonomy  = str_replace( 'attribute_', '', $attr_name );
            $is_colour = stripos( $taxonomy, 'color' ) !== false || stripos( $taxonomy, 'colour' ) !== false;

            // Key by the sanitized attribute name so the front end can match
            // against variation attribute keys (attribute_{sanitized-name}).
            // Custom attributes like "Size" are stored on variations as
            // "attribute_size" — the raw name would never match.
            $attr_key = sanitize_title( $attr_name );

            if ( $is_colour ) {
                $swatches = array();
                foreach ( $options as $option ) {
                    $term      = get_term_by( 'slug', $option, $taxonomy );
                    $color_hex = $term ? get_term_meta( $term->term_id, '_brand_color_hex', true ) : '';
                    $swatches[] = array(
                        'slug'  => $option,
                        'label' => $term ? $term->name : ucfirst( str_replace( '-', ' ', $option ) ),
                        'hex'   => $color_hex ?: null,
                    );
                }
                $color_attributes[ $attr_key ] = $swatches;
            } else {
                $choices = array();
                foreach ( $options as $option ) {
                    $term = get_term_by( 'slug', $option, $taxonomy );
                    $choices[] = array(
                        'slug'  => $option,
                        'label' => $term ? $term->name : ucfirst( str_replace( '-', ' ', $option ) ),
                    );
                }
                $select_attributes[ $attr_key ] = $choices;
            }
        }
    }

    // Bundle tiers — per-product meta first, then the socks category default.
    // Fixed bundle prices (not %), enforced server-side as a negative cart fee
    // (socks: brand_theme_apply_bundle_discount; meta-driven products such as the
    // foot massager: brand_theme_apply_meta_bundle_discount).
    $bundle_tiers = brand_theme_get_bundle_tiers( $product_id );

    // Prices.
    $regular_price = (float) $product->get_regular_price();
    $sale_price    = $product->get_sale_price() ? (float) $product->get_sale_price() : null;
    $active_price  = $sale_price ?: $regular_price;

    $checkout_url = function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/checkout/' );

    // Size guide — only for plantar-fasciitis-socks products that have a size attribute.
    $size_guide    = null;
    $has_size_attr = false;
    foreach ( array_keys( $select_attributes ) as $attr_name ) {
        if ( stripos( $attr_name, 'size' ) !== false ) {
            $has_size_attr = true;
            break;
        }
    }
    if ( $has_size_attr && has_term( 'plantar-fasciitis-socks', 'product_cat', $product_id ) ) {
        // Resolve to the media-library attachment so Smush's CDN applies to the
        // URL we hand the (client-rendered) size-guide modal.
        $size_guide_url = trailingslashit( wp_get_upload_dir()['baseurl'] ) . '2023/05/aussie-plantar-fasciitis-white-socks-size-guide.jpg';
        $size_guide_id  = attachment_url_to_postid( $size_guide_url );
        if ( $size_guide_id ) {
            $resolved = wp_get_attachment_image_url( $size_guide_id, 'large' );
            if ( $resolved ) {
                $size_guide_url = $resolved;
            }
        }
        $size_guide_url = brand_theme_cdn_url( $size_guide_url );
        $size_guide = array(
            'image' => $size_guide_url,
            'rows'  => array(
                __( 'S/M fits Women: shoe size 6-9 and Men: shoe size 5-8', 'brand-theme' ),
                __( 'L/XL fits Women: shoe size 9-13 and Men: shoe size 8-12', 'brand-theme' ),
            ),
        );
    }

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
        'colorAttributes'  => $color_attributes,
        'selectAttributes' => $select_attributes,
        'sizeGuide'        => $size_guide,
        'bundleTiers'     => $bundle_tiers,
        'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
        'wcAjaxUrl'       => WC_AJAX::get_endpoint( '%%endpoint%%' ),
        'cartUrl'         => wc_get_cart_url(),
        'checkoutUrl'     => $checkout_url,
        'nonce'           => wp_create_nonce( 'wc-product-' . $product_id ),
    );
}

// ──────────────────────────────────────────────
// Sock Bundles
//
// WooCommerce can't apply a percentage discount to the cart without a coupon,
// so bundle pricing is enforced as a negative cart fee. The fee is computed
// entirely server-side from cart contents (never trusting client input), based
// on the TOTAL quantity of socks in the cart — so mix-and-match across colours
// and a quantity reached via several "add to cart" clicks both qualify.
// ──────────────────────────────────────────────

/**
 * Sock bundle tiers — fixed total price per quantity (not a percentage).
 *
 * @return array<int, array{qty:int, price:float, label:string, badge:string}>
 */
function brand_theme_sock_bundle_tiers() {
    return array(
        array( 'qty' => 1, 'price' => 24.95, 'label' => 'Buy 1', 'badge' => '' ),
        array( 'qty' => 2, 'price' => 45.00, 'label' => 'Buy 2', 'badge' => 'Most Popular' ),
        array( 'qty' => 4, 'price' => 80.00, 'label' => 'Buy 4', 'badge' => 'Best Value' ),
    );
}

/**
 * Per-unit sock price for a given total quantity (the highest tier reached).
 *
 * qty 1 → $24.95, qty 2–3 → $22.50, qty 4+ → $20.00.
 *
 * @param int $qty Total sock quantity in the cart.
 * @return float
 */
function brand_theme_sock_bundle_unit_price( $qty ) {
    $tiers = brand_theme_sock_bundle_tiers();
    $unit  = $tiers[0]['price'] / $tiers[0]['qty'];
    foreach ( $tiers as $tier ) {
        if ( $qty >= $tier['qty'] ) {
            $unit = $tier['price'] / $tier['qty'];
        }
    }
    return $unit;
}

/**
 * Foot massager bundle tiers — fixed total price per quantity. Coded default for
 * the Triple Therapy Foot Massager landing page; overridable per-product via the
 * `_brand_bundle_tiers` meta box.
 *
 * @return array<int, array{qty:int, price:float, label:string, badge:string}>
 */
function brand_theme_massager_bundle_tiers() {
    return array(
        array( 'qty' => 1, 'price' => 69.00, 'regular' => 99.00, 'label' => '1 Massager', 'badge' => '' ),
        array( 'qty' => 2, 'price' => 99.00, 'regular' => 180.00, 'label' => '2 Massagers', 'badge' => 'Most Popular' ),
    );
}

/**
 * Resolve bundle tiers for a product: per-product `_brand_bundle_tiers` meta
 * (JSON) first, then coded defaults by category/slug, else none. This is what
 * makes bundles generic — any product can opt in by setting the meta in wp-admin
 * (Brand Theme → Product Extras → "Bundle Tiers"), while socks and the foot
 * massager ship with coded defaults.
 *
 * @param int $product_id
 * @return array<int, array{qty:int, price:float, label:string, badge:string}>
 */
function brand_theme_get_bundle_tiers( $product_id ) {
    $meta = get_post_meta( $product_id, '_brand_bundle_tiers', true );
    if ( $meta ) {
        $decoded = json_decode( $meta, true );
        if ( is_array( $decoded ) && ! empty( $decoded ) ) {
            return array_values( $decoded );
        }
    }

    if ( has_term( 'plantar-fasciitis-socks', 'product_cat', $product_id ) ) {
        return brand_theme_sock_bundle_tiers();
    }

    if ( 'triple-therapy-foot-massager' === get_post_field( 'post_name', $product_id ) ) {
        return brand_theme_massager_bundle_tiers();
    }

    return array();
}

/**
 * Per-unit price for an arbitrary tier set at a given quantity (the highest tier
 * reached). Generic counterpart to brand_theme_sock_bundle_unit_price().
 *
 * @param array<int, array{qty:int, price:float}> $tiers
 * @param int                                     $qty
 * @return float
 */
function brand_theme_bundle_unit_price( $tiers, $qty ) {
    if ( empty( $tiers ) ) {
        return 0.0;
    }
    $unit = (float) $tiers[0]['price'] / max( 1, (int) $tiers[0]['qty'] );
    foreach ( $tiers as $tier ) {
        if ( $qty >= (int) $tier['qty'] ) {
            $unit = (float) $tier['price'] / max( 1, (int) $tier['qty'] );
        }
    }
    return $unit;
}

/**
 * Apply the sock bundle discount as a negative cart fee.
 */
function brand_theme_apply_bundle_discount( $cart ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return;
    }
    if ( ! $cart || ! is_a( $cart, 'WC_Cart' ) ) {
        return;
    }

    $sock_qty        = 0;
    $sock_total_incl = 0.0; // Customer-facing (tax-inclusive) sock total.
    $sock_total_excl = 0.0; // The same socks with tax removed.
    $tax_class       = '';
    $found           = false;

    foreach ( $cart->get_cart() as $item ) {
        $product = $item['data'] ?? null;
        if ( ! $product ) {
            continue;
        }
        $parent_id = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();
        if ( ! has_term( 'plantar-fasciitis-socks', 'product_cat', $parent_id ) ) {
            continue;
        }
        $qty              = (int) $item['quantity'];
        $sock_qty        += $qty;
        $sock_total_incl += (float) wc_get_price_including_tax( $product, array( 'qty' => $qty ) );
        $sock_total_excl += (float) wc_get_price_excluding_tax( $product, array( 'qty' => $qty ) );
        if ( ! $found ) {
            $tax_class = $product->get_tax_class();
            $found     = true;
        }
    }

    if ( $sock_qty < 2 ) {
        return; // No bundle discount for a single sock.
    }

    // Tiers are the customer-facing (tax-inclusive) prices, e.g. 4 socks for $80.
    $bundle_total_incl = brand_theme_sock_bundle_unit_price( $sock_qty ) * $sock_qty;
    $discount_incl     = round( $sock_total_incl - $bundle_total_incl, 2 );

    if ( $discount_incl <= 0 ) {
        return;
    }

    // WooCommerce treats fee amounts as tax-EXCLUSIVE. In a tax-inclusive store
    // we therefore hand it the ex-tax discount as a TAXABLE fee, so the tax it
    // adds back lands the cart on the exact bundle total (e.g. $80, not $78).
    // The ratio comes from the actual prices, so it adapts to the customer's
    // tax context and is 1:1 wherever no tax applies.
    if ( wc_prices_include_tax() && $sock_total_incl > 0 ) {
        $discount_excl = $discount_incl * ( $sock_total_excl / $sock_total_incl );
        $cart->add_fee( __( 'Bundle discount', 'brand-theme' ), -1 * $discount_excl, true, $tax_class );
    } else {
        $cart->add_fee( __( 'Bundle discount', 'brand-theme' ), -1 * $discount_incl, false );
    }
}
add_action( 'woocommerce_cart_calculate_fees', 'brand_theme_apply_bundle_discount' );

/**
 * Apply per-product bundle discounts for products that define their own tiers via
 * `_brand_bundle_tiers` meta (e.g. the Triple Therapy Foot Massager: 1 = $49,
 * 2 = $75). Aggregates quantity per parent product so Black + Gray together still
 * reach the 2-pack price. Socks are intentionally skipped — they're handled by
 * brand_theme_apply_bundle_discount above. Kept as a separate hook so the socks
 * path is untouched.
 */
function brand_theme_apply_meta_bundle_discount( $cart ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return;
    }
    if ( ! $cart || ! is_a( $cart, 'WC_Cart' ) ) {
        return;
    }

    // Aggregate by parent product: qty + customer-facing (incl-tax) and ex-tax totals.
    $groups = array();
    foreach ( $cart->get_cart() as $item ) {
        $product = $item['data'] ?? null;
        if ( ! $product ) {
            continue;
        }
        $parent_id = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();

        // Socks have their own dedicated discount pass — never double-count them.
        if ( has_term( 'plantar-fasciitis-socks', 'product_cat', $parent_id ) ) {
            continue;
        }

        $tiers = brand_theme_get_bundle_tiers( $parent_id );
        if ( count( $tiers ) < 2 ) {
            continue;
        }

        $qty = (int) $item['quantity'];
        if ( ! isset( $groups[ $parent_id ] ) ) {
            $groups[ $parent_id ] = array(
                'qty'       => 0,
                'incl'      => 0.0,
                'excl'      => 0.0,
                'tax_class' => $product->get_tax_class(),
                'tiers'     => $tiers,
            );
        }
        $groups[ $parent_id ]['qty']  += $qty;
        $groups[ $parent_id ]['incl'] += (float) wc_get_price_including_tax( $product, array( 'qty' => $qty ) );
        $groups[ $parent_id ]['excl'] += (float) wc_get_price_excluding_tax( $product, array( 'qty' => $qty ) );
    }

    foreach ( $groups as $group ) {
        if ( $group['qty'] < 2 ) {
            continue; // No bundle discount for a single unit.
        }

        $bundle_total_incl = brand_theme_bundle_unit_price( $group['tiers'], $group['qty'] ) * $group['qty'];
        $discount_incl     = round( $group['incl'] - $bundle_total_incl, 2 );

        if ( $discount_incl <= 0 ) {
            continue;
        }

        // Same tax-inclusive fee maths as the socks path: WooCommerce treats fees
        // as ex-tax, so in a tax-inclusive store we hand it the ex-tax discount as
        // a taxable fee and let WC add the tax back to land on the exact bundle total.
        if ( wc_prices_include_tax() && $group['incl'] > 0 ) {
            $discount_excl = $discount_incl * ( $group['excl'] / $group['incl'] );
            $cart->add_fee( __( 'Bundle discount', 'brand-theme' ), -1 * $discount_excl, true, $group['tax_class'] );
        } else {
            $cart->add_fee( __( 'Bundle discount', 'brand-theme' ), -1 * $discount_incl, false );
        }
    }
}
add_action( 'woocommerce_cart_calculate_fees', 'brand_theme_apply_meta_bundle_discount' );

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
 * Get approved reviews for a product, from native WooCommerce reviews (comments).
 *
 * Maps each review comment plus its theme meta (image, featured, location) into
 * the shape ProductReviews.svelte expects. Cached per product per request.
 *
 * @param int|WC_Product $product Product ID or object.
 * @return array
 */
function brand_theme_get_reviews( $product ) {
	$product_id = is_a( $product, 'WC_Product' ) ? $product->get_id() : intval( $product );
	if ( ! $product_id ) {
		return array();
	}

	static $cache = array();
	if ( isset( $cache[ $product_id ] ) ) {
		return $cache[ $product_id ];
	}

	$comments = get_comments( array(
		'post_id' => $product_id,
		'status'  => 'approve',
		'parent'  => 0,
		'order'   => 'DESC',
		'orderby' => 'comment_date_gmt',
	) );

	$reviews = array();
	foreach ( $comments as $comment ) {
		// Skip pingbacks/trackbacks; keep reviews and plain comments.
		if ( ! in_array( $comment->comment_type, array( 'review', 'comment', '' ), true ) ) {
			continue;
		}
		$reviews[] = brand_theme_map_review_comment( $comment );
	}

	$cache[ $product_id ] = $reviews;
	return $reviews;
}

/**
 * Get featured reviews across ALL products, for a global testimonials section.
 *
 * A native review belongs to one product, so this surfaces standout reviews
 * (flagged "featured" in the comment meta box) regardless of which product they
 * were left on. Each result carries its source productId / productName.
 *
 * @param int $limit Max number to return (0 = no limit).
 * @return array
 */
function brand_theme_get_featured_reviews( $limit = 0 ) {
	$args = array(
		'status'     => 'approve',
		'parent'     => 0,
		'post_type'  => 'product',
		'meta_key'   => '_brand_review_featured', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'meta_value' => '1',                       // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		'order'      => 'DESC',
		'orderby'    => 'comment_date_gmt',
	);
	if ( $limit > 0 ) {
		$args['number'] = $limit;
	}

	$reviews = array();
	foreach ( get_comments( $args ) as $comment ) {
		if ( ! in_array( $comment->comment_type, array( 'review', 'comment', '' ), true ) ) {
			continue;
		}
		$review                = brand_theme_map_review_comment( $comment );
		$review['productId']   = intval( $comment->comment_post_ID );
		$review['productName'] = get_the_title( $comment->comment_post_ID );
		$reviews[]             = $review;
	}

	return $reviews;
}

/**
 * Map a WooCommerce review comment + its theme meta into the front-end shape.
 *
 * `image` is returned as the raw meta value (attachment ID, URL, or legacy
 * filename); the template resolves it via brand_theme_get_review_image_data().
 *
 * @param WP_Comment $comment
 * @return array
 */
function brand_theme_map_review_comment( $comment ) {
	$id     = intval( $comment->comment_ID );
	$rating = intval( get_comment_meta( $id, 'rating', true ) );

	// Photos are owned by the WooCommerce Photo Reviews plugin (VillaTheme),
	// stored in `reviews-images` as a serialized array of image URLs. Resolve
	// each through brand_theme_get_review_image_data() (URL passthrough).
	$images   = array();
	$img_meta = get_comment_meta( $id, 'reviews-images', true );
	if ( ! empty( $img_meta ) ) {
		foreach ( (array) maybe_unserialize( $img_meta ) as $img ) {
			$data = brand_theme_get_review_image_data( $img );
			if ( $data ) {
				$images[] = $data;
			}
		}
	}

	return array(
		'id'        => $id,
		'author'    => $comment->comment_author ? $comment->comment_author : __( 'Anonymous', 'brand-theme' ),
		'location'  => (string) get_comment_meta( $id, '_brand_review_location', true ), // theme meta
		'rating'    => $rating > 0 ? $rating : 5,
		'text'      => $comment->comment_content,
		'images'    => $images,                  // all photos (plugin)
		'image'     => $images[0] ?? null,       // back-compat: first photo
		'verified'  => (bool) get_comment_meta( $id, 'verified', true ),
		'votesUp'   => intval( get_comment_meta( $id, 'wcpr_vote_up_count', true ) ),
		'votesDown' => intval( get_comment_meta( $id, 'wcpr_vote_down_count', true ) ),
		'featured'  => '1' === get_comment_meta( $id, '_brand_review_featured', true ), // theme meta
		'date'      => $comment->comment_date,
	);
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

	// Media-library attachment ID — the path used by native WooCommerce reviews
	// (admin attaches the image, we store its ID). Resolve to the uploaded file
	// with a responsive srcset; Smush optimises it in prod and pull-uploads.sh
	// brings it down locally. WebP is left to Smush's transparent delivery.
	// Resolve to a media-library attachment when possible — a numeric ID (native
	// reviews) or a local uploads URL (WooCommerce Photo Reviews stores full
	// URLs). Building the data via wp_get_attachment_image_* means Smush's CDN
	// and WebP rewriting apply automatically (they hook image_downsize / srcset).
	$attachment_id = 0;
	if ( is_numeric( $filename ) ) {
		$attachment_id = intval( $filename );
	} elseif ( preg_match( '#^(https?:)?//#i', $filename ) ) {
		$attachment_id = attachment_url_to_postid( $filename );
	}

	if ( $attachment_id ) {
		$full = wp_get_attachment_image_src( $attachment_id, 'large' );
		if ( $full ) {
			return array(
				'src'    => esc_url( brand_theme_cdn_url( $full[0] ) ),
				'srcset' => brand_theme_cdn_srcset( (string) wp_get_attachment_image_srcset( $attachment_id, 'large' ) ),
				'webp'   => '',
				'alt'    => trim( (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ),
			);
		}
	}

	// Truly external image (not in our media library) — use the URL verbatim.
	if ( preg_match( '#^(https?:)?//#i', $filename ) ) {
		return array(
			'src'    => esc_url( $filename ),
			'srcset' => '',
			'webp'   => '',
			'alt'    => '',
		);
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

/**
 * ── Review meta box ─────────────────────────────────────────────────────────
 * Adds the theme-level fields the WooCommerce Photo Reviews plugin doesn't
 * provide — a Featured flag and a Location — to the comment edit screen for
 * product reviews. Photos, rating and votes are owned by the plugin.
 */

add_action( 'add_meta_boxes_comment', 'brand_theme_add_review_meta_box' );
function brand_theme_add_review_meta_box( $comment ) {
	if ( 'product' !== get_post_type( $comment->comment_post_ID ) ) {
		return;
	}
	add_meta_box(
		'brand-review-meta',
		__( 'Review Details (Theme)', 'brand-theme' ),
		'brand_theme_render_review_meta_box',
		'comment',
		'normal',
		'high'
	);
}

function brand_theme_render_review_meta_box( $comment ) {
	wp_nonce_field( 'brand_review_meta', 'brand_review_meta_nonce' );

	$featured = get_comment_meta( $comment->comment_ID, '_brand_review_featured', true );
	$location = get_comment_meta( $comment->comment_ID, '_brand_review_location', true );
	?>
	<p>
		<label>
			<input type="checkbox" name="brand_review_featured" value="1" <?php checked( '1', $featured ); ?> />
			<strong><?php esc_html_e( 'Featured', 'brand-theme' ); ?></strong>
			— <?php esc_html_e( 'show in featured / testimonials sections (across products)', 'brand-theme' ); ?>
		</label>
	</p>
	<p>
		<label for="brand_review_location"><strong><?php esc_html_e( 'Location', 'brand-theme' ); ?></strong></label><br>
		<input type="text" id="brand_review_location" name="brand_review_location"
			value="<?php echo esc_attr( $location ); ?>" class="widefat" placeholder="e.g. Sydney, NSW" />
	</p>
	<p class="description">
		<?php esc_html_e( 'Review photos, rating and votes are managed by the WooCommerce Photo Reviews plugin.', 'brand-theme' ); ?>
	</p>
	<?php
}

add_action( 'edit_comment', 'brand_theme_save_review_meta' );
function brand_theme_save_review_meta( $comment_id ) {
	if ( ! isset( $_POST['brand_review_meta_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['brand_review_meta_nonce'] ) ), 'brand_review_meta' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_comment', $comment_id ) ) {
		return;
	}

	if ( ! empty( $_POST['brand_review_featured'] ) ) {
		update_comment_meta( $comment_id, '_brand_review_featured', '1' );
	} else {
		delete_comment_meta( $comment_id, '_brand_review_featured' );
	}

	$location = isset( $_POST['brand_review_location'] ) ? sanitize_text_field( wp_unslash( $_POST['brand_review_location'] ) ) : '';
	if ( '' !== $location ) {
		update_comment_meta( $comment_id, '_brand_review_location', $location );
	} else {
		delete_comment_meta( $comment_id, '_brand_review_location' );
	}
}
