<?php

namespace Mail;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP as PHPMailerSmtp;
use PHPMailer\PHPMailer\Exception;

class Smtp
{
    public $smtp_hostname;
    public $smtp_username;
    public $smtp_password;
    public $smtp_port = 25;
    public $smtp_timeout = 5;
    public $verp = false;

    public function send()
    {
        $mail = new PHPMailer(true);

        //Server settings
        $mail->AuthType = 'LOGIN';
        $mail->SMTPAutoTLS = false;
        $mail->isSMTP(); //Send using SMTP
        $mail->SMTPAuth = true; //Enable SMTP authentication
        $mail->CharSet = PHPMailer::CHARSET_UTF8;
        $mail->SMTPDebug = (defined('DEV') && constant('DEV')) ? PHPMailerSmtp::DEBUG_SERVER : PHPMailerSmtp::DEBUG_OFF;
        $mail->Host = $this->smtp_hostname; //Set the SMTP server to send through
        $mail->Username = $this->smtp_username; //SMTP username
        $mail->Password = $this->smtp_password; //SMTP password
        $mail->Port = $this->smtp_port; //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        $mail->Debugoutput = 'error_log';
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        if ($this->smtp_port == 587)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        if ($this->smtp_port == 465)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;

        $mail->setFrom($this->from);
        $mail->addAddress($this->to);
        $mail->Subject = $this->subject;
        $mail->Body = $this->html;
        $mail->AltBody = $this->text;
        $mail->AllowEmpty = true;
        if (!empty($this->html)) {
            $mail->isHTML(true);
        } else {
            $mail->Body = $this->text;
        }
        $mail->send();
    }
}
