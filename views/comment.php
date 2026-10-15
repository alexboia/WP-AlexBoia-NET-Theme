<?php
/**
 * A comment in the comments list.
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
<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
	<article id="comment-<?php comment_ID(); ?>" class="comment">
		<header class="comment-meta comment-author vcard">
			<?php
				echo get_avatar($data->comment, 44);
				printf('<cite><b class="fn">%1$s</b> %2$s</cite>',
					get_comment_author_link(),
					// If current post author is also comment author, make it known visually.
					$data->isByPostAuthor ? '<span>' . __('Post author', 'abnet') . '</span>' : ''
				);
				printf('<a href="%1$s"><time datetime="%2$s">%3$s</time></a>',
					esc_url(get_comment_link($data->comment->comment_ID)),
					get_comment_time('c'),
					/* translators: 1: date, 2: time */
					sprintf(__('%1$s at %2$s', 'abnet'), get_comment_date(), get_comment_time())
				);
			?>
		</header><!-- .comment-meta -->

		<?php if ('0' == $data->comment->comment_approved): ?>
			<p class="comment-awaiting-moderation"><?php _e('Your comment is awaiting moderation.', 'abnet'); ?></p>
		<?php endif; ?>

		<section class="comment-content comment">
			<?php comment_text(); ?>
		</section><!-- .comment-content -->

		<div class="comment-control">
			<?php edit_comment_link('<span class="dashicons dashicons-edit"></span>' . __('Edit', 'abnet'), '<div class="edit-link">', '</div>'); ?>
			<div class="reply">
				<?php comment_reply_link(array_merge($data->args, array(
					'reply_text' => '<span class="dashicons dashicons-format-chat"></span>' . __('Reply', 'abnet'),
					'depth' => $data->depth,
					'max_depth' => $data->args['max_depth']
				))); ?>
			</div><!-- .reply -->
		</div>
	</article><!-- #comment-## -->
