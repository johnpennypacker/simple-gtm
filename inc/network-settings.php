<?php
/**
 * Description: Create the network-wide admin settings screen (multisite only).
 *
 * The Settings API's options.php flow only persists per-site options, so the
 * network settings are saved by hand against a network_admin_edit_* action.
 */

// Block direct requests
if ( ! defined( 'ABSPATH' ) )
	die( '-1' );

/**
 * Add the network settings page under the Network Admin → Settings menu.
 */
function sgtm_network_settings_menu_item() {
	add_submenu_page(
		'settings.php', // parent (Network Admin → Settings)
		__( 'Simple Google Tag Manager', 'sgtm' ), // page title
		__( 'Simple Google Tag Manager', 'sgtm' ), // menu title
		'manage_network_options', // capability
		'sgtm-network-settings', // menu slug
		'sgtm_network_settings_page' // callback
	);
}
add_action( 'network_admin_menu', 'sgtm_network_settings_menu_item' );


/**
 * Persist the network settings.
 *
 * Hooked to network_admin_edit_sgtm_network_settings, which fires when the
 * form below posts to edit.php?action=sgtm_network_settings.
 */
function sgtm_network_settings_save() {
	if ( ! current_user_can( 'manage_network_options' ) ) {
		wp_die( 'You do not have permission to change these settings.' );
	}
	check_admin_referer( 'sgtm_network_settings' );

	update_site_option( 'sgtm_id', sgtm_sanitize_ids( isset( $_POST['sgtm_id'] ) ? $_POST['sgtm_id'] : '' ) );
	update_site_option( 'sgtm_defer', sgtm_sanitize_checkbox( isset( $_POST['sgtm_defer'] ) ? $_POST['sgtm_defer'] : FALSE ) );
	update_site_option( 'sgtm_prevent_overrides', sgtm_sanitize_checkbox( isset( $_POST['sgtm_prevent_overrides'] ) ? $_POST['sgtm_prevent_overrides'] : FALSE ) );

	wp_safe_redirect( add_query_arg(
		array( 'page' => 'sgtm-network-settings', 'updated' => 'true' ),
		network_admin_url( 'settings.php' )
	) );
	exit;
}
add_action( 'network_admin_edit_sgtm_network_settings', 'sgtm_network_settings_save' );


/**
 * Render the network settings page.
 */
function sgtm_network_settings_page() {

	if ( ! current_user_can( 'manage_network_options' ) ) {
		return;
	}

?>
<div class="wrap">
<h1>Simple Google Tag Manager network settings</h1>

<?php if ( isset( $_GET['updated'] ) ) : ?>
	<div id="setting-message" class="updated notice is-dismissible"><p><strong>Settings saved.</strong></p></div>
<?php endif; ?>

<p>These values apply to every site in the network. Individual sites can override them unless you prevent overrides below.</p>

<form method="post" action="<?php echo esc_url( network_admin_url( 'edit.php?action=sgtm_network_settings' ) ); ?>">
	<?php wp_nonce_field( 'sgtm_network_settings' ); ?>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><label for="sgtm-id">Container ID</label></th>
			<td>
				<input type="text" class="regular-text" aria-describedby="sgtm-id-desc" name="sgtm_id" id="sgtm-id" value="<?php echo esc_attr( get_site_option( 'sgtm_id', '' ) ); ?>">
				<p class="description" id="sgtm-id-desc">Enter your Google Tag Manager Container ID. It should look like GTM-Z3V1L. Separate multiple IDs with commas.</p>
			</td>
		</tr>
		<tr>
			<th scope="row">Defer loading</th>
			<td>
				<label for="sgtm-defer">
					<input type="checkbox" aria-describedby="sgtm-defer-desc" name="sgtm_defer" id="sgtm-defer" value="1" <?php checked( get_site_option( 'sgtm_defer', FALSE ) ); ?>>
					Load Google Tag Manager after the user interacts with the page.
				</label>
				<p class="description" id="sgtm-defer-desc">This may reduce pageviews but it'll weed out many bots and bounces. It'll also improve page speed.</p>
			</td>
		</tr>
		<tr>
			<th scope="row">Site overrides</th>
			<td>
				<label for="sgtm-prevent-overrides">
					<input type="checkbox" aria-describedby="sgtm-prevent-overrides-desc" name="sgtm_prevent_overrides" id="sgtm-prevent-overrides" value="1" <?php checked( get_site_option( 'sgtm_prevent_overrides', FALSE ) ); ?>>
					Prevent individual site overrides
				</label>
				<p class="description" id="sgtm-prevent-overrides-desc">Force every site in the network to use these settings.</p>
			</td>
		</tr>
	</table>
	<?php submit_button( 'Save Settings' ); ?>
</form>

</div>
<?php } ?>
