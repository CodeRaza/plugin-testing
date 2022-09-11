<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://farazahmad.net
 * @since      1.0.0
 *
 * @package    Design_Pact
 * @subpackage Design_Pact/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Design_Pact
 * @subpackage Design_Pact/admin
 * @author     Faraz Ahmad <farazahmad759@gmail.com>
 */
class Design_Pact_Admin_Settings
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}


	/**
	 * Top level menu callback function
	 */
	function dpdv_options_page_html()
	{
		// check user capabilities
		if (!current_user_can('manage_options')) {
			return;
		}

		// add error/update messages

		// check if the user have submitted the settings
		// WordPress will add the "settings-updated" $_GET parameter to the url
		if (isset($_GET['settings-updated'])) {
			// add settings saved message with the class of "updated"
			add_settings_error('dpdv_messages', 'dpdv_message', __('Settings Saved', 'dpdv'), 'updated');
		}

		// show error/update messages
		settings_errors('dpdv_messages');
?>
		<div class="wrap">
			<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
			<form action="options.php" method="post">
				<?php
				// output security fields for the registered setting "dpdv"
				settings_fields('dpdv');
				// output setting sections and their fields
				// (sections are registered for "dpdv", each field is registered to a specific section)
				do_settings_sections('dpdv');
				// output save settings button
				submit_button('Save Settings');
				?>
			</form>
		</div>
	<?php
	}

	/**
	 * Developers section callback function.
	 *
	 * @param array $args  The settings array, defining title, id, callback.
	 */
	function section_general_cb($args)
	{
	?>
		<p id="<?php echo esc_attr($args['id']); ?>"><?php esc_html_e('Configure site, product ID etc.', 'dpdv'); ?></p>
	<?php
	}

	/**
	 * Product ID field callback function.
	 *
	 * WordPress has magic interaction with the following keys: label_for, class.
	 * - the "label_for" key value is used for the "for" attribute of the <label>.
	 * - the "class" key value is used for the "class" attribute of the <tr> containing the field.
	 * Note: you can add custom key value pairs to be used inside your callbacks.
	 *
	 * @param array $args
	 */
	function field_wc_product_id_cb($args)
	{
		// Get the value of the setting we've registered with register_setting()
		$options = get_option('dpdv_options');
	?>
		<input type="number" id="<?php echo esc_attr($args['label_for']); ?>" data-custom="<?php echo esc_attr($args['dpdv_custom_data']); ?>" name="dpdv_options[<?php echo esc_attr($args['label_for']); ?>]" value="<?php echo isset($options[$args['label_for']]) ? ($options[$args['label_for']]) : (0) ?>" />

		<p class="description">
			<?php esc_html_e('The Product ID for \'Custom Match\' Product.', 'dpdv'); ?>
		</p>
	<?php
	}

	function field_pf_api_access_token_cb($args)
	{
		// Get the value of the setting we've registered with register_setting()
		$options = get_option('dpdv_options');
	?>
		<input type="text" id="<?php echo esc_attr($args['label_for']); ?>" data-custom="<?php echo esc_attr($args['dpdv_custom_data']); ?>" name="dpdv_options[<?php echo esc_attr($args['label_for']); ?>]" value="<?php echo isset($options[$args['label_for']]) ? ($options[$args['label_for']]) : (0) ?>" style="width: 400px; max-width:100%" />

		<p class="description">
			<?php esc_html_e('Printful API Access Token', 'dpdv'); ?>
		</p>
	<?php
	}

	function field_shipengine_api_key_cb($args)
	{
		// Get the value of the setting we've registered with register_setting()
		$options = get_option('dpdv_options');
	?>
		<input type="text" id="<?php echo esc_attr($args['label_for']); ?>" data-custom="<?php echo esc_attr($args['dpdv_custom_data']); ?>" name="dpdv_options[<?php echo esc_attr($args['label_for']); ?>]" value="<?php echo isset($options[$args['label_for']]) ? ($options[$args['label_for']]) : (0) ?>" style="width: 400px; max-width:100%" />

		<p class="description">
			<?php esc_html_e('ShipEngine Api Key', 'dpdv'); ?>
		</p>
	<?php
	}

	/**
	 * Developers section callback function.
	 *
	 * @param array $args  The settings array, defining title, id, callback.
	 */
	function section_design_pact_node_api_cb($args)
	{
	?>
		<p id="<?php echo esc_attr($args['id']); ?>"><?php esc_html_e('Configure settings for Node API.', 'dpdv'); ?></p>
	<?php
	}

	function field_design_pact_node_api_domain_cb($args)
	{
		// Get the value of the setting we've registered with register_setting()
		$options = get_option('dpdv_options');
	?>
		<input type="text" id="<?php echo esc_attr($args['label_for']); ?>" data-custom="<?php echo esc_attr($args['dpdv_custom_data']); ?>" name="dpdv_options[<?php echo esc_attr($args['label_for']); ?>]" value="<?php echo isset($options[$args['label_for']]) ? ($options[$args['label_for']]) : (0) ?>" style="width: 400px; max-width:100%" />

		<p class="description">
			<?php esc_html_e('The Domain at which the Node API can be accessed.', 'dpdv'); ?>
		</p>
	<?php
	}
	function field_design_pact_node_api_logo_cb($args)
	{
		// Get the value of the setting we've registered with register_setting()
		$options = get_option('dpdv_options');
	?>
		<input type="text" id="<?php echo esc_attr($args['label_for']); ?>" data-custom="<?php echo esc_attr($args['dpdv_custom_data']); ?>" name="dpdv_options[<?php echo esc_attr($args['label_for']); ?>]" value="<?php echo isset($options[$args['label_for']]) ? ($options[$args['label_for']]) : (0) ?>" style="width: 400px; max-width:100%" />

		<p class="description">
			<?php esc_html_e('The Logo URL that you want to display in the Product Images', 'dpdv'); ?>
		</p>
	<?php
	}

	/**
	 * Developers section callback function.
	 *
	 * @param array $args  The settings array, defining title, id, callback.
	 */
	function section_wordpress_rest_api_cb($args)
	{
	?>
		<p id="<?php echo esc_attr($args['id']); ?>"><?php esc_html_e('WordPress REST API.', 'dpdv'); ?></p>
	<?php
	}

	function field_wordpress_rest_api_allowed_ip_addresses_cb($args)
	{
		// Get the value of the setting we've registered with register_setting()
		$options = get_option('dpdv_options');
	?>
		<textarea id="<?php echo esc_attr($args['label_for']); ?>" data-custom="<?php echo esc_attr($args['dpdv_custom_data']); ?>" name="dpdv_options[<?php echo esc_attr($args['label_for']); ?>]" style="width: 400px; max-width:100%"><?php echo isset($options[$args['label_for']]) ? ($options[$args['label_for']]) : ($_SERVER['SERVER_ADDR'] . ' // localhost') ?></textarea>
		<p class="description">
			<?php esc_html_e('Allowed IP Addresses for REST API (one IP per line)', 'dpdv'); ?>
		</p>
	<?php
	}

	/**
	 * Developers section callback function.
	 *
	 * @param array $args  The settings array, defining title, id, callback.
	 */
	function section_google_merchant_cb($args)
	{
	?>
		<p id="<?php echo esc_attr($args['id']); ?>"><?php esc_html_e('Settings for Google Merchant', 'dpdv'); ?></p>
	<?php
	}

	function field_brand_name_for_google_merchant_cb($args)
	{
		// Get the value of the setting we've registered with register_setting()
		$options = get_option('dpdv_options');
	?>
		<textarea id="<?php echo esc_attr($args['label_for']); ?>" data-custom="<?php echo esc_attr($args['dpdv_custom_data']); ?>" name="dpdv_options[<?php echo esc_attr($args['label_for']); ?>]" style="width: 400px; max-width:100%"><?php echo isset($options[$args['label_for']]) ? ($options[$args['label_for']]) : ('') ?></textarea>
		<p class="description">
			<?php esc_html_e('Brand name to be displayed in the feed item', 'dpdv'); ?>
		</p>
	<?php
	}


	/**
	 * Twilio Settings section callback function.
	 *
	 * @param array $args  The settings array, defining title, id, callback.
	 */
	function section_design_pact_twilio_settings_cb($args)
	{
	?>
		<p id="<?php echo esc_attr($args['id']); ?>"><?php esc_html_e('Settings for Twilio', 'dpdv'); ?></p>
	<?php
	}
	function field_twilio_account_id_cb($args)
	{
		// Get the value of the setting we've registered with register_setting()
		$options = get_option('dpdv_options');
	?>
		<input type="text" id="<?php echo esc_attr($args['label_for']); ?>" data-custom="<?php echo esc_attr($args['dpdv_custom_data']); ?>" name="dpdv_options[<?php echo esc_attr($args['label_for']); ?>]" value="<?php echo isset($options[$args['label_for']]) ? ($options[$args['label_for']]) : ('') ?>" style="width: 400px; max-width:100%" />

		<p class="description">
			<?php esc_html_e('Account ID', 'dpdv'); ?>
		</p>
	<?php
	}
	function field_twilio_messaging_service_id_cb($args)
	{
		// Get the value of the setting we've registered with register_setting()
		$options = get_option('dpdv_options');
	?>
		<input type="text" id="<?php echo esc_attr($args['label_for']); ?>" data-custom="<?php echo esc_attr($args['dpdv_custom_data']); ?>" name="dpdv_options[<?php echo esc_attr($args['label_for']); ?>]" value="<?php echo isset($options[$args['label_for']]) ? ($options[$args['label_for']]) : ('') ?>" style="width: 400px; max-width:100%" />

		<p class="description">
			<?php esc_html_e('Messaging Service ID', 'dpdv'); ?>
		</p>
	<?php
	}
	function field_twilio_api_key_cb($args)
	{
		// Get the value of the setting we've registered with register_setting()
		$options = get_option('dpdv_options');
	?>
		<input type="text" id="<?php echo esc_attr($args['label_for']); ?>" data-custom="<?php echo esc_attr($args['dpdv_custom_data']); ?>" name="dpdv_options[<?php echo esc_attr($args['label_for']); ?>]" value="<?php echo isset($options[$args['label_for']]) ? ($options[$args['label_for']]) : ('') ?>" style="width: 400px; max-width:100%" />

		<p class="description">
			<?php esc_html_e('API Key', 'dpdv'); ?>
		</p>
<?php
	}
}
