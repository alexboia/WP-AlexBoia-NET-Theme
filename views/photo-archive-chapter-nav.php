<?php
/**
 * Previous/next chapter navigation under a chapter post, with a link back to the archive.
 * Uses the markup of the regular post navigation (single.php), so it shares its look.
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 *
 * @var \AbNetTheme\ViewModel\PhotoArchiveChapterContextViewModel $data
 */

if (!defined('ABSPATH')) {
	exit;
}
?>
<nav class="nav-single abnet-pa-chapter-nav">
	<div class="nav-single-row">
		<h3 class="assistive-text"><?php esc_html_e('Chapter navigation', 'abnet'); ?></h3>
		<div class="nav-previous <?php echo $data->previous ? 'nav-with-post' : 'nav-no-post'; ?>">
			<?php if ($data->previous): ?>
				<a href="<?php echo esc_url($data->previous->permalink); ?>" rel="prev">
					<span class="dashicons dashicons-arrow-left nav-icon"></span>
					<h4>
						<span class="abnet-pa-nav-label"><?php esc_html_e('Previous chapter', 'abnet'); ?></span>
						<?php echo esc_html($data->previous->title); ?>
					</h4>
				</a>
			<?php endif; ?>
		</div>
		<div class="nav-next <?php echo $data->next ? 'nav-with-post' : 'nav-no-post'; ?>">
			<?php if ($data->next): ?>
				<a href="<?php echo esc_url($data->next->permalink); ?>" rel="next">
					<h4>
						<span class="abnet-pa-nav-label"><?php esc_html_e('Next chapter', 'abnet'); ?></span>
						<?php echo esc_html($data->next->title); ?>
					</h4>
					<span class="dashicons dashicons-arrow-right nav-icon"></span>
				</a>
			<?php endif; ?>
		</div>
	</div>
</nav>
<p class="abnet-pa-nav-archive">
	<a href="<?php echo esc_url($data->archiveUrl); ?>">
		<?php echo esc_html(sprintf(__('All chapters of %s', 'abnet'), $data->archiveTitle)); ?>
	</a>
</p>
