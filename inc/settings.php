<?php
/**
 * Description: Create the per-site admin settings screen
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
 *
 * Standalone (non-multisite) installs save through the Settings API / options.php.
 * On multisite the per-site form is saved by hand (see sgtm_settings_page) so it
 * can delete the site options when a site stops overriding the network.
 */
function sgtm_settings_init() {
	register_setting (
		'sgtm_settings', // option group
		'sgtm_id', // option name
		array(
			'sanitize_callback' => 'sgtm_sanitize_ids',
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
 * Sanitize a comma-separated list of GTM Container IDs.
 *
 * Splits on commas, trims, upper-cases, validates each against the GTM-XXXX
 * shape, drops anything invalid or duplicated, and returns a normalized,
 * comma-separated string.
 */
function sgtm_sanitize_ids( $value ) {
	$ids   = array_map( 'trim', explode( ',', (string) $value ) );
	$valid = array();
	foreach ( $ids as $id ) {
		$id = strtoupper( $id );
		if ( preg_match( '/^GTM-[A-Z0-9]+$/', $id ) && ! in_array( $id, $valid, TRUE ) ) {
			$valid[] = $id;
		}
	}
	return implode( ', ', $valid );
}


/**
 * Render the Container ID field (Settings API callback, standalone installs).
 */
function sgtm_field_id() {
	?>
	<input type="text" class="regular-text" aria-describedby="sgtm-id-desc" name="sgtm_id" id="sgtm-id" value="<?php echo esc_attr( get_option( 'sgtm_id', '' ) ); ?>">
	<p class="description" id="sgtm-id-desc">Enter your Google Tag Manager Container ID. It should look like GTM-Z3V1L. Separate multiple IDs with commas.</p>
	<?php
}


/**
 * Render the Defer loading field (Settings API callback, standalone installs).
 */
function sgtm_field_defer() {
	?>
	<label for="sgtm-defer">
		<input type="checkbox" aria-describedby="sgtm-defer-desc" name="sgtm_defer" id="sgtm-defer" value="1" <?php checked( get_option( 'sgtm_defer', FALSE ) ); ?>>
		Load Google Tag Manager after the user interacts with the page.
	</label>
	<p class="description" id="sgtm-defer-desc">This may reduce pageviews, but it'll weed out many bots and bounces It'll also improve page speed.</p>
	<?php
}


/**
 * Render the settings page.
 *
 * Three shapes:
 *   - standalone install      → plain Container ID + Defer fields
 *   - multisite, can override → one override toggle that activates the fields
 *   - multisite, locked       → read-only view of the enforced network values
 */
function sgtm_settings_page() {

	if ( ! current_user_can( 'manage_options' ) ) {
		echo '<div id="setting-message-denied" class="updated settings-error notice is-dismissible">
<p><strong>You do not have permission to use this form.</strong></p>
<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
		return;
	}

	if ( is_multisite() ) {
		sgtm_settings_page_network_member();
		return;
	}

	sgtm_settings_page_standalone();
}


/**
 * Per-site settings on a standalone (non-multisite) install: just the fields.
 */
function sgtm_settings_page_standalone() {
?>
<div class="wrap">
<h1>Simple Google Tag Manager settings</h1>

<form method="post" action="options.php">
	<?php settings_fields( 'sgtm_settings' ); ?>
	<?php do_settings_sections( 'sgtm_settings' ); ?>
	<?php submit_button( 'Save Settings' ); ?>
</form>

</div>
<?php
}


/**
 * Per-site settings for a site that belongs to a network.
 */
function sgtm_settings_page_network_member() {

	// When the network forbids overrides, this page is read-only.
	if ( get_site_option( 'sgtm_prevent_overrides', FALSE ) ) {
		sgtm_settings_page_locked();
		return;
	}

	// Handle our own save so we can delete the site options when not overriding.
	if ( isset( $_POST['sgtm_settings_submit'] ) ) {
		check_admin_referer( 'sgtm_site_settings' );
		if ( ! empty( $_POST['sgtm_override'] ) ) {
			update_option( 'sgtm_override', TRUE );
			update_option( 'sgtm_id', sgtm_sanitize_ids( isset( $_POST['sgtm_id'] ) ? $_POST['sgtm_id'] : '' ) );
			update_option( 'sgtm_defer', sgtm_sanitize_checkbox( isset( $_POST['sgtm_defer'] ) ? $_POST['sgtm_defer'] : FALSE ) );
		} else {
			// Stop overriding: fall back to the network by removing the site values.
			delete_option( 'sgtm_override' );
			delete_option( 'sgtm_id' );
			delete_option( 'sgtm_defer' );
		}
		echo '<div id="setting-message" class="updated notice is-dismissible"><p><strong>Settings saved.</strong></p></div>';
	}

	$overriding = get_option( 'sgtm_override', FALSE );
	$net_id     = get_site_option( 'sgtm_id', '' );
	$net_defer  = get_site_option( 'sgtm_defer', FALSE );
?>
<div class="wrap">
<h1>Simple Google Tag Manager settings</h1>

<style>#sgtm-fields[disabled]{opacity:.5;}</style>

<form method="post" action="">
	<?php wp_nonce_field( 'sgtm_site_settings' ); ?>

	<p>
		<label for="sgtm-override">
			<input type="checkbox" id="sgtm-override" name="sgtm_override" value="1" <?php checked( $overriding ); ?>>
			Override the network Google Tag Manager settings for this site.
		</label>
	</p>

	<fieldset id="sgtm-fields"<?php disabled( ! $overriding ); ?>>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="sgtm-id">Container ID</label></th>
				<td>
					<input type="text" class="regular-text" aria-describedby="sgtm-id-desc" name="sgtm_id" id="sgtm-id" value="<?php echo esc_attr( get_option( 'sgtm_id', '' ) ); ?>">
					<p class="description" id="sgtm-id-desc">Enter your Google Tag Manager Container ID. It should look like GTM-Z3V1L. Separate multiple IDs with commas.<br>Network default: <code><?php echo esc_html( $net_id ?: '(not set)' ); ?></code></p>
				</td>
			</tr>
			<tr>
				<th scope="row">Defer loading</th>
				<td>
					<label for="sgtm-defer">
						<input type="checkbox" aria-describedby="sgtm-defer-desc" name="sgtm_defer" id="sgtm-defer" value="1" <?php checked( get_option( 'sgtm_defer', FALSE ) ); ?>>
						Load Google Tag Manager after the user interacts with the page.
					</label>
					<p class="description" id="sgtm-defer-desc">This may reduce pageviews, but it'll weed out many bots and bounces It'll also improve page speed.<br>Network default: <code><?php echo $net_defer ? 'On' : 'Off'; ?></code></p>
				</td>
			</tr>
		</table>
	</fieldset>

	<input type="hidden" name="sgtm_settings_submit" value="1">
	<?php submit_button( 'Save Settings' ); ?>
</form>

<script>
( function() {
	var cb = document.getElementById( 'sgtm-override' );
	var fs = document.getElementById( 'sgtm-fields' );
	if ( ! cb || ! fs ) {
		return;
	}
	function sync() {
		fs.disabled = ! cb.checked;
	}
	cb.addEventListener( 'change', sync );
	sync();
} )();
</script>

</div>
<?php
}


/**
 * Read-only per-site view when the network has locked the settings.
 */
function sgtm_settings_page_locked() {
?>
<div class="wrap">
<h1>Simple Google Tag Manager settings</h1>

<div class="notice notice-info inline">
	<p>These settings are managed by your network administrator and can't be changed here.</p>
</div>
<table class="form-table" role="presentation">
	<tr>
		<th scope="row">Container ID</th>
		<td><code><?php echo esc_html( get_site_option( 'sgtm_id', '' ) ?: '(not set)' ); ?></code></td>
	</tr>
	<tr>
		<th scope="row">Defer loading</th>
		<td><?php echo get_site_option( 'sgtm_defer', FALSE ) ? 'On' : 'Off'; ?></td>
	</tr>
</table>

</div>
<?php
}
