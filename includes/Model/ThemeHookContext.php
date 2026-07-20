<?php
/**
 * Theme hook context class
 *
 * Provides context information for meta tag generation including blog settings,
 * current query, and current post/page information.
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 */

namespace AbNetTheme\Model;

use WP_Post;
use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ThemeHookContext {
	private array $_blogSettings;

	private ?\WP_Post $_post;

	private \WP_Query $_query;

	/**
	 * @param \WP_Query $query Optional. The current WP_Query object. Defaults to global $wp_query.
	 */
	public function __construct( ?\WP_Query $query = null ) {
		global $wp_query, $post;

		$this->_query = $query ?? $wp_query;
		$this->_post  = $post ?? null;

		$this->_blogSettings = array(
			'blog_name'        => get_bloginfo( 'name' ),
			'blog_description' => get_bloginfo( 'description' ),
			'blog_url'         => get_home_url(),
			'blog_language'    => get_bloginfo( 'language' ),
		);
	}

	public function getBlogSettings(): array {
		return $this->_blogSettings;
	}

	public function currentPost(): ?WP_Post {
		return $this->_post;
	}

	public function currentQuery(): WP_Query {
		return $this->_query;
	}

	public function isFrontPage() {
		return is_front_page();
	}

	public function isHome(): bool {
		return is_home();
	}

	public function isSingle(): bool {
		return is_single();
	}

	public function isPage(): bool{
		return is_page();
	}

	public function isArchive(): bool {
		return is_archive();
	}

	public function isCategoryArchive(): bool {
		return is_category();
	}

	public function isTagArchive(): bool {
		return is_tag();
	}

	public function isAuthorArchive(): bool {
		return is_author();
	}

	public function getArchiveTitle(): ?string {
		if ( is_category() ) {
			return single_cat_title( '', false );
		} elseif ( is_tag() ) {
			return single_tag_title( '', false );
		} elseif ( is_author() ) {
			return get_the_author_meta( 'display_name', get_query_var( 'author' ) );
		}
		return '';
	}
}
