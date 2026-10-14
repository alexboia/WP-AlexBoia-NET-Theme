# Schema.org Structured Data Implementation

A comprehensive, strongly typed system for generating schema.org structured data (JSON-LD) in the WP-AbNet theme.

## Components

### 1. SchemaOrg Class
Base class for creating any schema.org type.

**Features:**
- Strongly typed with PHP 8.2+ type hints
- Automatic property sanitization
- Chainable method calls
- JSON-LD and HTML script tag output

**Example:**
```php
use AbNetTheme\Model\SchemaOrg;

$org = new SchemaOrg('Organization');
$org
    ->setProperty('name', 'My Company')
    ->setProperty('url', 'https://example.com')
    ->setProperty('description', 'We make awesome stuff');

echo $org->render();
```

### 2. SchemaOrgBuilder Class
Static factory methods to create common schema.org types.
Each method takes a single immutable info object (namespace `AbNetTheme\Model\SchemaOrg`) holding its arguments.

**Supported Types:**
- `createArticle(ArticleInfo)` - Generic Article
- `createNewsArticle(ArticleInfo)` - News Article
- `createBlogPosting(ArticleInfo)` - Blog Post
- `createOrganization(OrganizationInfo)` - Organization
- `createPerson(PersonInfo)` - Person
- `createLocalBusiness(LocalBusinessInfo)` - Local Business
- `createBreadcrumbList(BreadcrumbItemInfo[])` - Breadcrumb Navigation
- `createWebPage(WebPageInfo)` - Web Page
- `createProduct(ProductInfo)` - Product with ratings
- `createRecipe(RecipeInfo)` - Recipe with instructions

**Example:**
```php
use AbNetTheme\Model\SchemaOrg\ArticleInfo;
use AbNetTheme\Model\SchemaOrgBuilder;

$article = SchemaOrgBuilder::createBlogPosting( new ArticleInfo(
    'My Article Title',
    'Article description...',
    'https://example.com/image.jpg',
    'Author Name',
    '2024-01-15T10:00:00Z',
    '2024-01-16T14:30:00Z'
) );

echo $article->render();
```

### 3. SchemaOrgManager Class
Automatically generates appropriate schemas based on page context.

**Features:**
- Auto-detects current page type (single, archive, home)
- Generates breadcrumbs automatically
- Includes organization schema on all pages
- Integrates with `ThemeHookContext`
- Filterable via `abnet_schema_org` hook

**Example:**
```php
use AbNetTheme\SchemaOrgManager;
use AbNetTheme\Model\ThemeHookContext;

$context = new ThemeHookContext();
$schemaManager = new SchemaOrgManager($context);

echo $schemaManager->render();
```

### 4. ThemeHookContext Class
Provides all necessary context information for schema generation.

**Available Methods:**
- `isSingle()` - Is single post/page
- `isPage()` - Is page
- `isArchive()` - Is archive
- `isCategory()` - Is category archive
- `isTag()` - Is tag archive
- `isAuthor()` - Is author archive
- `isFrontPage()` - Is front page
- `currentPost()` - Get current post object
- `getQuery()` - Get WP_Query object
- `getBlogSettings()` - Get blog name, URL, description, language
- `getArchiveTitle()` - Get archive title
- `getArchiveUrl()` - Get archive URL (category, tag and author archives)

## Integration with Theme

The schema.org system is automatically integrated into the theme:

1. **In `functions-meta.php`:**
   - `abnet_get_schema_org()` function creates and filters schemas
   - `abnet_output_schema_org()` outputs schemas to `wp_head` at priority 2

2. **Schema output on every page:**
   - Organization schema with blog info
   - Breadcrumb schema (when applicable)
   - Article schema (for posts/pages)
   - Archive schema (for category, tag, author pages)
   - Website schema (for front page)

## Customization via Filters

| Filter | Value | Extra arguments |
| --- | --- | --- |
| `abnet_schema_org` | `SchemaOrgManager` | `ThemeHookContext $context` |
| `wp_abnet_schema_org_properties_organization` | Final Organization properties (`array`) | `ThemeHookContext $context` |
| `wp_abnet_schema_org_properties_breadcrumbs` | Final BreadcrumbList properties (`array`) | `BreadcrumbItemInfo[] $breadcrumbs`, `ThemeHookContext $context` |
| `wp_abnet_schema_org_properties_blog_posting` | Final BlogPosting properties (`array`) | `WP_Post $post`, `ThemeHookContext $context` |

The `wp_abnet_schema_org_properties_*` filters receive the complete property array of a schema
(including `@context` and `@type`) right before it is added to the manager. The schema is rebuilt
from the returned array, so properties can be added, changed or removed. Returning anything other
than an array keeps the original schema.

### Modify the properties of a generated schema:
```php
add_filter('wp_abnet_schema_org_properties_blog_posting', function(array $properties, WP_Post $post, $context) {
    $properties['keywords'] = implode(', ', wp_get_post_tags($post->ID, array('fields' => 'names')));
    return $properties;
}, 10, 3);
```

### Add schemas:
```php
add_filter('abnet_schema_org', function($schemaManager, $context) {
    // Add custom schema for specific post types
    if (is_singular('product')) {
        $productSchema = SchemaOrgBuilder::createProduct( new ProductInfo(
            'Product Name',
            'Description',
            'image-url.jpg',
            '99.99',
            'USD',
            4.5,
            120
        ) );
        $schemaManager->addSchema($productSchema);
    }

    return $schemaManager;
}, 10, 2);
```

## Nested Objects

Schemas can contain nested objects:

```php
$person = SchemaOrgBuilder::createPerson( new PersonInfo('John Doe') );

$org = new SchemaOrg('Organization');
$org
    ->setProperty('name', 'My Company')
    ->setProperty('founder', $person);  // Pass SchemaOrg object

echo $org->render();
```

## Node Identifiers (@id)

Schemas that other nodes refer to get a stable `@id`, so they can be referenced instead of being repeated:

| Node | `@id` |
| --- | --- |
| Organization | `<home url>/#organization` |
| BlogPosting | `<permalink>#article` |

The BlogPosting refers to the organization as its publisher with `{"@id": "<home url>/#organization"}`.
`OrganizationInfo::$id`, `ArticleInfo::$id` and `ArticleInfo::$publisherId` control this when using the builder directly;
without `publisherId`, the article embeds a publisher built from the blog information.

### Linking nodes from plug-ins

The theme does not know about other structured data on the page. Plug-ins that output their own
JSON-LD nodes can link them to the theme's nodes by hooking into the `wp_abnet_schema_org_properties_*` filters
and adding a reference by `@id`, for instance:

```php
add_filter('wp_abnet_schema_org_properties_blog_posting', function(array $properties, WP_Post $post) {
    $properties['contentLocation'] = array('@id' => get_permalink($post) . '#my-plugin-place');
    return $properties;
}, 10, 2);
```

## Output Formats

### JSON-LD (for embedding in HTML)
```php
$schema = SchemaOrgBuilder::createOrganization( new OrganizationInfo(
    'Company Name',
    'Description',
    'https://example.com'
) );

echo $schema->render();
// Output: <script type="application/ld+json">{...}</script>
```

### Array representation
```php
$schemaArray = $schema->toArray();
// Use for API responses, custom processing, etc.
```

### JSON string
```php
$schemaJson = $schema->toJsonLd();
// Use for storing, logging, etc.
```

## Property Handling

Properties are automatically:
- **Sanitized** - Text fields are sanitized via `sanitize_text_field()`
- **Filtered** - Empty strings and null values are skipped
- **Escaped** - When rendered as HTML, values are properly escaped

## Usage Tips

1. **Use the builder when possible** - It handles common cases and provides better type hints
2. **Chain method calls** - More readable and concise code
3. **Pass SchemaOrg objects directly** - They're automatically converted to arrays
4. **Use filters for customization** - Don't modify SchemaOrgManager directly
5. **Check context before adding schemas** - Use `ThemeHookContext` methods

## Examples

See `SCHEMA_USAGE_EXAMPLES.php` for 15 comprehensive examples including:
- Basic organization schema
- Article and blog posting
- Nested objects
- Breadcrumbs
- Local business with address
- Products with ratings
- Recipes with instructions
- News articles
- Web pages
- Multiple properties at once
- Using in filters
- Custom extensions
- Array/JSON output
- Method chaining

## Files Structure

```
includes/
├── OGMetaTags.php                 # OG meta generation
├── SchemaOrgManager.php           # Auto-generation manager
└── Model/
    ├── MetaTag.php                # Open Graph meta tags
    ├── ThemeHookContext.php       # Context provider
    ├── SchemaOrg.php              # Base schema class
    ├── SchemaOrgBuilder.php       # Factory for common types
    └── SchemaOrg/                 # Info objects (builder arguments)
        ├── ArticleInfo.php
        ├── BreadcrumbItemInfo.php
        ├── LocalBusinessInfo.php
        ├── OrganizationInfo.php
        ├── PersonInfo.php
        ├── ProductInfo.php
        ├── RecipeInfo.php
        └── WebPageInfo.php
docs/
├── SCHEMA-README.md               # This file
└── SCHEMA_USAGE_EXAMPLES.php      # Usage examples
```

## SEO Benefits

Schema.org markup helps search engines understand:
- Content type and structure
- Publication dates
- Authors
- Images
- Breadcrumb navigation
- Organization information
- Product details and ratings
- Recipe ingredients and instructions
- And much more

This can result in:
- Rich snippets in search results
- Better SERP positioning
- Enhanced knowledge panel display
- Voice search optimization
