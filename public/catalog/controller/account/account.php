<?php

use Ninja\NinjaController;

class ControllerAccountAccount extends NinjaController
{
    public function index()
    {
        $data['logged'] = $this->getCustomer()->isLogged();
        if (!$data['logged']) {
            $this->getSession()->data['redirect'] = $this->getUrl()->link('account/account');

            $this->getResponse()->redirect($this->getUrl()->link('account/login'));
        }

        $this->getLoader()->language('account/account');

        $this->getDocument()->setTitle($this->getLanguage()->get('heading_title'));
        $this->getDocument()->setRobots('noindex,follow');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->getLanguage()->get('text_home'),
            'href' => $this->getUrl()->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->getLanguage()->get('text_account'),
            'href' => $this->getUrl()->link('account/account', '', true)
        );

        if (isset($this->getSession()->data['success'])) {
            $data['success'] = $this->getSession()->data['success'];

            unset($this->getSession()->data['success']);
        } else {
            $data['success'] = '';
        }

        $data['edit'] = $this->getUrl()->link('account/edit');
        $data['password'] = $this->getUrl()->link('account/password');
        $data['address'] = $this->getUrl()->link('account/address');

        $data['credit_cards'] = array();

        $files = glob(DIR_APPLICATION . 'controller/extension/credit_card/*.php');

        foreach ($files as $file) {
            $code = basename($file, '.php');

            if ($this->getConfig()->get('payment_' . $code . '_status') && $this->getConfig()->get('payment_' . $code . '_card')) {
                $this->getLoader()->language('extension/credit_card/' . $code, 'extension');

                $data['credit_cards'][] = array(
                    'name' => $this->getLanguage()->get('extension')->get('heading_title'),
                    'href' => $this->getUrl()->link('extension/credit_card/' . $code, '', true)
                );
            }
        }

        $data['wishlist'] = $this->getUrl()->link('account/wishlist');
        $data['order'] = $this->getUrl()->link('account/order');
        $data['download'] = $this->getUrl()->link('account/download');

        if ($this->getConfig()->get('total_reward_status')) {
            $data['reward'] = $this->getUrl()->link('account/reward');
        } else {
            $data['reward'] = '';
        }

        $data['return'] = $this->getUrl()->link('account/return');
        $data['transaction'] = $this->getUrl()->link('account/transaction');
        $data['newsletter'] = $this->getUrl()->link('account/newsletter');
        $data['recurring'] = $this->getUrl()->link('account/recurring');

        $this->getLoader()->model('account/customer');

        $affiliate_info = $this->model_account_customer->getAffiliate($this->getCustomer()->getId());

        if (!$affiliate_info) {
            $data['affiliate'] = $this->url->link('account/affiliate/add', '', true);
        } else {
            $data['affiliate'] = $this->url->link('account/affiliate/edit', '', true);
        }

        if ($affiliate_info) {
            $data['tracking'] = $this->url->link('account/tracking', '', true);
        } else {
            $data['tracking'] = '';
        }

        //Coupon on registration
        $data['coupon_on_registration'] = '';
        if ($data['logged']) {
            $data['coupon_on_registration'] = $this->getLoader()->controller('marketing/coupon/checkRegistrationCoupon');
        }

        $data['column_left'] = $this->getLoader()->controller('common/column_left');
        $data['column_right'] = $this->getLoader()->controller('common/column_right');
        $data['content_top'] = $this->getLoader()->controller('common/content_top');
        $data['content_bottom'] = $this->getLoader()->controller('common/content_bottom');
        $data['footer'] = $this->getLoader()->controller('common/footer');
        $data['header'] = $this->getLoader()->controller('common/header');

        $this->getResponse()->setOutput($this->getLoader()->view('account/account', $data));
    }

    public function country()
    {
        $json = array();

        $this->getLoader()->model('localisation/country');

        $country_info = $this->model_localisation_country->getCountry($this->getRequest()->get['country_id']);

        if ($country_info) {
            $this->getLoader()->model('localisation/zone');

            $json = array(
                'country_id' => $country_info['country_id'],
                'name' => $country_info['name'],
                'iso_code_2' => $country_info['iso_code_2'],
                'iso_code_3' => $country_info['iso_code_3'],
                'address_format' => $country_info['address_format'],
                'postcode_required' => $country_info['postcode_required'],
                'zone' => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
                'status' => $country_info['status']
            );
        }

        $this->getResponse()->addHeader('Content-Type: application/json');
        $this->getResponse()->setOutput(json_encode($json));
    }
}
