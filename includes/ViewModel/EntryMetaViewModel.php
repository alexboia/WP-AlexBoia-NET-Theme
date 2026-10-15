<?php
/**
 * View model for the post meta information (views/entry-meta.php)
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 */

namespace AbNetTheme\ViewModel;

if (!defined('ABSPATH')) {
	exit;
}

final readonly class EntryMetaViewModel implements ViewModel {
	/**
	 * @param string $categoriesListHtml The linked list of categories, or an empty string.
	 * @param string $tagListHtml        The linked list of tags, or an empty string.
	 */
	public function __construct(
		public string $categoriesListHtml,
		public string $tagListHtml
	){
		return;
	}
}
