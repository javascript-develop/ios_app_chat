<?php

class VisaNet_Payment_Gateway extends WC_Payment_Gateway {

    public function __construct() {
        $this->id = 'visanet';
        $this->method_title = 'VisaNet Direct Payment';
        $this->method_description = 'Accept payments using VisaNet Direct with direct API calls.';
        $this->supports = array('products');

  
        $this->init_form_fields();
        $this->init_settings();


        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');
        $this->merchant_id = $this->get_option('my merchant id');
        $this->api_key = $this->get_option('my visa net api key');


        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' => 'Enable/Disable',
                'type' => 'checkbox',
                'label' => 'Enable VisaNet Direct Payment',
                'default' => 'yes'
            ),
            'title' => array(
                'title' => 'Title',
                'type' => 'text',
                'description' => 'This controls the title seen during checkout.',
                'default' => 'VisaNet Direct Payment',
                'desc_tip' => true,
            ),
            'description' => array(
                'title' => 'Description',
                'type' => 'textarea',
                'description' => 'This controls the description seen during checkout.',
                'default' => 'Pay with VisaNet Direct',
                'desc_tip' => true,
            ),
            'merchant_id' => array(
                'title' => 'Merchant ID',
                'type' => 'text',
                'description' => 'Enter your VisaNet Direct Merchant ID.',
                'default' => '',
                'desc_tip' => true,
            ),
            'api_key' => array(
                'title' => 'API Key',
                'type' => 'password',
                'description' => 'Enter your VisaNet Direct API Key.',
                'default' => '',
                'desc_tip' => true,
            )
        );
    }

    public function process_payment($order_id) {
        $order = wc_get_order($order_id);

        // Create the payment via VisaNet API
        $visanet_api = new VisaNet_API($this->merchant_id, $this->api_key);
        $payment_response = $visanet_api->create_payment($order->get_total(), get_woocommerce_currency(), 'Order #' . $order_id);

        if ($payment_response && isset($payment_response->payment_url)) {
            // Redirect to the VisaNet payment page
            return array(
                'result' => 'success',
                'redirect' => $payment_response->payment_url
            );
        } else {
            wc_add_notice('Payment error: Unable to create VisaNet payment.', 'error');
            return array('result' => 'failure');
        }
    }
}
