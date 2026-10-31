<?php
/**
 * Footer social links.
 *
 * @package SLS
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$networks = array(
	'x'         => 'X (Twitter)',
	'facebook'  => 'Facebook',
	'instagram' => 'Instagram',
	'linkedin'  => 'LinkedIn',
);
?>
<ul class="social">
	<?php
	foreach ( $networks as $key => $label ) :
		$url = trim( $attributes[ $key ] ?? '' );
		if ( '' === $url ) {
			continue;
		}
		?>
		<li><a href="<?php echo '#' === $url ? '#' : esc_url( $url ); ?>"<?php echo '#' === $url ? ' data-todo="social-url"' : ''; ?> aria-label="<?php echo esc_attr( sprintf( /* translators: %s: social network. */ __( 'Smart Logic Systems on %s', 'sls' ), $label ) ); ?>"><?php echo sls_icon( $key ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a></li>
	<?php endforeach; ?>
</ul>
