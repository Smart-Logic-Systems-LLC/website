<?php
/**
 * Shared helpers for templates, patterns and block renderers.
 *
 * @package SLS
 */

defined( 'ABSPATH' ) || exit;

/**
 * Root-relative URL of a theme file, so seeded page content isn't tied to one host.
 *
 * @param string $path Path inside the theme, e.g. 'assets/logo-mark.svg'.
 */
function sls_asset( string $path ): string {
	return wp_make_link_relative( get_theme_file_uri( $path ) );
}

/**
 * Inline SVG for one of the theme's icons, or '' if unknown.
 *
 * @param string $name  Icon key from inc/icons.php.
 * @param string $class Optional class for the <svg> element.
 */
function sls_icon( string $name, string $class = '' ): string {
	static $icons = null;
	if ( null === $icons ) {
		$icons = require __DIR__ . '/icons.php';
	}
	if ( ! isset( $icons[ $name ] ) ) {
		return '';
	}
	$svg = $icons[ $name ];
	if ( $class ) {
		$svg = preg_replace( '/^<svg\b/', '<svg class="' . esc_attr( $class ) . '"', $svg, 1 );
	}
	return $svg;
}

/** Icon keys offered by the SLS Icon block. */
function sls_icon_names(): array {
	return array_keys( require __DIR__ . '/icons.php' );
}

/**
 * Solution type slugs and their display labels (directory tags and filters).
 */
function sls_solution_types(): array {
	return array(
		'accelerator' => __( 'Accelerator', 'sls' ),
		'add-on'      => __( 'Add-on', 'sls' ),
		'toolkit'     => __( 'Toolkit', 'sls' ),
	);
}

/**
 * Brand lockup: logo mark plus the two-line wordmark.
 *
 * @param array $args {
 *     @type bool   $large Use the large footer/contact variant.
 *     @type bool   $link  Wrap in a link to the home page.
 *     @type string $line1 First wordmark line.
 *     @type string $line2 Second wordmark line.
 *     @type int    $width Rendered width of the logo mark (height follows its 47:33 ratio).
 * }
 */
function sls_brand( array $args = array() ): string {
	$args = wp_parse_args(
		$args,
		array(
			'large' => false,
			'link'  => true,
			'line1' => 'Smart Logic',
			'line2' => 'Systems',
			'width' => 0,
		)
	);

	$width  = $args['width'] ? (int) $args['width'] : ( $args['large'] ? 73 : 47 );
	$height = (int) round( $width * ( $args['large'] ? 56 / 73 : 33 / 47 ) );
	$class  = 'brand' . ( $args['large'] ? ' brand--lg' : '' );

	$inner = sprintf(
		'<img class="brand__mark" src="%1$s" alt="" width="%2$d" height="%3$d"><span class="brand__name"><span class="brand__line1">%4$s</span><span class="brand__line2">%5$s</span></span>',
		esc_url( get_theme_file_uri( 'assets/logo-mark.svg' ) ),
		$width,
		$height,
		esc_html( $args['line1'] ),
		esc_html( $args['line2'] )
	);

	if ( ! $args['link'] ) {
		return sprintf( '<div class="%s">%s</div>', esc_attr( $class ), $inner );
	}

	return sprintf(
		'<a class="%1$s" href="%2$s" aria-label="%3$s">%4$s</a>',
		esc_attr( $class ),
		esc_url( home_url( '/' ) ),
		/* translators: %s: site name. */
		esc_attr( sprintf( __( '%s — Home', 'sls' ), trim( $args['line1'] . ' ' . $args['line2'] ) ) ),
		$inner
	);
}

/**
 * Whether a URL points at the page currently being viewed (path comparison).
 *
 * @param string $url Link URL.
 */
function sls_is_current_url( string $url ): bool {
	$request = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
	$current = wp_parse_url( $request, PHP_URL_PATH );
	$target  = wp_parse_url( $url, PHP_URL_PATH );
	if ( null === $target || null === $current ) {
		return false;
	}
	return untrailingslashit( $target ) === untrailingslashit( $current );
}
