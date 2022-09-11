<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://farazahmad.net
 * @since      1.0.0
 *
 * @package    Design_Pact
 * @subpackage Design_Pact/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Design_Pact
 * @subpackage Design_Pact/public
 * @author     Faraz Ahmad <farazahmad759@gmail.com>
 */
class Design_Pact_Public
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

	public $dv_helpers;

	public $woocommerce_service;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->dv_helpers = DV_Helpers::get_instance();
		$this->woocommerce_service = DPDV_WooCommerce_Service::get_instance();
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/design-pact-public.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/design-pact-public.js', array('jquery'), $this->version, false);
	}

	/*************************************************
	 * Woocommerce Callbacks
	 *************************************************/


	public function get_dropdown_data($data)
	{
		// override $product_id --> added for plugin support
		$product_id = mkdv_get_wc_main_product_id_with_variations();

		$handle = new WC_Product_Variable($product_id);
		$variations1 = $handle->get_children();
		$productTypes = array();
		$productSizes = array();
		$actualVariations = array();
		$productSizesNew = array();

		$sizeTerms = get_terms('pa_product-size');

		foreach ($sizeTerms as $sizeTerm) {
			$finalSizeTerms[] = $sizeTerm->slug;
		}

		// Loop through all possible variations in the database
		foreach ($variations1 as $x => $value) {
			$single_variation = new WC_Product_Variation($value);
			$v = $single_variation->get_variation_attributes();

			$temp = array();
			$temp['data-type'] = $v['attribute_pa_product-type'];
			$temp['data-color'] = $v['attribute_pa_product-color'];
			$temp['description'] = ucwords(str_replace("-", " ", $v['attribute_pa_product-color'] . " " . $v['attribute_pa_product-type']));
			array_push($productTypes, $temp);

			$temp = array();
			$temp['data-size'] = $v['attribute_pa_product-size'];
			$temp['description'] = get_term_by('slug', $v['attribute_pa_product-size'], 'pa_product-size')->name;
			// $temp['description'] = ucwords(str_replace("-", " ", $v['attribute_pa_product-size']));
			array_push($productSizes, $temp);

			$temp = array();
			$temp['data-size'] = $v['attribute_pa_product-size'];
			$temp['data-type'] = $v['attribute_pa_product-type'];
			$temp['data-color'] = $v['attribute_pa_product-color'];
			$temp['value'] = $value;
			$temp['data-size'] = get_term_by('slug', $v['attribute_pa_product-size'], 'pa_product-size')->slug;
			$temp['stock-status'] = $single_variation->get_data()['stock_status'];
			array_push($actualVariations, $temp);

			// -------------------------------------------------------- testing starts
			$temp = array();
			$temp['data-size'] = get_term_by('slug', $v['attribute_pa_product-size'], 'pa_product-size')->slug;
			$temp['description'] = get_term_by('slug', $v['attribute_pa_product-size'], 'pa_product-size')->name;
			$productSizesNew[array_search($v['attribute_pa_product-size'], $finalSizeTerms)] = $temp;
			// -------------------------------------------------------- testing ends

		}
		$productTypes = mkdv_remove_duplicates_from_array($productTypes, 'description');
		$productSizes = mkdv_remove_duplicates_from_array($productSizes, 'data-size');
		ksort($productSizesNew);
		$productSizesNew = mkdv_remove_duplicates_from_array($productSizesNew, 'data-size');
		$productSizes = $productSizesNew;

		// prepare output
		$out = array();
		$out['productSizesNew'] = $productSizesNew;
		$out['productTypes'] = $productTypes;
		$out['productSizes'] = $productSizes;
		$out['actualVariations'] = $actualVariations;

		// -------------------------------------
		// testing starts

		// testing ends
		// -------------------------------------

		echo json_encode($out);
	}

	function get_preselected_variation(WP_REST_Request $request)
	{
		$pid = $request['pid'];

		$preSelectedVariation = $request['pid'];
		$preSelectedVar = new WC_Product_Variation($preSelectedVariation);
		$preV = $preSelectedVar->get_variation_attributes();

		// designs
		$temp = array();
		$design = array();
		$temp['designId'] = esc_attr($_GET['did']);
		$temp['designImage'] = remove_wp_upload_base_url(get_the_post_thumbnail_url($_GET['did'], "full"));
		$temp['designMeta'] = get_post_meta($_GET['did']);
		$temp['designTitle'] = get_the_title($_GET['did']);
		$temp['designLayers'] = json_encode(MKGetLayers($temp['designMeta']));
		$design = $temp;

		// sneakers
		$temp = array();
		$sneaker = array();
		$temp['sneakerId'] = esc_attr($_GET['sid']);
		$temp['sneakerImage'] = esc_attr(get_the_post_thumbnail_url($_GET['sid'], "medium"));
		$temp['sneakerMeta'] = get_post_meta($_GET['sid']);
		$temp['sneakerTitle'] = get_the_title($_GET['sid']);
		$temp['colorData'] = MKGetBestColors($temp['sneakerMeta']);
		$temp['sneakerColors'] = $temp['colorData']['colors'];
		$sneaker = $temp;

		//
		$out = array();
		$out['pid'] = $pid;
		$out['sid'] = $_GET['sid'];
		$out['did'] = $_GET['did'];
		$out['product'] = $preV;
		$out['design'] = $design;
		$out['sneaker'] = $sneaker;
		echo json_encode($out);
	}


	// new
	public function wc_merge_orders($data)
	{
		global $wpdb;

		$confirm = $data->get_param('confirm');
		$double_confirm = $data->get_param('double_confirm');
		if ($confirm != 'yes' || $double_confirm != 'yes') {
			die("not allowed");
		}
		die("not allowed");

		$new_order_id = $data->get_param('new_order_id');
		$prev_order_id = $data->get_param('prev_order_id');

		$new_order = wc_get_order($new_order_id);
		$prev_order = wc_get_order($prev_order_id);


		$new_order->update_status('wc-merged');
		$new_order->add_order_note(__("Merged into order with Number = " . get_post_meta($prev_order_id, "_order_number", true)));

		$prev_order->add_order_note(__("Order with Number = " . get_post_meta($new_order_id, "_order_number", true) . " has been merged into this order."));

		$new_order_items = $new_order->get_items();
		foreach ($new_order_items as $key => $item) {
			$results = $wpdb->get_results(
				"UPDATE {$wpdb->prefix}woocommerce_order_items 
				SET order_id = {$prev_order_id} 
				WHERE order_item_id = {$item->get_id()} AND order_id = {$new_order_id} LIMIT 1",
				OBJECT
			);
		}

		$this->trigger_action_send_special_promo($new_order);
	}


	public function wc_add_item_to_cart($data)
	{

		// $variationID = $data->get_param('variationID');
		// $designID = $data->get_param('designID');
		// $shoeID = $data->get_param('shoeID');
		// $newDesign = $data->get_param('newDesign');
		// $shoeDesign = $data->get_param('shoeDesign');

		$variationID = $_POST['variationID'];
		$designID = $_POST['designID'];
		$shoeID = $_POST['shoeID'];
		$newDesign = $_POST['newDesign'];
		$shoeDesign = $_POST['shoeDesign'];

		$this->woocommerce_service->wc_add_item_to_cart($variationID, array(
			'designID' => $designID,
			'shoeID' => $shoeID,
			'newDesign' => $newDesign,
			'shoeDesign' => $shoeDesign,
		));

		return 'done';
	}

	public function wc_add_items_to_cart($data)
	{

		// $items_data = $data->get_param('items_data');
		$items_data = $_POST['items_data'];

		$this->woocommerce_service->wc_add_items_to_cart($items_data);

		return 'done';
	}

	public function wc_get_product_variations($data)
	{

		$productType = $data->get_param('productType');
		$productColor = $data->get_param('productColor');
		$productSize = $data->get_param('productSize');

		$product_id = mkdv_get_wc_main_product_id_with_variations();
		$actualVariations = $this->woocommerce_service->wc_get_product_variations($product_id, array(
			'productType' => $productType,
			'productColor' => $productColor,
			'productSize' => $productSize,
		));


		return $actualVariations;
	}

	public function get_products_in_collection($data)
	{

		$sneaker_slug = $data->get_param("sneaker_slug");
		$design_slug = $data->get_param("design_slug");
		$product_type = $data->get_param("product_type");

		if (!isset($sneaker_slug)) {
			echo json_encode(array(
				'error' => 'sneaker_slug cannot be NULLT'
			));
		}
		if (!isset($design_slug)) {
			echo json_encode(array(
				'error' => 'design_slug cannot be NULLT'
			));
		}

		if (!isset($product_type)) {
			$product_type = 't-shirt';
		}

		$dv_products = array();
		$dv_products = $this->dv_helpers->get_products_in_collection($sneaker_slug, $design_slug, $product_type);

		return $dv_products;
	}

	public function get_related_products($data)
	{

		$sneaker_slug = $data->get_param("sneaker_slug");
		$design_slug = $data->get_param("design_slug");

		if (!isset($sneaker_slug)) {
			echo json_encode(array(
				'error' => 'sneaker_slug cannot be NULLT'
			));
		}
		if (!isset($design_slug)) {
			echo json_encode(array(
				'error' => 'design_slug cannot be NULLT'
			));
		}

		$dv_products = array();
		$dv_products = $this->dv_helpers->get_related_products($sneaker_slug, $design_slug);

		return $dv_products;
	}

	public function get_current_user_id()
	{
		echo get_current_user_id();
	}

	public function add_to_user_specific_products_data($data)
	{
		$user_id = $data->get_param("user_id");
		$meta_key = $data->get_param("meta_key");
		$sneaker_slug = $data->get_param("sneaker_slug");
		$design_slug = $data->get_param("design_slug");
		$product_type = $data->get_param("product_type");

		if (!isset($meta_key)) {
			$meta_key = 'recently_viewed_products_data';
		}

		if (!isset($user_id)) {
			return "user_id cannot be NULL";
		}

		if (!isset($sneaker_slug)) {
			return "sneaker_slug cannot be NULL";
		}

		if (!isset($design_slug)) {
			return "design_slug cannot be NULL";
		}

		if (!isset($product_type)) {
			$product_type = "t-shirt";
		}

		// delete_user_meta($user_id, $meta_key);
		// $metas = get_user_meta($user_id, $meta_key);

		// $product_already_exists = false;
		// foreach ($metas as $m) {
		// 	if ($m['sneaker_slug'] == $sneaker_slug && $m['design_slug'] == $design_slug && $m['product_type'] == $product_type) {
		// 		$product_already_exists = true;
		// 	}
		// }

		// if ($product_already_exists) {
		// 	return "already exists";
		// }

		delete_user_meta($user_id, $meta_key, array(
			'sneaker_slug' => $sneaker_slug,
			'design_slug' => $design_slug,
			'product_type' => $product_type,
		));

		add_user_meta($user_id, $meta_key, array(
			'sneaker_slug' => $sneaker_slug,
			'design_slug' => $design_slug,
			'product_type' => $product_type,
		));

		return "done";
	}

	public function get_user_specific_products_data($data)
	{

		$user_id = $data->get_param("user_id");

		if (!$user_id) {
			return [];
		}
		$meta_key = $data->get_param("meta_key");
		$include_sneaker_object = $data->get_param("include_sneaker_object");

		if (!isset($meta_key)) {
			$meta_key = 'recently_viewed_products_data';
		}

		$args = array('user_id' => $user_id, 'meta_key' => $meta_key);
		if (isset($include_sneaker_object)) {
			$args['include_sneaker_object'] = $include_sneaker_object;
		}
		$return = $this->dv_helpers->get_user_specific_products_data($args);

		return $return;
	}

	public function delete_user_specific_products_data($data)
	{
		$user_id = $data->get_param("user_id");
		$meta_key = $data->get_param("meta_key");
		$sneaker_slug = $data->get_param("sneaker_slug");
		$design_slug = $data->get_param("design_slug");
		$product_type = $data->get_param("product_type");


		if (!isset($meta_key)) {
			$meta_key = 'recently_viewed_products_data';
		}

		delete_user_meta($user_id, $meta_key, array(
			'sneaker_slug' => $sneaker_slug,
			'design_slug' => $design_slug,
			'product_type' => $product_type,
		));

		echo 'done';
	}

	public function get_products_from_data($data)
	{

		$products_data = $data->get_param("products_data");

		$return = $this->dv_helpers->get_products_from_data($products_data);

		return $return;
	}

	public function wc_wipe_order_amount($data)
	{

		$order_id = $data->get_param("order_id");

		if (!isset($order_id)) {
			echo "order_id cannot be NULL";
		}

		$return = $this->woocommerce_service->wc_wipe_order_amount($order_id);

		return "DONE";
	}

	public function get_related_videos($data)
	{

		$args = [];

		$videos = $this->dv_helpers->get_related_videos($args);

		return $videos;
	}


	function wp_enqueue_scripts_001()
	{
		wp_register_script('jscolor', 'https://cdnjs.cloudflare.com/ajax/libs/jscolor/2.3.3/jscolor.js', null, null, true);
		wp_enqueue_script('jscolor');

		wp_register_script("clipboardjs", "https://cdn.jsdelivr.net/npm/clipboard@2.0.6/dist/clipboard.min.js", null, null, true);
		wp_enqueue_script('clipboardjs');

		wp_register_script("base64-url", "https://cdn.jsdelivr.net/npm/base64-url@2.3.3/index.min.js", null, null, true);
		wp_enqueue_script('base64-url');
	}


	function custom_data_hidden_fields()
	{
		echo '<div class="imput_fields custom-imput-fields">
			<input type="hidden" id="design_prod" name="attribute_pa_product-design" data-attribute_name="attribute_pa_product-design" value="" />
			<input type="hidden" id="shoe_prod" name="attribute_pa_shoe-design" data-attribute_name="attribute_pa_shoe-design" value="" />
			<!--<input type="hidden" id="extra_prod" name="attribute_pa_extra-data" data-attribute_name="attribute_pa_extra-data" value="" />-->
			<input type="hidden" id="shoeID" name="attribute_pa_shoeID" data-attribute_name="attribute_pa_shoeId" value="" />
			<input type="hidden" id="designID" name="attribute_pa_designID" data-attribute_name="attribute_pa_designId" value="" />
		</div><br>';
	}




	function iconic_add_engraving_text_to_order_items($item, $cart_item_key, $values, $order)
	{
		$item->add_meta_data(__('Design', 'attribute_pa_product-design'), $values['custom_data']['attribute_pa_product-design']);
		$item->add_meta_data(__('Shoe', 'attribute_pa_shoe-design'), $values['custom_data']['attribute_pa_shoe-design']);
		//$item->add_meta_data( __( 'Extra', 'attribute_pa_extra-data' ), $values['custom_data']['attribute_pa_extra-data'] );
		$item->add_meta_data(__('ShoeID', 'attribute_pa_shoeID'), $values['custom_data']['attribute_pa_shoeID']);
		$item->add_meta_data(__('DesignID', 'attribute_pa_designID'), $values['custom_data']['attribute_pa_designID']);
	}

	function save_custom_data_hidden_fields($cart_item_data, $product_id)
	{


		$data = array();


		if (isset($_POST['attribute_pa_product-design'])) {
			$cart_item_data['custom_data']['attribute_pa_product-design'] = $_POST['attribute_pa_product-design'];
			$data['attribute_pa_product-design'] = $_POST['attribute_pa_product-design'];
		}

		if (isset($_POST['attribute_pa_shoe-design'])) {
			$cart_item_data['custom_data']['attribute_pa_shoe-design'] = $_POST['attribute_pa_shoe-design'];
			$data['attribute_pa_shoe-design'] = $_POST['attribute_pa_shoe-design'];
		}

		if (isset($_POST['attribute_pa_extra-data'])) {
			$cart_item_data['custom_data']['attribute_pa_extra-data'] = $_POST['attribute_pa_extra-data'];
			$data['attribute_pa_extra-data'] = $_POST['attribute_pa_extra-data'];
		}

		if (isset($_POST['attribute_pa_extra-data'])) {
			$cart_item_data['custom_data']['attribute_pa_shoeID'] = $_POST['attribute_pa_shoeID'];
			$data['attribute_pa_shoeID'] = $_POST['attribute_pa_shoeID'];
		}

		if (isset($_POST['attribute_pa_extra-data'])) {
			$cart_item_data['custom_data']['attribute_pa_designID'] = $_POST['attribute_pa_designID'];
			$data['attribute_pa_designID'] = $_POST['attribute_pa_designID'];
		}

		// below statement make sure every add to cart action as unique line item
		$cart_item_data['custom_data']['unique_key'] = md5(microtime() . rand());
		WC()->session->set('price_calculation', $data);

		return $cart_item_data;
	}

	function insert_my_js_uprs()
	{
?>
		<script>
			jQuery("input").attr('autocomplete', 'off');
		</script>
	<?php
	}

	function update_cartstack_conversion($order_id)
	{
		$order = wc_get_order($order_id);

		$curl = curl_init();

		$query = array(
			"key" => "f958db5b2927aad29a4619bcf582fece",
			"siteid" => "k5VYWllK",
			"email" => $order->get_billing_email(),
			"total" => $order->get_total()
		);

		curl_setopt_array($curl, array(
			CURLOPT_URL => "https://api.cartstack.com/ss/v1/?" . http_build_query($query),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_POSTFIELDS => "{\r\n    \"is_portal_enabled\": true,\r\n    \"can_add_card\": true,\r\n    \"can_add_bank_account\": true\r\n}",
			CURLOPT_HTTPHEADER => array(
				"authorization: Zoho-oauthtoken 1000.0a233f1074584d49b202b0cc2744b98b.2b5a89424620c10d224d920866f2df40",
				"cache-control: no-cache",
				"content-type: application/json;charset=UTF-8",
				"postman-token: 155a7935-dc19-16b5-c470-07744ab07b07",
				"x-com-zoho-subscriptions-organizationid: 680928242"
			),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);
	}

	function update_titles($order_id)
	{
		global $wpdb;
		if (!$order_id)
			return;

		// Get an instance of the WC_Order object
		$order = wc_get_order($order_id);

		$order_number = get_post_meta($order_id, '_order_number', true);


		// Auto Upgrade Rush / Express Orders based on the Coupon Codes.
		foreach ($order->get_coupon_codes() as $coupon_code) {
			if ($coupon_code == 'reprint') {
				//$upgradeResults = $wpdb->get_results("UPDATE `mk_woocommerce_order_items`  SET `order_item_name` = 'Preferred (Est Delivery in 4-8 Business Days)' WHERE `order_item_type` = 'shipping' AND `order_id` = '".$order_id."'");
			}
		}

		// Loop through order items
		$totalItems = 0;
		//wp_mail("kody@iconicwebhq.com", "Items", print_r($order->get_items(),1));



		// Iterating through order shipping items
		foreach ($order->get_items('shipping') as $item_id => $item) {
			if (strpos($item->get_method_title(), "Add to Previous Order") !== false) {
				$thisIsAnAddOnOrder = true;
			}
		}

		if (!($thisIsAnAddOnOrder)) {
			// Add this person's order number to COOKIE's as "Recent Order"
			setcookie("recent_order", $order_id, time() + (86400 * 30), "/");
		}





		$rush = false;
		foreach ($order->get_items() as $item_id => $item) {
			$totalItems++;

			$in_stock = get_post_meta($item->get_variation_id(), '_york_warehouse_in_stock', true);

			//wp_mail("kody@iconicwebhq.com", "Report for Product #" . $item->get_variation_id(), "Stock is" . $in_stock);
			if ($in_stock != "1") {
				$atLeastOneProductNotInYorkWarehouse = "true";
			}

			if ($item->is_type('variable')) {
			}
			unset($variation_id);

			if ($item->get_meta("Design")) {
				parse_str(parse_url($item->get_meta("Design"))['query'], $designUrl);
				$image = getRenderedImage($item->get_meta("pa_product-color"), $designUrl['file'], $item->get_meta("Shoe"), null, $item->get_meta("pa_product-type"), json_decode($designUrl['data']));
				woocommerce_add_order_item_meta($item_id, 'OG Image', $image);
			}

			if ($item->get_meta("Order Number")) {
				$postmeta_table = $wpdb->prefix . "postmeta";
				$upgradeOrderNumber = $wpdb->get_results("SELECT `post_id` FROM `$postmeta_table` WHERE `meta_key` = '_order_number' AND `meta_value` =  " . $item->get_meta("Order Number"))[0]->post_id;
				$upgradeResults = $wpdb->get_results("UPDATE `mk_woocommerce_order_items`  SET `order_item_name` = 'Rush (Est Delivery in 3-4 Business Days)' WHERE `order_item_type` = 'shipping' AND `order_id` = '" . $upgradeOrderNumber . "'");
				$rush = true;
			}

			if ($item->get_meta("Design")) {

				do {
					$id = unique_id() . '.png';
					$results = $wpdb->get_results("SELECT * FROM `mk_woocommerce_order_itemmeta` WHERE `meta_value` = '{$id}'");
				} while ($id == $results[0]->meta_value);

				woocommerce_add_order_item_meta($item_id, 'pa_design-id', $id);

				global $wpdb;

				$wpdb->insert('mk_customer_designs', array(
					'order_id' => $order_id,
					'design' => $id,
					'status' => 'pending', // ... and so on
				));

				$wpdb->insert('mk_sales_log', array(
					'order_id' => $order_id,
					'order_item_id' => $item_id,
					'design_id' => $item->get_meta("DesignID"),
					'sneaker_id' => $item->get_meta("ShoeID"),
					'design_filename' => $id,
					'qty' => $item['quantity'],
					'emailed' => 'pending',
					'commission' => 'pending', // ... and so on
				));
			}
			if ($item->get_meta("DesignID")) {
				woocommerce_add_order_item_meta($item_id, 'Design Name', get_the_title($item->get_meta("DesignID")));
			}
			if ($item->get_meta("ShoeID")) {
				woocommerce_add_order_item_meta($item_id, 'Sneaker Name', get_the_title($item->get_meta("ShoeID")));
			}
			$item->save();
		}

		if ($atLeastOneProductNotInYorkWarehouse == "true") {
			// need to add meta saying this order needs pushed to Printful ASAP
			update_post_meta($order_id, '_printful_push_needed', "1");
		}

		//wp_mail("kody@iconicwebhq.com", "Qty", $totalItems);

		if ($rush == true && $totalItems == 1) {
			$order->update_status('completed');
		} else if ($rush == true) {
			$upgradeResults = $wpdb->get_results("UPDATE `mk_woocommerce_order_items`  SET `order_item_name` = 'Rush (Est Delivery in 3-4 Business Days)' WHERE `order_item_type` = 'shipping' AND `order_id` = '" . $order_id . "'");
		}

		// get SMS Opt in Form
		if ($_POST['_sms_opt_in'] == 1) {
			update_post_meta($order_id, '_sms_opt_in', $_POST['_sms_opt_in']);
			//$order->add_order_note("Confirmed! You'll receive tracking updates from us at this number. Please add us to your contacts to stay updated.", true, true);
		}

		$result = wc_clear_cart_after_payment();
	}

	function express_upgrade($order_id)
	{
		global $wpdb;
		if (!$order_id)
			return;

		// Get an instance of the WC_Order object
		$order = wc_get_order($order_id);
		// Loop through order items
		foreach ($order->get_items() as $item_id => $item) {
			if ($item->get_meta("Order Number")) {
				$postmeta_table = $wpdb->prefix . 'postmeta';
				$orderNumber = $wpdb->get_results("SELECT `post_id` FROM `$postmeta_table` WHERE `meta_key` = '_order_number' AND `meta_value` =  " . $item->get_meta("Order Number"))[0]->post_id;
				$wpdb->get_results("UPDATE `mk_woocommerce_order_items` SET `order_item_name` = 'Rush (Est Delivery in 3-4 Business Days)' WHERE `order_id` = " . $orderNumber . " AND `order_item_type` = 'shipping'");

				// If you don't have the WC_Order object (from a dynamic $order_id)
				$order = wc_get_order($orderNumber);

				// The text for the note
				$note = __("Order Upgraded to Express / Rush Processing from another order by the customer. --> Upgrade WooCommerce Order #" . get_post_meta($order_id, '_order_number', true));

				// Add the note
				$order->add_order_note($note);
			}
		}

		$result = wc_clear_cart_after_payment();
	}

	function safeCheckout()
	{
		echo "<img  onclick=\"jQuery('#place_order').click();\" src='/wp-content/themes/flatsome-child/safe_checkout1.png' style='width:100%;' />";
	}

	function moneyBackGuarentee()
	{
		echo "<img src='/wp-content/uploads/2021/02/14-Day-No-Questions-Asked-Money-Back-Guarantee.jpg' style='width:100%;' />";
	}

	/**
	 * @snippet       Disallow Shipping to PO BOX
	 * @how-to        Get CustomizeWoo.com FREE
	 * @author        Rodolfo Melogli
	 * @testedwith    WooCommerce 3.8
	 * @donate $9     https://businessbloomer.com/bloomer-armada/
	 */
	function bbloomer_disallow_pobox_shipping($posted)
	{
		$address = (isset($posted['shipping_address_1'])) ? $posted['shipping_address_1'] : $posted['billing_address_1'];
		$address2 = (isset($posted['shipping_address_2'])) ? $posted['shipping_address_2'] : $posted['billing_address_2'];
		$replace = array(" ", ".", ",");
		$address = strtolower(str_replace($replace, '', $address));
		$address2 = strtolower(str_replace($replace, '', $address2));
		if (strstr($address, 'pobox') || strstr($address2, 'pobox')) {
			wc_add_notice('Sorry, we do not ship to PO BOX addresses.', 'error');
		}
	}

	/**
	 * Add WooCommerce additional Checkbox checkout field
	 */
	function bt_add_checkout_checkbox()
	{

		woocommerce_form_field('_sms_opt_in', array( // CSS ID
			'type'          => 'checkbox',
			'class'         => array('form-row mycheckbox'), // CSS Class
			'label_class'   => array('woocommerce-form__label woocommerce-form__label-for-checkbox checkbox'),
			'input_class'   => array('woocommerce-form__input woocommerce-form__input-checkbox input-checkbox'),
			'required'      => false, // Mandatory or Optional
			'label'         => 'Text me Tracking Updates & Periodic Coupons', // Label and Link
		), '1');
	}

	// Adding 'Send Customer Return/Exchange Label' to action Metabox dropdown in admin order pages
	function filter_wc_add_send_return_label($actions)
	{
		$actions['send_return_label'] = __('Purchase & Send Return/Exchange Label', 'woocommerce');

		return $actions;
	}

	function send_return_label_via_api__cb($request)
	{
		$order_id = $request->get_param('order_id');

		if (empty($order_id)) {
			return 'order_id cannot be NULL';
		}

		$order = wc_get_order($order_id);

		$_GET['testing'] = 'yes';

		$this->trigger_action_send_return_label($order);

		return 'done';
	}

	// Trigger the email notification on 'Send Expedited email' action (composite hook)
	function trigger_action_send_return_label($order)
	{

		$phone = $order->get_billing_phone();

		$shipFrom = array(
			'name' => 'Customer',
			'phone' => $order->get_billing_phone(),
			'address_line1' => $order->get_shipping_address_1(),
			'address_line2' => $order->get_shipping_address_2(),
			'city_locality' => $order->get_shipping_city(),
			'state_province' => $order->get_shipping_state(),
			'postal_code' => $order->get_shipping_postcode(),
			'country_code' => $order->get_shipping_country(),
			'address_residential_indicator' => 'yes',
		);

		// Get weight

		$total_weight = 0;
		foreach ($order->get_items() as $item_id => $product_item) {
			$quantity = $product_item->get_quantity(); // get quantity
			$product = $product_item->get_product(); // get the WC_Product object
			$product_weight = $product->get_weight(); // get the product weight
			// Add the line item weight to the total weight calculation
			$total_weight += floatval($product_weight * $quantity);
		}

		if ($total_weight < 16) {
			$service = "usps_first_class_mail";
		} else {
			$service = "usps_priority_mail";
		}

		$curl = curl_init();

		// The country/state
		$store_raw_country = get_option('woocommerce_default_country');

		// Split the country/state
		$split_country = explode(":", $store_raw_country);

		// Country and state separated:
		$store_country = $split_country[0];
		$store_state   = $split_country[1];

		$data = array(
			'shipment' =>
			array(
				'service_code' => $service,
				'ship_to' =>
				array(
					'name' => 'Returns Department',
					'address_line1' => get_option('woocommerce_store_address'),
					'address_line2' => get_option('woocommerce_store_address_2'),
					'city_locality' => get_option('woocommerce_store_city'),
					'state_province' => $store_state,
					'postal_code' => get_option('woocommerce_store_postcode'),
					'country_code' => 'US',
					'address_residential_indicator' => 'yes',
				),
				'ship_from' => $shipFrom,
				'packages' =>
				array(
					0 =>
					array(
						'weight' =>
						array(
							'value' => $total_weight,
							'unit' => 'ounce',
						),
					),
				),
			),
		);

		$returnAddress = $data['shipment']['ship_to']['address_line1'] . " " . $data['shipment']['ship_to']['address_line2'] . " " . $data['shipment']['ship_to']['city_locality'] . ", " . $data['shipment']['ship_to']['state_province'] . " " . $data['shipment']['ship_to']['postal_code'] . " " . $data['shipment']['ship_to']['country_code'];

		$shipengine_service = new DPDV_ShipEngine_Service();
		$response = $shipengine_service->send_return_label($data);

		echo $response;

		$resp = json_decode($response, 1);
		if ($resp['label_download']['pdf']) {
			$labelLink = $resp['label_download']['pdf'];
			$order->add_order_note("Order Return/Exchange Label Purchased & Emailed to Customer", true, true);
			$order->add_order_note("<a href='$labelLink' target='_blank'>Click here to view, print or access the return/exchange label</a>", false, true);
			update_post_meta($order->get_id(), 'return_label', $resp['label_download']['pdf']);
			$site_url = get_site_url();

			$dpdv_options = get_option('dpdv_options');
			$brand_name = $dpdv_options['brand_name_for_google_merchant'];

			if ($_GET['testing'] == 'yes') {
				wp_mail(
					'shahzaddev125@gmail.com',
					"Return/Exchange Label From " . $brand_name . " - #" . get_post_meta($order->get_id(), "_order_number", true),
					"Hello,<br/><br/>Please click the link below to view and print your return label.<br/><br/>$labelLink<br/><br/><b><u>INSTRUCTIONS:</u></b><ul><li>NOTE: Please make sure you put your \"RMA\" # on the label or Inside the package. - You can get an RMA # by going to $site_url/solutions - You must fill out this form first.</li><li>Mail ASAP, this label will only work for 14 days from the date it was issued. Additionally, your exchange or reprint will not go out until we receive this package.</li><li>Mail to: $returnAddress</li></ul>Please email " . get_option('dpdv_support_email') . " if you have any issues or visit $site_url/chat.<br/><br/>Sincerly,<br/>Match Kicks Team",
					"Content-Type: text/html\r\n"
				);
			} else {
				wp_mail(
					get_option('dpdv_info_email') . "," . $order->get_billing_email(),
					"Return/Exchange Label From " . $brand_name . " - #" . get_post_meta($order->get_id(), "_order_number", true),
					"Hello,<br/><br/>Please click the link below to view and print your return label.<br/><br/>$labelLink<br/><br/><b><u>INSTRUCTIONS:</u></b><ul><li>NOTE: Please make sure you put your \"RMA\" # on the label or Inside the package. - You can get an RMA # by going to $site_url/solutions - You must fill out this form first.</li><li>Mail ASAP, this label will only work for 14 days from the date it was issued. Additionally, your exchange or reprint will not go out until we receive this package.</li><li>Mail to: $returnAddress</li></ul>Please email " . get_option('dpdv_support_email') . " if you have any issues or visit $site_url/chat.<br/><br/>Sincerly,<br/>Match Kicks Team",
					"Content-Type: text/html\r\n"
				);
			}
		} else {
			$order->add_order_note("Failed to create return/exchange label. --> $response", false, true);
		}
	}

	function send_sms_order_updates($comment)
	{
		// this will send a customer an SMS from our toll free number when a new "customer note" is added to their order.

		$email = get_post_meta($comment['order_id'], '_billing_email', true);

		$order_number = get_post_meta($comment['order_id'], '_order_number', true);

		mk_email_customer($email, mb_substr($comment['customer_note'], 0, 30) . "... - Order #" . $order_number, $comment['customer_note']);

		if (get_post_meta($comment['order_id'], '_sms_opt_in', true) == 1) {

			$phone = get_post_meta($comment['order_id'], '_billing_phone', true);

			mk_text_customer($phone, " #$order_number - " . $comment['customer_note']);
		}
	}

	function sms_opt_in_editable_order_meta_general($order)
	{  ?>
		<br class="clear" />
		<h4>Text the Customer Order Updates? <a href="#" class="edit_address">Edit</a></h4>
		<?php
		/*
		 * get all the meta data values we need
		 */
		$sms_opt_in = get_post_meta($order->get_id(), '_sms_opt_in', true);
		?>
		<strong><?php echo $sms_opt_in ? 'Yes' : 'No' ?></strong>

		<div class="edit_address">
			<?php

			woocommerce_wp_radio(array(
				'id' => 'sms_opt_in',
				'label' => 'SMS Opt In',
				'value' => $sms_opt_in,
				'description' => 'Customers can select this at checkout. Admins can turn it on or off if they request.',
				'options' => array(
					'' => 'No',
					'1' => 'Yes'
				),
				'style' => 'width:16px', // required for checkboxes and radio buttons
				'wrapper_class' => 'form-field-wide' // always add this class
			));

			?>
		</div>
	<?php
	}
	function sms_opt_in_save_general_details($ord_id)
	{
		update_post_meta($ord_id, '_sms_opt_in', wc_clean($_POST['sms_opt_in']));
		// wc_clean() and wc_sanitize_textarea() are WooCommerce sanitization functions
	}

	function set_most_recent_order_id($order_id)
	{
		if (!$order_id) {
			return;
		}

		$order = wc_get_order($order_id);

		if (!mkdv_is_addon_order($order)) {
			echo "MKDV --> most_recent_order_id = THIS.<br/><br/>";
			setcookie('most_recent_order_id', $order_id, time() + (2 * 60 * 60), "/");

			$user_id = get_current_user_id();
			if (!get_user_meta($user_id, 'most_recent_order_id')) {
				add_user_meta($user_id, 'most_recent_order_id', $order_id);
			} else {
				update_user_meta($user_id, 'most_recent_order_id', $order_id);
			}
			return;
		}
	}

	/**
	 * If the current order's shipping option is "Add to Previous Order", then add the current order's items to the most_recent_order
	 */
	function mkdv_add_to_previous_order($order_id)
	{
		// NOTE: To disable this option for customers, uncomment the following lines
		// if (!current_user_can("administrator")) {
		//     return;
		// }

		$current_user = wp_get_current_user();
		if ($current_user->user_login != "designsvalley") {
			WC()->cart->empty_cart();
		}

		if (!$order_id) {
			return;
		}

		$order = wc_get_order($order_id);

		if (!mkdv_is_addon_order($order, true)) {
			// echo "MKDV --> This is NOT an addon order.<br/><br/>";
			return;
		}
		// echo "MKDV --> This is an addon order.<br/><br/>";

		$prev_order_id = mkdv_get_most_recent_order_id();
		if (!$prev_order_id) {
			echo "MKDV --> most_recent_order_id = NONE.<br/><br/>";
			return;
		}
		$prev_order = wc_get_order($prev_order_id);


		echo "MKDV --> most_recent_order_id = " . $prev_order_id . ". Going to merge new order into it.<br/><br/>";

		$order->update_status('wc-merged');

		$order->add_order_note(__("Merged into order with Number = " . get_post_meta($prev_order_id, "_order_number", true)));
		$prev_order->add_order_note(__("Order with Number = " . get_post_meta($prev_order_id, "_order_number", true) . " has been merged into this order."));

		$current_order_items = $order->get_items();

		global $wpdb;
		foreach ($current_order_items as $key => $item) {
			$results = $wpdb->get_results(
				"UPDATE {$wpdb->prefix}woocommerce_order_items 
					SET order_id = {$prev_order_id} 
					WHERE order_item_id = {$item->get_id()} AND order_id = {$order_id} LIMIT 1",
				OBJECT
			);
		}

		$this->trigger_action_send_special_promo($order);
	}

	// Trigger the email notification on 'Send Expedited email' action (composite hook)
	function trigger_action_send_special_promo($order)
	{
		$order_number = get_post_meta($order->get_id(), '_order_number', true);

		if ($order->get_status() != "merged") {
			$order->add_order_note("Order status is not merged so we can't generate a special coupon for this customer. The order is actually: " . $order->get_status());
			return;
		}

		if (get_post_meta($order->get_id(), 'special_promo_sent', true) == "1") {
			$order->add_order_note("Special promo already generated and sent.");
			return;
		}

		// get all coupons in cart
		if ($order->get_items('coupon')) {
			$order->add_order_note("This 'Add-On' Order already has a coupon attached to it, so let's not give them another coupon.");
			return;
		}

		/**
		 * Create a coupon programatically
		 */


		$amount = ceil(rand(20, 30) / 5) * 5; // Amount

		$coupon_code = "M" . $order_number . "K" . $amount; // Code

		$discount_type = 'percent'; // Type: fixed_cart, percent, fixed_product, percent_product

		$coupon = array(
			'post_title' => $coupon_code,
			'post_excerpt' => 'Special promo generated for this customer to be used within the next 45 minutes only. It was issued/unlocked by adding another order on via our WooCommerce Automation.',
			'post_status' => 'publish',
			'post_author' => 1,
			'post_type' => 'shop_coupon'
		);

		$new_coupon_id = wp_insert_post($coupon);

		// Add meta
		update_post_meta($new_coupon_id, 'discount_type', $discount_type);
		update_post_meta($new_coupon_id, 'coupon_amount', $amount);
		update_post_meta($new_coupon_id, 'individual_use', 'yes');
		update_post_meta($new_coupon_id, 'product_ids', '');
		update_post_meta($new_coupon_id, 'customer_email', array($order->get_billing_email()));
		update_post_meta($new_coupon_id, 'usage_limit', '1');
		update_post_meta($new_coupon_id, 'expiry_date', date("Y-m-d", strtotime("tomorrow")));
		update_post_meta($new_coupon_id, 'apply_before_tax', 'yes');
		update_post_meta($new_coupon_id, 'free_shipping', 'no');

		$order->add_order_note($order->get_billing_first_name() . ", want $amount% OFF? U earned it with this add-on order. Coupon Code: $coupon_code. Exp. in 60 mins from now.", true, true);
		update_post_meta($order->get_id(), 'special_promo_sent', "1");
		return;
	}

	/**
	 * Return policy checkbox on checkout page
	 */
	function mkdv_add_checkout_return_policy_checkbox()
	{

		woocommerce_form_field('checkout_return_policy_checkbox', array( // CSS ID
			'type'          => 'checkbox',
			'class'         => array('form-row mycheckbox'), // CSS Class
			'label_class'   => array('woocommerce-form__label woocommerce-form__label-for-checkbox checkbox'),
			'input_class'   => array('woocommerce-form__input woocommerce-form__input-checkbox input-checkbox'),
			'required'      => true, // Mandatory or Optional
			'label'         => 'I agree with <a href="https://matchkicks.tawk.help/article/returns-refunds-policy" target="_blank" rel="noopener">Match Kicks Policies</a>', // Label and Link
		), 1);
	}

	function mkdv_add_checkout_return_policy_checkbox_warning()
	{
		if (!(int) isset($_POST['checkout_return_policy_checkbox'])) {
			wc_add_notice(__('Please acknowledge the Return Policy'), 'error');
		}
	}



	/**
	 * @snippet       WooCommerce add text to the thank you page
	 * @how-to        Get CustomizeWoo.com FREE
	 * @author        Rodolfo Melogli
	 * @testedwith    WooCommerce 4.5
	 */


	function mk_run_scripts_at_thank_you()
	{
	?>
		<script>
			jQuery.get(`/scripts/GoogleDriveUpload/download-design.php`);
			jQuery.get(`/scripts/send-reccomended-products.php`);
		</script>
	<?php
	}


	function add_discout_to_checkout()
	{
		// Set coupon code
		$coupon_code = WC()->session->get('coupon_code');
		if (!empty($coupon_code) && !WC()->cart->has_discount($coupon_code)) {
			WC()->cart->add_discount($coupon_code); // apply the coupon discount
			WC()->session->__unset('coupon_code'); // remove coupon code from session
		}
	}

	// Hook before calculate fees
	function mkdv_woocommerce_calculate_fees()
	{
		// ----------------------------------------
		// 1HALFOFF offer
		// ----------------------------------------
		$num_items = WC()->cart->cart_contents_count;
		$num_discounts = (int)($num_items / 2);
		$discount = 0;

		if ($num_items < 2) {
			return;
		}

		for ($i = 0; $i < $num_discounts; $i++) {
			$discount += (WC()->cart->subtotal / $num_items) * 0.5;
		}

		// WC()->cart->add_fee('1HALFOFF discount automatically applied.', -$discount);

		// ----------------------------------------
		// Smart-Buy Add-on dynamic pricing
		// ----------------------------------------
		$is_smart_by_enabled = false;
		$smart_buy_per_item_price = 3.99;
		$fees = WC()->cart->get_fees();
		foreach ($fees as $key => $fee) {
			if ($fees[$key]->id === __('smart-buy-route-protection')) {
				$is_smart_by_enabled = true;
				// $smart_buy_per_item_price = $fees[$key]->total;
				unset($fees[$key]);
			}
		}
		WC()->cart->fees_api()->set_fees($fees);

		$current_user = wp_get_current_user();
		if ($current_user->user_login == "designsvalley") {
			// $is_smart_by_enabled = false;
		}
		if ($is_smart_by_enabled) {
			$smart_by_cost = WC()->cart->cart_contents_count * $smart_buy_per_item_price;
			WC()->cart->add_fee(__("Smart Buy & Route Protection"), $smart_by_cost);
		}
	}

	function remove_smart_buy_add_on()
	{
		$fees = WC()->cart->get_fees();
		foreach ($fees as $key => $fee) {
			if ($fees[$key]->name === __('Smart Buy Protection: Per Item')) {
				$is_smart_by_enabled = true;
				// $smart_buy_per_item_price = $fees[$key]->total;
				unset($fees[$key]);
			}
		}
		WC()->cart->fees_api()->set_fees($fees);
	}

	function customMatch()
	{
		echo '<p onclick="jQuery(\'#place_order\').click();" class="alt-font" style="font-size: 100%;text-align:center;width:100%;margin-top:-20px;">Your perfect match is <span style="color: #1458a6;">just a click</span> away!</span></p>';
	}


	function mk_human()
	{
		echo '<br/><br/><h3>We are Match Kicks. This is Who We Are.</h3><iframe style="width:100%;height:400px;border:0;" src="https://www.youtube.com/embed/-d63Cm5PNOE" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
	}

	function mk_save_brands($post_ID, $post, $update)
	{
		if (empty($_POST['acf']['field_60f610d87fb53'])) {
			$_POST['acf']['field_60f610d87fb53'] = md5(rand());
		}
		//echo "<pre>" . print_r($_POST,1) . "</pre>"; die;
	}
	function mk_save_colors($post_ID, $post, $update)
	{

		$fields['color'] = $_POST['acf']['field_5f3342eed2301'];
		$fields['type'] = $_POST['acf']['field_5f1e1be8cf489'];
		$fields['texture'] = $_POST['acf']['field_5f334311d2302'];

		if ($_POST['acf']) {
			if ($fields['type'] == 'color') {
				wp_delete_attachment(get_post_thumbnail_id($post_ID));
				$image = file_get_contents(get_site_url() . "/scripts/create-color-image.php?c=" . substr($fields['color'], 1));
				$image = file_put_contents("../scripts/colors/" . substr($fields['color'], 1) . ".jpg", $image);
				$swatch = media_sideload_image(get_site_url() . "/scripts/colors/" . substr($fields['color'], 1) . ".jpg", $post_ID, null, 'id');
				set_post_thumbnail($post_ID, $swatch);
			} else {
				if (get_post_thumbnail_id($post_ID) != $fields['texture'])
					wp_delete_attachment(get_post_thumbnail_id($post_ID));
				set_post_thumbnail($post_ID, $fields['texture']);
			}
		} else {
			$type = get_post_meta($post_ID, 'type', true);
			if ($type == 'color') {
				$fields['color'] = get_post_meta($post_ID, 'color', true);
				wp_delete_attachment(get_post_thumbnail_id($post_ID));
				$image = file_get_contents(get_site_url() . "/scripts/create-color-image.php?c=" . substr($fields['color'], 1));
				$image = file_put_contents("../scripts/colors/" . substr($fields['color'], 1) . ".jpg", $image);
				$swatch = media_sideload_image(get_site_url() . "/scripts/colors/" . substr($fields['color'], 1) . ".jpg", $post_ID, null, 'id');
				set_post_thumbnail($post_ID, $swatch);
			}
		}
	}

	function mk_convert_svg_jpg($post_ID, $post, $update)
	{

		if (empty(get_post_meta($post_ID, 'hits_total', true))) {
			update_post_meta($post_ID, 'hits_total', 0);
		}

		if (empty(get_the_post_thumbnail_url($post_ID))) {
			//  wp_die("Missing a Featured Image. Please Upload your SVG file. Press the back button in your browser to go back and add it.", "Oops! You forgot to attach your design.", array("back_link" => true));
		} else {

			$image_blob = file_get_contents(str_replace(get_site_url(), "..", get_the_post_thumbnail_url($post_ID)));
			if (get_the_post_thumbnail_url($post_ID) && !empty($image_blob)) {
				$image = new Imagick();
				$image->readImageBlob($image_blob);
				$image->setImageFormat("jpeg");
				$image->resizeImage(450, 562.5, imagick::FILTER_LANCZOS, 1);
				$image->writeImage('../scripts/designs/' . $post_ID . '-sm.jpg');

				wp_delete_attachment($post_ID);
				//update_post_meta($post_ID, "featured_image_jpg", $imgID);
				//unlink("../scripts/temp/" . $post_ID . "-" . $key . ".jpg");
			} else {
				update_post_meta($post_ID, "featured_image_jpg", $_REQUEST['acf']['field_5fa750cc96be0']);
			}
		}
	}

	// Show only posts and media related to logged in author
	function query_set_only_author($wp_query)
	{
		global $current_user;
		if (is_admin() && !current_user_can('edit_others_posts')) {
			$wp_query->set('author', $current_user->ID);
			add_filter('views_edit-post', 'fix_post_counts');
			add_filter('views_upload', 'fix_media_counts');
		}
	}

	function add_index_headers()
	{
		echo '<meta name="robots" content="all"><meta name="googlebot" content="all">';
	}

	// // disable delete entirely
	function restrict_post_deletion_1($post_ID)
	{
		echo "You are not authorized to delete this.";
		exit;
	}

	function order_notes()
	{
		global $current_user;
		if (!current_user_can("administrator")) {
			echo "<style>.woocommerce-additional-fields {
display: none;
}";
		} else {
			echo "<style>.woocommerce-additional-fields:before {
content: 'ENTER IN INFORMATION ABOUT THE RE-PRINT OR OTHER ORDER NOTES TO SHOW IN SHIPSTATION HERE.';
FONT-WEIGHT: BOLD;
background: red;
color: white;
}</style>";
		}
	}

	// disable delete entirely
	function restrict_post_deletion_2($post_ID)
	{
		global $_POST;

		if ($_POST['action'] != 'woocommerce_remove_variations' && empty($_POST['menu']) && !current_user_can("administrator")) {
			wp_die("Deleting permantly is not allowed at Design Pact LLC. Additionally, this attempted action has been logged under your user id.");
		}
	}

	function post_screenshot($post_id, $post, $update)
	{
		if ($post->post_type != 'customer_videos') {
			return true;
		}

		$meta = get_post_meta($post_id);

		if ($meta['posted_to_fb'][0] != '1' && !empty($_POST['acf']['field_622a6ae158d55'])) {

			$hashtags = explode(" ", file_get_contents(get_site_url() . "/wp-content/themes/flatsome-child/instagram-hashtags.txt"));

			if (!empty($_POST['acf']['field_622bcc252550b']) && $_POST['acf']['field_622bcc252550b'] != 0) {
				global $wpdb;
				$postmeta_table = $wpdb->prefix . 'postmeta';
				$order_id = $wpdb->get_results("SELECT `post_id` FROM `$postmeta_table` WHERE `meta_key` = '_order_number' AND `meta_value` =  " . $_POST['acf']['field_622bcc252550b'])[0]->post_id;
				$order = wc_get_order($order_id);
				$order_data = $order->get_data(); // The Order data


				$message = "We hope you love your match " . $order_data['shipping']['first_name'] . "!";
			}

			shuffle($hashtags);

			for ($i = 0; $i < 20; $i++) {
				$finalHash[$i] = $hashtags[$i];
			}

			$finalHashtags = implode(" ", $finalHash);

			$curl = curl_init();

			curl_setopt_array($curl, array(
				CURLOPT_URL => 'https://hooks.zapier.com/hooks/catch/4780817/bs2vkpd',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_POSTFIELDS => json_encode(array(
					"title" => "New Customer Unboxing Video",
					"description" => "$message Thank you for all of your continued support! #matchkicks " . $finalHashtags,
					"video" => "https://" . wp_get_attachment_url($_POST['acf']['field_622a6ae158d55'])
				)),
				CURLOPT_HTTPHEADER => array(
					'Content-Type: application/json'
				),
			));

			$response = curl_exec($curl);

			curl_close($curl);
			echo $response;

			update_post_meta($post_id, 'posted_to_fb', "1");
		}
	}
	// Add the data to the custom columns for the book post type:
	function custom_design_column($column, $post_id)
	{
		switch ($column) {

			case 'design_preview':
				$track = array("hits_day", "hits_week", "hits_month");
				foreach ($track as $trackItem) {
					$totalHits[$trackItem] = get_option($trackItem . "_design");
				}

				$trend = '';
				foreach ($track as $trackItem) {
					$hits = get_post_meta($post_id, $trackItem, true);
					if (empty($hits)) {
						$hits = 0;
					}
					if (intval($totalHits[$trackItem]) == 0) {
						$totalHits[$trackItem] = 1;
					}
					$calc = round($hits / $totalHits[$trackItem] * 1000, 2);
					$trend .= "<span title='Popularity in $trackItem'>$calc%</span> ";
				}

				echo "<a href='/scripts/get-svg-admin.php?p=" . $post_id . "' target='_blank' title='Click here to view the raw .SVG file of this design.'><img style='max-width:30%' src='/scripts/designs/" . $post_id . "-sm.jpg?v=1'/></a><br/><span style='font-weight:bold'>Trend: $trend</span>";
				break;
		}
	}

	function sneakerShareImg()
	{
		global $post;
		if (!is_object($post)) {
			return;
		}
		if ($post->post_type == 'sneaker') {
			echo '<meta property="og:image" content="https://cdn1-183fe.kxcdn.com/scripts/cache/social-media/' . $post->ID . '.jpg?date=' . urlencode($post->post_modified) . '">
	<meta name="twitter:image" content="https://cdn1-183fe.kxcdn.com/scripts/cache/social-media/' . $post->ID . '.jpg?date=' . urlencode($post->post_modified) . '" /><meta property="og:description" content="The best sneaker matching tees for ' . $post->post_title . '.">';
		}
	}

	function postToFacebookFunctionTrigger()
	{
		if (isset($_GET['postToFacebook'])) {
			$this->postToFacebookFunction();
		}
	}

	function postToFacebookFunction()
	{
		$q2 = array(
			'post_type' => 'sneaker',
			'posts_per_page' => 1,
			'meta_key' => 'hits_total',
			'orderby' => 'meta_value_num',
			'post_status' => 'publish',
			'order' => 'DESC',
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key' => 'posted_to_facebook',
					'compare' => 'NOT EXISTS' // doesn't work
				),
				array(
					'key' => 'posted_to_facebook',
					'value' => '1',
					'compare' => '!='
				)
			)
		);
		$loop = new WP_Query($q2);
		$post = $loop->posts[0];
		if (get_post_meta($post->ID, 'posted_to_facebook', true) != 1 && get_post_meta($post->ID, 'colors', true)) {
			if (get_post_meta($post->ID, 'social_image_generated', true) != 1) {
				echo 'https://cdn1-183fe.kxcdn.com/scripts/cache/generate-social-images.php?id=' . $post->ID . '?date=' . urlencode($post->post_modified);
				$image = file_get_contents('https://cdn1-183fe.kxcdn.com/scripts/cache/generate-social-images.php?id=' . $post->ID . '&date=' . urlencode($post->post_modified));
				if (file_get_contents('https://cdn1-183fe.kxcdn.com/scripts/cache/social-media/' . $post->ID . '.jpg?date=' . urlencode($post->post_modified))) {
					update_post_meta($post->ID, 'social_image_generated', '1');
				}
				wp_die("Generated the image this time. " . 'https://cdn1-183fe.kxcdn.com/scripts/cache/social-media/' . $post->ID . '.jpg?date=' . urlencode($post->post_modified));
			}


			$terms = get_the_terms($post->ID, 'sneaker_category');
			$data = json_decode(json_encode($post), 1);

			$data['link'] = get_permalink($post->ID);

			$hashtags = "";
			foreach ($terms as $term) {
				$hashtags .= " #" . $term->name;
			}
			$words = explode(" ", $post->post_title);

			$hashtags .= " #" . $words[0] . $words[1] . "'s";

			foreach ($words as $word) {
				$hashtags .= " #" . strtolower(preg_replace("/[^A-Za-z0-9]/", "", $word));
			}

			$hashtags .= " #matchkicks #matchthekicks #sneakermatchingtees #sneakermatch #kicksoftheday #teesforsneakers";

			$data['hashtags'] = $hashtags;
			$dpdv_options = get_option('dpdv_options');
			$brand_name = $dpdv_options['brand_name_for_google_merchant'];
			$randomPost = array(
				"Just Released " . $post->post_title . " official sneaker matching T-Shirt apparel. Shop Now.",
				"Newest Sneaker Matching T-Shirt Collection - " . $post->post_title,
				"Shop Match Kicks for Official Sneaker Matching Apparel for " . $post->post_title,
				"We have just released the latest gear to match with " . $post->post_title,
				"SHOP NOW! The best and newest collection to match with your" . $post->post_title . " sneakers.",
				"Are you ready for the hotest sneaker release of the year? Match your sneakers with Official Sneaker Matching T-Shirts for " . $post->post_title,
				"#1-Rated High Quality Sneaker Matching T-Shirts to Match with Your " . $post->post_title . " sneakers.",
				$brand_name . " has the best quality sneaker match tees to match with " . $post->post_title
			);


			$data['postContent'] = $randomPost[rand(0, 6)];
			$data['socialImage'] = 'https://cdn1-183fe.kxcdn.com/scripts/cache/social-media/' . $post->ID . '.jpg?date=' . urlencode($post->post_modified);
			$url = "https://cdn1-183fe.kxcdn.com/scripts/facebook/facebook.php";

			$out['content'] = $data['postContent'] . " " . $data['hashtags'];
			$out['link'] = $data['link'];

			//echo "<pre>";
			//print_r($out);

			$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt(
				$curl,
				CURLOPT_HTTPHEADER,
				array("Content-type: application/json")
			);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($out));
			$response   = curl_exec($curl);

			update_post_meta($post->ID, 'posted_to_facebook', '1');
		}
		wp_die();
	}

	/**
	 * Action that fires when an entry is saved to the database.
	 *
	 * @link  https://wpforms.com/developers/wpforms_process_entry_save/
	 *
	 * @param array  $fields    Sanitized entry field. values/properties.
	 * @param array  $entry     Original $_POST global.
	 * @param int    $form_id   Form ID. 
	 * @param array  $form_data Form data and settings.
	 */
	function wpf_dev_process_entry_save_sneaker_requests()
	{
		global $_POST;

		if ($_POST['wpforms']['id'] != 93403) {
			return;
		}

		if (!empty($_POST['wpforms']['fields'][7]) && strpos($_POST['wpforms']['fields'][7], "wpforms_submit=true") === false) {

			if (!empty($_POST['wpforms']['fields'][4]))
				mk_text_customer($_POST['wpforms']['fields'][4], "Your requested custom sneaker match can be found here: " . $_POST['wpforms']['fields'][7]);

			if (!empty($_POST['wpforms']['fields'][5]))
				mk_email_customer($_POST['wpforms']['fields'][5], $_POST['wpforms']['fields'][1] . " - Your custom sneaker match",  "Great News! Someone on our team just added your sneaker. Please check it out at the link below and reply if you have any questions.<br/><br/>Your requested custom sneaker match can be found here: " . $_POST['wpforms']['fields'][7]);

			$_POST['wpforms']['fields'][7] .= "?wpforms_submit=true";
		}
	}

	// Add the data to the custom columns for the book post type:
	function custom_colors_column($column, $post_id)
	{
		switch ($column) {
			case 'colors_preview':
				if (empty(get_the_post_thumbnail_url($post_id))) {
					echo "Missing";
				} else {
					echo "<img style='width:50px;height:50px;' src='" . get_the_post_thumbnail_url($post_id) . "'/>";
				}
				break;
		}
	}

	// Add the data to the custom columns for the book post type:
	function custom_sneaker_column($column, $post_id)
	{
		switch ($column) {

			case 'sneaker_preview':
				$track = array("hits_day", "hits_week", "hits_month");
				foreach ($track as $trackItem) {
					$totalHits[$trackItem] = get_option($trackItem . "_sneaker");
				}

				$trend = '';
				foreach ($track as $trackItem) {
					$hits = get_post_meta($post_id, $trackItem, true);
					if (empty($hits)) {
						$hits = 0;
					}
					if (intval($totalHits[$trackItem]) == 0) {
						$totalHits[$trackItem] = 1;
					}
					$calc = round($hits / $totalHits[$trackItem] * 1000, 2);
					$trend .= "<span title='Popularity in $trackItem'>$calc%</span> ";
				}

				$thumbnail = get_the_post_thumbnail_URL($post_id);
				if (empty($thumbnail)) {
					$thumbnail = get_post_meta($post_id, 'image_link', true);
				}
				echo "<a href='#' target='_blank'><img style='max-width:30%' src='" . $thumbnail . "?v=1'/></a><br/><span style='font-weight:bold'>Trend: $trend</span>";
				break;
		}
	}

	function get_custom_coupon_code_to_session()
	{
		if (isset($_GET['coupon_code'])) {
			// Ensure that customer session is started
			if (isset(WC()->session) && !WC()->session->has_session())
				WC()->session->set_customer_session_cookie(true);

			// Check and register coupon code in a custom session variable
			$coupon_code = WC()->session->get('coupon_code');
			if (empty($coupon_code)) {
				$coupon_code = esc_attr($_GET['coupon_code']);
				WC()->session->set('coupon_code', $coupon_code); // Set the coupon code in session
			}
		}
	}

	function mk_approve_refunds()
	{
		global $post, $_GET;

	?>
		<script>
			jQuery(".add-line-item").remove();
			jQuery(".add-coupon").remove();
			jQuery(".remove-coupon").remove();
			jQuery(".calculate-action").remove();
			jQuery(".add-gift-card").remove();
			jQuery(".merge-order").remove();
		</script>
		<?php
		if (1 == 1) {
			// comment this if (1==1) to revoke agents access to refund (without approval)
		} else if (!current_user_can("administrator") || 1 == 2) {
			if (get_post_meta($post->ID, 'admin_refund_approved', true) == '1') {
		?>
			<?php
			} else {
			?>
				<script>
					jQuery(".refund-items").remove();
					jQuery(".add-items").append('<button type="button" class="button request-refund-items">Request Refund Approval</button>');

					jQuery(document).on("click", ".request-refund-items", function() {
						window.open("/a-order-refund-approval-form/?wpf113198_1=<?= get_post_meta($post->ID, '_order_number', true); ?>&wpf113198_4=<?= $post->ID; ?>");
					});
				</script>
			<?php
			}
		} else if (get_post_meta($post->ID, 'admin_refund_approved', true) != 1) {
			?>
			<script>
				jQuery(".add-items").append('<button type="button" class="button admin-allow-refund">Allow Agents to Refund Order</button>');

				jQuery(document).on("click", ".admin-allow-refund", function() {
					jQuery.get("post.php?post=<?= $post->ID; ?>&action=edit&allow-refunds=1");
					alert("Success! Agents can now refund this order.");
				});
			</script>
		<?php
		} else {
		?>
			<script>
				jQuery(".add-items").append('<button type="button" class="button admin-allow-refund">Disallow Agents to Refund Order</button>');

				jQuery(document).on("click", ".admin-allow-refund", function() {
					jQuery.get("post.php?post=<?= $post->ID; ?>&action=edit&allow-refunds=0");
					alert("Success! Agents can no longer refund.");
				});
			</script>
<?php
		}
		if (isset($_GET['allow-refunds']) && current_user_can("administrator")) {
			update_post_meta($post->ID, 'admin_refund_approved', $_GET['allow-refunds']);
		}

		//echo "<pre>" . print_r($post,1);
	}

	function my_action()
	{
		$value1 = $_POST['orderNum'];
		$value2 = $_POST['itemId'];

		$item = new WC_Order_Item_Product($value2);

		echo $item;
		wp_die();
	}

	/**
	 * my_update_item
	 * 
	 * Updates order item
	 */
	function my_update_item()
	{
		$value1 = $_POST['orderNum'];
		$value2 = $_POST['itemId'];
		$newDesign = $_POST['newDesign'];
		$shirtSize = $_POST["shirtSize"];
		$shirtColor = $_POST["shirtColor"];
		$shoeDesign = $_POST["shoeDesign"];
		$extraData = $_POST["extraData"];
		$shoeID = $_POST["shoeID"];
		$designID = $_POST["designID"];
		$varId = $_POST["varID"];


		$item = new WC_Order_Item_Product($value2);

		$order = wc_get_order($value1);
		$my_item_id = $value2;
		//echo $item;
		//echo $item->get_product()->get_attributes();
		//print_r($item->get_product()->get_attributes());
		//echo $item->get_id();
		//echo $item->get_variation_id();
		//echo $item->get_total();

		$_SESSION['size'] = $shirtSize;

		wc_update_order_item_meta($my_item_id, 'pa_product-color', $shirtColor);
		wc_update_order_item_meta($my_item_id, 'pa_product-size', $shirtSize);
		wc_update_order_item_meta($my_item_id, 'pa_product-design', $newDesign);
		wc_update_order_item_meta($my_item_id, 'Shoe', $shoeDesign);
		wc_update_order_item_meta($my_item_id, 'Design', $newDesign);
		//wc_update_order_item_meta($my_item_id, 'Extra', $extraData);
		wc_update_order_item_meta($my_item_id, 'ShoeID', $shoeID);
		wc_update_order_item_meta($my_item_id, 'DesignID', $designID);


		$item->set_variation_id($varId);
		$item->save();

		wp_die();
	}


	function get_update_cart_item()
	{
		$cart_item_key = $_POST['cart_item'];

		$cart = WC()->cart->cart_contents;
		foreach ($cart as $cart_item_id => $cart_item) {

			if ($cart_item_id == $cart_item_key) {

				echo json_encode([$cart_item["custom_data"], $cart_item["variation_id"], $cart_item["variation"]]);
			}
		}


		wp_die();
	}


	function my_update_cart_item()
	{
		$cart_item_key = $_POST['itemKey'];
		$newDesign = $_POST['newDesign'];
		$shirtSize = $_POST["shirtSize"];
		$shirtColor = $_POST["shirtColor"];
		$shoeDesign = $_POST["shoeDesign"];
		$extraData = $_POST["extraData"];
		$varId = $_POST["varID"];
		$prodId = $_POST["prodID"];
		$shoeID = $_POST["shoeID"];
		$designID = $_POST["designID"];

		$_SESSION['size'] = $shirtSize;

		$cart = WC()->cart;
		$custom_data = array();
		//add new item with the data of the last one
		$cart->add_to_cart($prodId, 1, $varId, [], [
			"custom_data" => ["attribute_pa_shoe-design" => $shoeDesign, "attribute_pa_product-design" => $newDesign, "attribute_pa_shoeID" => $shoeID, "attribute_pa_designID" => $designID]
		]);

		//remove the last one using the provided key
		$cart->remove_cart_item($cart_item_key);

		wp_die();
	}


	function change_cart_item_size()
	{
		$old_cart_item_key = $_POST['old_cart_item_key'];
		$variationID = $_POST['variationID'];

		// global $wpdb;
		// $sql = "select meta_value from " . "mk_" . "usermeta where meta_key='_woocommerce_persistent_cart_1'";
		// $array = $wpdb->get_results($sql);
		// //print_r($array);
		// $data = $array[0]->meta_value;
		// $de = unserialize($data);
		// print_r($de);
		// die();
		foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
			if ($old_cart_item_key == $cart_item_key) {
				$_product = $cart_item['data'];
				$meta     = $cart_item['custom_data'];
				$_POST['variationID'] = $variationID;
				$_POST["newDesign"] = $cart_item['custom_data']['attribute_pa_product-design'];
				$_POST["shoeDesign"] = $cart_item['custom_data']['attribute_pa_shoe-design'];
				$_POST["shoeID"] = $cart_item['custom_data']['attribute_pa_shoeID'];
				$_POST["designID"] = $cart_item['custom_data']['attribute_pa_designID'];
				$_POST['qty'] = $cart_item['quantity'];

				print_r([
					'cart_item_key' => $cart_item_key,
					'variationID' => $_POST['variationID'],
					'newDesign' => $_POST["newDesign"],
					'shoeDesign' => $_POST["shoeDesign"],
					'shoeID' => $_POST["shoeID"],
					'designID' => $_POST["designID"],
					'qty' => $_POST['qty']
				]);

				WC()->cart->remove_cart_item($cart_item_key);
				$this->my_custom_cart_item();
			}
		}

		die();
	}


	function my_custom_cart_item()
	{
		$variationID = $_POST["variationID"];
		$newDesign = $_POST['newDesign'];
		$shoeDesign = $_POST["shoeDesign"];
		$shoeID = $_POST["shoeID"];
		$designID = $_POST["designID"];
		// $extraData = $_POST["extraData"];
		// $shirtSize = $_POST["shirtSize"];
		// $shirtColor = $_POST["shirtColor"];


		$cart = WC()->cart;
		$custom_data = array();
		//need to replace the quotes with the encoded value
		$cleanedDesign = stripslashes($newDesign);
		$replaceQuotes = str_replace('"', "%22", $cleanedDesign);

		//echo $replaceQuotes;
		echo $shoeID;
		echo "<br/>";
		echo $designID;
		echo "<br/>";
		//echo $extraData;
		echo "<br/>";
		echo $variationID;

		$qty = 1;
		if (isset($_POST['qty'])) {
			$qty = $_POST['qty'];
		}
		//add new item with the data of the last one

		$product_id = mkdv_get_wc_main_product_id_with_variations();

		$cart->add_to_cart($product_id, $qty, $variationID, [], [
			"custom_data" => ["attribute_pa_shoe-design" => $shoeDesign, "attribute_pa_product-design" => $replaceQuotes, "attribute_pa_shoeID" => $shoeID, "attribute_pa_designID" => $designID]
		]);

		wp_die();
	}


	function my_customized_preset_cart_item()
	{
		$design = $_POST['design'];
		$shoeDesign = $_POST["shoeDesign"];
		$varId = $_POST["varID"];
		$prodId = $_POST["prodID"];
		$shoeID = $_POST["shoeID"];
		$designID = $_POST["designID"];

		// $extraData = $_POST["extraData"];
		// $shirtSize = $_POST["shirtSize"];
		// $shirtColor = $_POST["shirtColor"];

		$cart = WC()->cart;
		$custom_data = array();
		$cart->add_to_cart($prodId, 1, $varId, [], [
			"custom_data" => ["attribute_pa_shoe-design" => $shoeDesign, "attribute_pa_product-design" => $design, "attribute_pa_shoeID" => $shoeID, "attribute_pa_designID" => $designID]
		]);

		wp_die();
	}

	function change_order_item_attributes()
	{
		$itemKey = $_POST['itemKey'];
		$productType = $_POST['productType'];
		$productColor = $_POST['productColor'];
		$productSize = $_POST['productSize'];
		$newVariationID = $_POST['newVariationID'];
		$orderID = $_POST['orderID'];

		$order = wc_get_order($orderID);

		foreach ($order->get_items(array('line_item')) as $item_id => $order_item) {
			// $return = wc_update_order_item($item_id, array('variation_id' => 102043));

			// // $order_item->get_variation_id();
			// wc_update_order_item_meta($item_id, '_qty', 5);
			// wc_update_order_item_meta($item_id, 'variation_id', 44020);
			if ($itemKey == $item_id) {

				wc_update_order_item_meta($item_id, 'pa_product-type', $productType);
				wc_update_order_item_meta($item_id, 'pa_product-color', $productColor);
				wc_update_order_item_meta($item_id, 'pa_product-size', $productSize);
				wc_update_order_item_meta($item_id, '_variation_id', $newVariationID);
			}
		}
	}

	public function get_current_user()
	{
		echo json_encode(wp_get_current_user());
		die();
	}


	function woocommerce_get_item_data_001($data, $cartItem)
	{
		if (isset($cartItem['attribute_pa_product-design'])) {
			$data[] = array(
				'name' => 'My custom data',
				'value' => $cartItem['attribute_pa_product-design'],
				'name2' => 'My custom shoe',
				'value2' => $cartItem['attribute_pa_shoe-design'],
				'name3' => 'Product Extra Data',
				'value3' => $cartItem['attribute_pa_extra-data'],
				'name4' => 'Shoe ID',
				'value4' => $cartItem['attribute_pa_shoeID'],
				'name5' => 'Design ID',
				'value5' => $cartItem['attribute_pa_designID'],

			);
		}

		return $data;
	}

	function woocommerce_get_item_data_002($formatted_meta, $item)
	{
		foreach ($formatted_meta as $key => $meta) {
			if (in_array($meta->key, array('OG Image'))) {
				unset($formatted_meta[$key]);
			}
		}

		// Only on emails notifications
		$is_resend = isset($_POST['wc_order_action']) ?  wc_clean(wp_unslash($_POST['wc_order_action'])) === 'send_order_details' : false;

		if (!$is_resend && (is_admin() || is_wc_endpoint_url())) {
			return $formatted_meta;
		}

		foreach ($formatted_meta as $key => $meta) {
			if (in_array($meta->key, array('Design', 'pa_product-design', 'Shoe', 'Extra', 'pa_design-id', 'OG Image', 'ShoeID', 'DesignID'))) {
				unset($formatted_meta[$key]);
			}
		}
		return $formatted_meta;
	}

	// make design show in emails
	function filter_woocommerce_order_item_thumbnail($var, $item)
	{

		// Only for Items with a "Design" available
		if ($item->get_meta("Design")) {
			$product  = is_callable(array($item, 'get_product')) ? $item->get_product() : false;
			$image_id   = $product->get_image_id();
			$image_url = $image_id ? current(wp_get_attachment_image_src($image_id, 'woocommerce_gallery_thumbnail')) : '';

			parse_str(parse_url($item->get_meta("Design"))['query'], $designUrl);
			$image_url = getRenderedImage($item->get_meta("pa_product-color"), $designUrl['file'], $item->get_meta("Shoe"), null, $item->get_meta("pa_product-type"), json_decode($designUrl['data']));
			return "<img src='$image_url' style='max-width:150px;height:auto;' />";
		}

		return $var;
	}

	function orderFeesCustomFieldToShipStation($order_id)
	{
		global $wpdb;
		$results = $wpdb->get_results("SELECT *  FROM `mk_woocommerce_order_items` WHERE `order_item_type` = 'fee' AND `order_id` = " . $order_id);
		$out = '';
		foreach ($results as $result) {
			$out .= $result->order_item_name . " / ";
		}
		//wp_mail("kody@iconicwebhq.com", "OUT", $order_id . " - " . $out);
		return $out;
	}

	function handsome_bearded_guy_increase_variations_per_page()
	{
		return 500;
	}

	function bbloomer_redirect_checkout_add_cart()
	{
		return wc_get_checkout_url();
	}

	/**
	 * Show the shipping method "Add to Previous Order - Ship to the same address as the order I just placed." 
	 */
	function add_to_prev_order_shipping($rates)
	{

		$order_id = mkdv_get_most_recent_order_id();

		$order_date = 0;
		if (!empty($order_id)) {
			$order_date = get_the_date("U", $order_id);
		}

		// NOTE: To disable this option for customers, uncomment the following lines
		// if (!current_user_can("administrator")) {
		//     foreach ($rates as $rate_id => $rate) {
		//         if (strpos($rate->label, 'Add to Previous Order') !== false) {
		//             unset($rates[$rate_id]);
		//         }
		//     }
		//     return $rates;
		// }

		// PRODUCTION
		foreach ($rates as $rate_id => $rate) {
			if (strpos($rate->label, 'Add to Previous Order') !== false) {
				if (current_time("U") - $order_date > 60 * 60 * 2) {
					unset($rates[$rate_id]);
				}
			}
		}

		return $rates;
	}

	// Adding 'Send very special Promo for customers whos orders are merged and have the opportunity to add one more item' to action Metabox dropdown in admin order pages
	function filter_wc_add_send_special_promo($actions)
	{
		$actions['send_special_promo'] = __('Send Random 20-35% off Promo NOW', 'woocommerce');

		return $actions;
	}


	//add ACF rule
	function acf_location_rule_values_Post($choices)
	{
		$choices['product_variation'] = 'Product Variation';
		//print_r($choices);
		return $choices;
	}


	// Filter to fix the Post Author Dropdown
	function author_override($output)
	{
		global $post, $user_ID;

		// return if this isn't the theme author override dropdown
		if (!preg_match('/post_author_override/', $output)) return $output;

		// return if we've already replaced the list (end recursion)
		if (preg_match('/post_author_override_replaced/', $output)) return $output;

		// replacement call to wp_dropdown_users
		$output = wp_dropdown_users(array(
			'echo' => 0,
			'name' => 'post_author_override_replaced',
			'selected' => empty($post->ID) ? $user_ID : $post->post_author,
			'include_selected' => true
		));

		// put the original name back
		$output = preg_replace('/post_author_override_replaced/', 'post_author_override', $output);

		return $output;
	}

	function wc_make_processing_orders_editable($is_editable, $order)
	{
		if ($order->get_status() == 'processing') {
			$is_editable = true;
		}

		return $is_editable;
	}


	/**
	 * Send the entry id in webhook request.
	 *
	 * @link https://wpforms.com/developers/how-to-send-field-values-with-webhooks/
	 *
	 */

	function wpforms_webhooks_process_delivery_request_options_1($options, $webhook_data, $fields, $form_data, $entry_id)
	{
		if (
			!empty($form_data['id']) &&
			$form_data['id'] == 116487 &&
			!empty($entry_id)
		) {
			$options['entry_id'] = $entry_id;
		}
		return $options;
	}

	function enqueue_my_script()
	{
		global $current_user;
		if (!current_user_can('administrator')) {
			wp_enqueue_script('zoho-pagesense', 'https://cdn.pagesense.io/js/iconicwebhq/de14b872619549e19a0494aa49d382d9.js');
		}
	}

	// Add the custom columns to the book post type:
	function set_custom_edit_design_columns($columns)
	{
		$columns['design_preview'] = "Design";

		return $columns;
	}

	// Add the custom columns to the book post type:
	function set_custom_edit_colors_columns($columns)
	{
		$columns['colors_preview'] = "Preview";

		return $columns;
	}

	// Add the custom columns to the book post type:
	function set_custom_edit_sneaker_columns($columns)
	{
		$columns['sneaker_preview'] = "Sneaker Image";

		return $columns;
	}


	/* Add External Sitemap to Yoast Sitemap Index
* Credit: Paul https://wordpress.org/support/users/paulmighty/
* Modified by: Team Yoast
* Last Tested: Aug 25 2017 using Yoast SEO 5.3.2 on WordPress 4.8.1
*********
* This code adds two external sitemaps and must be modified before using.
* Replace http://www.example.com/external-sitemap-#.xml
with your external sitemap URL.
* Replace 2017-05-22T23:12:27+00:00
with the time and date your external sitemap was last updated.
Format: yyyy-MM-dd'T'HH:mm:ssZ
* If you have more/less sitemaps, add/remove the additional section.
*********
* Please note that changes will be applied upon next sitemap update.
* To manually refresh the sitemap, please disable and enable the sitemaps.
*/
	function add_sitemap_custom_items($sitemap_custom_items)
	{
		$sneakers = wp_count_posts('sneaker')->publish;
		$designs = wp_count_posts('design')->publish;
		$perPage = 2;
		$maps = ceil($sneakers / $perPage);
		$sitemap_custom_items = "";
		for ($i = 1; $i <= $maps; $i++) {
			$sitemap_page_url = null;
			if (str_contains(get_site_url(), 'matchkicks.')) {
				$sitemap_page_url =	get_site_url() . "/scripts/single-product-sitemap.php?perpage=$perPage&amp;page=$i&amp;version=3";
			} else {
				$sitemap_page_url =	get_site_url() . "/single-product-sitemap?perpage=$perPage&amp;page=$i&amp;version=2";
			}
			$sitemap_custom_items .= "
<sitemap>
<loc>$sitemap_page_url</loc>
</sitemap>";
		}
		/* DO NOT REMOVE ANYTHING BELOW THIS LINE
* Send the information to Yoast SEO
*/
		return $sitemap_custom_items;
	}

	function listsneakerpages()
	{
		$q = array(
			'post_type' => 'sneaker',
			'posts_per_page' => 100,
			'order' => 'ASC',
			'orderby' => 'title',
		);

		$loop = new WP_Query($q);

		foreach ($loop->posts as $post) {
			$i++;
			if ($_GET['img']) {
				$img = "<img src='" . get_the_post_thumbnail_url($post->ID, "medium") . "' style='max-height:50px;' />";
			}
			$return .= "$i. <a href='" . $post->guid . "'>$img" . $post->post_title . "</a><br/>";
		}
		return $return;
	}

	function listdesigns()
	{
		$return = '[row]';

		// Design Categories
		$terms = get_terms(array(
			'taxonomy' => 'design_category',
			'hide_empty' => true,
			'parent' => 0
		));

		foreach ($terms as $term) {
			$return .= '[col span__sm="12" margin="0px 0px -40px 0px"][title text="' . $term->name . '"][/col]';
			$term_children = get_term_children($term->term_id, 'design_category');
			foreach ($term_children as $termCID) {
				$termC = get_term($termCID);
				$return .= '[col span__sm="12" margin="0px 0px -40px 0px"][title text="' . $termC->name . '"][/col]';

				$q = array(
					'post_type' => 'design',
					'posts_per_page' => -1,
				);
				$q['tax_query'] = array(
					array(
						'taxonomy' => 'design_category',
						'field'    => 'term_id',
						'terms'    => $termC->term_id,
					),
				);

				$loop = new WP_Query($q);

				foreach ($loop->posts as $post) {
					$return .= '[col span="3" span__sm="6"]
	 <div class="box has-hover   has-hover box-shadow-2 box-default box-text-bottom">
<div class="box-image">
<a href="/shop/designer/" target="_blank" rel="noopener noreferrer"> <div class="">
<img src="/scripts/designs/' . $post->ID . '-sm.jpg?v=1"> </div>
</a> </div>
<div class="box-text text-center" style="background-color:rgb(255, 255, 255);">
<div class="box-text-inner">
' . $post->post_title . ' </div>
</div>
</div>[/col]';
				}
			}

			$q = array(
				'post_type' => 'design',
				'posts_per_page' => -1,
			);
			$q['tax_query'] = array(
				array(
					'taxonomy' => 'design_category',
					'field'    => 'term_id',
					'terms'    => $term->term_id,
				),
			);

			$loop = new WP_Query($q);

			foreach ($loop->posts as $post) {
				$return .= '[col span="3" span__sm="6"] <div class="box has-hover   has-hover box-shadow-2 box-default box-text-bottom">
<div class="box-image">
<a href="/shop/designer/" target="_blank" rel="noopener noreferrer"> <div class="">
<img src="/scripts/designs/' . $post->ID . '-sm.jpg?v=1"> </div>
</a> </div>
<div class="box-text text-center" style="background-color:rgb(255, 255, 255);">
<div class="box-text-inner">
' . $post->post_title . ' </div>
</div>
</div>[/col]';
			}
		}

		$return .= '[/row]';

		return do_shortcode($return);
	}

	function unboxing_videos_shortcode()
	{

		$videos = array(
			"https://nyc3.digitaloceanspaces.com/matchkicks-s3/2022/02/ssstiktok_1644851497.mp4",
			"https://nyc3.digitaloceanspaces.com/matchkicks-s3/2021/07/207366429_1139286016572387_6522531012857598026_n.mp4",
			"https://nyc3.digitaloceanspaces.com/matchkicks-s3/2021/06/10000000_210626824129638_5507439625440634425_n.mp4",
			"https://nyc3.digitaloceanspaces.com/matchkicks-s3/2021/06/MatchKicks-unboxing.mp4",
			"https://nyc3.digitaloceanspaces.com/matchkicks-s3/2022/02/matchkicks-sneakermatchtee-sneakerhead-https___matchkicks.com_.mp4",
			"https://nyc3.digitaloceanspaces.com/matchkicks-s3/2022/02/10000000_382236486786874_2085786632858027055_n.mp4",
			"https://nyc3.digitaloceanspaces.com/matchkicks-s3/2022/02/52433415_1577041652498063_1006492291816648056_n.mp4",
			"https://nyc3.digitaloceanspaces.com/matchkicks-s3/2022/02/227564215_208695164525752_2258485981969714905_n.mp4",
			"https://nyc3.digitaloceanspaces.com/matchkicks-s3/2022/02/joined_video_697db33a06d74fcba4528a2d97936ca4.mp4",
			"https://nyc3.digitaloceanspaces.com/matchkicks-s3/2022/02/joined_video_f7b8e45ee2ac4272808da638b0b1a5a5.mp4",
			"https://nyc3.digitaloceanspaces.com/matchkicks-s3/2022/02/Matchkicks-Review-_.mp4",
			"https://nyc3.digitaloceanspaces.com/matchkicks-s3/2022/02/Matchkicks.com-custom-order.mp4",
			"https://nyc3.digitaloceanspaces.com/matchkicks-s3/2022/02/RPReplay_Final1629118315.mp4",
			"https://nyc3.digitaloceanspaces.com/matchkicks-s3/2022/02/Shoe-matching-website.mp4",
			"https://nyc3.digitaloceanspaces.com/matchkicks-s3/2022/02/Snaptik_6982680952816323846_janiyah-jackson.mp4",
			"https://nyc3.digitaloceanspaces.com/matchkicks-s3/2022/02/Snaptik_6986405633696271621_kylah.mp4",
			"https://nyc3.digitaloceanspaces.com/matchkicks-s3/2022/02/Snaptik_6990004289364102405_kam.mp4",
			"https://nyc3.digitaloceanspaces.com/matchkicks-s3/2022/02/Snaptik_6992011829702642949_arickat.mp4",
			"https://nyc3.digitaloceanspaces.com/matchkicks-s3/2022/02/Snaptik_6994168726639693061_therealist-drew.mp4",
			"https://nyc3.digitaloceanspaces.com/matchkicks-s3/2022/02/Snaptik_6999764142521339142_the-ghetto-turns.mp4",
			"https://nyc3.digitaloceanspaces.com/matchkicks-s3/2022/02/Snaptik_7005344935146687749_whitney-m-bolden.mp4",
			"https://nyc3.digitaloceanspaces.com/matchkicks-s3/2022/02/Snaptik_7005355565501271302_melissa-s-a.mp4",
			"https://nyc3.digitaloceanspaces.com/matchkicks-s3/2022/02/Snaptik_7005664456097172741_bre-zhang.mp4",
			"https://nyc3.digitaloceanspaces.com/matchkicks-s3/2022/02/Snaptik_7010482485658258694_bre-zhang.mp4",
			"https://nyc3.digitaloceanspaces.com/matchkicks-s3/2022/02/Snaptik_7010883057741745413_eden-shoots-threes.mp4",
			"https://nyc3.digitaloceanspaces.com/matchkicks-s3/2022/02/ssstiktok_1642711772-1.mp4",
			"https://nyc3.digitaloceanspaces.com/matchkicks-s3/2022/02/ssstiktok_1642712568-1.mp4",
			"nyc3.digitaloceanspaces.com/matchkicks-s3/2022/02/ssstiktok_1642712606-1.mp4",
			"https://nyc3.digitaloceanspaces.com/matchkicks-s3/2022/02/ssstiktok_1642712651-1.mp4",
			"https://nyc3.digitaloceanspaces.com/matchkicks-s3/2022/02/ssstiktok_1642712711-1.mp4",
			"https://nyc3.digitaloceanspaces.com/matchkicks-s3/2022/02/ssstiktok_1642712812-1.mp4",
			"https://nyc3.digitaloceanspaces.com/matchkicks-s3/2022/02/ptKMuUYN-ssstiktok_1644851497.mp4",
			"https://nyc3.digitaloceanspaces.com/matchkicks-s3/2022/02/videoplayback.mp4"
		);

		$data = '[row]';

		foreach ($videos as $video) {
			$data .= '[col span="2" span__sm="4" margin__sm="0px 0px -30px 0px"]<video style="width:100%;height:auto;max-height:300px;background:#262626;" controls><source src="' . $video . '" type="video/mp4">Your browser does not support the video tag.</video>[/col]';
		}

		$data .= '[/row]';



		return do_shortcode($data);
	}

	function releaseDateSneakers()
	{
		$return = "";
		$return .= '<br/>[row_inner]';



		$results = new WP_QUERY(
			array(
				"post_type" => "sneaker",
				"posts_per_page" => 60,
				'meta_key' => 'release_date',
				'orderby' => 'meta_value_num',
				'order' => 'DESC',
				'meta_query' => array(
					'relation' => 'AND', // both of below conditions must match
					array(
						'key' => 'release_date',
						'value' => date("Ymd", strtotime("-90 days")),
						'compare' => '>'
					),
					array(
						'key' => 'release_date',
						'value' => date("Ymd", strtotime("+15 days")),
						'compare' => '<'
					)
				)
			)
		);

		$topDesigns =  get_posts(array(
			'post_type' => 'design',
			'posts_per_page' => 60,
			'meta_key' => 'hits_week',
			'orderby' => 'meta_value_num',
			'post_status' => 'publish',
			'order' => 'DESC'
		));

		shuffle($topDesigns);
		$i = 0;
		$maxResults = 60;
		foreach ($results->posts as $post) {
			$date = DateTime::createFromFormat('Ymd', get_post_meta($post->ID, 'release_date', true));
			$productTypeInt = 0;
			$productType[0] = 't-shirt';
			$productType[1] = 'hoodie';
			$sneakerImage = MKProductImage($post->ID, $topDesigns[$i % 30]->ID, $productType[$productTypeInt]);
			$imgPath = $sneakerImage['png'];
			$finalImage = $sneakerImage['preview'];

			$return .= '[col_inner span="3" span__sm="6" class="colorShirtCol"] <div class="box has-hover has-hover box-default box-text-bottom">
		<div class="box-image">
		<a href="/matching-sneakers/' . $post->post_name . '?first=' . $topDesigns[$i % $maxResults]->post_name . '&type=' . $_SESSION['type'] . '"> <div class="">
		<img class="lozad" data-src="' . $finalImage . '"> </div>
		</a> </div>
		<div class="box-text text-center" style="background-color:white;">
		<div class="box-text-inner"><b>' . $date->format('n/j/Y') . '</b><br/>
		' . $post->post_title . '</div>
		</div>
		</div>[/col_inner]';
			$i++;
		}

		$return .= '[/row_inner]';
		return do_shortcode($return);
	}

	function popularSneakers()
	{
		$return = "";
		if (isset($_GET['type'])) {
			$type = $_GET['type'];
		}
		if (!empty($_SESSION['type']) && empty($type)) {
			$type = $_SESSION['type'];
		}
		if (empty($type)) {
			$type = 't-shirt';
		}
		$_SESSION['type'] = $type;
		$_TYPE['t-shirt'] = 'outline';
		$_TYPE['hoodie'] = 'outline';
		$_TYPE['crop-top'] = 'outline';
		$_TYPE['gym-bag'] = 'outline';
		$_TYPE['socks'] = 'outline';
		unset($_TYPE[$type]);

		$return .= '<br/>[row_inner]';

		if (empty($_GET['pg'])) {
			$_GET['pg'] = 1;
		}
		if (empty($_GET['filter'])) {
			$_GET['filter'] = 'week';
		}
		$_FILTER['week'] = 'is-outline';
		$_FILTER['month'] = 'is-outline';
		$_FILTER['total'] = 'is-outline';
		unset($_FILTER[$_GET['filter']]);
		$return .= '[col_inner span="12"]';
		$return .= '<p style="margin-bottom:0;text-align:center"><i>Show Me These Products Available</i><br/><br/>';
		$return .= '<a href="?type=t-shirt" target="_self" class="button secondary dv-border-0 is-' . (isset($_TYPE['t-shirt']) ? $_TYPE['t-shirt'] : '') . ' is-small""><span>T-Shirts</span></a>';
		$return .= '<a href="?type=hoodie" target="_self" class="button secondary dv-border-0 is-' . (isset($_TYPE['hoodie']) ? $_TYPE['hoodie'] : '') . ' is-small""><span>Hoodies</span></a>';
		$return .= '<a href="?type=crop-top" target="_self" class="button secondary dv-border-0 is-' . (isset($_TYPE['crop-top']) ? $_TYPE['crop-top'] : '') . ' is-small""><span>Crop Tops</span></a>';
		$return .= '<a href="?type=gym-bag" target="_self" class="button secondary dv-border-0 is-' . (isset($_TYPE['gym-bag']) ? $_TYPE['gym-bag'] : '') . ' is-small""><span>Gym Bags</span></a>';
		$return .= '<a href="?type=socks" target="_self" class="button secondary dv-border-0 is-' . (isset($_TYPE['socks']) ? $_TYPE['socks'] : '') . ' is-small""><span>Socks</span></a>';
		$return .= '</p>';
		$return .= '[/col_inner]';

		$maxResults = 64;
		$maxSneakerPages = intval(wp_count_posts('sneaker')->publish / $maxResults);
		$sneakerPage = $_GET['pg'] % $maxSneakerPages;
		$maxDesignPages = intval(wp_count_posts('design')->publish / $maxResults);
		$designPage = $_GET['pg'] % $maxDesignPages;
		if ($sneakerPage == 0) {
			$sneakerPage = $maxSneakerPages;
		}
		if ($designPage == 0) {
			$designPage = $maxDesignPages;
		}
		$results = new WP_QUERY(
			array(
				"post_type" => "sneaker",
				"posts_per_page" => $maxResults,
				'meta_key' => 'hits_' . esc_attr($_GET['filter']),
				'orderby' => 'meta_value_num',
				'order' => 'DESC',
				'post_status' => 'publish',
				'paged' => $sneakerPage
			)
		);

		$topDesigns =  get_posts(array(
			'post_type' => 'design',
			'post_status' => 'publish',
			'posts_per_page' => $maxResults,
			'meta_key' => 'hits_month',
			'orderby' => 'meta_value_num',
			'order' => 'ASC',
			'paged' => $designPage,
			// 'tax_query' => array(
			//     array(
			//         'taxonomy' => 'design_category',
			//         'field' => 'slug',
			//         'terms' => "featured"
			//     )
			// )
		));
		if (current_user_can('administrator')) { // pending-remove
			echo "TOP_DESIGNS COUNT = " . sizeof($topDesigns);
		}

		if ($maxResults > sizeof($topDesigns)) {
			$moreDesigns = get_posts(array(
				'post_type' => 'design',
				'posts_per_page' => $maxResults - sizeof($topDesigns),
				'meta_key' => 'hits_month',
				'orderby' => 'meta_value_num',
				'order' => 'DESC',
				'paged' => "0",
				'post_status' => 'publish',
				'tax_query' => array(
					array(
						'taxonomy' => 'design_category',
						'field' => 'slug',
						'terms' => "featured"
					)
				)
			));
			$topDesigns = array_merge($topDesigns, $moreDesigns);
		}
		if ($maxResults > sizeof($topDesigns)) {
			$moreDesigns = get_posts(array(
				'post_type' => 'design',
				'posts_per_page' => $maxResults - sizeof($topDesigns),
				'meta_key' => 'hits_month',
				'orderby' => 'meta_value_num',
				'order' => 'DESC',
				'paged' => "0",
				'post_status' => 'publish',
				'tax_query' => array(
					array(
						'taxonomy' => 'design_category',
						'field' => 'slug',
						'terms' => "featured"
					)
				)
			));
			$topDesigns = array_merge($topDesigns, $moreDesigns);
		}


		// if (current_user_can('administrator')) { // pending-remove
		// echo "===> is admin";
		$sub = array_splice($topDesigns, 0, 20);
		shuffle($sub);
		array_splice($topDesigns, 0, 0, $sub);
		// }
		shuffle($topDesigns);


		$i = 0;
		foreach ($results->posts as $post) {
			$productTypeInt = 0;
			$sneakerImage = MKProductImage($post->ID, $topDesigns[$i % $maxResults]->ID,  $type);
			$imgPath = $sneakerImage['png'];
			$finalImage = $sneakerImage['preview'];


			$return .= '[col_inner span="3" span__sm="6" class="colorShirtCol"] <div class="box has-hover has-hover box-default box-text-bottom">
		<div class="box-image">
		<a href="/matching-sneakers/' . $post->post_name . '?first=' . $topDesigns[$i % $maxResults]->post_name . '&type=' . $_SESSION['type'] . '"
		data-sneaker"' . $post->post_name . '"
		data-design="' . $topDesigns[$i % $maxResults]->post_name . '"
		data-product-type"' . $_SESSION['type'] . '"
		data-user-id"' . get_current_user_id() . '"
		> <div class="">
		<img class="lozad" data-src="' . $finalImage . '"> </div>
		</a> </div>
		<div class="box-text text-center" style="background-color:white;">
		<div class="box-text-inner">
		' . $post->post_title . '</div>
		</div>
		</div>[/col_inner]';
			$i++;
		}

		$return .= '[/row_inner]';
		$return .= '[col_inner]';
		$return .= '<div>' . dv_custom_pagination(100, 4) . '</div>';
		$return .= '[/col_inner]';
		return do_shortcode($return);
	}

	function customer_upload_own_sneaker()
	{
		wp_enqueue_script("customer-color-picker", "/wp-content/themes/flatsome-child/js/customer-color-picker.js", false, "1.1.19");
		wp_enqueue_style("customer-color-picker", "/wp-content/themes/flatsome-child/css/customer-color-picker.css", false, "1.1.11");

		wp_enqueue_script("dv-vibrant", "/wp-content/themes/flatsome-child/js/vibrant.min.js", false, "1.1.19");

		global $wpdb;
		$findAndReplace = array(
			'{sneaker_image}' => '',
			'{material_colors}' => '',
			'{skip_first_color}' => ''
		);
		$find = array();
		$replace = array();


		$entry = $wpdb->get_results("SELECT * FROM `mk_wpforms_entries` WHERE `entry_id` = " . sanitize_text_field($_GET['id']));
		$data = json_decode($entry[0]->fields, 1);

		$parsed_data = [];
		foreach ($data as $d) {
			$parsed_data[$d['name']] = $d['value'];
		}
		if (!$data[2]['value']) {
			return "Invalid Entry.";
		}

		// --------------------------------------------------
		// Sneaker Image
		// --------------------------------------------------

		ob_start();
		content_sneaker_image($parsed_data);
		$findAndReplace['{sneaker_image}'] = ob_get_clean();
		ob_flush();

		// --------------------------------------------------
		// Material Colors
		// --------------------------------------------------
		ob_start();
		content_material_colors();
		$findAndReplace['{material_colors}'] = ob_get_clean();
		ob_flush();

		// --------------------------------------------------
		// Skip First Color
		// --------------------------------------------------
		ob_start();
		content_skip_first_colors();
		$findAndReplace['{skip_first_color}'] = ob_get_clean();
		ob_flush();


		// Modify UX block
		foreach ($findAndReplace as $k => $v) {
			$find[] = $k;
			$replace[] = $v;
		}

		$ux_block = str_replace($find, $replace,  do_shortcode('[block id="upload-your-own-sneaker"]'));

		return content_of_shortcode($ux_block);
	}

	function mkdv_get_dropdown_data($data)
	{
		$product_id = mkdv_get_wc_main_product_id_with_variations();

		$handle = new WC_Product_Variable($product_id);
		$variations1 = $handle->get_children();
		$productTypes = array();
		$productSizes = array();
		$actualVariations = array();
		$productSizesNew = array();

		$sizeTerms = get_terms('pa_product-size');

		foreach ($sizeTerms as $sizeTerm) {
			$finalSizeTerms[] = $sizeTerm->slug;
		}

		// Loop through all possible variations in the database
		foreach ($variations1 as $x => $value) {
			$single_variation = new WC_Product_Variation($value);
			$v = $single_variation->get_variation_attributes();

			$temp = array();
			$temp['data-type'] = $v['attribute_pa_product-type'];
			$temp['data-color'] = $v['attribute_pa_product-color'];
			$temp['description'] = ucwords(str_replace("-", " ", $v['attribute_pa_product-color'] . " " . $v['attribute_pa_product-type']));
			array_push($productTypes, $temp);

			$temp = array();
			$temp['data-size'] = $v['attribute_pa_product-size'];
			$temp['description'] = get_term_by('slug', $v['attribute_pa_product-size'], 'pa_product-size')->name;
			// $temp['description'] = ucwords(str_replace("-", " ", $v['attribute_pa_product-size']));
			array_push($productSizes, $temp);

			$temp = array();
			$temp['data-size'] = $v['attribute_pa_product-size'];
			$temp['data-type'] = $v['attribute_pa_product-type'];
			$temp['data-color'] = $v['attribute_pa_product-color'];
			$temp['value'] = $value;
			$temp['data-size'] = get_term_by('slug', $v['attribute_pa_product-size'], 'pa_product-size')->slug;
			$temp['stock-status'] = $single_variation->get_data()['stock_status'];
			array_push($actualVariations, $temp);

			// -------------------------------------------------------- testing starts
			$temp = array();
			$temp['data-size'] = get_term_by('slug', $v['attribute_pa_product-size'], 'pa_product-size')->slug;
			$temp['description'] = get_term_by('slug', $v['attribute_pa_product-size'], 'pa_product-size')->name;
			$productSizesNew[array_search($v['attribute_pa_product-size'], $finalSizeTerms)] = $temp;
			// -------------------------------------------------------- testing ends

		}
		$productTypes = mkdv_remove_duplicates_from_array($productTypes, 'description');
		$productSizes = mkdv_remove_duplicates_from_array($productSizes, 'data-size');
		ksort($productSizesNew);
		$productSizesNew = mkdv_remove_duplicates_from_array($productSizesNew, 'data-size');
		$productSizes = $productSizesNew;

		// prepare output
		$out = array();
		$out['productSizesNew'] = $productSizesNew;
		$out['productTypes'] = $productTypes;
		$out['productSizes'] = $productSizes;
		$out['actualVariations'] = $actualVariations;

		// -------------------------------------
		// testing starts

		// testing ends
		// -------------------------------------

		return $out;
		echo json_encode($out);
	}

	function template_include($template)
	{
		global $post;
		if (!is_object($post)) {
			return;
		}
		// variations-management
		$file = plugin_dir_path(__FILE__) . "templates/page-variations-management.php";
		if (file_exists($file) && $post->post_name == 'variations-management') {
			$template = $file;
		}

		return $template;
	}

	function pf_create_order_cb($request)
	{
		$return = array();
		$order_id = $request->get_param('order_id');
		$force_push = $request->get_param('force_push');
		$confirm = $request->get_param('confirm');
		$reset_pf_push_attempts = $request->get_param('reset_pf_push_attempts');

		if ($force_push != 'true') {
			$force_push = false;
		}
		if ($confirm != '1') {
			$confirm = '0';
		}
		if ($reset_pf_push_attempts != 'true') {
			$reset_pf_push_attempts = false;
		}

		$printful_service = new DPDV_Printful_Service($order_id);

		if ($reset_pf_push_attempts == 'true') {
			$printful_service->set_pf_push_attempts(0);
		}
		if ($printful_service->get_pf_push_attempts() >= 3) {
			$return = new WP_REST_Response(
				array(
					'status' => 500,
					'error' => 'Exceeded pf_push_attempts = ' . $printful_service->get_pf_push_attempts()
				),
				500
			);
			return $return;
		}

		$pf_order_response = $printful_service->fetch_pf_order($printful_service->pf_order_id);
		$pf_order_response_status_code = wp_remote_retrieve_response_code($pf_order_response);
		$pf_order_response_body = json_decode(wp_remote_retrieve_body($pf_order_response), true);

		if (is_wp_error($pf_order_response)) {
			$return = new WP_REST_Response(
				array(
					'status' => 500,
					'body' => $pf_order_response
				),
				500
			);
			return $return;
		}

		$printful_service->set_pf_order($pf_order_response_body['result']);

		if ($force_push == false && $printful_service->pf_order_exists) {
			$args = array(
				'status' => 400,
				'error' => 'This order already exists in Printful',
				'body' => $printful_service->get_pf_order()
			);
			$return = new WP_REST_Response($args, 400);
			return $return;
		}

		if ($force_push == true && $printful_service->is_pf_order_canceled()) {
			$printful_service->set_pf_order_id(rand());
		}
		$pf_order_POST_data = $printful_service->get_POST_data();

		if (is_wp_error($pf_order_POST_data)) {
			$printful_service->increment_pf_push_attempts(1);
			$args = array(
				'status' => 500,
				'message' => 'Error in pf_order_POST_data',
				'body' => array(
					'order_id' => $printful_service->get_wc_order_id(),
					'order_number' => $printful_service->get_wc_order_number(),
					'pf_push_attempts' => $printful_service->get_pf_push_attempts()
				),
				'error' => $pf_order_POST_data
			);
			$printful_service->send_error_emails($printful_service->get_wc_order_number(), $args);
			$return = new WP_REST_Response($args, 500);
			return $return;
		}

		$pf_order = $printful_service->create_pf_order($pf_order_POST_data, $confirm);
		if (is_wp_error($pf_order)) {
			$args = array(
				'status' => 500,
				'body' => array(
					'order_id' => $printful_service->get_wc_order_id(),
					'order_number' => $printful_service->get_wc_order_number(),
					'pf_push_attempts' => $printful_service->get_pf_push_attempts()
				),
				'error' => $pf_order
			);
			$printful_service->send_error_emails($printful_service->get_wc_order_number(), $args);
			$return = new WP_REST_Response($args, 500);
			return $return;
		}

		$response_code = wp_remote_retrieve_response_code($pf_order);
		$response_body = json_decode(wp_remote_retrieve_body($pf_order), true);

		$return = new WP_REST_Response(array(
			'status' => $response_code,
			'message' => 'Order created successfully in Printful',
			'body' => $response_body
		), $response_code,);
		return $return;
	}

	function pf_get_order_cb($request)
	{
		$return = array();
		$order_id = $request->get_param('order_id');
		$printful_service = new DPDV_Printful_Service($order_id);
		$pf_order_response = $printful_service->fetch_pf_order($printful_service->pf_order_id);

		$pf_order_response_status_code = wp_remote_retrieve_response_code($pf_order_response);
		$pf_order_response_body = json_decode(wp_remote_retrieve_body($pf_order_response), true);

		if (is_wp_error($pf_order_response) || $pf_order_response_status_code != 200) {
			$return = new WP_REST_Response(
				array(
					'status' => $pf_order_response_status_code ? $pf_order_response_status_code : 500,
					'body' => $pf_order_response
				),
				500
			);
			return $return;
		}

		$printful_service->set_pf_order($pf_order_response_body['result']);
		$pf_order_response_body['is_canceled'] = $printful_service->is_pf_order_canceled();
		$pf_order_response_body['is_editable'] = $printful_service->is_pf_order_editable();
		$return = new WP_REST_Response(array(
			'status' => $pf_order_response_status_code,
			'body' => $pf_order_response_body,
			// 'body' => $printful_service,
		), $pf_order_response_status_code);
		return $return;
	}

	function pf_cancel_order_cb($request)
	{
		$return = array();
		$order_id = $request->get_param('order_id');
		$reset_order_status = $request->get_param('reset_order_status');
		$printful_service = new DPDV_Printful_Service($order_id);
		$pf_order_response = $printful_service->cancel_pf_order($printful_service->pf_order_id);

		$pf_order_response_status_code = wp_remote_retrieve_response_code($pf_order_response);
		$pf_order_response_body = json_decode(wp_remote_retrieve_body($pf_order_response), true);

		if (is_wp_error($pf_order_response) || $pf_order_response_status_code != 200) {
			$return = new WP_REST_Response(
				array(
					'status' => $pf_order_response_status_code ? $pf_order_response_status_code : 500,
					'body' => $pf_order_response
				),
				$pf_order_response_status_code
			);
			return $return;
		}

		if ($reset_order_status == 'true') {
			$printful_service->wc_order->update_status('processing');
		}

		$return = new WP_REST_Response(array(
			'status' => $pf_order_response_status_code,
			'body' => $pf_order_response_body,
			// 'body' => $printful_service,
		), $pf_order_response_status_code);
		return $return;
	}

	function pf_create_order_cron_cb($request)
	{
		$limit = $request->get_param('limit');
		$confirm = $request->get_param('confirm');
		$order_age_threshold_in_hours = $request->get_param('order_age_threshold_in_hours');
		$final_response = array();

		if (!$limit) {
			$limit = 3;
		}
		$limit = intval($limit);
		if ($limit > 10) {
			$limit = 10;
		}
		if ($confirm != '1') {
			$confirm = '0';
		}
		if (!$order_age_threshold_in_hours) {
			$order_age_threshold_in_hours = 4;
		}

		date_default_timezone_set(wp_timezone_string());

		$final_response['timestamp_initial'] = strtotime('-7 day');
		$final_response['timestamp_final'] = strtotime('-' . $order_age_threshold_in_hours . ' hours');

		$final_response['initial_date'] = date("Y-m-d\TH:i:sP", $final_response['timestamp_initial']);
		$final_response['final_date'] = date("Y-m-d\TH:i:sP", $final_response['timestamp_final']);
		$final_response['current_date'] = date("Y-m-d\TH:i:sP");


		$orders = wc_get_orders(
			array(
				'limit' => $limit,
				'type' => 'shop_order',
				'status' => array('wc-processing'),
				'date_created' => $final_response['timestamp_initial'] . '...' . $final_response['timestamp_final'],
			)
		);

		if ($request->get_param('test_order_id')) {
			$test_order_id = 118829;
			$test_order_id = 121830;
			$orders = array();

			$the_order = new WC_Order($test_order_id);
			$the_order->set_date_created(strtotime('-8 hours'));

			$orders[] = $the_order;
		}

		$final_response['total_orders'] = sizeof($orders);
		foreach ($orders as $i => $order) {
			$order_custom_key = 'order_' . $order->ID;
			$final_response[$order_custom_key] = array();

			$final_response[$order_custom_key]['date_created'] = $order->date_created;
			if ($order->date_created < $final_response['initial_date'] || $order->date_created > $final_response['final_date']) {
				$final_response[$order_custom_key]['pf_push_status'] = 'skipped';
				continue;
			}

			$site = "https://" . $_SERVER['HTTP_HOST'];

			$url = $site . "/wp-json/dpdv/v1/printful/orders/create?order_id=" . $order->ID . '&force_push=true&confirm=' . $confirm;
			$final_response[$order_custom_key]['push_url'] = $url;
			$response = wp_remote_get($url, array(
				'method' => 'GET',
				'timeout' => 60,
			));

			$response_code = wp_remote_retrieve_response_code($response);
			$response_body = json_decode(wp_remote_retrieve_body($response), true);
			if (is_wp_error($response)) {
				$final_response[$order_custom_key]['pf_push_status'] = 'failed';
				$final_response[$order_custom_key]['pf_push_status_reason'] = $response;
				continue;
			}
			if ($response_code != 200) {
				$final_response[$order_custom_key]['pf_push_status'] = 'failed';
				$final_response[$order_custom_key]['pf_push_status_reason'] = $response_body;
				continue;
			}
			$final_response[$order_custom_key]['pf_push_status'] = 'success';
			$final_response[$order_custom_key]['pf_push_status_code'] = $response_code;
			$final_response[$order_custom_key]['pf_push_status_reason'] = $response_body;
		}

		return $final_response;
	}

	public function pf_reset_push_attempts_cb($request)
	{
		$order_id = $request->get_param('order_id');
		$printful_service = new DPDV_Printful_Service($order_id);
		$printful_service->set_pf_push_attempts(0);

		$return = new WP_REST_Response(array(
			'status' => 200,
			'body' => array('pf_push_attempts' => $printful_service->get_pf_push_attempts()),
		), 200);
		return $return;
	}

	public function pf_api_webhook_cb($request)
	{
		global $wpdb;
		$pf_api_log_file = "scripts/printful/pf-api.txt";
		$postmeta_table = $wpdb->prefix . 'postmeta';

		$data = $request->get_params();

		error_log('API Data ' . date("Y-m-d\TH:i:sP", time()) . PHP_EOL, 3, $pf_api_log_file);
		error_log(json_encode($data) . PHP_EOL . PHP_EOL . PHP_EOL, 3, $pf_api_log_file);

		$store = $data['store'];
		$type = $data['type'];
		$data = $data['data'];

		$data['order']['external_id'] = explode("-", $data['order']['external_id'])[0];

		$orderID = $wpdb->get_results("SELECT `post_id` FROM " . $postmeta_table . " WHERE `meta_key` = '_order_number' AND `meta_value` =  " . $data['order']['external_id'])[0]->post_id;



		if (empty($orderID)) {
			$return = new WP_REST_Response(array(
				'status' => 400,
				'error' => 'orderID is NULL'
			));
			return $return;
		}

		$order = wc_get_order($orderID);

		if (empty($order)) {
			$return = new WP_REST_Response(array(
				'status' => 400,
				'error' => 'order is NULL'
			));
			return $return;
		}

		$order_data = $order->get_data(); // The Order data
		$email_subject = "Note from Match Kicks Team";

		switch ($type) {
			case "package_shipped":
				// add tracking number
				if (function_exists('wc_st_add_tracking_number')) {
					$_the_date = date("Y-m-d", time());
					wc_st_add_tracking_number($orderID, $data['shipment']['tracking_number'], $data['shipment']['carrier'], time());
				}
				update_post_meta($orderID, 'tracking_number', $data['shipment']['tracking_number']);

				$order->add_order_note("Order was shipped via " . $data['shipment']['carrier'] . " with tracking number: " . $data['shipment']['tracking_number'], true, true);
				$order->update_status('wc-completed');


				$url_vars = http_build_query(array("carrier_code" => strtolower($data['shipment']['carrier']), "tracking_number" => $data['shipment']['tracking_number']));

				$shipengine_service = new DPDV_ShipEngine_Service();
				$response = $shipengine_service->subscribe($url_vars);

				if (!empty($response)) {
					$data = json_decode($response, 1);
					if (!empty($response['errors'])) {
						$msg = "<b>There was an error with trying to process/get this tracking number. Therefore, tracking this order automatically will not happen and it will stay in 'Completed' in WooCommerce.</b><br/><br/>";
						foreach ($response['errors'] as $key => $val) {
							$msg .= uc_words(str_replace("_", " ", $key . " - " . $val)) . "<br/>";
						}
						$order->add_order_note($msg);
					}
				} else {
					$order->add_order_note("ShipEngine Subscribed. We'll get notifications if anything goes on with this order during tracking.");
				}

				break;
			case "package_returned":
				$order->update_status('pf-returned');

				break;
			case "order_updated":
				switch ($data['order']['status']) {
					case "draft":
						break;
					case "pending":
						//$order->add_order_note("Pending - Our printing warehouse has received your order." . $data['reason'], true, true);
						break;
					case "cancelled":
						$order->add_order_note("Cancelled - Our printing warehouse has temporarily cancelled the order from their system." . $data['reason'], true, true);
						break;
					case "onhold":
						$order->update_status('pf-hold');
						break;
					case "inprocess":
						$order->update_status('pf-loaded');
						break;
					case "partial":
						$order->add_order_note("Partially Fulfilled - Some items are shipped already, the rest will follow." . $data['reason'], true, true);
						break;
				}
				break;
		}

		$return = new WP_REST_Response(array(
			'status' => 200,
			'body' => $data,
		), 200);

		return $return;
	}

	function pf_get_variation_cb($request)
	{
		// wc_variation_id = 44023, pf_variation_id = 10776
		$variation_id = $request->get_param('variation_id');

		$printful_service = new DPDV_Printful_Service();

		$pf_variation = $printful_service->get_pf_variation($variation_id);

		if (is_wp_error($pf_variation)) {
			$return = new WP_REST_Response(array(
				'status' => 404,
				'error' => $pf_variation->get_error_message(),
				'error_source' => 'get_pf_variation()'
			), 404);
			return $return;
		}

		$return = new WP_REST_Response(array(
			'status' => 200,
			'body' => $pf_variation,
		), 200);

		return $return;
	}

	function pf_fetch_and_cache_products_cb($request)
	{
		$q = $request->get_param('q');
		$limit = 500;
		$items_count = 0;

		$k1 = strtolower($request->get_param('k1'));
		$k2 = strtolower($request->get_param('k2'));

		$variations = array();

		$printful_service = new DPDV_Printful_Service();
		$products = $printful_service->get_printful_products();

		$fetched_products = 0;
		foreach ($products as $product) {
			if ($fetched_products < 3 || 1 == 1) {
				$_product = $printful_service->get_printful_single_product($product['id']);

				$_variants = [];

				foreach ($_product['variants'] as $variant) {
					array_push($_variants, array(
						'id' => $variant['id'],
						'title' => $variant['name']
					));
				}

				$_writeable = array(
					'variants' => $_variants
				);
				file_put_contents($printful_service->cached_variations_directory . '/' . $product['id'] . ".json", json_encode($_writeable));

				array_push($variations, $_variants);
			}
			$fetched_products++;
		}

		return $fetched_products;
	}

	function pf_cached_variations_cb($request)
	{
		$q = $request->get_param('q');
		$limit = 500;
		$items_count = 0;

		$k1 = strtolower($request->get_param('k1'));
		$k2 = strtolower($request->get_param('k2'));

		if (empty($k1)) {
			$k1 = ' ';
		}
		if (empty($k2)) {
			$k2 = ' ';
		}

		$path    = 'scripts/printful/cached-variations';

		$files = scandir($path);
		$files = array_diff(scandir($path), array('.', '..'));


		$variations = array();
		foreach ($files as $file) {
			$file_path = $path . '/' . $file;
			$json_data = file_get_contents($file_path);
			$data = json_decode($json_data, 1);

			$variants = array();
			foreach ($data['variants'] as $_variant) {

				$check = strtolower($_variant['title']);
				if ($items_count < $limit) {
					if (
						preg_match("/{$q}/i", $_variant['title'])
						&& (strpos($check, $k1) !== false || empty($k1))
						&& (strpos($_variant['title'], $k2) !== false || empty($k2))
					) {
						array_push($variants, $_variant);
						$items_count++;
					}
				}
			}
			if (sizeof($variants) > 0) {
				array_push($variations, array('variants' => $variants));
			}
		}
		sort($variations);
		return $variations;
	}

	function pf_map_variation_cb($request)
	{
		$type = $request->get_param('type');
		$wc_variation_id = $request->get_param('wc_variation_id');
		$pf_variation_id = $request->get_param('pf_variation_id');
		$pf_variation_name = $request->get_param('pf_variation_name');
		if ($type) {
			switch ($type) {
				case "primary":
					$metaName = "iconic_cffv_107746_printful_product_id";
					$metaName2 = "printful_product_name";
					break;
				case "backup":
					$metaName = "iconic_cffv_107746_printful_backup_product_id";
					$metaName2 = "printful_backup_product_name";
					break;
			}

			update_post_meta($wc_variation_id, $metaName, $pf_variation_id);
			update_post_meta($wc_variation_id, $metaName2, $pf_variation_name);
		}

		return 'done';
	}

	function send_recommended_products_to_customer_cb($request)
	{
		global $wpdb;
		$data = $request->get_params();

		// return $data;
		$topDesigns =  get_posts(array(
			'post_type' => 'design',
			'posts_per_page' => 28,
			'meta_key' => 'hits_week',
			'orderby' => 'meta_value_num',
			'order' => 'DESC'
		));

		$q = array(
			'post_type' => 'shop_order',
			'posts_per_page' => -1,
			'post_status' => array('wc-processing', 'wc-completed'),
			'order' => 'DESC',
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key' => 'suggestion_email_sent',
					'compare' => 'NOT EXISTS', // works!
					'value' => '' // This is ignored, but is necessary...
				),
				array(
					'key' => 'suggestion_email_sent',
					'value' => '1',
					'compare' => '!='
				)
			)
		);

		$posts = get_posts($q);

		foreach ($posts as $post) {
			$email = get_post_meta($post->ID, '_billing_email', true);
			if ($email == $data['email']) {
				$woocommerce_order_items_table = $wpdb->prefix . 'woocommerce_order_items';
				$first = get_post_meta($post->ID, '_billing_first_name', true);
				$last = get_post_meta($post->ID, '_billing_last_name', true);
				$items = $wpdb->get_results("SELECT * FROM `{$woocommerce_order_items_table}` WHERE `order_item_name` = 'Custom Match' AND `order_id` = " . $post->ID);
				$customerSneakers = array();
				foreach ($items as $item) {

					$sneaker = wc_get_order_item_meta($item->order_item_id, 'ShoeID', true);
					$type = wc_get_order_item_meta($item->order_item_id, 'pa_product-type', true);
					$color = wc_get_order_item_meta($item->order_item_id, 'pa_product-color', true);
					$customerSneakers[] = array(
						"sneaker" => $sneaker,
						"sneaker_data" => get_post($sneaker),
						"color" => $color,
						"type" => $type
					);
				}

				$middle = "<br/>Hey " . $first . "!<br/><br/>We love the matches you picked in your most recent order! We wanted you to show you the top selling designs this week on our website for the exact sneakers you picked.<br/><br/>";

				foreach ($topDesigns as $design) {
					$image = MKProductImage($customerSneakers[0]['sneaker'], $design->ID, $customerSneakers[0]['type'])['preview'];

					$middle .= '<div style="text-align:center;border:1px solid #ddd;border-radius:10px;padding:5px;margin:5px;display:inline-block;max-width:200px;"><a href="' . get_site_url() . '/product/match/?sneaker=' . $customerSneakers[0]['sneaker_data']->post_name . '&design=' . $design->post_name . '&product=' . $customerSneakers[0]['type'] . '"><img src="' . $image . '" style="width:190px;" /><br/>' . $design->post_title . '</a></div>';
				}
				$middle .= "<br/><br/>Feel free to shop more and add to your cart. Place another order within 5 hours of your first and we won't charge more shipping and handling!<br/><br/><a href='" . get_permalink($customerSneakers[0]['sneaker']) . "'><div style='width:100%;padding:20px 10px;margin:10px;text-align:center;background:#1458A6;color:white;font-weight:bold;'>SHOP MORE NOW</div></a>";

				$sneaker = $customerSneakers[0]['sneaker_data'];

				$headers  = 'From: Match Kicks <' . get_option('dpdv_info_email') . '>' . "\r\n";
				$headers .= 'Content-Type: text/html' . "\r\n";
				$headers .= 'Reply-To:  ' . get_option('dpdv_info_email') . ' ' . "\r\n";
				$email_subject = $sneaker->post_title . " More Best Selling Matches You'll Love";
				ob_start();

				wc_get_template('emails/email-header.php', array('email_heading' => $email_subject));
				$email_body_template_header = ob_get_clean();

				ob_start();

				wc_get_template('emails/email-footer.php');
				$email_body_template_footer = ob_get_clean();

				$site_title                 = get_bloginfo('name');
				$email_body_template_footer = str_ireplace('{site_title}', $site_title, $email_body_template_footer);


				$final_email_body = $email_body_template_header . $middle . $email_body_template_footer;
				wc_mail($email, $email_subject . ' - ' . $first . " " . $last, $final_email_body, $headers);

				update_post_meta($post->ID, 'suggestion_email_sent', '1');
			}
		}

		return 'done';
	}

	function update_customer_blacklist_cb($request)
	{
		global $wpdb;
		$return = array();
		$email = esc_attr($request->get_param('email'));


		if (empty($email)) {
			$json = file_get_contents('php://input');
			error_log($json);
			$data = json_decode($json, 1);
			$email = $data['email'];
			$dpdv_options = get_option('dpdv_options');
			$brand_name = $dpdv_options['brand_name_for_google_merchant'];

			wp_mail(get_option('dpdv_info_email'), "New Customer Blacklisted", "The customer with the email: $email, has been blacklisted and cannot purchase again on " . $brand_name . ". This is relation to a chargeback submitted. If the chargeback is resolved, you should remove them from the blacklist on the website.");
		}

		$postmeta_table = $wpdb->prefix . 'postmeta';
		$emails = $wpdb->get_results("SELECT `post_id` FROM `$postmeta_table` WHERE `meta_key` = '_billing_email' AND `meta_value` = '" . $email . "'");

		foreach ($emails as $email) {

			$post_id = $email->post_id;

			$order = wc_get_order($post_id);
			$customer = wmfo_get_customer_details_of_order($order);

			$return[] = $customer;

			WMFO_Blacklist_Handler::init($customer, $order, 'add', 'back');
		}

		return $return;
	}

	function order_lookup_cb($request)
	{
		$data = $request->get_params();
		$twilio_log_file = 'scripts/twilio.txt';
		file_put_contents($twilio_log_file, json_encode($data), FILE_APPEND);

		$dv_helpers = DV_Helpers::get_instance();

		$message = $dv_helpers->generate_lookup_message($data);

		$return = array("status" => "success", "message" => $message);

		return $return;
	}

	function currency_switch_function()
	{
		$return = "<span style='display:none;'> geoPlugin Activation: X17JzfpJ3dE4nsYv848Ub15XJBlzmL </span>";

		if (current_user_can("administrator") || wp_get_current_user()->user_login == "designsvalley-test-customer" || 1 == 1) {

			$cookie_currency = "currency";
			if (!isset($_COOKIE[$cookie_currency])) {
				// $geoLocation = unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip=' . $_SERVER['REMOTE_ADDR']));
				$ch = curl_init();
				$url = 'http://www.geoplugin.net/php.gp?ip=' . $_SERVER['REMOTE_ADDR'];
				$timeout = 800;

				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_TIMEOUT_MS, $timeout);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $timeout);

				$geoLocation = curl_exec($ch);
				curl_close($ch);

				if (isset($geoLocation['geoplugin_currencyCode'])) {
					setcookie($cookie_currency, $geoLocation['geoplugin_currencyCode'], time() + (86400 * 30), "/");
				}
			}


			if (get_option("currency_last_checked") < time() - 3600) {

				$ch = curl_init();
				// IMPORTANT: the below line is a security risk, read https://paragonie.com/blog/2017/10/certainty-automated-cacert-pem-management-for-php-software
				// in most cases, you should set it to true
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_URL, 'https://data.fixer.io/api/latest?access_key=6a55ac30a9224f9c49363869fd624d40&format=1&base=USD');
				$result = curl_exec($ch);
				curl_close($ch);

				$obj = json_decode($result, 1);

				update_option("currency_rates", $obj);
				update_option("currency_last_checked", time());

				$rates = $obj;
			} else {
				$rates = get_option("currency_rates");
			}

			if (empty($geoLocation)) {
				return '';
			}
			//echo "<pre>" . print_r($rates,1);

			$currency_symbols = array(
				'AED' => 'د.إ', // ?
				'AFN' => 'Af',
				'ALL' => 'Lek',
				'AMD' => '',
				'ANG' => 'ƒ',
				'AOA' => 'Kz', // ?
				'ARS' => '$',
				'AUD' => '$',
				'AWG' => 'ƒ',
				'AZN' => 'ман',
				'BAM' => 'KM',
				'BBD' => '$',
				'BDT' => '৳', // ?
				'BGN' => 'лв',
				'BHD' => '.د.ب', // ?
				'BIF' => 'FBu', // ?
				'BMD' => '$',
				'BND' => '$',
				'BOB' => '$b',
				'BRL' => 'R$',
				'BSD' => '$',
				'BTN' => 'Nu.', // ?
				'BWP' => 'P',
				'BYR' => 'p.',
				'BZD' => 'BZ$',
				'CAD' => '$',
				'CDF' => 'FC',
				'CHF' => 'CHF',
				'CLF' => '', // ?
				'CLP' => '$',
				'CNY' => '¥',
				'COP' => '$',
				'CRC' => '₡',
				'CUP' => '⃌',
				'CVE' => '$', // ?
				'CZK' => 'Kč',
				'DJF' => 'Fdj', // ?
				'DKK' => 'kr',
				'DOP' => 'RD$',
				'DZD' => 'دج', // ?
				'EGP' => '£',
				'ETB' => 'Br',
				'EUR' => '€',
				'FJD' => '$',
				'FKP' => '£',
				'GBP' => '£',
				'GEL' => 'ლ', // ?
				'GHS' => '¢',
				'GIP' => '£',
				'GMD' => 'D', // ?
				'GNF' => 'FG', // ?
				'GTQ' => 'Q',
				'GYD' => '$',
				'HKD' => '$',
				'HNL' => 'L',
				'HRK' => 'kn',
				'HTG' => 'G', // ?
				'HUF' => 'Ft',
				'IDR' => 'Rp',
				'ILS' => '₪',
				'INR' => '₹',
				'IQD' => 'ع.د', // ?
				'IRR' => '﷼',
				'ISK' => 'kr',
				'JEP' => '£',
				'JMD' => 'J$',
				'JOD' => 'JD', // ?
				'JPY' => '¥',
				'KES' => 'KSh', // ?
				'KGS' => 'лв',
				'KHR' => '៛',
				'KMF' => 'CF', // ?
				'KPW' => '₩',
				'KRW' => '₩',
				'KWD' => 'د.ك', // ?
				'KYD' => '$',
				'KZT' => 'лв',
				'LAK' => '₭',
				'LBP' => '£',
				'LKR' => '₨',
				'LRD' => '$',
				'LSL' => 'L', // ?
				'LTL' => 'Lt',
				'LVL' => 'Ls',
				'LYD' => 'ل.د', // ?
				'MAD' => 'د.م.', //?
				'MDL' => 'L',
				'MGA' => 'Ar', // ?
				'MKD' => 'ден',
				'MMK' => 'K',
				'MNT' => '₮',
				'MOP' => 'MOP$', // ?
				'MRO' => 'UM', // ?
				'MUR' => '₨', // ?
				'MVR' => '.ރ', // ?
				'MWK' => 'MK',
				'MXN' => '$',
				'MYR' => 'RM',
				'MZN' => 'MT',
				'NAD' => '$',
				'NGN' => '₦',
				'NIO' => 'C$',
				'NOK' => 'kr',
				'NPR' => '₨',
				'NZD' => '$',
				'OMR' => '﷼',
				'PAB' => 'B/.',
				'PEN' => 'S/.',
				'PGK' => 'K', // ?
				'PHP' => '₱',
				'PKR' => '₨',
				'PLN' => 'zł',
				'PYG' => 'Gs',
				'QAR' => '﷼',
				'RON' => 'lei',
				'RSD' => 'Дин.',
				'RUB' => 'руб',
				'RWF' => 'ر.س',
				'SAR' => '﷼',
				'SBD' => '$',
				'SCR' => '₨',
				'SDG' => '£', // ?
				'SEK' => 'kr',
				'SGD' => '$',
				'SHP' => '£',
				'SLL' => 'Le', // ?
				'SOS' => 'S',
				'SRD' => '$',
				'STD' => 'Db', // ?
				'SVC' => '$',
				'SYP' => '£',
				'SZL' => 'L', // ?
				'THB' => '฿',
				'TJS' => 'TJS', // ? TJS (guess)
				'TMT' => 'm',
				'TND' => 'د.ت',
				'TOP' => 'T$',
				'TRY' => '₤', // New Turkey Lira (old symbol used)
				'TTD' => '$',
				'TWD' => 'NT$',
				'TZS' => '',
				'UAH' => '₴',
				'UGX' => 'USh',
				'USD' => '$',
				'UYU' => '$U',
				'UZS' => 'лв',
				'VEF' => 'Bs',
				'VND' => '₫',
				'VUV' => 'VT',
				'WST' => 'WS$',
				'XAF' => 'FCFA',
				'XCD' => '$',
				'XDR' => '',
				'XOF' => '',
				'XPF' => 'F',
				'YER' => '﷼',
				'ZAR' => 'R',
				'ZMK' => 'ZK', // ?
				'ZWL' => 'Z$',
			);

			$return .=  "<select name='currency_switch'><option data-rate='1' value='USD'>Select a Currency</option>";

			if (isset($rates['rates']) && is_array($rates['rates'])) {
				foreach ($rates['rates'] as $currencyName => $rate) {
					if (isset($currency_symbols[$currencyName])) {
						$return .= "<option data-symbol='{$currency_symbols[$currencyName]}' data-rate='$rate' value='$currencyName'>$currencyName {$currency_symbols[$currencyName]}</option>";
					}
				}
			}

			$return .= "</select>";

			$geoplugin_currencyCode = isset($geoLocation['geoplugin_currencyCode']) ? $geoLocation['geoplugin_currencyCode'] : 'USD';
			$return .= "
        <script>
            function currency_switch() {
                
                var element = jQuery('select[name=\"currency_switch\"]').find('option:selected');
                var currentRate = element.attr('data-rate');    
                var currentCurrency = element.attr('value');   
                var currentSymbol = element.attr('data-symbol');

				if(currentCurrency) {
					console.log('currencySwitch ==== ' , currentCurrency);
					setCookie('currency', currentCurrency, 2);
                
					jQuery.each(jQuery('.item-price, .woocommerce-Price-amount'), function() {
						if (jQuery(this).attr('original') == null) {
							var currentPrice = jQuery(this).text().replace(/[^0-9\.]/g, '');
							jQuery(this).attr('original', currentPrice);
						} else {
							var currentPrice = jQuery(this).attr('original');
						}
						
						var newPrice = Number(currentPrice) * Number(currentRate);
						
						var newPriceValue = numberWithCommas(parseFloat(newPrice).toFixed(2)) + ' ' + currentCurrency;
						
						if (currentCurrency == 'USD') {
							var newPriceValue = '\$' + numberWithCommas(parseFloat(newPrice).toFixed(2));
						} else {
							var newPriceValue = numberWithCommas(parseFloat(newPrice).toFixed(2)) + ' ' + currentCurrency;
						}
						// console.log('hello ---->', currentRate, newPriceValue);
	
						jQuery(this).text(newPriceValue);
					});					
				} else {
					console.log('currencySwitch UNDEFINED ==== ' , currentCurrency);
				}
            }
        
            jQuery(document).ready(function() {
                
                var currentCountryCurrency = '" . $geoplugin_currencyCode . "';
                if (getCookie('currency') != null) {
                    jQuery('select[name=\"currency_switch\"]').val(getCookie('currency')).change();
                } else if (currentCountryCurrency != null && currentCountryCurrency != '') {
                    jQuery('select[name=\"currency_switch\"]').val(currentCountryCurrency).change();
                } else {
                    jQuery('select[name=\"currency_switch\"]').val('USD').change();
				}
            
                jQuery('select[name=\"currency_switch\"]').change(function() {
				console.log(\"dropdown_change --> \", currentCountryCurrency);
                    currency_switch();    
                });
            });
            
            jQuery( document.body ).on( 'updated_cart_totals', function(){
                currency_switch();
            });
            
            jQuery( document.body ).on( 'updated_checkout', function(){
                currency_switch();
            });
            
            
        </script>";

			return $return;
		} else {
			return $return;
		}
	}

	function rest_ip_permission_callback($request)
	{
		$is_allowed = false;
		$allowed_ip_addresses = $this->get_allowed_ips_for_rest_api();
		$viewer_ip = dpdv_get_viewer_ip_address();

		$request->set_param('viewer_ip', $viewer_ip);

		if (
			in_array($viewer_ip, $allowed_ip_addresses) &&
			in_array($viewer_ip, $allowed_ip_addresses)
		) {
			$is_allowed = true;
		}

		if ($is_allowed) {
			return $is_allowed;
		} else {
			return new WP_Error(
				'forbidden_access',
				'Access denied to IP ' . $viewer_ip,
				array('status' => 403)
			);
		}
		return false;
		return $is_allowed;
	}

	function filter_incoming_connections($errors)
	{
		$allowed_ip_addresses = $this->get_allowed_ips_for_rest_api();

		// var_dump($allowed_ip_addresses);
		// die;


		$viewer_ip = dpdv_get_viewer_ip_address();

		if (
			!in_array($viewer_ip, $allowed_ip_addresses)
			&& !in_array($viewer_ip, $allowed_ip_addresses)
		) {
			return new WP_Error(
				'forbidden_access',
				'Access denied to IP ' . $viewer_ip,
				array('status' => 403)
			);
		}

		return $errors;
	}

	function get_allowed_ips_for_rest_api()
	{
		$dpdv_options = get_option('dpdv_options');
		if (!isset($dpdv_options['wordpress_rest_api_allowed_ip_addresses'])) {
			$dpdv_options['wordpress_rest_api_allowed_ip_addresses'] = '';
		}
		$allowed_ip_addresses = $dpdv_options['wordpress_rest_api_allowed_ip_addresses'];
		$allowed_ip_addresses = explode("\n", $allowed_ip_addresses);

		foreach ($allowed_ip_addresses as $i => $allowed_ip) {
			$allowed_ip_addresses[$i] = str_replace(' ', '', $allowed_ip_addresses[$i]);
			if (str_contains($allowed_ip_addresses[$i], '//')) {
				$allowed_ip_addresses[$i] = substr($allowed_ip_addresses[$i], 0, strpos($allowed_ip_addresses[$i], "//"));
			}

			if ($allowed_ip_addresses[$i] == '') {
				unset($allowed_ip_addresses[$i]);
			}
		}

		if (!in_array('127.0.0.1', $allowed_ip_addresses)) {
			$allowed_ip_addresses[] = '127.0.0.1';
		}

		return $allowed_ip_addresses;
	}

	function wc_update_in_order_status_for_order_item_cb($request)
	{
		$item_id = $request->get_param('id');
		$status = $request->get_param('status');
		$order_id = $request->get_param('order');

		wc_update_order_item_meta($item_id, "In Order", $status);
		$result = "success";
		$reason = "";

		// If you don't have the WC_Order object (from a dynamic $order_id)
		$order = wc_get_order($order_id);

		// The text for the note
		$note = __("Item 'In Order' was set to: " . $status . " for Item #" . $item_id);

		// Add the note
		$order->add_order_note($note);

		$return = new WP_REST_Response(array(
			'status' => 200,
			'result' => $result,
			'reason' => $reason,
		), 200);
		return $return;
	}

	function merchant_feed_cb($request)
	{

		$maxs = array(
			'designsperpage' => 500,
			'sneakersperpage' => 1,
		);

		$designsperpage = $request->get_param('designsperpage');
		$designspage = $request->get_param('designspage');
		$perpage = $request->get_param('perpage');
		$page = $request->get_param('page');

		$output_for = $request->get_param('output_for');


		// Designs query parameters
		if (!isset($designsperpage)) {
			$designsperpage = 500;
		}
		if (!isset($designspage)) {
			$designspage = 1;
		}

		// Sneaker query parameters
		if (!isset($perpage)) {
			$perpage = 1;
		}

		if (!isset($page)) {
			$page = 1;
		}

		$sneakersperpage = $perpage;
		$sneakerspage = $page;


		if ($output_for == 'amazon') {
			$maxs = array(
				'designsperpage' => 30,
				'sneakersperpage' => 1,
			);
		}

		if ($designsperpage > $maxs['designsperpage']) {
			die('designsperpage cannot be greater than ' . $maxs['designsperpage']);
		}
		if ($sneakersperpage > $maxs['sneakersperpage']) {
			die('sneakersperpage cannot be greater than ' . $maxs['sneakersperpage']);
		}

		// get design
		$args = array(
			'name'        => $request->get_param('design'),
			'post_type'   => 'design',
			'post_status' => 'publish',
			'posts_per_page' => $designsperpage,
			'meta_key' => 'hits_month',
			'orderby' => 'meta_value_num',
			'order' => 'DESC',
			'paged' => $designspage,
		);
		$designs = get_posts($args);

		// get sneaker
		$args = array(
			'post_type'   => 'sneaker',
			'post_status' => 'publish',
			'posts_per_page' => $sneakersperpage,
			'meta_key' => 'hits_month',
			'orderby' => 'meta_value_num',
			// 'orderby' => 'publish_date',
			'order' => 'DESC',
			'paged' => $sneakerspage,
		);
		$sneakers = new WP_QUERY($args);

		$data = [];

		foreach ($sneakers->posts as $sneaker) {

			foreach ($designs as $design) {
				$product_types = array(
					't-shirt' => 'T-Shirt',
					'hoodie' => 'Hoodie',
					'crop-top' => 'Crop Top',
					'socks' => 'Socks'
				);

				foreach ($product_types as $product_type_key => $product_type_val) {
					$args = array();
					$temp = $this->mkdv_dv_construct_feed_product_for_google_and_ebay($product_type_key, $product_type_val, $sneaker, $design, $args);
					if (is_array($temp) && sizeof($temp) > 0) {
						$data[] = $temp;
					}
				}
			}
		}

		return $data;
	}

	function mkdv_dv_construct_feed_product_for_google_and_ebay($product_type_key, $product_type_val, $sneaker, $design, $args = array())
	{
		$data = array();
		$dpdv_options = get_option('dpdv_options');
		$brand_name = $dpdv_options['brand_name_for_google_merchant'];
		if (!isset($brand_name) || $brand_name == '') {
			die('DPDV Core - not configured properly.');
		}

		$pageTitle = "$product_type_val - " . ucwords(strtolower($sneaker->post_title)) . " Sneaker Matching $product_type_val (" . ucwords(strtolower($design->post_title)) . ")";
		$productImage = MKProductImage($sneaker->ID, $design->ID, $product_type_key, false, false);
		$ext = "&showLogoOverlay=false&v=1";
		$finalImage = $productImage['preview'] . $ext;

		if (!isset($_GET['facebook'])) {
			$groupID = 45;
		}

		$sizesArray = array("XS", "S", "M", "L", "XL", "2XL", "3XL", "4XL", "5XL");
		$id = "s-" . $sneaker->ID . "-d-" . $design->ID . "-" . strtolower($productImage['colorShirtName']) . "-" . $product_type_key;

		$product_link = get_site_url() . dpdv_prepare_single_product_link($sneaker, $design, $product_type_key, array('prepend_site_url' => true));

		$_attrs = array(
			'productType' => $product_type_key,
			'productColor' => strtolower($productImage['colorShirtName']),
		);
		$the_variation = $this->wc_get_product_variation($_attrs);

		if (empty($the_variation['price'])) {
			return;
		}

		$data = array(
			"title" => $pageTitle,
			"id" => $id,
			"parent_group_id" => 'parent_group_id',
			"item_group_id" => $groupID,
			"google_product_category" => "212",
			"ebay_stock" => "999",
			"link" => $product_link,
			"image_link" => $finalImage,
			"description" => $pageTitle . " is a high quality Sneaker Matching tee designed to match your " . ucwords(strtolower($sneaker->post_title)) . " sneakers.",
			"style" => $product_type_val,
			"condition" => "new",
			"availability" => "in stock",
			"price" => $the_variation['price'] . " USD",
			"sale_price" => $the_variation['price'] . " USD",
			"brand" => $brand_name,
			"is_bundle" => "no",
			"gender" => "unisex",
			"sizes" => $sizesArray,
			"age_group" => "adult",
			"color" => strtolower($productImage['colorShirtName']),
		);

		if (isset($_GET['channable'])) {
			foreach ($sizesArray as $size) {
				$data[] = array(
					"parent_group_id" => $id,
					"id" => $id . "-" . $size,
					"size" => $size,
					"price" => "34.99 USD",
					"ebay_stock" => "999",
				);
			}
		}
		return $data;
	}

	function twilio__get_order_status_cb($request)
	{
		$json = file_get_contents('php://input');
		$data = json_decode($json, 1);

		$twilio_service = new DPDV_Twilio_Service();

		$message = $twilio_service->save_log($json);
		$message = $twilio_service->generate_lookup_message($data);

		$return = array(
			'status' => 'success',
			'message' => $message,
		);

		return $return;
	}

	function twilio__send_promo_cb($request)
	{
		$amount = 50;

		if ($_GET['test'] == 'true') {
			for ($i = 0; $i < 1; $i++) {
				mk_text_customer("7174352021", "test", "https://nyc3.digitaloceanspaces.com/matchkicks-s3/2022/05/MEMORIAL-DAY.gif");
				echo "Sent $i <br/>";
			}
		}

		//wp_die("Edit the code to enable this");

		$promos = get_posts(array(
			"post_type" => "sms_campaign",
			"post_status" => "publish",
			"posts_per_page" => "1",
			"meta_key" => "finished",
			"meta_value" => "1",
			"meta_compare" => "!="
		));

		echo "<pre>" . print_r($promos, 1);

		if (!empty($promos[0]->ID)) {

			$statues = wc_get_order_statuses();

			foreach ($statues as $k => $v) {
				$s[] = $k;
			}

			$args = array(
				'post_type'      => 'shop_order',
				'post_status' 	 => $s,
				'posts_per_page' => '-1',
				'meta_key'       => '_sms_opt_in',
				'meta_value'     => '1',
				'meta_compare'   => '=' // default operator is (=) equals to 
			);

			$query = new WP_Query($args);

			foreach ($query->posts as $post) {
				$numbersToSend[get_post_meta($post->ID, '_billing_phone', true)] = $post->ID;
			}

			foreach ($promos as $promo) {
				$meta = get_post_meta($promo->ID);

				$image = wp_get_attachment_url(get_post_thumbnail_id($promo->ID));

				update_post_meta($promo->ID, 'progress', "1");

				$existingSentNumbers = json_decode($meta['existing_sent_numbers'][0], 1);

				if (!is_array($existingSentNumbers)) {
					$existingSentNumbers = array();
				}

				$i = 0;
				foreach ($numbersToSend as $phone => $v) {
					$phone = preg_replace('~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*~', '$1$2$3', $phone);

					if (!in_array($phone, $existingSentNumbers)) {
						$i++;

						if ($i < $amount) {
							// uncomment the below line to send more texts
							mk_text_customer($phone, $meta['message'][0], $image);
							//echo $phone . " - Sent <br/>";

							$phoneSent[] = $phone;
						}
					}
				}
				$finalExistingSent = array_merge($existingSentNumbers, $phoneSent);
				//print_r($phoneSent);
				if (empty($phoneSent)) {
					update_post_meta($promo->ID, 'finished', "1");
				} else {
					update_post_meta($promo->ID, 'existing_sent_numbers', json_encode($finalExistingSent));
				}
			}
		}
	}

	function wc_get_product_variation__cb($request)
	{
		$args = array();
		$args['productType'] = $request->get_param('productType');
		$args['productColor'] = $request->get_param('productColor');
		$args['productSize'] = $request->get_param('productSize');

		$return = $this->wc_get_product_variation($args);
		return $return;
	}

	function wc_get_product_variation($args)
	{

		$product_id = mkdv_get_wc_main_product_id_with_variations();
		if (!$product_id) {
			return 0;
		}
		$final_variation = array();


		$qProductType = $args['productType'];
		$qProductColor = $args['productColor'];
		$qProductSize = $args['productSize'];


		$preselectedSizes = ['adult-large', 'womens-medium', 'medium', 'one-size'];
		if (isset($qProductSize)) {
			$preselectedSizes = [$qProductSize];
		}

		for ($i = 0; $i < sizeof($preselectedSizes); $i++) {
			$match_attributes =  array(
				"attribute_pa_product-type" => $qProductType,
				"attribute_pa_product-color" => $qProductColor,
				"attribute_pa_product-size" => $preselectedSizes[$i],
			);

			$data_store   = WC_Data_Store::load('product');
			$variation_id = $data_store->find_matching_product_variation(
				new \WC_Product($product_id),
				$match_attributes
			);
			if ($variation_id != 0) {
				break;
			}
		}
		// find variation
		$single_variation = new WC_Product_Variation($variation_id);
		$v = $single_variation->get_variation_attributes();
		$typeTerms = get_terms('pa_product-type');
		$colorTerms = get_terms('pa_product-color');
		$sizeTerms = get_terms('pa_product-size');

		$temp = array();
		$temp['id'] = $variation_id;

		$temp['type-slug'] = $v['attribute_pa_product-type'];
		foreach ($typeTerms as $typeTerm) {
			if ($typeTerm->slug == $v['attribute_pa_product-type']) {
				$temp['type-name'] = $typeTerm->name;
			}
		}

		// attribute-color
		$temp['color-slug'] = $v['attribute_pa_product-color'];
		foreach ($colorTerms as $colorTerm) {
			if ($colorTerm->slug == $v['attribute_pa_product-color']) {
				$temp['color-name'] = $colorTerm->name;
			}
		}

		// attribute-size
		$temp['size-slug'] = $v['attribute_pa_product-size'];
		foreach ($sizeTerms as $sizeTerm) {
			if ($sizeTerm->slug == $v['attribute_pa_product-size']) {
				$temp['size-name'] = $sizeTerm->name;
			}
		}


		if (isset($temp['type-slug']) && isset($temp['color-slug']) && isset($temp['size-slug'])) {

			$temp['stock-status'] = $single_variation->get_stock_status();
			$temp['price'] = $single_variation->get_price();
			$temp['weight'] = $single_variation->get_weight();

			$preselectedSizes = ['adult-large', 'womens-medium', 'medium', 'one-size'];
			if (!empty($_SESSION['size'])) {
				$preselectedSizes[0] = $_SESSION['size'];
			}

			$temp['pre-selected'] = false;
			if (in_array($temp['size-slug'], $preselectedSizes)) {
				$temp['pre-selected'] = true;
			}

			$final_variation = $temp;
		}

		return $final_variation;
	}

	function mk_get_sneakers()
	{
		global $_GET;

		if (empty($_GET['page'])) {
			$_GET['page'] = 1;
		}

		$args = array(
			"post_type" => "sneaker",
			"posts_per_page" => "250",
			"paged" => $_GET['page']
		);
		if (!empty($_GET['id'])) {
			$args['p'] = $_GET['id'];
		}
		if (!empty($_GET['title'])) {
			$args['s'] = $_GET['title'];
		}
		$sneakers = get_posts(
			$args
		);

		$designs = get_posts(
			array(
				"post_type" => "design",
				"posts_per_page" => "250",
			)
		);

		$url = get_site_url();

		foreach ($sneakers as $i => $sneaker_cpt) {
			$design_cpt = $designs[$i % sizeof($designs)];
			$mk_product_image = MKProductImage($sneaker_cpt, $design_cpt);

			$output[] = array(
				"sneaker_id" => $sneaker_cpt->ID,
				"sneaker_title" => $sneaker_cpt->post_title,
				"sneaker_link" => $url . "/matching-sneakers/" . $sneaker_cpt->post_name,
				"sneaker_image" => get_the_post_thumbnail_url($sneaker_cpt->ID, 'full'),
				"product_image" => $mk_product_image['preview'],
			);
		}

		$output = array(
			"status" => "success",
			"results" => $output,
			"page" => $_GET['page'],
			"per_page" => "250",
			// "total" => wp_count_posts("sneaker")->publish
			"total" => wp_count_posts("sneaker")->publish
		);

		return $output;
	}

	function cron__update_order_status_to_final_touches_cb($request)
	{
		$limit = $request->get_param('limit');
		$final_response = array();

		if (!$limit) {
			$limit = 3;
		}
		$limit = intval($limit);
		if ($limit > 10) {
			$limit = 10;
		}

		date_default_timezone_set(wp_timezone_string());

		$final_response['timestamp_initial'] = strtotime('-7 day');
		$final_response['timestamp_final'] = strtotime('-3 day');

		$final_response['initial_date'] = date("Y-m-d\TH:i:sP", $final_response['timestamp_initial']);
		$final_response['final_date'] = date("Y-m-d\TH:i:sP", $final_response['timestamp_final']);
		$final_response['current_date'] = date("Y-m-d\TH:i:sP");


		$orders = wc_get_orders(
			array(
				'limit' => $limit,
				'type' => 'shop_order',
				'orderby' => 'id',
				'order' => 'ASC',
				'status' => array('pf-printing'),
				'date_created' => $final_response['timestamp_initial'] . '...' . $final_response['timestamp_final'],
			)
		);


		$final_response['total_orders'] = sizeof($orders);
		foreach ($orders as $i => $order) {

			$order_custom_key = 'order_' . $order->ID;
			$final_response[$order_custom_key] = array();

			// delete_post_meta($order->ID, 'pf_push_date');
			// update_post_meta($order->ID, 'pf_push_date', $final_response['current_date']);

			if (empty(get_post_meta($order->ID, 'pf_push_date'))) {
				$order_notes = get_private_order_notes($order->ID);
				$order_notes = array_reverse($order_notes);
				if (isset($order_notes[0])) {
					add_post_meta($order->ID, 'pf_push_date', $order_notes[0]['note_date'], true);
				} else {
					add_post_meta($order->ID, 'pf_push_date', $final_response['current_date'], true);
				}
			}
			$pf_push_date = get_post_meta($order->ID, 'pf_push_date', true);
			$pf_push_date_timestamp = strtotime($pf_push_date);

			$days_between = ((strtotime('-1 second') - $pf_push_date_timestamp) / 86400);

			$final_response[$order_custom_key]['ID'] = $order->ID;
			$final_response[$order_custom_key]['current_date'] = date("Y-m-d\TH:i:sP");
			$final_response[$order_custom_key]['pf_push_date'] = $pf_push_date;
			$final_response[$order_custom_key]['days_since_pf_push'] = $days_between;

			if ($days_between > 3) {
				$order->add_order_note("Final Touches - The order is in final touches. Stay tuned.", true, true);
				$order->update_status('pf-final-touches');

				$email = get_post_meta($order->ID, '_billing_email', true);
				// $email = 'shahzaddev125@gmail.com';
				mk_email_customer($email, 'Order Status Update', 'Your order is in final touches');
			}
		}

		return $final_response;
	}

	public function add_to_saved_sneakers__cb($request)
	{
		$user_id = $request->get_param('user_id');
		$sneaker_id = $request->get_param('sneaker_id');

		if (empty($user_id)) {
			return array(
				'status' => 400,
				'error_message' => 'user_id cannot be NULL',
				'results' => []
			);
		}
		if (empty($sneaker_id)) {
			return array(
				'status' => 400,
				'error_message' => 'sneaker_id cannot be NULL',
				'results' => []
			);
		}

		$sneaker_id = intval($sneaker_id);

		if (get_post_type($sneaker_id) != 'sneaker') {
			return array(
				'status' => 400,
				'error_message' => 'sneaker_id does not belong to sneakers',
				'results' => []
			);
		}

		$sneaker_ids = get_field('saved_sneakers', 'user_' . $user_id);

		if (empty($sneaker_ids)) {
			$sneaker_ids = [];
		}

		array_push($sneaker_ids, $sneaker_id);
		$sneaker_ids = array_unique($sneaker_ids);
		update_field('saved_sneakers', $sneaker_ids, 'user_' . $user_id);

		return array(
			'status' => 200,
			'results' => $sneaker_ids
		);
	}
	public function get_saved_sneakers__cb($request)
	{
		$user_id = $request->get_param('user_id');
		$output_type = $request->get_param('output_type');

		if (empty($user_id)) {
			return array(
				'status' => 400,
				'error_message' => 'user_id cannot be NULL',
				'results' => []
			);
		}

		if (empty($output_type)) {
			$output_type = 'sneaker_ids';
		}

		if (!in_array($output_type, array('sneaker_ids', 'products_data'))) {
			return array(
				'status' => 400,
				'error_message' => 'output_type is invalid',
				'results' => []
			);
		}

		$sneaker_ids = get_field('saved_sneakers', 'user_' . $user_id);

		if (empty($sneaker_ids)) {
			return array(
				'status' => 400,
				'error_message' => 'no saved_sneakers found',
				'results' => []
			);
		}

		$return = $sneaker_ids;

		if ($output_type == 'sneaker_ids') {
			return array(
				'status' => 200,
				'results' => $return
			);
		}

		$sneakers_per_page = $request->get_param('sneakers_per_page');
		$sneakers_page = $request->get_param('sneakers_page');
		$product_type = $request->get_param('product_type');

		if (empty($sneakers_per_page)) {
			$sneakers_per_page = 20;
		}

		if (empty($sneakers_page)) {
			$sneakers_page = 1;
		}

		if (empty($product_type)) {
			$product_type = 't-shirt';
		}

		$sneakers_per_page = intval($sneakers_per_page);
		$sneakers_page = intval($sneakers_page);

		$sneaker_ids = array_slice($sneaker_ids, (($sneakers_page - 1) * $sneakers_per_page), $sneakers_per_page);

		$return = array(
			'__meta' => array(
				'sneakers_per_page' => $sneakers_per_page,
				'sneakers_page' => $sneakers_page,
				'sneaker_ids' => $sneaker_ids,
			),
			'results' => array(),
		);

		if (count($sneaker_ids) == 0) {
			return $return;
		}

		// get sneakers
		$args = array(
			'post_type'   => 'sneaker',
			'post_status' => 'publish',
			'posts_per_page' => $sneakers_per_page,
			'post__in' => $sneaker_ids
		);
		$sneakers = get_posts($args);

		$designs_per_page = count($sneakers);
		$max_design_pages = intval(wp_count_posts('design')->publish / $designs_per_page);
		$design_page = $sneakers_page % $max_design_pages;

		$return['__meta']['designs_per_page'] = $designs_per_page;
		$return['__meta']['design_page'] = $design_page;

		// get designs
		$args = array(
			'post_type'   => 'design',
			'post_status' => 'publish',
			'posts_per_page' => $designs_per_page,
			'paged' => $design_page
		);
		$designs = get_posts($args);

		// render products
		$products = array();
		foreach ($sneakers as $i => $sneaker) {
			$design = $designs[$i];
			$sneaker_image_link = get_the_post_thumbnail_url($sneaker->ID, 'medium');
			if (!$sneaker_image_link) {
				$sneaker_image_link = get_post_meta($sneaker->ID, 'image_link', true);
			}
			$sneaker->image_link = $sneaker_image_link;

			$products[] = MKProductImage($sneaker, $design, $product_type, 93450, true, true);
		}

		$return['status'] = 200;
		$return['results'] = $products;

		return $return;
	}

	public function delete_saved_sneakers__cb($request)
	{
		$user_id = $request->get_param('user_id');
		$sneaker_id = $request->get_param('sneaker_id');
		$delete_type = $request->get_param('delete_type');

		if (empty($user_id)) {
			return array(
				'status' => 400,
				'error_message' => 'user_id cannot be NULL',
				'results' => []
			);
		}

		if (empty($delete_type)) {
			$delete_type = 'delete_one';
		}

		if (!in_array($delete_type, array('delete_one', 'delete_all'))) {
			return array(
				'status' => 400,
				'error_message' => "invalid delete_type '$delete_type'",
				'results' => []
			);
		}

		if ($delete_type == 'delete_all') {
			delete_field('saved_sneakers', 'user_' . $user_id);
			return array(
				'status' => 200,
				'message' => "all saved_sneakers deleted successfully",
				'results' => []
			);
		}

		if (empty($sneaker_id)) {
			return array(
				'status' => 400,
				'error_message' => "sneaker_id cannot be NULL",
				'results' => []
			);
		}

		$sneaker_id = intval($sneaker_id);

		if (get_post_type($sneaker_id) != 'sneaker') {
			return array(
				'status' => 400,
				'error_message' => "sneaker_id does not belong to sneakers",
				'results' => []
			);
		}

		$sneaker_ids = get_field('saved_sneakers', 'user_' . $user_id);

		$delete_index = -1;
		foreach ($sneaker_ids as $i => $_sneaker_id) {
			if ($sneaker_id == $_sneaker_id) {
				$delete_index = $i;
			}
		}

		if ($delete_index == -1) {
			return array(
				'status' => 400,
				'error_message' => "sneaker_id does not exist in saved-list",
				'results' => []
			);
		}

		array_splice($sneaker_ids, $delete_index, 1);
		$sneaker_ids = array_unique($sneaker_ids);
		update_field('saved_sneakers', $sneaker_ids, 'user_' . $user_id);

		return array(
			'status' => 200,
			'message' => "successfully deleted {$sneaker_id}",
			'results' => $sneaker_ids
		);
	}

	public function get_design_pact_product($request)
	{
		$return = array();
		$start_time = microtime(true);

		$product_type = $request->get_param('product_type');
		$sneaker = $request->get_param('sneaker');
		$design = $request->get_param('design');
		$count = $request->get_param('count');

		if (empty($count)) {
			$count = 1;
		}
		for ($i = 0; $i < intval($count); $i++) {
			if ($_GET['old']) {
				$return =  MKProductImage($sneaker, $design, $product_type);
			} else {
				$design_pact_product_instance = new Design_Pact_Product(array(
					'product_type' => $product_type,
					'sneaker' => $sneaker,
					'design' => $design,
				));

				if (count($design_pact_product_instance->wp_error->get_error_messages()) > 0) {
					return $design_pact_product_instance->wp_error->get_error_messages();
				}

				$design_pact_product_instance->get_product();

				$return = $design_pact_product_instance->old_formatted_output;
			}
		}
		$end_time = microtime(true);

		$ret = array(
			'__time' => $end_time - $start_time,
			'res' => $return
		);
		// return 'done1';
		return $ret;
	}

	function rest_api__get_release_date_sneakers__cb($request)
	{
		$apps = array();
		$query = new WP_QUERY(
			array(
				"post_type" => "sneaker",
				"posts_per_page" => 20,
				'meta_key' => 'release_date',
				'orderby' => 'meta_value_num',
				'order' => 'DESC',
				'meta_query' => array(
					'relation' => 'AND', // both of below conditions must match
					array(
						'key' => 'release_date',
						'value' => date("Ymd", strtotime("-90 days")),
						'compare' => '>'
					),
					array(
						'key' => 'release_date',
						'value' => date("Ymd", strtotime("+15 days")),
						'compare' => '<'
					)
				)
			)
		);

		$sneakers = $query->get_posts();

		foreach ($sneakers as $sneaker) {
			$sneakerImg = get_the_post_thumbnail_url($sneaker->ID, 'medium');
			if (!$sneakerImg) {
				$sneakerImg = get_post_meta($sneaker->ID, 'image_link', true);
			}
			$sneaker->image_link = $sneakerImg;
		}

		$return = new WP_REST_Response(
			array(
				'status' => 200,
				'data' => $sneakers,
			),
			200
		);

		return $return;
	}

	function rest_api__get_recent_purchases__cb($request)
	{
		$user_id = $request->get_param('user_id');
		if (empty($user_id)) {
			return;
		}

		$customer_orders = get_posts(array(
			'numberposts' => -1,
			'meta_key'    => '_customer_user',
			'meta_value'  => $user_id,
			'post_type'   => wc_get_order_types(),
			'post_status' => array_keys(wc_get_is_paid_statuses()),

		));

		if (!$customer_orders) {
			return;
		}

		$order_items = array();
		foreach ($customer_orders as $customer_order) {
			$order = wc_get_order($customer_order->ID);
			$items = $order->get_items();
			foreach ($items as $item) {
				$item_data = $item->get_data();
				array_push($order_items, $item_data);
			}
		}

		$return = new WP_REST_Response(
			array(
				'status' => 200,
				'orders_count' => count($customer_orders),
				'order_items_count' => count($order_items),
				'data' => $order_items,
			),
			200
		);

		return $return;
	}

	function rest_api__printful_sync_order_statuses__cb($request)
	{
		$order_number = $request->get_param('order_number');
		$order_id = $request->get_param('order_id');

		if (isset($order_id)) {
			$order_number = get_post_meta($order_id, '_order_number', true);
		}

		if (!isset($order_number)) {
			return array('invalid order_number');
		}

		$pf_api_log_files = array(
			"scripts/printful/pf-api.txt",
			"scripts/printful/pf-api-old.txt",
		);

		$payloads = array();

		foreach ($pf_api_log_files as $pf_api_log_file) {
			$handle = fopen($pf_api_log_file, "r");
			if ($handle) {
				$i = 0;
				while (($line = fgets($handle)) !== false) {
					if (
						1 == 1
						// && str_contains($line, '"type":"package_shipped"')
						// && str_contains($line, '"retries":0')
						&& str_contains($line, '"store":7266153')
					) {
						// if (isset($order_number)) {
						$exp = '"external_id":"' . $order_number . '-';
						if (
							1 == 1
							&& str_contains($line, $exp)
						) {
							array_push($payloads, $line);
						}
						// } else {
						// 	array_push($payloads, $line);
						// }
					}
					$i++;
				}

				fclose($handle);
			}
		}

		$filtered_payloads = array();

		foreach ($payloads as $i => $payload) {
			$payload = json_decode($payload, true);
			$payload_already_exists = false;
			foreach ($filtered_payloads as $j => $f_payload) {
				if (
					1 == 1
					&& $payload['type'] == $f_payload['type']
					&& $payload['data']['order']['id'] == $f_payload['data']['order']['id']
				) {
					$payload_already_exists = true;
					break;
				}
			}
			if (!$payload_already_exists) {
				array_push($filtered_payloads, $payload);
			}
		}

		return $filtered_payloads;
		foreach ($filtered_payloads as $i => $payload) {
			if ($i < 10) {
				var_dump(json_encode($payload));
				echo PHP_EOL . PHP_EOL;
			}
		}
	}
}
