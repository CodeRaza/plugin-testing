<?php

/******************************************************
 * This Class contains helper functions
 ******************************************************/


class DV_Product
{

    public $query_params = array(
        'sneaker_slug'   => '',
        'design_slug'    => '',
        'product_type'   => 't-shirt',
    );

    public $title = '';
    public $description = '';
    public $image = '';
    public $design_image = '';
    public $url = '';


    public $average_rating = '';
    public $rating_count = '';


    public $sneaker_title = '';
    public $design_title = '';
    public $design_artist = '';

    public $price = '';
    public $pre_decimal_price = 34;
    public $post_decmial_price = 99;
    public $compare_at_price = 99;
    public $discount = 21;
    public $delivery_date = '';
    public $order_within = '';
    public $weight = '';
    public $manufacturer = '';

    public $sneaker;
    public $design;
    public $variation;

    public $design_categories = null;

    public function __construct($args)
    {
        if (isset($args['sneaker_slug'])) {
            $this->query_params['sneaker_slug']  = $args['sneaker_slug'];
        }
        if (isset($args['design_slug'])) {
            $this->query_params['design_slug']  = $args['design_slug'];
        }
        if (isset($args['product_type'])) {
            $this->query_params['product_type']  = $args['product_type'];
        }

        if (isset($args['should_load_variations'])) {
            $this->query_params['should_load_variations']  = $args['should_load_variations'];
        } else {
            $this->query_params['should_load_variations']  = true;
        }

        $this->init();
    }

    public function init()
    {

        global $dv_wc_helpers;

        // get sneaker
        $args = array(
            'name'        => $this->query_params['sneaker_slug'],
            'post_type'   => 'sneaker',
            'post_status' => 'publish',
            'numberposts' => 1
        );
        $sneakers = get_posts($args);
        if (!is_countable($sneakers) || count($sneakers) == 0) {
            return null;
        }
        $this->sneaker = $sneakers[0];
        $this->sneaker->image = get_the_post_thumbnail_url($this->sneaker->ID, "medium");
        $this->sneaker_title = $this->sneaker->post_title;

        // get design
        $args = array(
            'name'        => $this->query_params['design_slug'],
            'post_type'   => 'design',
            'post_status' => 'publish',
            'numberposts' => 1
        );
        $designs = get_posts($args);
        if (!is_countable($designs) || count($designs) == 0) {
            return null;
        }
        $this->design = $designs[0];
        $this->design_title = $this->design->post_title;

        // find mk_product_image
        $mk_product_image = MKProductImage($this->sneaker, $this->design, $this->query_params['product_type'], false, true, false);
        $this->image = $mk_product_image['preview'];
        $mk_product_image['png'] = str_replace("v1/convertSvgToPng", "v4/convertSvgToPng", $mk_product_image['png']);
        $mk_product_image['png'] = str_replace("v3/convertSvgToPng", "v4/convertSvgToPng", $mk_product_image['png']);
        $this->design_image = $mk_product_image['png'];
        $this->query_params['product_color'] = strtolower($mk_product_image['colorShirtName']);

        // get variations
        if ($this->query_params['should_load_variations']) {
            $this->variations = $this->get_variations();
            $this->variation = $this->get_variation();

            $price_parts = explode(".", $this->variation['price']);
            $this->pre_decimal_price = $this->variation['price'];
            $this->post_decimal_price = 99;

            if (isset($price_parts) && sizeof($price_parts) > 1) {
                $this->pre_decimal_price = $price_parts[0];
                $this->post_decimal_price = $price_parts[1];
            }

            $this->discount = round($this->pre_decimal_price * 0.5) + 9;

            $this->compare_at_price = round($this->pre_decimal_price / (1 - ($this->discount / 100)));
        }

        $this->delivery_date = date("l, F jS", strtotime("+7 weekdays"));

        $currentHour = date("H", time());

        if ($currentHour > 12) {
            $differenceMax = 13;
        } else {
            $differenceMax = 24;
        }

        date_default_timezone_set("America/New_York");
        $diff = abs(strtotime(date("Y-m-d", time()) . " " . $differenceMax . ":00:00") - time());

        // Convert $diff to minutes
        $tmins = $diff / 60;

        // Get hours
        $hours = floor($tmins / 60);

        // Get minutes
        $mins = $tmins % 60;


        $this->order_within = "$hours hrs $mins mins";

        $this->set_title();
        $this->set_description();
        $this->product_id = 's-' . $this->sneaker->ID . '-d-' . $this->design->ID . '-' . strtolower($this->query_params['product_color']) . '-' . $this->query_params['product_type'];
        $this->url = "/product/match/?sneaker=" . $this->sneaker->post_name . "&design=" . $this->design->post_name . "&product=" . $this->query_params['product_type'];
    }

    public function get_variations()
    {
        global $dpdv_woocommerce_service;
        $dpdv_woocommerce_service = DPDV_WooCommerce_Service::get_instance();

        $all_variations = $dpdv_woocommerce_service->wc_get_product_variations(45, array(
            // 'productType' => $this->query_params['product_type'],
            // 'productColor' => $this->query_params['product_color'],
        ));

        foreach ($all_variations as $the_variation) {
            if ($the_variation['color-slug'] == $this->query_params['product_color']) {
                $variations[] = $the_variation;
            }
        }

        $this->all_variations = $all_variations;

        return $variations;
    }

    public function get_variation()
    {

        $final_variation = array();
        foreach ($this->variations as $the_variation) {
            if (
                $the_variation['type-slug'] == $this->query_params['product_type'] &&
                $the_variation['pre-selected'] == true
            ) {
                $final_variation = $the_variation;
            }
        }

        return $final_variation;
    }

    public function set_default_product_size()
    {
        $default_size = null;
        if (!empty($_SESSION['size'])) {
            $default_size = $_SESSION['size'];
            return $default_size;
        }

        if (in_array($this->query_params['product_type'], ['hoodie', 't-shirt'])) {
            $default_size = 'adult-large';
        } else if (in_array($this->query_params['product_type'], ['crop-top'])) {
            $default_size = 'womens-medium';
        } else if (in_array($this->query_params['product_type'], ['socks'])) {
            $default_size = 'medium';
        } else if (in_array($this->query_params['product_type'], ['gym-bag'])) {
            $default_size = 'one-size';
        }

        $this->query_params['product_size'] = $default_size;
    }

    public function get_design_category()
    {
        if ($this->design_categories == null) {
            $terms = get_the_terms($this->design, 'design_category');
            $this->design_categories = $terms;
        }

        if (!is_countable($this->design_categories)) {
            return null;
        }
        if (sizeof($this->design_categories) == 0) {
            return null;
        }

        $term = $this->design_categories[0];
        return $term;
    }

    public function set_title()
    {
        if (!isset($this->variation['type-name'])) {
            $this->variation['type-name'] = "T Shirt";
        }
        $this->title = "{$this->variation['type-name']} - {$this->sneaker->post_title} - Sneaker-Matching {$this->variation['type-name']} ({$this->design->post_title})";
    }

    public function set_description()
    {
        if (!isset($this->variation['type-name'])) {
            $this->variation['type-name'] = "T Shirt";
        }
        $this->description = $this->title . " is a high quality sneaker-matching " . $this->variation['type-name'] . " designed to match your " . $this->sneaker->post_title . " sneakers. This Tee is designed with the exact colors to match with a premium look and feel. We only use the best materials and inks to produce our merchandise. All sizes are true to size.";
        $this->description = "Buy our {$this->title}. Regular fit and Casual wear {$this->variation['type-name']}'s. Enjoy unique custom design and Hassle free return.";
    }
}
