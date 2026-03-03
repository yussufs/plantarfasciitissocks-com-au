<?php
/**
 * Page template.
 *
 * @package BrandTheme
 */

get_header();

while ( have_posts() ) : the_post();

    // WC pages need a wider container than regular pages.
    $is_wc_page    = function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() );
    $article_class = $is_wc_page ? 'mx-auto max-w-6xl px-4 py-8' : 'mx-auto max-w-3xl';

    if ( ! $is_wc_page ) {
        get_template_part( 'template-parts/content/page-header' );
    }
?>
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <article id="page-<?php the_ID(); ?>" <?php post_class( $article_class ); ?>>
            <?php if ( $is_wc_page ) : ?>
                <h1 class="mb-8 text-3xl font-bold text-gray-900"><?php the_title(); ?></h1>
            <?php endif; ?>

            <div class="<?php echo $is_wc_page ? 'max-w-none' : 'prose prose-gray max-w-none'; ?>">
                <?php the_content(); ?>
            </div>
        </article>
    </main>
<?php endwhile; ?>

<?php
get_footer();
