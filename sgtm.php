<?php
/*
Plugin Name: Simple Google Tag Manager
Plugin URI: 
Description: The simplest Google Tag Manager code inserter
Version: 0.1
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
	if ( $id ) {
		echo "<!-- Simple Google Tag Manager -->
		<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
		new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
		j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
		'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
		})(window,document,'script','dataLayer','" . $id . "');</script>
		<!-- End Simple Google Tag Manager -->";
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
		<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=' . $id . '"
		height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
		<!-- End Simple Google Tag Manager (noscript) -->';
	}
}
add_action( 'wp_body_open', 'sgtm_body' );

