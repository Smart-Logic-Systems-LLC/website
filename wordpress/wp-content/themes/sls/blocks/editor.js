/**
 * Editor side of the theme blocks (plain JS, no build step).
 *
 * Every theme block is server-rendered: the editor shows a live preview via
 * ServerSideRender and edits attributes in the block sidebar. Also registers
 * the rich-text formats used in headings (highlight underlines, oblique emphasis).
 */
( function ( wp, config ) {
	'use strict';

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var __ = wp.i18n.__;
	var blockEditor = wp.blockEditor;
	var c = wp.components;
	var ServerSideRender = wp.serverSideRender;

	var typeOptions = Object.keys( config.types ).map( function ( slug ) {
		return { value: slug, label: config.types[ slug ] };
	} );
	var iconOptions = config.icons.map( function ( name ) {
		return { value: name, label: name };
	} );

	/* ----- Field controls ---------------------------------------------------- */
	function control( field, value, onChange ) {
		if ( field.type === 'toggle' ) {
			return el( c.ToggleControl, { label: field.label, checked: !! value, onChange: onChange, __nextHasNoMarginBottom: true } );
		}
		if ( field.type === 'select' ) {
			return el( c.SelectControl, { label: field.label, value: value, options: field.options, onChange: onChange, __next40pxDefaultSize: true, __nextHasNoMarginBottom: true } );
		}
		return el( c.TextControl, { label: field.label, value: value || '', help: field.help, onChange: onChange, __next40pxDefaultSize: true, __nextHasNoMarginBottom: true } );
	}

	/** Repeater for array attributes: one card of fields per item. */
	function listControl( field, items, onChange ) {
		items = items || [];
		function update( index, key, value ) {
			var next = items.slice();
			next[ index ] = Object.assign( {}, next[ index ] );
			next[ index ][ key ] = value;
			onChange( next );
		}
		function move( index, delta ) {
			var next = items.slice();
			var item = next.splice( index, 1 )[ 0 ];
			next.splice( index + delta, 0, item );
			onChange( next );
		}
		function remove( index ) {
			onChange( items.filter( function ( _, i ) { return i !== index; } ) );
		}
		function add() {
			var blank = {};
			field.fields.forEach( function ( f ) {
				blank[ f.key ] = f.type === 'select' ? f.options[ 0 ].value : '';
			} );
			onChange( items.concat( [ blank ] ) );
		}

		return el( 'div', { className: 'sls-list-control' },
			items.map( function ( item, index ) {
				return el( 'div', { key: index, className: 'sls-list-control__item' },
					el( 'p', { className: 'sls-list-control__heading' }, field.itemLabel + ' ' + ( index + 1 ) ),
					field.fields.map( function ( f ) {
						return el( 'div', { key: f.key, className: 'sls-list-control__field' },
							control( f, item[ f.key ], function ( value ) { update( index, f.key, value ); } ) );
					} ),
					el( 'div', { className: 'sls-list-control__actions' },
						el( c.Button, { size: 'small', variant: 'tertiary', disabled: index === 0, onClick: function () { move( index, -1 ); } }, __( 'Move up', 'sls' ) ),
						el( c.Button, { size: 'small', variant: 'tertiary', disabled: index === items.length - 1, onClick: function () { move( index, 1 ); } }, __( 'Move down', 'sls' ) ),
						el( c.Button, { size: 'small', variant: 'tertiary', isDestructive: true, onClick: function () { remove( index ); } }, __( 'Remove', 'sls' ) )
					)
				);
			} ),
			el( c.Button, { variant: 'secondary', onClick: add }, __( 'Add', 'sls' ) + ' ' + field.itemLabel.toLowerCase() )
		);
	}

	/* ----- Block registration ------------------------------------------------ */
	function register( name, panels ) {
		wp.blocks.registerBlockType( name, {
			edit: function ( props ) {
				var attrs = props.attributes;
				var inspector = el( blockEditor.InspectorControls, null,
					panels.map( function ( panel ) {
						return el( c.PanelBody, { key: panel.title, title: panel.title, initialOpen: panel.open !== false },
							panel.fields.map( function ( field ) {
								var set = function ( value ) {
									var update = {};
									update[ field.key ] = value;
									props.setAttributes( update );
								};
								return el( 'div', { key: field.key, className: 'sls-field' },
									field.type === 'list' ? listControl( field, attrs[ field.key ], set ) : control( field, attrs[ field.key ], set ) );
							} )
						);
					} )
				);
				return el( Fragment, null,
					inspector,
					el( 'div', blockEditor.useBlockProps( { className: 'sls-block-preview' } ),
						el( ServerSideRender, { block: name, attributes: attrs, httpMethod: 'POST' } ) )
				);
			},
			save: function () {
				return null;
			},
		} );
	}

	register( 'sls/site-header', [ { title: __( 'Contact button', 'sls' ), fields: [
		{ key: 'ctaLabel', label: __( 'Label', 'sls' ) },
		{ key: 'ctaUrl', label: __( 'Link', 'sls' ), help: __( 'Menu links are managed under Appearance › Menus.', 'sls' ) },
	] } ] );

	register( 'sls/brand', [ { title: __( 'Brand', 'sls' ), fields: [
		{ key: 'large', label: __( 'Large (footer) size', 'sls' ), type: 'toggle' },
		{ key: 'link', label: __( 'Link to home page', 'sls' ), type: 'toggle' },
		{ key: 'line1', label: __( 'First line', 'sls' ) },
		{ key: 'line2', label: __( 'Second line', 'sls' ) },
	] } ] );

	register( 'sls/social-links', [ { title: __( 'Profiles', 'sls' ), fields: [
		{ key: 'x', label: 'X (Twitter) URL' },
		{ key: 'facebook', label: 'Facebook URL' },
		{ key: 'instagram', label: 'Instagram URL' },
		{ key: 'linkedin', label: 'LinkedIn URL', help: __( 'Leave a field empty to hide that icon; "#" marks a placeholder.', 'sls' ) },
	] } ] );

	register( 'sls/icon', [ { title: __( 'Icon', 'sls' ), fields: [
		{ key: 'icon', label: __( 'Icon', 'sls' ), type: 'select', options: iconOptions },
	] } ] );

	register( 'sls/solution-finder', [
		{ title: __( 'Text', 'sls' ), fields: [
			{ key: 'title', label: __( 'Title', 'sls' ) },
			{ key: 'placeholder', label: __( 'Search placeholder', 'sls' ) },
			{ key: 'countText', label: __( 'Footer text', 'sls' ) },
			{ key: 'linkLabel', label: __( 'Browse link label', 'sls' ) },
		] },
		{ title: __( 'Quick links', 'sls' ), fields: [
			{ key: 'items', type: 'list', itemLabel: __( 'Link', 'sls' ), fields: [
				{ key: 'label', label: __( 'Solution name', 'sls' ), help: __( 'Clicking searches the directory for this text.', 'sls' ) },
				{ key: 'type', label: __( 'Type', 'sls' ), type: 'select', options: typeOptions },
			] },
		] },
	] );

	register( 'sls/solutions-directory', [
		{ title: __( 'Solutions', 'sls' ), fields: [
			{ key: 'rows', type: 'list', itemLabel: __( 'Solution', 'sls' ), fields: [
				{ key: 'name', label: __( 'Name', 'sls' ) },
				{ key: 'desc', label: __( 'Description', 'sls' ) },
				{ key: 'type', label: __( 'Type', 'sls' ), type: 'select', options: typeOptions },
				{ key: 'best', label: __( 'Best for', 'sls' ) },
			] },
		] },
		{ title: __( 'Text', 'sls' ), open: false, fields: [
			{ key: 'searchLabel', label: __( 'Search label', 'sls' ) },
			{ key: 'placeholder', label: __( 'Search placeholder', 'sls' ) },
			{ key: 'emptyText', label: __( 'No results message', 'sls' ) },
		] },
	] );

	register( 'sls/contact-links', [ { title: __( 'Contact details', 'sls' ), fields: [
		{ key: 'email', label: __( 'Email', 'sls' ) },
		{ key: 'phone', label: __( 'Phone', 'sls' ), help: __( 'Shown as typed; the call link is built from its digits (+1 assumed unless it starts with +).', 'sls' ) },
	] } ] );

	register( 'sls/contact-form', [
		{ title: __( 'Form', 'sls' ), fields: [
			{ key: 'title', label: __( 'Title', 'sls' ) },
			{ key: 'submitLabel', label: __( 'Button label', 'sls' ) },
			{ key: 'endpoint', label: __( 'Submit endpoint (optional)', 'sls' ), help: __( 'URL that accepts a POST of the form fields. Without one, the form opens the visitor\'s email app.', 'sls' ) },
		] },
		{ title: __( 'Reasons', 'sls' ), fields: [
			{ key: 'reasons', type: 'list', itemLabel: __( 'Reason', 'sls' ), fields: [
				{ key: 'label', label: __( 'Label', 'sls' ) },
				{ key: 'value', label: __( 'Value', 'sls' ), help: __( 'Used in links such as /contact/?reason=sales', 'sls' ) },
				{ key: 'email', label: __( 'Send to', 'sls' ) },
			] },
		] },
	] );

	register( 'sls/subscribe-form', [ { title: __( 'Form', 'sls' ), fields: [
		{ key: 'placeholder', label: __( 'Placeholder', 'sls' ) },
		{ key: 'buttonLabel', label: __( 'Button label', 'sls' ) },
		{ key: 'endpoint', label: __( 'Submit endpoint (optional)', 'sls' ), help: __( 'Newsletter provider URL that accepts a POST with an "email" field.', 'sls' ) },
	] } ] );

	/* ----- Rich-text formats ------------------------------------------------- */
	function format( name, title, tagName, className, icon ) {
		wp.richText.registerFormatType( name, {
			title: title,
			tagName: tagName,
			className: className,
			edit: function ( props ) {
				return el( blockEditor.RichTextToolbarButton, {
					icon: icon,
					title: title,
					isActive: props.isActive,
					onClick: function () {
						props.onChange( wp.richText.toggleFormat( props.value, { type: name } ) );
					},
				} );
			},
		} );
	}

	format( 'sls/hl-gold', __( 'Gold underline', 'sls' ), 'span', 'hl--gold', 'admin-customizer' );
	format( 'sls/hl-green', __( 'Green underline', 'sls' ), 'span', 'hl--green', 'admin-customizer' );
	format( 'sls/em-italic', __( 'Oblique emphasis', 'sls' ), 'em', 'em-italic', 'editor-italic' );
} )( window.wp, window.slsBlocks );
