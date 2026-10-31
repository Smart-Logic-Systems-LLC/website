<?php
/**
 * Email and phone links for a contact card.
 *
 * @package SLS
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$email = sanitize_email( $attributes['email'] ?? '' );
$phone = trim( $attributes['phone'] ?? '' );
if ( ! $email && ! $phone ) {
	return;
}

// "(555) 019-2200" → tel:+15550192200 (assumes a North American number without a country code).
$digits = preg_replace( '/\D+/', '', $phone );
$tel    = str_starts_with( $phone, '+' ) ? '+' . $digits : '+1' . $digits;
?>
<ul class="contact-card__links">
	<?php if ( $email ) : ?>
		<li><a class="contact-link" href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo sls_icon( 'mail' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo str_replace( '@', '@<wbr>', esc_html( $email ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span></a></li>
	<?php endif; ?>
	<?php if ( $phone ) : ?>
		<li><a class="contact-link contact-link--phone" href="tel:<?php echo esc_attr( $tel ); ?>"><?php echo sls_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php echo esc_html( $phone ); ?></span></a></li>
	<?php endif; ?>
</ul>
