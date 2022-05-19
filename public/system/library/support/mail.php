<?php
/**
 * Created by PhpStorm.
 * User: ailus
 * Date: 11.10.19
 * Time: 22:40
 */

namespace Support;


class Mail extends CustomItem
{
    private $_mail;

    protected $_smtp_hostname;
    protected $_smtp_username;
    protected $_smtp_password;
    protected $_smtp_port = 25;
    protected $_smtp_timeout = 5;
    protected $_verp = false;

    public function __construct(\Registry &$registry)
    {
        parent::__construct($registry);

        $this->_mail = new \Mail($this->controller->getConfig()->get('config_mail_engine'));
        $this->configureSmtp(array(
                'smtp_hostname' => $this->controller->getConfig()->get('config_mail_smtp_hostname'),
                'smtp_username' => $this->controller->getConfig()->get('config_mail_smtp_username'),
                'smtp_password' => $this->controller->getConfig()->get('config_mail_smtp_password'),
                'smtp_port' => $this->controller->getConfig()->get('config_mail_smtp_port'),
                'smtp_timeout' => $this->controller->getConfig()->get('config_mail_smtp_timeout')
            )
        );
    }

    public function getMail(): \Mail
    {
        return $this->_mail;
    }

    public function configureSmtp(array $data)
    {
        $map = array(
            'smtp_hostname',
            'smtp_username',
            'smtp_password',
            'smtp_port',
            'smtp_timeout'
        );
        $this->mapper($data, $map);

        $this->getMail()->adaptor->smtp_hostname = $data['smtp_hostname'];
        $this->getMail()->adaptor->smtp_username = $data['smtp_username'];
        $this->getMail()->adaptor->smtp_password = $data['smtp_password'];
        $this->getMail()->adaptor->smtp_port = $data['smtp_port'];
        $this->getMail()->adaptor->smtp_timeout = $data['smtp_timeout'];
    }

    /**
     * @return string
     */
    public function getSmtpHostname(): string
    {
        return $this->_smtp_hostname;
    }

    /**
     * @param string $smtp_hostname
     */
    public function setSmtpHostname(string $smtp_hostname)
    {
        $this->_smtp_hostname = $smtp_hostname;
    }

    /**
     * @return string
     */
    public function getSmtpUsername(): string
    {
        return $this->_smtp_username;
    }

    /**
     * @param string $smtp_username
     */
    public function setSmtpUsername(string $smtp_username)
    {
        $this->_smtp_username = $smtp_username;
    }

    /**
     * @return string
     */
    public function getSmtpPassword(): string
    {
        return $this->_smtp_password;
    }

    /**
     * @param string $smtp_password
     */
    public function setSmtpPassword(string $smtp_password)
    {
        $this->_smtp_password = $smtp_password;
    }

    /**
     * @return int
     */
    public function getSmtpPort(): int
    {
        return $this->_smtp_port;
    }

    /**
     * @param int $smtp_port
     */
    public function setSmtpPort(int $smtp_port)
    {
        $this->_smtp_port = $smtp_port;
    }

    /**
     * @return int
     */
    public function getSmtpTimeout(): int
    {
        return $this->_smtp_timeout;
    }

    /**
     * @param int $smtp_timeout
     */
    public function setSmtpTimeout(int $smtp_timeout)
    {
        $this->_smtp_timeout = $smtp_timeout;
    }

    /**
     * @return bool
     */
    public function isVerp(): bool
    {
        return $this->_verp;
    }

    /**
     * @param bool $verp
     */
    public function setVerp(bool $verp)
    {
        $this->_verp = $verp;
    }


    public function send()
    {
        if (!empty($this->getMail()->adaptor->smtp_hostname) &&
            !empty($this->getMail()->adaptor->smtp_username) &&
            !empty($this->getMail()->adaptor->smtp_password) &&
            !empty($this->getMail()->adaptor->smtp_port) &&
            !empty($this->getMail()->adaptor->smtp_timeout)
        ) {
            $this->getMail()->send();
        }
    }

}