<?php
/**
 * SEO metabox for the post editor
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 *
 * @var \AbNetTheme\ViewModel\SeoMetaboxViewModel $data
 */

if (!defined('ABSPATH')) {
	exit;
}

wp_nonce_field($data->nonceAction, $data->nonceName);
?>
<p>
	<label for="abnet-seo-title"><strong><?php esc_html_e('SEO Title', 'abnet'); ?></strong></label>
	<input type="text"
		id="abnet-seo-title"
		name="abnet_seo_title"
		class="widefat"
		value="<?php echo esc_attr($data->seoTitle); ?>" />
	<span class="description"><?php esc_html_e('Recommended: up to 60 characters.', 'abnet'); ?></span>
</p>
<p>
	<label for="abnet-seo-use-in-document-title">
		<input type="checkbox" 
			id="abnet-seo-use-in-document-title" 
			name="abnet_seo_use_in_document_title" 
			value="1" 
			<?php checked($data->useInDocumentTitle); ?> />
		<?php esc_html_e('Also use the SEO title as the page title (browser tab)', 'abnet'); ?>
	</label>
</p>
<p>
	<label for="abnet-seo-description"><strong><?php esc_html_e('SEO Description', 'abnet'); ?></strong></label>
	<textarea id="abnet-seo-description"
		name="abnet_seo_description"
		class="widefat"
		rows="4"><?php echo esc_textarea($data->seoDescription); ?></textarea>
	<span class="description"><?php esc_html_e('Recommended: up to 160 characters.', 'abnet'); ?></span>
</p>
<p class="description">
	<?php esc_html_e('Optional. When filled in, they replace the post title and excerpt in the meta description, social media tags and structured data, as well as in the page title, if enabled.', 'abnet'); ?>
</p>
