<?php

class ControllerExtensionPaymentLiqPay extends \Ninja\NinjaController
{
    private $error = array();

    public function index()
    {
        $this->getLoader()->language('extension/payment/liqpay');

        $this->getDocument()->setTitle($this->language->get('heading_title'));

        $this->getLoader()->model('setting/setting');

        if (($this->getRequest()->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_liqpay', $this->getRequest()->post);

            $this->getSession()->data['success'] = $this->getLanguage()->get('text_success');

            $this->getResponse()->redirect($this->getUrl()->link('marketplace/extension', 'user_token=' . $this->getSession()->data['user_token'] . '&type=payment'));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['merchant'])) {
            $data['error_merchant'] = $this->error['merchant'];
        } else {
            $data['error_merchant'] = '';
        }

        if (isset($this->error['signature'])) {
            $data['error_signature'] = $this->error['signature'];
        } else {
            $data['error_signature'] = '';
        }

        if (isset($this->error['type'])) {
            $data['error_type'] = $this->error['type'];
        } else {
            $data['error_type'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->getLanguage()->get('text_home'),
            'href' => $this->getUrl()->link('common/dashboard', 'user_token=' . $this->getSession()->data['user_token'])
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->getLanguage()->get('text_extension'),
            'href' => $this->getUrl()->link('marketplace/extension', 'user_token=' . $this->getSession()->data['user_token'] . '&type=payment')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->getLanguage()->get('heading_title'),
            'href' => $this->getUrl()->link('extension/payment/liqpay', 'user_token=' . $this->getSession()->data['user_token'])
        );

        $data['action'] = $this->getUrl()->link('extension/payment/liqpay', 'user_token=' . $this->getSession()->data['user_token']);

        $data['cancel'] = $this->getUrl()->link('marketplace/extension', 'user_token=' . $this->getSession()->data['user_token'] . '&type=payment');

        if (isset($this->getRequest()->post['payment_liqpay_merchant'])) {
            $data['payment_liqpay_merchant'] = $this->getRequest()->post['payment_liqpay_merchant'];
        } else {
            $data['payment_liqpay_merchant'] = $this->getConfig()->get('payment_liqpay_merchant');
        }

        if (isset($this->getRequest()->post['payment_liqpay_signature'])) {
            $data['payment_liqpay_signature'] = $this->getRequest()->post['payment_liqpay_signature'];
        } else {
            $data['payment_liqpay_signature'] = $this->getConfig()->get('payment_liqpay_signature');
        }

        if (isset($this->getRequest()->post['payment_liqpay_type'])) {
            $data['payment_liqpay_type'] = $this->getRequest()->post['payment_liqpay_type'];
        } else {
            $data['payment_liqpay_type'] = $this->config->get('payment_liqpay_type');
        }

        if (isset($this->getRequest()->post['payment_liqpay_total'])) {
            $data['payment_liqpay_total'] = $this->getRequest()->post['payment_liqpay_total'];
        } else {
            $data['payment_liqpay_total'] = $this->getConfig()->get('payment_liqpay_total');
        }

        if (isset($this->getRequest()->post['payment_liqpay_order_status_id'])) {
            $data['payment_liqpay_order_status_id'] = $this->getRequest()->post['payment_liqpay_order_status_id'];
        } else {
            $data['payment_liqpay_order_status_id'] = $this->getConfig()->get('payment_liqpay_order_status_id');
        }

        if (isset($this->getRequest()->post['payment_liqpay_order_canceled_status_id'])) {
            $data['payment_liqpay_order_canceled_status_id'] = $this->getRequest()->post['payment_liqpay_order_canceled_status_id'];
        } else {
            $data['payment_liqpay_order_canceled_status_id'] = $this->getConfig()->get('payment_liqpay_order_canceled_status_id');
        }

        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        if (isset($this->getRequest()->post['payment_liqpay_geo_zone_id'])) {
            $data['payment_liqpay_geo_zone_id'] = $this->getRequest()->post['payment_liqpay_geo_zone_id'];
        } else {
            $data['payment_liqpay_geo_zone_id'] = $this->getConfig()->get('payment_liqpay_geo_zone_id');
        }

        $this->getLoader()->model('localisation/geo_zone');

        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        if (isset($this->getRequest()->post['payment_liqpay_status'])) {
            $data['payment_liqpay_status'] = $this->getRequest()->post['payment_liqpay_status'];
        } else {
            $data['payment_liqpay_status'] = $this->getConfig()->get('payment_liqpay_status');
        }

        if (isset($this->getRequest()->post['payment_liqpay_sort_order'])) {
            $data['payment_liqpay_sort_order'] = $this->getRequest()->post['payment_liqpay_sort_order'];
        } else {
            $data['payment_liqpay_sort_order'] = $this->getConfig()->get('payment_liqpay_sort_order');
        }

        $data['header'] = $this->getLoader()->controller('common/header');
        $data['column_left'] = $this->getLoader()->controller('common/column_left');
        $data['footer'] = $this->getLoader()->controller('common/footer');

        $this->getResponse()->setOutput($this->getLoader()->view('extension/payment/liqpay', $data));
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/payment/liqpay')) {
            $this->error['warning'] = $this->getLanguage()->get('error_permission');
        }

        if (!$this->getRequest()->post['payment_liqpay_merchant']) {
            $this->error['merchant'] = $this->getLanguage()->get('error_merchant');
        }

        if (!$this->getRequest()->post['payment_liqpay_signature']) {
            $this->error['signature'] = $this->getLanguage()->get('error_signature');
        }

        return !$this->error;
    }
}