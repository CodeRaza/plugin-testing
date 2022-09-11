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
class Design_Pact_Admin
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

	public $admin_settings;

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

		$this->load_dependencies();

		$this->admin_settings = new Design_Pact_Admin_Settings($plugin_name, $version);
	}

	private function load_dependencies()
	{
		/**
		 * The admin-settings
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-design-pact-admin-settings.php';
	}

	/**
	 * @internal never define functions inside callbacks.
	 * these functions could be run multiple times; this would result in a fatal error.
	 */

	/**
	 * custom option and settings
	 */
	function dpdv_settings_init()
	{
		// Register a new setting for "dpdv" page.
		register_setting('dpdv', 'dpdv_options');

		// Register a new section in the "dpdv" page.
		add_settings_section(
			'section_general',
			__('General', 'dpdv'),
			array($this->admin_settings, 'section_general_cb'),
			'dpdv'
		);

		// Register a new field in the "section_general" section, inside the "dpdv" page.
		add_settings_field(
			'field_wc_product_id', // As of WP 4.6 this value is used only internally.
			// Use $args' label_for to populate the id inside the callback.
			__('WC Product ID', 'dpdv'),
			array($this->admin_settings, 'field_wc_product_id_cb'),
			'dpdv',
			'section_general',
			array(
				'label_for'         => 'field_wc_product_id',
				'class'             => 'dpdv_row',
				'dpdv_custom_data' => 'custom',
			)
		);

		// Register a new field in the "section_general" section, inside the "dpdv" page.
		add_settings_field(
			'pf_api_access_token', // As of WP 4.6 this value is used only internally.
			// Use $args' label_for to populate the id inside the callback.
			__('Printful API Access Token', 'dpdv'),
			array($this->admin_settings, 'field_pf_api_access_token_cb'),
			'dpdv',
			'section_general',
			array(
				'label_for'         => 'pf_api_access_token',
				'class'             => 'dpdv_row',
				'dpdv_custom_data' => 'custom',
			)
		);

		// Register a new field in the "section_general" section, inside the "dpdv" page.
		add_settings_field(
			'shipengine_api_key', // As of WP 4.6 this value is used only internally.
			// Use $args' label_for to populate the id inside the callback.
			__('ShipEngine API Key', 'dpdv'),
			array($this->admin_settings, 'field_shipengine_api_key_cb'),
			'dpdv',
			'section_general',
			array(
				'label_for'         => 'shipengine_api_key',
				'class'             => 'dpdv_row',
				'dpdv_custom_data' => 'custom',
			)
		);


		// Register a new section in the "dpdv" page.
		add_settings_section(
			'section_design_pact_node_api',
			__('Design Pact Node API', 'dpdv'),
			array($this->admin_settings, 'section_design_pact_node_api_cb'),
			'dpdv'
		);
		// Register a new field in the "section_general" section, inside the "dpdv" page.
		add_settings_field(
			'design_pact_node_api_domain', // As of WP 4.6 this value is used only internally.
			// Use $args' label_for to populate the id inside the callback.
			__('Node API Domain', 'dpdv'),
			array($this->admin_settings, 'field_design_pact_node_api_domain_cb'),
			'dpdv',
			'section_design_pact_node_api',
			array(
				'label_for'         => 'design_pact_node_api_domain',
				'class'             => 'dpdv_row',
				'dpdv_custom_data' => 'custom',
			)
		);
		// Register a new field in the "section_general" section, inside the "dpdv" page.
		add_settings_field(
			'design_pact_node_api_logo', // As of WP 4.6 this value is used only internally.
			// Use $args' label_for to populate the id inside the callback.
			__('Node API Logo URL', 'dpdv'),
			array($this->admin_settings, 'field_design_pact_node_api_logo_cb'),
			'dpdv',
			'section_design_pact_node_api',
			array(
				'label_for'         => 'design_pact_node_api_logo',
				'class'             => 'dpdv_row',
				'dpdv_custom_data' => 'custom',
			)
		);


		// Register a new section in the "dpdv" page.
		add_settings_section(
			'section_wordpress_rest_api',
			__('WordPress REST API', 'dpdv'),
			array($this->admin_settings, 'section_wordpress_rest_api_cb'),
			'dpdv'
		);

		// Register a new field in the "section_general" section, inside the "dpdv" page.
		add_settings_field(
			'wordpress_rest_api_allowed_ip_addresses', // As of WP 4.6 this value is used only internally.
			// Use $args' label_for to populate the id inside the callback.
			__('Allowed IP Addresses', 'dpdv'),
			array($this->admin_settings, 'field_wordpress_rest_api_allowed_ip_addresses_cb'),
			'dpdv',
			'section_wordpress_rest_api',
			array(
				'label_for'         => 'wordpress_rest_api_allowed_ip_addresses',
				'class'             => 'dpdv_row',
				'dpdv_custom_data' => 'custom',
			)
		);

		// Register a new section in the "dpdv" page.
		add_settings_section(
			'section_google_merchant',
			__('Google Merchant', 'dpdv'),
			array($this->admin_settings, 'section_google_merchant_cb'),
			'dpdv'
		);

		// Register a new field in the "section_general" section, inside the "dpdv" page.
		add_settings_field(
			'brand_name_for_google_merchant', // As of WP 4.6 this value is used only internally.
			// Use $args' label_for to populate the id inside the callback.
			__('Brand Name', 'dpdv'),
			array($this->admin_settings, 'field_brand_name_for_google_merchant_cb'),
			'dpdv',
			'section_google_merchant',
			array(
				'label_for'         => 'brand_name_for_google_merchant',
				'class'             => 'dpdv_row',
				'dpdv_custom_data' => 'custom',
			)
		);


		// Register a new section in the "dpdv" page.
		add_settings_section(
			'section_design_pact_twilio_settings',
			__('Twilio Settings', 'dpdv'),
			array($this->admin_settings, 'section_design_pact_twilio_settings_cb'),
			'dpdv'
		);
		// Register a new field in the "section_design_pact_twilio_settings" section, inside the "dpdv" page.
		add_settings_field(
			'twilio_account_id', // As of WP 4.6 this value is used only internally.
			// Use $args' label_for to populate the id inside the callback.
			__('Account ID', 'dpdv'),
			array($this->admin_settings, 'field_twilio_account_id_cb'),
			'dpdv',
			'section_design_pact_twilio_settings',
			array(
				'label_for'         => 'twilio_account_id',
				'class'             => 'dpdv_row',
				'dpdv_custom_data' => 'custom',
			)
		);
		// Register a new field in the "section_design_pact_twilio_settings" section, inside the "dpdv" page.
		add_settings_field(
			'twilio_messaging_service_id', // As of WP 4.6 this value is used only internally.
			// Use $args' label_for to populate the id inside the callback.
			__('Messaging Service ID', 'dpdv'),
			array($this->admin_settings, 'field_twilio_messaging_service_id_cb'),
			'dpdv',
			'section_design_pact_twilio_settings',
			array(
				'label_for'         => 'twilio_messaging_service_id',
				'class'             => 'dpdv_row',
				'dpdv_custom_data' => 'custom',
			)
		);
		// Register a new field in the "section_design_pact_twilio_settings" section, inside the "dpdv" page.
		add_settings_field(
			'twilio_api_key', // As of WP 4.6 this value is used only internally.
			// Use $args' label_for to populate the id inside the callback.
			__('API Key', 'dpdv'),
			array($this->admin_settings, 'field_twilio_api_key_cb'),
			'dpdv',
			'section_design_pact_twilio_settings',
			array(
				'label_for'         => 'twilio_api_key',
				'class'             => 'dpdv_row',
				'dpdv_custom_data' => 'custom',
			)
		);
	}

	/**
	 * Add the top level menu page.
	 */
	function dpdv_options_page()
	{
		add_menu_page(
			'Design Pact',
			'Design Pact Options',
			'manage_options',
			'design-pact',
			array($this->admin_settings, 'dpdv_options_page_html'),
		);
	}


	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Design_Pact_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Design_Pact_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/design-pact-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Design_Pact_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Design_Pact_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/design-pact-admin.js', array('jquery'), $this->version, false);
	}

	//custom css for the orders area
	public function my_custom_style()
	{
		echo
		'<style>
					table.display_meta tbody tr:nth-child(3), table.display_meta tbody tr:nth-child(4), table.display_meta tbody tr:nth-child(5), table.display_meta tbody tr:nth-child(8), table.display_meta tbody tr:nth-child(9) {
					display: none !important;
					} 
				</style>';
	}

	// Add Custom Admin CSS
	function admin_head_145312()
	{
		global $post;
?>
		<style>
			.delete-order-item {
				display: none !important;
			}

			.in-order {
				color: white;
				padding: 2px 10px;
				font-weight: bold;
				text-align: center;
				cursor: pointer;
			}

			.in-order-Yes {
				background: #4caf50;
			}

			.in-order-No {
				background: #f44336;
			}
		</style>
		<script>
			jQuery(document).ready(function() {
				jQuery(".in-order").click(function() {
					var newAction = false;
					var toggleButton = jQuery(this);
					if (toggleButton.hasClass("in-order-Yes")) {
						var whatWeDo = "No";
						var whatWeHad = "Yes";
					} else {
						var whatWeDo = "Yes";
						var whatWeHad = "No";
					}
					let rest_auth_nonce = "<?php echo esc_attr(wp_create_nonce('wp_rest')); ?>";

					if (confirm("Change to " + whatWeDo)) {
						console.log("Running AJAX");
						jQuery.ajax({
							url: "/wp-json/dpdv/v1/actions/order-items/update-in-order-status",
							type: "get", //send it through get method
							data: {
								id: toggleButton.attr("data-item-id"),
								order: <?= $post->ID; ?>,
								status: whatWeDo
							},
							beforeSend: function(xhr) {
								xhr.setRequestHeader('X-WP-Nonce', rest_auth_nonce);
							},
							success: function(response) {
								// data = jQuery.parseJSON(response);
								data = response;
								console.log(data);
								if (data.result == 'success') {
									console.log("What we Had " + whatWeHad);
									console.log("What we Do " + whatWeDo);
									toggleButton.removeClass("in-order-" + whatWeHad);
									toggleButton.addClass("in-order-" + whatWeDo);
									toggleButton.html(whatWeDo);
								} else {
									alert("There was an error. " + data.reason);
								}
							},
							error: function(xhr) {
								//Do Something to handle error
							}
						});
					}

				});
			});
		</script>
	<?php
	}

	function load_custom_wp_admin_style()
	{
		wp_register_style('custom_wp_admin_css', get_bloginfo('stylesheet_directory') . '/admin-style.css', false, '1.0.17');
		wp_enqueue_style('custom_wp_admin_css');
	}


	function wpdocs_selectively_enqueue_admin_script($hook)
	{
		if ('post.php' != $hook) {
			return;
		}
		$current_screen = get_current_screen();

		wp_enqueue_script('ntc.js', get_site_url() . "/scripts/ntc.js", array(), '1.02', true);
		wp_enqueue_script('ntc-color.js', get_site_url() . "/scripts/ntc-color.js", array(), '1.03', true);
		if ($current_screen->id == 'shop_order') {
			wp_enqueue_script('edit-order-item-type-size-color.js', get_bloginfo('stylesheet_directory') . "/js/edit-order-item-type-size-color.js", array(), '1.03', true);
		}
	}


	function global_search_admin()
	{
	?>
		<div style='position:fixed;bottom:0;right:0;background:white;z-index:99999;padding:10px;font-weight:bold;border:1px solid black;'><span style='font-size:15px;'>Order or RMA #: </span><input style="width:100px" placeholder='R123' type='text' class='order-number-jump' /><input class="order-number-jump-submit" type="submit" value="Go" /></div>
		<script>
			jQuery(".order-number-jump-submit").click(function() {

				let orderNumber = jQuery(".order-number-jump").val();

				jQuery.ajax({
					url: "/scripts/admin/quick-find-order-id-json.php",
					type: "get", //send it through get method
					data: {
						number: orderNumber,
					},
					success: function(response) {
						data = jQuery.parseJSON(response);
						console.log(data);
						if (data.result == 'success') {
							if (data.what == 'order') {
								window.location = "/wp-admin/post.php?post=" + data.orderid + "&action=edit";
							} else if (data.what == 'rma') {
								window.location = "/wp-admin/admin.php?page=wpforms-entries&view=details&entry_id=" + data.orderid;
							}
						} else {
							alert("Order or RMA # not found, please try again. " + data.reason);
						}
					},
					error: function(xhr) {
						//Do Something to handle error
					}
				});
			});
		</script>
	<?php
	}

	function add_printful_metaboxes()
	{
		add_meta_box(
			'mkdv_order_actions',
			'MKDV Order Actions',
			'mkdv_order_actions',
			'shop_order',
			'normal',
			'core'
		);
	}


	function wpt_add_sneaker_metaboxes()
	{
		add_meta_box(
			'wpt_sneaker_previews',
			'Preview of Sneaker on Various Designs (Make Sure Colors Layer Good!)',
			'wpt_sneaker_previews',
			'sneaker',
			'normal',
			'high'
		);
	}

	function wpt_add_design_metaboxes()
	{
		add_meta_box(
			'wpt_design_previews',
			'Design Previews',
			'wpt_design_previews',
			'design',
			'normal',
			'high'
		);

		add_meta_box(
			'wpt_design',
			'Design',
			'wpt_design',
			'design',
			'side',
			'high'
		);
	}

	function add_printful_metaboxes_2()
	{
		add_meta_box(
			'mkdv_printful_actions',
			'Printful Actions',
			array($this, 'printful_actions'),
			'shop_order',
			'normal',
			'core'
		);
	}


	function add_column_to_order_item($_product, $item, $item_id = null)
	{
		if ($item->get_type() != "line_item") {
			return;
		}
		$printful_product_ids = array(
			'iconic_cffv_107746_printful_product_id' => array(
				'background' => "green",
				'color' => 'white',
				'text' => "PRIMARY:"
			),
			'iconic_cffv_107746_printful_backup_product_id' => array(
				'background' => "yellow",
				'color' => 'black',
				'text' => "BACKUP:"
			)
		);

		echo "<td>";

		foreach ($printful_product_ids as $key => $value) {

			$product = get_post_meta($_product->get_id(), $key, 1);
			$options = $value;
			mkdv_get_template_part(plugin_dir_path(__FILE__) . "partials/design-pact-admin-printful-column-data", "", array(
				'view' => 'product-data',
				'product' => $product,
				'options' => $options
			));
		}


		mkdv_get_template_part(plugin_dir_path(__FILE__) . "partials/design-pact-admin-printful-column-data", "", array(
			'view' => 'edit-button',
			'_product' => $_product,
		));

		echo "</td>";
	}

	///////////////////////////////////////
	// HELPERS
	///////////////////////////////////////
	function printful_actions()
	{
		// order-item's column javascript
		mkdv_get_template_part(plugin_dir_path(__FILE__) . "partials/design-pact-admin-printful-column-data", "", array(
			'view' => 'javascript'
		));

		// printful-actions
		mkdv_get_template_part(plugin_dir_path(__FILE__) . "partials/design-pact-admin-printful-metabox", "", array(
			'view' => 'actions'
		));
		// get_template_part("mkdv-printful/parts/metabox", "", array('view' => 'actions'));
	}

	function mkdv_woocommerce_edit_item_type_color_size($item_id, $item, $_product)
	{
		if ($item->get_type() != "line_item") {
			return;
		}

		$order_id = $_GET['post'];

		// $printful_status = get_post_meta($order_id, 'printful_status', true);

		$site = "https://" . $_SERVER['HTTP_HOST'];
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "{$site}/scripts/printful/api-get-printful-order.php?order_id=" . $order_id);
		// echo $site . "/scripts/printful/api-get-printful-order.php?order_id=" . $order_id;
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$printfulData = curl_exec($ch);
		$printfulData = json_decode($printfulData, 1);
		if (isset($printfulData['data']['body']['result'])) {
			$printfulData = $printfulData['data']['body']['result'];
		}
		$can_edit_order_items = false;

		if (empty($printfulData['status']) || in_array($printfulData['status'], ['archived', 'canceled'])) {
			$can_edit_order_items = true;
		}


	?>
		<br />
		<div id="my-content-id-<?php echo $item_id; ?>" style="display:none;">
			<h3>
				Item #<?php echo $item_id; ?>
			</h3>
			<br />

			<?php
			if ($can_edit_order_items && 1 == 1) {
			?>
				<div class="mkdv-item-form-fields">
					<b style="display:block; margin-bottom: 5px;">Change Product Type</b>
					<select class='item-type-dropdown' data-variation-id='<?php echo $item->get_variation_id(); ?>' data-item-key='<?php echo $item_id; ?>'>
					</select>
					<b style="display:block; margin-top:20px; margin-bottom: 5px;">Change Product Color</b>
					<select class='item-color-dropdown' data-variation-id='<?php echo $item->get_variation_id(); ?>' data-item-key='<?php echo $item_id; ?>'>
					</select>
					<b style="display:block; margin-top:20px; margin-bottom: 5px;">Change Product Size</b>
					<select class='item-size-dropdown order-item' data-variation-id='<?php echo $item->get_variation_id(); ?>' data-item-key='<?php echo $item_id; ?>' data-order-id='<?php echo $order_id; ?>'>
					</select>
				</div>
			<?php
			} else {
			?>
				<div class="mkdv-error-msg" style="color:red;">
					<b>Sorry, you cannot modify this order because it is already present in Printful (status = <?php echo $printfulData['status']; ?>). You can do the following to proceed</b>
					<ul>
						<li> 1. Cancel Printful Fulfillment.</li>
						<li> 2. Modify order items.</li>
						<li> 3. Push again to Printful.</li>
					</ul>
				</div>
			<?php
			}
			?>
		</div>

		<!-- functionality is written in "js/edit-order-item-type-size-color.js" -->

		<?php
		if ($can_edit_order_items && 1 == 1) {
		?>
			<a href=" #TB_inline?&width=600&height=550&inlineId=my-content-id-<?php echo $item_id; ?>" class="thickbox">Edit Item Type/Color/Size</a>
		<?php } else { ?>
			<div style="color:red;">Cancel Printful Fulfillment to edit Product Type/Color/Size</div>
		<?php } ?>
		<?php

	}


	// Add custom column headers here
	function my_woocommerce_admin_order_item_headers()
	{
		// display the column name
		echo '<th> Printful </th>';
		echo '<th> Design </th>';
		echo '<th> In Order </th>';
	}

	// Add custom column values here
	function my_woocommerce_admin_order_item_values($_product, $item, $item_id = null)
	{
		if ($item->get_type() != "line_item") {
			return;
		}
		// get the post meta value from the associated product
		// $value = get_post_meta($_product->post->ID, 'Design', 1);

		//print_r($item->get_meta("Design"));


		$d = explode("?", $item->get_meta("Design"));
		// 	var_dump(json_encode($item->get_data()));
		$design_img = $item->get_meta("Design");
		$design_img = remove_wp_upload_base_url($design_img);
		if (!empty($item->get_meta("product_image"))) {
			$product_img = $item->get_meta("product_image");
		} else {
			parse_str(parse_url($item->get_meta("Design"))['query'], $designUrl);
			$product_img = getRenderedImage($item->get_meta("pa_product-color"), $designUrl['file'], $item->get_meta("Shoe"), null, $item->get_meta("pa_product-type"), json_decode($designUrl['data']));
		}

		$design_img = str_replace("#", "", $design_img);
		$design_img = str_replace("/v1", "/v3", $design_img);
		$product_img = str_replace("#", "", $product_img);

		$design_img_url_components = parse_url($design_img);
		parse_str($design_img_url_components['query'], $design_img_params);

		$product_img_url_components = parse_url($product_img);
		parse_str($product_img_url_components['query'], $product_img_params);

		$design_img = str_replace('data=' . $design_img_params['data'], "data=" . urlencode($design_img_params['data']), $design_img);
		if (isset($product_img_params['designData'])) {
			$product_img = str_replace('designData=' . $product_img_params['designData'], "designData=" . urlencode($product_img_params['designData']), $product_img);
		}
		if (isset($product_img_params['background'])) {
			$product_img = str_replace('background=' . $product_img_params['background'], "background=" . urlencode($item->get_meta('pa_product-color')), $product_img);
		}

		// print_r("ddd->" . $product_img);
		if ($design_img) {
		?>
			<td class="kjhasdf">
				<a target="_blank" href="<?php echo $design_img; ?> &designWidth=4000">
					<img src="<?php echo $product_img; ?>" style="max-width:100px !important;padding:5px;border:1px solid #ddd;border-radius:10px;" />
				</a>
				<br />
				<a href="https://drive.google.com/drive/u/1/search?q=<?php echo $item->get_meta("pa_design-id"); ?>" target="_blank"><?php echo $item->get_meta("pa_design-id"); ?>
				</a>
				in Drive
			</td>
		<?php
		} else { ?>
			<td>----</td>
<?php
		}

		if ($item->get_meta("In Order") == "No") {
			echo '<td><div class="in-order in-order-No" data-item-id="' . $item_id . '">No</div><small>Item ID #' . $item_id . '</small></td>';
		} else {
			echo '<td><div class="in-order in-order-Yes" data-item-id="' . $item_id . '">Yes</div><small>Item ID #' . $item_id . '</small></td>';
		}
	}
}
