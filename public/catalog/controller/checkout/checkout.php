<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

class ControllerCheckoutCheckout extends \Ninja\NinjaController
{
    private string $route = 'checkout/checkout';
    private bool $isRegisterCustomer = false;
    private array $error = [];

    public function index()
    {
        // Validate cart has products and has stock.
        if ((!$this->cart->hasProducts() && empty($this->getSession()->data['vouchers'])) || (!$this->cart->hasStock() && !$this->getConfig()->get('config_stock_checkout'))) {
            $this->getResponse()->redirect($this->url->link('checkout/cart'));
        }

        // Validate minimum quantity requirements.
        $products = $this->cart->getProducts();

        foreach ($products as $product) {
            $product_total = 0;

            foreach ($products as $product_2) {
                if ($product_2['product_id'] == $product['product_id']) {
                    $product_total += $product_2['quantity'];
                }
            }

            if ($product['minimum'] > $product_total) {
                $this->getResponse()->redirect($this->url->link('checkout/cart'));
            }
        }

        $this->getLoader()->language('checkout/checkout');

        $this->getDocument()->setTitle($this->language->get('heading_title'));
        $this->getDocument()->setRobots('noindex,follow');

        $this->getDocument()->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js', 'header', 'defer');
        $this->getDocument()->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js', 'header', 'defer');
        $this->getDocument()->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js', 'header', 'defer');
        $this->getDocument()->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');

        // Required by klarna
        if ($this->getConfig()->get('payment_klarna_account') || $this->getConfig()->get('payment_klarna_invoice')) {
            $this->getDocument()->addScript('http://cdn.klarna.com/public/kitt/toc/v1.0/js/klarna.terms.min.js', 'header', 'defer');
        }

        if ($this->getRequest()->isRequestMethodPost()) {
            $this->validateCheckout();
            $this->processCheckout();
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->getLanguage()->get('text_home'),
            'href' => $this->getUrl()->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->getLanguage()->get('text_cart'),
            'href' => $this->getUrl()->link('checkout/cart')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->getLanguage()->get('heading_title'),
            'href' => $this->getUrl()->link('checkout/checkout', '', true)
        );

        $data['text_checkout_option'] = sprintf($this->getLanguage()->get('text_checkout_option'), 1);
        $data['text_checkout_account'] = sprintf($this->getLanguage()->get('text_checkout_account'), 2);
        $data['text_checkout_payment_address'] = sprintf($this->getLanguage()->get('text_checkout_payment_address'), 2);
        $data['text_checkout_shipping_address'] = sprintf($this->getLanguage()->get('text_checkout_shipping_address'), 3);
        $data['text_checkout_shipping_method'] = sprintf($this->getLanguage()->get('text_checkout_shipping_method'), 4);

        if ($this->cart->hasShipping()) {
            $data['text_checkout_payment_method'] = sprintf($this->getLanguage()->get('text_checkout_payment_method'), 5);
            $data['text_checkout_confirm'] = sprintf($this->getLanguage()->get('text_checkout_confirm'), 6);
        } else {
            $data['text_checkout_payment_method'] = sprintf($this->getLanguage()->get('text_checkout_payment_method'), 3);
            $data['text_checkout_confirm'] = sprintf($this->getLanguage()->get('text_checkout_confirm'), 4);
        }

        if (isset($this->getSession()->data['error'])) {
            $data['error_warning'] = $this->getSession()->data['error'];
            unset($this->getSession()->data['error']);
        } else {
            $data['error_warning'] = '';
        }

        $data['logged'] = $this->getCustomer()->isLogged();

        //Coupon on registration
        $data['coupon_on_registration'] = '';
        if ($data['logged']) {
            $data['coupon_on_registration'] = $this->getLoader()->controller('marketing/coupon/checkRegistrationCoupon');
        }

        if (isset($this->getSession()->data['account'])) {
            $data['account'] = $this->getSession()->data['account'];
        } else {
            $data['account'] = '';
        }

        if (!empty($first_name = $this->getRequest()->issetPost('first_name'))) {
            $data['first_name'] = $first_name;
        } else {
            $data['first_name'] = $this->getCustomer()->getFirstName();
        }
        if (!empty($last_name = $this->getRequest()->issetPost('last_name'))) {
            $data['last_name'] = $last_name;
        } else {
            $data['last_name'] = $this->getCustomer()->getLastName();
        }
        if (!empty($email = $this->getRequest()->issetPost('email'))) {
            $data['email'] = $email;
        } else {
            $data['email'] = $this->getCustomer()->getEmail();
        }
        if (!empty($telephone = $this->getRequest()->issetPost('telephone'))) {
            $data['telephone'] = $telephone;
        } else {
            $data['telephone'] = $this->getCustomer()->getTelephone();
        }

        $data['payment_code'] = $this->getRequest()->issetPost('payment_method');
        $data['shipping_code'] = $this->getRequest()->issetPost('shipping_method');

        $data['shipping_required'] = $this->cart->hasShipping();

        //Register Form

        $data['entry_newsletter'] = sprintf($this->getLanguage()->get('entry_newsletter'), $this->getConfig()->get('config_name'));

        $data['customer_groups'] = array();

        if (is_array($this->getConfig()->get('config_customer_group_display'))) {
            $this->getLoader()->model('account/customer_group');

            $customer_groups = $this->model_account_customer_group->getCustomerGroups();

            foreach ($customer_groups  as $customer_group) {
                if (in_array($customer_group['customer_group_id'], $this->getConfig()->get('config_customer_group_display'))) {
                    $data['customer_groups'][] = $customer_group;
                }
            }
        }

        $data['customer_group_id'] = $this->getConfig()->get('config_customer_group_id');

        if (isset($this->getSession()->data['shipping_address']['postcode'])) {
            $data['postcode'] = $this->getSession()->data['shipping_address']['postcode'];
        } else {
            $data['postcode'] = '';
        }

        if (isset($this->getSession()->data['shipping_address']['country_id'])) {
            $data['country_id'] = $this->getSession()->data['shipping_address']['country_id'];
        } else {
            $data['country_id'] = $this->getConfig()->get('config_country_id');
        }

        if (isset($this->getSession()->data['shipping_address']['zone_id'])) {
            $data['zone_id'] = $this->getSession()->data['shipping_address']['zone_id'];
        } else {
            $data['zone_id'] = '';
        }

        $this->getLoader()->model('localisation/country');

        $data['countries'] = $this->model_localisation_country->getCountries();

        // Custom Fields
        $this->getLoader()->model('account/custom_field');

        $data['custom_fields'] = $this->model_account_custom_field->getCustomFields();

        // Captcha
        if ($this->getConfig()->get('captcha_' . $this->getConfig()->get('config_captcha') . '_status') &&
            in_array('register', (array)$this->getConfig()->get('config_captcha_page'))) {
            $data['captcha'] = $this->getLoader()->controller('extension/captcha/' . $this->getConfig()->get('config_captcha'));
        } else {
            $data['captcha'] = '';
        }

        if ($this->getConfig()->get('config_account_id')) {
            $this->getLoader()->model('catalog/information');

            $information_info = $this->model_catalog_information->getInformation($this->getConfig()->get('config_account_id'));

            if ($information_info) {
                $data['text_agree'] = sprintf($this->getLanguage()->get('text_agree'), $this->getUrl()->link('information/information/agree', 'information_id=' . $this->getConfig()->get('config_account_id'), true), $information_info['title'], $information_info['title']);
            } else {
                $data['text_agree'] = '';
            }
        } else {
            $data['text_agree'] = '';
        }

        //Payment method
        $payments = $this->getLoader()->controller('common/cms/getPaymentMethods');
        foreach ($payments as $payment) {
            $item = $payment;

            //for all webmoneys make one logo
            if (strpos($item['code'], 'webmoney') !== false) {
                $item['code'] = 'webmoney';
            }

            $image = "catalog/view/theme/default/image/payment/{$item['code']}.png";
            if (is_file($image)) {
                $item['image'] = $image;
            } else {
                $image = "catalog/view/theme/default/image/payment/{$item['code']}.jpg";
                if (is_file($image)) {
                    $item['image'] = $image;
                }
            }
            $data['payments'][$payment['code']] = $item;
            unset($item);
        }

        //Shipping methods
        $data['shippings'] = $this->getLoader()->controller('common/cms/getShippingMethods');

        $data['action'] = $this->getUrl()->link($this->route);

        $data['column_left'] = $this->getLoader()->controller('common/column_left');
        $data['column_right'] = $this->getLoader()->controller('common/column_right');
        $data['content_top'] = $this->getLoader()->controller('common/content_top');
        $data['content_bottom'] = $this->getLoader()->controller('common/content_bottom');
        $data['footer'] = $this->getLoader()->controller('common/footer');
        $data['header'] = $this->getLoader()->controller('common/header');

        $this->getResponse()->setOutput($this->getLoader()->view('checkout/checkout', $data));
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

    public function customfield()
    {
        $json = array();

        $this->getLoader()->model('account/custom_field');

        // Customer Group
        if (isset($this->getRequest()->get['customer_group_id']) &&
            is_array($this->getConfig()->get('config_customer_group_display')) &&
            in_array($this->getRequest()->get['customer_group_id'], $this->getConfig()->get('config_customer_group_display'))) {
            $customer_group_id = $this->getRequest()->get['customer_group_id'];
        } else {
            $customer_group_id = $this->getConfig()->get('config_customer_group_id');
        }

        $custom_fields = $this->model_account_custom_field->getCustomFields($customer_group_id);

        foreach ($custom_fields as $custom_field) {
            $json[] = array(
                'custom_field_id' => $custom_field['custom_field_id'],
                'required' => $custom_field['required']
            );
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function validateCheckout(): void {
        $this->validateCustomer();
        $this->validatePassword();
        $this->validateShipping();

        // Captcha
        if ($this->getConfig()->get('captcha_' . $this->getConfig()->get('config_captcha') . '_status') && in_array('checkout', (array)$this->getConfig()->get('config_captcha_page'))) {
            $captcha = $this->getLoader()->controller('extension/captcha/' . $this->getConfig()->get('config_captcha') . '/validate');
            if ($captcha) {
                $this->error['captcha'] = $captcha;
            }
        }

    }

    private function validatePassword(): void {
        $this->isRegisterCustomer = (bool)$this->getRequest()->issetPost('is_register');
        if (!$this->getCustomer()->isLogged() && $this->isRegisterCustomer) {
            $password = html_entity_decode($this->getRequest()->issetPost('password'), ENT_QUOTES, 'UTF-8');
            if (utf8_strlen($password) < 4 || (utf8_strlen($password) > 40)) {
                $this->error['password'] = $this->getLanguage()->get('error_password');
            }

            $confirm = html_entity_decode($this->getRequest()->issetPost('confirm'), ENT_QUOTES, 'UTF-8');
            if ($confirm != $password) {
                $this->error['confirm'] = $this->getLanguage()->get('error_confirm');
            }
        }
    }

    private function validateCustomer(): void {
        $firstname = $this->getRequest()->issetPost('firstname');
        if ((utf8_strlen(trim($firstname)) < 1) || (utf8_strlen(trim($firstname)) > 32)) {
            $this->error['firstname'] = $this->getLanguage()->get('error_firstname');
        }

        $lastname = $this->getRequest()->issetPost('lastname');
        if ((utf8_strlen(trim($lastname)) < 1) || (utf8_strlen(trim($lastname)) > 32)) {
            $this->error['lastname'] = $this->getLanguage()->get('error_lastname');
        }

        $email = $this->getRequest()->issetPost('email');
        if ((utf8_strlen($email) > 96) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error['email'] = $this->getLanguage()->get('error_email');
        }

        $telephone = $this->getRequest()->issetPost('telephone');
        if ((utf8_strlen($telephone) < 3) || (utf8_strlen($telephone) > 32)) {
            $this->error['telephone'] = $this->getLanguage()->get('error_telephone');
        }

    }

    private function validateShipping(): void {
        $this->validateNovaposhta();
    }

    private function validateNovaposhta(): void {
        $city = $this->getRequest()->issetPost('city');
        if (empty($city)) {
            $this->error['city'] = $this->getLanguage()->get('error_city');
        }

        $warehouse = $this->getRequest()->issetPost('warehouse');
        if (empty($warehouse)) {
            $this->error['warehouse'] = $this->getLanguage()->get('error_warehouse');
        }
    }

    private function processCheckout()
    {
        if (empty($this->error)) {
            //Customer fields
            $firstname = $this->getRequest()->issetPost('firstname');
            $lastname = $this->getRequest()->issetPost('lastname');
            $email = $this->getRequest()->issetPost('email');
            $telephone = $this->getRequest()->issetPost('telephone');

            //shipping fields
            $country = $this->getRequest()->issetPost('country');
            $country_id = $this->getRequest()->issetPost('country_id');
            $city = $this->getRequest()->issetPost('city');
            $address_1 = $this->getRequest()->issetPost('address_1');

            //Novaposhta fields
            $warehouse = $this->getRequest()->issetPost('warehouse');

            //set session data for confirm page
            if ($this->isRegisterCustomer) {
                $password = html_entity_decode($this->getRequest()->issetPost('password'), ENT_QUOTES, 'UTF-8');
                $this->getLoader()->model('account/customer');
                $customer_id = $this->model_account_customer->addCustomer($this->getRequest()->post);
                // Clear any previous login attempts for unregistered accounts.
                $this->model_account_customer->deleteLoginAttempts($email);
                $this->getCustomer()->login($email, $password);
                unset($this->getSession()->data['guest']);
            } else {
                //just process as guest
                $this->getSession()->data['guest']['customer_group_id'] = $this->getConfig()->get('config_customer_group_id');
                $this->getSession()->data['guest']['firstname'] = $firstname;
                $this->getSession()->data['guest']['lastname'] = $lastname;
                $this->getSession()->data['guest']['email'] = $email;
                $this->getSession()->data['guest']['telephone'] = $telephone;
                //@todo process custom fields
                $this->getSession()->data['guest']['custom_field'] = [];
            }

            $addressInfo = [
                'firstname' => $firstname,
                'lastname' => $lastname,
                'company' => '',
                'address_1' => !empty($warehouse) ? $warehouse : $address_1,
                'address_2' => '',
                'city' => $city,
                'postcode' => '',
                'zone' => '',
                'zone_id' => '',
                'country' => $country,
                'country_id' => $country_id,
                'address_format' => '',
                'custom_field'  => []
            ];

            $this->getSession()->data['payment_address'] = $addressInfo;

            $payment_code = $this->getRequest()->issetPost('payment_method');
            $this->getSession()->data['payment_method']['code'] = $payment_code;

            $payment_title = $payment_code;
            $results = $this->getLoader()->controller('common/cms/getPaymentMethods');
            foreach ($results as $payment) {
                if ($payment['code'] == $payment_code) {
                    $payment_title = $payment['title'];
                    break;
                }
            }
            $this->getSession()->data['payment_method']['title'] = $payment_title;

            if ($this->cart->hasShipping()) {
                $this->getSession()->data['shipping_address'] = $addressInfo;
            }
            $shipping_code = $this->getRequest()->issetPost('shipping_method');
            $this->getSession()->data['shipping_method']['code'] = $shipping_code;

            $shipping_title = $shipping_code;
            $results = $this->getLoader()->controller('common/cms/getShippingMethods');
            foreach ($results as $shipping) {
                if ($shipping['code'] == $shipping_code) {
                    $shipping_title = $shipping['title'];
                    break;
                }
            }
            $this->getSession()->data['shipping_method']['title'] = $shipping_title;

            $this->getSession()->data['comment'] = $this->getRequest()->issetPost('comment');

            $this->getResponse()->redirect($this->getUrl()->link('checkout/confirm'));
        }
    }

}
