<?php
/**
 * A pingback or trackback in the comments list.
 * The list item is left open; wp_list_comments() closes it.
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 *
 * @var \AbNetTheme\ViewModel\CommentViewModel $data
 */

if (!defined('ABSPATH')) {
	exit;
}
?>
<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
	<p><?php _e('Pingback:', 'abnet'); ?> <?php comment_author_link(); ?> <?php edit_comment_link(__('(Edit)', 'abnet'), '<span class="edit-link">', '</span>'); ?></p>
