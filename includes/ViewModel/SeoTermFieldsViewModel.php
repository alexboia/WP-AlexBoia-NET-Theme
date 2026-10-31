<?php
/**
 * View model for the SEO fields of the term add and edit screens (views/seo-term-fields.php)
 * $isNewTerm selects the markup of the add form instead of the edit form table.
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 */

namespace AbNetTheme\ViewModel;

if (!defined('ABSPATH')) {
	exit;
}

final readonly class SeoTermFieldsViewModel implements ViewModel {
	public function __construct(
		public string $seoTitle,
		public string $seoDescription,
		public bool $useInDocumentTitle,
		public string $nonceAction,
		public string $nonceName,
		public bool $isNewTerm = false
	){
		return;
	}
}
