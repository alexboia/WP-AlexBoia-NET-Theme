<?php
/**
 * Open Graph meta tags generator
 *
 * Generates Open Graph meta tags for posts, pages, and archives.
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 */

namespace AbNetTheme;

use AbNetTheme\Model\MetaTag;
use AbNetTheme\Model\ThemeHookContext;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class OGMetaTags {
	/**
	 * Theme hook context
	 *
	 * @var ThemeHookContext
	 */
	private $_context;

	/**
	 * Array of meta tags
	 *
	 * @var MetaTag[]
	 */
	private array $_tags = array();

	/**
	 * Constructor
	 *
	 * @param ThemeHookContext $context The theme hook context.
	 */
	public function __construct( ThemeHookContext $context ) {
		$this->_context = $context;
		$this->_generateTags();
	}

	/**
	 * Generate OG meta tags based on current page
	 */
	private function _generateTags(): void {
		$this->_addDefaultTags();

		if ( $this->_context->isSingle() || $this->_context->isPage() ) {
			$this->_addPostTags();
		} elseif ( $this->_context->isArchive() ) {
			$this->_addArchiveTags();
		} else {
			$this->_addHomeTags();
		}
	}

	/**
	 * Add default OG tags
	 */
	private function _addDefaultTags(): void {
		$settings = $this->_context->getBlogSettings();

		$this->_tags[] = new MetaTag(
			'og:site_name', 
			$settings['blog_name'], 
			'og:site_name'
		);
		$this->_tags[] = new MetaTag(
			'og:type', 
			'website', 
			'og:type'
		);
		$this->_tags[] = new MetaTag(
			'og:locale', 
			$this->_determineOGLocale( $settings['blog_language'] ), 
			'og:locale'
		);
	}

	/**
	 * Add post-specific OG tags
	 */
	private function _addPostTags(): void {
		$post = $this->_context->currentPost();

		if ( ! $post ) {
			return;
		}

		$settings = $this->_context->getBlogSettings();

		// Basic post information
		$this->_tags[] = new MetaTag(
			'og:title', 
			$post->post_title, 
			'og:title'
		);
		$this->_tags[] = new MetaTag(
			'og:type', 
			'article', 
			'og:type'
		);
		$this->_tags[] = new MetaTag(
			'og:url', 
			get_permalink($post), 
			'og:url'
		);

		// Post description
		$description = $this->_computePostDescription($post);
		if (!empty($description)) {
			$this->_tags[] = new MetaTag(
				'og:description', 
				$description, 
				'og:description'
			);
		}

		// Featured image
		$imageUrl = $this->_getPostImageUrl($post);
		if ($imageUrl) {
			$this->_tags[] = new MetaTag(
				'og:image', 
				$imageUrl, 
				'og:image'
			);
		}

		// Publish and modified dates
		$this->_tags[] = new MetaTag(
			'og:published_time',
			mysql2date(\DateTime::ATOM, $post->post_date_gmt, false),
			'og:published_time'
		);

		if ( $post->post_modified_gmt !== $post->post_date_gmt ) {
			$this->_tags[] = new MetaTag(
				'og:modified_time',
				mysql2date(\DateTime::ATOM, $post->post_modified_gmt, false),
				'og:modified_time'
			);
		}

		// Author
		$authorName = get_the_author_meta('display_name', $post->post_author);
		if (!empty($authorName)) {
			$this->_tags[] = new MetaTag('author', $authorName, 'author');
		}
	}

	/**
	 * Add archive-specific OG tags
	 */
	private function _addArchiveTags(): void {
		$settings = $this->_context->getBlogSettings();
		$archive_title = $this->_context->getArchiveTitle();

		if ( $archive_title ) {
			$this->_tags[] = new MetaTag(
				'og:title', 
				$archive_title, 
				'og:title'
			);
		} else {
			$this->_tags[] = new MetaTag(
				'og:title', 
				$settings['blog_name'], 
				'og:title'
			);
		}

		$this->_tags[] = new MetaTag(
			'og:url', 
			get_the_archive_title(), 
			'og:url'
		);

		$this->_addBlogDescriptionTag();
	}

	/**
	 * Add home/front page OG tags
	 */
	private function _addHomeTags(): void {
		$settings = $this->_context->getBlogSettings();

		$this->_tags[] = new MetaTag( 
			'og:title', 
			$settings['blog_name'], 
			'og:title' 
		);

		$this->_tags[] = new MetaTag(
			'og:url', 
			$settings['blog_url'], 
			'og:url'
		);

		$this->_addBlogDescriptionTag();
	}

	private function _addBlogDescriptionTag(): void {
		$blogDescription = $this->_getBlogDescription();
		if (!empty($blogDescription)) {
			$this->_tags[] = new MetaTag(
				'og:description', 
				$blogDescription, 
				'og:description'
			);
		}
	}

	private function _getBlogDescription(): string {
		$description = '';
		$settings = $this->_context->getBlogSettings();
		if (!empty($settings['blog_description'])) {
			$description = $settings['blog_description'];
		}

		return apply_filters('abnet_og_blog_description', $description, $this->_context);
	}

	/**
	 * Get post description from excerpt or content
	 *
	 * @param \WP_Post $post The post object.
	 */
	private function _computePostDescription( \WP_Post $post ): string {
		if (!empty( $post->post_excerpt)) {
			return wp_strip_all_tags($post->post_excerpt);
		}

		if (!empty( $post->post_content)) {
			return wp_strip_all_tags(
				wp_trim_words($post->post_content, 30)
			);
		}

		return '';
	}

	/**
	 * Get post image URL
	 *
	 * Returns featured image URL if set, otherwise returns the first attached image.
	 *
	 * @param \WP_Post $post The post object.
	 * @return string|false Image URL or false if none found.
	 */
	private function _getPostImageUrl( \WP_Post $post ): string|false {
		// Try to get featured image first
		$imageId = get_post_thumbnail_id($post);
		if ($imageId) {
			return wp_get_attachment_url($imageId);
		}

		// Fall back to first attached image
		$attachments = get_children(array(
			'post_parent' => $post->ID,
			'post_type'   => 'attachment',
			'post_mime_type' => 'image',
			'orderby' => 'menu_order ID',
			'order' => 'ASC',
			'numberposts' => 1
		));

		if (!empty($attachments)) {
			$attachment = reset($attachments);
			return wp_get_attachment_url($attachment->ID);
		}

		return false;
	}

	/**
	 * Convert WordPress language code to OG locale format
	 *
	 * @param string $wpLang WordPress language code.
	 */
	private function _determineOGLocale(string $wpLang) {
		$map = array(
			'en' => 'en_US',
			'ro' => 'ro_RO',
			'fr' => 'fr_FR',
			'de' => 'de_DE',
			'es' => 'es_ES',
			'it' => 'it_IT'
		);

		$langCode = strtolower(substr($wpLang, 0, 2));

		return isset($map[$langCode]) 
			? $map[$langCode] 
			: 'en_US';
	}

	public function getTags(): array {
		return $this->_tags;
	}

	public function clearTags(): void {
		$this->_tags = array();
	}

	public function addTags(array $tags): void {
		foreach ($tags as $tag) {
			if ($tag instanceof MetaTag) {
				$this->_tags[] = $tag;
			}
		}
	}

	/**
	 * Get HTML representation of all meta tags
	 */
	public function render(): string {
		$html = '';

		foreach ( $this->_tags as $tag ) {
			$html .= $tag->render() . "\n";
		}

		return $html;
	}
}
