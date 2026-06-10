<?php
/**
 * Attach review photo URLs (from the CSV "Photos" column) to existing reviews
 * on product 1160, using the Photo Reviews plugin meta key `reviews-images`
 * (serialized array of full URLs — the format brand_theme_get_reviews() reads).
 *
 * Run via: wp eval-file attach-review-photos.php <csv-path>
 * Idempotent: matches comments by author + content and overwrites the meta.
 */

$file    = isset( $args[0] ) ? $args[0] : ( $argv[1] ?? '' );
$post_id = 1160;

if ( ! $file || ! file_exists( $file ) ) {
	WP_CLI::error( "CSV not found: $file" );
}

// Index existing review comments by "author|content".
$existing = array();
foreach ( get_comments( array( 'post_id' => $post_id, 'type' => 'review', 'status' => 'all', 'number' => 0 ) ) as $c ) {
	$existing[ trim( $c->comment_author ) . '|' . trim( $c->comment_content ) ] = (int) $c->comment_ID;
}

$fh = fopen( $file, 'r' );
fgetcsv( $fh ); // header
$attached = 0;
$missing  = 0;

while ( ( $row = fgetcsv( $fh ) ) !== false ) {
	$content = trim( $row[0] ?? '' );
	$author  = trim( $row[6] ?? '' );
	$photos  = trim( $row[7] ?? '' );

	if ( '' === $photos || '' === $author || '' === $content ) {
		continue;
	}

	// Support one or several URLs separated by comma / whitespace / newline.
	$urls = array_values( array_filter( array_map( 'trim', preg_split( '/[\s,]+/', $photos ) ) ) );
	if ( empty( $urls ) ) {
		continue;
	}

	$key = $author . '|' . $content;
	if ( ! isset( $existing[ $key ] ) ) {
		WP_CLI::warning( "No matching review for: $author" );
		$missing++;
		continue;
	}

	update_comment_meta( $existing[ $key ], 'reviews-images', $urls );
	WP_CLI::log( "  attached " . count( $urls ) . " image(s) to #{$existing[ $key ]} ($author)" );
	$attached++;
}
fclose( $fh );

WC_Comments::clear_transients( $post_id );

WP_CLI::success( "Photos attached: $attached | Unmatched: $missing" );
