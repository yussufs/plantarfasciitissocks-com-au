<?php
/**
 * Fix mojibake apostrophes in product reviews: Õ (U+00D5) -> '
 * Dry-run by default; pass `apply` as the first arg to write.
 *   Dry run: wp eval-file fix-mojibake.php
 *   Apply:   wp eval-file fix-mojibake.php apply
 */

global $wpdb;

$apply = isset( $args[0] ) && 'apply' === $args[0];
$bad   = "\u{00D5}"; // Õ
$good  = "'";

$rows = $wpdb->get_results(
	"SELECT comment_ID, comment_content
	 FROM {$wpdb->comments}
	 WHERE comment_type = 'review'"
);

$changed = 0;

foreach ( $rows as $r ) {
	if ( false === strpos( $r->comment_content, $bad ) ) {
		continue;
	}

	$new = str_replace( $bad, $good, $r->comment_content );
	$changed++;

	WP_CLI::log( "#{$r->comment_ID}" );
	WP_CLI::log( "  before: " . str_replace( "\n", ' ', $r->comment_content ) );
	WP_CLI::log( "  after:  " . str_replace( "\n", ' ', $new ) );

	if ( $apply ) {
		$wpdb->update(
			$wpdb->comments,
			array( 'comment_content' => $new ),
			array( 'comment_ID' => $r->comment_ID )
		);
		clean_comment_cache( $r->comment_ID );
	}
}

WP_CLI::log( '' );
if ( $apply ) {
	WP_CLI::success( "Updated {$changed} review(s)." );
} else {
	WP_CLI::success( "DRY RUN — would update {$changed} review(s). Re-run with 'apply' to write." );
}
