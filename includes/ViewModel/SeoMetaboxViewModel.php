<?php
/**
 * View model for the SEO metabox (views/seo-metabox.php)
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 */

namespace AbNetTheme\ViewModel;

if (!defined('ABSPATH')) {
	exit;
}

final readonly class SeoMetaboxViewModel implements ViewModel {
	public function __construct(
		public string $seoTitle,
		public string $seoDescription,
		public bool $useInDocumentTitle,
		public string $nonceAction,
		public string $nonceName
	){
		return;
	}
}
