<?php
/**
 * Photo archive settings metabox for the page editor
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 *
 * @var \AbNetTheme\ViewModel\PhotoArchiveMetaboxViewModel $data
 */

if (!defined('ABSPATH')) {
	exit;
}

wp_nonce_field($data->nonceAction, $data->nonceName);
?>
<p class="description">
	<?php esc_html_e('Used only when the page has the Photo Archive template.', 'abnet'); ?>
</p>
<p>
	<label for="abnet-photo-archive-category"><strong><?php esc_html_e('Chapter category', 'abnet'); ?></strong></label>
	<select id="abnet-photo-archive-category" name="abnet_photo_archive_category" class="widefat">
		<option value="0"><?php esc_html_e('— Select a category —', 'abnet'); ?></option>
		<?php foreach ($data->categories as $category): ?>
			<option value="<?php echo esc_attr($category->term_id); ?>" <?php selected($data->categoryId, $category->term_id); ?>>
				<?php echo esc_html($category->name); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<span class="description"><?php esc_html_e('The posts in this category are listed as chapters, oldest first.', 'abnet'); ?></span>
</p>
<p>
	<label for="abnet-photo-archive-subtitle"><strong><?php esc_html_e('Subtitle', 'abnet'); ?></strong></label>
	<input type="text"
		id="abnet-photo-archive-subtitle"
		name="abnet_photo_archive_subtitle"
		class="widefat"
		value="<?php echo esc_attr($data->subtitle); ?>" />
</p>
<p>
	<label for="abnet-photo-archive-period"><strong><?php esc_html_e('Period', 'abnet'); ?></strong></label>
	<input type="text"
		id="abnet-photo-archive-period"
		name="abnet_photo_archive_period"
		class="widefat"
		value="<?php echo esc_attr($data->period); ?>" />
	<span class="description"><?php esc_html_e('For instance: 1936–1938', 'abnet'); ?></span>
</p>
