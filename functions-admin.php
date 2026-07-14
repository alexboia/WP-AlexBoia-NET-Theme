<?php
if (!defined('ABSPATH')) {
	exit;
}

add_action('mce_external_plugins', function (array $plugins): array {
	$theme = wp_get_theme();
	if (!$theme instanceof WP_Theme) {
		return $plugins;
	}

	$plugins['abnet_enlighter_inline_fix'] = 
		_abnet_enlighter_plugin_url('enlighter-inline-fix.js');
	$plugins['abnet_enlighter_delete_code_block'] = 
		_abnet_enlighter_plugin_url('enlighter-delete-button.js');

	return $plugins;
});

function _abnet_enlighter_plugin_url(string $fileName): string {
	return get_stylesheet_directory_uri() . '/js/admin/' . $fileName;
}

//Setting the priority higher, otherwise it might not 
//	find the 'EnlighterEdit' target and will add the button to the end,
//	after which the enlighter mce_buttons hook will fire and add its own, 
//	effectively succeeding in lieu of preceding our buttons
add_filter('mce_buttons', function (array $buttons, string $editorId): array {
		if ($editorId !== 'content') {
			return $buttons;
		}

		$buttonName = 'EnlighterDelete';
		$position   = array_search(
			'EnlighterEdit',
			$buttons,
			true
		);

		if ($position === false) {
			$buttons[] = $buttonName;
		} else {
			array_splice(
				$buttons,
				$position + 1,
				0,
				[$buttonName]
			);
		}

		return $buttons;
	},
	200,
	2
);