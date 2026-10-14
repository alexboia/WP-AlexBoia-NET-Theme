<?php
/**
 * Meta & SEO settings page
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 *
 * @var \AbNetTheme\ViewModel\MetaSettingsPageViewModel $data
 */

if (!defined('ABSPATH')) {
	exit;
}
?>
<div class="wrap abnet-settings">
	<header class="abnet-settings-header">
		<h1 class="abnet-settings-title"><?php esc_html_e('Meta & SEO', 'abnet'); ?></h1>
		<p class="abnet-settings-tagline"><?php esc_html_e('How the blog presents itself to search engines and social networks.', 'abnet'); ?></p>
	</header>
	<hr class="wp-header-end" />

	<form method="post" action="options.php">
		<?php settings_fields($data->optionGroup); ?>

		<section class="abnet-settings-section">
			<h2 class="abnet-settings-section-title"><?php esc_html_e('Author', 'abnet'); ?></h2>
			<p class="abnet-settings-section-description"><?php esc_html_e('The person behind the blog. Search engines see them as the publisher of all articles and as the author of the articles written by the user below.', 'abnet'); ?></p>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="abnet-person-user-id"><?php esc_html_e('User', 'abnet'); ?></label></th>
					<td>
						<?php wp_dropdown_users(array(
							'id' => 'abnet-person-user-id',
							'name' => $data->fieldName('person_user_id'),
							'selected' => $data->personUserId,
							'show_option_none' => __('&mdash; None &mdash;', 'abnet'),
							'option_none_value' => 0,
							'capability' => array('edit_posts')
						)); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="abnet-person-name"><?php esc_html_e('Full name', 'abnet'); ?></label></th>
					<td>
						<input type="text"
							id="abnet-person-name"
							name="<?php echo esc_attr($data->fieldName('person_name')); ?>"
							class="regular-text"
							value="<?php echo esc_attr($data->personName); ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="abnet-about-page-id"><?php esc_html_e('About page', 'abnet'); ?></label></th>
					<td>
						<?php wp_dropdown_pages(array(
							'id' => 'abnet-about-page-id',
							'name' => $data->fieldName('about_page_id'),
							'selected' => $data->aboutPageId,
							'show_option_none' => __('&mdash; None &mdash;', 'abnet'),
							'option_none_value' => 0
						)); ?>
						<p class="description"><?php esc_html_e('Used as the URL of the author and marked up as a profile page.', 'abnet'); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="abnet-person-image-url"><?php esc_html_e('Image URL', 'abnet'); ?></label></th>
					<td>
						<input type="url"
							id="abnet-person-image-url"
							name="<?php echo esc_attr($data->fieldName('person_image_url')); ?>"
							class="regular-text code"
							value="<?php echo esc_attr($data->personImageUrl); ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="abnet-person-same-as"><?php esc_html_e('Profiles (sameAs)', 'abnet'); ?></label></th>
					<td>
						<textarea id="abnet-person-same-as"
							name="<?php echo esc_attr($data->fieldName('person_same_as')); ?>"
							class="large-text code"
							rows="4"><?php echo esc_textarea(implode("\n", $data->personSameAs)); ?></textarea>
						<p class="description"><?php esc_html_e('Profiles of the same person on other sites, one URL per line.', 'abnet'); ?></p>
					</td>
				</tr>
			</table>
		</section>

		<section class="abnet-settings-section">
			<h2 class="abnet-settings-section-title"><?php esc_html_e('Site', 'abnet'); ?></h2>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="abnet-site-name"><?php esc_html_e('Site name', 'abnet'); ?></label></th>
					<td>
						<input type="text"
							id="abnet-site-name"
							name="<?php echo esc_attr($data->fieldName('site_name')); ?>"
							class="regular-text"
							value="<?php echo esc_attr($data->siteName); ?>" />
						<p class="description"><?php esc_html_e('The name search engines show for the site. When empty, the blog title is used.', 'abnet'); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="abnet-site-alternate-name"><?php esc_html_e('Alternate name', 'abnet'); ?></label></th>
					<td>
						<input type="text"
							id="abnet-site-alternate-name"
							name="<?php echo esc_attr($data->fieldName('site_alternate_name')); ?>"
							class="regular-text"
							value="<?php echo esc_attr($data->siteAlternateName); ?>" />
						<p class="description"><?php esc_html_e('Used by search engines when the site name is not available.', 'abnet'); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="abnet-blog-meta-description"><?php esc_html_e('Meta description', 'abnet'); ?></label></th>
					<td>
						<textarea id="abnet-blog-meta-description"
							name="<?php echo esc_attr($data->fieldName('blog_meta_description')); ?>"
							class="large-text"
							rows="3"><?php echo esc_textarea($data->blogMetaDescription); ?></textarea>
						<p class="description"><?php esc_html_e('Description of the blog for the front page and archives. When empty, the tagline is used.', 'abnet'); ?></p>
					</td>
				</tr>
			</table>
		</section>

		<?php submit_button(); ?>
	</form>
</div>
