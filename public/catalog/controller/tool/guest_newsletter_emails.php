<?php
class ControllerToolGuestNewsletterEmails extends \Ninja\Controller {
	public function index() {
        $this->getLoader()->language('tool/guest_newsletter_emails');

		$data['action'] = $this->url->link('tool/guest_newsletter_emails/addGuestNewsletter', '', true);
        $data['template_name'] = $this->getConfig()->get('config_theme');

        if (is_file($this->getConfig()->get('template_directory') . "tool/guest_newsletter_emails.twig")) {
            return $this->getLoader()->view('tool/guest_newsletter_emails', $data);
        }

        return '';
	}

    public function addGuestNewsletter()
    {
        $this->getLoader()->language('tool/guest_newsletter_emails');
        $this->getLoader()->language('tool/validator');

        $json = array();

        if ( ($this->getRequest()->isRequestMethodPost()) &&
	         ($email = $this->getRequest()->issetPost('email')) &&
	         filter_var($email, FILTER_VALIDATE_EMAIL) )
        {
            $this->getLoader()->model('tool/guest_newsletter_emails');
            if ($this->model_tool_guest_newsletter_emails->isEmailExists($email)) {
                $json['error'] = $this->getLanguage()->get('error_email_already_registered');
            } else {
                $result = $this->model_tool_guest_newsletter_emails->addEmail($email);
                if ($result) {
                    $json['success'] = $this->getLanguage()->get('text_success_email');
                } else {
                    $json['error'] = $this->getLanguage()->get('error_db_insert_failed');
                }
            }
        } else {
            $json['error'] = $this->getLanguage()->get('error_enter_correct_email');
        }

        $this->getResponse()->addHeader('Content-Type: application/json');
        $this->getResponse()->setOutput(json_encode($json));
    }
}
