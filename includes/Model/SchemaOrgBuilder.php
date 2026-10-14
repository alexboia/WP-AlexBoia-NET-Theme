<?php
/**
 * Schema.org builder for common types
 *
 * Provides factory methods to create strongly typed schema.org objects
 * for common content types.
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 */

namespace AbNetTheme\Model;

use AbNetTheme\Model\SchemaOrg\ArticleInfo;
use AbNetTheme\Model\SchemaOrg\BreadcrumbItemInfo;
use AbNetTheme\Model\SchemaOrg\LocalBusinessInfo;
use AbNetTheme\Model\SchemaOrg\OrganizationInfo;
use AbNetTheme\Model\SchemaOrg\PersonInfo;
use AbNetTheme\Model\SchemaOrg\ProductInfo;
use AbNetTheme\Model\SchemaOrg\RecipeInfo;
use AbNetTheme\Model\SchemaOrg\WebPageInfo;

if (!defined('ABSPATH')) {
	exit;
}

class SchemaOrgBuilder {
	/**
	 * Create an Article schema
	 *
	 * @param ArticleInfo $articleInfo The article information.
	 * @return SchemaOrg
	 */
	public static function createArticle(ArticleInfo $articleInfo): SchemaOrg {
		$article = new SchemaOrg('Article');

		if (!empty($articleInfo->id)) {
			$article->setProperty('@id', $articleInfo->id);
		}

		$article->setProperty('headline', $articleInfo->title);
		$article->setProperty('description', $articleInfo->content);
		$article->setProperty('image', $articleInfo->imageUrl);
		$article->setProperty('datePublished', $articleInfo->datePublished);

		if (!empty($articleInfo->dateModified)) {
			$article->setProperty('dateModified', $articleInfo->dateModified);
		}

		// Author
		$author = self::createPerson(new PersonInfo($articleInfo->authorName));
		$article->setProperty('author', $author);

		// Publisher: reference an organization node emitted separately,
		//	or embed one built from the blog information
		if (!empty($articleInfo->publisherId)) {
			$article->setProperty('publisher', array('@id' => $articleInfo->publisherId));
		} else {
			$publisherInfo = new OrganizationInfo(
				get_bloginfo('name'),
				get_bloginfo('description'),
				home_url());

			$publisher = self::createOrganization($publisherInfo);
			$article->setProperty('publisher', $publisher);
		}

		return $article;
	}

	/**
	 * Create an Organization schema
	 *
	 * @return SchemaOrg
	 */
	public static function createOrganization(OrganizationInfo $organizationInfo): SchemaOrg {
		$org = new SchemaOrg('Organization');

		if (!empty($organizationInfo->id)) {
			$org->setProperty('@id', $organizationInfo->id);
		}

		$org->setProperty('name', $organizationInfo->name);

		if (!empty($organizationInfo->description)) {
			$org->setProperty('description', $organizationInfo->description);
		}

		if (!empty($organizationInfo->url)) {
			$org->setProperty('url', $organizationInfo->url);
		}

		if (!empty($organizationInfo->logoUrl)) {
			$org->setProperty('logo', $organizationInfo->logoUrl);
		}

		return $org;
	}

	/**
	 * Create a Person schema
	 * @return SchemaOrg
	 */
	public static function createPerson(PersonInfo $personInfo): SchemaOrg {
		$person = new SchemaOrg('Person');

		$person->setProperty('name', $personInfo->name);

		if (!empty($personInfo->url)) {
			$person->setProperty('url', $personInfo->url);
		}

		if (!empty($personInfo->email)) {
			$person->setProperty('email', $personInfo->email);
		}

		return $person;
	}

	/**
	 * Create a BreadcrumbList schema
	 *
	 * @param BreadcrumbItemInfo[] $breadcrumbs The breadcrumb items, in order.
	 * @return SchemaOrg
	 */
	public static function createBreadcrumbList(array $breadcrumbs): SchemaOrg {
		$breadcrumbList = new SchemaOrg('BreadcrumbList');

		$items = array();
		foreach (array_values($breadcrumbs) as $index => $breadcrumb) {
			$item = new SchemaOrg('ListItem');
			$item->setProperty('position', $index + 1);
			$item->setProperty('name', $breadcrumb->name);
			$item->setProperty('item', $breadcrumb->url);
			$items[] = $item;
		}

		$breadcrumbList->setProperty('itemListElement', $items);

		return $breadcrumbList;
	}

	/**
	 * Create a LocalBusiness schema
	 *
	 * @param LocalBusinessInfo $businessInfo The business information.
	 * @return SchemaOrg
	 */
	public static function createLocalBusiness(LocalBusinessInfo $businessInfo): SchemaOrg {
		$business = new SchemaOrg($businessInfo->type);

		$business->setProperty('name', $businessInfo->name);
		$business->setProperty('url', $businessInfo->url);

		if (!empty($businessInfo->phone)) {
			$business->setProperty('telephone', $businessInfo->phone);
		}

		if (!empty($businessInfo->address)) {
			$addressSchema = new SchemaOrg('PostalAddress');
			$addressSchema->setProperties($businessInfo->address);
			$business->setProperty('address', $addressSchema);
		}

		return $business;
	}

	/**
	 * Create a NewsArticle schema
	 *
	 * @param ArticleInfo $articleInfo The article information.
	 * @return SchemaOrg
	 */
	public static function createNewsArticle(ArticleInfo $articleInfo): SchemaOrg {
		$article = self::createArticle($articleInfo);

		// Change type to NewsArticle
		$article->setProperty('@type', 'NewsArticle');

		return $article;
	}

	/**
	 * Create a BlogPosting schema
	 *
	 * @param ArticleInfo $articleInfo The post information.
	 * @return SchemaOrg
	 */
	public static function createBlogPosting(ArticleInfo $articleInfo): SchemaOrg {
		$post = self::createArticle($articleInfo);

		// Change type to BlogPosting
		$post->setProperty('@type', 'BlogPosting');

		return $post;
	}

	/**
	 * Create a WebPage schema
	 *
	 * @param WebPageInfo $webPageInfo The page information.
	 * @return SchemaOrg
	 */
	public static function createWebPage(WebPageInfo $webPageInfo): SchemaOrg {
		$page = new SchemaOrg('WebPage');

		$page->setProperty('name', $webPageInfo->name);

		if (!empty($webPageInfo->description)) {
			$page->setProperty('description', $webPageInfo->description);
		}

		if (!empty($webPageInfo->url)) {
			$page->setProperty('url', $webPageInfo->url);
		}

		return $page;
	}

	/**
	 * Create a Product schema
	 *
	 * @param ProductInfo $productInfo The product information.
	 * @return SchemaOrg
	 */
	public static function createProduct(ProductInfo $productInfo): SchemaOrg {
		$product = new SchemaOrg('Product');

		$product->setProperty('name', $productInfo->name);
		$product->setProperty('description', $productInfo->description);
		$product->setProperty('image', $productInfo->imageUrl);

		if (!empty($productInfo->price) && !empty($productInfo->currency)) {
			$offer = new SchemaOrg('Offer');
			$offer->setProperty('price', $productInfo->price);
			$offer->setProperty('priceCurrency', $productInfo->currency);
			$product->setProperty('offers', $offer);
		}

		if ($productInfo->rating > 0) {
			$aggregateRating = new SchemaOrg('AggregateRating');
			$aggregateRating->setProperty('ratingValue', $productInfo->rating);
			$aggregateRating->setProperty('reviewCount', $productInfo->reviewCount);
			$product->setProperty('aggregateRating', $aggregateRating);
		}

		return $product;
	}

	/**
	 * Create a Recipe schema
	 *
	 * @param RecipeInfo $recipeInfo The recipe information.
	 * @return SchemaOrg
	 */
	public static function createRecipe(RecipeInfo $recipeInfo): SchemaOrg {
		$recipe = new SchemaOrg('Recipe');

		$recipe->setProperty('name', $recipeInfo->name);
		$recipe->setProperty('description', $recipeInfo->description);
		$recipe->setProperty('image', $recipeInfo->imageUrl);
		$recipe->setProperty('prepTime', $recipeInfo->prepTime);
		$recipe->setProperty('cookTime', $recipeInfo->cookTime);
		$recipe->setProperty('totalTime', $recipeInfo->totalTime);
		$recipe->setProperty('recipeYield', $recipeInfo->yield);

		if (!empty($recipeInfo->ingredients)) {
			$recipe->setProperty('recipeIngredient', $recipeInfo->ingredients);
		}

		if (!empty($recipeInfo->instructions)) {
			$instructionItems = array();
			foreach (array_values($recipeInfo->instructions) as $index => $instruction) {
				$item = new SchemaOrg('HowToStep');
				$item->setProperty('position', $index + 1);
				$item->setProperty('text', $instruction);
				$instructionItems[] = $item;
			}
			$recipe->setProperty('recipeInstructions', $instructionItems);
		}

		return $recipe;
	}
}
