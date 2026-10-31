<?php
/**
 * Icon block: a decorative SVG in a styled wrapper span.
 *
 * @package SLS
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$svg = sls_icon( $attributes['icon'] ?? '' );
if ( '' === $svg ) {
	return;
}

printf(
	'<span class="%s" aria-hidden="true">%s</span>',
	esc_attr( trim( $attributes['className'] ?? '' ) ?: 'icon-tile' ),
	$svg // phpcs:ignore WordPress.Security.EscapeOutput -- theme-owned SVG.
);
