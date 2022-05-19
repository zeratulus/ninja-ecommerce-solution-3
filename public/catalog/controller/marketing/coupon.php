<?php

use Ninja\NinjaController;

class ControllerMarketingCoupon extends NinjaController
{
    private string $route = 'marketing/coupon';

    public function checkRegistrationCoupon()
    {
        $results = '';
        $this->getLanguage()->load($this->route);
        $coupon_id = $this->getConfig()->get('coupon_settings_coupon_on_registration_id');
        $this->getLoader()->model('marketing/coupon');
        $coupon = $this->model_marketing_coupon->getCoupon($coupon_id);
        if (!empty($coupon)) {
            if ($coupon['status'] && $coupon['date_end'] > nowMySQLTimestamp()) {
                $isUsed = $this->model_marketing_coupon->isCouponUsedByCustomer($coupon_id, $this->getCustomer()->getId());
                if (empty($isUsed)) {
                    $results = sprintf($this->getLanguage()->get('text_account_unused_coupon'), $coupon['code'], $coupon['name']);
                }
            }
        }
        return $results;
    }

}
