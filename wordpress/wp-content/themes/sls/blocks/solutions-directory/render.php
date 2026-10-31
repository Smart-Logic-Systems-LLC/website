<?php
/**
 * Solutions directory: search, type filters, table and pagination.
 * Filtering and paging happen client-side in js/main.js.
 *
 * @package SLS
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$types   = sls_solution_types();
$filters = array(
	'all'         => array( __( 'All Solutions', 'sls' ), 195 ),
	'accelerator' => array( __( 'Accelerators', 'sls' ), 198 ),
	'add-on'      => array( __( 'Add-ons', 'sls' ), 149 ),
	'toolkit'     => array( __( 'Toolkits', 'sls' ), 144 ),
);
?>
<div class="directory__body" data-directory>
	<div class="directory__search" role="search">
		<label class="directory__label" for="directory-q"><?php echo esc_html( $attributes['searchLabel'] ?? '' ); ?></label>
		<div class="search-field">
			<input id="directory-q" type="search" placeholder="<?php echo esc_attr( $attributes['placeholder'] ?? '' ); ?>" autocomplete="off" data-directory-search>
		</div>
	</div>
	<ul class="filters" aria-label="<?php esc_attr_e( 'Filter by type', 'sls' ); ?>">
		<?php foreach ( $filters as $type => [ $label, $width ] ) : ?>
			<li><button class="filter" type="button" aria-pressed="<?php echo 'all' === $type ? 'true' : 'false'; ?>" data-filter="<?php echo esc_attr( $type ); ?>" style="--chip-w: <?php echo (int) $width; ?>px"><?php echo esc_html( $label ); ?></button></li>
		<?php endforeach; ?>
	</ul>
	<div class="dir-table-wrap">
		<table class="dir-table">
			<caption class="visually-hidden"><?php esc_html_e( 'Solutions directory', 'sls' ); ?></caption>
			<thead>
				<tr>
					<th scope="col"><span><?php esc_html_e( 'Solution', 'sls' ); ?></span></th>
					<th scope="col"><span><?php esc_html_e( 'Type', 'sls' ); ?></span></th>
					<th scope="col"><span><?php esc_html_e( 'Best for', 'sls' ); ?></span></th>
				</tr>
			</thead>
			<tbody data-directory-rows>
				<?php
				foreach ( (array) ( $attributes['rows'] ?? array() ) as $row ) :
					$name = trim( $row['name'] ?? '' );
					$type = isset( $types[ $row['type'] ?? '' ] ) ? $row['type'] : 'accelerator';
					if ( '' === $name ) {
						continue;
					}
					?>
					<tr data-type="<?php echo esc_attr( $type ); ?>">
						<td><p class="dir-table__name"><?php echo esc_html( $name ); ?></p><p class="dir-table__desc"><?php echo esc_html( $row['desc'] ?? '' ); ?></p></td>
						<td><span class="tag tag--<?php echo esc_attr( $type ); ?>"><?php echo esc_html( $types[ $type ] ); ?></span></td>
						<td><p class="dir-table__best"><?php echo esc_html( $row['best'] ?? '' ); ?></p></td>
					</tr>
				<?php endforeach; ?>
				<tr class="dir-table__empty" hidden data-directory-empty>
					<td colspan="3"><?php echo esc_html( $attributes['emptyText'] ?? '' ); ?></td>
				</tr>
			</tbody>
		</table>
	</div>
	<nav class="pagination" aria-label="<?php esc_attr_e( 'Directory pages', 'sls' ); ?>" data-pagination>
		<button class="pagination__btn pagination__btn--nav" type="button" aria-label="<?php esc_attr_e( 'Previous page', 'sls' ); ?>" data-page-prev><?php echo sls_icon( 'chevron-left' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
		<button class="pagination__btn" type="button" aria-current="page" data-page="1">1</button>
		<button class="pagination__btn pagination__btn--nav" type="button" aria-label="<?php esc_attr_e( 'Next page', 'sls' ); ?>" data-page-next><?php echo sls_icon( 'chevron-right' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
	</nav>
</div>
