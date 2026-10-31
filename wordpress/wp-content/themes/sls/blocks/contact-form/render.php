<?php
/**
 * Contact panel: brand artwork plus the contact form (validated in js/main.js).
 *
 * @package SLS
 *
 * @var array $attributes Block attributes.
 */

defined( 'ABSPATH' ) || exit;

$endpoint = trim( $attributes['endpoint'] ?? '' );
$fields   = array(
	'name'    => array( __( 'Name', 'sls' ), 'text', __( 'Name', 'sls' ), 'name', true ),
	'email'   => array( __( 'Email', 'sls' ), 'email', 'you@company.com', 'email', true ),
	'subject' => array( __( 'Subject', 'sls' ), 'text', __( "What's this about?", 'sls' ), '', false ),
);
?>
<div class="contact-panel" id="contact-form">
	<div class="contact-panel__art">
		<img class="contact-panel__grid" src="<?php echo esc_url( get_theme_file_uri( 'assets/decor/cta-grid.svg' ) ); ?>" alt="" width="520" height="420" loading="lazy">
		<img class="contact-panel__mark" src="<?php echo esc_url( get_theme_file_uri( 'assets/logo-mark.svg' ) ); ?>" alt="" width="259" height="183" loading="lazy">
		<?php echo sls_brand( array( 'large' => true, 'link' => false, 'width' => 76 ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</div>
	<form class="contact-form" action="#" method="post" novalidate data-contact-form<?php echo $endpoint ? ' data-endpoint="' . esc_url( $endpoint ) . '"' : ''; ?>>
		<h2 class="contact-form__title" id="form-title"><?php echo esc_html( $attributes['title'] ?? '' ); ?></h2>
		<?php foreach ( array_slice( $fields, 0, 2, true ) as $key => [ $label, $type, $placeholder, $autocomplete, $required ] ) : ?>
			<div class="field">
				<label for="cf-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label>
				<input id="cf-<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" type="<?php echo esc_attr( $type ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>" autocomplete="<?php echo esc_attr( $autocomplete ); ?>" required>
				<p class="field__error" id="cf-<?php echo esc_attr( $key ); ?>-error"></p>
			</div>
		<?php endforeach; ?>
		<div class="field">
			<label for="cf-reason"><?php esc_html_e( 'Reason for Inquiry', 'sls' ); ?></label>
			<div class="select">
				<select id="cf-reason" name="reason" required>
					<option value="" selected disabled><?php esc_html_e( 'Select Reason...', 'sls' ); ?></option>
					<?php foreach ( (array) ( $attributes['reasons'] ?? array() ) as $reason ) : ?>
						<?php
						if ( empty( $reason['value'] ) || empty( $reason['label'] ) ) {
							continue;
						}
						?>
						<option value="<?php echo esc_attr( $reason['value'] ); ?>" data-email="<?php echo esc_attr( sanitize_email( $reason['email'] ?? '' ) ); ?>"><?php echo esc_html( $reason['label'] ); ?></option>
					<?php endforeach; ?>
				</select>
				<?php echo sls_icon( 'select-chevron' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
			<p class="field__error" id="cf-reason-error"></p>
		</div>
		<?php [ $label, $type, $placeholder ] = $fields['subject']; ?>
		<div class="field">
			<label for="cf-subject"><?php echo esc_html( $label ); ?></label>
			<input id="cf-subject" name="subject" type="text" placeholder="<?php echo esc_attr( $placeholder ); ?>">
		</div>
		<div class="field">
			<label for="cf-message"><?php esc_html_e( 'Message', 'sls' ); ?></label>
			<textarea id="cf-message" name="message" rows="4" placeholder="<?php esc_attr_e( 'Your Message...', 'sls' ); ?>" required></textarea>
			<p class="field__error" id="cf-message-error"></p>
		</div>
		<button class="btn btn--primary btn--block" type="submit"><?php echo esc_html( $attributes['submitLabel'] ?? '' ); ?></button>
		<p class="form-status" role="status" aria-live="polite"></p>
	</form>
</div>
