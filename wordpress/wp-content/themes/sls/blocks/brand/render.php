<?php
/**
 * Brand lockup block.
 *
 * @package SLS
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

echo sls_brand( // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in sls_brand().
	array(
		'large' => ! empty( $attributes['large'] ),
		'link'  => ! empty( $attributes['link'] ),
		'line1' => $attributes['line1'] ?? 'Smart Logic',
		'line2' => $attributes['line2'] ?? 'Systems',
	)
);
