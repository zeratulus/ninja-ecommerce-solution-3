<?php
class ControllerMarketingGuestEmails extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('marketing/guest_emails');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['user_token'] = $this->session->data['user_token'];

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('marketing_contact'),
			'href' => $this->url->link('marketing/contact', 'user_token=' . $data['user_token'], true)
		);

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('marketing/guest_emails', 'user_token=' . $data['user_token'], true)
        );

		$data['cancel'] = $this->url->link('marketing/contact', 'user_token=' . $data['user_token'], true);

		$this->load->model('tool/guest_newsletter_emails');

		$data['emails'] = $this->model_tool_guest_newsletter_emails->getEmails();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('marketing/guest_emails', $data));
	}

}
