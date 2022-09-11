<?php

/******************************************************
 * This class is used to manage all the action and
 * filter hooks
 ******************************************************/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('DPDV_WooCommerce_Service')) :

    class DPDV_WooCommerce_Service
    {

        /**
         * The unique instance of the class.
         *
         * @var DPDV_WooCommerce_Service
         */
        private static $instance;

        private function __construct()
        {
            $this->initialize();
        }

        /**
         * Gets an instance of the class.
         *
         * @return DPDV_WooCommerce_Service
         */

        public static function get_instance()
        {
            if (null === self::$instance) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        /**
         * initialize
         *
         * Sets up the class.
         *
         * @date    11/04/22
         * @since   0.0.1
         *
         * @param   void
         * @return  void
         */
        private function initialize()
        {
        }


        public function wc_add_item_to_cart($variationID, $args)
        {
            if (!isset($args['designID'])) {
                echo "designID cannot be NULL";
                return;
            }
            if (!isset($args['shoeID'])) {
                echo "shoeID cannot be NULL";
                return;
            }
            if (!isset($args['newDesign'])) {
                echo "newDesign cannot be NULL";
                return;
            }
            if (!isset($args['shoeDesign'])) {
                echo "shoeDesign cannot be NULL";
                return;
            }

            // Optional fields
            if (!isset($args['qty'])) {
                $args['qty'] = 1;
            }


            $designID = $args['designID'];
            $shoeID = $args['shoeID'];
            $newDesign = $args['newDesign'];
            $shoeDesign = $args['shoeDesign'];
            $qty = $args['qty'];

            $cart = WC()->cart;


            //need to replace the quotes with the encoded value
            $cleanedDesign = stripslashes($newDesign);
            $replaceQuotes = str_replace('"', "%22", $cleanedDesign);

            //add new item with the data of the last one
            $custom_data = [
                "attribute_pa_shoe-design" => $shoeDesign,
                "attribute_pa_product-design" => $replaceQuotes,
                "attribute_pa_shoeID" => $shoeID,
                "attribute_pa_designID" => $designID
            ];

            var_dump(array('args' => $args, 'custom_data' => $custom_data));
            $dpdv_plugin_options = get_option('dpdv_options');
            if (!isset($dpdv_plugin_options['field_wc_product_id'])) {
                return 0;
            }

            // override $product_id --> added for plugin support
            $product_id = $dpdv_plugin_options['field_wc_product_id'];

            $cart->add_to_cart($product_id, $qty, $variationID, [], [
                "custom_data" => $custom_data
            ]);
        }

        function wc_add_items_to_cart($cart_items_data)
        {
            foreach ($cart_items_data as $item_data) {
                if (!isset($item_data['qty'])) {
                    $item_data['qty'] = 1;
                }

                $variationID = $item_data["variationID"];
                $designID = $item_data["designID"];
                $shoeID = $item_data["shoeID"];
                $newDesign = $item_data['newDesign'];
                $shoeDesign = $item_data["shoeDesign"];
                $qty = $item_data['qty'];

                $this->wc_add_item_to_cart($variationID, array(
                    'designID' => $designID,
                    'shoeID' => $shoeID,
                    'newDesign' => $newDesign,
                    'shoeDesign' => $shoeDesign,
                    'qty' => $qty,
                ));
            }
        }

        function wc_get_product_variations($product_id, $args)
        {

            // override $product_id --> added for plugin support
            $product_id = mkdv_get_wc_main_product_id_with_variations();
            if (!$product_id) {
                return [];
            }


            if (!isset($args['productType'])) {
                $args['productType'] = 'any-product-type';
            }
            if (!isset($args['productColor'])) {
                $args['productColor'] = 'any-product-color';
            }
            if (!isset($args['productSize'])) {
                $args['productSize'] = 'any-product-size';
            }

            $args['productType'] = strtolower($args['productType']);
            $args['productColor'] = strtolower($args['productColor']);
            $args['productSize'] = strtolower($args['productSize']);

            $qProductType = $args['productType'];
            $qProductColor = $args['productColor'];
            $qProductSize = $args['productSize'];

            $handle = new WC_Product_Variable($product_id);
            $variations = $handle->get_children();
            $actualVariations = array();

            $typeTerms = get_terms('pa_product-type');
            $colorTerms = get_terms('pa_product-color');
            $sizeTerms = get_terms('pa_product-size');

            // Loop through all possible variations in the database
            foreach ($variations as $x => $value) {
                $single_variation = new WC_Product_Variation($value);
                $v = $single_variation->get_variation_attributes();

                $temp = array();
                $temp['id'] = $value;

                // attribute-type
                if ($v['attribute_pa_product-type'] == $qProductType || $qProductType == "any-product-type") {
                    $temp['type-slug'] = $v['attribute_pa_product-type'];
                    foreach ($typeTerms as $typeTerm) {
                        if ($typeTerm->slug == $v['attribute_pa_product-type']) {
                            $temp['type-name'] = $typeTerm->name;
                        }
                    }
                }

                // attribute-color
                if ($v['attribute_pa_product-color'] == $qProductColor || $qProductColor == "any-product-color") {
                    $temp['color-slug'] = $v['attribute_pa_product-color'];
                    foreach ($colorTerms as $colorTerm) {
                        if ($colorTerm->slug == $v['attribute_pa_product-color']) {
                            $temp['color-name'] = $colorTerm->name;
                        }
                    }
                }

                // attribute-size
                if ($v['attribute_pa_product-size'] == $qProductSize || $qProductSize == "any-product-size") {
                    $temp['size-slug'] = $v['attribute_pa_product-size'];
                    foreach ($sizeTerms as $sizeTerm) {
                        if ($sizeTerm->slug == $v['attribute_pa_product-size']) {
                            $temp['size-name'] = $sizeTerm->name;
                        }
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

                    array_push($actualVariations, $temp);
                }
            }
            $out = array();
            $out['actualVariations'] = $actualVariations;
            $out['sizeTerms'] = $sizeTerms;

            return $actualVariations;
        }

        function wc_get_product_variation($product_id, $args)
        {
            $final_variation = array();

            // override $product_id --> added for plugin support
            $product_id = mkdv_get_wc_main_product_id_with_variations();
            if (!$product_id) {
                return [];
            }

            $args['productType'] = strtolower($args['productType']);
            $args['productColor'] = strtolower($args['productColor']);
            $args['productSize'] = strtolower($args['productSize']);

            $qProductType = $args['productType'];
            $qProductColor = $args['productColor'];
            $qProductSize = $args['productSize'];


            $match_attributes =  array(
                "attribute_pa_product-type" => $qProductType,
                "attribute_pa_product-color" => $qProductColor,
                "attribute_pa_product-size" => $qProductSize,
            );

            $data_store   = WC_Data_Store::load('product');
            $variation_id = $data_store->find_matching_product_variation(
                new \WC_Product($product_id),
                $match_attributes
            );

            // find variation
            $single_variation = new WC_Product_Variation($variation_id);
            $v = $single_variation->get_variation_attributes();
            $typeTerms = get_terms('pa_product-type');
            $colorTerms = get_terms('pa_product-color');
            $sizeTerms = get_terms('pa_product-size');


            $temp = array();
            $temp['id'] = $variation_id;
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

        /**
         * Gets all product types
         */

        public function wc_get_product_types()
        {

            $productTypeTerms = get_terms('pa_product-type');

            $temp = array();
            foreach ($productTypeTerms as $pTypeTerm) {
                $temp[] = $pTypeTerm->to_array();
            }

            return $temp;
        }

        public function wc_wipe_order_amount($order_id)
        {

            $order = wc_get_order($order_id); // The WC_Order object instance

            // Loop through Order items ("line_item" type)
            foreach ($order->get_items() as $item_id => $item) {
                // The new line item price
                $new_line_item_price = 0;

                // Set the new price
                $item->set_subtotal($new_line_item_price);
                $item->set_total($new_line_item_price);

                // Make new taxes calculations
                $item->calculate_taxes();

                $item->save(); // Save line item data
            }

            foreach ($order->get_items('fee') as $item_id => $item) {
                $order->remove_item($item_id);
            }
            foreach ($order->get_items('shipping') as $item_id => $item) {
                $order->remove_item($item_id);
            }
            // Make the calculations  for the order and SAVE
            $order->calculate_totals();
        }
    }

endif;
