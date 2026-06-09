<?php
/**
 * Minimal header for standalone landing / funnel offer pages.
 *
 * Loads the theme's styles/scripts via wp_head() but renders NO site
 * navigation — used by the offer page template. Pair with get_footer().
 *
 * @package BrandTheme
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'bg-white text-gray-900 antialiased' ); ?>>
<?php wp_body_open(); ?>
