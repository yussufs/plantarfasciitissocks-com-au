<?php
/**
 * seed-products.php — WooCommerce dummy product seeder.
 *
 * Run via:
 * SEED_PRODUCT_COUNT=12 SEED_PRODUCT_RESET=0 wp eval-file scripts/seed-products.php --path=/path/to/site
 */

if ( ! function_exists( 'wc_get_product' ) || ! class_exists( 'WC_Product_Simple' ) ) {
    if ( class_exists( 'WP_CLI' ) ) {
        WP_CLI::error( 'WooCommerce is required to seed products.' );
    }
    fwrite( STDERR, "WooCommerce is required to seed products.\n" );
    exit( 1 );
}

/**
 * Output helper that works both inside and outside WP-CLI.
 *
 * @param string $message Message text.
 * @return void
 */
function brand_seed_log( $message ) {
    if ( class_exists( 'WP_CLI' ) ) {
        WP_CLI::log( $message );
        return;
    }

    echo $message . PHP_EOL;
}

$count = (int) ( getenv( 'SEED_PRODUCT_COUNT' ) ?: 12 );
if ( $count < 1 ) {
    $count = 12;
}

$reset = strtolower( (string) getenv( 'SEED_PRODUCT_RESET' ) );
$reset = in_array( $reset, array( '1', 'true', 'yes' ), true );

$categories = array(
    array(
        'name'        => 'Arthritis Gloves',
        'slug'        => 'arthritis-gloves',
        'description' => 'Soft, breathable gloves for day-to-day joint support.',
    ),
    array(
        'name'        => 'Compression Gloves',
        'slug'        => 'compression-gloves',
        'description' => 'Firm compression styles designed for active support.',
    ),
    array(
        'name'        => 'Recovery Wraps',
        'slug'        => 'recovery-wraps',
        'description' => 'Wrist and hand wraps focused on post-activity recovery.',
    ),
    array(
        'name'        => 'Daily Comfort',
        'slug'        => 'daily-comfort',
        'description' => 'Lightweight comfort products for everyday wear.',
    ),
);

$category_ids = array();
foreach ( $categories as $category ) {
    $existing = term_exists( $category['slug'], 'product_cat' );

    if ( $existing && ! is_wp_error( $existing ) ) {
        $category_ids[ $category['slug'] ] = (int) $existing['term_id'];
        continue;
    }

    $created = wp_insert_term(
        $category['name'],
        'product_cat',
        array(
            'slug'        => $category['slug'],
            'description' => $category['description'],
        )
    );

    if ( is_wp_error( $created ) ) {
        if ( class_exists( 'WP_CLI' ) ) {
            WP_CLI::error( 'Could not create category "' . $category['name'] . '": ' . $created->get_error_message() );
        }
        fwrite( STDERR, 'Could not create category "' . $category['name'] . "\".\n" );
        exit( 1 );
    }

    $category_ids[ $category['slug'] ] = (int) $created['term_id'];
}

if ( $reset ) {
    $seeded_ids = get_posts(
        array(
            'post_type'      => 'product',
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_key'       => '_brand_theme_seeded',
            'meta_value'     => '1',
        )
    );

    foreach ( $seeded_ids as $seeded_id ) {
        wp_delete_post( (int) $seeded_id, true );
    }

    brand_seed_log( 'Removed ' . count( $seeded_ids ) . ' previously seeded products.' );
}

$templates = array(
    array(
        'name'              => 'Flex Relief Compression Gloves',
        'sku'               => 'FLEX-RELIEF',
        'category'          => 'compression-gloves',
        'regular'           => 39.95,
        'sale'              => 29.95,
        'stock'             => 28,
        'short_description' => 'Targeted compression for all-day hand support.',
        'description'       => 'A breathable knit glove with firm compression zones to support sore joints during daily activity.',
    ),
    array(
        'name'              => 'Copper Ease Fingerless Gloves',
        'sku'               => 'COPPER-EASE',
        'category'          => 'compression-gloves',
        'regular'           => 34.95,
        'sale'              => 0,
        'stock'             => 34,
        'short_description' => 'Fingerless fit with copper-infused comfort fabric.',
        'description'       => 'Fingerless design keeps dexterity high while maintaining gentle compression across the palm and wrist.',
    ),
    array(
        'name'              => 'Night Calm Arthritis Gloves',
        'sku'               => 'NIGHT-CALM',
        'category'          => 'arthritis-gloves',
        'regular'           => 29.95,
        'sale'              => 24.95,
        'stock'             => 22,
        'short_description' => 'Soft overnight support for stiff hands.',
        'description'       => 'Made for overnight wear with smooth seams and stretch fabric to reduce irritation while resting.',
    ),
    array(
        'name'              => 'Daily Motion Support Gloves',
        'sku'               => 'DAILY-MOTION',
        'category'          => 'arthritis-gloves',
        'regular'           => 31.95,
        'sale'              => 0,
        'stock'             => 40,
        'short_description' => 'Balanced support for daily errands and desk work.',
        'description'       => 'A lightweight pair with moderate compression and moisture-wicking material for extended daytime use.',
    ),
    array(
        'name'              => 'Wrist Guard Recovery Wrap',
        'sku'               => 'WRIST-GUARD',
        'category'          => 'recovery-wraps',
        'regular'           => 27.95,
        'sale'              => 0,
        'stock'             => 30,
        'short_description' => 'Adjustable wrap for wrist-focused support.',
        'description'       => 'Hook-and-loop closure gives adjustable compression and helps stabilise the wrist after repetitive strain.',
    ),
    array(
        'name'              => 'Thermal Joint Comfort Wrap',
        'sku'               => 'THERMAL-COMFORT',
        'category'          => 'recovery-wraps',
        'regular'           => 33.95,
        'sale'              => 26.95,
        'stock'             => 18,
        'short_description' => 'Heat-retaining wrap for post-activity comfort.',
        'description'       => 'Thermal blend helps retain warmth around the wrist and lower palm to ease tightness after activity.',
    ),
    array(
        'name'              => 'Soft Grip Gardening Gloves',
        'sku'               => 'SOFT-GRIP',
        'category'          => 'daily-comfort',
        'regular'           => 25.95,
        'sale'              => 0,
        'stock'             => 26,
        'short_description' => 'Comfort-focused gloves for light daily tasks.',
        'description'       => 'Soft-touch palm fabric and flexible cuff provide support for low-impact tasks like gardening and housework.',
    ),
    array(
        'name'              => 'Office Comfort Typing Gloves',
        'sku'               => 'OFFICE-COMFORT',
        'category'          => 'daily-comfort',
        'regular'           => 28.95,
        'sale'              => 0,
        'stock'             => 36,
        'short_description' => 'Low-profile gloves designed for desk use.',
        'description'       => 'A slim fingerless profile made for keyboard and mouse usage without sacrificing joint support.',
    ),
    array(
        'name'              => 'Active Support Training Gloves',
        'sku'               => 'ACTIVE-SUPPORT',
        'category'          => 'compression-gloves',
        'regular'           => 37.95,
        'sale'              => 0,
        'stock'             => 24,
        'short_description' => 'Stable support for training and movement sessions.',
        'description'       => 'Compression mapped for dynamic movement, with reinforced stitching around the palm and thumb.',
    ),
    array(
        'name'              => 'Recovery Pro Hand Wrap',
        'sku'               => 'RECOVERY-PRO',
        'category'          => 'recovery-wraps',
        'regular'           => 35.95,
        'sale'              => 30.95,
        'stock'             => 20,
        'short_description' => 'Performance wrap for post-workout hand recovery.',
        'description'       => 'Elastic support panel and secure closure provide firm compression when recovering after high-repetition work.',
    ),
    array(
        'name'              => 'Cloud Knit Relief Gloves',
        'sku'               => 'CLOUD-KNIT',
        'category'          => 'arthritis-gloves',
        'regular'           => 32.95,
        'sale'              => 0,
        'stock'             => 27,
        'short_description' => 'Soft knit comfort with gentle pressure zones.',
        'description'       => 'Designed for sensitive skin with seamless fingertips and a plush knit that applies even pressure.',
    ),
    array(
        'name'              => 'Starter Relief Twin Pack',
        'sku'               => 'STARTER-TWIN',
        'category'          => 'daily-comfort',
        'regular'           => 49.95,
        'sale'              => 39.95,
        'stock'             => 16,
        'short_description' => 'Two-pair value pack for first-time customers.',
        'description'       => 'Includes two best-selling comfort styles, making it easy to test different compression levels.',
    ),
);

$template_count = count( $templates );
$created_count  = 0;
$updated_count  = 0;

for ( $i = 0; $i < $count; $i++ ) {
    $template      = $templates[ $i % $template_count ];
    $variant_index = (int) floor( $i / $template_count ) + 1;
    $name_suffix   = $variant_index > 1 ? ' #' . $variant_index : '';
    $sku_suffix    = $variant_index > 1 ? '-' . $variant_index : '';

    $sku         = 'DUMMY-' . $template['sku'] . $sku_suffix;
    $product_id  = wc_get_product_id_by_sku( $sku );
    $product     = $product_id ? wc_get_product( $product_id ) : new WC_Product_Simple();
    $is_existing = (bool) $product_id;

    if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
        $product = new WC_Product_Simple();
    }

    $regular_price = (float) $template['regular'] + ( max( 0, $variant_index - 1 ) * 2 );
    $sale_price    = (float) $template['sale'];
    if ( $sale_price > 0 ) {
        $sale_price += ( max( 0, $variant_index - 1 ) * 2 );
    }

    $regular_price = number_format( $regular_price, 2, '.', '' );
    $sale_price    = $sale_price > 0 ? number_format( $sale_price, 2, '.', '' ) : '';
    $display_price = '' !== $sale_price ? $sale_price : $regular_price;
    $stock_qty     = (int) $template['stock'] + ( ( $i * 2 ) % 5 );

    $product->set_name( $template['name'] . $name_suffix );
    $product->set_status( 'publish' );
    $product->set_catalog_visibility( 'visible' );
    $product->set_description( $template['description'] );
    $product->set_short_description( $template['short_description'] );
    $product->set_sku( $sku );
    $product->set_regular_price( (string) $regular_price );
    $product->set_sale_price( (string) $sale_price );
    $product->set_price( (string) $display_price );
    $product->set_manage_stock( true );
    $product->set_stock_quantity( $stock_qty );
    $product->set_stock_status( $stock_qty > 0 ? 'instock' : 'outofstock' );
    $product->set_category_ids( array( $category_ids[ $template['category'] ] ) );
    $product->set_reviews_allowed( true );
    $product->set_sold_individually( false );

    $saved_id = $product->save();

    update_post_meta( $saved_id, '_brand_theme_seeded', '1' );
    update_post_meta( $saved_id, '_brand_theme_seed_source', 'scripts/seed-products.php' );
    update_post_meta( $saved_id, '_brand_theme_seed_batch_utc', gmdate( 'Y-m-d H:i:s' ) );

    if ( $is_existing ) {
        $updated_count++;
    } else {
        $created_count++;
    }

    if ( function_exists( 'wc_delete_product_transients' ) ) {
        wc_delete_product_transients( $saved_id );
    }
}

if ( function_exists( 'wc_update_product_lookup_tables' ) ) {
    wc_update_product_lookup_tables();
}

brand_seed_log(
    sprintf(
        'Seed complete: %d created, %d updated (%d requested).',
        $created_count,
        $updated_count,
        $count
    )
);

brand_seed_log( 'Categories ready: Arthritis Gloves, Compression Gloves, Recovery Wraps, Daily Comfort.' );
