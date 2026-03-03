<?php
/**
 * The main template file.
 *
 * @package BrandTheme
 */

get_header();
?>

<main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
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
            'class'     => 'flex items-center gap-2',
            'prev_text' => __( '&larr; Previous', 'brand-theme' ),
            'next_text' => __( 'Next &rarr;', 'brand-theme' ),
        ) ); ?>
    </div>
<?php else : ?>
    <p class="text-gray-600"><?php esc_html_e( 'No posts found.', 'brand-theme' ); ?></p>
<?php endif; ?>
</main>

<?php
get_footer();
