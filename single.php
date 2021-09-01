<?php
/**
 * The Template for displaying all single posts.
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 */

get_header(); ?>

	<div id="primary" class="site-content">
		<div id="content" role="main">

			<?php while ( have_posts() ) : the_post(); ?>

				<?php get_template_part( 'content', get_post_format() ); ?>

				<nav class="nav-single">
					<div class="nav-single-row">
						<h3 class="assistive-text"><?php _e( 'Post navigation', 'abnet' ); ?></h3>
						<?php $prev_post_link = get_previous_post_link( '%link', '<span class="dashicons dashicons-arrow-left nav-icon"></span> <h4>%title</h4>' ); ?>
						<?php $next_post_link = get_next_post_link( '%link', '<h4>%title</h4> <span class="dashicons dashicons-arrow-right nav-icon"></span>' ); ?>
						<div class="nav-previous <?php echo !empty($prev_post_link) ? 'nav-with-post' : 'nav-no-post' ?>">
							<?php echo $prev_post_link; ?>
						</div>
						<div class="nav-next <?php echo !empty($next_post_link) ? 'nav-with-post' : 'nav-no-post' ?>">
							<?php echo $next_post_link; ?>
						</div>
					</div>
				</nav><!-- .nav-single -->

				<?php comments_template( '', true ); ?>

			<?php endwhile; // end of the loop. ?>

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>