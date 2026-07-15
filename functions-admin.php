<?php
if (!defined('ABSPATH')) {
	exit;
}

function abnet_mce_register_enlighter_customization_plugins(array $plugins): array {
	$theme = wp_get_theme();
	if (!$theme instanceof WP_Theme) {
		return $plugins;
	}

	$plugins['abnet_enlighter_inline_fix'] = 
		_abnet_enlighter_plugin_url('enlighter-inline-fix.js');
	$plugins['abnet_enlighter_delete_code_block'] = 
		_abnet_enlighter_plugin_url('enlighter-delete-button.js');

	return $plugins;
}

add_action('mce_external_plugins', 
	'abnet_mce_register_enlighter_customization_plugins');

function _abnet_enlighter_plugin_url(string $fileName): string {
	return get_stylesheet_directory_uri() . '/js/admin/' . $fileName;
}

function abnet_add_mce_enlighter_delete_button(array $buttons, string $editorId): array {
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
}

//Setting the priority higher, otherwise it might not 
//	find the 'EnlighterEdit' target and will add the button to the end,
//	after which the enlighter mce_buttons hook will fire and add its own, 
//	effectively succeeding in lieu of preceding our buttons
add_filter('mce_buttons', 
	'abnet_add_mce_enlighter_delete_button',
	200,
	2
);

/**
 * Force custom editor style to the absolute end of the TinyMCE loading stack.
 */
function abnet_force_editor_style_last(string|null $mceCss ) {
    $styleUrl = get_theme_file_uri( 'editor-style.css' );

	if (defined('WP_DEBUG') && WP_DEBUG) {
		$styleUrl = add_query_arg('_abnetCb', time(), $styleUrl);
	}

	if (!empty($mceCss)) {
		$cssStyles = explode( ',', $mceCss );
    	$cssStyles[] = $styleUrl;
		return implode( ',', array_unique($cssStyles));
	} else {
		return $styleUrl;
	}
}
add_filter( 'mce_css', 
	'abnet_force_editor_style_last', 
	9999 );
