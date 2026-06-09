<?php
/**
 * Offer body — Triple Therapy Foot Massager.
 *
 * Shared markup used by both the live funnel offer
 * (single-wfocu_offer-triple-therapy-foot-massager-offer.php) and the local
 * preview page template (page-offer-preview-triple-therapy.php). Wrap it with
 * get_header( 'offer' ) … get_footer().
 *
 * @package BrandTheme
 */

defined( 'ABSPATH' ) || exit;

$uploads      = trailingslashit( wp_get_upload_dir()['baseurl'] );
$accept_black = home_url( '/?wfocu-accept-link=yes&key=1' );
$accept_gray  = home_url( '/?wfocu-accept-link=yes&key=2' );
$reject_link  = home_url( '/?wfocu-reject-link=yes' );

// Inline helper: looping muted autoplay video, theme-styled.
$offer_video = static function ( $file ) use ( $uploads ) {
	printf(
		'<video class="w-full rounded-2xl shadow-sm" autoplay loop muted playsinline controlslist="nodownload" src="%s"></video>',
		esc_url( $uploads . $file )
	);
};
?>

<main class="offer-page">

	<!-- Headline band -->
	<section class="bg-brand-600 px-4 py-7 text-center sm:px-6 sm:py-9">
		<h1 class="mx-auto max-w-4xl text-2xl font-extrabold leading-tight text-white sm:text-3xl lg:text-4xl">
			<?php esc_html_e( 'Experience the Ultimate Solution to Your Plantar Fasciitis Pain', 'brand-theme' ); ?>
		</h1>
	</section>

	<!-- Offer hero -->
	<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
		<div class="grid gap-8 lg:grid-cols-2 lg:items-center lg:gap-14">
			<div>
				<h2 class="text-2xl font-extrabold text-zinc-900 sm:text-3xl lg:text-4xl">
					<?php esc_html_e( 'EXCLUSIVE OFFER — Get 50% Off Our Triple Therapy Foot Massager', 'brand-theme' ); ?>
				</h2>
				<p class="mt-3 text-lg font-semibold text-zinc-700">
					<?php esc_html_e( 'Add soothing relief that works while you rest.', 'brand-theme' ); ?>
				</p>

				<div class="mt-4 flex items-center gap-2">
					<span class="flex gap-0.5">
						<?php for ( $i = 0; $i < 5; $i++ ) {
							brand_theme_icon( 'star', array( 'class' => 'h-5 w-5 text-amber-400 fill-current' ) );
						} ?>
					</span>
					<span class="font-medium text-zinc-700"><?php esc_html_e( '4.6 stars (63 reviews from 3,697 sales)', 'brand-theme' ); ?></span>
				</div>

				<div class="mt-6 space-y-4 text-zinc-700">
					<p><?php esc_html_e( 'You just gave your feet all-day support. Now give them the end-of-day reward.', 'brand-theme' ); ?></p>
					<p><?php echo wp_kses_post( __( '<strong>Heat. Massage. Compression</strong> — all in one.', 'brand-theme' ) ); ?></p>
					<p><?php echo wp_kses_post( __( 'Meet the <strong>Plantar Fasciitis Australia</strong> — <strong>Triple Therapy Foot Massager</strong>. It wraps your feet in soothing warmth, gentle massage, and light compression to ease the aches plantar fasciitis leaves behind.', 'brand-theme' ) ); ?></p>
					<p><?php echo wp_kses_post( __( 'Slip it on, <strong>press one button, relax for 15 minutes</strong>. That\'s it.', 'brand-theme' ) ); ?></p>
				</div>

				<ul class="mt-6 space-y-2.5">
					<?php
					$offer_benefits = array(
						__( 'Soothing heat for tired, cold, stiff feet', 'brand-theme' ),
						__( 'Gentle massage that works into the arch and heel', 'brand-theme' ),
						__( 'Light compression for all-over comfort', 'brand-theme' ),
						__( 'Drug-free and simple — no pills, no appointments', 'brand-theme' ),
						__( '3 adjustable intensity levels', 'brand-theme' ),
						__( 'Use it on the couch, in bed, or while you read', 'brand-theme' ),
					);
					foreach ( $offer_benefits as $benefit ) :
						?>
						<li class="flex items-start gap-2.5 text-zinc-700">
							<?php brand_theme_icon( 'check', array( 'class' => 'mt-0.5 h-5 w-5 flex-shrink-0 text-brand-600' ) ); ?>
							<span><?php echo esc_html( $benefit ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>

				<p class="mt-6 text-lg font-bold text-zinc-900">
					<?php echo wp_kses_post( __( 'We usually sell this for <span class="text-zinc-400 line-through">$98</span>. But this offer means you can pick one up for just <span class="text-brand-600">$49</span> (50% off).', 'brand-theme' ) ); ?>
				</p>

				<a href="#order-section" class="offer-cta mt-7"><?php esc_html_e( 'CHOOSE COLOUR', 'brand-theme' ); ?> &rsaquo;</a>
			</div>

			<div>
				<?php $offer_video( '2026/05/asset_1753357541368.mp4' ); ?>
			</div>
		</div>
	</section>

	<!-- Order section -->
	<section id="order-section" class="scroll-mt-6 bg-zinc-50 px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
		<div class="mx-auto grid max-w-4xl gap-8 sm:grid-cols-2">
			<!-- Black -->
			<div class="flex flex-col rounded-2xl bg-white p-6 text-center shadow-sm ring-1 ring-zinc-200">
				<h3 class="text-xl font-bold text-zinc-900"><?php esc_html_e( 'Get 50% OFF The Black Version', 'brand-theme' ); ?></h3>
				<img
					class="mx-auto mt-4 aspect-square w-full max-w-sm rounded-xl object-cover"
					src="<?php echo esc_url( $uploads . '2026/05/Triple-Therapy-Foot-Massager-Black-OG-scaled.jpg' ); ?>"
					alt="<?php esc_attr_e( 'Triple Therapy Foot Massager — Black', 'brand-theme' ); ?>"
					loading="lazy"
				>
				<a href="<?php echo esc_url( $accept_black ); ?>" class="offer-cta offer-cta--block mt-6">
					<?php esc_html_e( 'Add The Black One To My Order — $49', 'brand-theme' ); ?>
				</a>
				<a href="<?php echo esc_url( $reject_link ); ?>" class="offer-decline mt-3">
					<?php esc_html_e( 'No thanks, I do not want this exclusive offer. Please skip the offer and complete my order.', 'brand-theme' ); ?>
				</a>
			</div>
			<!-- Gray -->
			<div class="flex flex-col rounded-2xl bg-white p-6 text-center shadow-sm ring-1 ring-zinc-200">
				<h3 class="text-xl font-bold text-zinc-900"><?php esc_html_e( 'Get 50% OFF The Gray Version', 'brand-theme' ); ?></h3>
				<img
					class="mx-auto mt-4 aspect-square w-full max-w-sm rounded-xl object-cover"
					src="<?php echo esc_url( $uploads . '2026/05/Triple-Therapy-Foot-Massager-Grey-OG-scaled.jpg' ); ?>"
					alt="<?php esc_attr_e( 'Triple Therapy Foot Massager — Grey', 'brand-theme' ); ?>"
					loading="lazy"
				>
				<a href="<?php echo esc_url( $accept_gray ); ?>" class="offer-cta offer-cta--block mt-6">
					<?php esc_html_e( 'Add The Gray One To My Order — $49', 'brand-theme' ); ?>
				</a>
				<a href="<?php echo esc_url( $reject_link ); ?>" class="offer-decline mt-3">
					<?php esc_html_e( 'No thanks, I do not want this exclusive offer. Please skip the offer and complete my order.', 'brand-theme' ); ?>
				</a>
			</div>
		</div>
	</section>

	<!-- The Perfect At-Home Therapy -->
	<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
		<div class="grid gap-8 lg:grid-cols-2 lg:items-center lg:gap-14">
			<div>
				<h2 class="text-2xl font-extrabold text-zinc-900 sm:text-3xl"><?php esc_html_e( 'The Perfect At-Home Therapy', 'brand-theme' ); ?></h2>
				<div class="mt-4 space-y-4 text-zinc-700">
					<p><?php echo wp_kses_post( __( 'The Triple Therapy Foot Massager uses clinically backed methods — <strong>heat therapy, massage</strong>, and <strong>compression</strong> — to deliver safe, drug-free relief.', 'brand-theme' ) ); ?></p>
					<p><?php esc_html_e( 'There\'s no complicated setup. Just slip it on, press a button, choose from three massage intensity levels, and let the soothing therapy work while you relax.', 'brand-theme' ); ?></p>
					<p><?php esc_html_e( 'Use it while watching TV, reading, or simply napping.', 'brand-theme' ); ?></p>
				</div>
				<a href="#order-section" class="offer-cta mt-7"><?php esc_html_e( 'CHOOSE COLOUR', 'brand-theme' ); ?> &rsaquo;</a>
			</div>
			<div>
				<?php $offer_video( '2026/05/asset_1753357895174.mp4' ); ?>
			</div>
		</div>
	</section>

	<!-- 3 Simple Steps -->
	<section class="bg-zinc-50 px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
		<div class="mx-auto max-w-7xl">
			<div class="text-center">
				<h2 class="text-2xl font-extrabold text-zinc-900 sm:text-3xl"><?php esc_html_e( '3 Simple Steps For Foot Pain Relief', 'brand-theme' ); ?></h2>
				<p class="mx-auto mt-3 max-w-2xl text-zinc-700"><?php esc_html_e( 'Simply select the massage intensity and heating level and enjoy pain relief!', 'brand-theme' ); ?></p>
			</div>

			<div class="mt-10 grid gap-8 md:grid-cols-3">
				<?php
				$offer_steps = array(
					array(
						'video' => '2026/05/asset_1753358000519.mp4',
						'step'  => __( 'Step 1', 'brand-theme' ),
						'title' => __( 'Wrap It Around Your Feet', 'brand-theme' ),
						'text'  => __( 'Slide your feet into the massager and fasten the adjustable straps for a comfortable fit.', 'brand-theme' ),
					),
					array(
						'video' => '2026/05/asset_1753358205540.mp4',
						'step'  => __( 'Step 2', 'brand-theme' ),
						'title' => __( 'Choose Your Settings', 'brand-theme' ),
						'text'  => __( 'Select your preferred massage intensity and heat level using the simple control panel.', 'brand-theme' ),
					),
					array(
						'video' => '2026/05/asset_1753358042473.mp4',
						'step'  => __( 'Step 3', 'brand-theme' ),
						'title' => __( 'Sit Back and Relax', 'brand-theme' ),
						'text'  => __( 'That\'s it! In just minutes, you\'ll feel soothing warmth, massaging pulses, and light compression working together to reduce discomfort.', 'brand-theme' ),
					),
				);
				foreach ( $offer_steps as $step ) :
					?>
					<div>
						<?php $offer_video( $step['video'] ); ?>
						<p class="mt-4 text-sm font-bold uppercase tracking-wide text-brand-600"><?php echo esc_html( $step['step'] ); ?></p>
						<h3 class="mt-1 text-lg font-bold text-zinc-900"><?php echo esc_html( $step['title'] ); ?></h3>
						<p class="mt-2 text-sm text-zinc-700"><?php echo esc_html( $step['text'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="mt-10 text-center">
				<a href="#order-section" class="offer-cta"><?php esc_html_e( 'CHOOSE COLOUR', 'brand-theme' ); ?> &rsaquo;</a>
			</div>
		</div>
	</section>

	<!-- Reviews -->
	<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
		<h2 class="text-center text-2xl font-extrabold text-zinc-900 sm:text-3xl"><?php esc_html_e( 'Our Reviews', 'brand-theme' ); ?></h2>
		<div class="mt-8 grid gap-6 sm:grid-cols-2">
			<?php
			$offer_reviews = array(
				array(
					'quote'  => 'I been dealing with nerve pain for a while now and honestly didn\'t think anything would help. But The Triple Therapy Foot Massager? Wow 🙌🏼 I use it every night now and my feet feel so much better. They\'re not burning like before and I actually sleep through the night now.',
					'author' => 'Amber',
				),
				array(
					'quote'  => 'I work on my feet all day and by the time I get home, they\'re dead. This massager really surprised me. That compression part feels great and the heat is just right. My wife steals it sometimes too lol.',
					'author' => 'Helen',
				),
				array(
					'quote'  => 'I got this cuz my doctor said my feet got bad blood flow and it might help. I been using it couple weeks now and I think it\'s working. My toes not as numb as before and it feels like something\'s actually moving in there now, will definitely keep using it!!',
					'author' => 'Ron',
				),
				array(
					'quote'  => 'My neuropathy was keeping me up almost every night. Burning, tingling — just awful. A friend recommended The Triple Therapy Foot Massager and I\'m so glad I tried it. It\'s gentle but effective, and after a week of regular use, I noticed a big change. I can finally rest without constant foot pain. Worth every cent.',
					'author' => 'Courtney',
				),
			);
			foreach ( $offer_reviews as $review ) :
				?>
				<figure class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-zinc-200">
					<span class="flex gap-0.5">
						<?php for ( $i = 0; $i < 5; $i++ ) {
							brand_theme_icon( 'star', array( 'class' => 'h-4 w-4 text-amber-400 fill-current' ) );
						} ?>
					</span>
					<blockquote class="mt-3 leading-relaxed text-zinc-700"><?php echo esc_html( $review['quote'] ); ?></blockquote>
					<figcaption class="mt-4 font-bold text-zinc-900"><?php echo esc_html( $review['author'] ); ?></figcaption>
				</figure>
			<?php endforeach; ?>
		</div>
	</section>

	<!-- Decline (bottom) -->
	<section class="px-4 pb-14 text-center sm:px-6 lg:px-8">
		<a href="<?php echo esc_url( $reject_link ); ?>" class="offer-decline">
			<?php esc_html_e( 'No thanks, I do not want this exclusive offer. Please skip the offer and complete my order.', 'brand-theme' ); ?>
		</a>
	</section>

</main>
