<?php
/**
 * Description: Create admin settings screen
 */

// Block direct requests
if ( !defined('ABSPATH') )
	die('-1');

/**
 * Add the settings page to the settings menu.
 * @see https://developer.wordpress.org/reference/functions/add_options_page/
 */
function sgtm_settings_menu_item() {
	add_options_page(
		__( 'Google Tag Manager', 'sgtm' ), // page title
		__( 'Google Tag Manager', 'sgtm' ), // menu title
		'manage_options', // capability
		'sgtm-settings', // menu slug
		'sgtm_settings_page' // callback
	);
}
add_action( 'admin_menu', 'sgtm_settings_menu_item' );


/**
 * Register the settings with WordPress.
 */
function sgtm_settings_init() {
	register_setting (
		'sgtm_settings', // option group
		'sgtm_id', // option name
		array(
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest' => TRUE,
			'type' => 'string'
		)
	);
}
add_action( 'admin_init', 'sgtm_settings_init' );


/**
 * Render the settings page.
 */
function sgtm_settings_page() {

	if ( ! current_user_can( 'manage_options' ) ) {
		echo '<div id="setting-message-denied" class="updated settings-error notice is-dismissible"> 
<p><strong>You do not have permission to use this form.</strong></p>
<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
		return;
	}

	if( ! empty ( $_POST ) ) {	
		echo '<div id="setting-message" class="updated settings-message notice is-dismissible"> 
<p><strong>Data refreshed.</strong></p>
<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
	}


?>
<div class="wrap">
<h1>Stupid simple Google Tag Manager settings</h1>

<form method="post" action="options.php">
	<?php settings_fields( 'sgtm_settings' ); ?>
	<?php do_settings_sections( 'sgtm_settings' ); ?>
	<label><strong>Container ID</strong>
	<input type="text" class="regular-text" aria-describedby="sgtm-id-desc" name="sgtm_id" id="sgtm-id" value="<?php echo get_option( 'sgtm_id', '' ) ?>">
	<p class="sgtm-desc">Enter your Google Tag Manager Container ID. It should look like GTM-Z3V1L.</p>
	</label>
	<?php submit_button( 'Save Settings' ); ?>
</form>

</div>
<?php } ?>