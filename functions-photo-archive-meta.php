<?php
/**
 * Photo archives: Open Graph and schema.org metadata
 *
 * Builds on the generic metadata of functions-meta.php through its filters:
 * - the archive page becomes a CollectionPage listing its chapters (ItemList),
 *   with the period as temporalCoverage and the subtitle as description;
 * - a chapter post is marked as part of its archive, with its position,
 *   the photos of its galleries as associatedMedia,
 *   and the archive page in its breadcrumbs.
 * The @id of each node keeps the theme convention (<permalink>#article).
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 */

use AbNetTheme\Model\ThemeHookContext;

if (!defined('ABSPATH')) {
	exit;
}

function _abnet_is_photo_archive_post(WP_Post $post): bool {
	return $post->post_type === 'page'
		&& get_page_template_slug($post) === ABNET_PHOTO_ARCHIVE_TEMPLATE;
}

/**
 * Get the period of an archive page in the ISO 8601 interval form schema.org expects
 *
 * @since WP AbNet 1.0
 *
 * @param string $period The period as entered, for instance "1929–1930".
 * @return string "1929/1930" for a span of years, otherwise the period as entered.
 */
function _abnet_photo_archive_temporal_coverage(string $period): string {
	if (preg_match('/^(\d{4})\s*[-–—]\s*(\d{4})$/u', $period, $matches)) {
		return $matches[1] . '/' . $matches[2];
	}

	return $period;
}

/**
 * Get the attachment IDs of the photos in a post, in the order they appear
 *
 * @since WP AbNet 1.0
 *
 * @param WP_Post $post The post.
 * @return int[] Image blocks, gallery blocks, [gallery ids] and classic editor images (wp-image-ID).
 */
function _abnet_get_post_photo_ids(WP_Post $post): array {
	$content = $post->post_content;
	$ids = array();

	if (has_blocks($content)) {
		$ids = _abnet_get_block_photo_ids(parse_blocks($content));
	} elseif (preg_match_all('/\[gallery[^\]]*\bids="([^"]*)"|\bwp-image-(\d+)\b/i', $content, $matches, PREG_SET_ORDER)) {
		foreach ($matches as $match) {
			if (!empty($match[2])) {
				$ids[] = (int)$match[2];
			} else {
				foreach (explode(',', $match[1]) as $id) {
					$ids[] = (int)trim($id);
				}
			}
		}
	}

	return array_values(array_unique(array_filter($ids, function (int $id): bool {
		return $id > 0 && wp_attachment_is_image($id);
	})));
}

function _abnet_get_block_photo_ids(array $blocks): array {
	$ids = array();

	foreach ($blocks as $block) {
		if ($block['blockName'] === 'core/image' && !empty($block['attrs']['id'])) {
			$ids[] = (int)$block['attrs']['id'];
		} elseif ($block['blockName'] === 'core/gallery'
			&& empty($block['innerBlocks'])
			&& !empty($block['attrs']['ids'])) {
			$ids = array_merge($ids, array_map('intval', $block['attrs']['ids']));
		} elseif ($block['blockName'] === 'core/shortcode'
			&& preg_match('/\[gallery[^\]]*\bids="([^"]*)"/i', $block['innerHTML'], $match)) {
			$ids = array_merge($ids, array_map('intval', explode(',', $match[1])));
		}

		if (!empty($block['innerBlocks'])) {
			$ids = array_merge($ids, _abnet_get_block_photo_ids($block['innerBlocks']));
		}
	}

	return $ids;
}

/**
 * Describe a photo as a schema.org ImageObject
 *
 * @since WP AbNet 1.0
 *
 * @param int $attachmentId The attachment.
 * @return array|null The node properties, null when the file is missing.
 */
function _abnet_photo_image_object(int $attachmentId): ?array {
	$url = wp_get_attachment_url($attachmentId);
	if (!$url) {
		return null;
	}

	$image = array(
		'@type' => 'ImageObject',
		'contentUrl' => $url
	);

	$metadata = wp_get_attachment_metadata($attachmentId);
	if (!empty($metadata['width']) && !empty($metadata['height'])) {
		$image['width'] = (int)$metadata['width'];
		$image['height'] = (int)$metadata['height'];
	}

	$caption = trim(wp_strip_all_tags((string)wp_get_attachment_caption($attachmentId)));
	if ($caption !== '') {
		$image['caption'] = $caption;
	}

	$alt = trim((string)get_post_meta($attachmentId, '_wp_attachment_image_alt', true));
	if ($alt !== '' && $alt !== $caption) {
		$image['description'] = $alt;
	}

	return $image;
}

/*
 * Descriptions: the subtitle of an archive page stands in for a missing excerpt.
 * Runs before functions-seo.php (priority 10), so an SEO description still wins.
 */

function abnet_photo_archive_og_description(string $description, ThemeHookContext $context, WP_Post $post): string {
	if (!_abnet_is_photo_archive_post($post) || trim($post->post_excerpt) !== '') {
		return $description;
	}

	$subtitle = trim((string)get_post_meta($post->ID, ABNET_PHOTO_ARCHIVE_SUBTITLE_META_KEY, true));
	return $subtitle !== '' ? $subtitle : $description;
}

add_filter('abnet_og_post_description',
	'abnet_photo_archive_og_description',
	5,
	3);

function abnet_photo_archive_schema_description(array $properties, WP_Post $post): array {
	if (!_abnet_is_photo_archive_post($post) || trim($post->post_excerpt) !== '') {
		return $properties;
	}

	$subtitle = trim((string)get_post_meta($post->ID, ABNET_PHOTO_ARCHIVE_SUBTITLE_META_KEY, true));
	if ($subtitle !== '') {
		$properties['description'] = $subtitle;
	}

	return $properties;
}

add_filter('wp_abnet_schema_org_properties_blog_posting',
	'abnet_photo_archive_schema_description',
	5,
	2);

/*
 * Archive page: a CollectionPage with the chapters as its main entity
 */

function abnet_photo_archive_schema_collection_page(array $properties, WP_Post $post): array {
	if (!_abnet_is_photo_archive_post($post)) {
		return $properties;
	}

	$permalink = get_permalink($post);
	$properties['@type'] = 'CollectionPage';
	$properties['name'] = $properties['headline'] ?? $post->post_title;
	$properties['url'] = $permalink;

	$period = trim((string)get_post_meta($post->ID, ABNET_PHOTO_ARCHIVE_PERIOD_META_KEY, true));
	if ($period !== '') {
		$properties['temporalCoverage'] = _abnet_photo_archive_temporal_coverage($period);
	}

	$chapters = abnet_get_photo_archive_chapters($post->ID);
	if (!empty($chapters)) {
		$items = array();
		foreach ($chapters as $chapter) {
			$items[] = array(
				'@type' => 'ListItem',
				'position' => $chapter->number,
				'url' => $chapter->permalink,
				'name' => $chapter->title
			);
		}

		$properties['mainEntity'] = array(
			'@type' => 'ItemList',
			'itemListOrder' => 'https://schema.org/ItemListOrderAscending',
			'numberOfItems' => count($items),
			'itemListElement' => $items
		);
	}

	return $properties;
}

add_filter('wp_abnet_schema_org_properties_blog_posting',
	'abnet_photo_archive_schema_collection_page',
	20,
	2);

/*
 * Chapter posts: part of the archive, with their photos
 */

function abnet_photo_archive_schema_chapter(array $properties, WP_Post $post): array {
	$context = abnet_get_photo_archive_chapter_context($post);
	if (!$context) {
		return $properties;
	}

	$properties['isPartOf'] = array(
		'@type' => 'CollectionPage',
		'@id' => $context->archiveUrl . '#article',
		'name' => $context->archiveTitle,
		'url' => $context->archiveUrl
	);
	$properties['position'] = $context->current->number;

	$images = array();
	foreach (_abnet_get_post_photo_ids($post) as $attachmentId) {
		$image = _abnet_photo_image_object($attachmentId);
		if ($image !== null) {
			$images[] = $image;
		}
	}

	if (!empty($images)) {
		$properties['associatedMedia'] = $images;
	}

	return $properties;
}

add_filter('wp_abnet_schema_org_properties_blog_posting',
	'abnet_photo_archive_schema_chapter',
	20,
	2);

/**
 * Put the archive page between the home page and a chapter post in its breadcrumbs
 */
function abnet_photo_archive_schema_breadcrumbs(array $properties, array $breadcrumbs, ThemeHookContext $context): array {
	$post = $context->currentPost();
	if (!$post || !$context->isSingle() || empty($properties['itemListElement'])) {
		return $properties;
	}

	$chapterContext = abnet_get_photo_archive_chapter_context($post);
	if (!$chapterContext) {
		return $properties;
	}

	$items = array_values($properties['itemListElement']);
	array_splice($items, count($items) - 1, 0, array(array(
		'@type' => 'ListItem',
		'position' => 0,
		'name' => $chapterContext->archiveTitle,
		'item' => $chapterContext->archiveUrl
	)));

	foreach ($items as $index => $item) {
		$items[$index]['position'] = $index + 1;
	}

	$properties['itemListElement'] = $items;
	return $properties;
}

add_filter('wp_abnet_schema_org_properties_breadcrumbs',
	'abnet_photo_archive_schema_breadcrumbs',
	10,
	3);
