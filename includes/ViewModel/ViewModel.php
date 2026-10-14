<?php
/**
 * Marker interface for view models
 *
 * Every view rendered with abnet_render_view() receives an explicit,
 * typed view model implementing this interface, available in the view as $data.
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 */

namespace AbNetTheme\ViewModel;

if (!defined('ABSPATH')) {
	exit;
}

interface ViewModel {
}
