<?php
/**
 * Title: Solutions hero
 * Slug: sls/hero-solutions
 * Categories: sls-sections
 * Inserter: true
 *
 * @package SLS
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"tagName":"section","className":"hero hero\u002d\u002dpage hero\u002d\u002dsolutions"} -->
<section class="wp-block-group hero hero--page hero--solutions"><!-- wp:group {"className":"hero__decor is-decorative"} -->
<div class="wp-block-group hero__decor is-decorative"><!-- wp:group {"className":"hero__glow tag-span"} -->
<div class="wp-block-group hero__glow tag-span"></div>
<!-- /wp:group -->

<!-- wp:image {"className":"hero__grid"} -->
<figure class="wp-block-image hero__grid"><img src="<?php echo esc_url( sls_asset( 'assets/decor/hero-grid.svg' ) ); ?>" alt=""/></figure>
<!-- /wp:image --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"container hero__split"} -->
<div class="wp-block-group container hero__split"><!-- wp:group {"className":"hero__intro"} -->
<div class="wp-block-group hero__intro"><!-- wp:heading {"level":1,"className":"page-title"} -->
<h1 class="wp-block-heading page-title">Every accelerator we've built, in one place!</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"lead lead\u002d\u002dsm"} -->
<p class="lead lead--sm">Start with our most-used accelerators below, or search the full directory as it grows.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:sls/solution-finder /--></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
