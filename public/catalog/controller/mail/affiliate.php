<?php

class ControllerMailAffiliate extends \Ninja\NinjaController
{
    public function index(&$route, &$args, &$output)
    {
        $this->getLoader()->language('mail/affiliate');

        $data['text_welcome'] = sprintf($this->getLanguage()->get('text_welcome'), html_entity_decode($this->getConfig()->get('config_name' . $this->language_id), ENT_QUOTES, 'UTF-8'));
        $data['text_login'] = $this->getLanguage()->get('text_login');
        $data['text_approval'] = $this->getLanguage()->get('text_approval');
        $data['text_service'] = $this->getLanguage()->get('text_service');
        $data['text_thanks'] = $this->getLanguage()->get('text_thanks');

        $this->load->model('account/customer_group');

        if ($this->getCustomer()->isLogged()) {
            $customer_group_id = $this->getCustomer()->getGroupId();
        } else {
            $customer_group_id = $args[1]['customer_group_id'];
        }

        $customer_group_info = $this->model_account_customer_group->getCustomerGroup($customer_group_id);

        if ($customer_group_info) {
            $data['approval'] = ($this->getConfig()->get('config_affiliate_approval') || $customer_group_info['approval']);
        } else {
            $data['approval'] = '';
        }

        $data['login'] = $this->getUrl()->link('affiliate/login', '', true);
        $data['store'] = html_entity_decode($this->getConfig()->get('config_name'), ENT_QUOTES, 'UTF-8');

        $mail = new Mail($this->getConfig()->get('config_mail_engine'));
        $mail->initSmtp($this->getConfig());

        if ($this->getCustomer()->isLogged()) {
            $mail->setTo($this->getCustomer()->getEmail());
        } else {
            $mail->setTo($args[1]['email']);
        }

        $mail->setFrom($this->getConfig()->get('config_email'));
        $mail->setSender(html_entity_decode($this->getConfig()->get('config_name' . $this->language_id), ENT_QUOTES, 'UTF-8'));
        $mail->setSubject(sprintf($this->getLanguage()->get('text_subject'), html_entity_decode($this->getConfig()->get('config_name' . $this->language_id), ENT_QUOTES, 'UTF-8')));
        $mail->setHtml($this->getLoader()->view('mail/affiliate', $data));
        $mail->send();
    }

    public function alert(&$route, &$args, &$output)
    {
        // Send to main admin email if new affiliate email is enabled
        if (in_array('affiliate', (array)$this->config->get('config_mail_alert'))) {
            $this->getLoader()->language('mail/affiliate');

            $data['text_signup'] = $this->getLanguage()->get('text_signup');
            $data['text_website'] = $this->getLanguage()->get('text_website');
            $data['text_firstname'] = $this->getLanguage()->get('text_firstname');
            $data['text_lastname'] = $this->getLanguage()->get('text_lastname');
            $data['text_customer_group'] = $this->getLanguage()->get('text_customer_group');
            $data['text_email'] = $this->getLanguage()->get('text_email');
            $data['text_telephone'] = $this->getLanguage()->get('text_telephone');

            if ($this->getCustomer()->isLogged()) {
                $customer_group_id = $this->getCustomer()->getGroupId();

                $data['firstname'] = $this->getCustomer()->getFirstName();
                $data['lastname'] = $this->getCustomer()->getLastName();
                $data['email'] = $this->getCustomer()->getEmail();
                $data['telephone'] = $this->getCustomer()->getTelephone();
            } else {
                $customer_group_id = $args[1]['customer_group_id'];

                $data['firstname'] = $args[1]['firstname'];
                $data['lastname'] = $args[1]['lastname'];
                $data['email'] = $args[1]['email'];
                $data['telephone'] = $args[1]['telephone'];
            }

            $data['website'] = html_entity_decode($args[1]['website'], ENT_QUOTES, 'UTF-8');
            $data['company'] = $args[1]['company'];

            $this->getLoader()->model('account/customer_group');

            $customer_group_info = $this->model_account_customer_group->getCustomerGroup($customer_group_id);

            if ($customer_group_info) {
                $data['customer_group'] = $customer_group_info['name'];
            } else {
                $data['customer_group'] = '';
            }

            $mail = new Mail($this->getConfig()->get('config_mail_engine'));
            $mail->initSmtp($this->getConfig());

            $mail->setTo($this->getConfig()->get('config_email'));
            $mail->setFrom($this->getConfig()->get('config_email'));
            $mail->setSender(html_entity_decode($this->getConfig()->get('config_name' . $this->language_id), ENT_QUOTES, 'UTF-8'));
            $mail->setSubject(html_entity_decode($this->getLanguage()->get('text_new_affiliate'), ENT_QUOTES, 'UTF-8'));
            $mail->setHtml($this->getLoader()->view('mail/affiliate_alert', $data));
            $mail->send();

            // Send to additional alert emails if new affiliate email is enabled
            $emails = explode(',', $this->getConfig()->get('config_mail_alert_email'));

            foreach ($emails as $email) {
                if (utf8_strlen($email) > 0 && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $mail->setTo($email);
                    $mail->send();
                }
            }
        }
    }
}		