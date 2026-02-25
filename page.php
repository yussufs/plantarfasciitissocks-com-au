<?php
/**
 * Page template.
 *
 * @package BrandTheme
 */

get_header();
?>

<?php while ( have_posts() ) : the_post(); ?>
    <article id="page-<?php the_ID(); ?>" <?php post_class( 'mx-auto max-w-3xl' ); ?>>
        <h1 class="mb-8 text-3xl font-bold text-gray-900"><?php the_title(); ?></h1>

        <div class="prose prose-gray max-w-none">
            <?php the_content(); ?>
        </div>
    </article>
<?php endwhile; ?>

<?php
get_footer();
