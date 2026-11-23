<?php
/**
 * View model for the photo archive settings metabox of the page editor (views/photo-archive-metabox.php)
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 */

namespace AbNetTheme\ViewModel;

if (!defined('ABSPATH')) {
	exit;
}

use WP_Term;

final readonly class PhotoArchiveMetaboxViewModel implements ViewModel {
	/**
	 * @param int       $categoryId  The selected chapter category, 0 when none.
	 * @param WP_Term[] $categories  The categories to choose from.
	 * @param string    $subtitle    The subtitle.
	 * @param string    $period      The period the photos cover.
	 * @param bool      $showSidebar Whether the page keeps the blog sidebar.
	 * @param string    $nonceAction The nonce action.
	 * @param string    $nonceName   The nonce field name.
	 */
	public function __construct(
		public int $categoryId,
		public array $categories,
		public string $subtitle,
		public string $period,
		public bool $showSidebar,
		public string $nonceAction,
		public string $nonceName
	){
		return;
	}
}
