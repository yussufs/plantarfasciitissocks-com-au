<?php
/**
 * Single Product — Customer Reviews section.
 *
 * Reads curated reviews from data/reviews.json, renders a Svelte mount point
 * and outputs JSON-LD structured data for search engines.
 *
 * @package BrandTheme
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
	return;
}

$product_slug = $product->get_slug();
$reviews      = brand_theme_get_reviews( $product_slug );

if ( empty( $reviews ) ) {
	return;
}

// Build image data for each review.
$reviews_with_images = array();
foreach ( $reviews as $review ) {
	$image_data = null;
	if ( ! empty( $review['image'] ) ) {
		$image_data = brand_theme_get_review_image_data( $review['image'] );
	}

	$reviews_with_images[] = array(
		'id'       => $review['id'] ?? 0,
		'author'   => $review['author'] ?? 'Anonymous',
		'location' => $review['location'] ?? '',
		'rating'   => intval( $review['rating'] ?? 5 ),
		'text'     => $review['text'] ?? '',
		'image'    => $image_data,
		'featured' => ! empty( $review['featured'] ),
		'date'     => $review['date'] ?? '',
	);
}

// Calculate aggregate rating.
$total_rating = 0;
foreach ( $reviews_with_images as $r ) {
	$total_rating += $r['rating'];
}
$avg_rating   = round( $total_rating / count( $reviews_with_images ), 1 );
$review_count = count( $reviews_with_images );

$config = array(
	'reviews'     => $reviews_with_images,
	'avgRating'   => $avg_rating,
	'reviewCount' => $review_count,
	'productName' => $product->get_name(),
);
?>

<section class="mt-12 lg:mt-16">
	<div id="product-reviews" data-config='<?php echo esc_attr( wp_json_encode( $config ) ); ?>'></div>
</section>

<?php
// ── JSON-LD structured data (server-rendered for crawlers) ──────────────

$schema_reviews = array();
foreach ( $reviews_with_images as $r ) {
	$schema_review = array(
		'@type'        => 'Review',
		'author'       => array(
			'@type' => 'Person',
			'name'  => $r['author'],
		),
		'reviewRating' => array(
			'@type'       => 'Rating',
			'ratingValue' => $r['rating'],
			'bestRating'  => 5,
		),
		'reviewBody'   => $r['text'],
	);

	if ( ! empty( $r['date'] ) ) {
		$schema_review['datePublished'] = gmdate( 'Y-m-d', strtotime( $r['date'] ) );
	}

	$schema_reviews[] = $schema_review;
}

$schema = array(
	'@context'        => 'https://schema.org',
	'@type'           => 'Product',
	'name'            => $product->get_name(),
	'review'          => $schema_reviews,
	'aggregateRating' => array(
		'@type'       => 'AggregateRating',
		'ratingValue' => $avg_rating,
		'bestRating'  => 5,
		'reviewCount' => $review_count,
	),
);
?>
<script type="application/ld+json"><?php echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES ); ?></script>
