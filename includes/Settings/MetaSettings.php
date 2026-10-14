<?php
/**
 * Configurable meta settings
 *
 * Holds the attributes of the meta layer (Open Graph tags, schema.org data)
 * that can be configured from Settings > Meta & SEO.
 * Values not saved yet fall back to their defaults.
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 */

namespace AbNetTheme\Settings;

if (!defined('ABSPATH')) {
	exit;
}

class MetaSettings {
	const OPTION_NAME = 'abnet_meta_settings';

	const OPTION_GROUP = 'abnet_meta_settings';

	const DEFAULT_ABOUT_PAGE_PATH = 'despre';

	const DEFAULT_SAME_AS = array(
		'https://substack.com/@alexandruboia'
	);

	const DEFAULT_SITE_ALTERNATE_NAME = 'AlexBoia.NET';

	const DEFAULT_BLOG_META_DESCRIPTION = 'Jurnal de călătorii, biciclete, tehnologie și observații personale, scris cu umor, curiozitate și ceva parapon.';

	/**
	 * The effective settings values (stored values over defaults)
	 *
	 * @var array
	 */
	private array $_values;

	public function __construct() {
		$stored = get_option(self::OPTION_NAME, array());
		$this->_values = array_merge(self::getDefaults(),
			is_array($stored) ? $stored : array());
	}

	/**
	 * Get the default settings values
	 *
	 * @return array
	 */
	public static function getDefaults(): array {
		$aboutPage = get_page_by_path(self::DEFAULT_ABOUT_PAGE_PATH);

		return array(
			'person_user_id' => $aboutPage ? (int)$aboutPage->post_author : 0,
			'person_name' => get_bloginfo('name'),
			'person_image_url' => '',
			'person_same_as' => self::DEFAULT_SAME_AS,
			'about_page_id' => $aboutPage ? (int)$aboutPage->ID : 0,
			'site_name' => get_bloginfo('name'),
			'site_alternate_name' => self::DEFAULT_SITE_ALTERNATE_NAME,
			'blog_meta_description' => self::DEFAULT_BLOG_META_DESCRIPTION
		);
	}

	/**
	 * Sanitize the submitted settings values.
	 * Used as the sanitize callback of the registered setting.
	 *
	 * @param mixed $input The submitted values.
	 * @return array
	 */
	public static function sanitize(mixed $input): array {
		$input = is_array($input) ? $input : array();

		return array(
			'person_user_id' => absint($input['person_user_id'] ?? 0),
			'person_name' => sanitize_text_field($input['person_name'] ?? ''),
			'person_image_url' => self::_sanitizeUrl($input['person_image_url'] ?? ''),
			'person_same_as' => self::_sanitizeUrlList($input['person_same_as'] ?? array()),
			'about_page_id' => absint($input['about_page_id'] ?? 0),
			'site_name' => sanitize_text_field($input['site_name'] ?? ''),
			'site_alternate_name' => sanitize_text_field($input['site_alternate_name'] ?? ''),
			'blog_meta_description' => sanitize_text_field($input['blog_meta_description'] ?? '')
		);
	}

	/**
	 * Sanitize a list of URLs, given either as an array
	 * or as text with one URL per line
	 *
	 * @param mixed $urls The URLs.
	 * @return string[]
	 */
	private static function _sanitizeUrlList(mixed $urls): array {
		if (is_string($urls)) {
			$urls = preg_split('/\R/', $urls);
		}

		if (!is_array($urls)) {
			return array();
		}

		$sanitized = array();
		foreach ($urls as $url) {
			$url = self::_sanitizeUrl($url);
			if ($url !== '') {
				$sanitized[] = $url;
			}
		}

		return array_values(array_unique($sanitized));
	}

	/**
	 * Sanitize an absolute http(s) URL
	 *
	 * @param mixed $url The URL.
	 * @return string The sanitized URL, or an empty string if it is not a valid http(s) URL.
	 */
	private static function _sanitizeUrl(mixed $url): string {
		$url = trim((string)$url);
		if ($url === '' 
			|| !preg_match('#^https?://#i', $url) 
			|| filter_var($url, FILTER_VALIDATE_URL) === false) {
			return '';
		}

		return esc_url_raw($url, array('http', 'https'));
	}

	public function getPersonUserId(): int {
		return (int)$this->_values['person_user_id'];
	}

	public function getPersonName(): string {
		return (string)$this->_values['person_name'];
	}

	public function getPersonImageUrl(): string {
		return (string)$this->_values['person_image_url'];
	}

	/**
	 * @return string[]
	 */
	public function getPersonSameAs(): array {
		return (array)$this->_values['person_same_as'];
	}

	public function getAboutPageId(): int {
		return (int)$this->_values['about_page_id'];
	}

	public function getAboutPageUrl(): string {
		$aboutPageId = $this->getAboutPageId();
		if ($aboutPageId <= 0) {
			return '';
		}

		$url = get_permalink($aboutPageId);
		return $url ? $url : '';
	}

	/**
	 * Get the site name, falling back to the blog name when empty
	 *
	 * @return string
	 */
	public function getSiteName(): string {
		$siteName = (string)$this->_values['site_name'];
		return $siteName !== '' ? $siteName : get_bloginfo('name');
	}

	public function getSiteAlternateName(): string {
		return (string)$this->_values['site_alternate_name'];
	}

	public function getBlogMetaDescription(): string {
		return (string)$this->_values['blog_meta_description'];
	}
}
