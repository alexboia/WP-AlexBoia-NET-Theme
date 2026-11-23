<?php
/**
 * View model for the hero at the top of a chapter post (views/photo-archive-chapter-hero.php)
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 */

namespace AbNetTheme\ViewModel;

if (!defined('ABSPATH')) {
	exit;
}

final readonly class PhotoArchiveChapterHeroViewModel implements ViewModel {
	/**
	 * @param PhotoArchiveChapterContextViewModel $context  The chapter and the archive it belongs to.
	 * @param string                              $imageUrl The URL of the post's featured image, empty when it has none.
	 * @param string                              $imageAlt The alternative text of the featured image.
	 */
	public function __construct(
		public PhotoArchiveChapterContextViewModel $context,
		public string $imageUrl,
		public string $imageAlt
	){
		return;
	}
}
