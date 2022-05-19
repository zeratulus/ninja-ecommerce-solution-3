<?php

use Mail\MailUtils;

class ControllerMailRegister extends \Ninja\NinjaController
{
    public function index(&$route, &$args, &$output)
    {
        $this->getLoader()->language('mail/register');

        $mailUtils = new MailUtils($this->registry);
        $data['unsubscribe'] = $mailUtils->getUnsubscribeSection();

        $data['text_welcome'] = sprintf($this->getLanguage()->get('text_welcome'), html_entity_decode($this->getConfig()->get('config_name' . $this->language_id), ENT_QUOTES, 'UTF-8'));
        $data['text_login'] = $this->getLanguage()->get('text_login');
        $data['text_approval'] = $this->getLanguage()->get('text_approval');
        $data['text_service'] = $this->getLanguage()->get('text_service');
        $data['text_thanks'] = $this->getLanguage()->get('text_thanks');

        $this->load->model('account/customer_group');

        if (isset($args[0]['customer_group_id'])) {
            $customer_group_id = $args[0]['customer_group_id'];
        } else {
            $customer_group_id = $this->getConfig()->get('config_customer_group_id');
        }

        $customer_group_info = $this->model_account_customer_group->getCustomerGroup($customer_group_id);

        if ($customer_group_info) {
            $data['approval'] = $customer_group_info['approval'];
        } else {
            $data['approval'] = '';
        }
        $data['logo'] = $mailUtils->getLogoSection();
        $data['email_from'] = $this->getConfig()->get('config_email');
        $data['email_to'] = $args[0]['email'];
        $data['login'] = $this->getUrl()->link('account/login');
        $data['store'] = html_entity_decode($this->getConfig()->get('config_name' . $this->language_id), ENT_QUOTES, 'UTF-8');
        $data['sender'] = html_entity_decode($this->getConfig()->get('config_name' . $this->language_id), ENT_QUOTES, 'UTF-8');
        $data['subject'] = sprintf($this->getLanguage()->get('text_subject'), html_entity_decode($this->getConfig()->get('config_name' . $this->language_id), ENT_QUOTES, 'UTF-8'));
        $data['html'] = $this->getLoader()->view('mail/register', $data);

        $mailUtils->trySendMail('register', $data);
    }

    public function alert(&$route, &$args, &$output)
    {
        // Send to main admin email if new account email is enabled
        if (in_array('account', (array)$this->getConfig()->get('config_mail_alert'))) {
            $this->getLoader()->language('mail/register');

            $data['text_signup'] = $this->getLanguage()->get('text_signup');
            $data['text_firstname'] = $this->getLanguage()->get('text_firstname');
            $data['text_lastname'] = $this->getLanguage()->get('text_lastname');
            $data['text_customer_group'] = $this->getLanguage()->get('text_customer_group');
            $data['text_email'] = $this->getLanguage()->get('text_email');
            $data['text_telephone'] = $this->getLanguage()->get('text_telephone');

            $data['firstname'] = $args[0]['firstname'];
            $data['lastname'] = $args[0]['lastname'];

            $this->getLoader()->model('account/customer_group');

            if (isset($args[0]['customer_group_id'])) {
                $customer_group_id = $args[0]['customer_group_id'];
            } else {
                $customer_group_id = $this->getConfig()->get('config_customer_group_id');
            }

            $customer_group_info = $this->model_account_customer_group->getCustomerGroup($customer_group_id);

            if ($customer_group_info) {
                $data['customer_group'] = $customer_group_info['name'];
            } else {
                $data['customer_group'] = '';
            }

            $data['email'] = $args[0]['email'];
            $data['telephone'] = $args[0]['telephone'];

            $mail = new Mail($this->config->get('config_mail_engine'));
            $mail->initSmtp($this->getConfig());

            $mail->setTo($this->getConfig()->get('config_email'));
            $mail->setFrom($this->getConfig()->get('config_email'));
            $mail->setSender(html_entity_decode($this->getConfig()->get('config_name' . $this->language_id), ENT_QUOTES, 'UTF-8'));
            $mail->setSubject(html_entity_decode($this->getLanguage()->get('text_new_customer'), ENT_QUOTES, 'UTF-8'));
            $mail->setHtml($this->getLoader()->view('mail/register_alert', $data));
            $mail->send();

            // Send to additional alert emails if new account email is enabled
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