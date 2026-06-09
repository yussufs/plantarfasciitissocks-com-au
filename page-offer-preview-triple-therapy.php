<?php
/**
 * Template Name: Offer Preview — Triple Therapy
 *
 * Local-preview wrapper for the Triple Therapy Foot Massager offer. Lets you
 * view the offer layout on a normal Page (no FunnelKit required) — assign this
 * template to a draft Page. The live funnel URL uses
 * single-wfocu_offer-triple-therapy-foot-massager-offer.php, which renders the
 * same shared body part. Same no-nav header + theme footer.
 *
 * @package BrandTheme
 */

defined( 'ABSPATH' ) || exit;

get_header( 'offer' );
get_template_part( 'template-parts/offer/triple-therapy-foot-massager' );
get_footer();
