<?php

// Method 2
class PublicRestAPI{

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

        // NEEDED????? :)

		// $this->define_admin_hooks();
		// $this->define_public_hooks();
		// $this->define_shortcodes();
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
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/services-functions/design-pact-utility-functions.php';

		/**
		 * The temp functions
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/services-functions/design-pact-temp-functions.php';

		/**
		 * The class responsible for DesignPact Product
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/services-functions/class-design-pact-product.php';

		/**
		 * The class responsible for design-pact-product
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-design-pact-product.php';

		/**
		 * The class responsible for DesignPact Woocommerce Service
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/services-functions/class-design-pact-woocommerce-service.php';

		/**
		 * The class responsible for DesignPact Printful Service
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/services-functions/class-design-pact-printful-service.php';

		/**
		 * The class responsible for DesignPact Twilio Service
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/services-functions/class-design-pact-twilio-service.php';

		/**
		 * The class responsible for DesignPact ShipEngine Service
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/services-functions/class-design-pact-shipengine-service.php';

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


    private function userRoute(){

        // get_current_user_id
		add_action('rest_api_init', function () {
			register_rest_route($this->namespace_old, '/get_current_user_id', array(
				'methods' => 'GET',
				'callback' => array($this->plugin_public, 'get_current_user_id'),
				'permission_callback' => '__return_true',
			));
		});

		// add_to_user_specific_products_data
		add_action('rest_api_init', function () {
			register_rest_route($this->namespace_old, '/user_specific_products_data', array(
				'methods' => 'POST',
				'callback' => array($this->plugin_public, 'add_to_user_specific_products_data'),
				'permission_callback' => '__return_true',
			));
		});

		// get_user_specific_products_data
		add_action('rest_api_init', function () {
			register_rest_route($this->namespace_old, '/user_specific_products_data', array(
				'methods' => 'GET',
				'callback' => array($this->plugin_public, 'get_user_specific_products_data'),
				'permission_callback' => '__return_true',
			));
		});

		// delete_user_specific_products_data
		add_action('rest_api_init', function () {
			register_rest_route($this->namespace_old, '/user_specific_products_data', array(
				'methods' => 'DELETE',
				'callback' => array($this->plugin_public, 'delete_user_specific_products_data'),
				'permission_callback' => '__return_true',
			));
		});

        // add_to_saved_sneakers__cb
		add_action('rest_api_init', function () {
			register_rest_route($this->namespace, '/users/saved-sneakers', array(
				'methods' => 'POST',
				'callback' => array($this->plugin_public, 'add_to_saved_sneakers__cb'),
				'permission_callback' => '__return_true',
			));
		});

		// get_saved_sneakers__cb
		add_action('rest_api_init', function () {
			register_rest_route($this->namespace, '/users/saved-sneakers', array(
				'methods' => 'GET',
				'callback' => array($this->plugin_public, 'get_saved_sneakers__cb'),
				'permission_callback' => '__return_true',
			));
		});

		// delete_saved_sneakers__cb
		add_action('rest_api_init', function () {
			register_rest_route($this->namespace, '/users/saved-sneakers', array(
				'methods' => 'DELETE',
				'callback' => array($this->plugin_public, 'delete_saved_sneakers__cb'),
				'permission_callback' => '__return_true',
			));
		});
    }

    private function productRoute(){

        // wc_add_item_to_cart
		add_action('rest_api_init', function () {
			register_rest_route($this->namespace_old, '/wc_add_item_to_cart', array(
				'methods' => 'POST',
				'callback' => array($this->plugin_public, 'wc_add_item_to_cart'),
				'permission_callback' => '__return_true',
			));
		});

		// wc_add_items_to_cart
		add_action('rest_api_init', function () {
			register_rest_route($this->namespace_old, '/wc_add_items_to_cart', array(
				'methods' => 'POST',
				'callback' => array($this->plugin_public, 'wc_add_items_to_cart'),
				'permission_callback' => '__return_true',
			));
		});

        // wc_get_product_variations
		add_action('rest_api_init', function () {
			register_rest_route($this->namespace_old, '/wc_get_product_variations', array(
				'methods' => 'GET',
				'callback' => array($this->plugin_public, 'wc_get_product_variations'),
				'permission_callback' => '__return_true',
			));
		});

        // wc_get_product_variation
        add_action('rest_api_init', function () {
            register_rest_route($this->namespace, '/wc_get_product_variation', array(
                'methods' => 'GET',
                'callback' => array($this->plugin_public, 'wc_get_product_variation__cb'),
                'permission_callback' => '__return_true',
            ));
        });

		// get_products_in_collection
		add_action('rest_api_init', function () {
			register_rest_route($this->namespace_old, '/get_products_in_collection', array(
				'methods' => 'GET',
				'callback' => array($this->plugin_public, 'get_products_in_collection'),
				'permission_callback' => '__return_true',
			));
		});

		// get_related_products
		add_action('rest_api_init', function () {
			register_rest_route($this->namespace_old, '/get_related_products', array(
				'methods' => 'GET',
				'callback' => array($this->plugin_public, 'get_related_products'),
				'permission_callback' => '__return_true',
			));
		});

        // get_products_from_data
		add_action('rest_api_init', function () {
			register_rest_route($this->namespace_old, '/get_products_from_data', array(
				'methods' => 'POST',
				'callback' => array($this->plugin_public, 'get_products_from_data'),
				'permission_callback' => '__return_true',
			));
		});

		// get_related_videos
		add_action('rest_api_init', function () {
			register_rest_route($this->namespace_old, '/get_related_videos', array(
				'methods' => 'GET',
				'callback' => array($this->plugin_public, 'get_related_videos'),
				'permission_callback' => '__return_true',
			));
		});

        // get_sneakers
		add_action('rest_api_init', function () {
			register_rest_route($this->namespace, '/sneakers', array(
				'methods' => 'GET',
				'callback' => array($this->plugin_public, 'mk_get_sneakers'),
				'permission_callback' => '__return_true',
			));
		});

        add_action('rest_api_init', function () {
			register_rest_route($this->namespace, '/design_pact_product', array(
				'methods' => 'GET',
				'callback' => array($this->plugin_public, 'get_design_pact_product'),
				'permission_callback' => '__return_true',
			));
		});

		add_action('rest_api_init', function () {
			register_rest_route($this->namespace, '/sneakers/release_dates', array(
				'methods' => 'GET',
				'callback' => array($this->plugin_public, 'rest_api__get_release_date_sneakers__cb'),
				'permission_callback' => '__return_true',
			));
		});

        add_action('rest_api_init', function () {
			register_rest_route($this->namespace, '/recent-purchases', array(
				'methods' => 'GET',
				'callback' => array($this->plugin_public, 'rest_api__get_recent_purchases__cb'),
				'permission_callback' => '__return_true',
			));
		});
    }

    private function wcRoute(){
        add_action('rest_api_init', function () {
			register_rest_route('mkdv/feeds/v1', '/customizer_page', array(
				'methods' => 'GET',
				'callback' => array($this->plugin_public, 'mkdv_get_dropdown_data'),
				'permission_callback' => '__return_true',
			));
		});

        add_action('rest_api_init', function () {
			register_rest_route($this->namespace, '/get_dropdown_data', array(
				'methods' => 'GET',
				'callback' => array($this->plugin_public, 'get_dropdown_data'),
				'permission_callback' => '__return_true',
			));
		});

        add_action('rest_api_init', function () {
			register_rest_route($this->namespace, 'preselected_variation/(?P<pid>\d+)', array(
				'methods' => 'GET',
				'callback' => array($this->plugin_public, 'get_preselected_variation'),
				'permission_callback' => '__return_true',
			));
		});

        // send_recommended_products_to_customer
		add_action('rest_api_init', function () {
			register_rest_route($this->namespace, '/actions/send-recommended-products-to-customer', array(
				'methods' => 'GET',
				'callback' => array($this->plugin_public, 'send_recommended_products_to_customer_cb'),
				'permission_callback' => '__return_true',
			));
		});

		// update_customer_blacklist
		add_action('rest_api_init', function () {
			register_rest_route($this->namespace, '/actions/update-customer-blacklist', array(
				'methods' => 'GET',
				'callback' => array($this->plugin_public, 'update_customer_blacklist_cb'),
				'permission_callback' => '__return_true',
			));
		});
    }

    private function orderRoutes(){
        // wc_merge_orders
		add_action('rest_api_init', function () {
			register_rest_route($this->namespace_old, '/wc_merge_orders', array(
				'methods' => 'GET',
				'callback' => array($this->plugin_public, 'wc_merge_orders'),
				'permission_callback' => '__return_true',
			));
		});

		// pf_create_order_cron
		add_action('rest_api_init', function () {
			register_rest_route($this->namespace, '/printful/orders/create/cron', array(
				'methods' => 'GET',
				'callback' => array($this->plugin_public, 'pf_create_order_cron_cb'),
				'permission_callback' => '__return_true',
			));
		});

		// pf_create_order
		add_action('rest_api_init', function () {
			register_rest_route($this->namespace, '/printful/orders/create', array(
				'methods' => 'GET',
				'callback' => array($this->plugin_public, 'pf_create_order_cb'),
				'permission_callback' => '__return_true',
			));
		});

		// pf_get_order
		add_action('rest_api_init', function () {
			register_rest_route($this->namespace, '/printful/orders/get', array(
				'methods' => 'GET',
				'callback' => array($this->plugin_public, 'pf_get_order_cb'),
				'permission_callback' => '__return_true',
			));
		});

		// pf_cancel_order
		add_action('rest_api_init', function () {
			register_rest_route($this->namespace, '/printful/orders/cancel', array(
				'methods' => 'GET',
				'callback' => array($this->plugin_public, 'pf_cancel_order_cb'),
				'permission_callback' => '__return_true',
			));
		});

        // wc_wipe_order_amount
		add_action('rest_api_init', function () {
			register_rest_route($this->namespace_old, '/wc_wipe_order_amount', array(
				'methods' => 'GET',
				'callback' => array($this->plugin_public, 'wc_wipe_order_amount'),
				'permission_callback' => '__return_true',
			));
		});

        // order_lookup
		add_action('rest_api_init', function () {
			register_rest_route($this->namespace, '/actions/order-lookup', array(
				'methods' => 'GET',
				'callback' => array($this->plugin_public, 'order_lookup_cb'),
				'permission_callback' => '__return_true',
			));
		});

		// wc_update_in_order_status_for_order_item
		add_action('rest_api_init', function () {
			register_rest_route($this->namespace, '/actions/order-items/update-in-order-status', array(
				'methods' => 'GET',
				'callback' => array($this->plugin_public, 'wc_update_in_order_status_for_order_item_cb'),
				'permission_callback' => function () {
					$allowed = false;
					if (current_user_can("read_shop_order") || current_user_can("administrator")) {
						$allowed = true;
					}
					return $allowed;
				},
				// 'callback' => function ($request) {
				// 	$return = wp_create_nonce('wp_rest');
				// 	return $return;
				// },
				// 'permission_callback' => '__return_true',
			));
		});

		// merchant_feed
		add_action('rest_api_init', function () {
			register_rest_route($this->namespace, '/actions/merchant-feed', array(
				'methods' => 'GET',
				'callback' => array($this->plugin_public, 'merchant_feed_cb'),
				'permission_callback' => '__return_true',
			));
		});

        // send_return_label_via_api__cb
        add_action('rest_api_init', function () {
            register_rest_route($this->namespace, '/wc/orders/send-return-label', array(
                'methods' => 'GET',
                'callback' => array($this->plugin_public, 'send_return_label_via_api__cb'),
                'permission_callback' => '__return_true',
            ));
        });

    }

    private function printfullRoutes(){
        // pf_reset_push_attempts
        add_action('rest_api_init', function () {
            register_rest_route($this->namespace, '/printful/orders/reset-pf-push-attempts', array(
                'methods' => 'GET',
                'callback' => array($this->plugin_public, 'pf_reset_push_attempts_cb'),
                'permission_callback' => '__return_true',
            ));
        });

        // pf_api_webhook
        add_action('rest_api_init', function () {
            register_rest_route($this->namespace, '/printful/api-webhook', array(
                'methods' => 'POST',
                'callback' => array($this->plugin_public, 'pf_api_webhook_cb'),
                'permission_callback' => '__return_true',
            ));
        });

        // pf_get_variation
        add_action('rest_api_init', function () {
            register_rest_route($this->namespace, '/printful/get-variation', array(
                'methods' => 'GET',
                'callback' => array($this->plugin_public, 'pf_get_variation_cb'),
                'permission_callback' => '__return_true',
            ));
        });

        // pf_fetch_and_cache_products
        add_action('rest_api_init', function () {
            register_rest_route($this->namespace, '/printful/fetch-and-cache-products', array(
                'methods' => 'GET',
                'callback' => array($this->plugin_public, 'pf_fetch_and_cache_products_cb'),
                'permission_callback' => '__return_true',
            ));
        });

        // pf_cached_variations
        add_action('rest_api_init', function () {
            register_rest_route($this->namespace, '/printful/get-cached-variations', array(
                'methods' => 'GET',
                'callback' => array($this->plugin_public, 'pf_cached_variations_cb'),
                'permission_callback' => '__return_true',
            ));
        });

        // pf_map_variation
        add_action('rest_api_init', function () {
            register_rest_route($this->namespace, '/printful/map-variation', array(
                'methods' => 'GET',
                'callback' => array($this->plugin_public, 'pf_map_variation_cb'),
                'permission_callback' => '__return_true',
            ));
        });

        add_action('rest_api_init', function () {
			register_rest_route($this->namespace, '/printful/sync-order-statuses', array(
				'methods' => 'GET',
				'callback' => array($this->plugin_public, 'rest_api__printful_sync_order_statuses__cb'),
				'permission_callback' => '__return_true',
			));
		});
    }

    private function twilioRoutes(){
        // get_order_status
		add_action('rest_api_init', function () {
			register_rest_route($this->namespace, '/twilio/get-order-status', array(
				'methods' => 'POST',
				'callback' => array($this->plugin_public, 'twilio__get_order_status_cb'),
				'permission_callback' => '__return_true',
			));
		});

		// twilio__send_promo
		add_action('rest_api_init', function () {
			register_rest_route($this->namespace, '/twilio/send-promo', array(
				'methods' => 'GET',
				'callback' => array($this->plugin_public, 'twilio__send_promo_cb'),
				'permission_callback' => '__return_true',
			));
		});

    }


}