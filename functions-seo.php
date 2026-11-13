<?php
/**
 * SEO title and description overrides
 *
 * Adds an editor metabox where a post's SEO title and SEO description can be set,
 * and the same fields to the category add and edit screens.
 * When present, they replace the post title or category name and the computed description
 * in the document title, the meta description, Open Graph tags and schema.org data.
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 */

use AbNetTheme\Model\ThemeHookContext;
use AbNetTheme\ViewModel\SeoMetaboxViewModel;
use AbNetTheme\ViewModel\SeoTermFieldsViewModel;

if (!defined('ABSPATH')) {
	exit;
}

const ABNET_SEO_TITLE_META_KEY = '_abnet_seo_title';
const ABNET_SEO_DESCRIPTION_META_KEY = '_abnet_seo_description';
const ABNET_SEO_USE_IN_DOCUMENT_TITLE_META_KEY = '_abnet_seo_use_in_document_title';
const ABNET_SEO_NONCE_ACTION = 'abnet_save_seo_metabox';
const ABNET_SEO_NONCE_NAME = 'abnet_seo_metabox_nonce';

/**
 * Get the SEO title of a post
 *
 * @since WP AbNet 1.0
 *
 * @param int $postId The post identifier.
 * @return string The SEO title, or an empty string if not set.
 */
function abnet_get_post_seo_title(int $postId): string {
	return trim((string)get_post_meta($postId, ABNET_SEO_TITLE_META_KEY, true));
}

/**
 * Get the SEO description of a post
 *
 * @since WP AbNet 1.0
 *
 * @param int $postId The post identifier.
 * @return string The SEO description, or an empty string if not set.
 */
function abnet_get_post_seo_description(int $postId): string {
	return trim((string)get_post_meta($postId, ABNET_SEO_DESCRIPTION_META_KEY, true));
}

/**
 * Check whether the SEO title of a post should also be used as the document title (<title>).
 * Enabled by default; only an explicit opt-out is stored.
 *
 * @since WP AbNet 1.0
 *
 * @param int $postId The post identifier.
 * @return bool
 */
function abnet_post_uses_seo_title_in_document_title(int $postId): bool {
	return get_post_meta($postId, ABNET_SEO_USE_IN_DOCUMENT_TITLE_META_KEY, true) !== '0';
}

function _abnet_seo_post_types(): array {
	return array('post', 'page');
}

/*
 * Editor metabox
 */

function abnet_register_seo_metabox(): void {
	add_meta_box('abnet-seo-metabox',
		__('SEO', 'abnet'),
		'abnet_render_seo_metabox',
		_abnet_seo_post_types(),
		'side',
		'default');
}

add_action('add_meta_boxes',
	'abnet_register_seo_metabox');

function abnet_render_seo_metabox(WP_Post $post): void {
	$viewModel = new SeoMetaboxViewModel(
		abnet_get_post_seo_title($post->ID),
		abnet_get_post_seo_description($post->ID),
		abnet_post_uses_seo_title_in_document_title($post->ID),
		ABNET_SEO_NONCE_ACTION,
		ABNET_SEO_NONCE_NAME
	);

	abnet_render_view('seo-metabox', $viewModel);
}

function abnet_save_seo_metabox(int $postId, WP_Post $post): void {
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	if (wp_is_post_revision($postId) 
		|| !in_array($post->post_type, _abnet_seo_post_types(), true)) {
		return;
	}

	$nonce = isset($_POST[ABNET_SEO_NONCE_NAME])
		? sanitize_text_field(wp_unslash($_POST[ABNET_SEO_NONCE_NAME]))
		: '';

	if (!wp_verify_nonce($nonce, ABNET_SEO_NONCE_ACTION) 
		|| !current_user_can('edit_post', $postId)) {
		return;
	}

	_abnet_save_seo_fields('post', $postId);
}

add_action('save_post',
	'abnet_save_seo_metabox',
	10,
	2);

/**
 * Save the submitted SEO fields as metadata of a post or a term
 *
 * @param string $metaType The metadata type: 'post' or 'term'.
 * @param int    $objectId The post or term identifier.
 */
function _abnet_save_seo_fields(string $metaType, int $objectId): void {
	_abnet_update_seo_meta($metaType, 
		$objectId, 
		ABNET_SEO_TITLE_META_KEY, 
		'abnet_seo_title');

	_abnet_update_seo_meta($metaType, 
		$objectId, 
		ABNET_SEO_DESCRIPTION_META_KEY, 
		'abnet_seo_description');

	if (!empty($_POST['abnet_seo_use_in_document_title'])) {
		delete_metadata($metaType, 
			$objectId, 
			ABNET_SEO_USE_IN_DOCUMENT_TITLE_META_KEY);
	} else {
		update_metadata($metaType, 
			$objectId, 
			ABNET_SEO_USE_IN_DOCUMENT_TITLE_META_KEY, 
			'0');
	}
}

function _abnet_update_seo_meta(string $metaType, int $objectId, string $metaKey, string $fieldName): void {
	$value = isset($_POST[$fieldName])
		? trim(sanitize_text_field(wp_unslash($_POST[$fieldName])))
		: '';

	if ($value !== '') {
		update_metadata($metaType, 
			$objectId, 
			$metaKey, 
			$value);
	} else {
		delete_metadata($metaType, 
			$objectId, 
			$metaKey);
	}
}

/*
 * Term edit screen
 */

/**
 * Get the SEO title of a term
 *
 * @since WP AbNet 1.0
 *
 * @param int $termId The term identifier.
 * @return string The SEO title, or an empty string if not set.
 */
function abnet_get_term_seo_title(int $termId): string {
	return trim((string)get_term_meta($termId, ABNET_SEO_TITLE_META_KEY, true));
}

/**
 * Get the SEO description of a term
 *
 * @since WP AbNet 1.0
 *
 * @param int $termId The term identifier.
 * @return string The SEO description, or an empty string if not set.
 */
function abnet_get_term_seo_description(int $termId): string {
	return trim((string)get_term_meta($termId, ABNET_SEO_DESCRIPTION_META_KEY, true));
}

/**
 * Check whether the SEO title of a term should also be used as the document title (<title>).
 * Enabled by default; only an explicit opt-out is stored.
 *
 * @since WP AbNet 1.0
 *
 * @param int $termId The term identifier.
 * @return bool
 */
function abnet_term_uses_seo_title_in_document_title(int $termId): bool {
	return get_term_meta($termId, ABNET_SEO_USE_IN_DOCUMENT_TITLE_META_KEY, true) !== '0';
}

/**
 * Taxonomies whose terms get SEO fields.
 * Tags are left out, because their archives are not indexed (see abnet_seo_wp_robots()).
 */
function _abnet_seo_taxonomies(): array {
	return array('category');
}

function abnet_register_seo_term_fields(): void {
	foreach (_abnet_seo_taxonomies() as $taxonomy) {
		add_action($taxonomy . '_add_form_fields',
			'abnet_render_seo_new_term_fields');

		add_action($taxonomy . '_edit_form_fields',
			'abnet_render_seo_term_fields');

		add_action('created_' . $taxonomy,
			'abnet_save_seo_term_fields');

		add_action('edited_' . $taxonomy,
			'abnet_save_seo_term_fields');
	}
}

add_action('admin_init',
	'abnet_register_seo_term_fields');

/**
 * Render the SEO fields of the add term form, next to the term list.
 * The SEO title is used as the document title by default, as for existing terms.
 */
function abnet_render_seo_new_term_fields(): void {
	$viewModel = new SeoTermFieldsViewModel(
		'',
		'',
		true,
		ABNET_SEO_NONCE_ACTION,
		ABNET_SEO_NONCE_NAME,
		isNewTerm: true
	);

	abnet_render_view('seo-term-fields', $viewModel);
}

function abnet_render_seo_term_fields(WP_Term $term): void {
	$viewModel = new SeoTermFieldsViewModel(
		abnet_get_term_seo_title($term->term_id),
		abnet_get_term_seo_description($term->term_id),
		abnet_term_uses_seo_title_in_document_title($term->term_id),
		ABNET_SEO_NONCE_ACTION,
		ABNET_SEO_NONCE_NAME
	);

	abnet_render_view('seo-term-fields', $viewModel);
}

/**
 * Save the SEO fields of a term, when it is created from the add form or updated from the edit screen.
 * The created_{$taxonomy} and edited_{$taxonomy} actions also fire for terms created or changed elsewhere
 * (quick edit, the category box of the post editor), which do not submit these fields;
 * the nonce check leaves the stored values alone in that case.
 *
 * @param int $termId The term identifier.
 */
function abnet_save_seo_term_fields(int $termId): void {
	$nonce = isset($_POST[ABNET_SEO_NONCE_NAME])
		? sanitize_text_field(wp_unslash($_POST[ABNET_SEO_NONCE_NAME]))
		: '';

	if (!wp_verify_nonce($nonce, ABNET_SEO_NONCE_ACTION) 
		|| !current_user_can('edit_term', $termId)) {
		return;
	}

	_abnet_save_seo_fields('term', $termId);
}

/*
 * Overrides
 */

function _abnet_seo_current_post(): ?WP_Post {
	if (!is_singular(_abnet_seo_post_types())) {
		return null;
	}

	$post = get_queried_object();
	return $post instanceof WP_Post ? $post : null;
}

/**
 * Use the SEO title in the document title
 *
 * @param array $titleParts The title parts, before the site name is appended by abnet_wp_title().
 * @return array
 */
function abnet_seo_wp_title_parts(array $titleParts): array {
	$seoTitle = '';

	$post = _abnet_seo_current_post();
	if ($post && abnet_post_uses_seo_title_in_document_title($post->ID)) {
		$seoTitle = abnet_get_post_seo_title($post->ID);
	}

	$term = _abnet_seo_current_term();
	if ($term && abnet_term_uses_seo_title_in_document_title($term->term_id)) {
		$seoTitle = abnet_get_term_seo_title($term->term_id);
	}

	if ($seoTitle !== '') {
		$titleParts = array($seoTitle);
	}

	return $titleParts;
}

add_filter('wp_title_parts',
	'abnet_seo_wp_title_parts');

function _abnet_seo_current_term(): ?WP_Term {
	$taxonomies = _abnet_seo_taxonomies();
	if (!is_category() && !is_tax($taxonomies)) {
		return null;
	}

	$term = get_queried_object();
	return $term instanceof WP_Term 
		&& in_array($term->taxonomy, $taxonomies, true) ?
			$term 
			: null;
}

function abnet_seo_og_archive_title(string $title, ThemeHookContext $context): string {
	$term = _abnet_seo_current_term();
	$seoTitle = $term ? abnet_get_term_seo_title($term->term_id) : '';
	return $seoTitle !== '' ? $seoTitle : $title;
}

add_filter('abnet_og_archive_title',
	'abnet_seo_og_archive_title',
	10,
	2);

function abnet_seo_og_archive_description(string $description, ThemeHookContext $context): string {
	$term = _abnet_seo_current_term();
	$seoDescription = $term ? abnet_get_term_seo_description($term->term_id) : '';
	return $seoDescription !== '' ? $seoDescription : $description;
}

add_filter('abnet_og_archive_description',
	'abnet_seo_og_archive_description',
	10,
	2);

function abnet_seo_schema_org_archive_web_page(array $properties, ThemeHookContext $context): array {
	$term = _abnet_seo_current_term();
	if (!$term) {
		return $properties;
	}

	$seoTitle = abnet_get_term_seo_title($term->term_id);
	if ($seoTitle !== '') {
		$properties['name'] = $seoTitle;
	}

	$seoDescription = abnet_get_term_seo_description($term->term_id);
	if ($seoDescription !== '') {
		$properties['description'] = $seoDescription;
	}

	return $properties;
}

add_filter('wp_abnet_schema_org_properties_archive_web_page',
	'abnet_seo_schema_org_archive_web_page',
	10,
	2);

function abnet_seo_og_post_title(string $title, ThemeHookContext $context, WP_Post $post): string {
	$seoTitle = abnet_get_post_seo_title($post->ID);
	return $seoTitle !== '' ? $seoTitle : $title;
}

add_filter('abnet_og_post_title',
	'abnet_seo_og_post_title',
	10,
	3);

function abnet_seo_og_post_description(string $description, ThemeHookContext $context, WP_Post $post): string {
	$seoDescription = abnet_get_post_seo_description($post->ID);
	return $seoDescription !== '' ? $seoDescription : $description;
}

add_filter('abnet_og_post_description',
	'abnet_seo_og_post_description',
	10,
	3);

function abnet_seo_schema_org_blog_posting(array $properties, WP_Post $post): array {
	$seoTitle = abnet_get_post_seo_title($post->ID);
	if ($seoTitle !== '') {
		$properties['headline'] = $seoTitle;
	}

	$seoDescription = abnet_get_post_seo_description($post->ID);
	if ($seoDescription !== '') {
		$properties['description'] = $seoDescription;
	}

	return $properties;
}

add_filter('wp_abnet_schema_org_properties_blog_posting',
	'abnet_seo_schema_org_blog_posting',
	10,
	2);

/**
 * Redirect attachment pages to the post they belong to, so they do not
 * end up indexed as thin pages. Attachments without a published parent
 * are redirected to the file itself.
 */
function abnet_seo_redirect_attachment_page(): void {
	if (!is_attachment()) {
		return;
	}

	$attachmentId = get_queried_object_id();
	$parentId = wp_get_post_parent_id($attachmentId);

	if ($parentId && get_post_status($parentId) === 'publish') {
		$target = get_permalink($parentId);
	} else {
		$target = wp_get_attachment_url($attachmentId);
	}

	if (empty($target)) {
		return;
	}

	wp_safe_redirect($target, 301);
	exit;
}

add_action('template_redirect',
	'abnet_seo_redirect_attachment_page',
	1);

/**
 * Keep thin archive pages (tags, date archives, author archives) out of
 * the search index, while still letting crawlers follow their links.
 *
 * @param array $robots The robots directives.
 * @return array
 */
function abnet_seo_wp_robots(array $robots): array {
	if (is_tag() || is_date() || is_author()) {
		$robots['noindex'] = true;
		$robots['follow'] = true;
	}

	return $robots;
}

add_filter('wp_robots',
	'abnet_seo_wp_robots');
