<?php
/**
 * Template Name: Photo Archive
 *
 * Landing page of a photo archive: a hero with the featured image and figures,
 * the page content (introduction, context, sources) and the chapter cards.
 * The chapters are the posts of the category chosen in the Photo archive box of the page editor.
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 */

get_header(); ?>

	<div id="primary" class="site-content">
		<div id="content" role="main">

			<?php while (have_posts()): the_post(); ?>
				<?php $viewModel = abnet_get_photo_archive_page_view_model(get_post()); ?>

				<article id="post-<?php the_ID(); ?>" <?php post_class('abnet-pa'); ?>>
					<?php abnet_render_view('photo-archive-hero', $viewModel); ?>

					<?php if (trim(get_the_content()) !== ''): ?>
						<div class="entry-content abnet-pa-intro">
							<?php the_content(); ?>
						</div>
					<?php endif; ?>

					<?php abnet_render_view('photo-archive-chapters', $viewModel); ?>

					<footer class="entry-meta">
						<?php edit_post_link(__('Edit', 'abnet'), '<span class="edit-link">', '</span>'); ?>
					</footer>
				</article>
			<?php endwhile; ?>

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_footer(); ?>
