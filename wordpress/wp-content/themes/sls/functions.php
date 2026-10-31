<?php
/**
 * Smart Logic Systems theme bootstrap.
 *
 * @package SLS
 */

defined( 'ABSPATH' ) || exit;

define( 'SLS_VERSION', wp_get_theme( get_template() )->get( 'Version' ) );
define( 'SLS_FONTS_URL', 'https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600&family=Google+Sans+Code:wght@400..600&family=Google+Sans+Flex:opsz,slnt,wght@6..144,-10..0,300..700&display=swap' );

require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/markup.php';
require_once __DIR__ . '/inc/blocks.php';

/* ----- Theme supports ------------------------------------------------------ */
add_action(
	'after_setup_theme',
	function () {
		// The design's spacing lives in css/styles.css; don't let core layout CSS add gaps.
		add_theme_support( 'disable-layout-styles' );
		remove_theme_support( 'core-block-patterns' );

		add_theme_support( 'editor-styles' );
		add_editor_style( array( SLS_FONTS_URL, 'css/styles.css', 'css/wp.css', 'css/editor.css' ) );

		register_nav_menus( array( 'primary' => __( 'Primary navigation', 'sls' ) ) );
		add_post_type_support( 'page', 'excerpt' );
	}
);

/* ----- Front-end assets ---------------------------------------------------- */
add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_style( 'sls-fonts', SLS_FONTS_URL, array(), null );
		wp_enqueue_style( 'sls-styles', get_theme_file_uri( 'css/styles.css' ), array(), SLS_VERSION );
		wp_enqueue_style( 'sls-wp', get_theme_file_uri( 'css/wp.css' ), array( 'sls-styles' ), SLS_VERSION );
		wp_enqueue_script(
			'sls-main',
			get_theme_file_uri( 'js/main.js' ),
			array(),
			SLS_VERSION,
			array(
				'in_footer' => false,
				'strategy'  => 'defer',
			)
		);
	}
);

// The header block prints the site's own skip link (styled by css/styles.css).
remove_action( 'wp_enqueue_scripts', 'wp_enqueue_block_template_skip_link' );
remove_action( 'wp_footer', 'the_block_template_skip_link' );

add_action(
	'wp_head',
	function () {
		echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
		echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
		echo '<meta name="theme-color" content="#1b2955">' . "\n";
		if ( ! has_site_icon() ) {
			printf( '<link rel="icon" href="%s" type="image/svg+xml">' . "\n", esc_url( get_theme_file_uri( 'assets/logo-mark.svg' ) ) );
		}
		if ( is_singular() && has_excerpt() ) {
			printf( '<meta name="description" content="%s">' . "\n", esc_attr( wp_strip_all_tags( get_the_excerpt() ) ) );
		}
	},
	1
);

add_filter(
	'document_title_separator',
	function () {
		return '|';
	}
);

/* ----- Pattern category ---------------------------------------------------- */
add_action(
	'init',
	function () {
		register_block_pattern_category( 'sls-sections', array( 'label' => __( 'Smart Logic Systems', 'sls' ) ) );
	}
);
