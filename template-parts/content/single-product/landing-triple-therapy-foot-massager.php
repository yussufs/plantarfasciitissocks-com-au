<?php
/**
 * Custom long-form product landing page — Triple Therapy Foot Massager.
 *
 * Built for cold / ad traffic. Rendered full-width from
 * woocommerce/single-product.php via a slug branch. Reuses the standard product
 * Svelte mounts (#product-gallery, #product-options, #product-reviews) so the
 * real WooCommerce cart / variation / bundle flow keeps working — only the page
 * structure and copy are bespoke.
 *
 * @package BrandTheme
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
	return;
}

$svelte_data = brand_theme_get_product_svelte_data( $product );
$uploads     = trailingslashit( wp_get_upload_dir()['baseurl'] );

// Social proof — real review data only (the proof bar is hidden when there are none).
$review_count = (int) $product->get_review_count();
$avg_rating   = (float) $product->get_average_rating();
$has_reviews  = ! empty( brand_theme_get_reviews( $product ) );

// Standard product-page hero data (mirrors woocommerce/single-product.php so the
// top of the page reads as a genuine product page for Google Shopping review).
$short_desc = $product->get_short_description();
$benefits   = array();
if ( $short_desc ) {
	$lines = preg_split( '/\r?\n/', wp_strip_all_tags( $short_desc ) );
	foreach ( $lines as $line ) {
		$line = trim( $line, " \t\n\r\0\x0B-•*" );
		if ( $line ) {
			$benefits[] = $line;
		}
	}
}
$delivery_days = get_post_meta( $product->get_id(), '_brand_delivery_days', true );
if ( ! $delivery_days ) {
	$delivery_days = '3-7';
}

// Lead price for the sticky bar (first bundle tier, else active price).
$currency   = $svelte_data['currencySymbol'];
$lead_price = ! empty( $svelte_data['bundleTiers'] ) ? (float) $svelte_data['bundleTiers'][0]['price'] : (float) $svelte_data['activePrice'];

// Inline helper: looping muted autoplay video, theme-styled.
$ttfm_video = static function ( $file ) use ( $uploads ) {
	printf(
		'<video class="w-full rounded-2xl shadow-sm" autoplay loop muted playsinline controlslist="nodownload" src="%s"></video>',
		esc_url( $uploads . $file )
	);
};
?>

<main class="landing-page">

	<!-- ─── Product hero (standard product-page layout, above the fold) ──── -->
	<section id="order-section" class="scroll-mt-4 mx-auto max-w-7xl px-4 pt-6 pb-10 sm:px-6 lg:px-8 lg:pt-10 lg:pb-12">
		<div class="grid grid-cols-1 gap-5 lg:grid-cols-2 lg:gap-12">
			<!-- Gallery -->
			<div class="min-w-0 lg:sticky lg:top-8 lg:self-start">
				<?php
				set_query_var( 'gallery_images', $svelte_data['images'] );
				get_template_part( 'template-parts/content/single-product/gallery' );
				?>
			</div>

			<!-- Details -->
			<div class="min-w-0 space-y-3 lg:space-y-5">
				<?php get_template_part( 'template-parts/content/single-product/product-badge' ); ?>

				<h1 class="product-title"><?php echo esc_html( $product->get_name() ); ?></h1>

				<?php if ( $review_count > 0 ) : ?>
					<a href="#product-reviews" class="inline-flex items-center gap-1.5 group no-underline">
						<span class="flex gap-0.5">
							<?php for ( $i = 1; $i <= 5; $i++ ) :
								if ( $i <= floor( $avg_rating ) ) :
									brand_theme_icon( 'star', array( 'class' => 'w-4 h-4 text-amber-400 fill-amber-400' ) );
								else :
									brand_theme_icon( 'star', array( 'class' => 'w-4 h-4 text-zinc-300' ) );
								endif;
							endfor; ?>
						</span>
						<span class="text-sm font-medium text-zinc-700"><?php echo esc_html( number_format( $avg_rating, 1 ) ); ?></span>
						<span class="text-sm text-zinc-500 group-hover:text-zinc-700 transition-colors">(<?php echo esc_html( $review_count ); ?> <?php echo esc_html( 1 === $review_count ? 'review' : 'reviews' ); ?>)</span>
					</a>
				<?php endif; ?>

				<?php get_template_part( 'template-parts/content/single-product/trust-badges' ); ?>

				<?php
				set_query_var( 'svelte_data', $svelte_data );
				get_template_part( 'template-parts/content/single-product/price-options' );
				?>

				<?php get_template_part( 'template-parts/content/single-product/payment-icons' ); ?>

				<?php
				set_query_var( 'delivery_days', $delivery_days );
				get_template_part( 'template-parts/content/single-product/delivery-estimate' );
				?>

				<?php if ( ! empty( $benefits ) ) :
					set_query_var( 'product_benefits', $benefits );
					get_template_part( 'template-parts/content/single-product/product-benefits' );
				endif; ?>
			</div>
		</div>
	</section>

	<!-- ─── 1. STORY HERO ───────────────────────────────────────────────── -->
	<section class="mx-auto max-w-7xl px-4 pt-6 pb-12 sm:px-6 lg:px-8 lg:pt-10 lg:pb-16">
		<div class="grid gap-8 lg:grid-cols-2 lg:items-center lg:gap-14">
			<div>
				<h2 class="text-3xl font-extrabold leading-tight text-zinc-900 sm:text-4xl">
					<?php esc_html_e( 'Finally, Real Relief for Tired, Aching Feet — in Just 15 Minutes a Day', 'brand-theme' ); ?>
				</h2>
				<p class="mt-4 text-lg text-zinc-700">
					<?php esc_html_e( 'Warming heat, gentle massage, and light compression in one easy device — designed to soothe sore heels, aching arches, and tired, overworked feet at the end of the day.', 'brand-theme' ); ?>
				</p>

				<a href="#order-section" class="offer-cta mt-7"><?php esc_html_e( 'GET MINE', 'brand-theme' ); ?> &rsaquo;</a>
			</div>

			<div>
				<?php $ttfm_video( '2026/05/asset_1753357541368.mp4' ); ?>
			</div>
		</div>
	</section>

	<!-- ─── 2. SOCIAL PROOF BAR ─────────────────────────────────────────── -->
	<?php if ( $has_reviews && $review_count > 0 ) : ?>
		<section class="bg-brand-600 px-4 py-3 text-center text-white">
			<p class="flex flex-wrap items-center justify-center gap-2 text-sm font-semibold sm:text-base">
				<span class="flex gap-0.5">
					<?php for ( $i = 1; $i <= 5; $i++ ) {
						$star_class = $i <= round( $avg_rating ) ? 'h-5 w-5 fill-current text-amber-300' : 'h-5 w-5 text-amber-300/40';
						brand_theme_icon( 'star', array( 'class' => $star_class ) );
					} ?>
				</span>
				<?php
				/* translators: 1: average rating, 2: review count. */
				printf(
					esc_html( _n( 'Rated %1$s/5 by %2$s happy customer', 'Rated %1$s/5 by %2$s happy customers', $review_count, 'brand-theme' ) ),
					esc_html( number_format( $avg_rating, 1 ) ),
					esc_html( number_format( $review_count ) )
				);
				?>
			</p>
		</section>
	<?php endif; ?>

	<!-- ─── 3. THE PROBLEM ──────────────────────────────────────────────── -->
	<section class="mx-auto max-w-3xl px-4 py-12 text-center sm:px-6 lg:py-16">
		<h2 class="text-2xl font-extrabold text-zinc-900 sm:text-3xl">
			<?php esc_html_e( 'If your feet hurt by the end of the day, you already know the drill.', 'brand-theme' ); ?>
		</h2>
		<div class="mt-5 space-y-4 text-lg text-zinc-700">
			<p><?php esc_html_e( 'The sharp heel pain on your first steps in the morning. The deep ache after a long day on your feet. The sore arches and stiffness no amount of stretching fully fixes.', 'brand-theme' ); ?></p>
			<p><?php esc_html_e( 'Most people just reach for painkillers, book expensive appointments, or learn to live with it.', 'brand-theme' ); ?></p>
			<p class="font-semibold text-zinc-900"><?php esc_html_e( 'There’s a simpler way to give your feet relief — at home, on your own schedule.', 'brand-theme' ); ?></p>
		</div>
	</section>

	<!-- ─── 4. THE SOLUTION — Triple Therapy ────────────────────────────── -->
	<section class="bg-zinc-50 px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
		<div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-2 lg:items-center lg:gap-14">
			<div>
				<?php
				echo brand_theme_uploads_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- helper returns escaped markup.
					'2026/05/secondary-main-gray-foot-massager.jpg',
					__( 'Triple Therapy Foot Massager', 'brand-theme' ),
					'w-full rounded-2xl shadow-sm'
				);
				?>
			</div>
			<div>
				<h2 class="text-2xl font-extrabold text-zinc-900 sm:text-3xl">
					<?php esc_html_e( 'Meet the Triple Therapy Foot Massager', 'brand-theme' ); ?>
				</h2>
				<p class="mt-4 text-zinc-700">
					<?php esc_html_e( 'It looks like a simple foot wrap. But the moment you turn it on, you’ll feel the difference. Three soothing therapies, one device:', 'brand-theme' ); ?>
				</p>

				<div class="mt-6 space-y-5">
					<?php
					$mechanisms = array(
						array(
							'icon'  => 'flame',
							'title' => __( 'Warming Heat', 'brand-theme' ),
							'text'  => __( 'Gently warms tired, cold, stiff feet to help you relax and loosen up.', 'brand-theme' ),
						),
						array(
							'icon'  => 'target',
							'title' => __( 'Targeted Massage', 'brand-theme' ),
							'text'  => __( 'Works into the arch and heel where the deepest aches and tension build up.', 'brand-theme' ),
						),
						array(
							'icon'  => 'shield-check',
							'title' => __( 'Gentle Compression', 'brand-theme' ),
							'text'  => __( 'Wraps your feet in supportive, all-over comfort.', 'brand-theme' ),
						),
					);
					foreach ( $mechanisms as $m ) :
						?>
						<div class="flex items-start gap-4">
							<span class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-full bg-brand-100 text-brand-600">
								<?php brand_theme_icon( $m['icon'], array( 'class' => 'h-6 w-6' ) ); ?>
							</span>
							<div>
								<h3 class="font-bold text-zinc-900"><?php echo esc_html( $m['title'] ); ?></h3>
								<p class="text-zinc-700"><?php echo esc_html( $m['text'] ); ?></p>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

				<p class="mt-6 font-semibold text-zinc-900">
					<?php esc_html_e( 'Together, 15 minutes feels like a professional foot session — without leaving the couch.', 'brand-theme' ); ?>
				</p>
				<a href="#order-section" class="offer-cta mt-6"><?php esc_html_e( 'GET MINE', 'brand-theme' ); ?> &rsaquo;</a>
			</div>
		</div>
	</section>

	<!-- ─── 5. BENEFITS ─────────────────────────────────────────────────── -->
	<section class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
		<h2 class="text-center text-2xl font-extrabold text-zinc-900 sm:text-3xl">
			<?php esc_html_e( 'Why people love it', 'brand-theme' ); ?>
		</h2>
		<ul class="mt-8 grid gap-x-8 gap-y-3 sm:grid-cols-2">
			<?php
			$benefits = array(
				__( 'Eases everyday foot pain, sore heels, and arch tension', 'brand-theme' ),
				__( 'Soothes tired, sore, swollen feet after a long day', 'brand-theme' ),
				__( 'Warms cold feet and helps you wind down before bed', 'brand-theme' ),
				__( 'Drug-free — no pills, no side effects, no prescriptions', 'brand-theme' ),
				__( 'Costs less than a single foot-care appointment', 'brand-theme' ),
				__( '3 adjustable intensity levels — set it to what feels good', 'brand-theme' ),
				__( 'Lightweight — use it while you read, watch TV, or relax', 'brand-theme' ),
			);
			foreach ( $benefits as $benefit ) :
				?>
				<li class="flex items-start gap-2.5 text-zinc-700">
					<?php brand_theme_icon( 'check', array( 'class' => 'mt-0.5 h-5 w-5 flex-shrink-0 text-brand-600' ) ); ?>
					<span><?php echo esc_html( $benefit ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>

	<!-- ─── 6. HOW IT WORKS ─────────────────────────────────────────────── -->
	<section class="bg-zinc-50 px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
		<div class="mx-auto max-w-7xl">
			<h2 class="text-center text-2xl font-extrabold text-zinc-900 sm:text-3xl">
				<?php esc_html_e( 'Relief in 3 simple steps', 'brand-theme' ); ?>
			</h2>
			<div class="mt-10 grid gap-8 md:grid-cols-3">
				<?php
				$steps = array(
					array(
						'video' => '2026/05/asset_1753358000519.mp4',
						'step'  => __( 'Step 1', 'brand-theme' ),
						'title' => __( 'Wrap it on', 'brand-theme' ),
						'text'  => __( 'Slide your feet in and fasten the adjustable straps for a snug fit.', 'brand-theme' ),
					),
					array(
						'video' => '2026/05/asset_1753358205540.mp4',
						'step'  => __( 'Step 2', 'brand-theme' ),
						'title' => __( 'Choose your settings', 'brand-theme' ),
						'text'  => __( 'Pick your heat and massage level on the simple control panel.', 'brand-theme' ),
					),
					array(
						'video' => '2026/05/asset_1753358042473.mp4',
						'step'  => __( 'Step 3', 'brand-theme' ),
						'title' => __( 'Sit back and relax', 'brand-theme' ),
						'text'  => __( 'In minutes you’ll feel warmth, massage, and compression working together. Use 10–15 minutes a day, as often as feels good.', 'brand-theme' ),
					),
				);
				foreach ( $steps as $step ) :
					?>
					<div>
						<?php $ttfm_video( $step['video'] ); ?>
						<p class="mt-4 text-sm font-bold uppercase tracking-wide text-brand-600"><?php echo esc_html( $step['step'] ); ?></p>
						<h3 class="mt-1 text-lg font-bold text-zinc-900"><?php echo esc_html( $step['title'] ); ?></h3>
						<p class="mt-2 text-sm text-zinc-700"><?php echo esc_html( $step['text'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- ─── 7. WHO IT'S FOR ─────────────────────────────────────────────── -->
	<section class="mx-auto max-w-3xl px-4 py-12 text-center sm:px-6 lg:py-16">
		<h2 class="text-2xl font-extrabold text-zinc-900 sm:text-3xl">
			<?php esc_html_e( 'Made for feet that work hard', 'brand-theme' ); ?>
		</h2>
		<p class="mt-5 text-lg text-zinc-700">
			<?php esc_html_e( 'Whether you’re on your feet all day, dealing with sore heels and aching arches, recovering after exercise, or just want to unwind in the evening — this is for you. Especially loved by nurses, tradies, retail and hospitality workers, runners, and anyone whose feet are done by the end of the day.', 'brand-theme' ); ?>
		</p>
		<p class="mx-auto mt-5 max-w-2xl text-sm italic text-zinc-500">
			<?php esc_html_e( 'Note: if you have a pacemaker or other implanted device, are pregnant, or have a foot-related medical condition, check with your doctor before use.', 'brand-theme' ); ?>
		</p>
	</section>

	<!-- ─── 8. COMPARISON ───────────────────────────────────────────────── -->
	<section class="bg-zinc-50 px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
		<div class="mx-auto max-w-3xl">
			<h2 class="text-center text-2xl font-extrabold text-zinc-900 sm:text-3xl">
				<?php esc_html_e( 'A smarter way to care for your feet', 'brand-theme' ); ?>
			</h2>
			<div class="mt-8 overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-zinc-200">
				<table class="w-full text-sm">
					<thead>
						<tr class="border-b border-zinc-200">
							<th class="p-3 text-left"></th>
							<th class="bg-brand-50 p-3 text-center font-bold text-brand-700"><?php esc_html_e( 'Triple Therapy Massager', 'brand-theme' ); ?></th>
							<th class="p-3 text-center font-semibold text-zinc-500"><?php esc_html_e( 'Painkillers', 'brand-theme' ); ?></th>
							<th class="p-3 text-center font-semibold text-zinc-500"><?php esc_html_e( 'Clinic visits', 'brand-theme' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						// yes / no / note per column: [feature, massager, painkillers, clinic].
						$compare_yes  = static function () {
							echo '<span class="inline-flex">';
							brand_theme_icon( 'check', array( 'class' => 'h-5 w-5 text-green-600' ) );
							echo '</span>';
						};
						$compare_no   = static function () {
							echo '<span class="inline-flex">';
							brand_theme_icon( 'x', array( 'class' => 'h-5 w-5 text-zinc-300' ) );
							echo '</span>';
						};
						$compare_rows = array(
							array( __( 'Drug-free', 'brand-theme' ), 'yes', 'no', 'yes' ),
							array( __( 'Use anytime at home', 'brand-theme' ), 'yes', 'yes', 'no' ),
							array( __( 'One-time cost', 'brand-theme' ), 'yes', 'no', 'no' ),
							array( __( 'Relaxing, not just a fix', 'brand-theme' ), 'yes', 'no', 'yes' ),
						);
						foreach ( $compare_rows as $row ) :
							?>
							<tr class="border-b border-zinc-100 last:border-0">
								<td class="p-3 font-medium text-zinc-700"><?php echo esc_html( $row[0] ); ?></td>
								<?php for ( $c = 1; $c <= 3; $c++ ) : ?>
									<td class="p-3 text-center <?php echo 1 === $c ? 'bg-brand-50' : ''; ?>">
										<?php 'yes' === $row[ $c ] ? $compare_yes() : $compare_no(); ?>
									</td>
								<?php endfor; ?>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</section>

	<?php if ( $has_reviews ) : ?>
		<!-- ─── 9. REVIEWS ─────────────────────────────────────────────── -->
		<section class="mx-auto max-w-7xl px-4 pt-12 sm:px-6 lg:px-8 lg:pt-16">
			<h2 class="text-center text-2xl font-extrabold text-zinc-900 sm:text-3xl">
				<?php esc_html_e( 'What customers are saying', 'brand-theme' ); ?>
			</h2>
		</section>
		<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
			<?php get_template_part( 'template-parts/content/single-product/reviews' ); ?>
		</div>
	<?php endif; ?>

	<!-- ─── 10. CTA BAND ────────────────────────────────────────────────── -->
	<section class="px-4 py-12 text-center sm:px-6 lg:px-8 lg:py-16">
		<div class="mx-auto max-w-2xl">
			<h2 class="text-2xl font-extrabold text-zinc-900 sm:text-3xl">
				<?php esc_html_e( 'Ready to give your feet a break?', 'brand-theme' ); ?>
			</h2>
			<p class="mt-3 text-zinc-700">
				<?php esc_html_e( 'Choose your colour and pack size at the top of the page — the 2-pack is our best value.', 'brand-theme' ); ?>
			</p>
			<a href="#order-section" class="offer-cta mt-6"><?php esc_html_e( 'GET MINE', 'brand-theme' ); ?> &rsaquo;</a>
			<p class="mt-4 flex flex-wrap justify-center gap-x-4 gap-y-1 text-sm text-zinc-600">
				<span class="flex items-center gap-1.5"><?php brand_theme_icon( 'truck', array( 'class' => 'h-4 w-4 text-brand-600' ) ); ?><?php esc_html_e( 'Free shipping on every order', 'brand-theme' ); ?></span>
				<span class="flex items-center gap-1.5"><?php brand_theme_icon( 'shield-check', array( 'class' => 'h-4 w-4 text-brand-600' ) ); ?><?php esc_html_e( '30-day money-back guarantee', 'brand-theme' ); ?></span>
			</p>
		</div>
	</section>

	<!-- ─── 11. GUARANTEE & TRUST ───────────────────────────────────────── -->
	<section class="bg-zinc-50 px-4 py-12 text-center sm:px-6 lg:px-8 lg:py-16">
		<div class="mx-auto max-w-2xl">
			<span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-brand-100 text-brand-600">
				<?php brand_theme_icon( 'shield-check', array( 'class' => 'h-8 w-8' ) ); ?>
			</span>
			<h2 class="mt-4 text-2xl font-extrabold text-zinc-900 sm:text-3xl">
				<?php esc_html_e( 'Try it risk-free for 30 days', 'brand-theme' ); ?>
			</h2>
			<p class="mt-3 text-zinc-700">
				<?php esc_html_e( 'We’re confident your feet will thank you. If you’re not happy, send it back within 30 days for a full refund. No fuss.', 'brand-theme' ); ?>
			</p>
			<div class="mt-6 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-sm font-medium text-zinc-600">
				<span class="flex items-center gap-1.5"><?php brand_theme_icon( 'shield-check', array( 'class' => 'h-5 w-5 text-brand-600' ) ); ?><?php esc_html_e( 'Secure Checkout', 'brand-theme' ); ?></span>
				<span class="flex items-center gap-1.5"><?php brand_theme_icon( 'truck', array( 'class' => 'h-5 w-5 text-brand-600' ) ); ?><?php esc_html_e( 'Free Shipping', 'brand-theme' ); ?></span>
				<span class="flex items-center gap-1.5"><?php brand_theme_icon( 'refresh-ccw', array( 'class' => 'h-5 w-5 text-brand-600' ) ); ?><?php esc_html_e( '30-Day Money-Back Guarantee', 'brand-theme' ); ?></span>
			</div>
		</div>
	</section>

	<!-- ─── 12. FAQ ─────────────────────────────────────────────────────── -->
	<section class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:py-16">
		<h2 class="text-center text-2xl font-extrabold text-zinc-900 sm:text-3xl">
			<?php esc_html_e( 'Frequently asked questions', 'brand-theme' ); ?>
		</h2>
		<div class="mt-8 space-y-3">
			<?php
			$faqs = array(
				array(
					'q' => __( 'How often should I use it?', 'brand-theme' ),
					'a' => __( '10–15 minutes a day, as often as you like. Many people use it in the evening to unwind.', 'brand-theme' ),
				),
				array(
					'q' => __( 'Does it hurt?', 'brand-theme' ),
					'a' => __( 'No — it’s designed to feel soothing. Start on the lowest setting and adjust to what feels comfortable.', 'brand-theme' ),
				),
				array(
					'q' => __( 'Can I use it alongside compression socks or insoles?', 'brand-theme' ),
					'a' => __( 'Yes. Lots of customers wear support socks or insoles through the day and use the massager to relax their feet in the evening.', 'brand-theme' ),
				),
				array(
					'q' => __( 'How long until I feel a difference?', 'brand-theme' ),
					'a' => __( 'Many people feel relaxed and warmed after the first session. For aches and stiffness, regular daily use gives the best results.', 'brand-theme' ),
				),
				array(
					'q' => __( 'How is it powered, and how long does it last?', 'brand-theme' ),
					'a' => __( 'It’s completely cordless and USB-rechargeable, with a built-in 1100mAh battery — charge it up and use it anywhere at home, the office, or while travelling. Just note it can’t be switched on while it’s charging.', 'brand-theme' ),
				),
				array(
					'q' => __( 'What’s in the box, and what are the settings?', 'brand-theme' ),
					'a' => __( 'You get the heated foot wrap plus a USB charging cable. It has 4 infrared heat levels and 3 massage vibration modes — gentle daily care, sports recovery, and deeper relief — all controlled with simple power, heat, and massage buttons. Soft cloth material, 7.5W, available in Black or Grey.', 'brand-theme' ),
				),
				array(
					'q' => __( 'What’s your return policy?', 'brand-theme' ),
					'a' => sprintf(
						/* translators: %s: refund policy link (opening tag) … closing tag. */
						__( 'Simple — 30 days, money back. If you’re not happy, just send it back for a full refund. See our %1$srefund policy%2$s for the full details.', 'brand-theme' ),
						'<a href="' . esc_url( home_url( '/refund-policy/' ) ) . '" target="_blank" rel="noopener" class="font-medium text-brand-600 underline">',
						'</a>'
					),
				),
			);
			foreach ( $faqs as $faq ) :
				?>
				<details class="group rounded-xl bg-white shadow-sm ring-1 ring-zinc-200">
					<summary class="flex cursor-pointer items-center justify-between gap-3 p-4 font-semibold text-zinc-900 [&::-webkit-details-marker]:hidden">
						<span><?php echo esc_html( $faq['q'] ); ?></span>
						<?php brand_theme_icon( 'chevron-down', array( 'class' => 'h-5 w-5 flex-shrink-0 text-zinc-400 transition-transform group-open:rotate-180' ) ); ?>
					</summary>
					<div class="px-4 pb-4 text-zinc-700"><?php echo wp_kses_post( $faq['a'] ); ?></div>
				</details>
			<?php endforeach; ?>
		</div>
	</section>

	<!-- ─── 13. FINAL CTA ───────────────────────────────────────────────── -->
	<section class="bg-brand-600 px-4 py-14 text-center sm:px-6 lg:px-8">
		<div class="mx-auto max-w-2xl">
			<h2 class="text-2xl font-extrabold text-white sm:text-3xl">
				<?php esc_html_e( 'Give your feet the relief they’ve been asking for.', 'brand-theme' ); ?>
			</h2>
			<a href="#order-section" class="offer-cta mt-7 bg-white !text-brand-700 hover:bg-zinc-100"><?php esc_html_e( 'GET MINE', 'brand-theme' ); ?> &rsaquo;</a>
			<p class="mt-4 text-sm text-brand-100"><?php esc_html_e( 'Limited-time pricing while stock lasts.', 'brand-theme' ); ?></p>
		</div>
	</section>

	<!-- ─── Sticky mobile add-to-cart bar ───────────────────────────────── -->
	<div class="landing-sticky-bar lg:hidden">
		<div class="flex items-center justify-between gap-3">
			<?php if ( $lead_price > 0 ) : ?>
				<span class="whitespace-nowrap text-sm font-bold text-zinc-900">
					<?php
					/* translators: %s: formatted lead price. */
					printf( esc_html__( 'From %s', 'brand-theme' ), esc_html( $currency . number_format( $lead_price, 0 ) ) );
					?>
				</span>
			<?php endif; ?>
			<a href="#order-section" class="offer-cta offer-cta--block !px-4 !py-3 flex-1 text-sm">
				<?php esc_html_e( 'GET MINE', 'brand-theme' ); ?>
			</a>
		</div>
	</div>

</main>
