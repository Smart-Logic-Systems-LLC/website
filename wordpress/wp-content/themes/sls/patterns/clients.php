<?php
/**
 * Title: Client logos
 * Slug: sls/clients
 * Categories: sls-sections
 * Inserter: true
 *
 * @package SLS
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"tagName":"section","className":"clients"} -->
<section class="wp-block-group clients"><!-- wp:group {"className":"container"} -->
<div class="wp-block-group container"><!-- wp:heading {"className":"clients__title"} -->
<h2 class="wp-block-heading clients__title">Empowering modern enterprises</h2>
<!-- /wp:heading -->

<!-- wp:group {"className":"clients__logos tag-ul"} -->
<div class="wp-block-group clients__logos tag-ul"><!-- wp:image {"className":"tag-li"} -->
<figure class="wp-block-image tag-li"><img src="<?php echo esc_url( sls_asset( 'assets/clients/acme.svg' ) ); ?>" alt="ACME"/></figure>
<!-- /wp:image -->

<!-- wp:image {"className":"tag-li"} -->
<figure class="wp-block-image tag-li"><img src="<?php echo esc_url( sls_asset( 'assets/clients/meridian.svg' ) ); ?>" alt="Meridian Group"/></figure>
<!-- /wp:image -->

<!-- wp:image {"className":"tag-li"} -->
<figure class="wp-block-image tag-li"><img src="<?php echo esc_url( sls_asset( 'assets/clients/azure.svg' ) ); ?>" alt="Microsoft Azure"/></figure>
<!-- /wp:image -->

<!-- wp:image {"className":"tag-li"} -->
<figure class="wp-block-image tag-li"><img src="<?php echo esc_url( sls_asset( 'assets/clients/terraform.svg' ) ); ?>" alt="Terraform"/></figure>
<!-- /wp:image -->

<!-- wp:image {"className":"tag-li"} -->
<figure class="wp-block-image tag-li"><img src="<?php echo esc_url( sls_asset( 'assets/clients/apex.svg' ) ); ?>" alt="Apex Creative Media"/></figure>
<!-- /wp:image --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
