<?php

class ControllerMailForgotten extends \Ninja\NinjaController
{
    public function index(&$route, &$args, &$output)
    {
        $this->getLoader()->language('mail/forgotten');

        $data['text_greeting'] = sprintf($this->getLanguage()->get('text_greeting'), html_entity_decode($this->getConfig()->get('config_name' . $this->language_id), ENT_QUOTES, 'UTF-8'));
        $data['text_change'] = $this->getLanguage()->get('text_change');
        $data['text_ip'] = $this->getLanguage()->get('text_ip');

        $data['reset'] = str_replace('&amp;', '&', $this->getUrl()->link('account/reset', 'code=' . $args[1], true));
        $data['ip'] = $this->getRequest()->server['REMOTE_ADDR'];

        $mail = new Mail($this->getConfig()->get('config_mail_engine'));
        $mail->initSmtp($this->getConfig());

        $mail->setTo($args[0]);
        $mail->setFrom($this->getConfig()->get('config_email'));
        $mail->setSender(html_entity_decode($this->getConfig()->get('config_name' . $this->language_id), ENT_QUOTES, 'UTF-8'));
        $mail->setSubject(html_entity_decode(sprintf($this->getLanguage()->get('text_subject'), html_entity_decode($this->getConfig()->get('config_name' . $this->language_id), ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8'));
        $mail->setHtml($this->getLoader()->view('mail/forgotten', $data));
        $mail->send();
    }
}
