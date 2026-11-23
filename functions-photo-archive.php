<?php
/**
 * Photo archives
 *
 * A photo archive is a page using the Photo Archive template (page-templates/photo-archive.php)
 * whose chapters are the posts of one category, each carrying a gallery.
 * The landing page shows a hero, the page content and the chapter cards;
 * the chapter posts get a banner linking back to it and a chapter navigation.
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 */

use AbNetTheme\Model\PhotoArchiveChapter;
use AbNetTheme\ViewModel\PhotoArchiveChapterContextViewModel;
use AbNetTheme\ViewModel\PhotoArchiveChapterHeroViewModel;
use AbNetTheme\ViewModel\PhotoArchiveMetaboxViewModel;
use AbNetTheme\ViewModel\PhotoArchivePageViewModel;

if (!defined('ABSPATH')) {
	exit;
}

const ABNET_PHOTO_ARCHIVE_TEMPLATE = 'page-templates/photo-archive.php';
const ABNET_PHOTO_ARCHIVE_CATEGORY_META_KEY = '_abnet_photo_archive_category';
const ABNET_PHOTO_ARCHIVE_SUBTITLE_META_KEY = '_abnet_photo_archive_subtitle';
const ABNET_PHOTO_ARCHIVE_PERIOD_META_KEY = '_abnet_photo_archive_period';
const ABNET_PHOTO_ARCHIVE_SIDEBAR_META_KEY = '_abnet_photo_archive_sidebar';
const ABNET_PHOTO_ARCHIVE_NONCE_ACTION = 'abnet_save_photo_archive_metabox';
const ABNET_PHOTO_ARCHIVE_NONCE_NAME = 'abnet_photo_archive_metabox_nonce';

function abnet_is_photo_archive_page(): bool {
	return is_page_template(ABNET_PHOTO_ARCHIVE_TEMPLATE);
}

/**
 * Whether a photo archive page keeps the blog sidebar instead of spanning the full width
 *
 * @since WP AbNet 1.0
 *
 * @param int $pageId The archive page.
 * @return bool True when the "Show the blog sidebar" option is on and the sidebar has widgets.
 */
function abnet_photo_archive_has_sidebar(int $pageId): bool {
	return get_post_meta($pageId, ABNET_PHOTO_ARCHIVE_SIDEBAR_META_KEY, true) === '1'
		&& is_active_sidebar('sidebar-1');
}

function _abnet_current_photo_archive_has_sidebar(): bool {
	return abnet_is_photo_archive_page()
		&& abnet_photo_archive_has_sidebar(get_queried_object_id());
}

/**
 * Get the category whose posts are the chapters of a photo archive page
 *
 * @since WP AbNet 1.0
 *
 * @param int $pageId The archive page identifier.
 * @return int The category identifier, or 0 if none was chosen.
 */
function abnet_get_photo_archive_category(int $pageId): int {
	return (int)get_post_meta($pageId, ABNET_PHOTO_ARCHIVE_CATEGORY_META_KEY, true);
}

/**
 * Get the chapters of a photo archive page, oldest post first
 *
 * @since WP AbNet 1.0
 *
 * @param int $pageId The archive page identifier.
 * @return PhotoArchiveChapter[]
 */
function abnet_get_photo_archive_chapters(int $pageId): array {
	static $cache = array();
	if (isset($cache[$pageId])) {
		return $cache[$pageId];
	}

	$categoryId = abnet_get_photo_archive_category($pageId);
	if ($categoryId <= 0) {
		return $cache[$pageId] = array();
	}

	$posts = get_posts(array(
		'cat' => $categoryId,
		'post_type' => 'post',
		'post_status' => 'publish',
		'orderby' => 'date',
		'order' => 'ASC',
		'numberposts' => -1,
		'suppress_filters' => false
	));

	$chapters = array();
	foreach ($posts as $index => $post) {
		$chapters[] = _abnet_create_photo_archive_chapter($post, $index + 1);
	}

	return $cache[$pageId] = $chapters;
}

function _abnet_create_photo_archive_chapter(WP_Post $post, int $number): PhotoArchiveChapter {
	$thumbnailId = (int)get_post_thumbnail_id($post);
	$thumbnailUrl = $thumbnailId > 0
		? (string)wp_get_attachment_image_url($thumbnailId, 'medium_large')
		: '';

	$thumbnailAlt = $thumbnailId > 0
		? trim((string)get_post_meta($thumbnailId, '_wp_attachment_image_alt', true))
		: '';

	$excerpt = has_excerpt($post)
		? $post->post_excerpt
		: wp_trim_words(strip_shortcodes($post->post_content), 28, '…');

	return new PhotoArchiveChapter(
		$post->ID,
		$number,
		get_the_title($post),
		(string)get_permalink($post),
		trim(wp_strip_all_tags($excerpt)),
		$thumbnailUrl,
		$thumbnailAlt,
		_abnet_count_post_photos($post)
	);
}

/**
 * Count the images of a post: image blocks (also inside galleries and other blocks),
 * old-style gallery blocks that only list their image ids, and [gallery] shortcodes.
 */
function _abnet_count_post_photos(WP_Post $post): int {
	$content = $post->post_content;
	$count = 0;

	if (has_blocks($content)) {
		$count += _abnet_count_image_blocks(parse_blocks($content));
	} else {
		$count += preg_match_all('/<img\s/i', $content);
	}

	if (preg_match_all('/\[gallery[^\]]*\bids="([^"]*)"/i', $content, $matches)) {
		foreach ($matches[1] as $ids) {
			$count += count(array_filter(array_map('trim', explode(',', $ids))));
		}
	}

	return $count;
}

function _abnet_count_image_blocks(array $blocks): int {
	$count = 0;

	foreach ($blocks as $block) {
		if ($block['blockName'] === 'core/image') {
			$count++;
		} elseif ($block['blockName'] === 'core/gallery'
			&& empty($block['innerBlocks'])
			&& !empty($block['attrs']['ids'])) {
			$count += count($block['attrs']['ids']);
		}

		if (!empty($block['innerBlocks'])) {
			$count += _abnet_count_image_blocks($block['innerBlocks']);
		}
	}

	return $count;
}

/**
 * Build the view model of a photo archive landing page
 *
 * @since WP AbNet 1.0
 *
 * @param WP_Post $page The archive page.
 * @return PhotoArchivePageViewModel
 */
function abnet_get_photo_archive_page_view_model(WP_Post $page): PhotoArchivePageViewModel {
	$chapters = abnet_get_photo_archive_chapters($page->ID);

	$photoCount = 0;
	foreach ($chapters as $chapter) {
		$photoCount += $chapter->photoCount;
	}

	$heroId = (int)get_post_thumbnail_id($page);

	return new PhotoArchivePageViewModel(
		get_the_title($page),
		trim((string)get_post_meta($page->ID, ABNET_PHOTO_ARCHIVE_SUBTITLE_META_KEY, true)),
		trim((string)get_post_meta($page->ID, ABNET_PHOTO_ARCHIVE_PERIOD_META_KEY, true)),
		$heroId > 0 ? (string)wp_get_attachment_image_url($heroId, 'full') : '',
		$heroId > 0 ? trim((string)get_post_meta($heroId, '_wp_attachment_image_alt', true)) : '',
		$chapters,
		$photoCount,
		abnet_get_photo_archive_category($page->ID) > 0
	);
}

/*
 * Chapter posts
 */

/**
 * Find the photo archive a post belongs to, as a chapter
 *
 * @since WP AbNet 1.0
 *
 * @param WP_Post $post The post.
 * @return PhotoArchiveChapterContextViewModel|null The chapter context, or null if the post is not a chapter.
 */
function abnet_get_photo_archive_chapter_context(WP_Post $post): ?PhotoArchiveChapterContextViewModel {
	static $cache = array();
	if (array_key_exists($post->ID, $cache)) {
		return $cache[$post->ID];
	}

	$cache[$post->ID] = null;
	if ($post->post_type !== 'post') {
		return null;
	}

	$categoryIds = wp_get_post_categories($post->ID);
	if (empty($categoryIds)) {
		return null;
	}

	$archivePages = get_posts(array(
		'post_type' => 'page',
		'post_status' => 'publish',
		'numberposts' => 1,
		'meta_query' => array(
			'relation' => 'AND',
			array(
				'key' => '_wp_page_template',
				'value' => ABNET_PHOTO_ARCHIVE_TEMPLATE
			),
			array(
				'key' => ABNET_PHOTO_ARCHIVE_CATEGORY_META_KEY,
				'value' => array_map('strval', $categoryIds),
				'compare' => 'IN'
			)
		)
	));

	if (empty($archivePages)) {
		return null;
	}

	$archivePage = $archivePages[0];
	$chapters = abnet_get_photo_archive_chapters($archivePage->ID);

	foreach ($chapters as $index => $chapter) {
		if ($chapter->postId !== $post->ID) {
			continue;
		}

		return $cache[$post->ID] = new PhotoArchiveChapterContextViewModel(
			get_the_title($archivePage),
			(string)get_permalink($archivePage),
			$chapter,
			count($chapters),
			$chapters[$index - 1] ?? null,
			$chapters[$index + 1] ?? null
		);
	}

	return null;
}

function _abnet_current_photo_archive_chapter_context(): ?PhotoArchiveChapterContextViewModel {
	$post = get_post();
	return is_single() && $post instanceof WP_Post
		? abnet_get_photo_archive_chapter_context($post)
		: null;
}

/**
 * Output the hero of a chapter post (featured image, chapter position, archive link and title),
 * in place of the regular featured image and title of content.php.
 *
 * @since WP AbNet 1.0
 *
 * @return bool Whether the hero was output (false for posts that are not chapters).
 */
function abnet_photo_archive_chapter_hero(): bool {
	$context = _abnet_current_photo_archive_chapter_context();
	if (!$context) {
		return false;
	}

	$imageId = (int)get_post_thumbnail_id($context->current->postId);

	abnet_render_view('photo-archive-chapter-hero', new PhotoArchiveChapterHeroViewModel(
		$context,
		$imageId > 0 ? (string)wp_get_attachment_image_url($imageId, 'large') : '',
		$imageId > 0 ? trim((string)get_post_meta($imageId, '_wp_attachment_image_alt', true)) : ''
	));

	return true;
}

/**
 * Output the previous/next chapter navigation of a chapter post.
 *
 * @since WP AbNet 1.0
 *
 * @return bool Whether the navigation was output (false for posts that are not chapters).
 */
function abnet_photo_archive_chapter_nav(): bool {
	$context = _abnet_current_photo_archive_chapter_context();
	if (!$context) {
		return false;
	}

	abnet_render_view('photo-archive-chapter-nav', $context);
	return true;
}

/**
 * Get the chapter categories of all published photo archive pages
 *
 * @since WP AbNet 1.0
 *
 * @return int[] The category identifiers.
 */
function abnet_get_photo_archive_categories(): array {
	static $categoryIds = null;
	if ($categoryIds !== null) {
		return $categoryIds;
	}

	$pageIds = get_posts(array(
		'post_type' => 'page',
		'post_status' => 'publish',
		'numberposts' => -1,
		'fields' => 'ids',
		'meta_key' => '_wp_page_template',
		'meta_value' => ABNET_PHOTO_ARCHIVE_TEMPLATE
	));

	$categoryIds = array();
	foreach ($pageIds as $pageId) {
		$categoryId = abnet_get_photo_archive_category((int)$pageId);
		if ($categoryId > 0) {
			$categoryIds[] = $categoryId;
		}
	}

	return $categoryIds = array_values(array_unique($categoryIds));
}

/**
 * Whether a post is a chapter of a photo archive, that is, a post in the chapter category of an archive page
 *
 * @since WP AbNet 1.0
 *
 * @param WP_Post $post The post.
 * @return bool
 */
function abnet_is_photo_archive_chapter_post(WP_Post $post): bool {
	$categoryIds = abnet_get_photo_archive_categories();
	return $post->post_type === 'post'
		&& !empty($categoryIds)
		&& has_category($categoryIds, $post);
}

/**
 * Leave out the featured image of chapter posts in the post listings (blog index, categories, tags, search):
 * it is shown on the chapter itself, as a banner, and on the archive page cards.
 * The image remains the post's featured image for Open Graph and schema.org, which read it directly.
 *
 * @param string $html   The featured image markup.
 * @param int    $postId The post identifier.
 * @return string
 */
function abnet_photo_archive_listing_thumbnail(string $html, int $postId): string {
	if ($html === '' || is_singular() || !in_the_loop() || !is_main_query()) {
		return $html;
	}

	$post = get_post($postId);
	return $post instanceof WP_Post && abnet_is_photo_archive_chapter_post($post)
		? ''
		: $html;
}

add_filter('post_thumbnail_html',
	'abnet_photo_archive_listing_thumbnail',
	10,
	2);

/*
 * Layout and assets
 */

function abnet_photo_archive_body_class(array $classes): array {
	if (abnet_is_photo_archive_page()) {
		$classes[] = 'template-photo-archive';
		$classes[] = _abnet_current_photo_archive_has_sidebar()
			? 'photo-archive-with-sidebar'
			: 'full-width';
	} elseif (_abnet_current_photo_archive_chapter_context()) {
		$classes[] = 'photo-archive-chapter';
	}

	return $classes;
}

add_filter('body_class',
	'abnet_photo_archive_body_class',
	20);

function abnet_photo_archive_content_width(): void {
	if (abnet_is_photo_archive_page() && !_abnet_current_photo_archive_has_sidebar()) {
		global $content_width;
		$content_width = 1010;
	}
}

add_action('template_redirect',
	'abnet_photo_archive_content_width',
	20);

function abnet_photo_archive_scripts_styles(): void {
	if (!abnet_is_photo_archive_page() && !_abnet_current_photo_archive_chapter_context()) {
		return;
	}

	wp_enqueue_style('abnet-photo-archive',
		get_template_directory_uri() . '/css/photo-archive.css',
		array('abnet-style'),
		wp_get_theme()->get('Version'));
}

add_action('wp_enqueue_scripts',
	'abnet_photo_archive_scripts_styles',
	20);

/*
 * Page editor metabox
 */

function abnet_register_photo_archive_metabox(): void {
	add_meta_box('abnet-photo-archive-metabox',
		__('Photo archive', 'abnet'),
		'abnet_render_photo_archive_metabox',
		'page',
		'side',
		'default');
}

add_action('add_meta_boxes',
	'abnet_register_photo_archive_metabox');

function abnet_render_photo_archive_metabox(WP_Post $post): void {
	$viewModel = new PhotoArchiveMetaboxViewModel(
		abnet_get_photo_archive_category($post->ID),
		get_categories(array('hide_empty' => false)),
		(string)get_post_meta($post->ID, ABNET_PHOTO_ARCHIVE_SUBTITLE_META_KEY, true),
		(string)get_post_meta($post->ID, ABNET_PHOTO_ARCHIVE_PERIOD_META_KEY, true),
		get_post_meta($post->ID, ABNET_PHOTO_ARCHIVE_SIDEBAR_META_KEY, true) === '1',
		ABNET_PHOTO_ARCHIVE_NONCE_ACTION,
		ABNET_PHOTO_ARCHIVE_NONCE_NAME
	);

	abnet_render_view('photo-archive-metabox', $viewModel);
}

function abnet_save_photo_archive_metabox(int $postId, WP_Post $post): void {
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	if (wp_is_post_revision($postId) || $post->post_type !== 'page') {
		return;
	}

	$nonce = isset($_POST[ABNET_PHOTO_ARCHIVE_NONCE_NAME])
		? sanitize_text_field(wp_unslash($_POST[ABNET_PHOTO_ARCHIVE_NONCE_NAME]))
		: '';

	if (!wp_verify_nonce($nonce, ABNET_PHOTO_ARCHIVE_NONCE_ACTION)
		|| !current_user_can('edit_post', $postId)) {
		return;
	}

	$categoryId = isset($_POST['abnet_photo_archive_category'])
		? absint($_POST['abnet_photo_archive_category'])
		: 0;

	if ($categoryId > 0 && term_exists($categoryId, 'category')) {
		update_post_meta($postId, ABNET_PHOTO_ARCHIVE_CATEGORY_META_KEY, $categoryId);
	} else {
		delete_post_meta($postId, ABNET_PHOTO_ARCHIVE_CATEGORY_META_KEY);
	}

	_abnet_update_photo_archive_text_meta($postId,
		ABNET_PHOTO_ARCHIVE_SUBTITLE_META_KEY,
		'abnet_photo_archive_subtitle');

	_abnet_update_photo_archive_text_meta($postId,
		ABNET_PHOTO_ARCHIVE_PERIOD_META_KEY,
		'abnet_photo_archive_period');

	if (!empty($_POST['abnet_photo_archive_sidebar'])) {
		update_post_meta($postId, ABNET_PHOTO_ARCHIVE_SIDEBAR_META_KEY, '1');
	} else {
		delete_post_meta($postId, ABNET_PHOTO_ARCHIVE_SIDEBAR_META_KEY);
	}
}

add_action('save_post',
	'abnet_save_photo_archive_metabox',
	10,
	2);

function _abnet_update_photo_archive_text_meta(int $postId, string $metaKey, string $fieldName): void {
	$value = isset($_POST[$fieldName])
		? trim(sanitize_text_field(wp_unslash($_POST[$fieldName])))
		: '';

	if ($value !== '') {
		update_post_meta($postId, $metaKey, $value);
	} else {
		delete_post_meta($postId, $metaKey);
	}
}
