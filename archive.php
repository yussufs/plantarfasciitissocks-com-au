<?php
/**
 * Archive template.
 *
 * @package BrandTheme
 */

get_header();
?>

<header class="mb-8">
    <?php the_archive_title( '<h1 class="text-3xl font-bold text-gray-900">', '</h1>' ); ?>
    <?php the_archive_description( '<div class="mt-2 text-gray-600">', '</div>' ); ?>
</header>

<?php if ( have_posts() ) : ?>
    <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
        <?php while ( have_posts() ) : the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class( 'rounded-lg border border-gray-200 p-6' ); ?>>
                <?php if ( has_post_thumbnail() ) : ?>
                    <a href="<?php the_permalink(); ?>" class="mb-4 block overflow-hidden rounded-md">
                        <?php the_post_thumbnail( 'medium_large', array( 'class' => 'h-48 w-full object-cover' ) ); ?>
                    </a>
                <?php endif; ?>

                <h2 class="mb-2 text-lg font-semibold">
                    <a href="<?php the_permalink(); ?>" class="text-gray-900 hover:text-brand-600">
                        <?php the_title(); ?>
                    </a>
                </h2>

                <div class="text-sm text-gray-600">
                    <?php the_excerpt(); ?>
                </div>
            </article>
        <?php endwhile; ?>
    </div>

    <div class="mt-8">
        <?php the_posts_pagination( array(
            'prev_text' => __( '&larr; Previous', 'brand-theme' ),
            'next_text' => __( 'Next &rarr;', 'brand-theme' ),
        ) ); ?>
    </div>
<?php else : ?>
    <p class="text-gray-600"><?php esc_html_e( 'No posts found.', 'brand-theme' ); ?></p>
<?php endif; ?>

<?php
get_footer();
