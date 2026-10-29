<?php
	/**
	 * The Header for our theme.
	 *
	 * Displays all of the <head> section and everything up till <div id="main">
	 *
	 * @package WordPress
	 * @subpackage WP_AbNet
	 * @since WP AbNet 1.0
	 */
	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: *');
	header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>" />
		<meta name="viewport" content="width=device-width" />
		<title><?php wp_title( '|', true, 'right' ); ?></title>
		<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
		<?php wp_head(); ?>
	</head>

	<body <?php body_class(); ?>>
	<div id="page" class="hfeed">
		<div id="page-head" class="site site-header-container">
			<header id="masthead" class="site-header" role="banner">
				<div class="site-header-line1">
					<div class="site-name">
						<?php /* The site name is the page's main heading only on the blog index; elsewhere the page has its own H1. */ ?>
						<?php $siteTitleTag = (is_home() || is_front_page()) ? 'h1' : 'p'; ?>
						<<?php echo $siteTitleTag; ?> class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></<?php echo $siteTitleTag; ?>>
						<p class="site-description"><?php bloginfo( 'description' ); ?></p>
					</div>
					<div class="site-share-ft">
						<a class="share-github-ft" href="https://github.com/alexboia" target="_blank"></a>
					</div>
					<div class="site-share-ft">
						<a class="share-substack-ft" href="https://substack.com/@alexandruboia" target="_blank"></a>
					</div>
					<div class="site-share-ft">
						<a class="share-rss-ft" href="http://feeds.feedburner.com/alexboia-net/toate-posturile" target="_blank"></a>
					</div>
					<div class="clear"></div>
				</div>

				<div class="site-header-line2"> 
					<nav id="site-navigation" class="main-navigation" role="navigation">
						<h3 class="menu-toggle"><?php _e( 'Menu', 'abnet' ); ?></h3>
						<a class="assistive-text" href="#content" title="<?php esc_attr_e( 'Skip to content', 'abnet' ); ?>"><?php _e( 'Skip to content', 'abnet' ); ?></a>
						</span><?php wp_nav_menu( array( 'theme_location' => 'primary', 'menu_class' => 'nav-menu' ) ); ?>
					</nav><!-- #site-navigation -->
				</div>
			</header><!-- #masthead -->
		</div>

		<div id="page-body" class="site site-body-container">
			<div id="main" class="wrapper">