<?php
/**
 * Page template.
 *
 * @package BrandTheme
 */

get_header();
?>

<?php
while ( have_posts() ) : the_post();

    // WC pages need a wider container than regular pages.
    $is_wc_page    = function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() );
    $article_class = $is_wc_page ? 'mx-auto max-w-6xl px-4 py-8' : 'mx-auto max-w-3xl';
?>
    <article id="page-<?php the_ID(); ?>" <?php post_class( $article_class ); ?>>
        <h1 class="mb-8 text-3xl font-bold text-gray-900"><?php the_title(); ?></h1>

        <div class="<?php echo $is_wc_page ? 'max-w-none' : 'prose prose-gray max-w-none'; ?>">
            <?php the_content(); ?>
        </div>
    </article>
<?php endwhile; ?>

<?php
get_footer();
