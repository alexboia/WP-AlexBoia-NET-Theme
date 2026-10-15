<?php
/**
 * View model for the linked post date (views/entry-meta-date.php)
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 */

namespace AbNetTheme\ViewModel;

if (!defined('ABSPATH')) {
	exit;
}

final readonly class EntryMetaDateViewModel implements ViewModel {
	/**
	 * @param string $permalink   The post URL.
	 * @param string $time        The post time, used as link title.
	 * @param string $dateIso     The post date in ISO 8601 format.
	 * @param string $dateDisplay The post date, formatted for display.
	 */
	public function __construct(
		public string $permalink,
		public string $time,
		public string $dateIso,
		public string $dateDisplay
	){
		return;
	}
}
