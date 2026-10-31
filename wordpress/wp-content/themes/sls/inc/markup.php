<?php
/**
 * Front-end markup adapter for core blocks.
 *
 * Page content is authored with core blocks that carry the static site's class
 * names (e.g. a Group with className "acc-card"). This filter strips the wrapper
 * classes core adds and applies a few marker classes, so the rendered HTML
 * matches what css/styles.css was written against:
 *
 *   tag-ul / tag-ol / tag-li / tag-span / tag-nav  Render the Group or Paragraph as that element
 *                                                  (Image + tag-li renders <li><img></li>).
 *   inner-span    Paragraph: wrap the text in a <span>.
 *   li-span       List: wrap each item's text in a <span>.
 *   is-decorative Add aria-hidden="true".
 *
 * Buttons render as plain <a class="btn ..."> links, Images as bare <img>
 * elements, and Details blocks with className "faq__item" as the FAQ accordion.
 * A Group with tag-nav takes its aria-label from the block's name in List View.
 *
 * @package SLS
 */

defined( 'ABSPATH' ) || exit;

/** Classes that core adds to block wrappers, plus this theme's marker classes. */
const SLS_DROP_CLASS = '/^(?:wp-block-(?:group|heading|paragraph|list|buttons|button|image|separator|details|post-content|template-part)|wp-block-[a-z-]+-is-layout-[a-z-]+|is-layout-[a-z-]+|wp-container-[a-z0-9-]+|has-alpha-channel-opacity|has-global-padding|is-content-justification-[a-z-]+|is-nowrap|entry-content|tag-[a-z]+|inner-span|li-span|is-decorative)$/';

const SLS_TAGS = array( 'ul', 'ol', 'li', 'span', 'nav' );

// Per-block hooks run after the generic render_block filter (core uses them to
// add classes such as wp-block-paragraph), so adapt the markup last.
foreach ( array( 'group', 'heading', 'paragraph', 'list', 'buttons', 'button', 'image', 'separator', 'details', 'post-content', 'template-part' ) as $sls_block ) {
	add_filter( 'render_block_core/' . $sls_block, 'sls_adapt_block_markup', 999, 2 );
}
unset( $sls_block );

// Keep copy exactly as written: no curly-quote/dash conversion or "WordPress" respelling.
add_filter( 'run_wptexturize', '__return_false' );
foreach ( array( 'the_content', 'the_title', 'wp_title', 'document_title', 'comment_text' ) as $sls_hook ) {
	remove_filter( $sls_hook, 'capital_P_dangit', 11 );
}
unset( $sls_hook );

/**
 * Rewrite one rendered core block.
 *
 * @param string $html  Rendered block HTML.
 * @param array  $block Parsed block.
 */
function sls_adapt_block_markup( $html, $block ) {
	$name = $block['blockName'] ?? '';
	if ( is_admin() || '' === trim( (string) $html ) || ! str_starts_with( (string) $name, 'core/' ) ) {
		return $html;
	}

	$classes = preg_split( '/\s+/', trim( $block['attrs']['className'] ?? '' ), -1, PREG_SPLIT_NO_EMPTY );

	switch ( $name ) {
		case 'core/button':
			return sls_button_markup( $html, $classes );
		case 'core/image':
			return sls_image_markup( $html, $classes );
		case 'core/details':
			return in_array( 'faq__item', $classes, true ) ? sls_faq_item_markup( $html, $block ) : $html;
		case 'core/post-content':
			// Page sections sit directly in <main>, as in the static site.
			if ( ! $classes ) {
				return sls_unwrap( $html );
			}
			break;
		case 'core/template-part':
			if ( 'header' === ( $block['attrs']['slug'] ?? '' ) ) {
				return sls_unwrap( $html );
			}
			break;
		case 'core/group':
		case 'core/heading':
		case 'core/paragraph':
		case 'core/list':
		case 'core/buttons':
		case 'core/separator':
			break;
		default:
			return $html;
	}

	$html = sls_clean_outer_tag( $html, $classes, $block );

	foreach ( SLS_TAGS as $tag ) {
		if ( in_array( 'tag-' . $tag, $classes, true ) ) {
			$html = sls_rename_outer_tag( $html, $tag );
			break;
		}
	}

	if ( in_array( 'inner-span', $classes, true ) ) {
		$html = preg_replace( '/^(\s*<[^>]+>)(.*)(<\/[a-z0-9]+>\s*)$/s', '$1<span>$2</span>$3', $html );
	}

	if ( in_array( 'li-span', $classes, true ) ) {
		$html = preg_replace( '/<li>(.*?)<\/li>/s', '<li><span>$1</span></li>', $html );
	}

	return $html;
}

/**
 * Remove core wrapper/marker classes from the first tag and apply attribute markers.
 *
 * @param string $html    Block HTML.
 * @param array  $classes The block's className tokens.
 * @param array  $block   Parsed block.
 */
function sls_clean_outer_tag( string $html, array $classes, array $block ): string {
	$tags = new WP_HTML_Tag_Processor( $html );
	if ( ! $tags->next_tag() ) {
		return $html;
	}

	$class = sls_filter_classes( (string) $tags->get_attribute( 'class' ) );
	if ( '' === $class ) {
		$tags->remove_attribute( 'class' );
	} else {
		$tags->set_attribute( 'class', $class );
	}

	if ( in_array( 'is-decorative', $classes, true ) ) {
		$tags->set_attribute( 'aria-hidden', 'true' );
	}

	$label = $block['attrs']['metadata']['name'] ?? '';
	if ( $label && in_array( 'tag-nav', $classes, true ) ) {
		$tags->set_attribute( 'aria-label', $label );
	}

	return $tags->get_updated_html();
}

/**
 * Drop core and marker classes from a class attribute value.
 *
 * @param string $class Space-separated classes.
 */
function sls_filter_classes( string $class ): string {
	$keep = array_filter(
		preg_split( '/\s+/', trim( $class ), -1, PREG_SPLIT_NO_EMPTY ),
		fn( $token ) => ! preg_match( SLS_DROP_CLASS, $token )
	);
	return implode( ' ', array_unique( $keep ) );
}

/**
 * Change the outermost element of a fragment to another tag.
 *
 * @param string $html Fragment with a single root element.
 * @param string $tag  New tag name.
 */
function sls_rename_outer_tag( string $html, string $tag ): string {
	if ( ! preg_match( '/^\s*<([a-z0-9]+)/i', $html, $open ) ) {
		return $html;
	}
	$html = preg_replace( '/^(\s*)<' . $open[1] . '\b/i', '$1<' . $tag, $html, 1 );
	return preg_replace( '/<\/' . $open[1] . '>(\s*)$/i', '</' . $tag . '>$1', $html, 1 );
}

/**
 * Return only the inner HTML of a fragment's root element.
 *
 * @param string $html Fragment with a single root element.
 */
function sls_unwrap( string $html ): string {
	return preg_replace( '/^\s*<[^>]+>(.*)<\/[a-z0-9]+>\s*$/s', '$1', $html );
}

/**
 * Button block → <a class="btn ..."> (the wrapper div and core classes are dropped).
 *
 * @param string $html    Rendered core/button.
 * @param array  $classes Block className tokens.
 */
function sls_button_markup( string $html, array $classes ): string {
	if ( ! preg_match( '/<a\b[^>]*>(.*?)<\/a>/s', $html, $link ) ) {
		return $html;
	}
	$tags = new WP_HTML_Tag_Processor( $link[0] );
	$tags->next_tag( 'a' );
	$tags->set_attribute( 'class', sls_filter_classes( implode( ' ', $classes ) ) );
	return $tags->get_updated_html();
}

/**
 * Image block → bare <img> carrying the block's classes (or <li><img></li> with tag-li).
 *
 * @param string $html    Rendered core/image.
 * @param array  $classes Block className tokens.
 */
function sls_image_markup( string $html, array $classes ): string {
	if ( ! preg_match( '/<img\b[^>]*>/', $html, $img ) ) {
		return $html;
	}
	$tags = new WP_HTML_Tag_Processor( $img[0] );
	$tags->next_tag( 'img' );
	$class = sls_filter_classes( trim( $tags->get_attribute( 'class' ) . ' ' . implode( ' ', $classes ) ) );
	if ( $class ) {
		$tags->set_attribute( 'class', $class );
	} else {
		$tags->remove_attribute( 'class' );
	}
	if ( in_array( 'is-decorative', $classes, true ) ) {
		$tags->set_attribute( 'aria-hidden', 'true' );
	}
	$out = $tags->get_updated_html();
	return in_array( 'tag-li', $classes, true ) ? '<li>' . $out . '</li>' : $out;
}

/**
 * Details block (className faq__item) → the site's accessible FAQ accordion item.
 *
 * @param string $html  Rendered core/details.
 * @param array  $block Parsed block.
 */
function sls_faq_item_markup( string $html, array $block ): string {
	static $n = 0;
	if ( ! preg_match( '/<summary[^>]*>(.*?)<\/summary>(.*)<\/details>\s*$/s', $html, $parts ) ) {
		return $html;
	}
	++$n;
	$open = ! empty( $block['attrs']['showContent'] );

	return sprintf(
		'<div class="faq__item%1$s"><h3><button class="faq__trigger" type="button" id="faq-q%2$d" aria-expanded="%3$s" aria-controls="faq-a%2$d"><span class="faq__question">%4$s</span><span class="faq__icon">%5$s</span></button></h3><div class="faq__panel" id="faq-a%2$d" role="region" aria-labelledby="faq-q%2$d"%6$s><div><div class="faq__answer">%7$s</div></div></div></div>',
		$open ? ' is-open' : '',
		$n,
		$open ? 'true' : 'false',
		$parts[1],
		sls_icon( 'plus' ),
		$open ? '' : ' inert',
		trim( $parts[2] )
	);
}
