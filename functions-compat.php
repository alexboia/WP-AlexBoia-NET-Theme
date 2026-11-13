<?php
/**
 * Temporary compatibility shims
 *
 * Each shim works around a known issue in a plug-in until a fixed version is released,
 * and disables itself for the fixed versions. Remove a shim once the fix is deployed.
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * WP Trip Summary up to 0.3.3 removes the core wpautop filter from the_content,
 * but applies it back only on single posts and pages that are not password protected.
 * Elsewhere (front page, archives, search) line breaks and paragraphs are lost.
 * Fixed in WP Trip Summary 0.3.4.
 *
 * Applying wpautop on content that already went through it only changes whitespace,
 * so the shim is harmless even if it overlaps with a fixed plug-in version.
 *
 * @param string|null $content The post content.
 * @return string|null
 */
function abnet_compat_wpts_wpautop(?string $content): ?string {
	if ($content === null || !_abnet_compat_wpts_wpautop_needed()) {
		return $content;
	}

	$postId = (int)get_the_ID();
	$pluginAppliedWpAutoP = $postId > 0
		&& (is_single() || is_page())
		&& !post_password_required($postId);

	return $pluginAppliedWpAutoP ? $content : wpautop($content);
}

function _abnet_compat_wpts_wpautop_needed(): bool {
	return defined('ABP01_VERSION')
		&& version_compare(ABP01_VERSION, '0.3.4', '<')
		&& has_filter('the_content', 'wpautop') === false;
}

// Right after the plug-in content filter (priority 0), before shortcodes
add_filter('the_content', 'abnet_compat_wpts_wpautop', 1);
