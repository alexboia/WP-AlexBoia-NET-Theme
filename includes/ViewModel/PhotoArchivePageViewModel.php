<?php
/**
 * View model for the photo archive landing page sections
 * (views/photo-archive-hero.php, views/photo-archive-chapters.php)
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

final readonly class PhotoArchivePageViewModel implements ViewModel {
	/**
	 * @param string                $title       The page title.
	 * @param string                $subtitle    The subtitle shown under the title, may be empty.
	 * @param string                $period      The period the photos cover (e.g. "1936–1938"), may be empty.
	 * @param string                $heroUrl     The URL of the page's featured image, empty when the page has none.
	 * @param string                $heroAlt     The alternative text of the featured image.
	 * @param PhotoArchiveChapter[] $chapters    The chapters, in reading order.
	 * @param int                   $photoCount  The number of photos across all chapters.
	 * @param bool                  $hasCategory Whether a chapter category has been chosen for the page.
	 */
	public function __construct(
		public string $title,
		public string $subtitle,
		public string $period,
		public string $heroUrl,
		public string $heroAlt,
		public array $chapters,
		public int $photoCount,
		public bool $hasCategory
	){
		return;
	}
}
