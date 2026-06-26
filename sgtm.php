<?php
/*
Plugin Name: Simple Google Tag Manager
Plugin URI: 
Description: The simplest Google Tag Manager code inserter
Version: 0.2
Author: John Pennypacker
Author URI: https://pennypacker.net
*/

// the settings screen
if( is_admin() ) {
	include( 'inc/settings.php' );
}

/**  
 * A warpper to get the GTM Container ID. 
 */
function sgtm_get_id() {
	return get_option( 'sgtm_id', FALSE );
}

/**
 * Adds the js version of the GTM code to the <head>. 
 */
function sgtm_head() {
	$id = sgtm_get_id();
	$defer = get_option( 'sgtm_defer' );
	
	if ( $id ) {
		echo '<!-- Simple Google Tag Manager -->
<script>
	// this is the guts of the default anonymous function from the Googz.
	function initGTM( w, d, s, l, i ) {
		console.log( "initGTM", "fired", i );
		w[l] = w[l] || [];
		w[l].push({ "gtm.start": new Date().getTime(), event: "gtm.js" });
		var f = d.getElementsByTagName(s)[0],
			j = d.createElement(s),
			dl = l != "dataLayer" ? "&l=" + l : "";
		j.defer = true;
		j.src = "https://www.googletagmanager.com/gtm.js?id=" + i + dl;
		f.parentNode.insertBefore( j, f );
	}
';

		if( TRUE == $defer ) {
			echo '
// Monmouth wrote this. works great.
window.addEventListener("loadTracking", function(event) {
	initGTM( window, document, "script", "dataLayer", "' . esc_html( $id ) . '" );
}, false);

const loadTracking = new Event( "loadTracking" );
const triggerEvents = [
	"keydown", "mousedown", "mousemove", "touchmove", "touchstart", "touchend", "wheel", "visibilitychange"
];

function triggerTrackingScriptLoad() {
	// remove listeners for future loadTracking events
	triggerEvents.forEach( event => document.removeEventListener( event, triggerTrackingScriptLoad, false ) );
	// fire loadTracking event
	window.dispatchEvent( loadTracking );
}

// Parse the query string from the URL
const trackingURLParams = new URLSearchParams( window.location.search );

// Check if "sgtm=nodefer" is in the query string to ensure GTM loading
if ( "nodefer" === trackingURLParams.get( "sgtm" ) ) {
	window.addEventListener( "load", function( event ) {
		window.dispatchEvent( loadTracking );
	});
} else {
	triggerEvents.forEach( event => document.addEventListener( event, triggerTrackingScriptLoad, {
		passive: true
	}));
}
';
		} else {
			echo '
initGTM( window, document, "script", "dataLayer", "' . esc_html( $id ) . '" );';
		}
		
		
		echo '
</script>
<!-- End Simple Google Tag Manager -->
		';

	}

}
add_action( 'wp_head', 'sgtm_head', 0 );

/**
 * Adds the non-js version of the GTM code to the <body>. 
 */
function sgtm_body() {
	$id = sgtm_get_id();
	if ( $id ) {
		echo '<!-- Simple Google Tag Manager (noscript) -->
		<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=' . esc_html( $id ) . '"
		height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
		<!-- End Simple Google Tag Manager (noscript) -->';
	}
}
add_action( 'wp_body_open', 'sgtm_body' );

