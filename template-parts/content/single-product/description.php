<?php
/**
 * Single product — full (long) description.
 *
 * Renders the product's main content (the WooCommerce long description).
 * Must be called inside the product loop (after the_post()).
 *
 * @package BrandTheme
 */

defined( 'ABSPATH' ) || exit;

// Skip the section entirely if the product has no description.
if ( ! trim( (string) get_the_content() ) ) {
	return;
}
?>

<section class="mt-12 lg:mt-16">
	<div class="prose prose-zinc max-w-none text-[0.9375rem]">
		<?php the_content(); ?>
	</div>
</section>
