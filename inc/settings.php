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
		__( 'Simple Google Tag Manager', 'sgtm' ), // page title
		__( 'Simple Google Tag Manager', 'sgtm' ), // menu title
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
	register_setting (
		'sgtm_settings', // option group
		'sgtm_defer', // option name
		array(
			'sanitize_callback' => 'sgtm_sanitize_checkbox',
			'show_in_rest' => TRUE,
			'type' => 'boolean',
			'default' => FALSE
		)
	);

	add_settings_section(
		'sgtm_settings_section', // section id
		'', // title (none — the page <h1> is enough)
		'__return_false', // no intro callback
		'sgtm_settings' // page slug (matches do_settings_sections)
	);

	add_settings_field(
		'sgtm_id', // field id
		'Container ID', // label (rendered in the <th>)
		'sgtm_field_id', // render callback
		'sgtm_settings', // page slug
		'sgtm_settings_section', // section id
		array( 'label_for' => 'sgtm-id' )
	);

	add_settings_field(
		'sgtm_defer', // field id
		'Defer loading', // label (rendered in the <th>)
		'sgtm_field_defer', // render callback
		'sgtm_settings', // page slug
		'sgtm_settings_section' // section id
	);
}
add_action( 'admin_init', 'sgtm_settings_init' );


/**
 * Sanitize a checkbox value into a boolean.
 */
function sgtm_sanitize_checkbox( $value ) {
	return ! empty( $value );
}


/**
 * Render the Container ID field.
 */
function sgtm_field_id() {
	?>
	<input type="text" class="regular-text" aria-describedby="sgtm-id-desc" name="sgtm_id" id="sgtm-id" value="<?php echo esc_attr( get_option( 'sgtm_id', '' ) ); ?>">
	<p class="description" id="sgtm-id-desc">Enter your Google Tag Manager Container ID. It should look like GTM-Z3V1L.</p>
	<?php
}


/**
 * Render the Defer loading field.
 */
function sgtm_field_defer() {
	?>
	<label for="sgtm-defer">
		<input type="checkbox" aria-describedby="sgtm-defer-desc" name="sgtm_defer" id="sgtm-defer" value="1" <?php checked( get_option( 'sgtm_defer', FALSE ) ); ?>>
		Load Google Tag Manager after the user interacts with the page.
	</label>
	<p class="description" id="sgtm-defer-desc">This will reduce your pageviews and other data, but it'll weed out many bots and bounces, and it'll improve your page speed.</p>
	<?php
}


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
<h1>Simple Google Tag Manager settings</h1>

<form method="post" action="options.php">
	<?php settings_fields( 'sgtm_settings' ); ?>
	<?php do_settings_sections( 'sgtm_settings' ); ?>
	<?php submit_button( 'Save Settings' ); ?>
</form>

</div>
<?php } ?>