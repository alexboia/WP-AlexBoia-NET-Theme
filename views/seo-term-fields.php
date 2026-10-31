<?php
/**
 * SEO fields for the term (category) add and edit screens
 *
 * On the edit screen they are rendered inside the form table, so each field is a table row.
 * On the add screen (next to the term list) each field is a form-field block.
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 *
 * @var \AbNetTheme\ViewModel\SeoTermFieldsViewModel $data
 */

if (!defined('ABSPATH')) {
	exit;
}

$titleLabel = __('SEO Title', 'abnet');
$descriptionLabel = __('SEO Description', 'abnet');
?>
<?php if ($data->isNewTerm): ?>
	<div class="form-field abnet-seo-term-title-wrap">
		<?php wp_nonce_field($data->nonceAction, $data->nonceName, false); ?>
		<label for="abnet-seo-title"><?php echo esc_html($titleLabel); ?></label>
		<input type="text"
			id="abnet-seo-title"
			name="abnet_seo_title"
			value="<?php echo esc_attr($data->seoTitle); ?>" />
		<p class="description"><?php esc_html_e('Recommended: up to 60 characters.', 'abnet'); ?></p>
	</div>
	<div class="form-field abnet-seo-term-use-in-document-title-wrap">
		<label for="abnet-seo-use-in-document-title">
			<input type="checkbox"
				id="abnet-seo-use-in-document-title"
				name="abnet_seo_use_in_document_title"
				value="1"
				<?php checked($data->useInDocumentTitle); ?> />
			<?php esc_html_e('Also use the SEO title as the page title (browser tab)', 'abnet'); ?>
		</label>
	</div>
	<div class="form-field abnet-seo-term-description-wrap">
		<label for="abnet-seo-description"><?php echo esc_html($descriptionLabel); ?></label>
		<textarea id="abnet-seo-description"
			name="abnet_seo_description"
			rows="4"><?php echo esc_textarea($data->seoDescription); ?></textarea>
		<p class="description"><?php esc_html_e('Recommended: up to 160 characters.', 'abnet'); ?></p>
		<p class="description"><?php esc_html_e('Optional. When filled in, they replace the category name and the site description in the meta description, social media tags and structured data, as well as in the page title, if enabled.', 'abnet'); ?></p>
	</div>
<?php else: ?>
	<tr class="form-field abnet-seo-term-title-wrap">
		<th scope="row">
			<label for="abnet-seo-title"><?php echo esc_html($titleLabel); ?></label>
		</th>
		<td>
			<?php wp_nonce_field($data->nonceAction, $data->nonceName, false); ?>
			<input type="text"
				id="abnet-seo-title"
				name="abnet_seo_title"
				value="<?php echo esc_attr($data->seoTitle); ?>" />
			<p class="description"><?php esc_html_e('Recommended: up to 60 characters.', 'abnet'); ?></p>
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
		</td>
	</tr>
	<tr class="form-field abnet-seo-term-description-wrap">
		<th scope="row">
			<label for="abnet-seo-description"><?php echo esc_html($descriptionLabel); ?></label>
		</th>
		<td>
			<textarea id="abnet-seo-description"
				name="abnet_seo_description"
				rows="4"><?php echo esc_textarea($data->seoDescription); ?></textarea>
			<p class="description"><?php esc_html_e('Recommended: up to 160 characters.', 'abnet'); ?></p>
			<p class="description"><?php esc_html_e('Optional. When filled in, they replace the category name and the site description in the meta description, social media tags and structured data, as well as in the page title, if enabled.', 'abnet'); ?></p>
		</td>
	</tr>
<?php endif; ?>
