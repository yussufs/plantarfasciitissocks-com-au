<?php
/**
 * Post-purchase upsell offer — Triple Therapy Foot Massager.
 *
 * Renders the FunnelKit (wfocu_offer) offer at
 * /offer/triple-therapy-foot-massager-offer/ natively in this theme:
 * no site navigation (header-offer.php), theme footer kept. The body markup is
 * shared with the local preview template via the offer template part.
 *
 * @package BrandTheme
 */

defined( 'ABSPATH' ) || exit;

get_header( 'offer' );
get_template_part( 'template-parts/offer/triple-therapy-foot-massager' );
get_footer();
