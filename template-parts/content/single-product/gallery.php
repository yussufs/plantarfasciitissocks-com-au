<?php
/**
 * Product gallery mount point with a server-rendered first frame.
 *
 * The LCP image must exist in the initial HTML so the browser can discover
 * and paint it before the JS bundle hydrates. Markup mirrors
 * ProductGallery.svelte exactly; app.ts clears it just before mounting the
 * component.
 *
 * Expects query var:
 * - gallery_images: array of {id, src, thumb, alt} (from brand_theme_get_product_svelte_data)
 *
 * @package BrandTheme
 */

defined( 'ABSPATH' ) || exit;

$gallery_images = get_query_var( 'gallery_images' );
if ( ! is_array( $gallery_images ) ) {
	$gallery_images = array();
}
$first_image = $gallery_images[0] ?? null;
?>
<div id="product-gallery" data-config='<?php echo esc_attr( wp_json_encode( array( 'images' => $gallery_images ) ) ); ?>'>
	<?php if ( $first_image ) : ?>
	<div class="product-gallery">
		<div class="product-gallery-viewport">
			<div class="product-gallery-container">
				<div class="product-gallery-slide">
					<?php // skip-lazy: stops Smush's lazy-loader rewriting src to data-src, which would make the LCP image undiscoverable again. ?>
					<img
						class="skip-lazy"
						src="<?php echo esc_url( $first_image['src'] ); ?>"
						alt="<?php echo esc_attr( $first_image['alt'] ); ?>"
						loading="eager"
						fetchpriority="high"
					/>
				</div>
			</div>
		</div>
		<?php if ( count( $gallery_images ) > 1 ) : ?>
		<div class="product-gallery-thumbs">
			<div class="product-gallery-thumbs-scroll">
				<?php foreach ( $gallery_images as $i => $gallery_image ) : ?>
					<button
						type="button"
						class="product-gallery-thumb<?php echo 0 === $i ? ' is-active' : ''; ?>"
						aria-label="<?php echo esc_attr( sprintf( __( 'View image %d', 'brand-theme' ), $i + 1 ) ); ?>"
					>
						<img src="<?php echo esc_url( $gallery_image['thumb'] ); ?>" alt="" loading="lazy" />
					</button>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endif; ?>
	</div>
	<?php endif; ?>
</div>
