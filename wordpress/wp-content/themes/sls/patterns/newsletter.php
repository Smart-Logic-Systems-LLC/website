<?php
/**
 * Title: Newsletter signup
 * Slug: sls/newsletter
 * Categories: sls-sections
 * Inserter: true
 *
 * @package SLS
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"tagName":"section","className":"cta-wrap newsletter"} -->
<section class="wp-block-group cta-wrap newsletter"><!-- wp:group {"className":"cta cta\u002d\u002dnewsletter"} -->
<div class="wp-block-group cta cta--newsletter"><!-- wp:group {"className":"cta__decor is-decorative"} -->
<div class="wp-block-group cta__decor is-decorative"><!-- wp:image {"className":"cta__grid"} -->
<figure class="wp-block-image cta__grid"><img src="<?php echo esc_url( sls_asset( 'assets/decor/cta-grid.svg' ) ); ?>" alt=""/></figure>
<!-- /wp:image -->

<!-- wp:image {"className":"cta__logo"} -->
<figure class="wp-block-image cta__logo"><img src="<?php echo esc_url( sls_asset( 'assets/logo-mark.svg' ) ); ?>" alt=""/></figure>
<!-- /wp:image --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"cta__inner"} -->
<div class="wp-block-group cta__inner"><!-- wp:group {"className":"cta__text"} -->
<div class="wp-block-group cta__text"><!-- wp:heading {"className":"cta__title"} -->
<h2 class="wp-block-heading cta__title">Get new articles in your inbox</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"cta__lead"} -->
<p class="cta__lead">One email when we publish. No spam, unsubscribe anytime.</p>
<!-- /wp:paragraph -->

<!-- wp:sls/subscribe-form /--></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
