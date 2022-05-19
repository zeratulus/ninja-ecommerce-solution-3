<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

class ControllerCheckoutSuccess extends \Ninja\NinjaController
{
    public function index()
    {
        if (isset($this->getSession()->data['order_id']) && (!empty($this->getSession()->data['order_id']))) {
            $this->getSession()->data['last_order_id'] = $order_id = $this->getSession()->data['order_id'];
        }

//At this moment we cant have a response from LiqPay so just show success page
//        $this->getLoader()->model('checkout/order');
//        $order = $this->model_checkout_order->getOrder($order_id);
//        if (!empty($order)) {
//            if ($order['payment_code'] == 'liqpay') {
//                if ($order['order_status_id'] == $this->getConfig()->get('payment_liqpay_order_status_id')) {
//                    $this->getLoader()->language('checkout/success');
//                } else if ($order['order_status_id'] == $this->getConfig()->get('payment_liqpay_order_canceled_status_id')) {
//                    $this->getLoader()->language('checkout/error');
//                }
//            } else {
                $this->getLoader()->language('checkout/success');
//            }
//        }

        if (isset($this->getSession()->data['order_id'])) {
            $this->cart->clear();

            unset($this->getSession()->data['shipping_method']);
            unset($this->getSession()->data['shipping_methods']);
            unset($this->getSession()->data['payment_method']);
            unset($this->getSession()->data['payment_methods']);
            unset($this->getSession()->data['guest']);
            unset($this->getSession()->data['comment']);
            unset($this->getSession()->data['order_id']);
            unset($this->getSession()->data['coupon']);
            unset($this->getSession()->data['reward']);
            unset($this->getSession()->data['voucher']);
            unset($this->getSession()->data['vouchers']);
            unset($this->getSession()->data['totals']);
        }

        if (!empty($this->getSession()->data['last_order_id'])) {
            $this->getDocument()->setTitle(sprintf($this->language->get('heading_title_customer'), $this->session->data['last_order_id']));
        } else {
            $this->getDocument()->setTitle($this->language->get('heading_title'));
        }
        $this->getDocument()->setRobots('noindex,follow');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->getLanguage()->get('text_home'),
            'href' => $this->getUrl()->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->getLanguage()->get('text_basket'),
            'href' => $this->getUrl()->link('checkout/cart')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->getLanguage()->get('text_checkout'),
            'href' => $this->getUrl()->link('checkout/checkout', '', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->getLanguage()->get('text_success'),
            'href' => $this->getUrl()->link('checkout/success')
        );

        if (!empty($this->getSession()->data['last_order_id'])) {
            $data['heading_title'] = sprintf($this->getLanguage()->get('heading_title_customer'), $this->getSession()->data['last_order_id']);
        } else {
            $data['heading_title'] = $this->getLanguage()->get('heading_title');
        }

        if ($this->customer->isLogged()) {
            $data['text_message'] = sprintf($this->getLanguage()->get('text_customer'),
                $this->getUrl()->link('account/order/info&order_id=' . $this->session->data['last_order_id']),
                $this->getUrl()->link('account/account'),
                $this->getUrl()->link('account/order'),
                $this->getUrl()->link('information/contact'),
                $this->getUrl()->link('product/special'),
                $this->getSession()->data['last_order_id'],
                $this->getUrl()->link('account/download')
            );
        } else {
            $data['text_message'] = sprintf($this->getLanguage()->get('text_guest'),
                $this->getUrl()->link('information/contact'),
                $this->getSession()->data['last_order_id']
            );
        }

        $data['continue'] = $this->getUrl()->link('common/home');

        $data['column_left'] = $this->getLoader()->controller('common/column_left');
        $data['column_right'] = $this->getLoader()->controller('common/column_right');
        $data['content_top'] = $this->getLoader()->controller('common/content_top');
        $data['content_bottom'] = $this->getLoader()->controller('common/content_bottom');
        $data['footer'] = $this->getLoader()->controller('common/footer');
        $data['header'] = $this->getLoader()->controller('common/header');

        $this->getResponse()->setOutput($this->getLoader()->view('common/success', $data));
    }

}