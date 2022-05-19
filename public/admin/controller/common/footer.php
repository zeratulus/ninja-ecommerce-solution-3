<?php
class ControllerCommonFooter extends \Ninja\AdminController
{
	public function index() {
		$this->load->language('common/footer');

		if ($this->user->isLogged() && isset($this->request->get['user_token']) && ($this->request->get['user_token'] == $this->session->data['user_token'])) {
			$data['logged'] = true;
			$data['text_version'] = sprintf($this->language->get('text_version'), VERSION);
		} else {
			$data['logged'] = false;
			$data['text_version'] = '';
		}

		if (defined('DEV')) {
			if (DEV == true) {
				//update SQLs
				$this->registry->get('debugbar')->getCollector('mysqli_queries')->setQueries($this->registry->get('db')->getQueries());
				//DebugBar configuration
				$data['debugbar'] = $this->getDebugBar()->getJavascriptRenderer();
			}
		}

		$data['tinymce_filemanager'] = $this->getUrl()->link('tool/tinymce', "user_token={$this->getUserToken()}");

		return $this->load->view('common/footer', $data);
	}
}
