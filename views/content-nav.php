<?php
/**
 * Navigation to next/previous pages of posts
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 *
 * @var \AbNetTheme\ViewModel\ContentNavViewModel $data
 */

if (!defined('ABSPATH')) {
	exit;
}
?>
<nav id="<?php echo esc_attr($data->htmlId); ?>" class="navigation" role="navigation">
	<h3 class="assistive-text"><?php _e('Post navigation', 'abnet'); ?></h3>
	<div class="nav-previous"><?php next_posts_link(__('<span class="meta-nav">&larr;</span> Older posts', 'abnet')); ?></div>
	<div class="nav-next"><?php previous_posts_link(__('Newer posts <span class="meta-nav">&rarr;</span>', 'abnet')); ?></div>
</nav><!-- #<?php echo esc_attr($data->htmlId); ?> .navigation -->
