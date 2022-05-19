<?php

class ControllerExtensionPaymentLiqPay extends \Ninja\NinjaController
{
    const STATUS_SUCCESS = 'success';
    const STATUS_WAIT_ACCEPT = 'wait_accept';

    public function index()
    {
        $data['button_confirm'] = $this->getLanguage()->get('button_confirm');

        $this->getLoader()->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->getSession()->data['order_id']);

        $data['action'] = 'https://liqpay.ua/?do=clickNbuy';

        $xml = '<request>';
        $xml .= '	<version>1.2</version>';
        $xml .= '	<result_url>' . $this->getUrl()->link('checkout/success') . '</result_url>';
        $xml .= '	<server_url>' . $this->getUrl()->link('extension/payment/liqpay/callback') . '</server_url>';
        $xml .= '	<merchant_id>' . $this->getConfig()->get('payment_liqpay_merchant') . '</merchant_id>';
        $xml .= '	<order_id>' . $this->getSession()->data['order_id'] . '</order_id>';
        $xml .= '	<amount>' . $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false) . '</amount>';
        $xml .= '	<currency>' . $order_info['currency_code'] . '</currency>';
        $xml .= '	<description>' . $this->getConfig()->get('config_name' . $this->language_id) . ' ' . $order_info['payment_firstname'] . ' ' . $order_info['payment_address_1'] . ' ' . $order_info['payment_address_2'] . ' ' . $order_info['payment_city'] . ' ' . $order_info['email'] . '</description>';
        $xml .= '	<default_phone></default_phone>';
        $xml .= '	<pay_way>' . $this->getConfig()->get('payment_liqpay_type') . '</pay_way>';
        $xml .= '</request>';

        $data['xml'] = base64_encode($xml);
        $data['signature'] = base64_encode(sha1($this->getConfig()->get('payment_liqpay_signature') . $xml . $this->getConfig()->get('payment_liqpay_signature'), true));

        return $this->getLoader()->view('extension/payment/liqpay', $data);
    }

    public function callback()
    {
        $xml = base64_decode($this->getRequest()->post['operation_xml']);
        $signature = base64_encode(sha1($this->getConfig()->get('payment_liqpay_signature') . $xml . $this->getConfig()->get('payment_liqpay_signature'), true));

        if ($signature == $this->getRequest()->post['signature']) {
            //process LiqPay response
            $response = simplexml_load_string($xml);
            if ($response !== false) {
                $this->getLoader()->model('checkout/order');
                $order_id = $response->order_id;
                $status = utf8_strtolower($response->status);
                $transaction_id = $response->transaction_id;
                if ($status == self::STATUS_SUCCESS || $status == self::STATUS_WAIT_ACCEPT) {
                    $order_status_id = $this->getConfig()->get('payment_liqpay_order_status_id');
                } else {
                    $order_status_id = $this->getConfig()->get('payment_liqpay_order_canceled_status_id');
                }
                $comment = " - LiqPay Response for Order #$order_id; LiqPay Transaction: $transaction_id; Status: $status";
                if (property_exists($response, 'err_code') && property_exists($response, 'err_description')) {
                    if (!empty($response->err_code) || !empty($response->err_description)) {
                        $this->getSession()->data['error'] = $error = "LiqPay Error #{$response->err_code}: {$response->err_description}";
                        $comment .= "\n$error";
                    }
                }
                $this->model_checkout_order->addOrderHistory($order_id, $order_status_id, $comment);
            }
        }
    }

}