<?php
/**
 * Post meta information: categories and tags
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 *
 * @var \AbNetTheme\ViewModel\EntryMetaViewModel $data
 */

if (!defined('ABSPATH')) {
	exit;
}
?>
<?php if ($data->categoriesListHtml !== ''): ?><span class="entry-meta-categories"><?php echo $data->categoriesListHtml; ?></span><?php endif; ?><?php if ($data->tagListHtml !== ''): ?><span class="entry-meta-tags"><?php echo $data->tagListHtml; ?></span><?php endif; ?>
