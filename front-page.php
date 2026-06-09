<?php
/**
 * Front page template.
 *
 * @package BrandTheme
 */

get_header();
?>

<main>

	<!-- Hero — Full-bleed background image -->
	<section class="relative isolate flex min-h-[60vh] items-center overflow-hidden lg:min-h-[70vh]">
		<!-- Background image (media library → Smush CDN / srcset) -->
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- helper returns escaped markup.
		echo brand_theme_uploads_image(
			'2023/04/Plantar-Fasciitis-Roller-Ball-Home-Page-For-Plantar-Fasciitis-Socks-Australia.jpg',
			'',
			'absolute inset-0 -z-20 h-full w-full object-cover object-center',
			'full',
			array(
				'aria-hidden'   => 'true',
				'loading'       => 'eager',
				'fetchpriority' => 'high',
			)
		);
		?>
		<!-- Dark overlay for legibility -->
		<div class="absolute inset-0 -z-10 bg-black/40"></div>

		<div class="mx-auto w-full max-w-5xl px-4 py-20 text-center sm:px-6 sm:py-28 lg:px-8 lg:py-32">
			<div class="mx-auto max-w-3xl bg-black/30 p-8 backdrop-blur-[2px] sm:p-12">
				<h1 class="text-3xl font-extrabold uppercase tracking-wide text-white drop-shadow-sm sm:text-4xl lg:text-5xl">
					<?php esc_html_e( 'Plantar Fasciitis Socks and Relief Products', 'brand-theme' ); ?>
				</h1>
				<p class="mx-auto mt-5 max-w-2xl text-base text-white/90 sm:text-lg">
					<?php esc_html_e( 'Targeted compression socks, soothing massage tools, and proven relief — trusted by Aussies to ease heel and arch pain, every step of the day.', 'brand-theme' ); ?>
				</p>
				<a
					href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>"
					class="mt-8 inline-block rounded-lg bg-brand-600 px-8 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700"
				>
					<?php esc_html_e( 'Shop Now', 'brand-theme' ); ?>
				</a>
			</div>
		</div>
	</section>

	<!-- Trust Bar -->
	<section class="border-y border-gray-200">
		<div class="mx-auto flex max-w-7xl flex-col items-center justify-center gap-6 px-4 py-6 sm:flex-row sm:gap-12 sm:px-6 lg:px-8">
			<div class="flex items-center gap-2 text-sm text-gray-700">
				<?php brand_theme_icon( 'truck', array( 'class' => 'h-5 w-5 text-brand-600' ) ); ?>
				<span><?php esc_html_e( 'Free Shipping Australia-Wide', 'brand-theme' ); ?></span>
			</div>
			<div class="flex items-center gap-2 text-sm text-gray-700">
				<?php brand_theme_icon( 'shield-check', array( 'class' => 'h-5 w-5 text-brand-600' ) ); ?>
				<span><?php esc_html_e( '30-Day Money-Back Guarantee', 'brand-theme' ); ?></span>
			</div>
			<div class="flex items-center gap-2 text-sm text-gray-700">
				<?php brand_theme_icon( 'package', array( 'class' => 'h-5 w-5 text-brand-600' ) ); ?>
				<span><?php esc_html_e( 'Australian Owned & Operated', 'brand-theme' ); ?></span>
			</div>
		</div>
	</section>

	<!-- Featured Products -->
	<?php if ( function_exists( 'wc_get_products' ) ) : ?>
		<?php
		$featured_products = wc_get_products( array(
			'featured' => true,
			'limit'    => 4,
			'status'   => 'publish',
		) );
		?>

		<?php if ( $featured_products ) : ?>
			<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
				<h2 class="text-center text-2xl font-bold text-gray-900 sm:text-3xl">
					<?php esc_html_e( 'Featured Products', 'brand-theme' ); ?>
				</h2>

				<div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
					<?php foreach ( $featured_products as $product ) : ?>
						<?php
						$GLOBALS['product'] = $product;
						get_template_part( 'template-parts/content/product-card' );
						?>
					<?php endforeach; ?>
					<?php wp_reset_postdata(); ?>
				</div>

				<div class="mt-8 text-center">
					<a
						href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>"
						class="inline-block text-sm font-semibold text-brand-600 transition hover:text-brand-700"
					>
						<?php esc_html_e( 'Shop All Products →', 'brand-theme' ); ?>
					</a>
				</div>
			</section>
		<?php endif; ?>
	<?php endif; ?>

	<!-- Mission / About -->
	<section class="bg-zinc-50">
		<div class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
			<h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">
				<?php esc_html_e( 'Our Mission', 'brand-theme' ); ?>
			</h2>
			<p class="mt-4 text-gray-600">
				<?php esc_html_e( 'At Plantar Fasciitis Socks Australia, our mission is simple: to help Aussies get back on their feet, pain-free. We focus exclusively on plantar fasciitis — sourcing and designing compression socks, massage tools, and relief products that deliver real results. Every product is chosen for proven comfort and support, backed by genuine customer reviews and our 30-day satisfaction guarantee.', 'brand-theme' ); ?>
			</p>
		</div>
	</section>

	<!-- Benefits -->
	<section class="bg-brand-600">
		<div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
			<div class="max-w-3xl">
				<h2 class="text-2xl font-bold text-white sm:text-3xl">
					<?php esc_html_e( 'Discover the Benefits of Our Plantar Fasciitis Socks', 'brand-theme' ); ?>
				</h2>
				<p class="mt-4 text-brand-100">
					<?php esc_html_e( 'Our specially designed plantar fasciitis socks provide targeted compression and support to help alleviate heel pain, arch pain, and discomfort caused by plantar fasciitis. Made from high-quality, breathable materials, they keep you comfortable all day while promoting proper foot alignment and improved blood circulation.', 'brand-theme' ); ?>
				</p>
			</div>

			<div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
				<div class="rounded-xl bg-white p-8 shadow-sm">
					<?php brand_theme_icon( 'target', array( 'class' => 'h-9 w-9 text-brand-600' ) ); ?>
					<h3 class="mt-5 text-lg font-semibold text-gray-900">
						<?php esc_html_e( 'Targeted compression for pain relief and support', 'brand-theme' ); ?>
					</h3>
					<p class="mt-2 text-sm text-gray-600">
						<?php esc_html_e( 'Graduated compression supports the arch and heel right where plantar fasciitis pain strikes.', 'brand-theme' ); ?>
					</p>
				</div>

				<div class="rounded-xl bg-white p-8 shadow-sm">
					<?php brand_theme_icon( 'droplets', array( 'class' => 'h-9 w-9 text-brand-600' ) ); ?>
					<h3 class="mt-5 text-lg font-semibold text-gray-900">
						<?php esc_html_e( 'Reduced inflammation and swelling', 'brand-theme' ); ?>
					</h3>
					<p class="mt-2 text-sm text-gray-600">
						<?php esc_html_e( 'Gentle, consistent pressure helps calm inflammation and keep swelling down throughout the day.', 'brand-theme' ); ?>
					</p>
				</div>

				<div class="rounded-xl bg-white p-8 shadow-sm">
					<?php brand_theme_icon( 'heart-pulse', array( 'class' => 'h-9 w-9 text-brand-600' ) ); ?>
					<h3 class="mt-5 text-lg font-semibold text-gray-900">
						<?php esc_html_e( 'Enhanced blood circulation', 'brand-theme' ); ?>
					</h3>
					<p class="mt-2 text-sm text-gray-600">
						<?php esc_html_e( 'Improved circulation promotes faster recovery and keeps tired feet feeling fresh.', 'brand-theme' ); ?>
					</p>
				</div>
			</div>
		</div>
	</section>

	<!-- Get Back on Your Feet — sock range -->
	<?php
	// Socks shown in this section. Add a new sock by appending to this array.
	$sock_range = array(
		array(
			'title' => __( 'White Plantar Fasciitis Socks', 'brand-theme' ),
			'image' => '2023/04/plantar-fasciitis-socks-white-single-pair-with-box.jpg',
			'slug'  => 'white-plantar-fasciitis-compression-socks',
		),
		array(
			'title' => __( 'Black Plantar Fasciitis Socks', 'brand-theme' ),
			'image' => '2023/04/plantar-fasciitis-socks-black-single-pair-with-box.jpg',
			'slug'  => 'black-plantar-fasciitis-compression-socks',
		),
		array(
			'title' => __( 'Black/Copper Plantar Fasciitis Socks', 'brand-theme' ),
			'image' => '2026/06/black-copper-plantar-fasciitis-socks-single-pair-with-box.png',
			'slug'  => 'black-copper-plantar-fasciitis-compression-socks',
		),
	);
	?>
	<section class="bg-gray-50">
		<div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
			<div class="max-w-3xl">
				<h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">
					<?php esc_html_e( 'Get Back on Your Feet with Plantar Fasciitis Socks Australia', 'brand-theme' ); ?>
				</h2>
				<p class="mt-4 text-gray-600">
					<?php esc_html_e( 'Don\'t let plantar fasciitis pain hold you back from living an active and fulfilling life. Trust Plantar Fasciitis Socks Australia to provide you with the best plantar fasciitis socks and relief products on the market. Shop our selection today and experience the difference that superior comfort and support can make.', 'brand-theme' ); ?>
				</p>
			</div>

			<div class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
				<?php foreach ( $sock_range as $sock ) : ?>
					<?php
					$sock_post = get_page_by_path( $sock['slug'], OBJECT, 'product' );
					$sock_url  = $sock_post ? get_permalink( $sock_post ) : get_permalink( wc_get_page_id( 'shop' ) );
					?>
					<a href="<?php echo esc_url( $sock_url ); ?>" class="group block">
						<div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200 transition group-hover:shadow-md">
							<?php
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- helper returns escaped markup.
							echo brand_theme_uploads_image(
								$sock['image'],
								$sock['title'],
								'h-auto w-full object-cover transition duration-300 group-hover:scale-[1.02]'
							);
							?>
						</div>
						<h3 class="mt-5 text-xl font-bold text-gray-900 group-hover:text-brand-600">
							<?php echo esc_html( $sock['title'] ); ?>
						</h3>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- Why Choose -->
	<?php
	$why_choose = array(
		__( 'Expertly curated selection of products', 'brand-theme' ),
		__( 'Competitive pricing and unbeatable value', 'brand-theme' ),
		__( 'Fast shipping and hassle-free returns', 'brand-theme' ),
		__( 'Secure online shopping experience', 'brand-theme' ),
		__( 'Outstanding customer service', 'brand-theme' ),
	);
	?>
	<section class="bg-brand-600">
		<div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 lg:py-24">
			<div class="max-w-3xl">
				<h2 class="text-2xl font-bold text-white sm:text-3xl">
					<?php esc_html_e( 'Why Choose Plantar Fasciitis Socks Australia for Your Plantar Fasciitis Needs', 'brand-theme' ); ?>
				</h2>
				<p class="mt-4 text-brand-100">
					<?php esc_html_e( 'At Plantar Fasciitis Socks Australia, we are dedicated to providing you with the most effective and high-quality plantar fasciitis products available. Here\'s why our customers love shopping with us:', 'brand-theme' ); ?>
				</p>

				<ul class="mt-8 space-y-4">
					<?php foreach ( $why_choose as $reason ) : ?>
						<li class="flex items-center gap-3 text-white">
							<span class="flex h-6 w-6 flex-none items-center justify-center rounded bg-green-500">
								<?php brand_theme_icon( 'check', array( 'class' => 'h-4 w-4 text-white' ) ); ?>
							</span>
							<span><?php echo esc_html( $reason ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</section>

	<!-- Testimonials — real customer reviews -->
	<?php
	// Social proof from real reviews: featured first, then the most recent.
	// Keep 4★+ reviews that have text, de-duplicated, capped at 3.
	$home_reviews    = brand_theme_get_featured_reviews( 6 );
	$recent_comments = get_comments( array(
		'status'    => 'approve',
		'parent'    => 0,
		'post_type' => 'product',
		'number'    => 12,
		'orderby'   => 'comment_date_gmt',
		'order'     => 'DESC',
	) );
	foreach ( $recent_comments as $home_comment ) {
		if ( in_array( $home_comment->comment_type, array( 'review', 'comment', '' ), true ) ) {
			$home_reviews[] = brand_theme_map_review_comment( $home_comment );
		}
	}

	$home_seen    = array();
	$testimonials = array();
	foreach ( $home_reviews as $home_review ) {
		if ( isset( $home_seen[ $home_review['id'] ] ) ) {
			continue;
		}
		$home_seen[ $home_review['id'] ] = true;
		if ( ( $home_review['rating'] ?? 0 ) < 4 || '' === trim( (string) $home_review['text'] ) ) {
			continue;
		}
		$testimonials[] = array(
			'quote'    => $home_review['text'],
			'author'   => $home_review['author'],
			'verified' => $home_review['verified'],
		);
		if ( count( $testimonials ) >= 3 ) {
			break;
		}
	}
	?>
	<?php if ( $testimonials ) : ?>
	<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
		<h2 class="text-center text-2xl font-bold text-gray-900 sm:text-3xl">
			<?php esc_html_e( 'What Our Customers Say', 'brand-theme' ); ?>
		</h2>

		<div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
			<?php
			foreach ( $testimonials as $testimonial ) :
				set_query_var( 'testimonial', $testimonial );
				get_template_part( 'template-parts/content/single-product/testimonial' );
			endforeach;
			?>
		</div>
	</section>
	<?php endif; ?>

	<!-- CTA Banner -->
	<section class="bg-brand-600">
		<div class="mx-auto max-w-7xl px-4 py-16 text-center sm:px-6 lg:px-8">
			<h2 class="text-2xl font-bold text-white sm:text-3xl">
				<?php esc_html_e( 'Ready to Experience the Difference?', 'brand-theme' ); ?>
			</h2>
			<p class="mx-auto mt-4 max-w-2xl text-brand-100">
				<?php esc_html_e( 'Join thousands of happy customers across Australia. Free shipping on every order.', 'brand-theme' ); ?>
			</p>
			<a
				href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>"
				class="mt-8 inline-block rounded-lg bg-white px-8 py-3 text-sm font-semibold text-brand-600 shadow-sm transition hover:bg-brand-50"
			>
				<?php esc_html_e( 'Shop Now', 'brand-theme' ); ?>
			</a>
		</div>
	</section>

</main>

<?php
get_footer();
