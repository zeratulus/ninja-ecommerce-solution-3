<?php

class ControllerExtensionTotalCoupon extends \Ninja\NinjaController
{
    public function index()
    {
        if ($this->getConfig()->get('total_coupon_status')) {
            $this->getLoader()->language('extension/total/coupon');

            if (isset($this->getSession()->data['coupon'])) {
                $data['coupon'] = $this->getSession()->data['coupon'];
            } else {
                $data['coupon'] = '';
            }

            return $this->getLoader()->view('extension/total/coupon', $data);
        }
    }

    public function coupon()
    {
        $this->getLoader()->language('extension/total/coupon');

        $json = array();

        $this->getLoader()->model('extension/total/coupon');

        $coupon = $this->getRequest()->issetPost('coupon');

        $coupon_info = $this->model_extension_total_coupon->getCoupon($coupon);

        if (empty($coupon)) {
            $json['error'] = $this->getLanguage()->get('error_empty');

            unset($this->getSession()->data['coupon']);
        } elseif ($coupon_info) {
            $this->getSession()->data['coupon'] = $coupon;

            $this->getSession()->data['success'] = $this->getLanguage()->get('text_success');

            $json['redirect'] = $this->getUrl()->link('checkout/cart');
        } else {
            $json['error'] = $this->getLanguage()->get('error_coupon');
        }

        $this->getResponse()->addHeaderAppJson();
        $this->getResponse()->setOutput(json_encode($json));
    }
}
