<?php
/**
 * Single post template.
 *
 * @package BrandTheme
 */

get_header();
?>

<main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
<?php while ( have_posts() ) : the_post(); ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class( 'mx-auto max-w-3xl' ); ?>>
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900"><?php the_title(); ?></h1>
            <time class="mt-2 block text-sm text-gray-500" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                <?php echo esc_html( get_the_date() ); ?>
            </time>
        </header>

        <?php if ( has_post_thumbnail() ) : ?>
            <div class="mb-8 overflow-hidden rounded-lg">
                <?php the_post_thumbnail( 'large', array( 'class' => 'w-full' ) ); ?>
            </div>
        <?php endif; ?>

        <div class="prose prose-gray max-w-none">
            <?php the_content(); ?>
        </div>

        <footer class="mt-8 border-t border-gray-200 pt-4 text-sm text-gray-500">
            <?php
            the_tags(
                '<div class="flex flex-wrap gap-2">' . esc_html__( 'Tags: ', 'brand-theme' ),
                ', ',
                '</div>'
            );
            ?>
        </footer>
    </article>

    <?php
    the_post_navigation( array(
        'prev_text' => '<span class="text-sm text-gray-500">' . esc_html__( 'Previous', 'brand-theme' ) . '</span><br>%title',
        'next_text' => '<span class="text-sm text-gray-500">' . esc_html__( 'Next', 'brand-theme' ) . '</span><br>%title',
        'class'     => 'mt-8 flex justify-between',
    ) );
    ?>
<?php endwhile; ?>
</main>

<?php
get_footer();
