<?php
/**
 * Theme blocks: server-rendered blocks for the site chrome and interactive parts.
 *
 * Each block lives in blocks/<name>/ (block.json + render.php). One plain-JS
 * editor script (blocks/editor.js, no build step) registers them all in the
 * editor, previews them with ServerSideRender and adds rich-text formats.
 *
 * @package SLS
 */

defined( 'ABSPATH' ) || exit;

const SLS_BLOCKS = array(
	'site-header',
	'brand',
	'social-links',
	'icon',
	'solution-finder',
	'solutions-directory',
	'contact-links',
	'contact-form',
	'subscribe-form',
);

add_action(
	'init',
	function () {
		$asset = require get_theme_file_path( 'blocks/editor.asset.php' );
		wp_register_script(
			'sls-blocks-editor',
			get_theme_file_uri( 'blocks/editor.js' ),
			$asset['dependencies'],
			SLS_VERSION,
			array( 'in_footer' => true )
		);
		wp_localize_script(
			'sls-blocks-editor',
			'slsBlocks',
			array(
				'icons'     => sls_icon_names(),
				'types'     => sls_solution_types(),
			)
		);

		foreach ( SLS_BLOCKS as $block ) {
			register_block_type( get_theme_file_path( 'blocks/' . $block ) );
		}
	}
);

// The rich-text formats live in the same script, so load it for every editor.
add_action(
	'enqueue_block_editor_assets',
	function () {
		wp_enqueue_script( 'sls-blocks-editor' );
		wp_register_style( 'sls-blocks-editor', false, array(), SLS_VERSION );
		wp_enqueue_style( 'sls-blocks-editor' );
		wp_add_inline_style(
			'sls-blocks-editor',
			'.sls-field + .sls-field{margin-top:16px}' .
			'.sls-list-control__item{margin-bottom:16px;padding:12px;border:1px solid #ddd;border-radius:4px}' .
			'.sls-list-control__heading{margin:0 0 8px;font-weight:600}' .
			'.sls-list-control__field + .sls-list-control__field{margin-top:12px}' .
			'.sls-list-control__actions{display:flex;flex-wrap:wrap;gap:4px;margin-top:8px}'
		);
	}
);

add_filter(
	'block_categories_all',
	function ( $categories ) {
		array_unshift(
			$categories,
			array(
				'slug'  => 'sls',
				'title' => __( 'Smart Logic Systems', 'sls' ),
			)
		);
		return $categories;
	}
);
