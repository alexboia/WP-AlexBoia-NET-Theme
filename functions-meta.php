<?php
if (!defined('ABSPATH')) {
	exit;
}

use AbNetTheme\Model\ThemeHookContext;
use AbNetTheme\OGMetaTags;

function _abnet_default_blog_meta_description(): string {
	return 'Jurnal de călătorii, biciclete, tehnologie și observații personale, scris cu umor, curiozitate și ceva parapon.';
}

function abnet_og_blog_description(string $blogDescription, ThemeHookContext $context): string {
	return _abnet_default_blog_meta_description();
}

add_filter('abnet_og_blog_description', 
	'abnet_og_blog_description', 
	10, 
	2
);

/**
 * Get meta tags for the current page
 *
 * Generates meta tags including Open Graph tags for the current page context.
 * Applies a filter to allow modifications before rendering.
 *
 * @since WP AbNet 1.0
 *
 * @return OGMetaTags The OG meta tags object for the current page.
 */
function abnet_get_meta_tags(): OGMetaTags {
	$context = new ThemeHookContext();
	$defaultMetatags = new OGMetaTags($context);

	/**
	 * Filters the OG meta tags object before rendering
	 *
	 * @since WP AbNet 1.0
	 *
	 * @param OGMetaTags $metaTags The OG meta tags object.
	 * @param ThemeHookContext  $context  The theme hook context.
	 */
	$metaTags = apply_filters('abnet_og_meta_tags', $defaultMetatags, $context);

	if (!($metaTags instanceof OGMetaTags)) {
		$metaTags = $defaultMetatags;
	}

	return $metaTags;
}

/**
 * Output OG meta tags to the header
 *
 * Hooked to wp_head to output the generated OG meta tags.
 *
 * @since WP AbNet 1.0
 *
 * @return void
 */
function abnet_output_og_meta_tags(): void {
	$metaTags = abnet_get_meta_tags();
	echo $metaTags->render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

add_action( 'wp_head', 'abnet_output_og_meta_tags', 1 );
