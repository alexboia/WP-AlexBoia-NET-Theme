<?php
/**
 * View model for a comment or a pingback/trackback (views/comment.php, views/comment-pingback.php)
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 */

namespace AbNetTheme\ViewModel;

if (!defined('ABSPATH')) {
	exit;
}

use WP_Comment;

final readonly class CommentViewModel implements ViewModel {
	/**
	 * @param WP_Comment $comment        The comment.
	 * @param array      $args           The wp_list_comments() arguments.
	 * @param int        $depth          The depth of the comment.
	 * @param bool       $isByPostAuthor Whether the comment was written by the author of the post.
	 */
	public function __construct(
		public WP_Comment $comment,
		public array $args,
		public int $depth,
		public bool $isByPostAuthor
	){
		return;
	}
}
