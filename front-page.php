<?php
/**
 * Front page template.
 *
 * @package BrandTheme
 */

get_header();
?>

<section class="py-12 text-center">
    <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">
        <?php bloginfo( 'name' ); ?>
    </h1>
    <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-600">
        <?php bloginfo( 'description' ); ?>
    </p>
</section>

<?php if ( function_exists( 'wc_get_products' ) ) : ?>
    <?php
    $featured_products = wc_get_products( array(
        'featured' => true,
        'limit'    => 4,
        'status'   => 'publish',
    ) );
    ?>

    <?php if ( $featured_products ) : ?>
        <section class="py-12">
            <h2 class="mb-8 text-2xl font-bold text-gray-900">
                <?php esc_html_e( 'Featured Products', 'brand-theme' ); ?>
            </h2>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <?php foreach ( $featured_products as $product ) : ?>
                    <?php
                    $GLOBALS['product'] = $product;
                    get_template_part( 'template-parts/content/product-card' );
                    ?>
                <?php endforeach; ?>
                <?php wp_reset_postdata(); ?>
            </div>
        </section>
    <?php endif; ?>
<?php endif; ?>

<?php
// Example Svelte component mount point — demonstrates the pattern.
$example_data = array(
    'title'       => get_bloginfo( 'name' ),
    'initialCount' => 0,
);
?>
<section class="py-12">
    <h2 class="mb-4 text-2xl font-bold text-gray-900">
        <?php esc_html_e( 'Interactive Component Demo', 'brand-theme' ); ?>
    </h2>
    <div
        id="example-component"
        data-config='<?php echo esc_attr( wp_json_encode( $example_data ) ); ?>'
    ></div>
</section>

<?php
get_footer();
