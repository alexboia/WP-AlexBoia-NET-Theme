<?php
/**
 * Hero of a photo archive landing page: featured image, title, subtitle and figures
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

$chapterCount = count($data->chapters);
?>
<header class="abnet-pa-hero<?php echo $data->heroUrl !== '' ? ' abnet-pa-hero-with-image' : ''; ?>">
	<?php if ($data->heroUrl !== ''): ?>
		<img class="abnet-pa-hero-image"
			src="<?php echo esc_url($data->heroUrl); ?>"
			alt="<?php echo esc_attr($data->heroAlt); ?>"
			fetchpriority="high" />
	<?php endif; ?>
	<div class="abnet-pa-hero-inner">
		<p class="abnet-pa-kicker"><?php esc_html_e('Photo archive', 'abnet'); ?></p>
		<h1 class="abnet-pa-title"><?php echo esc_html($data->title); ?></h1>
		<?php if ($data->subtitle !== ''): ?>
			<p class="abnet-pa-subtitle"><?php echo esc_html($data->subtitle); ?></p>
		<?php endif; ?>
		<?php if ($chapterCount > 0 || $data->period !== ''): ?>
			<ul class="abnet-pa-stats">
				<?php if ($data->period !== ''): ?>
					<li>
						<strong><?php echo esc_html($data->period); ?></strong>
						<span><?php esc_html_e('Period', 'abnet'); ?></span>
					</li>
				<?php endif; ?>
				<?php if ($chapterCount > 0): ?>
					<li>
						<strong><?php echo esc_html(number_format_i18n($chapterCount)); ?></strong>
						<span><?php echo esc_html(_n('chapter', 'chapters', $chapterCount, 'abnet')); ?></span>
					</li>
				<?php endif; ?>
				<?php if ($data->photoCount > 0): ?>
					<li>
						<strong><?php echo esc_html(number_format_i18n($data->photoCount)); ?></strong>
						<span><?php echo esc_html(_n('photo', 'photos', $data->photoCount, 'abnet')); ?></span>
					</li>
				<?php endif; ?>
			</ul>
		<?php endif; ?>
	</div>
</header>
