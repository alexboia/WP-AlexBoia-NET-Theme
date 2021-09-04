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
						<div class="abnet-credits-item abnet-copy">
							<a href="http://alexboia.net/despre/" target="_blank"><?php echo sprintf(esc_html__( 'Copyright &copy; 2013 - %d alexboia.net.', 'abnet' ), date('Y')); ?></a>
						</div>
						<div class="abnet-credits-item abnet-reclama">
							<span class="abnet-credits-item abnet-development">
								<a href="https://github.com/alexboia/WP-AlexBoia-NET-Theme" target="_blank"><?php echo esc_html__( 'Tema dezvolata peste clasicul Twenty Twelve.', 'abnet' ); ?></a>
							</span>
							<span class="abnet-credits-item abnet-wpts">
								<a href="https://wordpress.org/plugins/wp-trip-summary/" target="_blank"><?php echo esc_html__( 'Jurnale de tura documentate folosind WP-Trip-Summary.', 'abnet' ); ?></a>
							</span>
						</div>
						<div class="abnet-credits-item abnet-caterinca">
							<?php echo esc_html__( 'Scris cu parapon in Bucuresti.', 'abnet' ); ?>
						</div>
					</div><!-- .site-info -->
				</footer><!-- #colophon -->
			</div><!-- #page-body -->
		</div><!-- #page -->

		<?php wp_footer(); ?>
	</body>
</html>