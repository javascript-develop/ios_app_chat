<?php

class VisaNet_API {

    private $merchant_id;
    private $api_key;

    public function __construct($merchant_id, $api_key) {
        $this->merchant_id = $merchant_id;
        $this->api_key = $api_key;
    }

    public function create_payment($amount, $currency, $description = 'Payment') {
        $payload = array(
            'amount' => $amount,
            'currency' => $currency,
            'merchant_id' => $this->merchant_id,
            'api_key' => $this->api_key,
            'description' => $description
        );

        $response = wp_remote_post(`https://sandbox.api.visa.com/visadirect/mvisa/v1/merchantpushpayments/${merchant_id}`, array(
            'body' => json_encode($payload),
            'headers' => array('Content-Type' => 'application/json')
        ));

        if (is_wp_error($response)) {
            return false;
        }

        return json_decode(wp_remote_retrieve_body($response));
    }
}
