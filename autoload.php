<?php
/**
 * PSR-4 Autoloader for AbNetTheme namespace
 *
 * Maps the AbNetTheme root namespace to the inc folder.
 *
 * @package WordPress
 * @subpackage WP_AbNet
 * @since WP AbNet 1.0
 */

if (!defined( 'ABSPATH')) {
	exit;
}

spl_autoload_register(
	function (string $class): void {
		$prefix   = 'AbNetTheme\\';
		$baseDir = __DIR__ . '/includes/';

		$len = strlen( $prefix );
		if ( strncmp( $prefix, $class, $len ) !== 0 ) {
			return;
		}

		$relativeClassName = substr( $class, $len );
		$file = $baseDir . str_replace( '\\', '/', $relativeClassName ) . '.php';

		if (file_exists($file)) {
			require $file;
		}
	}
);
