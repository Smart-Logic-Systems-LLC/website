<?php
/**
 * Title: Articles hero
 * Slug: sls/hero-articles
 * Categories: sls-sections
 * Inserter: true
 *
 * @package SLS
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"tagName":"section","className":"hero hero\u002d\u002dcompact"} -->
<section class="wp-block-group hero hero--compact"><!-- wp:group {"className":"hero__decor is-decorative"} -->
<div class="wp-block-group hero__decor is-decorative"><!-- wp:group {"className":"hero__glow tag-span"} -->
<div class="wp-block-group hero__glow tag-span"></div>
<!-- /wp:group -->

<!-- wp:image {"className":"hero__grid"} -->
<figure class="wp-block-image hero__grid"><img src="<?php echo esc_url( sls_asset( 'assets/decor/hero-grid.svg' ) ); ?>" alt=""/></figure>
<!-- /wp:image --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"container"} -->
<div class="wp-block-group container"><!-- wp:group {"className":"hero__intro"} -->
<div class="wp-block-group hero__intro"><!-- wp:heading {"level":1,"className":"page-title"} -->
<h1 class="wp-block-heading page-title">Insight that Saves you Time.</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"lead lead\u002d\u002dsm"} -->
<p class="lead lead--sm">Engineering and business insight from the consultants building SLS's accelerators — written to save you the research time we've already put in.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
