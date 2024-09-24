<?php
/**
 * Plugin Name: VisaNet Direct Payment Gateway
 * Description: Custom payment gateway for VisaNet Direct Payment.
 * Version: 1.0
 * Author: Your Name
 * License: GPL2
 */

defined('ABSPATH') || exit;

add_action('plugins_loaded', 'visanet_custom_gateway_init');

function visanet_custom_gateway_init() {
    if (!class_exists('WC_Payment_Gateway')) return;

    class WC_Gateway_VisaNet_Custom extends WC_Payment_Gateway {

        public function __construct() {
            $this->id = 'visanet_custom';
            $this->icon = ''; // URL to the icon
            $this->has_fields = true;
            $this->method_title = 'VisaNet Direct Payment';
            $this->method_description = 'Allows payments via VisaNet Direct Payment.';

            $this->init_form_fields();
            $this->init_settings();

            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->merchant_id = $this->get_option('merchant_id');
            $this->api_key = $this->get_option('api_key');

            add_action('woocommerce_receipt_visanet_custom', array($this, 'receipt_page'));
            add_action('woocommerce_api_wc_gateway_visanet_custom', array($this, 'check_response'));
        }

        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title'   => 'Enable/Disable',
                    'type'    => 'checkbox',
                    'label'   => 'Enable VisaNet Direct Payment',
                    'default' => 'no',
                ),
                'title' => array(
                    'title'       => 'Title',
                    'type'        => 'text',
                    'description' => 'Title for the payment method displayed at checkout.',
                    'default'     => 'Credit Card (VisaNet)',
                ),
                'description' => array(
                    'title'       => 'Description',
                    'type'        => 'textarea',
                    'description' => 'Description displayed at checkout.',
                    'default'     => 'Pay securely using your credit card via VisaNet.',
                ),
                'merchant_id' => array(
                    'title'       => 'VisaNet Merchant ID',
                    'type'        => 'text',
                    'description' => 'Your VisaNet Merchant ID.',
                ),
                'api_key' => array(
                    'title'       => 'VisaNet API Key',
                    'type'        => 'text',
                    'description' => 'Your VisaNet API Key.',
                ),
            );
        }

        public function process_payment($order_id) {
            global $woocommerce;

            $order = wc_get_order($order_id);

            // Create the VisaNet payment request here
            $response = $this->send_payment_request($order);

            if ($response['success']) {
                // Mark order as complete
                $order->payment_complete();
                $woocommerce->cart->empty_cart();

                // Return thank you page redirect
                return array(
                    'result' => 'success',
                    'redirect' => $this->get_return_url($order),
                );
            } else {
                // Return an error message
                wc_add_notice($response['message'], 'error');
                return array('result' => 'fail');
            }
        }

        private function send_payment_request($order) {
            // Example payment request to VisaNet API
            $endpoint = 'https://api.visanet.com/payment'; // Example endpoint
            $data = array(
                'merchant_id' => $this->merchant_id,
                'api_key' => $this->api_key,
                'amount' => $order->get_total(),
                'currency' => get_woocommerce_currency(),
                // Other required parameters
            );

            $response = wp_remote_post($endpoint, array(
                'body' => json_encode($data),
                'headers' => array('Content-Type' => 'application/json'),
            ));

            if (is_wp_error($response)) {
                return array('success' => false, 'message' => 'Payment request failed.');
            }

            $response_body = wp_remote_retrieve_body($response);
            $response_data = json_decode($response_body, true);

            // Handle response and return appropriate result
            return array('success' => true); // Example success response
        }

        public function receipt_page($order) {
            // Display receipt page content if needed
        }

        public function check_response() {
            // Handle the response from VisaNet
        }
    }

    function add_visanet_custom_gateway($methods) {
        $methods[] = 'WC_Gateway_VisaNet_Custom';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'add_visanet_custom_gateway');
}
