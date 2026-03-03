<?php
/**
 * Single Product page — WooCommerce template override.
 *
 * @package BrandTheme
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
    the_post();

    global $product;

    if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
        $product = wc_get_product( get_the_ID() );
    }

    if ( ! $product ) {
        continue;
    }

    $svelte_data = brand_theme_get_product_svelte_data( $product );

    // Benefits — parse from short description (one line per bullet).
    $short_desc = $product->get_short_description();
    $benefits   = array();
    if ( $short_desc ) {
        $lines = preg_split( '/\r?\n/', wp_strip_all_tags( $short_desc ) );
        foreach ( $lines as $line ) {
            $line = trim( $line, " \t\n\r\0\x0B-•*" );
            if ( $line ) {
                $benefits[] = $line;
            }
        }
    }

    // Testimonial.
    $testimonial_raw = get_post_meta( $product->get_id(), '_brand_testimonial', true );
    $testimonial     = $testimonial_raw ? json_decode( $testimonial_raw, true ) : null;

    // Delivery days.
    $delivery_days = get_post_meta( $product->get_id(), '_brand_delivery_days', true );
    if ( ! $delivery_days ) {
        $delivery_days = '3-7';
    }

    // Shipping info.
    $shipping_info = get_post_meta( $product->get_id(), '_brand_shipping_info', true );

    // FAQs.
    $faqs_raw = get_post_meta( $product->get_id(), '_brand_faqs', true );
    $faqs     = $faqs_raw ? json_decode( $faqs_raw, true ) : null;

    // Review count (WooCommerce native).
    $review_count = $product->get_review_count();
    $avg_rating   = $product->get_average_rating();

    // Static reviews from data/reviews.json.
    $static_reviews     = brand_theme_get_reviews( $product->get_slug() );
    $static_review_count = count( $static_reviews );
    $static_avg_rating   = 0;
    if ( $static_review_count > 0 ) {
        $total = 0;
        foreach ( $static_reviews as $sr ) {
            $total += intval( $sr['rating'] ?? 5 );
        }
        $static_avg_rating = round( $total / $static_review_count, 1 );
    }
    ?>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 lg:gap-12">
        <!-- Gallery Column -->
        <div class="lg:sticky lg:top-8 lg:self-start">
            <div id="product-gallery" data-config='<?php echo esc_attr( wp_json_encode( array(
                'images' => $svelte_data['images'],
            ) ) ); ?>'></div>
        </div>

        <!-- Details Column -->
        <div class="space-y-5">
            <?php get_template_part( 'template-parts/content/single-product/product-badge' ); ?>

            <h1 class="product-title"><?php the_title(); ?></h1>

            <?php if ( $static_review_count > 0 ) : ?>
                <a href="#product-reviews" class="inline-flex items-center gap-1.5 group no-underline">
                    <span class="flex gap-0.5">
                        <?php for ( $i = 1; $i <= 5; $i++ ) :
                            if ( $i <= floor( $static_avg_rating ) ) :
                                brand_theme_icon( 'star', array( 'class' => 'w-4 h-4 text-amber-400 fill-amber-400' ) );
                            else :
                                brand_theme_icon( 'star', array( 'class' => 'w-4 h-4 text-zinc-300' ) );
                            endif;
                        endfor; ?>
                    </span>
                    <span class="text-sm font-medium text-zinc-700"><?php echo esc_html( number_format( $static_avg_rating, 1 ) ); ?></span>
                    <span class="text-sm text-zinc-500 group-hover:text-zinc-700 transition-colors">(<?php echo esc_html( $static_review_count ); ?> <?php echo esc_html( $static_review_count === 1 ? 'review' : 'reviews' ); ?>)</span>
                </a>
            <?php endif; ?>

            <!-- Server-rendered price (SEO) — hidden when Svelte mounts -->
            <div id="product-price-static" class="flex items-center gap-3">
                <?php if ( $product->is_on_sale() ) : ?>
                    <span class="product-price"><?php echo wp_kses_post( wc_price( $product->get_sale_price() ) ); ?></span>
                    <span class="product-price-compare"><?php echo wp_kses_post( wc_price( $product->get_regular_price() ) ); ?></span>
                <?php else : ?>
                    <span class="product-price"><?php echo wp_kses_post( wc_price( $product->get_regular_price() ) ); ?></span>
                <?php endif; ?>

                <?php if ( $review_count > 0 ) : ?>
                    <span class="product-rating ml-2">
                        <?php brand_theme_icon( 'star', array( 'class' => 'product-rating-star fill-current' ) ); ?>
                        <span class="text-sm font-medium text-zinc-700"><?php echo esc_html( number_format( (float) $avg_rating, 1 ) ); ?></span>
                        <span class="text-sm text-zinc-400">(<?php echo esc_html( $review_count ); ?>)</span>
                    </span>
                <?php endif; ?>
            </div>

            <?php get_template_part( 'template-parts/content/single-product/trust-badges' ); ?>

            <!-- Svelte interactive options (swatches, bundles, CTA buttons) -->
            <div id="product-options" data-config='<?php echo esc_attr( wp_json_encode( $svelte_data ) ); ?>'></div>

            <?php get_template_part( 'template-parts/content/single-product/payment-icons' ); ?>

            <?php
            set_query_var( 'delivery_days', $delivery_days );
            get_template_part( 'template-parts/content/single-product/delivery-estimate' );
            ?>

            <?php if ( ! empty( $benefits ) ) :
                set_query_var( 'product_benefits', $benefits );
                get_template_part( 'template-parts/content/single-product/product-benefits' );
            endif; ?>

            <?php if ( $testimonial ) :
                set_query_var( 'testimonial', $testimonial );
                get_template_part( 'template-parts/content/single-product/testimonial' );
            endif; ?>

            <?php
            set_query_var( 'shipping_info', $shipping_info );
            get_template_part( 'template-parts/content/single-product/accordion-shipping' );
            ?>

            <?php if ( ! empty( $faqs ) ) :
                set_query_var( 'product_faqs', $faqs );
                get_template_part( 'template-parts/content/single-product/accordion-faqs' );
            endif; ?>
        </div>
    </div>

    <?php get_template_part( 'template-parts/content/single-product/reviews' ); ?>

    <?php
endwhile;

get_footer();
