<?php
/**
 * Site header: brand, primary menu and contact button.
 *
 * Menu items come from the "Primary navigation" location (Appearance › Menus);
 * without a menu, published top-level pages are listed in menu order.
 *
 * @package SLS
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$cta_url   = $attributes['ctaUrl'] ?? '/contact/';
$cta_label = $attributes['ctaLabel'] ?? __( 'Contact Us', 'sls' );
$queried   = get_queried_object_id();
$links     = array();

$locations = get_nav_menu_locations();
$items     = ! empty( $locations['primary'] ) ? wp_get_nav_menu_items( $locations['primary'] ) : false;

if ( $items ) {
	foreach ( $items as $item ) {
		if ( (int) $item->menu_item_parent ) {
			continue;
		}
		$links[] = array(
			'url'     => $item->url,
			'label'   => $item->title,
			'current' => ( 'post_type' === $item->type && (int) $item->object_id === $queried ) || ( 'custom' === $item->type && sls_is_current_url( $item->url ) ),
		);
	}
} else {
	foreach ( get_pages( array( 'parent' => 0, 'sort_column' => 'menu_order,post_title' ) ) as $page ) {
		$url = get_permalink( $page );
		if ( untrailingslashit( wp_make_link_relative( $url ) ) === untrailingslashit( $cta_url ) ) {
			continue; // Already shown as the contact button.
		}
		$links[] = array(
			'url'     => $url,
			'label'   => get_the_title( $page ),
			'current' => $page->ID === $queried,
		);
	}
}
?>
<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to main content', 'sls' ); ?></a>
<header class="site-header" data-header>
	<div class="site-header__bar">
		<?php echo sls_brand(); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in sls_brand(). ?>
		<button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav" aria-label="<?php esc_attr_e( 'Open menu', 'sls' ); ?>" data-nav-toggle>
			<span class="nav-toggle__bars" aria-hidden="true"></span>
		</button>
		<nav class="site-nav" id="site-nav" aria-label="<?php esc_attr_e( 'Primary', 'sls' ); ?>">
			<ul class="site-nav__list">
				<?php foreach ( $links as $link ) : ?>
					<li><a class="site-nav__link" href="<?php echo esc_url( $link['url'] ); ?>"<?php echo $link['current'] ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $link['label'] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
			<a class="btn btn--outline btn--pill site-nav__cta" href="<?php echo esc_url( $cta_url ); ?>"<?php echo sls_is_current_url( $cta_url ) ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $cta_label ); ?></a>
		</nav>
	</div>
</header>
