<?php
/**
 * Image lightbox
 *
 * Opens the images of a post in a PhotoSwipe lightbox (js/vendor/photoswipe, MIT licence),
 * with captions, keyboard and swipe navigation and zoom.
 * It is loaded only on single posts that contain images. The lightbox module itself
 * is small; the PhotoSwipe core is imported by the browser only when an image is opened.
 *
 * PhotoSwipe needs the size of each image before opening it. The links to images are
 * therefore given data-pswp-width and data-pswp-height attributes here, from the attachment
 * metadata. Links to attachment pages (which abnet_seo_redirect_attachment_page() redirects
 * back to the post) are pointed at the image file instead, and so are the [gallery] links.
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 */

if (!defined('ABSPATH')) {
	exit;
}

const ABNET_LIGHTBOX_MODULE_ID = 'abnet-lightbox';

/**
 * Whether the current page gets the lightbox: a single post whose content has images
 */
function _abnet_needs_lightbox(): bool {
	static $needsLightbox = null;
	if ($needsLightbox !== null) {
		return $needsLightbox;
	}

	$post = is_single() ? get_queried_object() : null;
	if (!($post instanceof WP_Post) || $post->post_type !== 'post') {
		return $needsLightbox = false;
	}

	return $needsLightbox = stripos($post->post_content, '<img') !== false
		|| stripos($post->post_content, '[gallery') !== false;
}

function abnet_lightbox_scripts_styles(): void {
	if (!_abnet_needs_lightbox()) {
		return;
	}

	$version = wp_get_theme()->get('Version');

	wp_enqueue_style('photoswipe',
		get_template_directory_uri() . '/css/vendor/photoswipe/photoswipe.css',
		array(),
		'5.4.4');

	wp_enqueue_style('abnet-lightbox',
		get_template_directory_uri() . '/css/lightbox.css',
		array('photoswipe'),
		$version);

	wp_enqueue_script_module(ABNET_LIGHTBOX_MODULE_ID,
		get_template_directory_uri() . '/js/lightbox.js',
		array(),
		$version);
}

add_action('wp_enqueue_scripts',
	'abnet_lightbox_scripts_styles');

/**
 * Translated labels of the lightbox controls, read by js/lightbox.js
 *
 * @param array $data The data passed to the script module.
 * @return array
 */
function abnet_lightbox_module_data(array $data): array {
	return array_merge($data, array(
		'closeTitle' => __('Close', 'abnet'),
		'zoomTitle' => __('Zoom', 'abnet'),
		'arrowPrevTitle' => __('Previous image', 'abnet'),
		'arrowNextTitle' => __('Next image', 'abnet'),
		'errorMsg' => __('The image could not be loaded.', 'abnet')
	));
}

add_filter('script_module_data_' . ABNET_LIGHTBOX_MODULE_ID,
	'abnet_lightbox_module_data');

/**
 * Make [gallery] link its images to the image files, which the lightbox opens,
 * instead of their attachment pages. A gallery set to link="none" is left alone.
 *
 * @param array $out The gallery attributes, merged with the defaults.
 * @return array
 */
function abnet_lightbox_gallery_atts(array $out): array {
	if (_abnet_needs_lightbox() && ($out['link'] ?? '') !== 'none') {
		$out['link'] = 'file';
	}

	return $out;
}

add_filter('shortcode_atts_gallery',
	'abnet_lightbox_gallery_atts');

/**
 * Add the image size to the [gallery] links to image files
 *
 * @param string     $html      The link markup.
 * @param int|string $postId    The attachment identifier.
 * @param mixed      $size      The size of the image shown in the link.
 * @param bool       $permalink Whether the link goes to the attachment page.
 * @return string
 */
function abnet_lightbox_attachment_link(string $html, int|string $postId, mixed $size, bool $permalink): string {
	if ($permalink || !_abnet_needs_lightbox()) {
		return $html;
	}

	$processor = new WP_HTML_Tag_Processor($html);
	if (!$processor->next_tag('a')) {
		return $html;
	}

	$target = _abnet_lightbox_target((int)$postId, (string)$processor->get_attribute('href'));
	if ($target === null) {
		return $html;
	}

	_abnet_lightbox_set_link_attributes($processor, $target);
	return $processor->get_updated_html();
}

add_filter('wp_get_attachment_link',
	'abnet_lightbox_attachment_link',
	10,
	4);

/**
 * Prepare the links around the images of the post content for the lightbox:
 * a link wrapping an image of the media library (class wp-image-ID, or rel wp-att-ID)
 * gets the size of the image it points to, and a link to the attachment page
 * is pointed at the full image file.
 * Runs after do_shortcode, so captions are already rendered;
 * [gallery] links are handled by abnet_lightbox_attachment_link().
 *
 * @param string $content The post content.
 * @return string
 */
function abnet_lightbox_content(string $content): string {
	if (!_abnet_needs_lightbox() || !in_the_loop() || !is_main_query()) {
		return $content;
	}

	$processor = new WP_HTML_Tag_Processor($content);
	$bookmark = 'abnet-lightbox-link';

	while ($processor->next_tag('a')) {
		if ($processor->get_attribute('data-pswp-width') !== null) {
			continue;
		}

		$href = (string)$processor->get_attribute('href');
		$rel = (string)$processor->get_attribute('rel');

		$processor->set_bookmark($bookmark);
		$attachmentId = 0;

		if ($processor->next_tag() && $processor->get_tag() === 'IMG') {
			$class = (string)$processor->get_attribute('class');
			if (preg_match('/\bwp-image-(\d+)\b/', $class, $matches)) {
				$attachmentId = (int)$matches[1];
			}
		}

		if ($attachmentId === 0 && preg_match('/\bwp-att-(\d+)\b/', $rel, $matches)) {
			$attachmentId = (int)$matches[1];
		}

		$processor->seek($bookmark);

		$target = $attachmentId > 0
			? _abnet_lightbox_target($attachmentId, $href)
			: null;

		if ($target !== null) {
			_abnet_lightbox_set_link_attributes($processor, $target);
		}
	}

	$processor->release_bookmark($bookmark);
	return $processor->get_updated_html();
}

add_filter('the_content',
	'abnet_lightbox_content',
	20);

/**
 * Find the image a link should open in the lightbox, with its size
 *
 * @param int    $attachmentId The attachment identifier.
 * @param string $href         The link URL.
 * @return array|null array('url' => string, 'width' => int, 'height' => int),
 *                    or null when the link does not point to this image or its attachment page.
 */
function _abnet_lightbox_target(int $attachmentId, string $href): ?array {
	if (!wp_attachment_is_image($attachmentId)) {
		return null;
	}

	$metadata = wp_get_attachment_metadata($attachmentId);
	if (empty($metadata['file']) || empty($metadata['width']) || empty($metadata['height'])) {
		return null;
	}

	$fullUrl = (string)wp_get_attachment_image_url($attachmentId, 'full');
	$full = array(
		'url' => $fullUrl,
		'width' => (int)$metadata['width'],
		'height' => (int)$metadata['height']
	);

	$path = (string)wp_parse_url($href, PHP_URL_PATH);
	$isImageFile = preg_match('/\.(jpe?g|png|gif|webp|avif)$/i', $path) === 1;

	if (!$isImageFile) {
		// A link to the attachment page: open the full image instead.
		$attachmentPage = (string)get_attachment_link($attachmentId);
		$pointsToAttachment = $href !== ''
			&& (untrailingslashit($href) === untrailingslashit($attachmentPage)
				|| str_contains($href, 'attachment_id=' . $attachmentId));

		return $pointsToAttachment ? $full : null;
	}

	$fileName = wp_basename($path);
	if ($fileName === wp_basename($metadata['file'])) {
		return $full;
	}

	foreach ($metadata['sizes'] ?? array() as $sizeData) {
		if (($sizeData['file'] ?? '') === $fileName
			&& !empty($sizeData['width'])
			&& !empty($sizeData['height'])) {
			return array(
				'url' => $href,
				'width' => (int)$sizeData['width'],
				'height' => (int)$sizeData['height']
			);
		}
	}

	return null;
}

function _abnet_lightbox_set_link_attributes(WP_HTML_Tag_Processor $processor, array $target): void {
	$processor->set_attribute('href', $target['url']);
	$processor->set_attribute('data-pswp-width', (string)$target['width']);
	$processor->set_attribute('data-pswp-height', (string)$target['height']);
}
