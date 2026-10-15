<?php
/**
 * The post date, linked to the post
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 *
 * @var \AbNetTheme\ViewModel\EntryMetaDateViewModel $data
 */

if (!defined('ABSPATH')) {
	exit;
}
?>
<a href="<?php echo esc_url($data->permalink); ?>" title="<?php echo esc_attr($data->time); ?>" rel="bookmark"><time class="entry-date" datetime="<?php echo esc_attr($data->dateIso); ?>"><?php echo esc_html($data->dateDisplay); ?></time></a><?php
