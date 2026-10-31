<?php
/**
 * Solution finder card. Its search box and quick links drive the Solutions
 * Directory block on the same page (js/main.js).
 *
 * @package SLS
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$types = sls_solution_types();
?>
<div class="finder" role="search" aria-labelledby="finder-title">
	<p class="finder__title" id="finder-title"><?php echo esc_html( $attributes['title'] ?? '' ); ?></p>
	<form class="search-field" action="#directory" data-finder-form>
		<?php echo sls_icon( 'search', 'search-field__icon' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<label class="visually-hidden" for="finder-q"><?php esc_html_e( 'Search accelerators', 'sls' ); ?></label>
		<input id="finder-q" name="q" type="search" placeholder="<?php echo esc_attr( $attributes['placeholder'] ?? '' ); ?>" autocomplete="off">
	</form>
	<ul class="finder__list">
		<?php
		foreach ( (array) ( $attributes['items'] ?? array() ) as $item ) :
			$label = trim( $item['label'] ?? '' );
			$type  = isset( $types[ $item['type'] ?? '' ] ) ? $item['type'] : 'accelerator';
			if ( '' === $label ) {
				continue;
			}
			?>
			<li><a class="finder__item" href="#directory" data-directory-query="<?php echo esc_attr( $label ); ?>"><span><?php echo esc_html( $label ); ?></span><span class="tag tag--sm tag--<?php echo esc_attr( $type ); ?>"><?php echo esc_html( $types[ $type ] ); ?></span></a></li>
		<?php endforeach; ?>
	</ul>
	<div class="finder__foot">
		<p class="finder__count"><?php echo esc_html( $attributes['countText'] ?? '' ); ?></p>
		<a class="text-link" href="#directory" data-directory-reset><span class="trim"><?php echo esc_html( $attributes['linkLabel'] ?? '' ); ?></span> <?php echo sls_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
	</div>
</div>
