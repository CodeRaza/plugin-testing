<?php

/******************************************************
 * This class is used to manage all the action and
 * filter hooks
 ******************************************************/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('DPDV_Twilio_Service')) :

    class DPDV_Twilio_Service
    {

        public $site;

        public $wp_error;

        public $twilio_directory = "scripts/twilio";


        public function __construct()
        {
            $this->wp_error = new WP_Error();

            $this->initialize();
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


        function send_promo()
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
                    'post_status'      => $s,
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

        function save_log($json)
        {
            file_put_contents($this->twilio_directory . '/twilio.txt', $json, FILE_APPEND);
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
