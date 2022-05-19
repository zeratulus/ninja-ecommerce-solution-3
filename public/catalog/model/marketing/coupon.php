<?php

class ModelMarketingCoupon extends Model
{

    public function getCoupon($coupon_id) {
        $query = $this->getDb()->query("SELECT DISTINCT * FROM " . DB_PREFIX . "coupon WHERE coupon_id = '" . (int)$coupon_id . "'");

        return $query->row;
    }

    public function isCouponUsedByCustomer($coupon_id, $customer_id) {
        $query = $this->getDb()->query("SELECT * FROM " . DB_PREFIX . "coupon_history ch WHERE ch.coupon_id = '$coupon_id' AND ch.customer_id = '$customer_id' ORDER BY ch.date_added");
        return $query->rows;
    }

}