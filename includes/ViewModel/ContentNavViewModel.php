<?php
/**
 * View model for the posts navigation (views/content-nav.php)
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 */

namespace AbNetTheme\ViewModel;

if (!defined('ABSPATH')) {
	exit;
}

final readonly class ContentNavViewModel implements ViewModel {
	/**
	 * @param string $htmlId The id attribute of the navigation element.
	 */
	public function __construct(
		public string $htmlId
	){
		return;
	}
}
