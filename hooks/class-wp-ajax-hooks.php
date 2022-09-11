<?php


class Design_Pact_Wp_Ajax_Hooks
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Design_Pact_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	public $namespace = "dpdv/v1";
	public $namespace_old = "dv/v1";

	private $plugin_public;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		if (defined('DESIGN_PACT_VERSION')) {
			$this->version = DESIGN_PACT_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'design-pact';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_shortcodes();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Design_Pact_Loader. Orchestrates the hooks of the plugin.
	 * - Design_Pact_i18n. Defines internationalization functionality.
	 * - Design_Pact_Admin. Defines all hooks for the admin area.
	 * - Design_Pact_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{

		/**
		 * The utility functions
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/functions/design-pact-utility-functions.php';

		/**
		 * The temp functions
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/functions/design-pact-temp-functions.php';

		/**
		 * The class responsible for DesignPact Product
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/functions/class-design-pact-product.php';

		/**
		 * The class responsible for design-pact-product
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-design-pact-product.php';

		/**
		 * The class responsible for DesignPact Woocommerce Service
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/functions/class-design-pact-woocommerce-service.php';

		/**
		 * The class responsible for DesignPact Printful Service
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/functions/class-design-pact-printful-service.php';

		/**
		 * The class responsible for DesignPact Twilio Service
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/functions/class-design-pact-twilio-service.php';

		/**
		 * The class responsible for DesignPact ShipEngine Service
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/functions/class-design-pact-shipengine-service.php';

		/**
		 * The class responsible for helper functions
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-design-pact-helpers.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-design-pact-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-design-pact-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-design-pact-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-design-pact-public.php';

		$this->loader = new Design_Pact_Loader();
		$this->plugin_public = new Design_Pact_Public($this->get_plugin_name(), $this->get_version());
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Design_Pact_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale()
	{

		$plugin_i18n = new Design_Pact_i18n();

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{

		$plugin_admin = new Design_Pact_Admin($this->get_plugin_name(), $this->get_version());

		/**
		 * Register our dpdv_settings_init to the admin_init action hook.
		 */
		$this->loader->add_action('admin_init', $plugin_admin, 'dpdv_settings_init');
		/**
		 * Register our dpdv_options_page to the admin_menu action hook.
		 */
		$this->loader->add_action('admin_menu', $plugin_admin, 'dpdv_options_page');

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

		$this->loader->add_action('admin_head', $plugin_admin, 'my_custom_style');
		$this->loader->add_action("admin_head", $plugin_admin, "admin_head_145312");
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'load_custom_wp_admin_style');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'wpdocs_selectively_enqueue_admin_script');
		// // if (current_user_can("read_shop_order") || current_user_can("administrator")) {
		$this->loader->add_action('admin_footer', $plugin_admin, 'global_search_admin');
		// // }
		$this->loader->add_action('add_meta_boxes', $plugin_admin, 'add_printful_metaboxes');
		$this->loader->add_action('add_meta_boxes', $plugin_admin, 'wpt_add_sneaker_metaboxes');
		$this->loader->add_action('add_meta_boxes', $plugin_admin, 'wpt_add_design_metaboxes');
		$this->loader->add_action('add_meta_boxes', $plugin_admin, 'add_printful_metaboxes_2');
		$this->loader->add_action('woocommerce_admin_order_item_values', $plugin_admin, 'add_column_to_order_item', 10, 3);
		$this->loader->add_action('woocommerce_after_order_itemmeta', $plugin_admin, 'mkdv_woocommerce_edit_item_type_color_size', 10, 3);
		$this->loader->add_action('woocommerce_admin_order_item_headers', $plugin_admin, 'my_woocommerce_admin_order_item_headers');
		$this->loader->add_action('woocommerce_admin_order_item_values', $plugin_admin, 'my_woocommerce_admin_order_item_values', 10, 3);
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks()
	{

		$plugin_public = new Design_Pact_Public($this->get_plugin_name(), $this->get_version());

		$this->define_public_add_action_hooks();
		$this->define_public_add_filter_hooks();
		$this->define_public_wp_ajax_hooks();
		$this->define_public_rest_api_hooks();
		$this->register_rest_fields();
	}

	private function define_shortcodes()
	{
		$plugin_public = new Design_Pact_Public($this->get_plugin_name(), $this->get_version());

		add_shortcode("ListSneakerPages", array($plugin_public, "listsneakerpages"));
		add_shortcode("DesignsList", array($plugin_public, "listdesigns"));
		add_shortcode("unboxing-videos", array($plugin_public, "unboxing_videos_shortcode"));
		add_shortcode("release-date-sneakers", array($plugin_public, "releaseDateSneakers"));
		add_shortcode("popular-sneakers", array($plugin_public, "popularSneakers"));
		add_shortcode("customer-upload-own-sneaker", array($plugin_public, "customer_upload_own_sneaker"));

		add_shortcode("currency_switch", array($plugin_public, "currency_switch_function"));
		add_shortcode("saved_sneakers", array($plugin_public, "saved_sneakers_shortcode__cb"));
	}

	private function define_public_add_action_hooks()
	{
		$plugin_public = new Design_Pact_Public($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'wp_enqueue_scripts_001');
		// // add_action("flatsome_cart_sidebar", array($add_action_callbacks, "mk_human"), 99); // disabled

		$this->loader->add_action('save_post_brands', $plugin_public, 'mk_save_brands', 10, 3);
		$this->loader->add_action('save_post_colors', $plugin_public, 'mk_save_colors', 10, 3);
		$this->loader->add_action('save_post_design', $plugin_public, 'mk_convert_svg_jpg', 10, 3);
		$this->loader->add_action('pre_get_posts', $plugin_public, 'query_set_only_author');
		$this->loader->add_action("wp_head", $plugin_public, "add_index_headers");
		// add_action('before_delete_post', $plugin_public, 'restrict_post_deletion_1', 10, 1); // disabled
		$this->loader->add_action("wp_head", $plugin_public, "order_notes");
		$this->loader->add_action('before_delete_post', $plugin_public, 'restrict_post_deletion_2', 10, 1);
		$this->loader->add_action('save_post', $plugin_public, 'post_screenshot', 10, 3);
		$this->loader->add_action('wp_insert_post', $plugin_public, 'post_screenshot', 10, 3);
		$this->loader->add_action('manage_design_posts_custom_column', $plugin_public, 'custom_design_column', 10, 2);
		$this->loader->add_action("wp_head", $plugin_public, "sneakerShareImg");
		$this->loader->add_action("wp_head", $plugin_public, "postToFacebookFunctionTrigger");
		$this->loader->add_action("postToFacebook", $plugin_public, "postToFacebookFunction");
		$this->loader->add_action('wpforms_pro_admin_entries_edit_submit_before_processing', $plugin_public, 'wpf_dev_process_entry_save_sneaker_requests', 10, 0);
		$this->loader->add_action('manage_colors_posts_custom_column', $plugin_public, 'custom_colors_column', 10, 2);
		$this->loader->add_action('manage_sneaker_posts_custom_column', $plugin_public, 'custom_sneaker_column', 10, 2);
		$this->loader->add_action('init', $plugin_public, 'get_custom_coupon_code_to_session');
		$this->loader->add_action("edit_form_advanced", $plugin_public, "mk_approve_refunds");

		$this->loader->add_action('woocommerce_before_add_to_cart_button', $plugin_public, 'custom_data_hidden_fields');
		$this->loader->add_action('woocommerce_checkout_create_order_line_item', $plugin_public, 'iconic_add_engraving_text_to_order_items', 10, 4);
		$this->loader->add_action('woocommerce_add_cart_item_data', $plugin_public, 'save_custom_data_hidden_fields', 10, 2);
		$this->loader->add_action('woocommerce_after_checkout_form', $plugin_public, 'insert_my_js_uprs');
		$this->loader->add_action('woocommerce_checkout_update_order_meta', $plugin_public, 'update_cartstack_conversion', 10, 1);
		$this->loader->add_action('woocommerce_checkout_update_order_meta', $plugin_public, 'update_titles', 10, 1);
		$this->loader->add_action('woocommerce_checkout_update_order_meta', $plugin_public, 'express_upgrade', 10, 1);
		//$this->loader->add_action("woocommerce_review_order_after_submit", $plugin_public, "safeCheckout");
		// add_action("woocommerce_after_cart_totals", $plugin_public, "moneyBackGuarentee");
		// add_action("woocommerce_checkout_before_customer_details", $plugin_public, "moneyBackGuarentee");
		// add_action('woocommerce_after_checkout_validation', $plugin_public, 'bbloomer_disallow_pobox_shipping');
		$this->loader->add_action('woocommerce_after_checkout_billing_form', $plugin_public, 'bt_add_checkout_checkbox', 10);
		$this->loader->add_filter('woocommerce_order_actions', $plugin_public, 'filter_wc_add_send_return_label', 20, 1);
		$this->loader->add_action('woocommerce_order_action_send_return_label', $plugin_public, 'trigger_action_send_return_label', 20, 1);
		$this->loader->add_action("woocommerce_new_customer_note", $plugin_public, "send_sms_order_updates");
		$this->loader->add_action('woocommerce_admin_order_data_after_order_details', $plugin_public, 'sms_opt_in_editable_order_meta_general');
		$this->loader->add_action('woocommerce_process_shop_order_meta', $plugin_public, 'sms_opt_in_save_general_details');
		$this->loader->add_action('woocommerce_checkout_update_order_meta', $plugin_public, 'set_most_recent_order_id', 10, 1);
		$this->loader->add_action('woocommerce_thankyou', $plugin_public, 'mkdv_add_to_previous_order', 10, 1);
		$this->loader->add_action('woocommerce_order_action_send_special_promo', $plugin_public, 'trigger_action_send_special_promo', 20, 1);
		$this->loader->add_action('woocommerce_review_order_before_submit', $plugin_public, 'mkdv_add_checkout_return_policy_checkbox', 10);
		$this->loader->add_action('woocommerce_checkout_process',  $plugin_public, 'mkdv_add_checkout_return_policy_checkbox_warning');

		// add_action('woocommerce_thankyou', $plugin_public, 'mk_run_scripts_at_thank_you');
		$this->loader->add_action('woocommerce_before_checkout_form', $plugin_public, 'add_discout_to_checkout', 10, 0);
		$this->loader->add_action('woocommerce_cart_calculate_fees', $plugin_public, 'mkdv_woocommerce_calculate_fees', 11, 1);
		//$this->loader->add_action('woocommerce_review_order_before_submit', $plugin_public, 'customMatch');

		$this->loader->add_action('woocommerce_after_cart', $plugin_public, 'currency_switch_function');
	}

	private function define_public_remove_action_hooks()
	{
		remove_action('wp_head', 'rel_canonical');
	}

	private function define_public_add_filter_hooks()
	{
		$plugin_public = new Design_Pact_Public($this->get_plugin_name(), $this->get_version());

		$this->loader->add_filter('acf/location/rule_values/post_type', $plugin_public, 'acf_location_rule_values_Post');

		add_filter('wpseo_canonical',  '__return_false');
		add_filter('fue_multipart_mail',  '__return_false');
		$this->loader->add_filter('wp_dropdown_users', $plugin_public, 'author_override');
		$this->loader->add_filter('wc_order_is_editable', $plugin_public, 'wc_make_processing_orders_editable', 10, 2);
		$this->loader->add_filter('wpforms_webhooks_process_delivery_request_options', $plugin_public, 'wpforms_webhooks_process_delivery_request_options_1', 10, 5);
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_my_script');
		$this->loader->add_filter('manage_design_posts_columns', $plugin_public, 'set_custom_edit_design_columns');
		$this->loader->add_filter('manage_colors_posts_columns', $plugin_public, 'set_custom_edit_colors_columns');
		$this->loader->add_filter('manage_sneaker_posts_columns', $plugin_public, 'set_custom_edit_sneaker_columns');
		$this->loader->add_filter('wpseo_sitemap_index', $plugin_public, 'add_sitemap_custom_items');

		// Filter the search page
		$this->loader->add_filter('pre_get_sneaker', $plugin_public, 'my_search_pre_get_sneaker'); // does not exist

		$this->loader->add_filter('woocommerce_get_item_data', $plugin_public, 'woocommerce_get_item_data_001', 10, 2);
		$this->loader->add_filter('woocommerce_order_item_get_formatted_meta_data', $plugin_public, 'woocommerce_get_item_data_002', 10, 2);
		$this->loader->add_filter('woocommerce_order_item_thumbnail', $plugin_public, 'filter_woocommerce_order_item_thumbnail', 10, 2);
		$this->loader->add_filter("woocommerce_shipstation_export_custom_field_2_value", $plugin_public, "orderFeesCustomFieldToShipStation");
		$this->loader->add_filter('woocommerce_admin_meta_boxes_variations_per_page', $plugin_public, 'handsome_bearded_guy_increase_variations_per_page');
		$this->loader->add_filter('woocommerce_add_to_cart_redirect', $plugin_public, 'bbloomer_redirect_checkout_add_cart');
		$this->loader->add_filter('woocommerce_package_rates', $plugin_public, 'add_to_prev_order_shipping', 100);
		$this->loader->add_filter('woocommerce_order_actions',  $plugin_public, 'filter_wc_add_send_special_promo', 20, 1);

		// source: https://stackoverflow.com/questions/4647604/wp-use-file-in-plugin-directory-as-custom-page-template
		$this->loader->add_action('template_include', $plugin_public, 'template_include');

		// $this->loader->add_filter('rest_authentication_errors', $plugin_public, 'filter_incoming_connections');
	}

	private function define_public_wp_ajax_hooks()
	{
		$plugin_public = new Design_Pact_Public($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('wp_ajax_my_actionn', $plugin_public, 'my_action');
		$this->loader->add_action('wp_ajax_nopriv_my_actionn', $plugin_public, 'my_action');

		$this->loader->add_action('wp_ajax_my_update_item', $plugin_public, 'my_update_item');
		$this->loader->add_action('wp_ajax_nopriv_my_update_item', $plugin_public, 'my_update_item');

		$this->loader->add_action('wp_ajax_get_update_cart_item', $plugin_public, 'get_update_cart_item');
		$this->loader->add_action('wp_ajax_nopriv_get_update_cart_item', $plugin_public, 'get_update_cart_item');

		$this->loader->add_action('wp_ajax_my_update_cart_item', $plugin_public, 'my_update_cart_item');
		$this->loader->add_action('wp_ajax_nopriv_my_update_cart_item', $plugin_public, 'my_update_cart_item');

		$this->loader->add_action('wp_ajax_change_cart_item_size', $plugin_public, 'change_cart_item_size');
		$this->loader->add_action('wp_ajax_nopriv_change_cart_item_size', $plugin_public, 'change_cart_item_size');

		//add to cart inside "/matching-sneakers/nike-air-raid-dark-grey-multi-color-pine/"
		$this->loader->add_action('wp_ajax_my_custom_cart_item', $plugin_public, 'my_custom_cart_item');
		$this->loader->add_action('wp_ajax_nopriv_my_custom_cart_item', $plugin_public, 'my_custom_cart_item');

		$this->loader->add_action('wp_ajax_my_customized_preset_cart_item', $plugin_public, 'my_customized_preset_cart_item');
		$this->loader->add_action('wp_ajax_nopriv_my_customized_preset_cart_item', $plugin_public, 'my_customized_preset_cart_item');

		$this->loader->add_action('wp_ajax_change_order_item_attributes', $plugin_public, 'change_order_item_attributes');
		$this->loader->add_action('wp_ajax_nopriv_change_order_item_attributes', $plugin_public, 'change_order_item_attributes');

		// wc_add_item_to_cart
		$this->loader->add_action('wp_ajax_dv_wc_add_item_to_cart', $plugin_public, 'wc_add_item_to_cart');
		$this->loader->add_action('wp_ajax_nopriv_dv_wc_add_item_to_cart', $plugin_public, 'wc_add_item_to_cart');

		// wc_add_items_to_cart
		$this->loader->add_action('wp_ajax_dv_wc_add_items_to_cart', $plugin_public, 'wc_add_items_to_cart');
		$this->loader->add_action('wp_ajax_nopriv_dv_wc_add_items_to_cart', $plugin_public, 'wc_add_items_to_cart');

		// get_current_user
		$this->loader->add_action('wp_ajax_dv_get_current_user', $plugin_public, 'get_current_user');
		$this->loader->add_action('wp_ajax_nopriv_dv_get_current_user', $plugin_public, 'get_current_user');
	}

	private function register_rest_fields()
	{
		register_rest_field('sneaker', 'metadata', array(
			'get_callback' => function ($data) {
				return get_post_meta($data['id']);
				// return get_fields($data['id'], true);
			},
		));
	}


	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Design_Pact_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{
		return $this->version;
	}
}
