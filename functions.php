<?php
/**
 * Functions and definitions.
 *
 * Sets up the theme and provides some helper functions, which are used
 * in the theme as custom template tags. Others are attached to action and
 * filter hooks in WordPress to change core functionality.
 *
 * When using a child theme (see http://codex.wordpress.org/Theme_Development and
 * http://codex.wordpress.org/Child_Themes), you can override certain functions
 * (those wrapped in a function_exists() call) by defining them first in your child theme's
 * functions.php file. The child theme's functions.php file is included before the parent
 * theme's file, so the child theme functions would be used.
 *
 * Functions that are not pluggable (not wrapped in function_exists()) are instead attached
 * to a filter or action hook.
 *
 * For more information on hooks, actions, and filters, see http://codex.wordpress.org/Plugin_API.
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 */

use AbNetTheme\ViewModel\CommentViewModel;
use AbNetTheme\ViewModel\ContentNavViewModel;
use AbNetTheme\ViewModel\EntryMetaDateViewModel;
use AbNetTheme\ViewModel\EntryMetaViewModel;
use AbNetTheme\ViewModel\ViewModel;

require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/functions-meta.php';
require_once __DIR__ . '/functions-admin.php';
require_once __DIR__ . '/functions-seo.php';
require_once __DIR__ . '/functions-settings.php';
require_once __DIR__ . '/functions-plugin-connectors.php';

/**
 * Render a view from the theme's views directory.
 * The view receives its explicit view model as $data.
 *
 * @since WP AbNet 1.0
 *
 * @param string         $viewName The view file name, without the .php extension.
 * @param ViewModel|null $data     The view model.
 */
function abnet_render_view(string $viewName, ?ViewModel $data = null): void {
	$viewFile = __DIR__ . '/views/' . $viewName . '.php';
	if (is_readable($viewFile)) {
		require $viewFile;
	}
}

/**
 * Render a view from the theme's views directory and return its output.
 *
 * @since WP AbNet 1.0
 *
 * @param string         $viewName The view file name, without the .php extension.
 * @param ViewModel|null $data     The view model.
 * @return string The rendered view.
 */
function abnet_get_rendered_view(string $viewName, ?ViewModel $data = null): string {
	ob_start();
	abnet_render_view($viewName, $data);
	return (string)ob_get_clean();
}

/**
 * Sets up the content width value based on the theme's design and stylesheet.
 */
if (!isset($content_width)) {
	$content_width = 625;
}

/**
 * Sets up theme defaults and registers the various WordPress features that
 * Twenty Twelve supports.
 *
 * @uses load_theme_textdomain() For translation/localization support.
 * @uses add_editor_style() To add a Visual Editor stylesheet.
 * @uses add_theme_support() To add support for post thumbnails, automatic feed links,
 * 	custom background, and post formats.
 * @uses register_nav_menu() To add support for navigation menus.
 * @uses set_post_thumbnail_size() To set a custom post thumbnail size.
 *
 * @since WP AbNet 1.0
 */
function abnet_setup(): void {
	/*
	 * Makes theme available for translation.
	 *
	 * Translations can be added to the /languages/ directory.
	 * If you're building a theme based on Twenty Twelve, use a find and replace
	 * to change 'abnet' to the name of your theme in all the template files.
	 */
	load_theme_textdomain('abnet', get_template_directory() . '/languages');

	// This theme styles the visual editor with editor-style.css to match the theme style.
	add_editor_style();

	// Adds RSS feed links to <head> for posts and comments.
	add_theme_support('automatic-feed-links');

	// This theme supports a variety of post formats.
	add_theme_support('post-formats', array('aside', 'image', 'link', 'quote', 'status'));

	// This theme uses wp_nav_menu() in one location.
	register_nav_menu('primary', __('Primary Menu', 'abnet'));

	/*
	 * This theme supports custom background color and image, and here
	 * we also set up the default background color.
	 */
	add_theme_support('custom-background', array(
		'default-color' => 'e6e6e6',
	));

	// This theme uses a custom image size for featured images, displayed on "standard" posts.
	add_theme_support('post-thumbnails');
	set_post_thumbnail_size(1024, 9999); // Unlimited height, soft crop

	remove_theme_support('widgets-block-editor');
}

add_action('after_setup_theme', 'abnet_setup');

/**
 * Returns the Google font stylesheet URL if available.
 * Only the editor uses it (see editor-style.css); the front end does not.
 *
 * The use of Open Sans by default is localized. For languages that use
 * characters not supported by the font, the font can be disabled.
 *
 * @since WP AbNet 1.2
 *
 * @return string Font stylesheet or empty string if disabled.
 */
function abnet_get_font_url(): string {
	$fontUrl = '';

	/* translators: If there are characters in your language that are not supported
	 by Open Sans, translate this to 'off'. Do not translate into your own language. */
	if ('off' !== _x('on', 'Open Sans font: on or off', 'abnet')) {
		$subsets = 'latin,latin-ext';

		/* translators: To add an additional Open Sans character subset specific to your language, translate
		 this to 'greek', 'cyrillic' or 'vietnamese'. Do not translate into your own language. */
		$subset = _x('no-subset', 'Open Sans font: add new subset (greek, cyrillic, vietnamese)', 'abnet');

		if ('cyrillic' == $subset) {
			$subsets .= ',cyrillic,cyrillic-ext';
		} elseif ('greek' == $subset) {
			$subsets .= ',greek,greek-ext';
		} elseif ('vietnamese' == $subset) {
			$subsets .= ',vietnamese';
		}

		$protocol = is_ssl() ? 'https' : 'http';
		$queryArgs = array(
			'family' => 'Open+Sans:400italic,700italic,400,700',
			'subset' => $subsets,
		);
		$fontUrl = add_query_arg($queryArgs, "$protocol://fonts.googleapis.com/css");
	}

	return $fontUrl;
}

/**
 * Retrieve the URI of the Genericicons font stylesheet definition
 */
function get_generic_icons_stylesheet_uri(): string {
	return get_stylesheet_directory_uri() . '/genericicons/genericons.css';
}

/**
 * Enqueues scripts and styles for front-end.
 *
 * @since WP AbNet 1.0
 */
function abnet_scripts_styles(): void {
	/*
	 * Adds JavaScript to pages with the comment form to support
	 * sites with threaded comments (when in use).
	 */
	if (is_singular() && comments_open() && get_option('thread_comments')) {
		wp_enqueue_script('comment-reply');
	}

	/*
	 * Adds JavaScript for handling the navigation menu hide-and-show behavior.
	 */
	wp_enqueue_script('abnet-navigation', get_template_directory_uri() . '/js/navigation.js', array(), '1.0', true);

	/*
	 * Loads embeds marked with .abnet-deferred-embed (e.g. the newsletter widget)
	 * only after the page has fully loaded.
	 */
	wp_enqueue_script('abnet-deferred-embeds', get_template_directory_uri() . '/js/deferred-embeds.js', array(), wp_get_theme()->get('Version'), array(
		'in_footer' => true,
		'strategy' => 'defer',
	));

	$version = wp_get_theme()->get('Version');

	/*
	 * Loads the self-hosted font faces. They are enqueued instead of being
	 * imported from style.css, so the browser can fetch them in parallel.
	 */
	$fontHandles = array();
	foreach (_abnet_font_faces() as $fontName) {
		$handle = 'abnet-font-' . $fontName;
		wp_enqueue_style($handle, get_template_directory_uri() . '/css/fonts/' . $fontName . '/index.css', array(), $version);
		$fontHandles[] = $handle;
	}

	/*
	 * Loads our main stylesheet. Dashicons are a dependency only where
	 * the templates use them, so a plug-in dequeuing them does not break those pages.
	 */
	$styleDeps = $fontHandles;
	if (_abnet_needs_dashicons()) {
		$styleDeps[] = 'dashicons';
	}

	wp_enqueue_style('abnet-style', get_stylesheet_uri(), $styleDeps, $version);
	wp_enqueue_style('genericicons-style', get_generic_icons_stylesheet_uri(), array(), $version);

	/*
	 * Loads the Internet Explorer specific stylesheet.
	 */
	//wp_enqueue_style('abnet-ie', get_template_directory_uri() . '/css/ie.css', array('abnet-style'), '20121010');
	//$wp_styles->add_data('abnet-ie', 'conditional', 'lt IE 9');
}

add_action('wp_enqueue_scripts', 'abnet_scripts_styles');

/**
 * The self-hosted font faces used by the theme, as folder names under css/fonts.
 *
 * @return string[]
 */
function _abnet_font_faces(): array {
	return array('noir', 'fonseca', 'inter');
}

/**
 * Whether the current page shows Dashicons from the theme's templates:
 * the previous/next arrows on single posts (single.php) and the reply links
 * of the comments (views/comment.php). The edit links are only shown to
 * logged-in users, whose admin bar already loads Dashicons.
 */
function _abnet_needs_dashicons(): bool {
	return is_single() || (is_singular() && comments_open());
}

/**
 * Preloads the font files needed to render the header and the body text,
 * so they do not wait for the font stylesheets to be parsed.
 *
 * @param array $resources The resources to preload.
 * @return array
 */
function abnet_preload_fonts(array $resources): array {
	$fontsUri = get_template_directory_uri() . '/css/fonts';
	$fontFiles = array(
		'/noir/noir-regular.woff2',
		'/fonseca/fonseca-regular.woff2',
		'/inter/v19/UcCO3FwrK3iLTeHuS_nVMrMxCp50SjIw2boKoduKmMEVuLyfAZ9hiA.woff2',
	);

	foreach ($fontFiles as $fontFile) {
		$resources[] = array(
			'href' => $fontsUri . $fontFile,
			'as' => 'font',
			'type' => 'font/woff2',
			'crossorigin' => 'anonymous',
		);
	}

	return $resources;
}

add_filter('wp_preload_resources', 'abnet_preload_fonts');

/**
 * Adds additional stylesheets to the TinyMCE editor if needed.
 *
 * @uses abnet_get_font_url() To get the Google Font stylesheet URL.
 *
 * @since WP AbNet 1.2
 *
 * @param string|null $mceCss CSS path to load in TinyMCE.
 * @return string|null
 */
function abnet_mce_css(?string $mceCss): ?string {
	$fontUrl = abnet_get_font_url();

	if (empty($fontUrl)) {
		return $mceCss;
	}

	if (!empty($mceCss)) {
		$mceCss .= ',';
	}

	$mceCss .= esc_url_raw(str_replace(',', '%2C', $fontUrl));

	return $mceCss;
}

add_filter('mce_css', 'abnet_mce_css');

/**
 * Creates a nicely formatted and more specific title element text
 * for output in head of document, based on current view.
 *
 * @since WP AbNet 1.0
 *
 * @param string|null $title Default title text for current view.
 * @param string|null $sep   Optional separator.
 * @return string Filtered title.
 */
function abnet_wp_title(?string $title, ?string $sep): string {
	global $paged, $page;

	$title = (string)$title;
	if (is_feed()) {
		return $title;
	}

	// Add the site name.
	$title .= get_bloginfo('name');

	// Add the site description for the home/front page.
	$siteDescription = get_bloginfo('description', 'display');
	if ($siteDescription && (is_home() || is_front_page())) {
		$title = "$title $sep $siteDescription";
	}

	// Add a page number if necessary.
	if ($paged >= 2 || $page >= 2) {
		$title = "$title $sep " . sprintf(__('Page %s', 'abnet'), max($paged, $page));
	}

	return $title;
}

add_filter('wp_title', 'abnet_wp_title', 10, 2);

/**
 * Makes our wp_nav_menu() fallback -- wp_page_menu() -- show a home link.
 *
 * @since WP AbNet 1.0
 */
function abnet_page_menu_args(array $args): array {
	if (!isset($args['show_home'])) {
		$args['show_home'] = true;
	}
	return $args;
}

add_filter('wp_page_menu_args', 'abnet_page_menu_args');

/**
 * Registers our main widget area and the front page widget areas.
 *
 * @since WP AbNet 1.0
 */
function abnet_widgets_init(): void {
	register_sidebar(array(
		'name' => __('Main Sidebar', 'abnet'),
		'id' => 'sidebar-1',
		'description' => __('Appears on posts and pages except the optional Front Page template, which has its own widgets', 'abnet'),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	));

	register_sidebar(array(
		'name' => __('First Front Page Widget Area', 'abnet'),
		'id' => 'sidebar-2',
		'description' => __('Appears when using the optional Front Page template with a page set as Static Front Page', 'abnet'),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	));

	register_sidebar(array(
		'name' => __('Second Front Page Widget Area', 'abnet'),
		'id' => 'sidebar-3',
		'description' => __('Appears when using the optional Front Page template with a page set as Static Front Page', 'abnet'),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	));
}

add_action('widgets_init', 'abnet_widgets_init');

if (!function_exists('abnet_content_nav')):
	/**
	 * Displays navigation to next/previous pages when applicable.
	 *
	 * @since WP AbNet 1.0
	 *
	 * @param string $htmlId The id attribute of the navigation element.
	 */
	function abnet_content_nav(string $htmlId): void {
		global $wp_query;

		if ($wp_query->max_num_pages > 1) {
			abnet_render_view('content-nav', new ContentNavViewModel($htmlId));
		}
	}
endif;

if (!function_exists('abnet_comment')):
	/**
	 * Template for comments and pingbacks.
	 *
	 * To override this walker in a child theme without modifying the comments template
	 * simply create your own abnet_comment(), and that function will be used instead.
	 *
	 * Used as a callback by wp_list_comments() for displaying the comments.
	 *
	 * @since WP AbNet 1.0
	 *
	 * @param WP_Comment $comment The comment.
	 * @param array      $args    The wp_list_comments() arguments.
	 * @param int        $depth   The depth of the comment.
	 */
	function abnet_comment(WP_Comment $comment, array $args, int $depth): void {
		global $post;

		$GLOBALS['comment'] = $comment;

		$isByPostAuthor = $post instanceof WP_Post
			&& (int)$comment->user_id > 0
			&& (int)$comment->user_id === (int)$post->post_author;

		$viewModel = new CommentViewModel($comment, $args, $depth, $isByPostAuthor);

		switch ($comment->comment_type) {
			case 'pingback':
			case 'trackback':
				// Display trackbacks differently than normal comments.
				abnet_render_view('comment-pingback', $viewModel);
				break;
			default:
				abnet_render_view('comment', $viewModel);
				break;
		}
	}
endif;

if (!function_exists('abnet_entry_meta')):
	/**
	 * Prints HTML with meta information for current post: categories and tags.
	 *
	 * Create your own abnet_entry_meta() to override in a child theme.
	 *
	 * @since WP AbNet 1.0
	 */
	function abnet_entry_meta(): void {
		// Translators: used between list items, there is a space after the comma.
		$categoriesList = get_the_category_list(__(', ', 'abnet'));

		// Translators: used between list items, there is a space after the comma.
		$tagList = get_the_tag_list('', __(', ', 'abnet'));

		abnet_render_view('entry-meta', new EntryMetaViewModel(
			$categoriesList ? strtolower($categoriesList) : '',
			is_string($tagList) ? $tagList : ''
		));
	}
endif;

if (!function_exists('abnet_entry_meta_date')):
	/**
	 * Retrieves the formatted post date as a link
	 *
	 * @since WP AbNet 1.0
	 *
	 * @return string
	 */
	function abnet_entry_meta_date(): string {
		return abnet_get_rendered_view('entry-meta-date', new EntryMetaDateViewModel(
			(string)get_permalink(),
			(string)get_the_time(),
			(string)get_the_date('c'),
			(string)get_the_date()
		));
	}
endif;

/**
 * Extends the default WordPress body class to denote:
 * 1. Using a full-width layout, when no active widgets in the sidebar
 *    or full-width template.
 * 2. Front Page template: thumbnail in use and number of sidebars for
 *    widget areas.
 * 3. White or empty background color to change the layout and spacing.
 * 4. Single or multiple authors.
 *
 * @since WP AbNet 1.0
 *
 * @param array $classes Existing class values.
 * @return array Filtered class values.
 */
function abnet_body_class(array $classes): array {
	$backgroundColor = get_background_color();
	$backgroundImage = get_background_image();

	if (!is_active_sidebar('sidebar-1') || is_page_template('page-templates/full-width.php')) {
		$classes[] = 'full-width';
	}

	if (is_page_template('page-templates/front-page.php')) {
		$classes[] = 'template-front-page';
		if (has_post_thumbnail()) {
			$classes[] = 'has-post-thumbnail';
		}
		if (is_active_sidebar('sidebar-2') && is_active_sidebar('sidebar-3')) {
			$classes[] = 'two-sidebars';
		}
	}

	if (empty($backgroundImage)) {
		if (empty($backgroundColor)) {
			$classes[] = 'custom-background-empty';
		} elseif (in_array($backgroundColor, array('fff', 'ffffff'))) {
			$classes[] = 'custom-background-white';
		}
	}

	if (!is_multi_author()) {
		$classes[] = 'single-author';
	}

	return $classes;
}

add_filter('body_class', 'abnet_body_class');

/**
 * Adjusts content_width value for full-width and single image attachment
 * templates, and when there are no active widgets in the sidebar.
 */
function abnet_content_width(): void {
	if (is_page_template('page-templates/full-width.php')
		|| is_attachment()
		|| !is_active_sidebar('sidebar-1')) {
		global $content_width;
		//$content_width = 960;
		$content_width = 1010;
	}
}

add_action('template_redirect', 'abnet_content_width');

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 */
function abnet_customize_register(WP_Customize_Manager $wpCustomize): void {
	$wpCustomize->get_setting('blogname')->transport = 'postMessage';
	$wpCustomize->get_setting('blogdescription')->transport = 'postMessage';
	$wpCustomize->get_setting('header_textcolor')->transport = 'postMessage';
}

add_action('customize_register', 'abnet_customize_register');

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 *
 * @since WP AbNet 1.0
 */
function abnet_customize_preview_js(): void {
	wp_enqueue_script('abnet-customizer', get_template_directory_uri() . '/js/theme-customizer.js', array('customize-preview'), '20130301', true);
}

add_action('customize_preview_init', 'abnet_customize_preview_js');

/**
 * Replaces the core caption shortcodes with abnet_img_caption_shortcode()
 */
function abnet_customize_image_caption(): void {
	remove_shortcode('wp_caption');
	remove_shortcode('caption');
	add_shortcode('wp_caption', 'abnet_img_caption_shortcode');
	add_shortcode('caption', 'abnet_img_caption_shortcode');
}

/**
 * Renders the caption shortcode, sizing the caption text to the image width
 *
 * @param array|string $attr    The shortcode attributes (an empty string when there are none).
 * @param string|null  $content The shortcode content.
 * @return string
 */
function abnet_img_caption_shortcode(array|string $attr, ?string $content = ''): string {
	$attr = is_array($attr) ? $attr : array();
	$originalShortCodeContents = img_caption_shortcode($attr, (string)$content);
	if (empty($attr['width'])) {
		return $originalShortCodeContents;
	}

	$width = (int)$attr['width'];
	if ($width < 0) {
		return $originalShortCodeContents;
	}

	return str_ireplace(
		'class="wp-caption-text"',
		'class="wp-caption-text" style="width: ' . $width . 'px;"',
		$originalShortCodeContents
	);
}

add_action('wp_head', 'abnet_customize_image_caption');
