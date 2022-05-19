<?php

namespace Mail;

use Exception;
use Ninja\NinjaController;
use Sendpulse\RestApi\exApiClient;
use Sendpulse\RestApi\Storage\FileStorage;

class MailUtils
{
    private NinjaController $c;

    public function __construct(\Registry $registry)
    {
        $this->c = new NinjaController($registry);
        $this->c->getLanguage()->load('mail/template_globals');
    }

    public function getUnsubscribeSection(): string
    {
        return "<table style='width: 100%; border: none;'><tbody><tr><td style='text-align: center;'>{$this->c->getLanguage()->get('text_unsubscribe')} <a href='{$this->c->getUrl()->link('account/newsletter')}'>{$this->c->getLanguage()->get('text_unsubscribe_href')}</a></td></tr></tbody></table>";
    }

    public function getLogoSection(): string
    {
        if ($this->c->getRequest()->server['HTTPS']) {
            $server = $this->c->getConfig()->get('config_ssl');
        } else {
            $server = $this->c->getConfig()->get('config_url');
        }

        if (is_file(DIR_IMAGE . $this->c->getConfig()->get('config_logo'))) {
            $logo = $server . 'image/' . $this->c->getConfig()->get('config_logo');
        } else {
            $logo = $this->c->getConfig()->get('config_name' . $this->c->language_id);
        }

        return "<table style='width: 100%; border: none;'><tbody><tr><td style='text-align: center;'><a href='{$this->c->getUrl()->link('common/home')}'><img src='{$logo}' style='width: 220px; height: auto; margin: 5px;'></a></td></tr></tbody></table>";
    }

//    public function getSocialsSection(): string
//    {
//        return "<tr><td class='text-center'></td></tr>";
//    }

    public function trySendMail(string $eventName, array $data)
    {
        try {
            if ($this->c->getConfig()->get('config_sendpulse_rest_api_status')) {
                //TODO: Sendpulse integration
                $api = new exApiClient(
                    $this->c->getConfig()->get('config_sendpulse_rest_api_user_id'),
                    $this->c->getConfig()->get('config_sendpulse_rest_api_secret'),
                    new FileStorage(DIR_STORAGE)
                );
                $api->startEventAutomation360($eventName, $data);
            } else {
                $mail = new \Mail($this->c->getConfig()->get('config_mail_engine'));
                $mail->initSmtp($this->c->getConfig());

                $mail->setTo($data['email_to']);
                $mail->setFrom($data['email_from']);
                $mail->setSender($data['sender']);
                $mail->setSubject($data['subject']);
                $mail->setHtml($data['html'] ?? '');
                $mail->setText($data['text'] ?? '');
                $mail->send();
            }

        } catch (Exception $e) {
            $this->c->getLog()->write($e->getMessage());
        }
    }

}