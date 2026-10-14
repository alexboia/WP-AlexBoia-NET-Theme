<?php
/**
 * Schema.org structured data manager
 *
 * Manages schema.org structured data generation for different content types
 * and integrates with the OG meta tags system.
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 */

namespace AbNetTheme;

use AbNetTheme\Model\SchemaOrg;
use AbNetTheme\Model\SchemaOrg\ArticleInfo;
use AbNetTheme\Model\SchemaOrg\BreadcrumbItemInfo;
use AbNetTheme\Model\SchemaOrg\PersonInfo;
use AbNetTheme\Model\SchemaOrg\ProfilePageInfo;
use AbNetTheme\Model\SchemaOrg\WebPageInfo;
use AbNetTheme\Model\SchemaOrg\WebSiteInfo;
use AbNetTheme\Model\SchemaOrgBuilder;
use AbNetTheme\Model\ThemeHookContext;
use AbNetTheme\Settings\MetaSettings;
use Hamcrest\Text\StringContainsIgnoringCase;

if (!defined('ABSPATH')) {
	exit;
}

class SchemaOrgManager {
	/**
	 * Current context
	 *
	 * @var ThemeHookContext
	 */
	private ThemeHookContext $_context;

	/**
	 * Configurable meta settings
	 *
	 * @var MetaSettings
	 */
	private MetaSettings $_metaSettings;

	/**
	 * Schema objects to render
	 *
	 * @var SchemaOrg[]
	 */
	private array $_schemas = array();

	/**
	 * Constructor
	 *
	 * @param ThemeHookContext $context The theme hook context.
	 */
	public function __construct(ThemeHookContext $context) {
		$this->_context = $context;
		$this->_metaSettings = new MetaSettings();
		$this->_generateSchemas();
	}

	/**
	 * Generate schemas based on current page
	 *
	 * @return void
	 */
	private function _generateSchemas(): void {
		// Always add the schema of the person behind the blog (author and publisher)
		$this->_addPersonSchema();

		// Add breadcrumb schema
		$this->_addBreadcrumbSchema();

		// Add page-specific schema
		if ($this->_context->isSingle() || $this->_context->isPage()) {
			$this->_addPostSchema();
		} elseif ($this->_context->isArchive()) {
			$this->_addArchiveSchema();
		} elseif ($this->_context->isFrontPage()) {
			$this->_addWebSiteSchema();
		}
	}

	/**
	 * Add the schema of the person behind the blog
	 *
	 * The node is referenced by @id as the publisher of all articles
	 * and as the author of the articles written by the configured user.
	 *
	 * @return void
	 */
	private function _addPersonSchema(): void {
		$personInfo = new PersonInfo($this->_metaSettings->getPersonName(),
			$this->_metaSettings->getAboutPageUrl(),
			id: $this->_getPersonId(),
			imageUrl: $this->_metaSettings->getPersonImageUrl(),
			sameAs: $this->_metaSettings->getPersonSameAs());

		$person = SchemaOrgBuilder::createPerson($personInfo);

		/**
		 * Filters the final properties of the Person schema
		 * describing the person behind the blog
		 *
		 * @since WP AbNet 1.0
		 *
		 * @param array            $properties The schema properties, including @context and @type.
		 * @param ThemeHookContext $context    The theme hook context.
		 */
		$this->_schemas[] = $this->_filterSchemaProperties($person,
			'wp_abnet_schema_org_properties_person',
			$this->_context);
	}

	/**
	 * Get the JSON-LD node identifier of the person schema
	 *
	 * @return string
	 */
	private function _getPersonId(): string {
		$settings = $this->_context->getBlogSettings();
		return trailingslashit($settings['blog_url']) . '#author';
	}

	/**
	 * Pass the properties of a schema through the given filter
	 * and rebuild the schema from the result
	 *
	 * @param SchemaOrg $schema   The schema to filter.
	 * @param string    $hookName The filter name.
	 * @param mixed     ...$args  Additional filter arguments.
	 * @return SchemaOrg
	 */
	private function _filterSchemaProperties(SchemaOrg $schema, string $hookName, ...$args): SchemaOrg {
		$properties = apply_filters($hookName, $schema->toArray(), ...$args);

		if (!is_array($properties)) {
			return $schema;
		}

		$filteredSchema = new SchemaOrg($properties['@type'] ?? $schema->getType());
		$filteredSchema->setProperties($properties);

		return $filteredSchema;
	}

	/**
	 * Add breadcrumb schema
	 *
	 * @return void
	 */
	private function _addBreadcrumbSchema(): void {
		$breadcrumbs = $this->_generateBreadcrumbs();

		if (empty($breadcrumbs)) {
			return;
		}

		$breadcrumbList = SchemaOrgBuilder::createBreadcrumbList($breadcrumbs);

		/**
		 * Filters the final properties of the BreadcrumbList schema
		 *
		 * @since WP AbNet 1.0
		 *
		 * @param array                $properties  The schema properties, including @context and @type.
		 * @param BreadcrumbItemInfo[] $breadcrumbs The breadcrumb items the schema was built from.
		 * @param ThemeHookContext     $context     The theme hook context.
		 */
		$this->_schemas[] = $this->_filterSchemaProperties($breadcrumbList,
			'wp_abnet_schema_org_properties_breadcrumbs',
			$breadcrumbs,
			$this->_context);
	}

	/**
	 * Generate breadcrumb items for current page
	 *
	 * @return BreadcrumbItemInfo[]
	 */
	private function _generateBreadcrumbs(): array {
		$breadcrumbs = array(
			new BreadcrumbItemInfo('Home', get_home_url()),
		);

		if ($this->_context->isSingle() || $this->_context->isPage()) {
			$post = $this->_context->currentPost();
			if ($post) {
				// Add parent pages if it's a page
				if ($this->_context->isPage() && $post->post_parent) {
					$parent_id = $post->post_parent;
					while ($parent_id) {
						$parent = get_post($parent_id);
						if (!$parent) {
							break;
						}

						array_splice(
							$breadcrumbs,
							1,
							0,
							array(
								new BreadcrumbItemInfo($parent->post_title, get_permalink($parent)),
							)
						);

						$parent_id = $parent->post_parent;
					}
				}

				// Add current page (no link for current)
				$breadcrumbs[] = new BreadcrumbItemInfo($post->post_title, get_permalink($post));
			}
		} elseif ($this->_context->isArchive()) {
			$title = $this->_context->getArchiveTitle();
			if ($title) {
				$breadcrumbs[] = new BreadcrumbItemInfo($title, $this->_context->getArchiveUrl());
			}
		}

		return count($breadcrumbs) > 1 ? $breadcrumbs : array();
	}

	/**
	 * Add post/page schema
	 *
	 * @return void
	 */
	private function _addPostSchema(): void {
		$post = $this->_context->currentPost();

		if (!$post) {
			return;
		}

		if ($this->_isAboutPage($post)) {
			$this->_addProfilePageSchema($post);
			return;
		}

		// Get image
		$imageUrl = $this->_getPostImageUrl($post);
		if (!$imageUrl) {
			return; // Require image for structured data
		}

		// Get description
		$description = $this->_computePostDescription($post);
		if (!$description) {
			return; // Require description
		}

		$author_name = get_the_author_meta('display_name', $post->post_author);
		$isWrittenByPerson = $this->_isWrittenByPerson($post);
		$datePublished = mysql2date(\DateTime::ATOM, $post->post_date_gmt, false);
		$dateModified = $post->post_modified_gmt !== $post->post_date_gmt
			? mysql2date(\DateTime::ATOM, $post->post_modified_gmt, false)
			: '';

		$articleInfo = new ArticleInfo(
			$post->post_title,
			$description,
			$imageUrl,
			$isWrittenByPerson
				? $this->_metaSettings->getPersonName()
				: ($author_name ?: get_bloginfo('name')),
			$datePublished,
			$dateModified,
			id: get_permalink($post) . '#article',
			publisherId: $this->_getPersonId(),
			authorId: $isWrittenByPerson ? $this->_getPersonId() : '',
			authorUrl: $isWrittenByPerson ? $this->_metaSettings->getAboutPageUrl() : ''
		);

		// Determine article type
		$type = $this->_context->isPage() ? 'BlogPosting' : 'BlogPosting';

		// Use builder based on type
		if ('BlogPosting' === $type) {
			$articleSchema = SchemaOrgBuilder::createBlogPosting($articleInfo);
		} else {
			$articleSchema = SchemaOrgBuilder::createArticle($articleInfo);
		}

		/**
		 * Filters the final properties of the BlogPosting schema
		 *
		 * @since WP AbNet 1.0
		 *
		 * @param array            $properties The schema properties, including @context and @type.
		 * @param \WP_Post         $post       The current post.
		 * @param ThemeHookContext $context    The theme hook context.
		 */
		$this->_schemas[] = $this->_filterSchemaProperties($articleSchema,
			'wp_abnet_schema_org_properties_blog_posting',
			$post,
			$this->_context);
	}

	/**
	 * Check whether the post is the configured about page
	 *
	 * @param \WP_Post $post The post object.
	 * @return bool
	 */
	private function _isAboutPage(\WP_Post $post): bool {
		$aboutPageId = $this->_metaSettings->getAboutPageId();
		return $aboutPageId > 0 && $post->ID === $aboutPageId;
	}

	/**
	 * Check whether the post was written by the user represented by the person schema
	 *
	 * @param \WP_Post $post The post object.
	 * @return bool
	 */
	private function _isWrittenByPerson(\WP_Post $post): bool {
		$personUserId = $this->_metaSettings->getPersonUserId();
		return $personUserId > 0 && (int)$post->post_author === $personUserId;
	}

	/**
	 * Add the profile page schema for the about page
	 *
	 * @param \WP_Post $post The about page.
	 * @return void
	 */
	private function _addProfilePageSchema(\WP_Post $post): void {
		$permalink = get_permalink($post);
		$dateModified = $post->post_modified_gmt !== $post->post_date_gmt
			? mysql2date(\DateTime::ATOM, $post->post_modified_gmt, false)
			: '';

		$profilePageInfo = new ProfilePageInfo(
			$post->post_title,
			$permalink,
			$this->_getPersonId(),
			mysql2date(\DateTime::ATOM, $post->post_date_gmt, false),
			$dateModified,
			id: $permalink . '#profilepage'
		);

		$profilePage = SchemaOrgBuilder::createProfilePage($profilePageInfo);

		/**
		 * Filters the final properties of the ProfilePage schema of the about page
		 *
		 * @since WP AbNet 1.0
		 *
		 * @param array            $properties The schema properties, including @context and @type.
		 * @param \WP_Post         $post       The about page.
		 * @param ThemeHookContext $context    The theme hook context.
		 */
		$this->_schemas[] = $this->_filterSchemaProperties($profilePage,
			'wp_abnet_schema_org_properties_profile_page',
			$post,
			$this->_context);
	}

	/**
	 * Add archive schema
	 *
	 * @return void
	 */
	private function _addArchiveSchema(): void {
		$settings = $this->_context->getBlogSettings();
		$title = $this->_context->getArchiveTitle();

		if (!$title) {
			$title = $settings['blog_name'];
		}

		$webPageInfo = new WebPageInfo(
			$title,
			$settings['blog_description'],
			$this->_context->getArchiveUrl()
		);

		$webPage = SchemaOrgBuilder::createWebPage($webPageInfo);

		$this->_schemas[] = $webPage;
	}

	/**
	 * Add website schema for front page
	 *
	 * Google reads the site name shown in search results
	 * from this node, on the front page only.
	 *
	 * @return void
	 */
	private function _addWebSiteSchema(): void {
		$settings = $this->_context->getBlogSettings();
		$description = $this->_metaSettings->getBlogMetaDescription();

		$webSiteInfo = new WebSiteInfo(
			$this->_metaSettings->getSiteName(),
			trailingslashit($settings['blog_url']),
			$description !== '' ? $description : $settings['blog_description'],
			$this->_metaSettings->getSiteAlternateName(),
			$this->_getWebSiteId(),
			$this->_getPersonId(),
			$this->_getWebSiteLanguage()
		);

		$website = SchemaOrgBuilder::createWebSite($webSiteInfo);

		/**
		 * Filters the final properties of the WebSite schema of the front page
		 *
		 * @since WP AbNet 1.0
		 *
		 * @param array            $properties The schema properties, including @context and @type.
		 * @param ThemeHookContext $context    The theme hook context.
		 */
		$this->_schemas[] = $this->_filterSchemaProperties($website,
			'wp_abnet_schema_org_properties_website',
			$this->_context);
	}

	/**
	 * Get the JSON-LD node identifier of the website schema
	 *
	 * @return string
	 */
	private function _getWebSiteId(): string {
		$settings = $this->_context->getBlogSettings();
		return trailingslashit($settings['blog_url']) . '#website';
	}

	private function _getWebSiteLanguage(): string {
		$locale = get_locale();
		if (empty($locale)) {
			return apply_filters('abnet_schema_org_website_default_language', 
				'en-US', 
				$this->_context);
		} else {
			return str_replace('_', '-', $locale);
		}
	}

	/**
	 * Get post image URL (featured or first attached)
	 *
	 * @param \WP_Post $post The post object.
	 * @return string|false
	 */
	private function _getPostImageUrl(\WP_Post $post) {
		$imageId = get_post_thumbnail_id($post);
		if ($imageId) {
			return wp_get_attachment_url($imageId);
		}

		// Fall back to first attached image
		$attachments = get_children(array(
			'post_parent' => $post->ID,
			'post_type' => 'attachment',
			'post_mime_type' => 'image',
			'orderby' => 'menu_order ID',
			'order' => 'ASC',
			'numberposts' => 1,
		));

		if (!empty($attachments)) {
			$attachment = reset($attachments);
			return wp_get_attachment_url($attachment->ID);
		}

		return false;
	}

	/**
	 * Get post description
	 *
	 * @param \WP_Post $post The post object.
	 * @return string
	 */
	private function _computePostDescription(\WP_Post $post): string {
		if (!empty($post->post_excerpt)) {
			return wp_strip_all_tags($post->post_excerpt);
		}

		if (!empty($post->post_content)) {
			return wp_strip_all_tags(
				wp_trim_words($post->post_content, 30)
			);
		}

		return '';
	}

	/**
	 * Add a schema object
	 *
	 * @param SchemaOrg $schema The schema object to add.
	 * @return self
	 */
	public function addSchema(SchemaOrg $schema): self {
		$this->_schemas[] = $schema;
		return $this;
	}

	/**
	 * Get all schemas
	 *
	 * @return SchemaOrg[]
	 */
	public function getSchemas(): array {
		return $this->_schemas;
	}

	/**
	 * Render all schemas as HTML script tags
	 *
	 * @return string
	 */
	public function render(): string {
		$html = '';

		foreach ($this->_schemas as $schema) {
			$html .= $schema->render() . "\n";
		}

		return $html;
	}
}
