<?php

class ControllerMailTransaction extends \Ninja\NinjaController
{
    public function index(&$route, &$args, &$output)
    {
        $this->getLoader()->language('mail/transaction');

        $this->getLoader()->model('account/customer');

        $customer_info = $this->model_account_customer->getCustomer($args[0]);

        if ($customer_info) {
            $data['text_received'] = sprintf($this->getLanguage()->get('text_received'), $this->getConfig()->get('config_name' . $this->language_id));
            $data['text_amount'] = $this->getLanguage()->get('text_amount');
            $data['text_total'] = $this->getLanguage()->get('text_total');

            $data['amount'] = $this->currency->format($args[2], $this->getConfig()->get('config_currency'));
            $data['total'] = $this->currency->format($this->model_account_customer->getTransactionTotal($args[0]), $this->getConfig()->get('config_currency'));

            $mail = new Mail($this->config->get('config_mail_engine'));
            $mail->initSmtp($this->getConfig());

            $mail->setTo($customer_info['email']);
            $mail->setFrom($this->getConfig()->get('config_email'));
            $mail->setSender(html_entity_decode($this->getConfig()->get('config_name' . $this->language_id), ENT_QUOTES, 'UTF-8'));
            $mail->setSubject(html_entity_decode(sprintf($this->getLanguage()->get('text_subject'), $this->getConfig()->get('config_name' . $this->language_id)), ENT_QUOTES, 'UTF-8'));
            $mail->setHtml($this->getLoader()->view('mail/transaction', $data));
            $mail->send();
        }
    }
}