<?php
/**
 * Title: Home hero
 * Slug: sls/hero-home
 * Categories: sls-sections
 * Inserter: true
 *
 * @package SLS
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"tagName":"section","className":"hero hero\u002d\u002dhome"} -->
<section class="wp-block-group hero hero--home"><!-- wp:group {"className":"hero__decor is-decorative"} -->
<div class="wp-block-group hero__decor is-decorative"><!-- wp:image {"className":"hero__grid"} -->
<figure class="wp-block-image hero__grid"><img src="<?php echo esc_url( sls_asset( 'assets/decor/hero-grid-home.svg' ) ); ?>" alt=""/></figure>
<!-- /wp:image --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"container"} -->
<div class="wp-block-group container"><!-- wp:paragraph {"className":"eyebrow-pill"} -->
<p class="eyebrow-pill">Enterprise Technology Accelerators</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1,"className":"display-title"} -->
<h1 class="wp-block-heading display-title">Skip the Boilerplate, <br>Save the <span class="hl hl--gold">Time</span> and <span class="hl hl--green">Money</span> <br>for what matters.</h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"lead"} -->
<p class="lead">Smart Logic Systems delivers out-of-the-box accelerators that eliminate weeks of setup, so your team focuses on what matters — building great software.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"className":"btn-group"} -->
<div class="wp-block-buttons btn-group"><!-- wp:button {"className":"btn btn\u002d\u002dprimary"} -->
<div class="wp-block-button btn btn--primary"><a class="wp-block-button__link wp-element-button" href="/solutions/#featured">Explore Accelerators</a></div>
<!-- /wp:button -->

<!-- wp:button {"className":"btn btn\u002d\u002doutline"} -->
<div class="wp-block-button btn btn--outline"><a class="wp-block-button__link wp-element-button" href="/solutions/#directory">Browse Solutions</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
