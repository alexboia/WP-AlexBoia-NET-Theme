<?php
/**
 * A chapter of a photo archive: one post with a gallery, listed on the archive landing page
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 */

namespace AbNetTheme\Model;

if (!defined('ABSPATH')) {
	exit;
}

final readonly class PhotoArchiveChapter {
	/**
	 * @param int    $postId       The post identifier.
	 * @param int    $number       The 1-based position of the chapter in the archive.
	 * @param string $title        The post title.
	 * @param string $permalink    The post URL.
	 * @param string $excerpt      A short summary, without markup.
	 * @param string $thumbnailUrl The URL of the featured image, empty when the post has none.
	 * @param string $thumbnailAlt The alternative text of the featured image.
	 * @param int    $photoCount   The number of images in the post content.
	 */
	public function __construct(
		public int $postId,
		public int $number,
		public string $title,
		public string $permalink,
		public string $excerpt,
		public string $thumbnailUrl,
		public string $thumbnailAlt,
		public int $photoCount
	){
		return;
	}

	/**
	 * The chapter number, padded to two digits ("01", "02", ...)
	 */
	public function paddedNumber(): string {
		return str_pad((string)$this->number, 2, '0', STR_PAD_LEFT);
	}
}
