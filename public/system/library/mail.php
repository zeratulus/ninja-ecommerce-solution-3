<?php
/**
 * @package OpenCart
 * @author Daniel Kerr
 * @copyright Copyright (c) 2005 - 2017, OpenCart, Ltd. (https://www.opencart.com/)
 * @license https://opensource.org/licenses/GPL-3.0
 * @link https://www.opencart.com
 */

class Mail
{
    protected $to;
    protected $from;
    protected $sender;
    protected $reply_to;
    protected $subject;
    protected $text;
    protected $html;
    protected $attachments = array();
    public $parameter;

    public function __construct(string $adaptor = 'mail')
    {
        $class = 'Mail\\' . $adaptor;

        if (class_exists($class)) {
            $this->adaptor = new $class();
        } else {
            trigger_error('Error: Could not load mail adaptor ' . $adaptor . '!');
            exit();
        }
    }

    public function setTo(string $to)
    {
        $this->to = $to;
    }

    public function setFrom(string $from)
    {
        $this->from = $from;
    }

    public function setSender(string $sender)
    {
        $this->sender = $sender;
    }

    public function setReplyTo(string $reply_to)
    {
        $this->reply_to = $reply_to;
    }

    public function setSubject(string $subject)
    {
        $this->subject = $subject;
    }

    public function setText(string $text)
    {
        $this->text = $text;
    }

    public function setHtml(string $html)
    {
        $this->html = $html;
    }

    public function addAttachment(string $filename)
    {
        $this->attachments[] = $filename;
    }

    /**
     * Send email
     */
    public function send()
    {
        if (!$this->to) {
            throw new \Exception('Error: E-Mail to required!');
        }

        if (!$this->from) {
            throw new \Exception('Error: E-Mail from required!');
        }

        if (!$this->sender) {
            throw new \Exception('Error: E-Mail sender required!');
        }

        if (!$this->subject) {
            throw new \Exception('Error: E-Mail subject required!');
        }

        if ((!$this->text) && (!$this->html)) {
            throw new \Exception('Error: E-Mail message required!');
        }

        foreach (get_object_vars($this) as $key => $value) {
            $this->adaptor->$key = $value;
        }

        if (!isFrameworkDebug()) {
            $this->adaptor->send();
        }
    }

    /**
     * Init SMTP configs if current Adaptor is instance of Mail\Smtp
     * @param Config $config
     */
    public function initSmtp(Config $config)
    {
        if (get_class($this->adaptor) == 'Mail\Smtp') {
            $this->adaptor->parameter = $config->get('config_mail_parameter');
            $this->adaptor->smtp_hostname = $config->get('config_mail_smtp_hostname');
            $this->adaptor->smtp_username = $config->get('config_mail_smtp_username');
            $this->adaptor->smtp_password = html_entity_decode($config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
            $this->adaptor->smtp_port = $config->get('config_mail_smtp_port');
            $this->adaptor->smtp_timeout = $config->get('config_mail_smtp_timeout');
        }
    }

}