<?php
/**
 * Single Product page — WooCommerce template override.
 *
 * @package BrandTheme
 */

defined( 'ABSPATH' ) || exit;

get_header();

// Custom, long-form landing page for the Triple Therapy Foot Massager (cold/ad
// traffic). Renders full-width and bypasses the generic 2-column layout below.
$queried_product = wc_get_product( get_queried_object_id() );
if ( $queried_product && 'triple-therapy-foot-massager' === $queried_product->get_slug() ) {
    global $product;
    $product = $queried_product;
    get_template_part( 'template-parts/content/single-product/landing-triple-therapy-foot-massager' );
    get_footer();
    return;
}
?>

<main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

<?php
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

    // Review rating/count from WooCommerce (native reviews, managed by the
    // WooCommerce Photo Reviews plugin).
    $review_count = (int) $product->get_review_count();
    $avg_rating   = (float) $product->get_average_rating();
    ?>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2 lg:gap-12">
        <!-- Gallery Column -->
        <div class="min-w-0 lg:sticky lg:top-8 lg:self-start">
            <?php
            set_query_var( 'gallery_images', $svelte_data['images'] );
            get_template_part( 'template-parts/content/single-product/gallery' );
            ?>
        </div>

        <!-- Details Column -->
        <div class="min-w-0 space-y-3 lg:space-y-5">
            <?php get_template_part( 'template-parts/content/single-product/product-badge' ); ?>

            <h1 class="product-title"><?php the_title(); ?></h1>

            <?php if ( $review_count > 0 ) : ?>
                <a href="#product-reviews" class="inline-flex items-center gap-1.5 group no-underline">
                    <span class="flex gap-0.5">
                        <?php for ( $i = 1; $i <= 5; $i++ ) :
                            if ( $i <= floor( $avg_rating ) ) :
                                brand_theme_icon( 'star', array( 'class' => 'w-4 h-4 text-amber-400 fill-amber-400' ) );
                            else :
                                brand_theme_icon( 'star', array( 'class' => 'w-4 h-4 text-zinc-300' ) );
                            endif;
                        endfor; ?>
                    </span>
                    <span class="text-sm font-medium text-zinc-700"><?php echo esc_html( number_format( $avg_rating, 1 ) ); ?></span>
                    <span class="text-sm text-zinc-500 group-hover:text-zinc-700 transition-colors">(<?php echo esc_html( $review_count ); ?> <?php echo esc_html( 1 === $review_count ? 'review' : 'reviews' ); ?>)</span>
                </a>
            <?php endif; ?>

            <?php get_template_part( 'template-parts/content/single-product/trust-badges' ); ?>

            <?php
            set_query_var( 'svelte_data', $svelte_data );
            get_template_part( 'template-parts/content/single-product/price-options' );
            ?>

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

    <?php get_template_part( 'template-parts/content/single-product/description' ); ?>

    <?php get_template_part( 'template-parts/content/single-product/reviews' ); ?>

    <?php
endwhile;
?>

</main>

<?php
get_footer();
