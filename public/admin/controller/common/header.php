<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

class ControllerCommonHeader extends \Ninja\AdminController
{
	public function index() {
		$data['title'] = $this->document->getTitle();

		if ($this->request->server['HTTPS']) {
			$data['base'] = HTTPS_SERVER;
		} else {
			$data['base'] = HTTP_SERVER;
		}

		$data['description'] = $this->document->getDescription();
		$data['keywords'] = $this->document->getKeywords();
		$data['links'] = $this->document->getLinks();
		$data['styles'] = $this->document->getStyles();
		$data['scripts'] = $this->document->getScripts();
		$data['lang'] = $this->language->get('code');
		$data['direction'] = $this->language->get('direction');

		$this->load->language('common/header');
		
		$data['text_logged'] = sprintf($this->language->get('text_logged'), $this->user->getUserName());

        $data['href_sign_in'] = $this->getUrl()->link('common/login', '');
        $data['href_forgot_password'] = $this->getUrl()->link('common/forgotten', '');

		if (!isset($this->request->get['user_token']) || !isset($this->session->data['user_token']) || ($this->request->get['user_token'] != $this->session->data['user_token'])) {
			$data['logged'] = '';

			$data['home'] = $this->url->link('common/dashboard', '');
		} else {
			$data['logged'] = true;

			$data['home'] = $this->getUrl()->link('common/dashboard', 'user_token=' . $this->session->data['user_token']);
			$data['logout'] = $this->getUrl()->link('common/logout', 'user_token=' . $this->session->data['user_token']);
			$data['profile'] = $this->getUrl()->link('common/profile', 'user_token=' . $this->session->data['user_token']);
			$data['new_category'] = $this->getUrl()->link('catalog/category/add', 'user_token=' . $this->session->data['user_token']);
			$data['new_customer'] = $this->getUrl()->link('user/user/add', 'user_token=' . $this->session->data['user_token']);
			$data['new_download'] = $this->getUrl()->link('catalog/download/add', 'user_token=' . $this->session->data['user_token']);
			$data['new_manufacturer'] = $this->getUrl()->link('catalog/manufacturer/add', 'user_token=' . $this->session->data['user_token']);
			$data['new_product'] = $this->getUrl()->link('catalog/product/add', 'user_token=' . $this->session->data['user_token']);

			$data['projects'] = $this->getUrl()->link('support/projects', 'user_token=' . $this->session->data['user_token']);
            $data['text_projects'] = $this->getLanguage()->get('text_projects');

            $this->load->model('user/user');
	
			$this->load->model('tool/image');
	
			$user_info = $this->model_user_user->getUser($this->user->getId());
	
			if ($user_info) {
				$data['firstname'] = $user_info['firstname'];
				$data['lastname'] = $user_info['lastname'];
				$data['username']  = $user_info['username'];
				$data['user_group'] = $user_info['user_group'];

				if (is_file($filename = DIR_IMAGE . $user_info['image'])) {
					$data['image'] = $this->model_tool_image->resize($user_info['image'], 45, 45);
				} else {
					$data['image'] = $this->model_tool_image->resize('profile.png', 45, 45);
				}

			} else {
				$data['firstname'] = '';
				$data['lastname'] = '';
				$data['user_group'] = '';
				$data['image'] = '';
			}

			// Online Stores
			$data['stores'] = array();

			$data['stores'][] = array(
				'name' => $this->config->get('config_name' . $this->language_id),
				'href' => HTTP_CATALOG
			);

			$this->load->model('setting/store');

			$results = $this->model_setting_store->getStores();

			foreach ($results as $result) {
				$data['stores'][] = array(
					'name' => $result['name'],
					'href' => $result['url']
				);
			}
		}

		$data['search'] = $this->load->controller('search/search');

		if (isFrameworkDebug()) {
            //DebugBar configuration
            $debugBar = $this->getDebugBar();
            $renderer = $debugBar->getJavascriptRenderer();
            $renderer->setBasePath('/catalog/view/javascript/debugbar/');
            $renderer->setBaseUrl(HTTP_CATALOG.'/catalog/view/javascript/debugbar/');
            $renderer->setEnableJqueryNoConflict(false);
            $renderer->disableVendor('jquery');
            $renderer->disableVendor('fontawesome');

            $data['debugbar'] = $renderer;
		}

		return $this->load->view('common/header', $data);
	}
}