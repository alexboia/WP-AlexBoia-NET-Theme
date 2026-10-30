<?php
/**
 * Plug-in connectors
 *
 * Small adjustments to how third-party plug-ins behave on the front end,
 * such as loading their assets only on the pages that use them.
 * Each adjustment checks that its plug-in is loaded and does nothing otherwise.
 * Larger integrations belong in the plug-ins, through the theme's hooks.
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Captcha (BestWebSoft) enqueues its stylesheets on every front-end page,
 * but on the front end it is only used by the comment form.
 * Dequeue them wherever that form is not open, together with the Dashicons
 * it enqueues for its icons. Styles that declare Dashicons as a dependency
 * (the theme on single posts, WP Trip Summary, the admin bar) still load them.
 * The login page uses login_enqueue_scripts and is not affected.
 */
function abnet_connector_captcha_styles(): void {
	if (!function_exists('cptch_front_end_styles')) {
		return;
	}

	if (is_singular() && comments_open()) {
		return;
	}

	$handles = array('cptch_stylesheet', 'cptch_desktop_style', 'cptch_mobile_style', 'dashicons');
	foreach ($handles as $handle) {
		wp_dequeue_style($handle);
	}
}

add_action('wp_enqueue_scripts',
	'abnet_connector_captcha_styles',
	20);

add_filter('tablepress_frontend_legacy_css_loading', 
	'__return_false');