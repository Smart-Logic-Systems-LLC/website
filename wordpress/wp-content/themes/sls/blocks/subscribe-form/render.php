<?php
/**
 * Newsletter signup form (validated and submitted in js/main.js).
 *
 * @package SLS
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$endpoint = trim( $attributes['endpoint'] ?? '' );
?>
<form class="subscribe" action="#" method="post" novalidate data-subscribe-form<?php echo $endpoint ? ' data-endpoint="' . esc_url( $endpoint ) . '"' : ''; ?>>
	<label class="visually-hidden" for="subscribe-email"><?php esc_html_e( 'Email address', 'sls' ); ?></label>
	<input class="subscribe__field" id="subscribe-email" name="email" type="email" placeholder="<?php echo esc_attr( $attributes['placeholder'] ?? '' ); ?>" autocomplete="email" required>
	<button class="btn btn--gold" type="submit"><?php echo esc_html( $attributes['buttonLabel'] ?? '' ); ?></button>
	<p class="form-status" role="status" aria-live="polite"></p>
</form>
