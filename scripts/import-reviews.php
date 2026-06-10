<?php
/**
 * One-off review importer for product 1160 (Triple Therapy Foot Massager).
 * Run via: wp eval-file import-reviews.php <csv-path>
 * Idempotent: rows already present (matched by author + content) are repaired,
 * not duplicated. Recalculates WooCommerce rating/review meta at the end.
 */

$file    = isset( $args[0] ) ? $args[0] : ( $argv[1] ?? '' );
$post_id = 1160;

if ( ! $file || ! file_exists( $file ) ) {
	WP_CLI::error( "CSV not found: $file" );
}
if ( 'product' !== get_post_type( $post_id ) ) {
	WP_CLI::error( "Post $post_id is not a product." );
}

// Index existing reviews by "author|content" so we never double-insert.
$existing = array();
foreach ( get_comments( array( 'post_id' => $post_id, 'type' => 'review', 'status' => 'all', 'number' => 0 ) ) as $c ) {
	$existing[ trim( $c->comment_author ) . '|' . trim( $c->comment_content ) ] = (int) $c->comment_ID;
}

$fh = fopen( $file, 'r' );
fgetcsv( $fh ); // header
$inserted = 0;
$repaired = 0;

while ( ( $row = fgetcsv( $fh ) ) !== false ) {
	if ( count( $row ) < 7 ) {
		continue;
	}
	list( $content, $status, $verified, $rating, $date, $pid, $author ) = array_pad( $row, 7, '' );
	$content  = trim( $content );
	$author   = trim( $author );
	$rating   = (int) $rating;
	$verified = (int) $verified ? 1 : 0;
	$approved = (int) $status ? 1 : 0;

	if ( '' === $content || '' === $author ) {
		continue;
	}

	$key = $author . '|' . $content;

	if ( isset( $existing[ $key ] ) ) {
		// Already present (e.g. the partial first import) — just ensure meta is correct.
		update_comment_meta( $existing[ $key ], 'rating', $rating );
		update_comment_meta( $existing[ $key ], 'verified', $verified );
		$repaired++;
		continue;
	}

	$cid = wp_insert_comment( array(
		'comment_post_ID'      => $post_id,
		'comment_author'       => $author,
		'comment_author_email' => '',
		'comment_content'      => $content,
		'comment_type'         => 'review',
		'comment_approved'     => $approved,
	) );

	if ( ! $cid ) {
		WP_CLI::warning( "Failed to insert: $author" );
		continue;
	}

	update_comment_meta( $cid, 'rating', $rating );
	update_comment_meta( $cid, 'verified', $verified );
	$existing[ $key ] = $cid;
	$inserted++;
}
fclose( $fh );

// Recalculate WooCommerce rating + review counts from all approved reviews.
WC_Comments::clear_transients( $post_id );

$ratings      = array();
$review_count = 0;
foreach ( get_comments( array( 'post_id' => $post_id, 'type' => 'review', 'status' => 'approve', 'number' => 0 ) ) as $c ) {
	$review_count++;
	$r = (int) get_comment_meta( $c->comment_ID, 'rating', true );
	if ( $r >= 1 && $r <= 5 ) {
		$ratings[ $r ] = ( $ratings[ $r ] ?? 0 ) + 1;
	}
}

$total_rated = array_sum( $ratings );
$sum         = 0;
foreach ( $ratings as $star => $n ) {
	$sum += $star * $n;
}
$avg = $total_rated ? round( $sum / $total_rated, 2 ) : 0;

$product = wc_get_product( $post_id );
$product->set_rating_counts( $ratings );
$product->set_average_rating( $avg );
$product->set_review_count( $review_count );
$product->save();
wp_update_comment_count( $post_id );

WP_CLI::success( "Inserted: $inserted | Repaired existing: $repaired | Approved reviews now: $review_count | Avg rating: $avg" );
