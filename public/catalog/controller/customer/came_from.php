<?php

class ControllerCustomerCameFrom extends \Ninja\Controller {
	private $_route = 'customer/came_from';
	private $_model = 'model_customer_came_from';

	public function index()
	{
		$data = array();

		if (!isset($this->getSession()->data['came_from_processed'])) {
			$this->getLoader()->model($this->_route);
			//Prepare data
			$data['session_id'] = $this->getSession()->getId();

			$detector = $this->getBrowserDetection();
			$data['platform'] = $detector->getPlatform();
			$data['platform_version'] = $detector->getPlatformVersion();
			$data['browser'] = $detector->getName();
			$data['browser_version'] = $detector->getVersion();
			$data['user_agent'] = $detector->getUserAgent();

			isset($this->getRequest()->server['HTTPS']) ? $url = HTTPS_SERVER : $url = HTTP_SERVER;
			if (isset($this->getRequest()->server['REQUEST_URI'])) {
				$url .= $this->getRequest()->server['REQUEST_URI'];
			}
			$data['url'] = $url;

			$referer = '';
			if (isset($this->getRequest()->server['HTTP_REFERER'])) {
				$referer = $this->getRequest()->server['HTTP_REFERER'];
			}
			$data['referer'] = $referer;

			if ($this->getCustomer()->isLogged()) {
				$data['customer_id'] = $this->getCustomer()->getId();
			} else {
				$data['customer_id'] = null;
			}

			$addr = new RemoteAddress();
			$data['ip'] = $addr->getIpAddress();

			$data['screen_detected'] = false;
			$data['screen_width'] = 0;
			$data['screen_height'] = 0;
			$data['date_added'] = nowMySQLTimestamp();

			$data['utm_id'] = null;

			//Process UTM
			if (!empty($utm_source = $this->getRequest()->issetGet('utm_source')) ||
				!empty($utm_medium = $this->getRequest()->issetGet('utm_medium')) ||
				!empty($utm_campaign = $this->getRequest()->issetGet('utm_campaign')) ||
				!empty($utm_content = $this->getRequest()->issetGet('utm_content')) ||
				!empty($utm_term = $this->getRequest()->issetGet('utm_term')))
			{
				$utm['utm_source'] = $utm_source;
				$utm['utm_medium'] = $utm_medium;
				$utm['utm_campaign'] = $utm_campaign;
				$utm['utm_content'] = $utm_content;
				$utm['utm_term'] = $utm_term;

				$data['utm_id'] = $this->{$this->_model}->addUtm($utm);
			}

			$this->{$this->_model}->addCameFrom($data);
			$this->getSession()->data['came_from_processed'] = true;
		}
	}

	public function screen()
	{
//		if (!isset($this->getSession()->data['came_from_processed_screen'])) {
			if (!empty($width = $this->getRequest()->issetPost('width')) &&
				!empty($height = $this->getRequest()->issetPost('height')) &&
				!empty($id = $this->getRequest()->issetPost('id')))
			{
				$this->getLoader()->model($this->_route);

				$data = array();
				$data['screen_detected'] = true;
				$data['screen_width'] = $width;
				$data['screen_height'] = $height;

				$this->{$this->_model}->updateScreen($id, $data);
				$this->getSession()->data['came_from_processed_screen'] = true;
			}
//		}
	}
//	TODO: Process screen

}