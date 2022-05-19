<?php
class ControllerCommonFooter extends \Ninja\Controller {
	public function index() {
		$this->load->language('common/footer');
        $this->load->language('tool/guest_newsletter_emails');
		$this->load->model('catalog/information');

		$data['informations'] = array();

		foreach ($this->model_catalog_information->getInformations() as $result) {
			if ($result['bottom']) {
				$data['informations'][] = array(
					'title' => $result['title'],
					'href'  => $this->url->link('information/information', 'information_id=' . $result['information_id'])
				);
			}
		}

		$data['contact'] = $this->url->link('information/contact');
		$data['return'] = $this->url->link('account/return/add', '', true);
		$data['sitemap'] = $this->url->link('information/sitemap');
		$data['tracking'] = $this->url->link('information/tracking');
		$data['manufacturer'] = $this->url->link('product/manufacturer');
		$data['voucher'] = $this->url->link('account/voucher', '', true);
		$data['affiliate'] = $this->url->link('affiliate/login', '', true);
		$data['special'] = $this->url->link('product/special');
		$data['account'] = $this->url->link('account/account', '', true);
		$data['order'] = $this->url->link('account/order', '', true);
		$data['wishlist'] = $this->url->link('account/wishlist', '', true);
		$data['newsletter'] = $this->url->link('account/newsletter', '', true);

		$data['powered'] = sprintf($this->language->get('text_powered'), $this->config->get('config_name' . $this->language_id), date('Y', time()));

		// Whos Online
		if ($this->config->get('config_customer_online')) {
			$this->load->model('tool/online');

			if (isset($this->request->server['REMOTE_ADDR'])) {
				$ip = $this->request->server['REMOTE_ADDR'];
			} else {
				$ip = '';
			}

			if (isset($this->request->server['HTTP_HOST']) && isset($this->request->server['REQUEST_URI'])) {
				$url = ($this->request->server['HTTPS'] ? 'https://' : 'http://') . $this->request->server['HTTP_HOST'] . $this->request->server['REQUEST_URI'];
			} else {
				$url = '';
			}

			if (isset($this->request->server['HTTP_REFERER'])) {
				$referer = $this->request->server['HTTP_REFERER'];
			} else {
				$referer = '';
			}

			$this->model_tool_online->addOnline($ip, $this->customer->getId(), $url, $referer);
		}

		//Telephones
        $data['telephones'] = $this->getLoader()->controller('common/cms/getTelephonesLinks');

		//Address
        $data['address'] = nl2br($this->config->get('config_address' . $this->language_id));

        // Analytics
        $data['analytics'] = $this->getLoader()->controller('common/cms/getAnalyticsScripts');

        //Guest Newsletter Collect
		$data['guest_newsletter'] = $this->getLoader()->controller('tool/guest_newsletter_emails');

        $data['template_name'] = $this->config->get('config_theme');
        $data['name'] = $this->config->get('config_name' . $this->language_id);
        $data['powered'] = date('Y') . ' - ' . $data['name'] . ' - <a href="https://monsters-studio.com" target="_blank">Developed By Ninja-Studio</a>';
		$data['scripts'] = $this->document->getScripts('footer');

		if (isFrameworkDebug()) {
            $this->getDebugBar()->getCollector('mysqli_queries')->setQueries($this->registry->get('db')->getQueries());;

            $renderer = $this->getDebugBar()->getJavascriptRenderer();
            $renderer->setEnableJqueryNoConflict(false);
            $data['debugbarRenderer'] = $renderer;
        }

		return $this->load->view('common/footer', $data);
	}

}