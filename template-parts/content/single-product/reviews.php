<?php
/**
 * Single Product — Customer Reviews section.
 *
 * Reads native WooCommerce reviews (comments) for the product and renders a
 * Svelte mount point. Structured data (Product/Review/AggregateRating schema) is
 * emitted by Rank Math from the native reviews — not duplicated here.
 *
 * @package BrandTheme
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
	return;
}

$reviews = brand_theme_get_reviews( $product );

if ( empty( $reviews ) ) {
	return;
}

// $reviews are already fully shaped by brand_theme_map_review_comment() (images
// resolved, votes, verified, etc.). Aggregate rating comes from WooCommerce so
// it matches the native star display and counts all approved reviews.
$config = array(
	'reviews'     => $reviews,
	'avgRating'   => (float) $product->get_average_rating(),
	'reviewCount' => (int) $product->get_review_count(),
	'productName' => $product->get_name(),
);
?>

<section class="mt-12 lg:mt-16">
	<div id="product-reviews" data-config='<?php echo esc_attr( wp_json_encode( $config ) ); ?>'></div>
</section>
<?php
// Structured data (Product / Review / AggregateRating) is emitted by Rank Math
// from the native WooCommerce reviews — intentionally not duplicated here, as a
// second Product schema block would trigger Search Console "duplicate" warnings.
