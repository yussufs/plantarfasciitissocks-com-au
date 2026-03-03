<?php
/**
 * Front page template.
 *
 * @package BrandTheme
 */

get_header();
?>

<main>

	<!-- Hero — Split Layout -->
	<section class="bg-brand-50">
		<div class="mx-auto grid max-w-7xl items-center gap-8 px-4 py-16 sm:px-6 lg:grid-cols-2 lg:gap-16 lg:px-8 lg:py-24">
			<div>
				<h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl lg:text-6xl">
					<?php esc_html_e( 'Premium Quality You Can Trust', 'brand-theme' ); ?>
				</h1>
				<p class="mt-4 text-lg text-gray-600 sm:text-xl">
					<?php esc_html_e( 'Discover our carefully crafted products designed to make a real difference in your everyday life.', 'brand-theme' ); ?>
				</p>
				<a
					href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>"
					class="mt-8 inline-block rounded-lg bg-brand-600 px-8 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-700"
				>
					<?php esc_html_e( 'Shop Now', 'brand-theme' ); ?>
				</a>
			</div>
			<div class="overflow-hidden rounded-2xl">
				<?php brand_theme_picture( 'hero-product.jpg', __( 'Featured product', 'brand-theme' ), 'h-full w-full object-cover', '(max-width: 1024px) 100vw, 50vw' ); ?>
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
		<div class="mx-auto grid max-w-7xl items-center gap-8 px-4 py-16 sm:px-6 lg:grid-cols-2 lg:gap-16 lg:px-8 lg:py-24">
			<div>
				<h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">
					<?php esc_html_e( 'Our Mission', 'brand-theme' ); ?>
				</h2>
				<p class="mt-4 text-gray-600">
					<?php esc_html_e( 'We believe everyone deserves access to high-quality products that genuinely improve their wellbeing. That\'s why we source only the best materials and work with trusted manufacturers to deliver products you can rely on — backed by real customer reviews and our 30-day satisfaction guarantee.', 'brand-theme' ); ?>
				</p>
			</div>
			<div class="overflow-hidden rounded-2xl">
				<?php brand_theme_picture( 'about-mission.jpg', __( 'Our mission', 'brand-theme' ), 'h-full w-full object-cover', '(max-width: 1024px) 100vw, 50vw' ); ?>
			</div>
		</div>
	</section>

	<!-- Testimonials -->
	<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
		<h2 class="text-center text-2xl font-bold text-gray-900 sm:text-3xl">
			<?php esc_html_e( 'What Our Customers Say', 'brand-theme' ); ?>
		</h2>

		<div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
			<?php
			$testimonials = array(
				array(
					'quote'    => 'Absolutely love this product! It arrived quickly and the quality exceeded my expectations. Will definitely be ordering again.',
					'author'   => 'Sarah M.',
					'verified' => true,
				),
				array(
					'quote'    => 'I was sceptical at first, but after using it for a few weeks I can honestly say it\'s made a huge difference. Highly recommend.',
					'author'   => 'James T.',
					'verified' => true,
				),
				array(
					'quote'    => 'Great customer service and fast shipping. The product is exactly as described. Five stars from me!',
					'author'   => 'Michelle R.',
					'verified' => true,
				),
			);

			foreach ( $testimonials as $testimonial ) :
				set_query_var( 'testimonial', $testimonial );
				get_template_part( 'template-parts/content/single-product/testimonial' );
			endforeach;
			?>
		</div>
	</section>

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
