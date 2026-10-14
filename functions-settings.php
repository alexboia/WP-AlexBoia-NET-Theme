<?php
/**
 * Theme settings pages
 *
 * Adds the Settings > Meta & SEO page, where the configurable attributes
 * of the meta layer (Open Graph tags, schema.org data) are edited.
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 */

use AbNetTheme\Settings\MetaSettings;
use AbNetTheme\ViewModel\MetaSettingsPageViewModel;

if (!defined('ABSPATH')) {
	exit;
}

const ABNET_META_SETTINGS_PAGE_SLUG = 'abnet-meta-settings';

function abnet_register_meta_settings(): void {
	register_setting(MetaSettings::OPTION_GROUP,
		MetaSettings::OPTION_NAME,
		array(
			'type' => 'array',
			'sanitize_callback' => array(MetaSettings::class, 'sanitize')
		));
}

add_action('admin_init',
	'abnet_register_meta_settings');

function abnet_add_meta_settings_page(): void {
	add_options_page(__('Meta & SEO', 'abnet'),
		__('Meta & SEO', 'abnet'),
		'manage_options',
		ABNET_META_SETTINGS_PAGE_SLUG,
		'abnet_render_meta_settings_page');
}

add_action('admin_menu',
	'abnet_add_meta_settings_page');

function abnet_enqueue_settings_page_styles(string $hookSuffix): void {
	if ($hookSuffix !== 'settings_page_' . ABNET_META_SETTINGS_PAGE_SLUG) {
		return;
	}

	$themeUri = get_template_directory_uri();
	$version = wp_get_theme()->get('Version');

	foreach (array('inter') as $font) {
		wp_enqueue_style('abnet-font-' . $font,
			$themeUri . '/css/fonts/' . $font . '/index.css',
			array(),
			$version);
	}

	wp_enqueue_style('abnet-admin-settings',
		$themeUri . '/css/admin/settings.css',
		array('abnet-font-inter'),
		$version);
}

add_action('admin_enqueue_scripts',
	'abnet_enqueue_settings_page_styles');

function abnet_render_meta_settings_page(): void {
	if (!current_user_can('manage_options')) {
		return;
	}

	$settings = new MetaSettings();
	$viewModel = new MetaSettingsPageViewModel(
		MetaSettings::OPTION_GROUP,
		MetaSettings::OPTION_NAME,
		$settings->getPersonUserId(),
		$settings->getPersonName(),
		$settings->getPersonImageUrl(),
		$settings->getPersonSameAs(),
		$settings->getAboutPageId(),
		$settings->getSiteName(),
		$settings->getSiteAlternateName(),
		$settings->getBlogMetaDescription()
	);

	abnet_render_view('meta-settings-page', $viewModel);
}
