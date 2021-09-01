<?php
/**
 * The template for displaying the footer.
 *
 * Contains footer content and the closing of the
 * #main and #page div elements.
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 */
?>
				</div><!-- #main .wrapper -->
				<footer id="colophon" role="contentinfo">
					<div class="site-info">
						<?php do_action( 'abnet_credits' ); ?>
						<a href="<?php echo esc_url( __( 'http://wordpress.org/', 'abnet' ) ); ?>" title="<?php esc_attr_e( 'Semantic Personal Publishing Platform', 'abnet' ); ?>"><?php printf( __( 'Proudly powered by %s', 'abnet' ), 'WordPress' ); ?></a>
					</div><!-- .site-info -->
				</footer><!-- #colophon -->
			</div><!-- #page-body -->
		</div><!-- #page -->

		<?php wp_footer(); ?>
	</body>
</html>