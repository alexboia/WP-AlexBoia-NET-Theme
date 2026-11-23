<?php
/**
 * Chapter cards of a photo archive landing page
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 *
 * @var \AbNetTheme\ViewModel\PhotoArchivePageViewModel $data
 */

if (!defined('ABSPATH')) {
	exit;
}
?>
<section class="abnet-pa-chapters" aria-labelledby="abnet-pa-chapters-title">
	<h2 id="abnet-pa-chapters-title" class="abnet-pa-section-title"><?php esc_html_e('Chapters', 'abnet'); ?></h2>

	<?php if (empty($data->chapters)): ?>
		<p class="abnet-pa-empty">
			<?php if ($data->hasCategory): ?>
				<?php esc_html_e('No chapters have been published yet.', 'abnet'); ?>
			<?php elseif (current_user_can('edit_pages')): ?>
				<?php esc_html_e('Choose the chapter category in the Photo archive box of the page editor.', 'abnet'); ?>
			<?php else: ?>
				<?php esc_html_e('No chapters have been published yet.', 'abnet'); ?>
			<?php endif; ?>
		</p>
	<?php else: ?>
		<ol class="abnet-pa-chapter-list">
			<?php foreach ($data->chapters as $chapter): ?>
				<li class="abnet-pa-chapter">
					<a class="abnet-pa-chapter-link" href="<?php echo esc_url($chapter->permalink); ?>">
						<span class="abnet-pa-chapter-media">
							<?php if ($chapter->thumbnailUrl !== ''): ?>
								<img src="<?php echo esc_url($chapter->thumbnailUrl); ?>"
									alt="<?php echo esc_attr($chapter->thumbnailAlt); ?>"
									loading="lazy" />
							<?php endif; ?>
							<span class="abnet-pa-chapter-number" aria-hidden="true"><?php echo esc_html($chapter->paddedNumber()); ?></span>
						</span>
						<span class="abnet-pa-chapter-body">
							<span class="abnet-pa-chapter-label">
								<?php echo esc_html(sprintf(__('Chapter %s', 'abnet'), number_format_i18n($chapter->number))); ?>
							</span>
							<span class="abnet-pa-chapter-title"><?php echo esc_html($chapter->title); ?></span>
							<?php if ($chapter->excerpt !== ''): ?>
								<span class="abnet-pa-chapter-excerpt"><?php echo esc_html($chapter->excerpt); ?></span>
							<?php endif; ?>
							<span class="abnet-pa-chapter-footer">
								<?php if ($chapter->photoCount > 0): ?>
									<span class="abnet-pa-chapter-count">
										<span class="genericon genericon-image" aria-hidden="true"></span>
										<?php echo esc_html(sprintf(_n('%s photo', '%s photos', $chapter->photoCount, 'abnet'), number_format_i18n($chapter->photoCount))); ?>
									</span>
								<?php endif; ?>
								<span class="abnet-pa-chapter-cta"><?php esc_html_e('View chapter', 'abnet'); ?> &rarr;</span>
							</span>
						</span>
					</a>
				</li>
			<?php endforeach; ?>
		</ol>
	<?php endif; ?>
</section>
