<?php
/**
 * Theme footer.
 *
 * @package BrandTheme
 */

$footer_domain  = wp_parse_url( home_url(), PHP_URL_HOST );
$footer_email   = 'support@' . $footer_domain;

$footer_contact_page = get_page_by_path( 'contact' );
$footer_contact_url  = $footer_contact_page ? get_permalink( $footer_contact_page ) : home_url( '/contact/' );

$footer_tracking_url = 'https://auspost.com.au/mypost/track/search';

$footer_refund_page = get_page_by_path( 'refund-policy' );
$footer_refund_url  = $footer_refund_page ? get_permalink( $footer_refund_page ) : home_url( '/refund-policy/' );

$footer_terms_page = get_page_by_path( 'terms-of-service' );
$footer_terms_url  = $footer_terms_page ? get_permalink( $footer_terms_page ) : home_url( '/terms-of-service/' );
?>

<footer class="mt-auto bg-brand-600 text-white">
	<div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
		<div class="grid gap-10 md:grid-cols-3">
			<!-- Brand info -->
			<div class="md:col-span-2 space-y-4">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="inline-block text-white no-underline" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
					<?php get_template_part( 'template-parts/logo', null, array( 'class' => 'h-10 w-auto' ) ); ?>
				</a>

				<?php
				$tagline = get_bloginfo( 'description' );
				if ( $tagline ) :
				?>
					<p class="text-brand-100 italic"><?php echo esc_html( $tagline ); ?></p>
				<?php endif; ?>

				<div class="space-y-1 text-sm text-brand-100">
					<p>
						<strong class="text-white"><?php esc_html_e( 'email:', 'brand-theme' ); ?></strong>
						<a href="mailto:<?php echo esc_attr( $footer_email ); ?>" class="text-white hover:text-brand-200 no-underline font-semibold"><?php echo esc_html( $footer_email ); ?></a>
					</p>
					<p>
						<strong class="text-white"><?php esc_html_e( 'address:', 'brand-theme' ); ?></strong>
						<?php esc_html_e( 'PO BOX 2210 Sunnybank Hills, Queensland, Australia', 'brand-theme' ); ?>
					</p>
					<p>
						<strong class="text-white"><?php esc_html_e( 'open:', 'brand-theme' ); ?></strong>
						<?php esc_html_e( '9am-5pm (7 days a week)', 'brand-theme' ); ?>
					</p>
				</div>
			</div>

			<!-- Quick Links -->
			<div>
				<h3 class="text-lg font-bold text-white"><?php esc_html_e( 'Quick Links', 'brand-theme' ); ?></h3>
				<ul class="mt-4 space-y-2 text-sm">
					<li><a href="<?php echo esc_url( $footer_contact_url ); ?>" class="text-brand-100 hover:text-white no-underline"><?php esc_html_e( 'Contact Us', 'brand-theme' ); ?></a></li>
					<li><a href="<?php echo esc_url( $footer_tracking_url ); ?>" target="_blank" rel="noopener noreferrer" class="text-brand-100 hover:text-white no-underline"><?php esc_html_e( 'Track Your Order', 'brand-theme' ); ?></a></li>
					<li><a href="<?php echo esc_url( $footer_refund_url ); ?>" class="text-brand-100 hover:text-white no-underline"><?php esc_html_e( 'Refund Policy', 'brand-theme' ); ?></a></li>
					<li><a href="<?php echo esc_url( $footer_terms_url ); ?>" class="text-brand-100 hover:text-white no-underline"><?php esc_html_e( 'Terms of Service', 'brand-theme' ); ?></a></li>
				</ul>
			</div>
		</div>

		<!-- Copyright -->
		<div class="mt-10 border-t border-brand-500 pt-6 text-center text-sm text-brand-200">
			<p>
				&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?>
				<?php bloginfo( 'name' ); ?>.
				<?php esc_html_e( 'All rights reserved.', 'brand-theme' ); ?>
			</p>
		</div>
	</div>
</footer>

<button id="scroll-to-top" type="button" aria-label="<?php esc_attr_e( 'Scroll to top', 'brand-theme' ); ?>" class="fixed bottom-6 right-6 z-50 hidden rounded-full bg-brand-600 p-3 text-white shadow-lg transition-opacity hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
	<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/></svg>
</button>

<?php
// Cart drawer mount point (available on all pages).
$cart_drawer_cart_url     = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );
$cart_drawer_checkout_url = function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/checkout/' );
?>
<div id="cart-drawer" data-config='<?php echo esc_attr( wp_json_encode( array(
	'cartUrl'     => $cart_drawer_cart_url,
	'checkoutUrl' => $cart_drawer_checkout_url,
) ) ); ?>'></div>

<?php wp_footer(); ?>
</body>
</html>
