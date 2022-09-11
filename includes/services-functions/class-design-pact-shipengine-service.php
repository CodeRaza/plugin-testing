<?php

/******************************************************
 * This class is used to manage all the action and
 * filter hooks
 ******************************************************/

use function Crontrol\get_message;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('DPDV_ShipEngine_Service')) :

    class DPDV_ShipEngine_Service
    {


        public function __construct()
        {
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

        public function subscribe($url_vars)
        {
            $curl = curl_init();

            // Subscribe to shipment notifcations via our ShipEngine account!
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.shipengine.com/v1/tracking/start?' . $url_vars,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_HTTPHEADER => array(
                    'API-Key: ' . $this->api_key()
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            return $response;
        }

        public function send_return_label($data)
        {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.shipengine.com/v1/labels',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => array(
                    'API-Key: ' . $this->api_key(),
                    'Content-Type: application/json'
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);

            return $response;
        }

        public function api_key()
        {
            // u3hxiLLBL9GTqXOAMSXPDwY8QfsJfccVUai1i05d3JQ
            $dpdv_options = get_option('dpdv_options');
            if (!isset($dpdv_options['shipengine_api_key'])) {
                return 0;
            }

            return $dpdv_options['shipengine_api_key'];
        }
    }
endif;
