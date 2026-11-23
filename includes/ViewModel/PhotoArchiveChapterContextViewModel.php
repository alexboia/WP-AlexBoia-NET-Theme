<?php
/**
 * View model for the archive banner and chapter navigation shown on a chapter post
 * (views/photo-archive-chapter-nav.php; views/photo-archive-chapter-hero.php through PhotoArchiveChapterHeroViewModel)
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 */

namespace AbNetTheme\ViewModel;

if (!defined('ABSPATH')) {
	exit;
}

use AbNetTheme\Model\PhotoArchiveChapter;

final readonly class PhotoArchiveChapterContextViewModel implements ViewModel {
	/**
	 * @param string                   $archiveTitle The title of the archive landing page.
	 * @param string                   $archiveUrl   The URL of the archive landing page.
	 * @param PhotoArchiveChapter      $current      The chapter being viewed.
	 * @param int                      $total        The number of chapters in the archive.
	 * @param PhotoArchiveChapter|null $previous     The previous chapter, if any.
	 * @param PhotoArchiveChapter|null $next         The next chapter, if any.
	 */
	public function __construct(
		public string $archiveTitle,
		public string $archiveUrl,
		public PhotoArchiveChapter $current,
		public int $total,
		public ?PhotoArchiveChapter $previous,
		public ?PhotoArchiveChapter $next
	){
		return;
	}
}
