<?php
/**
 * Seed site content: pages built from the theme's patterns, front page, menu.
 * Run with: wp eval-file /seed/seed.php --user=admin
 *
 * Safe to re-run; pages are matched by slug and their content is replaced
 * with the current patterns. Set SLS_SEED_KEEP=1 to leave the content of
 * existing pages untouched (titles, excerpts, menu and options still update).
 *
 * @package SLS
 */

if ( ! defined( 'WP_CLI' ) ) {
	exit;
}

$pages = array(
	'home'       => array(
		'title'   => 'Home',
		'excerpt' => 'Smart Logic Systems delivers out-of-the-box accelerators that eliminate weeks of setup, so your team focuses on what matters — building great software.',
		'patterns' => array( 'hero-home', 'clients', 'values', 'accelerators', 'cta-home' ),
	),
	'industries' => array(
		'title'   => 'Industries',
		'excerpt' => "Our accelerators are horizontal by design, but the time and cost pressure they relieve looks different in every industry. Here's how our customers are putting them to work.",
		'patterns' => array( 'hero-industries', 'industry-features', 'constraints', 'cta-industries' ),
	),
	'solutions'  => array(
		'title'   => 'Solutions',
		'excerpt' => 'Start with our most-used accelerators, or search the full directory of Smart Logic Systems solutions.',
		'patterns' => array( 'hero-solutions', 'featured-accelerators', 'methodology', 'directory', 'faq', 'cta-solutions' ),
	),
	'articles'   => array(
		'title'   => 'Articles',
		'excerpt' => "Engineering and business insight from the consultants building SLS's accelerators — written to save you the research time we've already put in.",
		'patterns' => array( 'hero-articles', 'newsletter' ),
	),
	'contact'    => array(
		'title'   => 'Contact Us',
		'excerpt' => "Whether you have a project timeline already or you're just exploring what an accelerator could save you, we'll get back to you within one business day.",
		'patterns' => array( 'hero-contact', 'contact' ),
	),
);

$registry = WP_Block_Patterns_Registry::get_instance();
$keep     = (bool) getenv( 'SLS_SEED_KEEP' );

/** Pattern markup, whether the registry stores content or a file path. */
$pattern_content = function ( string $slug ) use ( $registry ): string {
	$pattern = $registry->get_registered( 'sls/' . $slug );
	if ( ! $pattern ) {
		WP_CLI::error( "Pattern sls/$slug is not registered — is the sls theme active?" );
	}
	if ( isset( $pattern['content'] ) ) {
		return $pattern['content'];
	}
	ob_start();
	include $pattern['filePath'];
	return ob_get_clean();
};

// WordPress's sample content, only right after `wp core install` (setup.sh sets SLS_FRESH_INSTALL).
if ( getenv( 'SLS_FRESH_INSTALL' ) ) {
	foreach ( array( 'hello-world' => 'post', 'sample-page' => 'page', 'privacy-policy' => 'page' ) as $slug => $type ) {
		$post = get_page_by_path( $slug, OBJECT, $type );
		if ( $post ) {
			wp_delete_post( $post->ID, true );
		}
	}
}

$ids   = array();
$order = 0;
foreach ( $pages as $slug => $page ) {
	$existing = get_page_by_path( $slug, OBJECT, 'page' );
	$content  = implode( "\n\n", array_map( $pattern_content, $page['patterns'] ) );

	$data = array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_name'    => $slug,
		'post_title'   => $page['title'],
		'post_excerpt' => $page['excerpt'],
		'menu_order'   => $order++,
	);
	if ( ! $existing || ! $keep ) {
		$data['post_content'] = $content;
	}
	if ( $existing ) {
		// wp_update_post() merges with the stored post, so omitted content is kept.
		$data['ID'] = $existing->ID;
		$id         = wp_update_post( wp_slash( $data ), true );
	} else {
		$id = wp_insert_post( wp_slash( $data ), true );
	}
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( $id );
	}
	$ids[ $slug ] = $id;
	WP_CLI::log( sprintf( '%s page "%s" (#%d)', $existing ? 'Updated' : 'Created', $page['title'], $id ) );
}

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $ids['home'] );
update_option( 'blogname', 'Smart Logic Systems' );
update_option( 'blogdescription', 'Enterprise Technology Accelerators' );

// Primary menu: everything except Contact, which the header shows as a button.
$menu = wp_get_nav_menu_object( 'Primary' );
$menu_id = $menu ? $menu->term_id : wp_create_nav_menu( 'Primary' );
foreach ( (array) wp_get_nav_menu_items( $menu_id ) as $item ) {
	wp_delete_post( $item->ID, true );
}
$position = 0;
foreach ( array( 'home', 'industries', 'solutions', 'articles' ) as $slug ) {
	wp_update_nav_menu_item(
		$menu_id,
		0,
		array(
			'menu-item-title'     => 'home' === $slug ? 'Home' : $pages[ $slug ]['title'],
			'menu-item-object'    => 'page',
			'menu-item-object-id' => $ids[ $slug ],
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
			'menu-item-position'  => ++$position,
		)
	);
}
set_theme_mod( 'nav_menu_locations', array_merge( (array) get_theme_mod( 'nav_menu_locations', array() ), array( 'primary' => $menu_id ) ) );

WP_CLI::success( 'Site content seeded.' );
