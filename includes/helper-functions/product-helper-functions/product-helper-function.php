<?php

/******************************************************
 * This class is used to manage all the action and
 * filter hooks
 ******************************************************/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('DV_Product_Helpers')) :

    class DV_Product_Helpers{
        /**
         * The unique instance of the class.
         *
         * @var DV_Product_Helpers
         */
        private static $instance;

        public $woocommerce_service;


        private function __construct()
        {
            $this->initialize();
        }

        /**
         * Gets an instance of the class.
         *
         * @return DV_Product_Helpers
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
            $this->woocommerce_service = DPDV_WooCommerce_Service::get_instance();
        }

        /**
         * Gets humanoid mockup images based on the current product_type and product_color
         */

        public function get_humanoid_mockup_images($product_type, $product_image)
        {
            $live_mock_appends = [];
            $live_mock_appends = mkdv_get_valid_mock_appends($product_type, true);

            $humanoid_mockups = [];
            array_push($humanoid_mockups, array(
                'slug' => $product_type,
                'image' => $product_image,
            ));
            foreach ($live_mock_appends as $i => $val) {
                $slug = $product_type . "-" . $val;
                $image = str_replace("mockupType=" . $product_type, "mockupType=$slug", $product_image);

                array_push($humanoid_mockups, array(
                    'slug' => $slug,
                    'image' => $image,
                ));
            }

            // get size-chart
            $typeTerms = get_terms('pa_product-type');
            foreach ($typeTerms as $st) {
                if ($st->slug == $product_type) {
                    $attachment_id = get_term_meta($st->term_id, 'size_chart', true);
                    $attachment_image = wp_get_attachment_url($attachment_id);
                    $size_chart = array(
                        'slug' => 'size-chart',
                        'image' => $attachment_image
                    );
                }
            }

            if ($size_chart['image']) {
                $humanoid_mockups = array_merge(array_slice($humanoid_mockups, 0, 2), array($size_chart), array_slice($humanoid_mockups, 2));
            }

            return $humanoid_mockups;
        }

        /**
         * This gets all products from the available product-types in Product Variations
         */
        function get_products_from_all_product_types($product_types, $dv_product)
        {
            $dv_products = array();
            foreach ($product_types as $i => $p_type) {
                $temp_dv_product = clone ($dv_product);
                $temp_dv_product->query_params['product_type'] = $p_type['slug'];

                $new_product_image = str_replace(
                    "mockupType=" . $dv_product->query_params['product_type'],
                    "mockupType=" . $temp_dv_product->query_params['product_type'],
                    $dv_product->image
                );

                if ($p_type['slug'] == $dv_product->query_params['product_type']) {
                    $temp_dv_product->is_selected = true;
                }

                $new_url = get_current_url();
                $new_url = change_url_parameter($new_url, "productType", $temp_dv_product->query_params['product_type']);
                $new_url = change_url_parameter($new_url, "product", $temp_dv_product->query_params['product_type']);

                $temp_dv_product->image = $new_product_image;
                $temp_dv_product->url = $new_url;
                $temp_dv_product->variation = $temp_dv_product->get_variation();

                $does_variation_exist = mkdv_find_matching_product_variation_id(45, array(
                    'pa_product-color' => $temp_dv_product->query_params['product_color'],
                    'pa_product-type' => $temp_dv_product->query_params['product_type'],
                ));

                if ($does_variation_exist != 0) {
                    $dv_products[] = $temp_dv_product;
                }
            }

            return $dv_products;
        }

        /**
         * Frequently Bought Together Items
         */

        public function frequently_bought_together($product_type_slug, $product_types, $dv_product)
        {
            $fbt_product_types = array();
            $fbt_product_types[0] = array();

            foreach ($product_types as $the_product_type) {
                if (in_array($the_product_type['slug'], [$product_type_slug])) {
                    $fbt_product_types[0] = $the_product_type;
                }
                if (in_array($product_type_slug, ['t-shirt', 'hoodie', 'crop-top'])) {
                    if (in_array($the_product_type['slug'], ['socks', 'gym-bag'])) {
                        $fbt_product_types[] = $the_product_type;
                    }
                } else if (in_array($product_type_slug, ['socks'])) {
                    if (in_array($the_product_type['slug'], ['t-shirt', 'gym-bag'])) {
                        $fbt_product_types[] = $the_product_type;
                    }
                } else if (in_array($product_type_slug, ['gym-bag'])) {
                    if (in_array($the_product_type['slug'], ['t-shirt', 'socks'])) {
                        $fbt_product_types[] = $the_product_type;
                    }
                }
            }

            $dv_products = $this->get_products_from_all_product_types($fbt_product_types, $dv_product);

            return $dv_products;
        }

        /**
         * Products in a collection
         */

        public function get_products_in_collection($sneaker_slug, $design_slug, $product_type)
        {
            $dv_products = array();

            // get design
            $args = array(
                'name'        => $design_slug,
                'post_type'   => 'design',
                'post_status' => 'publish',
                'numberposts' => 1
            );
            $design = get_posts($args)[0];

            // get collection
            $term = get_the_terms($design, 'design_category')[0];

            // Actually order by RAND Please 
            $q2 = array(
                'post_type' => 'design',
                'posts_per_page' => 64,
                'order' => 'ASC',
                // Make sure to show designs that are only "MATCH KICKS" Brand
                'meta_key' => 'brand',
                'meta_value' => '"93450"',
                'meta_compare' => 'LIKE'
            );

            if ($term->slug) {
                $q2['tax_query'] = array(
                    array(
                        'taxonomy' => 'design_category',
                        'field'    => 'slug',
                        'terms'    => $term->slug,
                    ),
                );
            }

            $designs_loop = new WP_Query($q2);
            $designs = $designs_loop->posts;
            shuffle($designs);

            $i = 0;
            foreach ($designs as $newDesign) {
                if ($i < 16) {

                    // in_collection_products
                    $dv_product = new DV_Product(array(
                        'sneaker_slug' => $sneaker_slug,
                        'design_slug' => $newDesign->post_name,
                        'product_type' => $product_type,
                        'should_load_variations' => false,
                    ));

                    unset($dv_product->variations);

                    $dv_products[] = $dv_product;

                    $i++;
                }
            }

            return $dv_products;
        }

        /**
         * Products related to the current product
         */

        public function get_related_products($sneaker_slug, $design_slug)
        {
            $dv_products = array();

            // get design
            $args = array(
                'name'        => $design_slug,
                'post_type'   => 'design',
                'post_status' => 'publish',
                'numberposts' => 1
            );
            $design = get_posts($args)[0];

            // get collection
            $term = get_the_terms($design, 'design_category')[0];

            // Actually order by RAND Please 
            $q2 = array(
                'post_type' => 'design',
                'posts_per_page' => 64,
                'order' => 'ASC',
                // Make sure to show designs that are only "MATCH KICKS" Brand
                'meta_key' => 'brand',
                'meta_value' => '"93450"',
                'meta_compare' => 'LIKE'
            );

            if ($term->slug) {
                $q2['tax_query'] = array(
                    array(
                        'taxonomy' => 'design_category',
                        'field'    => 'slug',
                        'terms'    => $term->slug,
                    ),
                );
            }

            $designs_loop = new WP_Query($q2);
            $designs = $designs_loop->posts;
            shuffle($designs);

            $i = 0;
            foreach ($designs as $newDesign) {
                if ($i < 16) {

                    $product_types = $this->woocommerce_service->wc_get_product_types();
                    $random_type = $product_types[rand(0, sizeof($product_types) - 1)]['slug'];

                    // in_collection_products
                    $dv_product = new DV_Product(array(
                        'sneaker_slug' => $sneaker_slug,
                        'design_slug' => $newDesign->post_name,
                        'product_type' => $random_type,
                        'should_load_variations' => false,
                    ));

                    unset($dv_product->variations);

                    $dv_products[] = $dv_product;

                    $i++;
                }
            }

            return $dv_products;
        }

        /**
         * Videos related to the current and related products
         */

        public function get_related_videos($args)
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

            $q2 = array(
                'post_type' => 'customer_videos',
                'posts_per_page' => 64,
                'order' => 'ASC',
            );
            $customer_videos_loop = new WP_Query($q2);
            $customer_videos = $customer_videos_loop->posts;

            $videos = [];
            foreach ($customer_videos as $i => $customer_vid) {
                $customer_videos[$i]->file_id = get_post_meta($customer_vid->ID, 'file', true);
                $customer_videos[$i]->file_url = wp_get_attachment_url(($customer_videos[$i]->file_id
                ));

                $videos[] = $customer_videos[$i]->file_url;
            }

            return $videos;
        }

             /**
         * Recently viewed products data
         */

        public function get_user_specific_products_data($args)
        {

            if (!isset($args['user_id'])) {
                echo "user_id cannot be NULL";
                return;
            }

            $user_id = $args['user_id'];
            $meta_key = $args["meta_key"];

            if (!isset($meta_key)) {
                $meta_key = 'recently_viewed_products_data';
            }


            $metas = get_user_meta($user_id, $meta_key);

            $metas = array_slice($metas, -50, 50, true);

            $metas = array_reverse($metas);

            if (isset($args['include_sneaker_object']) && $args['include_sneaker_object'] == true) {
                foreach ($metas as $i => $meta) {
                    $sneakers = get_posts(array(
                        'name' => $meta['sneaker_slug'],
                        'post_type'   => 'sneaker',
                        'numberposts' => 1
                    ));

                    if (count($sneakers) == 0) {
                        continue;
                    }

                    $sneaker = $sneakers[0];
                    $sneaker_image = get_the_post_thumbnail_url($sneaker->ID, 'medium');
                    if (!$sneaker_image) {
                        $sneaker_image = get_post_meta($sneaker->ID, 'image_link', true);
                    }

                    $sneaker->image_link = $sneaker_image;
                    $meta['sneaker'] = $sneaker;

                    $metas[$i] = $meta;
                }
            }

            return $metas;
        }

        /**
         * Get Products from Products data
         */

        public function get_products_from_data($products_data)
        {

            $dv_products = array();

            foreach ($products_data as $pd) {
                $dv_product = new DV_Product(array(
                    'sneaker_slug' => $pd['sneaker_slug'],
                    'design_slug' => $pd['design_slug'],
                    'product_type' => $pd['product_type'],
                    'should_load_variations' => false,
                ));

                unset($dv_product->variations);

                $dv_products[] = $dv_product;
            }
            return $dv_products;
        }

        function generate_lookup_message($data)
        {
            global $wpdb;
            if ($data['secret_key'] == "hjfeui2whr2ihr213io4u") {

                if ($data['lookup_type'] == 'name') {
                    $phone = ltrim(preg_replace('/[^0-9]/', '', $data['lookup']), '1');
                    $orderID = $wpdb->get_results("SELECT `post_id` FROM `mk_postmeta` WHERE `meta_key` = '_billing_phone' AND `meta_value` LIKE \"%$phone%\" ORDER BY `meta_id` DESC LIMIT 1")[0]->post_id;

                    if ($orderID) {
                        return get_post_meta($orderID, '_billing_first_name', true);
                    } else {
                        return "There";
                    }
                }

                if (!empty($data['lookup']) && strlen($data['lookup']) < 10) {

                    $orderID = $wpdb->get_results("SELECT `post_id` FROM `mk_postmeta` WHERE `meta_key` = '_order_number' AND `meta_value` =  " . $data['lookup'])[0]->post_id;
                    //return $orderID;

                }

                if (!empty($data['lookup']) && strlen($data['lookup']) > 9) {
                    $phone = ltrim(preg_replace('/[^0-9]/', '', $data['lookup']), '1');
                    $orderID = $wpdb->get_results("SELECT `post_id` FROM `mk_postmeta` WHERE `meta_key` = '_billing_phone' AND `meta_value` LIKE \"%$phone%\" ORDER BY `meta_id` DESC LIMIT 1")[0]->post_id;

                    if ($orderID) {
                        $message = "We found a recent order number. " . implode(' ', str_split(get_post_meta($orderID, '_order_number', true))) . ". from your phone number. ";
                    }
                }

                if (empty($orderID)) {
                    $message = "Sorry, we couldn't find an order with the number #" . implode(' ', str_split($data['lookup']));
                } else {
                    $order = wc_get_order($orderID);


                    $message .= $order->get_billing_first_name();

                    $message .= ", Your order was created on " . date("F j, Y", strtotime($order->get_date_created())) . " and is currently " . str_replace("pf-printing", "Printing", $order->get_status());

                    $order_notes = get_private_order_notes($orderID);
                    foreach ($order_notes as $note) {
                        if (strpos($note['note_content'], "shipped via")) {
                            $note['note_content'] = strstr($note['note_content'], "shipped via");
                        }
                        if (get_comment_meta($note['note_id'], 'is_customer_note', true) == '1') {
                            $message .= ". On " . date("F j", strtotime($note['note_date'])) . " " . $note['note_content'];
                            $atLeastOneStatusUpdate = true;
                        }
                    }

                    if (!$atLeastOneStatusUpdate) {
                        $message .= " - That is all we know for now. Please check your email or call in again after a few days for more information.";
                    }


                    $order->add_order_note("Customer used our phone number to lookup the status of their order.");
                }
            } else {
                $message = "There was an authentication error with our server. Please contact us another way such as live chat on our website for assistance with this order.";
            }
            return $message;
        }
    }

endif;