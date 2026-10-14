<?php
/**
 * Schema.org structured data representation
 *
 * Provides a strongly typed representation of schema.org types
 * for generating JSON-LD structured data.
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 */

namespace AbNetTheme\Model;

if (!defined('ABSPATH')) {
	exit;
}

class SchemaOrg {
	/**
	 * Schema type (e.g., 'Article', 'Person', 'Organization')
	 *
	 * @var string
	 */
	private string $_type;

	/**
	 * Schema properties
	 *
	 * @var array
	 */
	private array $_properties = array();

	/**
	 * Constructor
	 *
	 * @param string $type The schema.org type.
	 */
	public function __construct(string $type) {
		$this->_type = $type;
		$this->_properties['@context'] = 'https://schema.org';
		$this->_properties['@type'] = $type;
	}

	/**
	 * Get the schema type
	 *
	 * @return string
	 */
	public function getType(): string {
		return $this->_type;
	}

	/**
	 * Set a property
	 *
	 * @param string       $key   The property name.
	 * @param mixed        $value The property value.
	 * @param string|null  $type  Optional. The property type for nested objects.
	 * @return self
	 */
	public function setProperty(string $key, $value, ?string $type = null): self {
		if ($value === null || $value === '') {
			return $this;
		}

		if (is_string($value)) {
			$this->_properties[$key] = sanitize_text_field($value);
		} elseif (is_array($value)) {
			$this->_properties[$key] = $this->_sanitizeArray($value);
		} elseif (is_object($value) && $value instanceof SchemaOrg) {
			$this->_properties[$key] = $value->toArray(false);
		} else {
			$this->_properties[$key] = $value;
		}

		return $this;
	}

	/**
	 * Set multiple properties at once
	 *
	 * @param array $properties Associative array of property key/value pairs.
	 * @return self
	 */
	public function setProperties(array $properties): self {
		foreach ($properties as $key => $value) {
			$this->setProperty($key, $value);
		}

		return $this;
	}

	/**
	 * Get a property
	 *
	 * @param string $key The property name.
	 * @return mixed|null
	 */
	public function getProperty(string $key) {
		return $this->_properties[$key] ?? null;
	}

	/**
	 * Get all properties as array
	 *
	 * @param bool $includeContext Optional. Whether to include @context. 
	 *                             Nested objects inherit it from the top level node.
	 * @return array
	 */
	public function toArray(bool $includeContext = true): array {
		if ($includeContext) {
			return $this->_properties;
		}

		$properties = $this->_properties;
		unset($properties['@context']);
		return $properties;
	}

	/**
	 * Get JSON-LD representation
	 *
	 * @return string
	 */
	public function toJsonLd(): string {
		return wp_json_encode($this->_properties, 		
			JSON_UNESCAPED_UNICODE 
				| JSON_HEX_TAG 
				| JSON_PRETTY_PRINT);
	}

	/**
	 * Render as HTML script tag
	 *
	 * @return string
	 */
	public function render(): string {
		return sprintf(
			'<script type="application/ld+json">%s</script>',
			$this->toJsonLd()
		);
	}

	/**
	 * Sanitize array values
	 *
	 * @param array $array The array to sanitize.
	 * @return array
	 */
	private function _sanitizeArray(array $array): array {
		$sanitized = array();

		foreach ($array as $key => $value) {
			if (is_string($value)) {
				$sanitized[$key] = sanitize_text_field($value);
			} elseif (is_array($value)) {
				$sanitized[$key] = $this->_sanitizeArray($value);
			} elseif ($value instanceof SchemaOrg) {
				$sanitized[$key] = $value->toArray(false);
			} else {
				$sanitized[$key] = $value;
			}
		}

		return $sanitized;
	}
}
