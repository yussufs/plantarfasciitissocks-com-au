<?php
/**
 * READ-ONLY survey of mojibake characters in product REVIEW content.
 * Uses exact PHP byte scanning (NOT SQL LIKE — the DB collation is
 * accent-insensitive, so LIKE '%O%' would match plain letters).
 * Run: wp eval-file survey-mojibake.php
 */

global $wpdb;

// Suspect char (from code point) => what it should be.
$map = array(
	"\u{00D4}" => "'",   // O-circumflex : left single quote  -> apostrophe
	"\u{00D5}" => "'",   // O-tilde      : right single quote -> apostrophe
	"\u{00D2}" => '"',   // O-grave      : left double quote
	"\u{00D3}" => '"',   // O-acute      : right double quote
	"\u{00D0}" => '-',   // Eth          : en dash
	"\u{00D1}" => '-',   // N-tilde      : em dash
	"\u{00C9}" => '...', // E-acute      : ellipsis
	"\u{00CA}" => ' ',   // E-circumflex : non-breaking space
);

// Pull only product reviews (not order notes / system comments).
$rows = $wpdb->get_results(
	"SELECT comment_ID, comment_post_ID, comment_content
	 FROM {$wpdb->comments}
	 WHERE comment_type = 'review'"
);

WP_CLI::log( 'Scanned ' . count( $rows ) . " review comments.\n" );

$per_char  = array_fill_keys( array_keys( $map ), 0 );
$affected  = array();
$by_post   = array();

foreach ( $rows as $r ) {
	$hits = array();
	foreach ( $map as $bad => $good ) {
		$n = substr_count( $r->comment_content, $bad );
		if ( $n > 0 ) {
			$per_char[ $bad ] += $n;
			$hits[] = $bad;
		}
	}
	if ( $hits ) {
		$affected[ $r->comment_ID ] = $r;
		$by_post[ $r->comment_post_ID ] = ( $by_post[ $r->comment_post_ID ] ?? 0 ) + 1;
	}
}

WP_CLI::log( "--- Occurrences per character ---" );
foreach ( $per_char as $bad => $count ) {
	WP_CLI::log( sprintf( "  '%s' (U+%04X) -> '%s' : %d occurrence(s)", $bad, mb_ord( $bad ), $map[ $bad ], $count ) );
}

WP_CLI::log( "\n--- Affected reviews per product post ---" );
foreach ( $by_post as $pid => $count ) {
	$title = get_the_title( $pid );
	WP_CLI::log( "  post {$pid} ({$title}): {$count} review(s)" );
}

WP_CLI::log( "\n--- Sample (up to 15) ---" );
$shown = 0;
foreach ( $affected as $id => $r ) {
	if ( $shown++ >= 15 ) {
		break;
	}
	WP_CLI::log( "  #{$id} (post {$r->comment_post_ID}): " . str_replace( "\n", ' ', mb_substr( $r->comment_content, 0, 140 ) ) );
}

WP_CLI::log( "\nTOTAL distinct reviews affected: " . count( $affected ) );
