<?php
/**
 * Plugin Name: SLS mail relay
 * Description: Sends WordPress mail through the Postfix relay container in the site's pod, which relays to Azure Communication Services (deployed by wordpress/infra).
 *
 * @package SLS
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'phpmailer_init',
	static function ( $mailer ) {
		$mailer->isSMTP();
		$mailer->Host        = '127.0.0.1';
		$mailer->Port        = 587;
		$mailer->SMTPAuth    = false;
		$mailer->SMTPAutoTLS = false;
	}
);

// Sender name: the site title instead of WordPress's default "WordPress".
add_filter(
	'wp_mail_from_name',
	static fn( $name ) => 'WordPress' === $name ? get_option( 'blogname' ) : $name
);
