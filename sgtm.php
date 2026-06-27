<?php
/*
Plugin Name: Simple Google Tag Manager
Plugin URI: 
Description: The simplest Google Tag Manager code inserter
Version: 0.9
Author: John Pennypacker
Author URI: https://pennypacker.net
*/

// the settings screen
if( is_admin() ) {
	include( 'inc/settings.php' );
	// the network-wide settings screen only exists on multisite
	if ( is_multisite() ) {
		include( 'inc/network-settings.php' );
	}
}

/**
 * Resolve a stored setting, honoring multisite network defaults and per-site overrides.
 *
 * On a single-site install this is simply get_option(). On multisite a site
 * inherits every network value unless it has opted to override them — and the
 * network admin can forbid overrides entirely.
 */
function sgtm_get_option( $key, $default = FALSE ) {
	if ( ! is_multisite() ) {
		return get_option( $key, $default );
	}
	// The network can force its values on every site.
	if ( get_site_option( 'sgtm_prevent_overrides', FALSE ) ) {
		return get_site_option( $key, $default );
	}
	// Otherwise the site inherits the network value unless it overrides.
	if ( get_option( 'sgtm_override', FALSE ) ) {
		return get_option( $key, $default );
	}
	return get_site_option( $key, $default );
}

/**
 * Get the resolved Container IDs as an array of individual, trimmed IDs.
 * The value is stored as a comma-separated string but may hold several IDs.
 */
function sgtm_get_ids() {
	$raw = sgtm_get_option( 'sgtm_id', FALSE );
	if ( empty( $raw ) ) {
		return array();
	}
	return array_values( array_filter( array_map( 'trim', explode( ',', $raw ) ) ) );
}

/**
 * A wrapper to get the resolved Defer loading flag.
 */
function sgtm_get_defer() {
	return (bool) sgtm_get_option( 'sgtm_defer', FALSE );
}

/**
 * Adds the js version of the GTM code to the <head>. 
 */
function sgtm_head() {
	$ids   = sgtm_get_ids();
	$defer = sgtm_get_defer();

	if ( empty( $ids ) ) {
		return;
	}

	// Build one init call per container so multiple IDs all load.
	$init_calls = '';
	foreach ( $ids as $id ) {
		$init_calls .= ' initGTM( window, document, "script", "dataLayer", "' . esc_js( $id ) . '" );' . "\n";
	}

	echo '<!-- Simple Google Tag Manager -->
<script>
	// this is the guts of the default anonymous function from the Googz.
	function initGTM( w, d, s, l, i ) {
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

	if ( $defer ) {
		echo '
// Monmouth wrote this. works great.
window.addEventListener("loadTracking", function(event) {
' . $init_calls . '}, false);

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
		echo "\n" . $init_calls;
	}

	echo '</script>
<!-- End Simple Google Tag Manager -->
';

}
add_action( 'wp_head', 'sgtm_head', 0 );

/**
 * Adds the non-js version of the GTM code to the <body>. 
 */
function sgtm_body() {
	$ids = sgtm_get_ids();
	if ( empty( $ids ) ) {
		return;
	}
	echo '<!-- Simple Google Tag Manager (noscript) -->';
	foreach ( $ids as $id ) {
		echo '
		<noscript><iframe src="' . esc_url( 'https://www.googletagmanager.com/ns.html?id=' . $id ) . '"
		height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>';
	}
	echo '
		<!-- End Simple Google Tag Manager (noscript) -->';
}
add_action( 'wp_body_open', 'sgtm_body' );

