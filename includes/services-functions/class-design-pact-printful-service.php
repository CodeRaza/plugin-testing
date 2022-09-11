<?php

/******************************************************
 * This class is used to manage all the action and
 * filter hooks
 ******************************************************/

use function Crontrol\get_message;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('DPDV_Printful_Service')) :

    class DPDV_Printful_Service
    {

        public $site;

        public $wc_order_id;
        public $wc_order_number;
        public $wc_order;

        public $pf_order_id;
        public $pf_order_exists = false;
        public $pf_order_status = false;
        public $pf_order;
        public $pf_push_attempts = 0;

        public $printful_id;

        public $temp_directory = "scripts/printful/temp";
        public $cached_variations_directory = "scripts/printful/cached-variations";

        public $wp_error;

        public function __construct($order_id = false, $printful_id = false)
        {
            $this->wp_error = new WP_Error();
            $this->site = "https://" . $_SERVER['HTTP_HOST'];

            $this->initialize($order_id, $printful_id);
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
        private function initialize($order_id, $printful_id)
        {
            $this->set_wc_order_id($order_id);
            $this->set_wc_order_number($order_id);

            $this->set_printful_id($printful_id);
            $this->set_pf_order_id();

            $this->set_wc_order();

            $this->set_pf_push_attempts($this->get_pf_push_attempts());
        }

        // SETTERS

        function set_wc_order_id($order_id)
        {
            $order = wc_get_order($order_id);
            if ($order) {
                $this->wc_order_id = $order_id;
            }
        }

        function set_wc_order_number($order_id)
        {
            $this->wc_order_number = get_post_meta($this->wc_order_id, '_order_number', true);
            if (!$this->wc_order_number) {
                $this->wc_order_number = $order_id;
            }
        }

        public function set_wc_order()
        {
            $order = wc_get_order($this->wc_order_id);

            if ($order) {
                $this->wc_order = $order;
            }
        }

        function set_printful_id($printful_id = false)
        {
            if ($printful_id) {
                $this->printful_id = $printful_id;
                return;
            }

            $printful_id = get_post_meta($this->wc_order_id, 'printful_id', true);
            if (!$printful_id) {
                $printful_id = "0123456789";
            }
            $this->printful_id = $printful_id;
        }

        function set_pf_order_id($printful_id = false)
        {
            if ($printful_id) {
                $this->set_printful_id($printful_id);
            }

            $this->pf_order_id = $this->get_wc_order_number() . "-" . $this->get_printful_id();
        }

        public function set_pf_push_attempts($val)
        {
            if (!is_int($val)) {
                return;
            }
            update_post_meta($this->wc_order_id, 'pf_push_attempts', $val);
            $this->pf_push_attempts = $val;
        }

        public function increment_pf_push_attempts($inc)
        {
            if (!$inc) {
                $inc = 1;
            }
            $pf_push_attempts = $this->get_pf_push_attempts();
            $pf_push_attempts = intval($pf_push_attempts);
            $this->set_pf_push_attempts($pf_push_attempts + $inc);
        }

        public function set_pf_order($order)
        {
            $this->pf_order = $order;

            if (isset($this->pf_order) && isset($this->pf_order['id'])) {
                $this->pf_order_exists = true;
            }
            if (isset($this->pf_order) && isset($this->pf_order['status'])) {
                $this->pf_order_status = $this->pf_order['status'];
            }
        }

        // GETTERS

        function get_wc_order_id()
        {
            return $this->wc_order_id;
        }

        function get_wc_order_number()
        {
            return $this->wc_order_number;
        }

        function get_printful_id()
        {
            return $this->printful_id;
        }

        function get_pf_order_id()
        {
            return $this->set_pf_order_id;
        }

        public function get_pf_push_attempts()
        {
            $pf_push_attempts = get_post_meta($this->wc_order_id, 'pf_push_attempts', true);
            if (!$pf_push_attempts) {
                $pf_push_attempts = 0;
                update_post_meta($this->wc_order_id, 'pf_push_attempts', $pf_push_attempts);
            }
            return $pf_push_attempts;
        }

        public function get_pf_order()
        {
            return $this->pf_order;
        }

        // OTHERS

        public function create_pf_order($data, $confirm = '0')
        {
            $url = "https://api.printful.com/orders?confirm=" . $confirm;
            $wp_request_headers = array(
                'Authorization' => 'Basic ' . $this->api_access_token(),
                "cache-control: no-cache",
                "content-type: application/json",
                "postman-token: 60034206-2b76-b158-6a83-a956666da48d",
            );

            $response = wp_remote_post($url, array(
                'method' => 'POST',
                'timeout' => 10,
                'headers' => $wp_request_headers,
                'body' => json_encode($data)
            ));

            $this->increment_pf_push_attempts(1);

            $response_code = wp_remote_retrieve_response_code($response);
            $response_body = json_decode(wp_remote_retrieve_body($response), true);

            if (is_wp_error($response)) {
                return $response;
            }

            if ($response_code != 200) {
                $return = new WP_Error(
                    $response_code,
                    $response_body
                );
                return $return;
            }

            update_post_meta($this->wc_order_id, 'printful_id', $this->printful_id);
            update_post_meta($this->wc_order_id, 'pf_push_date', date("Y-m-d\TH:i:sP"));
            $this->wc_order->update_status('pf-printing');

            return $response;
        }


        public function fetch_pf_order($pf_order_id)
        {
            if ($pf_order_id) {
                $this->pf_order_id = $pf_order_id;
            }

            $url = "https://api.printful.com/orders/@" . $this->pf_order_id;
            $wp_request_headers = array(
                'Authorization' => 'Basic ' . $this->api_access_token(),
                "cache-control: no-cache",
                "content-type: application/json",
                // "postman-token: 60034206-2b76-b158-6a83-a956666da48d",
            );
            $response = wp_remote_get($url, array(
                'method' => 'GET',
                'timeout' => 10,
                'headers' => $wp_request_headers
            ));

            return $response;
        }

        public function cancel_pf_order($pf_order_id)
        {
            if ($pf_order_id) {
                $this->pf_order_id = $pf_order_id;
            }

            $url = "https://api.printful.com/orders/@" . $this->pf_order_id;
            $wp_request_headers = array(
                'Authorization' => 'Basic ' . $this->api_access_token(),
                "cache-control: no-cache",
                "content-type: application/json",
                // "postman-token: 60034206-2b76-b158-6a83-a956666da48d",
            );
            $response = wp_remote_get($url, array(
                'method' => 'DELETE',
                'timeout' => 10,
                'headers' => $wp_request_headers
            ));

            return $response;
        }

        public function get_POST_data()
        {
            $this->wp_error = new WP_Error();
            $order = $this->wc_order;

            $data = array(
                "external_id" => $this->pf_order_id,
                "shipping" => "STANDARD",
                "recipient" => array(
                    "name" => $order->get_formatted_shipping_full_name(),
                    "address1" => $order->get_shipping_address_1(),
                    "address2" => $order->get_shipping_address_2(),
                    "city" => $order->get_shipping_city(),
                    "state_code" => $order->get_shipping_state(),
                    "country_code" => $order->get_shipping_country(),
                    "zip" => $order->get_shipping_postcode(),
                    "phone" => $order->get_billing_phone(),
                    "email" => $order->get_billing_email()
                ),
                "retail_cost" => array(
                    "currency" => $order->get_currency(),
                    "subtotal" => $order->get_subtotal(),
                    "discount" => $order->get_total_discount(),
                    "shipping" => $order->get_shipping_total(),
                    "tax" => $order->get_total_tax(),
                    "vat" => 0,
                    "total" => $order->get_total()
                ),
                "confirm" => true,
                "update_existing" => true
            );

            $items = $this->prepare_order_items_data($order);
            $data['items'] = $items;

            if (sizeof($this->wp_error->get_error_messages()) > 0) {
                return $this->wp_error;
            }

            return $data;
        }


        private function prepare_order_items_data($order)
        {
            $items = array();
            foreach ($order->get_items() as $item_id => $item) {
                if ($item->get_type() != "line_item") {
                    continue;
                }
                if ($item->get_meta("In Order") == "No") {
                    continue;
                }

                $product_id = $item->get_product_id();
                $variation_id = $item->get_variation_id();
                if (!$product_id) {
                    $this->wp_error->add(100, 'product_id is NULL for item_id = ' . $item_id);
                }
                if (!$variation_id) {
                    $this->wp_error->add(100, 'variation_id is NULL for item_id = ' . $item_id);
                }

                $pf_variation_id = $this->get_pf_variation_id($variation_id);
                if (!$pf_variation_id) {
                    $this->wp_error->add(100, 'pf_variation_id is NULL for item_id = ' . $item_id . ' (wc_variation_id = ' . $variation_id . ')');
                } else if (is_wp_error($pf_variation_id)) {
                    $this->wp_error->add(404, $pf_variation_id);
                }

                $design_url = $this->get_design_url($item);

                $items[] = array(
                    "external_id" => $item_id,
                    "variant_id" => (int) $pf_variation_id,
                    "quantity" => $item->get_quantity(),
                    "retail_price" => $item->get_total(),
                    "name" => $item->get_name() . " - " . $item->get_meta("pa_design-id", true),
                    // "options" => [
                    //     array(
                    //         'id' => 'thread_colors_chest_center',
                    //         'value' => ['#FFCC00']
                    //     )
                    // ],
                    "files" => array(
                        array(
                            // "type" => "embroidery_chest_center",
                            "filename" => $item->get_meta("pa_design-id", true),
                            "url" => $design_url
                        )
                    )
                );
            }

            return $items;
        }

        function get_pf_variation_id($wc_variation_id)
        {
            $printful_variation_id = null;

            $product = get_post_meta($wc_variation_id, 'iconic_cffv_107746_printful_product_id', 1);
            if (!$product) {
                $product = get_post_meta($wc_variation_id, 'iconic_cffv_107746_printful_backup_product_id', 1);
            }

            if (!$product) {
                $this->wp_error->add(404, "FAILURE: The variation {$wc_variation_id} does not have a printful product mapped");
                return 0;
            }

            $my_url = $this->site . "/wp-json/dpdv/v1/printful/get-variation?variation_id=" . $product;
            $variation_response = wp_remote_get($my_url, array('timeout' => 10000));

            if (is_wp_error($variation_response)) {
                $this->wp_error->add(404, $variation_response->get_error_message(), $my_url);
                return $variation_response->get_error_message();
            }

            $variation_response['body'] = json_decode(wp_remote_retrieve_body($variation_response), true);
            $variant = $variation_response['body']['body']['result']['variant'];
            if ($variant['in_stock'] === true) {
                $printful_variation_id = $product;
            } else {
                return new WP_Error(404, "Printful product is out-of-stock for the wc_variation_id = {$wc_variation_id}");
            }
            return $printful_variation_id;
        }

        function get_design_url($item, $url_type = "matchkicks")
        {
            if (isset($_GET['url_type'])) {
                $url_type = $_GET['url_type'];
            }

            $temp = microtime();
            if (!isset($_GET['abc']) || $_GET['abc'] == '') {
                $_GET['abc'] =  "30";
            }
            $abcValue = "abc=" . $_GET['abc'];

            // var_dump($item->get_meta());
            $designURL = str_replace("/api/v1", "/api/v3", $item->get_meta("Design", true) . "&designWidth=5000");
            $designURL = remove_wp_upload_base_url($designURL);
            $productType = $item->get_meta("pa_product-type", true);

            if ($productType == "socks" || $productType == "drawstring-bag") {
                // print_r($item);
                $designURL = str_replace("/api/v3", "/api/v4", $designURL);
                $designURL = $designURL . "&backgroundColor=" . $item->get_meta("pa_product-color", true);
            }
            $designURL = str_replace("https://nyc3.digitaloceanspaces.com/matchkicks-s3", "", $designURL);

            $parsedDesignURL = parse_url($designURL);
            parse_str($parsedDesignURL['query'], $parsedQuery);

            $designURL = $parsedDesignURL['scheme'] . "://" . $parsedDesignURL['host'] . $parsedDesignURL['path'] . "?";
            foreach ($parsedQuery as $key => $param) {
                $param = urlencode($param);
                $designURL .= $key . "=" . $param . "&";
            }
            $designURL .= $abcValue;


            // $designFileContents = file_get_contents($designURL);
            $context = stream_context_create(array('ssl' => array(
                'verify_peer' => false,
                // 'cafile' => '/path/to/ca-bundle.crt'
            )));
            $designFileContents = fopen($designURL, 'r', false, $context);
            file_put_contents($this->temp_directory . "/" . $temp . ".png", $designFileContents);

            if (empty($designFileContents)) {
                $this->wp_error->add(100, "Could not fetch Design Image", $designURL);
            }

            if ($url_type == "matchkicks") {
                return "{$this->site}/{$this->temp_directory}/" . $temp . ".png";
            } else {
                return $designURL;
            }
        }

        public function get_pf_variation($pf_variation_id)
        {
            $url = "https://api.printful.com/products/variant/" . $pf_variation_id;

            $wp_request_headers = array(
                'Authorization' => 'Basic ' . $this->api_access_token(),
                "cache-control: no-cache",
                "content-type: application/json",
                // "postman-token: 60034206-2b76-b158-6a83-a956666da48d",
            );
            $response = wp_remote_get($url, array(
                'method' => 'GET',
                'timeout' => 10,
                'headers' => $wp_request_headers
            ));

            if (is_wp_error($response)) {
                return $response;
            }

            $response_code = wp_remote_retrieve_response_code($response);
            $response_body = json_decode(wp_remote_retrieve_body($response), true);

            if ($response_code != 200) {
                $return = new WP_Error(
                    $response_code,
                    $response_body
                );
                return $return;
            }

            $return = $response_body;
            return $return;
        }

        private function api_access_token()
        {
            $dpdv_options = get_option('dpdv_options');
            if (!isset($dpdv_options['pf_api_access_token'])) {
                return 0;
            }

            return $dpdv_options['pf_api_access_token'];
        }

        public function is_pf_order_canceled()
        {
            if (in_array($this->pf_order_status, ['canceled', 'archived'])) {
                return true;
            }
            return false;
        }
        public function is_pf_order_editable()
        {
            if (in_array($this->pf_order_status, ['pending', 'draft'])) {
                return true;
            }
            return false;
        }

        public function send_error_emails($order_number, $message)
        {
            // $message['body']['pf_push_attempts'] = intval($message['body']['pf_push_attempts']);
            // if ($message['body']['pf_push_attempts'] > 3) {
            //     return;
            // }
            // $json_message =  json_encode($message);

            // $emails = array(
            //     get_option('admin_email'),
            //     "shahzaddev125@gmail.com"
            // );

            // foreach ($emails as $email) {
            //     wc_mail($email, "Failed to push order Number " . $order_number . " to printful", $json_message);
            // }

            // Pushing this data to CLICKUP now instead of emails directly to us!

            $data = array(
                "order_number" => $order_number,
                "order_id" => $message['body']['order_id'],
                "all" => $message
            );


            if (intval($message['body']['pf_push_attempts']) != 3) {
                return;
            }

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://flow.zoho.com/773955203/flow/webhook/incoming?zapikey=1001.ce40dcd09b36504b5e920fd4944699e3.044231348d5fd243196a76561456c29e&isdebug=false',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Cookie: 9444ce7cc2=77ee58e1fe5359ceb43eae6a2ca0dd89; _zcsr_tmp=5da08e1a-aeb2-4589-888a-43bf86d1508b; zipccn=5da08e1a-aeb2-4589-888a-43bf86d1508b'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
        }

        public function get_printful_products()
        {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.printful.com/products/",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                // CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => array(
                    "authorization: Basic " . $this->api_access_token(),
                    "cache-control: no-cache",
                    "content-type: application/json",
                    "postman-token: 60034206-2b76-b158-6a83-a956666da48d"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                dv_response_error(400, "cURL Error #:" . $err);
            }

            $products = json_decode($response, 1)['result'];

            return $products;
        }

        public function get_printful_single_product($id)
        {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.printful.com/products/$id",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                // CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => array(
                    "authorization: Basic " . $this->api_access_token(),
                    "cache-control: no-cache",
                    "content-type: application/json",
                    "postman-token: 60034206-2b76-b158-6a83-a956666da48d"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                dv_response_error(400, "cURL Error #:" . $err);
            }

            $product = json_decode($response, 1)['result'];

            return $product;
        }
    }
endif;
