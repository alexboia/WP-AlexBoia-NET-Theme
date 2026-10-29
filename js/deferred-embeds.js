/**
 * deferred-embeds.js
 *
 * Loads embeds (such as iframes) only after the page has fully loaded,
 * showing a loading indicator until the embed itself has loaded.
 *
 * Markup:
 *   <div class="abnet-deferred-embed" style="width: 305px; height: 150px;">
 *     <template><iframe src="..." ...></iframe></template>
 *   </div>
 *
 * The browser does not load what is inside <template>; this script moves it
 * into the page once the window "load" event has fired.
 */
( function() {
	var LOAD_TIMEOUT_MS = 15000;

	function markLoaded( container ) {
		container.classList.remove( 'is-loading' );
		container.classList.add( 'is-loaded' );
		container.removeAttribute( 'aria-busy' );
	}

	function loadEmbed( container ) {
		var template = container.querySelector( 'template' ),
			content, frame, timeoutId;

		if ( ! template || container.classList.contains( 'is-loading' ) || container.classList.contains( 'is-loaded' ) )
			return;

		content = template.content.cloneNode( true );
		frame = content.querySelector( 'iframe' );

		container.classList.add( 'is-loading' );
		container.setAttribute( 'aria-busy', 'true' );

		if ( frame ) {
			// Do not keep the indicator forever if the embed never reports it has loaded.
			timeoutId = window.setTimeout( function() {
				markLoaded( container );
			}, LOAD_TIMEOUT_MS );

			frame.addEventListener( 'load', function() {
				window.clearTimeout( timeoutId );
				markLoaded( container );
			} );
		}

		container.appendChild( content );
		template.parentNode.removeChild( template );

		if ( ! frame )
			markLoaded( container );
	}

	function loadAll() {
		var containers = document.querySelectorAll( '.abnet-deferred-embed' ), i;
		for ( i = 0; i < containers.length; i++ )
			loadEmbed( containers[i] );
	}

	if ( document.readyState === 'complete' )
		loadAll();
	else
		window.addEventListener( 'load', loadAll );
} )();
