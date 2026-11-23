<?php
/**
 * Hero at the top of a chapter post, in the style of the archive landing page:
 * the featured image under the navy gradient, the chapter position with a link to the archive, and the title.
 * Replaces the featured image and the title of content.php on chapter posts.
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 *
 * @var \AbNetTheme\ViewModel\PhotoArchiveChapterHeroViewModel $data
 */

if (!defined('ABSPATH')) {
	exit;
}

$context = $data->context;
?>
<div class="abnet-pa-hero abnet-pa-hero-chapter<?php echo $data->imageUrl !== '' ? ' abnet-pa-hero-with-image' : ''; ?>">
	<?php if ($data->imageUrl !== ''): ?>
		<img class="abnet-pa-hero-image"
			src="<?php echo esc_url($data->imageUrl); ?>"
			alt="<?php echo esc_attr($data->imageAlt); ?>"
			fetchpriority="high" />
	<?php endif; ?>
	<div class="abnet-pa-hero-inner">
		<p class="abnet-pa-kicker">
			<span class="abnet-pa-kicker-position">
				<?php echo esc_html(sprintf(__('Chapter %1$s of %2$s', 'abnet'),
					number_format_i18n($context->current->number),
					number_format_i18n($context->total))); ?>
			</span>
			<span class="abnet-pa-kicker-separator" aria-hidden="true">·</span>
			<a class="abnet-pa-kicker-archive" href="<?php echo esc_url($context->archiveUrl); ?>"><?php echo esc_html($context->archiveTitle); ?></a>
		</p>
		<h1 class="entry-title abnet-pa-title"><?php echo esc_html($context->current->title); ?></h1>
	</div>
</div>
