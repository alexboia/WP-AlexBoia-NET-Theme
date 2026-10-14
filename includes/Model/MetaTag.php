<?php
/**
 * Meta tag representation class
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 */

namespace AbNetTheme\Model;

if (!defined( 'ABSPATH')) {
	exit;
}

class MetaTag {
	private ?string $_name;

	private ?string $_content;

	private ?string $_property;

	public function __construct(string $name, ?string $content, ?string $property = null ) {
		$this->_name = self::sanitizeKeyWithColons($name);
		$this->_content = sanitize_text_field($content);
		$this->_property = $property ? self::sanitizeKeyWithColons($property) : null;
	}

	public static function sanitizeKeyWithColons(?string $string): ?string {
		// Replaces special chars with empty string, but preserves a-z, 0-9, -, _, and :
		return preg_replace('/[^a-z0-9\-_:]/', '', strtolower($string));
	}

	public function getName(): ?string {
		return $this->_name;
	}

	public function getContent(): ?string {
		return $this->_content;
	}

	public function getProperty(): ?string {
		return $this->_property;
	}

	/**
	 * Render the meta tag as HTML
	 *
	 * @return string
	 */
	public function render(): string {
		if ($this->_property) {
			return sprintf(
				'<meta property="%s" content="%s" />',
				esc_attr($this->_property),
				esc_attr($this->_content)
			);
		}

		return sprintf(
			'<meta name="%s" content="%s" />',
			esc_attr($this->_name),
			esc_attr($this->_content)
		);
	}
}
