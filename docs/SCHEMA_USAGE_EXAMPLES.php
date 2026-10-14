<?php
/**
 * Schema.org Usage Examples
 *
 * This file demonstrates how to use the SchemaOrg and SchemaOrgBuilder classes
 * to generate structured data for search engines.
 *
 * @package WordPress
 * @subpackage WP_AbNet
 */

/*
 * BASIC USAGE EXAMPLES
 * ====================
 */

// 1. Create a simple schema with raw properties
// =============================================
use AbNetTheme\Model\SchemaOrg;
use AbNetTheme\Model\SchemaOrg\ArticleInfo;
use AbNetTheme\Model\SchemaOrg\BreadcrumbItemInfo;
use AbNetTheme\Model\SchemaOrg\LocalBusinessInfo;
use AbNetTheme\Model\SchemaOrg\OrganizationInfo;
use AbNetTheme\Model\SchemaOrg\PersonInfo;
use AbNetTheme\Model\SchemaOrg\ProductInfo;
use AbNetTheme\Model\SchemaOrg\RecipeInfo;
use AbNetTheme\Model\SchemaOrg\WebPageInfo;
use AbNetTheme\Model\SchemaOrgBuilder;

$organization = new SchemaOrg('Organization');
$organization
	->setProperty('name', 'My Company')
	->setProperty('url', 'https://example.com')
	->setProperty('description', 'We do amazing things');

echo $organization->render();
// Output: <script type="application/ld+json">{"@context":"https://schema.org","@type":"Organization","name":"My Company",...}</script>


// 2. Use the builder to create an article
// =======================================
$article = SchemaOrgBuilder::createArticle(new ArticleInfo(
	'How to Use Schema.org',                           // title
	'A comprehensive guide to using schema.org...',    // content
	'https://example.com/article-image.jpg',           // imageUrl
	'John Doe',                                         // authorName
	'2024-01-15T10:00:00Z',                           // datePublished
	'2024-01-16T14:30:00Z'                            // dateModified (optional)
));

echo $article->render();


// 3. Create a blog posting with all details
// =========================================
$blogPost = SchemaOrgBuilder::createBlogPosting(new ArticleInfo(
	'WordPress Theme Development Tips',
	'Here are some useful tips for developing WordPress themes...',
	'https://example.com/featured-image.jpg',
	'Jane Smith',
	'2024-01-20T09:00:00Z'
));

echo $blogPost->render();


// 4. Create nested schemas (person within organization)
// ====================================================
$person = SchemaOrgBuilder::createPerson(new PersonInfo(
	'John Developer',
	'https://johndeveloper.com',
	'john@example.com'
));

$org = new SchemaOrg('Organization');
$org
	->setProperty('name', 'Tech Company')
	->setProperty('founder', $person);  // Pass SchemaOrg object directly

echo $org->render();


// 5. Create a breadcrumb list
// ==========================
$breadcrumbs = SchemaOrgBuilder::createBreadcrumbList(array(
	new BreadcrumbItemInfo('Home', 'https://example.com'),
	new BreadcrumbItemInfo('Blog', 'https://example.com/blog'),
	new BreadcrumbItemInfo('Article', 'https://example.com/blog/article'),
));

echo $breadcrumbs->render();


// 6. Create a local business
// ==========================
$business = SchemaOrgBuilder::createLocalBusiness(new LocalBusinessInfo(
	'Coffee Shop Corner',
	'CoffeeShop',
	'https://coffeeshopcorner.com',
	'+1-555-0123',
	array(
		'streetAddress' => '123 Main St',
		'addressLocality' => 'Springfield',
		'addressRegion' => 'IL',
		'postalCode' => '62701',
		'addressCountry' => 'US',
	)
));

echo $business->render();


// 7. Create a product with rating
// ===============================
$product = SchemaOrgBuilder::createProduct(new ProductInfo(
	'Premium Theme for WordPress',
	'A beautiful and fast WordPress theme...',
	'https://example.com/theme-image.jpg',
	'99.99',      // price
	'USD',        // currency
	4.8,          // rating (0-5)
	142           // reviewCount
));

echo $product->render();


// 8. Create a recipe
// =================
$recipe = SchemaOrgBuilder::createRecipe(new RecipeInfo(
	'Chocolate Chip Cookies',
	'Classic homemade chocolate chip cookies...',
	'https://example.com/recipe-image.jpg',
	'PT15M',      // prepTime (15 minutes)
	'PT12M',      // cookTime (12 minutes)
	'PT27M',      // totalTime (27 minutes)
	24,           // yield (servings)
	array(
		'2 cups flour',
		'1 cup butter',
		'1 cup chocolate chips',
	),
	array(
		'Preheat oven to 375°F',
		'Mix butter and flour',
		'Add chocolate chips',
		'Bake for 12 minutes',
	)
));

echo $recipe->render();


// 9. Create a news article
// =======================
$newsArticle = SchemaOrgBuilder::createNewsArticle(new ArticleInfo(
	'Breaking: New Theme Released',
	'The highly anticipated theme is finally available...',
	'https://example.com/news-image.jpg',
	'Reporter Name',
	'2024-01-25T12:00:00Z'
));

echo $newsArticle->render();


// 10. Create a web page
// ====================
$webpage = SchemaOrgBuilder::createWebPage(new WebPageInfo(
	'Services',
	'Learn about our services and what we offer',
	'https://example.com/services'
));

echo $webpage->render();


// 11. Advanced: Set multiple properties at once
// ===========================================
$event = new SchemaOrg('Event');
$event->setProperties(array(
	'name' => 'WordCamp 2024',
	'description' => 'Annual WordPress conference',
	'startDate' => '2024-06-15T09:00:00Z',
	'endDate' => '2024-06-17T18:00:00Z',
	'location' => 'New York City',
));

echo $event->render();


// 12. Working with the ThemeHookContext in filters
// ================================================
add_filter('abnet_og_meta_tags', function($metaTags, $context) {
	// Check current page type
	if ($context->isSingle()) {
		// Add custom logic for single posts
	}

	if ($context->isCategory()) {
		// Add custom logic for category archives
	}

	return $metaTags;
}, 10, 2);


// 13. Extending with custom schemas
// ==================================
add_filter('abnet_schema_org', function($schemaManager, $context) {
	// Add a custom schema
	if (is_singular('product')) {
		$customSchema = SchemaOrgBuilder::createProduct(new ProductInfo(
			'Custom Product',
			'Description',
			'https://example.com/image.jpg',
			'19.99',
			'USD'
		));

		$schemaManager->addSchema($customSchema);
	}

	return $schemaManager;
}, 10, 2);


// 13b. Filtering the final properties of a generated schema
// ==========================================================
// Also available: wp_abnet_schema_org_properties_organization ($properties, $context)
// and wp_abnet_schema_org_properties_breadcrumbs ($properties, $breadcrumbs, $context).
add_filter('wp_abnet_schema_org_properties_blog_posting', function(array $properties, WP_Post $post, $context) {
	$properties['inLanguage'] = get_bloginfo('language');
	return $properties;
}, 10, 3);


// 13c. Referencing nodes by @id
// =============================
$publisher = SchemaOrgBuilder::createOrganization(new OrganizationInfo(
	'My Company',
	'',
	'https://example.com/',
	id: 'https://example.com/#organization'
));

$linkedArticle = SchemaOrgBuilder::createBlogPosting(new ArticleInfo(
	'Linked Article',
	'An article referencing its publisher by @id...',
	'https://example.com/image.jpg',
	'Jane Smith',
	'2024-01-20T09:00:00Z',
	id: 'https://example.com/linked-article/#article',
	publisherId: 'https://example.com/#organization'
));
// "publisher": {"@id": "https://example.com/#organization"}


// 14. Get array representation instead of HTML
// ============================================
$schema = SchemaOrgBuilder::createOrganization(new OrganizationInfo(
	'My Company',
	'We make awesome products',
	'https://example.com'
));

// Get as array
$schemaArray = $schema->toArray();
// Use for API, etc.

// Get as JSON string
$schemaJson = $schema->toJsonLd();


// 15. Chaining method calls
// =========================
$article = new SchemaOrg('NewsArticle');
$article
	->setProperty('headline', 'Breaking News')
	->setProperty('description', 'This is important...')
	->setProperty('image', 'https://example.com/image.jpg')
	->setProperty('author', SchemaOrgBuilder::createPerson(new PersonInfo('Reporter Name')));

echo $article->render();
