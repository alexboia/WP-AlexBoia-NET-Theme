<?php
/**
 * View model for the meta settings page (views/meta-settings-page.php)
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 */

namespace AbNetTheme\ViewModel;

if (!defined('ABSPATH')) {
	exit;
}

final readonly class MetaSettingsPageViewModel implements ViewModel {
	/**
	 * @param string   $optionGroup         The settings group, for settings_fields().
	 * @param string   $optionName          The option name, used as prefix for the field names.
	 * @param int      $personUserId        The user represented by the person schema.
	 * @param string   $personName          The full name of the person.
	 * @param string   $personImageUrl      The image of the person.
	 * @param string[] $personSameAs        The profile URLs of the person.
	 * @param int      $aboutPageId         The about page.
	 * @param string   $siteName            The name of the site.
	 * @param string   $siteAlternateName   An alternate name of the site.
	 * @param string   $blogMetaDescription The meta description of the blog.
	 */
	public function __construct(
		public string $optionGroup,
		public string $optionName,
		public int $personUserId,
		public string $personName,
		public string $personImageUrl,
		public array $personSameAs,
		public int $aboutPageId,
		public string $siteName,
		public string $siteAlternateName,
		public string $blogMetaDescription
	){
		return;
	}

	/**
	 * Get the name attribute of a settings field
	 *
	 * @param string $key The settings key.
	 * @return string
	 */
	public function fieldName(string $key): string {
		return $this->optionName . '[' . $key . ']';
	}
}
